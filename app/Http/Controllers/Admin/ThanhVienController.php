<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThanhVien;
use App\Models\ThongBaoCaNhan;
use App\Services\AdminNotificationService;
use Illuminate\Http\Request;

class ThanhVienController extends Controller
{
    /**
     * Hiển thị danh sách thẻ thành viên trong Admin.
     * Có tìm kiếm, lọc hạng và thống kê nhanh.
     */
    public function index(Request $request)
    {
        $query = ThanhVien::with('nguoiDung')
            ->withCount('lichSuDiems');

        // Tìm kiếm theo mã thành viên, tên khách, email hoặc số điện thoại
        if ($request->filled('tim_kiem')) {
            $keyword = $request->tim_kiem;

            $query->where(function ($q) use ($keyword) {
                $q->where(
                    'ma_thanh_vien',
                    'like',
                    "%{$keyword}%"
                )->orWhereHas('nguoiDung', function ($userQuery) use ($keyword) {
                    $userQuery->where(
                        'ho_ten',
                        'like',
                        "%{$keyword}%"
                    )
                        ->orWhere(
                            'email',
                            'like',
                            "%{$keyword}%"
                        )
                        ->orWhere(
                            'so_dien_thoai',
                            'like',
                            "%{$keyword}%"
                        );
                });
            });
        }

        // Lọc theo hạng thành viên
        if ($request->filled('hang_thanh_vien')) {
            $query->where(
                'hang_thanh_vien',
                $request->hang_thanh_vien
            );
        }

        $thanhViens = $query
            ->orderByDesc('tong_diem_tich_luy')
            ->paginate(10)
            ->withQueryString();

        // Thống kê số lượng thành viên theo từng hạng
        $tongThanhVien = ThanhVien::count();

        $tongMember = ThanhVien::where(
            'hang_thanh_vien',
            'member'
        )->count();

        $tongSilver = ThanhVien::where(
            'hang_thanh_vien',
            'silver'
        )->count();

        $tongGold = ThanhVien::where(
            'hang_thanh_vien',
            'gold'
        )->count();

        $tongPlatinum = ThanhVien::where(
            'hang_thanh_vien',
            'platinum'
        )->count();

        return view(
            'admin.thanh_vien.index',
            compact(
                'thanhViens',
                'tongThanhVien',
                'tongMember',
                'tongSilver',
                'tongGold',
                'tongPlatinum'
            )
        );
    }


    /**
     * Hiển thị chi tiết một thẻ thành viên.
     */
    public function show(ThanhVien $thanhVien)
    {
        $thanhVien->load([
            'nguoiDung.veXemPhims',
            'nguoiDung.vouchersCaNhan.voucher',
            'lichSuDiems.veXemPhim',
        ]);

        $nguoiDung = $thanhVien->nguoiDung;

        // Tổng số vé khách đã mua
        $tongVe = $nguoiDung?->veXemPhims()->count() ?? 0;

        // Tổng chi tiêu chỉ tính vé chưa bị hủy
        $tongChiTieu = $nguoiDung?->veXemPhims()
            ->where('trang_thai', '!=', 'da_huy')
            ->sum('tong_tien') ?? 0;

        // Lịch sử điểm mới nhất
        $lichSuDiems = $thanhVien->lichSuDiems()
            ->with('veXemPhim')
            ->latest()
            ->paginate(10);

        // Danh sách voucher khách hàng đang sở hữu
        $vouchers = $nguoiDung?->vouchersCaNhan()
            ->with('voucher')
            ->latest()
            ->get() ?? collect();

        return view(
            'admin.thanh_vien.show',
            compact(
                'thanhVien',
                'nguoiDung',
                'tongVe',
                'tongChiTieu',
                'lichSuDiems',
                'vouchers'
            )
        );
    }


