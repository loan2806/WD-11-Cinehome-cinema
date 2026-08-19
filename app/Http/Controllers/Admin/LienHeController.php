<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\LienHePhanHoiMail;
use App\Mail\VoucherUuDaiMail;
use App\Models\LienHe;
use App\Models\NguoiDungVoucher;
use App\Models\Voucher;
use App\Traits\Loggable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\ThongBaoCaNhan;

class LienHeController extends Controller
{
    use Loggable;

    /**
     * Chỉ những liên hệ thuộc các chủ đề lỗi thực sự mới được tặng voucher.
     * "Góp ý" là phản hồi tích cực/đề xuất, không phải sự cố nên không tặng.
     */
    private const CHU_DE_DUOC_TANG_VOUCHER = ['Lỗi đặt vé', 'Lỗi thanh toán', 'Lỗi tài khoản', 'Khác'];

    public function index(Request $request)
    {
        if (! \coQuyen('lien_he.xem')) {
            return redirect()->route('admin.dashboard')->with('error', 'Tài khoản của bạn không có quyền xem Danh sách liên hệ!');
        }

        $query = LienHe::with('nguoiDung')->latest();

        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->string('trang_thai'));
        }

        if ($request->filled('tim_kiem')) {
            $tuKhoa = $request->string('tim_kiem');
            $query->where(function ($q) use ($tuKhoa) {
                $q->where('ho_ten', 'like', "%{$tuKhoa}%")
                    ->orWhere('email', 'like', "%{$tuKhoa}%")
                    ->orWhere('so_dien_thoai', 'like', "%{$tuKhoa}%");
            });
        }

        $lienHes = $query->paginate(10)->withQueryString();

        $thongKe = [
            'tong' => LienHe::count(),
            'cho_xu_ly' => LienHe::where('trang_thai', 'cho_xu_ly')->count(),
            'dang_xu_ly' => LienHe::where('trang_thai', 'dang_xu_ly')->count(),
            'da_xu_ly' => LienHe::where('trang_thai', 'da_xu_ly')->count(),
        ];

        $soChoXuLy = $thongKe['cho_xu_ly'];

        return view('admin.lien-he.index', compact('lienHes', 'soChoXuLy', 'thongKe'));
    }

    public function show(LienHe $lienHe)
    {
        // 🌟 Kiểm tra quyền xem chi tiết liên hệ
        if (!\coQuyen('lien_he.xem')) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Tài khoản của bạn không có quyền xem Chi tiết liên hệ!');
        }

        // Nếu đang chờ xử lý thì tự động chuyển sang đang xử lý
        if ($lienHe->trang_thai === 'cho_xu_ly') {
            $lienHe->update([
                'trang_thai' => 'dang_xu_ly',
            ]);
        }

        $lienHe->load([
            'nguoiDung',
            'nguoiXuLy',
            'voucher',
        ]);

        $activeVouchers = Voucher::where('trang_thai', true)
            ->whereDate('ngay_het_han', '>=', today())
            ->orderBy('ten_voucher')
            ->get();

        $duocTangVoucher = in_array(
            $lienHe->chu_de,
            self::CHU_DE_DUOC_TANG_VOUCHER,
            true
        );

        return view(
            'admin.lien-he.show',
            compact('lienHe', 'activeVouchers', 'duocTangVoucher')
        );
    }
    public function update(Request $request, LienHe $lienHe)
    {
        if (!\coQuyen('lien_he.cap_nhat')) {
            return back()->with(
                'error',
                'Tài khoản của bạn không có quyền cập nhật liên hệ!'
            );
        }

        // Đã xử lý thì không cho cập nhật lại
        if ($lienHe->trang_thai === 'da_xu_ly') {
            return back()->with(
                'error',
                'Liên hệ này đã được xử lý, không thể cập nhật lại.'
            );
        }

        $data = $request->validate([
            'phan_hoi' => ['nullable', 'string', 'max:2000'],
            'voucher_id' => ['nullable', 'exists:vouchers,id'],
        ]);

        $phanHoiCu = $lienHe->phan_hoi;

        /*
    |--------------------------------------------------------------------------
    | Tự động xác định trạng thái
    |--------------------------------------------------------------------------
    */

        if (!empty($data['phan_hoi']) || !empty($data['voucher_id'])) {
            $data['trang_thai'] = 'da_xu_ly';
            $data['thoi_gian_xu_ly'] = now();
        } else {
            $data['trang_thai'] = 'dang_xu_ly';
            $data['thoi_gian_xu_ly'] = null;
        }
        $data['nguoi_xu_ly_id'] = auth()->id();

        /*
    |--------------------------------------------------------------------------
    | Voucher
    |--------------------------------------------------------------------------
    */

        $voucher = null;
        $nguoiDungVoucher = null;

        if (!empty($data['voucher_id'])) {

            // Không cho tặng nếu liên hệ không đủ điều kiện
            if (!in_array(
                $lienHe->chu_de,
                self::CHU_DE_DUOC_TANG_VOUCHER,
                true
            )) {
                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'Chủ đề liên hệ này không được phép tặng voucher.'
                    );
            }

            // Phải có tài khoản
            if (!$lienHe->nguoi_dung_id) {
                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'Khách hàng chưa đăng nhập tài khoản nên không thể tặng voucher.'
                    );
            }

            // Không cho tặng lần 2
            if ($lienHe->voucher_id) {
                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'Liên hệ này đã được tặng voucher trước đó.'
                    );
            }

            $voucher = Voucher::findOrFail($data['voucher_id']);

            if (
                !$voucher->trang_thai ||
                $voucher->ngay_het_han->lt(today())
            ) {
                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'Voucher này đang tắt hoặc đã hết hạn.'
                    );
            }
        }

        /*
    |--------------------------------------------------------------------------
    | Lưu liên hệ + tặng voucher trong cùng transaction
    |--------------------------------------------------------------------------
    */

        DB::transaction(function () use (
            $lienHe,
            $data,
            $voucher,
            &$nguoiDungVoucher
        ) {

            // Không lưu voucher_id vào update nếu chưa thực sự tặng
            unset($data['voucher_id']);

            $lienHe->update($data);

            if ($voucher) {

                $nguoiDungVoucher = NguoiDungVoucher::create([
                    'nguoi_dung_id' => $lienHe->nguoi_dung_id,
                    'voucher_id' => $voucher->id,
                    'ma_voucher_ca_nhan' => $this->maVoucherCaNhan($voucher),
                    'loai_cap_phat' => 'admin_tang',
                    'nam_ap_dung' => now()->year,
                    'da_su_dung' => false,
                    'ngay_nhan' => now(),
                    'ngay_het_han' => $voucher->ngay_het_han->copy()->endOfDay(),
                ]);

                $lienHe->update([
                    'voucher_id' => $voucher->id,
                    'thoi_gian_tang_voucher' => now(),
                ]);
            }
        });

        /*
    |--------------------------------------------------------------------------
    | Gửi email phản hồi
    |--------------------------------------------------------------------------
    */

        $daGuiEmail = false;

        if ($voucher && $nguoiDungVoucher) {

            Mail::to($lienHe->email)
                ->send(
                    new VoucherUuDaiMail(
                        $lienHe,
                        $nguoiDungVoucher,
                        $lienHe->chu_de
                    )
                );

            $daGuiEmail = true;
        } elseif (!empty($data['phan_hoi']) && $data['phan_hoi'] !== $phanHoiCu) {

            Mail::to($lienHe->email)
                ->send(
                    new LienHePhanHoiMail($lienHe)
                );

            $daGuiEmail = true;
        }

        /*
    |--------------------------------------------------------------------------
    | Nếu có voucher → thông báo + email voucher
    |--------------------------------------------------------------------------
    */

        if ($voucher && $nguoiDungVoucher) {

            ThongBaoCaNhan::create([
                'nguoi_dung_id' => $lienHe->nguoi_dung_id,
                'tieu_de' => 'Bạn được tặng voucher',
                'noi_dung' => "Hệ thống đã tặng bạn voucher "
                    . "\"{$voucher->ten_voucher}\". "
                    . "Mã voucher: {$nguoiDungVoucher->ma_voucher_ca_nhan}. "
                    . "Hạn sử dụng đến "
                    . $nguoiDungVoucher->ngay_het_han->format('d/m/Y') . ".",
                'loai' => 'voucher',
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | Ghi nhật ký
    |--------------------------------------------------------------------------
    */

        $moTa = "Cập nhật liên hệ #{$lienHe->id} sang {$data['trang_thai']}";

        if ($voucher && $nguoiDungVoucher) {
            $moTa .= " và tặng voucher {$nguoiDungVoucher->ma_voucher_ca_nhan}";
        }

        $this->ghiNhatKy(
            $request,
            'Cập nhật liên hệ khách hàng',
            'Quản lý liên hệ',
            $moTa
        );

        /*
    |--------------------------------------------------------------------------
    | Thông báo kết quả
    |--------------------------------------------------------------------------
    */

        if ($voucher && $nguoiDungVoucher) {

            return back()->with(
                'success',
                "Đã cập nhật liên hệ, tặng voucher {$nguoiDungVoucher->ma_voucher_ca_nhan} và gửi thông báo cho khách hàng."
            );
        }

        return back()->with(
            'success',
            $daGuiEmail
                ? 'Đã cập nhật liên hệ và gửi email phản hồi tới khách hàng.'
                : 'Đã cập nhật liên hệ.'
        );
    }

    private function maVoucherCaNhan(Voucher $voucher): string
    {
        do {
            $code = Str::upper($voucher->ma_voucher . '-' . Str::random(8));
        } while (NguoiDungVoucher::where('ma_voucher_ca_nhan', $code)->exists());

        return $code;
    }

    public function destroy(Request $request, LienHe $lienHe)
    {
        if (! \coQuyen('lien_he.xoa')) {
            return back()->with('error', 'Tài khoản của bạn không có quyền xóa liên hệ!');
        }

        $id = $lienHe->id;
        $lienHe->delete();

        $this->ghiNhatKy(
            $request,
            'Xóa liên hệ khách hàng',
            'Quản lý liên hệ',
            "Xóa liên hệ #{$id}"
        );

        return redirect()
            ->route('admin.lien-he.index')
            ->with('success', 'Đã xóa liên hệ.');
    }
}
