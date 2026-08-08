<?php

namespace App\Http\Controllers;

use App\Models\ThanhVien;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use App\Models\ThongBaoCaNhan;

class HoSoController extends Controller
{
    /**
     * Điểm thưởng cho chương trình giới thiệu.
     */
    private const DIEM_NGUOI_GIOI_THIEU = 50;
    private const DIEM_NGUOI_DUOC_GIOI_THIEU = 20;

    /**
     * Hiển thị giao diện chỉnh sửa hồ sơ.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        $user->load([
            'thanhVien.nguoiGioiThieu.nguoiDung',
        ]);

        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Cập nhật thông tin hồ sơ và xử lý mã giới thiệu.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'ho_ten' => [
                'required',
                'string',
                'max:255',
            ],

            'ngay_sinh' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'ma_gioi_thieu' => [
                'nullable',
                'string',
                'max:50',
            ],
        ], [
            'ho_ten.required' => 'Vui lòng nhập họ và tên của bạn.',
            'ho_ten.max' => 'Họ và tên không được vượt quá 255 ký tự.',

            'ngay_sinh.date' => 'Ngày sinh không đúng định dạng ngày tháng.',
            'ngay_sinh.before_or_equal' => 'Ngày sinh không được lớn hơn ngày hiện tại.',

            'ma_gioi_thieu.string' => 'Mã giới thiệu không hợp lệ.',
            'ma_gioi_thieu.max' => 'Mã giới thiệu không được vượt quá 50 ký tự.',
        ]);

        /*
     * Chuẩn hóa mã giới thiệu.
     */
        $maGioiThieu = strtoupper(
            trim((string) ($data['ma_gioi_thieu'] ?? ''))
        );

        /*
     * ma_gioi_thieu không thuộc bảng nguoi_dungs.
     */
        unset($data['ma_gioi_thieu']);

        /*
     * Ngày sinh chỉ được thiết lập một lần.
     *
     * Chưa có ngày sinh:
     *     -> cho phép lưu ngày sinh lần đầu.
     *
     * Đã có ngày sinh:
     *     -> loại bỏ khỏi dữ liệu cập nhật,
     *        không thể thay đổi nữa.
     */
        if ($user->ngay_sinh) {
            unset($data['ngay_sinh']);
        }

        $daLienKetGioiThieu = false;