    /**
     * Admin tặng điểm thủ công cho một thành viên.
     */
    public function tangDiem(
        Request $request,
        ThanhVien $thanhVien
    ) {
        $data = $request->validate([
            'loai' => [
                'required',
                'in:tang,thu_hoi'
            ],

            'so_diem' => [
                'required',
                'integer',
                'min:1',
                'max:10000'
            ],

            'noi_dung' => [
                'required',
                'string',
                'max:255'
            ],

            'tinh_vao_hang' => [
                'nullable',
                'boolean'
            ],

            'hang_thanh_vien' => [
                'nullable',
                'in:member,silver,gold,platinum'
            ],
        ], [
            'so_diem.required' =>
            'Vui lòng nhập số điểm.',

            'so_diem.integer' =>
            'Số điểm phải là số nguyên.',

            'so_diem.min' =>
            'Số điểm phải lớn hơn hoặc bằng 1.',

            'so_diem.max' =>
            'Số điểm không được vượt quá 10.000.',

            'noi_dung.required' =>
            'Vui lòng nhập nội dung.',

            'noi_dung.max' =>
            'Nội dung không được vượt quá 255 ký tự.',

            'hang_thanh_vien.in' =>
            'Hạng thành viên không hợp lệ.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | TẶNG ĐIỂM CÓ XÉT HẠNG
        |--------------------------------------------------------------------------
        */

        if ((int) ($data['tinh_vao_hang'] ?? 0) === 1) {
            $thanhVien->congDiem(
                $data['so_diem'],
                null,
                'Admin tặng điểm có xét hạng: '
                    . $data['noi_dung']
            );
        } else {
            /*
            |--------------------------------------------------------------------------
            | TẶNG ĐIỂM KHÔNG XÉT HẠNG
            |--------------------------------------------------------------------------
            */

            $thanhVien->congDiemKhongXetHang(
                $data['so_diem'],
                'Admin tặng điểm không xét hạng: '
                    . $data['noi_dung']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | THÔNG BÁO CHO USER
        |--------------------------------------------------------------------------
        */

        if ($thanhVien->nguoiDung) {
            ThongBaoCaNhan::create([
                'nguoi_dung_id' => $thanhVien->nguoiDung->id,

                'tieu_de' => '🎁 Bạn được tặng điểm',

                'noi_dung' =>
                'Bạn vừa được Admin tặng '
                    . number_format($data['so_diem'])
                    . ' điểm. '
                    . $data['noi_dung'],

                'loai_thong_bao' => 'diem',

                'duong_dan' => route(
                    'user.thanh-vien.index'
                ),

                'da_doc' => false,

                'doc_luc' => null,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | THÔNG BÁO CHO ADMIN
        |--------------------------------------------------------------------------
        */

        AdminNotificationService::push(
            '🎁 Admin đã tặng điểm',

            'Admin vừa tặng '
                . number_format($data['so_diem'])
                . ' điểm cho thành viên '
                . ($thanhVien->nguoiDung->ho_ten ?? 'không xác định')
                . '. Nội dung: '
                . $data['noi_dung'],

            'Success'
        );

        return back()->with(
            'success',
            'Đã tặng điểm cho thành viên thành công.'
        );
    }


    /**
     * Admin thu hồi điểm thủ công cho một thành viên.
     */
    public function truDiem(
        Request $request,
        ThanhVien $thanhVien
    ) {
        $data = $request->validate([
            'so_diem' => [
                'required',
                'integer',
                'min:1',
                'max:10000'
            ],

            'noi_dung' => [
                'required',
                'string',
                'max:255'
            ],
        ], [
            'so_diem.required' =>
            'Vui lòng nhập số điểm.',

            'so_diem.integer' =>
            'Số điểm phải là số nguyên.',

            'so_diem.min' =>
            'Số điểm phải lớn hơn hoặc bằng 1.',

            'so_diem.max' =>
            'Số điểm không được vượt quá 10.000.',

            'noi_dung.required' =>
            'Vui lòng nhập nội dung.',

            'noi_dung.max' =>
            'Nội dung không được vượt quá 255 ký tự.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | KHÔNG CHO THU HỒI QUÁ SỐ ĐIỂM HIỆN TẠI
        |--------------------------------------------------------------------------
        */

        $soDiemThuHoi = min(
            $data['so_diem'],
            (int) $thanhVien->diem_hien_tai
        );

        if ($soDiemThuHoi <= 0) {
            return back()->with(
                'error',
                'Thành viên hiện không có điểm để thu hồi.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | THU HỒI ĐIỂM
        |--------------------------------------------------------------------------
        */

        $thanhVien->thuHoiDiem(
            $soDiemThuHoi,
            'Admin thu hồi điểm: '
                . $data['noi_dung']
        );

        /*
        |--------------------------------------------------------------------------
        | THÔNG BÁO CHO USER
        |--------------------------------------------------------------------------
        */

        if ($thanhVien->nguoiDung) {
            ThongBaoCaNhan::create([
                'nguoi_dung_id' => $thanhVien->nguoiDung->id,

                'tieu_de' =>
                '🔄 Điểm thành viên được cập nhật',

                'noi_dung' =>
                'Admin đã thu hồi '
                    . number_format($soDiemThuHoi)
                    . ' điểm từ tài khoản của bạn. '
                    . $data['noi_dung'],

                'loai_thong_bao' => 'diem',

                'duong_dan' => route(
                    'user.thanh-vien.index'
                ),

                'da_doc' => false,

                'doc_luc' => null,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | THÔNG BÁO CHO ADMIN
        |--------------------------------------------------------------------------
        */

        AdminNotificationService::push(
            '🔄 Admin đã thu hồi điểm',

            'Admin vừa thu hồi '
                . number_format($soDiemThuHoi)
                . ' điểm của thành viên '
                . ($thanhVien->nguoiDung->ho_ten ?? 'không xác định')
                . '. Nội dung: '
                . $data['noi_dung'],

            'Warning'
        );

        return back()->with(
            'success',
            'Đã thu hồi điểm thành viên thành công.'
        );
    }


    /**
     * Trang tặng / thu hồi điểm cho thành viên theo hạng.
     */
    public function diemTatCa()
    {
        $soLuongThanhVien = ThanhVien::count();

        return view(
            'admin.thanh_vien.diem-tat-ca',
            compact('soLuongThanhVien')
        );
    }


    /**
     * Tặng hoặc thu hồi điểm cho toàn bộ thành viên
     * thuộc một hạng cụ thể.
     */
    public function xuLyDiemHangLoat(Request $request)
    {
        $rules = [
            'loai' => [
                'required',
                'in:tang,thu_hoi'
            ],
            'hang_thanh_vien' => [
                'nullable',
                'in:member,silver,gold,platinum'
            ],

            'so_diem' => [
                'required',
                'integer',
                'min:1',
                'max:10000'
            ],

            'noi_dung' => [
                'required',
                'string',
                'max:255'
            ],
        ];

        // Chỉ form TẶNG mới cần trường này
        if ($request->input('loai') === 'tang') {
            $rules['tinh_vao_hang'] = [
                'required',
                'boolean'
            ];
        }

        $data = $request->validate($rules, [
            'so_diem.required' =>
            'Vui lòng nhập số điểm.',

            'so_diem.integer' =>
            'Số điểm phải là số nguyên.',

            'so_diem.min' =>
            'Số điểm phải lớn hơn hoặc bằng 1.',

            'so_diem.max' =>
            'Số điểm không được vượt quá 10.000.',

            'noi_dung.required' =>
            'Vui lòng nhập nội dung.',

            'noi_dung.max' =>
            'Nội dung không được vượt quá 255 ký tự.',

            'hang_thanh_vien.in' =>
            'Hạng thành viên không hợp lệ.',

            'tinh_vao_hang.required' =>
            'Vui lòng chọn cách tính hạng.',
        ]);

        // Kiểm tra hạng được chọn có thành viên hay không
        if (!empty($data['hang_thanh_vien'])) {

            $soThanhVienTheoHang = ThanhVien::where(
                'hang_thanh_vien',
                $data['hang_thanh_vien']
            )->count();

            if ($soThanhVienTheoHang === 0) {

                $tenHang = match ($data['hang_thanh_vien']) {
                    'member' => 'Thành viên',
                    'silver' => 'Bạc',
                    'gold' => 'Vàng',
                    'platinum' => 'Bạch kim',
                    default => $data['hang_thanh_vien'],
                };

                return back()
                    ->withInput()
                    ->with(
                        'error',
                        "Hiện không có thành viên nào thuộc hạng {$tenHang}."
                    );
            }
        }

        /*
    |--------------------------------------------------------------------------
    | LẤY THÀNH VIÊN
    |--------------------------------------------------------------------------
    */

        $query = ThanhVien::with('nguoiDung');

        // Nếu có chọn hạng thì chỉ xử lý hạng đó.
        // Không chọn hạng = xử lý tất cả.
        if (!empty($data['hang_thanh_vien'])) {
            $query->where(
                'hang_thanh_vien',
                $data['hang_thanh_vien']
            );
        }

        $thanhViens = $query->get();

        $soLuong = 0;

        /*
    |--------------------------------------------------------------------------
    | XỬ LÝ TỪNG THÀNH VIÊN
    |--------------------------------------------------------------------------
    */

        foreach ($thanhViens as $thanhVien) {

            /*
        |--------------------------------------------------------------------------
        | TẶNG ĐIỂM
        |--------------------------------------------------------------------------
        */

            if ($data['loai'] === 'tang') {

                if ((int) ($data['tinh_vao_hang'] ?? 0) === 1) {

                    $thanhVien->congDiem(
                        $data['so_diem'],
                        null,
                        'Admin tặng điểm cho bạn : '
                            . $data['noi_dung']
                    );
                } else {

                    $thanhVien->congDiemKhongXetHang(
                        $data['so_diem'],
                        'Admin tặng điểm  không xét hạng: '
                            . $data['noi_dung']
                    );
                }

                /*
            |--------------------------------------------------------------------------
            | THÔNG BÁO CHO USER
            |--------------------------------------------------------------------------
            */

                if ($thanhVien->nguoiDung) {

                    $hangText = !empty($data['hang_thanh_vien'])
                        ? ucfirst($data['hang_thanh_vien'])
                        : 'tất cả hạng';

                    ThongBaoCaNhan::create([
                        'nguoi_dung_id' =>
                        $thanhVien->nguoiDung->id,

                        'tieu_de' =>
                        '🎁 Bạn được tặng điểm',

                        'noi_dung' =>
                        'Bạn vừa được Admin tặng '
                            . number_format($data['so_diem'])
                            . ' điểm dành cho '
                            . $hangText
                            . '. '
                            . $data['noi_dung'],

                        'loai_thong_bao' =>
                        'diem',

                        'duong_dan' =>
                        route('user.thanh-vien.index'),

                        'da_doc' => false,

                        'doc_luc' => null,
                    ]);
                }
            }

            /*
        |--------------------------------------------------------------------------
        | THU HỒI ĐIỂM
        |--------------------------------------------------------------------------
        */ else {

                // Không cho thu hồi quá số điểm hiện tại
                $soDiemThuHoi = min(
                    $data['so_diem'],
                    (int) $thanhVien->diem_hien_tai
                );

                if ($soDiemThuHoi > 0) {

                    $thanhVien->thuHoiDiem(
                        $soDiemThuHoi,
                        'Admin thu hồi điểm của bạn : '
                            . $data['noi_dung']
                    );

                    /*
                |--------------------------------------------------------------------------
                | THÔNG BÁO CHO USER
                |--------------------------------------------------------------------------
                */

                    if ($thanhVien->nguoiDung) {

                        ThongBaoCaNhan::create([
                            'nguoi_dung_id' =>
                            $thanhVien->nguoiDung->id,

                            'tieu_de' =>
                            '🔄 Điểm thành viên được cập nhật',

                            'noi_dung' =>
                            'Admin đã thu hồi '
                                . number_format($soDiemThuHoi)
                                . ' điểm từ tài khoản của bạn. '
                                . $data['noi_dung'],

                            'loai_thong_bao' =>
                            'diem',

                            'duong_dan' =>
                            route('user.thanh-vien.index'),

                            'da_doc' => false,

                            'doc_luc' => null,
                        ]);
                    }
                }
            }

            $soLuong++;
        }

        /*
    |--------------------------------------------------------------------------
    | THÔNG BÁO CHO ADMIN
    |--------------------------------------------------------------------------
    */

        if ($data['loai'] === 'tang') {

            AdminNotificationService::push(
                '🎁 Admin đã tặng điểm hàng loạt',

                'Admin vừa tặng '
                    . number_format($data['so_diem'])
                    . ' điểm cho '
                    . $soLuong
                    . ' thành viên. Nội dung: '
                    . $data['noi_dung'],

                'Success'
            );
        } else {

            AdminNotificationService::push(
                '🔄 Admin đã thu hồi điểm hàng loạt',

                'Admin vừa thu hồi điểm của '
                    . $soLuong
                    . ' thành viên. Nội dung: '
                    . $data['noi_dung'],

                'Warning'
            );
        }

        /*
    |--------------------------------------------------------------------------
    | THÔNG BÁO THÀNH CÔNG
    |--------------------------------------------------------------------------
    */

        $message = $data['loai'] === 'tang'

            ? "Đã tặng {$data['so_diem']} điểm cho {$soLuong} thành viên."

            : "Đã thu hồi điểm của {$soLuong} thành viên.";

        return redirect()
            ->route('admin.thanh-vien.diem-tat-ca')
            ->with('success', $message);
    }
}
