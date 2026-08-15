<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThanhVien;
use App\Models\ThongBaoCaNhan;
use App\Services\AdminNotificationService;
use Illuminate\Http\Request;
use App\Mail\DiemThanhVienMail;
use Illuminate\Support\Facades\Mail;

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
        foreach ($thanhViens as $thanhVien) {
            $thanhVien->xuLyDiemHetHan();
        }

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
        $thanhVien->xuLyDiemHetHan();
        
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

            'form' => [
                'required',
                'in:tang'
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
    | LẤY NGƯỜI DÙNG
    |--------------------------------------------------------------------------
    */

        $nguoiDung = $thanhVien->nguoiDung;


        /*
    |--------------------------------------------------------------------------
    | KIỂM TRA NGƯỜI DÙNG
    |--------------------------------------------------------------------------
    */

        if (!$nguoiDung) {

            return back()->with(
                'error',
                'Không tìm thấy tài khoản người dùng của thành viên.'
            );
        }


        /*
    |--------------------------------------------------------------------------
    | KIỂM TRA EMAIL ĐÃ XÁC THỰC
    |--------------------------------------------------------------------------
    |
    | Tài khoản chưa xác thực email:
    | - Không được cộng điểm
    | - Không gửi email
    | - Không tạo thông báo điểm
    |
    | Sau này xác thực cũng không được nhận lại điểm
    | của lần Admin tặng trước đó.
    |--------------------------------------------------------------------------
    */

        if (is_null($nguoiDung->email_verified_at)) {

            return back()->with(
                'error',
                'Tài khoản này chưa xác thực email nên không thể tặng điểm.'
            );
        }


        /*
    |--------------------------------------------------------------------------
    | TẶNG ĐIỂM
    |--------------------------------------------------------------------------
    */

        if ((int) ($data['tinh_vao_hang'] ?? 0) === 1) {

            $thanhVien->congDiem(
                (int) $data['so_diem'],
                null,
                'Admin tặng điểm có xét hạng: '
                    . $data['noi_dung']
            );
        } else {

            $thanhVien->congDiemKhongXetHang(
                (int) $data['so_diem'],
                'Admin tặng điểm không xét hạng: '
                    . $data['noi_dung']
            );
        }


        /*
    |--------------------------------------------------------------------------
    | REFRESH ĐIỂM
    |--------------------------------------------------------------------------
    */

        $thanhVien->refresh();


        /*
    |--------------------------------------------------------------------------
    | GỬI EMAIL
    |--------------------------------------------------------------------------
    |
    | Đến đây chắc chắn tài khoản đã xác thực email.
    |--------------------------------------------------------------------------
    */

        if (!empty($nguoiDung->email)) {

            try {

                Mail::to($nguoiDung->email)->send(
                    new DiemThanhVienMail(
                        'tang',
                        (int) $data['so_diem'],
                        $data['noi_dung'],
                        (int) $thanhVien->diem_hien_tai,
                        $nguoiDung->ho_ten ?? 'Quý khách'
                    )
                );
            } catch (\Throwable $e) {

                \Log::error(
                    'Không thể gửi email tặng điểm.',
                    [
                        'thanh_vien_id' => $thanhVien->id,
                        'nguoi_dung_id' => $nguoiDung->id,
                        'email' => $nguoiDung->email,
                        'so_diem' => $data['so_diem'],
                        'error' => $e->getMessage(),
                    ]
                );
            }
        }


        /*
    |--------------------------------------------------------------------------
    | THÔNG BÁO CHO USER
    |--------------------------------------------------------------------------
    */

        ThongBaoCaNhan::create([

            'nguoi_dung_id' =>
            $nguoiDung->id,

            'tieu_de' =>
            '🎁 Bạn được tặng điểm',

            'noi_dung' =>
            'Bạn vừa được Admin tặng '
                . number_format($data['so_diem'])
                . ' điểm. '
                . $data['noi_dung'],

            'loai_thong_bao' =>
            'diem',

            'duong_dan' =>
            route('user.thanh-vien.index'),

            'da_doc' =>
            false,

            'doc_luc' =>
            null,
        ]);


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
                . ($nguoiDung->ho_ten ?? 'không xác định')
                . '. Nội dung: '
                . $data['noi_dung'],

            'Success'
        );


        /*
    |--------------------------------------------------------------------------
    | KẾT QUẢ
    |--------------------------------------------------------------------------
    */

        return back()->with(
            'success',
            'Đã tặng '
                . number_format($data['so_diem'])
                . ' điểm cho thành viên thành công.'
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

            'form' => [
                'required',
                'in:thu_hoi'
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

        if ($data['so_diem'] > $thanhVien->diem_hien_tai) {

            return back()->with(
                'error',
                'Số điểm thu hồi không được lớn hơn số điểm hiện tại của thành viên.'
            );
        }


        $soDiemThuHoi = $data['so_diem'];

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


        $nguoiDung = $thanhVien->nguoiDung;


        /*
        |--------------------------------------------------------------------------
        | GỬI EMAIL - CHỈ TÀI KHOẢN ĐÃ XÁC THỰC
        |--------------------------------------------------------------------------
        */

        if (
            $nguoiDung &&
            !empty($nguoiDung->email) &&
            !is_null($nguoiDung->email_verified_at)
        ) {

            Mail::to($nguoiDung->email)
                ->send(
                    new DiemThanhVienMail(
                        'thu_hoi',
                        $soDiemThuHoi,
                        $data['noi_dung'],
                        $thanhVien->diem_hien_tai
                    )
                );
        }


        /*
        |--------------------------------------------------------------------------
        | THÔNG BÁO CHO USER
        |--------------------------------------------------------------------------
        */

        if ($nguoiDung) {

            ThongBaoCaNhan::create([

                'nguoi_dung_id' =>
                $nguoiDung->id,

                'tieu_de' =>
                '🔄 Điểm thành viên được cập nhật',

                'noi_dung' =>
                'Admin đã thu hồi '
                    . number_format($soDiemThuHoi)
                    . ' điểm từ tài khoản của bạn. ',

                'loai_thong_bao' =>
                'diem',

                'duong_dan' =>
                route('user.thanh-vien.index'),

                'da_doc' =>
                false,

                'doc_luc' =>
                null,
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
                . ($nguoiDung->ho_ten ?? 'không xác định')
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
        /*
        |--------------------------------------------------------------------------
        | VALIDATE
        |--------------------------------------------------------------------------
        */

        $rules = [

            'loai' => [
                'required',
                'in:tang,thu_hoi',
            ],

            'hang_thanh_vien' => [
                'nullable',
                'in:member,silver,gold,platinum',
            ],

            'so_diem' => [
                'required',
                'integer',
                'min:1',
                'max:10000',
            ],

            'noi_dung' => [
                'required',
                'string',
                'max:255',
            ],
        ];


        // Chỉ tặng điểm mới cần tinh_vao_hang
        if ($request->input('loai') === 'tang') {

            $rules['tinh_vao_hang'] = [
                'required',
                'boolean',
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


        /*
        |--------------------------------------------------------------------------
        | KIỂM TRA HẠNG
        |--------------------------------------------------------------------------
        */

        if (!empty($data['hang_thanh_vien'])) {

            $soThanhVienTheoHang = ThanhVien::where(
                'hang_thanh_vien',
                $data['hang_thanh_vien']
            )->count();

            if ($soThanhVienTheoHang === 0) {

                $tenHang = match ($data['hang_thanh_vien']) {

                    'member' =>
                    'Thành viên',

                    'silver' =>
                    'Bạc',

                    'gold' =>
                    'Vàng',

                    'platinum' =>
                    'Bạch kim',

                    default =>
                    $data['hang_thanh_vien'],
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
        | LẤY DANH SÁCH THÀNH VIÊN
        |--------------------------------------------------------------------------
        */

        $query = ThanhVien::with('nguoiDung');

        if (!empty($data['hang_thanh_vien'])) {

            $query->where(
                'hang_thanh_vien',
                $data['hang_thanh_vien']
            );
        }

        $thanhViens = $query->get();


        /*
        |--------------------------------------------------------------------------
        | BIẾN THỐNG KÊ
        |--------------------------------------------------------------------------
        */

        $soLuong = 0;
        $soEmailThanhCong = 0;
        $soEmailLoi = 0;
        $soLuongBoQua = 0;


        /*
        |--------------------------------------------------------------------------
        | TÊN HẠNG
        |--------------------------------------------------------------------------
        */

        $hangText = match ($data['hang_thanh_vien'] ?? null) {

            'member' =>
            'Thành viên',

            'silver' =>
            'Bạc',

            'gold' =>
            'Vàng',

            'platinum' =>
            'Bạch kim',

            default =>
            'tất cả hạng',
        };


        /*
        |--------------------------------------------------------------------------
        | XỬ LÝ TỪNG THÀNH VIÊN
        |--------------------------------------------------------------------------
        */

        foreach ($thanhViens as $thanhVien) {

            /*
            |--------------------------------------------------------------------------
            | PHẢI CÓ NGƯỜI DÙNG
            |--------------------------------------------------------------------------
            */

            $nguoiDung = $thanhVien->nguoiDung;

            if (!$nguoiDung) {

                \Log::warning(
                    'Bỏ qua thành viên vì không có người dùng liên kết.',
                    [
                        'thanh_vien_id' =>
                        $thanhVien->id,
                    ]
                );

                $soLuongBoQua++;

                continue;
            }
            // Chỉ tặng/thu hồi điểm cho tài khoản đã xác thực email
            if (is_null($nguoiDung->email_verified_at)) {
                $soLuongBoQua++;

                \Log::info(
                    'Bỏ qua thành viên chưa xác thực email khi tặng/thu hồi điểm hàng loạt.',
                    [
                        'thanh_vien_id' => $thanhVien->id,
                        'nguoi_dung_id' => $nguoiDung->id,
                        'email' => $nguoiDung->email,
                    ]
                );

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | LẤY EMAIL CỦA NGƯỜI NÀY
            |--------------------------------------------------------------------------
            */

            $email = trim(
                (string) $nguoiDung->email
            );

            $hoTen = $nguoiDung->ho_ten
                ?: 'Quý khách';


            /*
            |--------------------------------------------------------------------------
            | KIỂM TRA EMAIL ĐÃ XÁC THỰC
            |--------------------------------------------------------------------------
            */

            $emailDaXacThuc =
                !is_null(
                    $nguoiDung->email_verified_at
                );


            /*
            |--------------------------------------------------------------------------
            | TẶNG ĐIỂM
            |--------------------------------------------------------------------------
            */

            if ($data['loai'] === 'tang') {

                /*
                |------------------------------------------------------------------
                | TẶNG CÓ XÉT HẠNG
                |------------------------------------------------------------------
                */

                if (
                    (int) ($data['tinh_vao_hang'] ?? 0) === 1
                ) {

                    $thanhVien->congDiem(

                        (int) $data['so_diem'],

                        null,

                        'Admin tặng điểm cho bạn: '
                            . $data['noi_dung']
                    );
                } else {

                    /*
                    |------------------------------------------------------------------
                    | TẶNG KHÔNG XÉT HẠNG
                    |------------------------------------------------------------------
                    */

                    $thanhVien->congDiemKhongXetHang(

                        (int) $data['so_diem'],

                        'Admin tặng điểm không xét hạng: '
                            . $data['noi_dung']
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | REFRESH ĐIỂM
                |--------------------------------------------------------------------------
                */

                $thanhVien->refresh();


                /*
                |--------------------------------------------------------------------------
                | THÔNG BÁO WEBSITE
                |--------------------------------------------------------------------------
                */

                ThongBaoCaNhan::create([

                    'nguoi_dung_id' =>
                    $nguoiDung->id,

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

                    'da_doc' =>
                    false,

                    'doc_luc' =>
                    null,
                ]);


                /*
                |--------------------------------------------------------------------------
                | GỬI EMAIL
                |
                | CHỈ GỬI KHI:
                | 1. Có email
                | 2. Email đã xác thực
                |--------------------------------------------------------------------------
                */

                if (
                    $email !== '' &&
                    $emailDaXacThuc
                ) {

                    try {

                        Mail::to($email)->send(

                            new DiemThanhVienMail(

                                'tang',

                                (int) $data['so_diem'],

                                $data['noi_dung'],

                                (int) $thanhVien->diem_hien_tai,

                                $hoTen
                            )
                        );


                        $soEmailThanhCong++;


                        \Log::info(

                            'Đã gửi email tặng điểm cho tài khoản đã xác thực.',

                            [
                                'thanh_vien_id' =>
                                $thanhVien->id,

                                'nguoi_dung_id' =>
                                $nguoiDung->id,

                                'email' =>
                                $email,

                                'so_diem' =>
                                $data['so_diem'],
                            ]
                        );
                    } catch (\Throwable $e) {

                        $soEmailLoi++;


                        \Log::error(

                            'Không thể gửi email tặng điểm.',

                            [
                                'thanh_vien_id' =>
                                $thanhVien->id,

                                'nguoi_dung_id' =>
                                $nguoiDung->id,

                                'email' =>
                                $email,

                                'error' =>
                                $e->getMessage(),
                            ]
                        );
                    }
                }


                $soLuong++;
            }


            /*
            |--------------------------------------------------------------------------
            | THU HỒI ĐIỂM
            |--------------------------------------------------------------------------
            */ else {

                /*
                |------------------------------------------------------------------
                | KHÔNG CHO THU HỒI QUÁ SỐ ĐIỂM HIỆN TẠI
                |------------------------------------------------------------------
                */

                if (
                    (int) $data['so_diem']
                    >
                    (int) $thanhVien->diem_hien_tai
                ) {

                    $soLuongBoQua++;


                    \Log::warning(

                        'Bỏ qua thành viên vì không đủ điểm để thu hồi.',

                        [
                            'thanh_vien_id' =>
                            $thanhVien->id,

                            'email' =>
                            $email,

                            'diem_hien_tai' =>
                            $thanhVien->diem_hien_tai,

                            'so_diem_thu_hoi' =>
                            $data['so_diem'],
                        ]
                    );

                    continue;
                }


                $soDiemThuHoi =
                    (int) $data['so_diem'];


                if ($soDiemThuHoi <= 0) {

                    $soLuongBoQua++;

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | THU HỒI ĐIỂM
                |--------------------------------------------------------------------------
                */

                $thanhVien->thuHoiDiem(

                    $soDiemThuHoi,

                    'Admin thu hồi điểm của bạn: '
                        . $data['noi_dung']
                );


                /*
                |--------------------------------------------------------------------------
                | REFRESH
                |--------------------------------------------------------------------------
                */

                $thanhVien->refresh();


                /*
                |--------------------------------------------------------------------------
                | THÔNG BÁO WEBSITE
                |--------------------------------------------------------------------------
                */

                ThongBaoCaNhan::create([

                    'nguoi_dung_id' =>
                    $nguoiDung->id,

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

                    'da_doc' =>
                    false,

                    'doc_luc' =>
                    null,
                ]);


                /*
                |--------------------------------------------------------------------------
                | GỬI EMAIL
                |
                | CHỈ GỬI KHI:
                | 1. Có email
                | 2. Email đã xác thực
                |--------------------------------------------------------------------------
                */

                if (
                    $email !== '' &&
                    $emailDaXacThuc
                ) {

                    try {

                        Mail::to($email)->send(

                            new DiemThanhVienMail(

                                'thu_hoi',

                                $soDiemThuHoi,

                                $data['noi_dung'],

                                (int) $thanhVien->diem_hien_tai,

                                $hoTen
                            )
                        );


                        $soEmailThanhCong++;


                        \Log::info(

                            'Đã gửi email thu hồi điểm cho tài khoản đã xác thực.',

                            [
                                'thanh_vien_id' =>
                                $thanhVien->id,

                                'nguoi_dung_id' =>
                                $nguoiDung->id,

                                'email' =>
                                $email,

                                'so_diem' =>
                                $soDiemThuHoi,
                            ]
                        );
                    } catch (\Throwable $e) {

                        $soEmailLoi++;


                        \Log::error(

                            'Không thể gửi email thu hồi điểm.',

                            [
                                'thanh_vien_id' =>
                                $thanhVien->id,

                                'nguoi_dung_id' =>
                                $nguoiDung->id,

                                'email' =>
                                $email,

                                'error' =>
                                $e->getMessage(),
                            ]
                        );
                    }
                }


                $soLuong++;
            }
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
                    . ' thành viên. '
                    . 'Email thành công: '
                    . $soEmailThanhCong
                    . '. Email lỗi: '
                    . $soEmailLoi
                    . '. Nội dung: '
                    . $data['noi_dung'],

                'Success'
            );
        } else {

            AdminNotificationService::push(

                '🔄 Admin đã thu hồi điểm hàng loạt',

                'Admin vừa thu hồi điểm của '
                    . $soLuong
                    . ' thành viên. '
                    . 'Email thành công: '
                    . $soEmailThanhCong
                    . '. Email lỗi: '
                    . $soEmailLoi
                    . '. Nội dung: '
                    . $data['noi_dung'],

                'Warning'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | THÔNG BÁO KẾT QUẢ
        |--------------------------------------------------------------------------
        */

        if ($data['loai'] === 'tang') {

            $message =
                "Đã tặng "
                . number_format($data['so_diem'])
                . " điểm cho "
                . $soLuong
                . " thành viên. "
                . "Đã gửi "
                . $soEmailThanhCong
                . " email.";
        } else {

            $message =
                "Đã thu hồi điểm của "
                . $soLuong
                . " thành viên. "
                . "Đã gửi "
                . $soEmailThanhCong
                . " email.";
        }


        /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('admin.thanh-vien.diem-tat-ca')
            ->with(
                'success',
                $message
            );
    }
}