        DB::transaction(function () use (
            $user,
            $data,
            $maGioiThieu,
            &$daLienKetGioiThieu
        ): void {

            /*
         * Cập nhật thông tin hồ sơ.
         */
            /*
 * Cập nhật thông tin hồ sơ.
 */
            $user->fill($data);

            $coThayDoi = $user->isDirty();

            if ($coThayDoi) {
                $user->save();

                ThongBaoCaNhan::create([
                    'nguoi_dung_id' => $user->id,
                    'tieu_de' => 'Thông tin tài khoản đã được cập nhật',
                    'noi_dung' => 'Thông tin cá nhân của bạn đã được cập nhật thành công.',
                    'loai_thong_bao' => 'tai_khoan',
                    'duong_dan' => route('profile.edit'),
                    'da_doc' => 0,
                    'doc_luc' => null,
                ]);
            }

            /*
         * Người dùng không nhập mã giới thiệu
         * thì chỉ cập nhật hồ sơ.
         */
            if ($maGioiThieu === '') {
                return;
            }

            /*
             * Khóa thẻ thành viên hiện tại để tránh người dùng
             * gửi yêu cầu nhiều lần và nhận thưởng trùng.
             */
            $thanhVien = ThanhVien::query()
                ->where('nguoi_dung_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (!$thanhVien) {
                throw ValidationException::withMessages([
                    'ma_gioi_thieu' => 'Tài khoản của bạn chưa có thẻ thành viên.',
                ]);
            }

            /*
             * Mỗi thành viên chỉ được liên kết người giới thiệu một lần.
             */
            if (
                $thanhVien->nguoi_gioi_thieu_id !== null
                || $thanhVien->da_nhan_thuong
            ) {
                throw ValidationException::withMessages([
                    'ma_gioi_thieu' => 'Bạn đã liên kết mã giới thiệu trước đó và không thể thay đổi.',
                ]);
            }

            /*
             * Không được sử dụng mã giới thiệu của chính mình.
             */
            if (
                $thanhVien->ma_gioi_thieu
                && strcasecmp($thanhVien->ma_gioi_thieu, $maGioiThieu) === 0
            ) {
                throw ValidationException::withMessages([
                    'ma_gioi_thieu' => 'Bạn không thể sử dụng mã giới thiệu của chính mình.',
                ]);
            }

            /*
             * Tìm chủ sở hữu của mã giới thiệu.
             */
            $nguoiGioiThieu = ThanhVien::query()
                ->where('ma_gioi_thieu', $maGioiThieu)
                ->where('id', '!=', $thanhVien->id)
                ->lockForUpdate()
                ->first();

            /*
 * Chặn vòng lặp giới thiệu.
 *
 * Các trường hợp bị chặn:
 * A nhập mã của B, sau đó B nhập mã của A.
 *
 * A được B giới thiệu,
 * B được C giới thiệu,
 * sau đó C nhập mã của A.
 */
            $thanhVienDangKiemTra = $nguoiGioiThieu;
            $cacThanhVienDaDuyet = [];

            while ($thanhVienDangKiemTra) {
                /*
     * Nếu đi ngược chuỗi giới thiệu và gặp lại chính
     * thành viên đang nhập mã thì sẽ tạo vòng lặp.
     */
                if (
                    (int) $thanhVienDangKiemTra->id
                    === (int) $thanhVien->id
                ) {
                    throw ValidationException::withMessages([
                        'ma_gioi_thieu' =>
                        'Không thể sử dụng mã này vì sẽ tạo vòng lặp giới thiệu.',
                    ]);
                }

                /*
     * Bảo vệ trường hợp dữ liệu cũ đã có vòng lặp,
     * tránh while chạy vô hạn.
     */
                if (
                    in_array(
                        (int) $thanhVienDangKiemTra->id,
                        $cacThanhVienDaDuyet,
                        true
                    )
                ) {
                    throw ValidationException::withMessages([
                        'ma_gioi_thieu' =>
                        'Chuỗi giới thiệu của mã này đang không hợp lệ.',
                    ]);
                }

                $cacThanhVienDaDuyet[] =
                    (int) $thanhVienDangKiemTra->id;

                /*
     * Không còn người giới thiệu phía trên thì chuỗi hợp lệ.
     */
                if (!$thanhVienDangKiemTra->nguoi_gioi_thieu_id) {
                    break;
                }

                /*
     * Tiếp tục kiểm tra người giới thiệu phía trên.
     */
                $thanhVienDangKiemTra = ThanhVien::query()
                    ->where(
                        'id',
                        $thanhVienDangKiemTra->nguoi_gioi_thieu_id
                    )
                    ->lockForUpdate()
                    ->first();
            }

            if (!$nguoiGioiThieu) {
                throw ValidationException::withMessages([
                    'ma_gioi_thieu' => 'Mã giới thiệu không tồn tại hoặc không hợp lệ.',
                ]);
            }

            /*
             * Kiểm tra tài khoản của người giới thiệu.
             */
            $taiKhoanNguoiGioiThieu = $nguoiGioiThieu->nguoiDung;

            if (
                !$taiKhoanNguoiGioiThieu
                || !$taiKhoanNguoiGioiThieu->trang_thai_hoat_dong
            ) {
                throw ValidationException::withMessages([
                    'ma_gioi_thieu' => 'Tài khoản sở hữu mã giới thiệu hiện không hoạt động.',
                ]);
            }

            /*
             * Liên kết người giới thiệu và đánh dấu đã nhận thưởng.
             */
            $thanhVien->update([
                'nguoi_gioi_thieu_id' => $nguoiGioiThieu->id,
                'da_nhan_thuong' => true,
            ]);

            /*
             * Thưởng cho người giới thiệu.
             * Không tính vào tổng điểm xét hạng thành viên.
             */
            $nguoiGioiThieu->congDiemKhongXetHang(
                self::DIEM_NGUOI_GIOI_THIEU,
                'Thưởng ' . self::DIEM_NGUOI_GIOI_THIEU
                    . ' điểm do giới thiệu thành viên '
                    . ($user->ho_ten ?? $user->email)
                    . ' thành công.'
            );

            /*
             * Thưởng cho người nhập mã giới thiệu.
             */
            $thanhVien->congDiemKhongXetHang(
                self::DIEM_NGUOI_DUOC_GIOI_THIEU,
                'Thưởng ' . self::DIEM_NGUOI_DUOC_GIOI_THIEU
                    . ' điểm khi nhập mã giới thiệu '
                    . $maGioiThieu
                    . '.'
            );

            $daLienKetGioiThieu = true;
        });

        $redirect = Redirect::route('profile.edit')
            ->with('status', 'profile-updated');

        if ($daLienKetGioiThieu) {
            $redirect->with(
                'referral-success',
                'Liên kết mã giới thiệu thành công. Bạn đã nhận '
                    . self::DIEM_NGUOI_DUOC_GIOI_THIEU
                    . ' điểm thưởng.'
            );
        }

        return $redirect;
    }

    /**
     * Xóa tài khoản thành viên.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validateWithBag('userDeletion', [
            'mat_khau' => ['required'],
        ], [
            'mat_khau.required' => 'Vui lòng nhập mật khẩu để xác nhận quyền chủ sở hữu.',
        ]);

        if (!Hash::check($request->mat_khau, $user->password)) {
            return back()->withErrors([
                'mat_khau' => 'Mật khẩu xác nhận danh tính không chính xác.',
            ], 'userDeletion');
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
