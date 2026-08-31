<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThanhVien;
use App\Models\ThongBaoCaNhan;
use App\Services\AdminNotificationService;
use Illuminate\Http\Request;
use App\Mail\DiemThanhVienMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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

        // Thống kê số lượng thành viên
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

        // Danh sách voucher
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
     *
     * GIỚI HẠN:
     * - Tối thiểu: 1
     * - Tối đa: 100.000
     */
    public function tangDiem(
        Request $request,
        ThanhVien $thanhVien
    ) {
        $data = $request->validate(
            [
                'form' => [
                    'required',
                    'in:tang'
                ],

                'so_diem' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:100000'
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
            ],
            [
                'so_diem.required' =>
                    'Vui lòng nhập số điểm.',

                'so_diem.integer' =>
                    'Số điểm phải là số nguyên.',

                'so_diem.min' =>
                    'Số điểm phải lớn hơn hoặc bằng 1.',

                'so_diem.max' =>
                    'Số điểm tặng không được vượt quá 100.000.',

                'noi_dung.required' =>
                    'Vui lòng nhập nội dung.',

                'noi_dung.max' =>
                    'Nội dung không được vượt quá 255 ký tự.',

                'hang_thanh_vien.in' =>
                    'Hạng thành viên không hợp lệ.',
            ]
        );


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
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Không tìm thấy tài khoản người dùng của thành viên.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | KIỂM TRA EMAIL ĐÃ XÁC THỰC
        |--------------------------------------------------------------------------
        */

        if (is_null($nguoiDung->email_verified_at)) {
            return back()
                ->withInput()
                ->with(
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
        | REFRESH
        |--------------------------------------------------------------------------
        */

        $thanhVien->refresh();


        /*
        |--------------------------------------------------------------------------
        | GỬI EMAIL
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

                Log::error(
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
        | THÔNG BÁO USER
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
        | THÔNG BÁO ADMIN
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


        return back()
            ->with(
                'success',
                'Đã tặng '
                . number_format($data['so_diem'])
                . ' điểm cho thành viên thành công.'
            );
    }


    /**
     * Admin thu hồi điểm thủ công cho một thành viên.
     *
     * GIỚI HẠN:
     * - Tối thiểu: 1
     * - Tối đa: 100.000
     *
     * Đồng thời không được thu hồi quá số điểm
     * hiện tại của thành viên.
     */
    public function truDiem(
        Request $request,
        ThanhVien $thanhVien
    ) {
        $data = $request->validate(
            [
                'form' => [
                    'required',
                    'in:thu_hoi'
                ],

                /*
                 * ĐỒNG BỘ VỚI FORM TẶNG ĐIỂM
                 * VÀ TRANG ĐIỂM HÀNG LOẠT.
                 */
                'so_diem' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:100000'
                ],

                'noi_dung' => [
                    'nullable',
                    'string',
                    'max:255'
                ],
            ],
            [
                'so_diem.required' =>
                    'Vui lòng nhập số điểm.',

                'so_diem.integer' =>
                    'Số điểm phải là số nguyên.',

                'so_diem.min' =>
                    'Số điểm phải lớn hơn hoặc bằng 1.',

                'so_diem.max' =>
                    'Số điểm thu hồi không được vượt quá 100.000.',

                'noi_dung.max' =>
                    'Nội dung không được vượt quá 255 ký tự.',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | NỘI DUNG MẶC ĐỊNH
        |--------------------------------------------------------------------------
        */

        $noiDung = trim(
            (string) ($data['noi_dung'] ?? '')
        );

        if ($noiDung === '') {
            $noiDung = 'Admin thu hồi điểm';
        }


        /*
        |--------------------------------------------------------------------------
        | KIỂM TRA ĐIỂM HIỆN TẠI
        |--------------------------------------------------------------------------
        */

        if (
            (int) $data['so_diem']
            >
            (int) $thanhVien->diem_hien_tai
        ) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Số điểm thu hồi không được lớn hơn số điểm hiện tại của thành viên.'
                );
        }


        $soDiemThuHoi = (int) $data['so_diem'];


        /*
        |--------------------------------------------------------------------------
        | THU HỒI ĐIỂM
        |--------------------------------------------------------------------------
        */

        $thanhVien->thuHoiDiem(
            $soDiemThuHoi,
            'Admin thu hồi điểm: ' . $noiDung
        );


        /*
        |--------------------------------------------------------------------------
        | LẤY USER
        |--------------------------------------------------------------------------
        */

        $nguoiDung = $thanhVien->nguoiDung;


        /*
        |--------------------------------------------------------------------------
        | REFRESH
        |--------------------------------------------------------------------------
        */

        $thanhVien->refresh();


        /*
        |--------------------------------------------------------------------------
        | GỬI EMAIL
        |--------------------------------------------------------------------------
        */

        if (
            $nguoiDung &&
            !empty($nguoiDung->email) &&
            !is_null($nguoiDung->email_verified_at)
        ) {

            try {

                Mail::to($nguoiDung->email)->send(

                    new DiemThanhVienMail(
                        'thu_hoi',
                        $soDiemThuHoi,
                        $noiDung,
                        $thanhVien->diem_hien_tai,
                        $nguoiDung->ho_ten ?? 'Quý khách'
                    )
                );

            } catch (\Throwable $e) {

                Log::error(
                    'Không thể gửi email thu hồi điểm.',
                    [
                        'thanh_vien_id' => $thanhVien->id,
                        'nguoi_dung_id' => $nguoiDung->id,
                        'email' => $nguoiDung->email,
                        'so_diem' => $soDiemThuHoi,
                        'error' => $e->getMessage(),
                    ]
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | THÔNG BÁO USER
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
                    . ' điểm từ tài khoản của bạn. '
                    . $noiDung,

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
        | THÔNG BÁO ADMIN
        |--------------------------------------------------------------------------
        */

        AdminNotificationService::push(

            '🔄 Admin đã thu hồi điểm',

            'Admin vừa thu hồi '
                . number_format($soDiemThuHoi)
                . ' điểm của thành viên '
                . ($nguoiDung->ho_ten ?? 'không xác định')
                . '. Nội dung: '
                . $noiDung,

            'Warning'
        );


        return back()
            ->with(
                'success',
                'Đã thu hồi điểm thành viên thành công.'
            );
    }


    /**
     * Trang tặng / thu hồi điểm hàng loạt.
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
     * Tặng hoặc thu hồi điểm hàng loạt.
     */
    public function xuLyDiemHangLoat(Request $request)
    {
        $loai = $request->input('loai');


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

            'doi_tuong_nhan' => [
                'required',
                'in:all,hang_thanh_vien,nguoi_dung_cu_the',
            ],

            'hang_thanh_vien' => [
                'nullable',
                'in:member,silver,gold,platinum',
            ],

            'nguoi_dung_cu_the' => [
                'nullable',
                'array',
            ],

            'nguoi_dung_cu_the.*' => [
                'integer',
                'distinct',
                'exists:nguoi_dungs,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | TỐI ĐA 100.000 CHO CẢ TẶNG VÀ THU HỒI
            |--------------------------------------------------------------------------
            */

            'so_diem' => [
                'required',
                'integer',
                'min:1',
                'max:100000',
            ],

            'noi_dung' => [
                $loai === 'tang'
                    ? 'required'
                    : 'nullable',

                'string',
                'max:255',
            ],
        ];


        if ($loai === 'tang') {

            $rules['tinh_vao_hang'] = [
                'required',
                'boolean',
            ];
        }


        $data = $request->validate(
            $rules,
            [
                'loai.required' =>
                    'Vui lòng chọn loại thao tác.',

                'loai.in' =>
                    'Loại thao tác không hợp lệ.',

                'doi_tuong_nhan.required' =>
                    'Vui lòng chọn đối tượng áp dụng.',

                'doi_tuong_nhan.in' =>
                    'Đối tượng áp dụng không hợp lệ.',

                'hang_thanh_vien.in' =>
                    'Hạng thành viên không hợp lệ.',

                'nguoi_dung_cu_the.array' =>
                    'Danh sách người dùng không hợp lệ.',

                'nguoi_dung_cu_the.*.integer' =>
                    'ID người dùng không hợp lệ.',

                'nguoi_dung_cu_the.*.distinct' =>
                    'Không được chọn trùng người dùng.',

                'nguoi_dung_cu_the.*.exists' =>
                    'Người dùng được chọn không tồn tại.',

                'so_diem.required' =>
                    'Vui lòng nhập số điểm.',

                'so_diem.integer' =>
                    'Số điểm phải là số nguyên.',

                'so_diem.min' =>
                    'Số điểm phải lớn hơn hoặc bằng 1.',

                'so_diem.max' =>
                    'Số điểm không được vượt quá 100.000.',

                'noi_dung.required' =>
                    'Vui lòng nhập nội dung khi tặng điểm.',

                'noi_dung.max' =>
                    'Nội dung không được vượt quá 255 ký tự.',

                'tinh_vao_hang.required' =>
                    'Vui lòng chọn cách tính hạng.',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | NỘI DUNG
        |--------------------------------------------------------------------------
        */

        $noiDung = trim(
            (string) ($data['noi_dung'] ?? '')
        );

        if ($noiDung === '') {
            $noiDung = 'Admin thu hồi điểm';
        }


        /*
        |--------------------------------------------------------------------------
        | NGƯỜI DÙNG CỤ THỂ
        |--------------------------------------------------------------------------
        */

        if (
            $data['doi_tuong_nhan'] === 'nguoi_dung_cu_the'
        ) {

            $nguoiDungIds =
                $data['nguoi_dung_cu_the'] ?? [];

            if (empty($nguoiDungIds)) {

                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'Vui lòng chọn ít nhất một người dùng.'
                    );
            }

            $data['nguoi_dung_cu_the'] =
                array_values(
                    array_unique(
                        array_map(
                            'intval',
                            $nguoiDungIds
                        )
                    )
                );
        }


        /*
        |--------------------------------------------------------------------------
        | KIỂM TRA HẠNG
        |--------------------------------------------------------------------------
        */

        if (
            $data['doi_tuong_nhan'] === 'hang_thanh_vien'
        ) {

            if (
                empty($data['hang_thanh_vien'])
            ) {

                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'Vui lòng chọn hạng thành viên.'
                    );
            }


            $soThanhVienTheoHang =
                ThanhVien::where(
                    'hang_thanh_vien',
                    $data['hang_thanh_vien']
                )->count();


            if ($soThanhVienTheoHang === 0) {

                $tenHang = match (
                    $data['hang_thanh_vien']
                ) {

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
        | QUERY THÀNH VIÊN
        |--------------------------------------------------------------------------
        */

        $query = ThanhVien::with('nguoiDung');


        if (
            $data['doi_tuong_nhan'] === 'hang_thanh_vien'
        ) {

            $query->where(
                'hang_thanh_vien',
                $data['hang_thanh_vien']
            );

        } elseif (
            $data['doi_tuong_nhan'] === 'nguoi_dung_cu_the'
        ) {

            $query->whereIn(
                'nguoi_dung_id',
                $data['nguoi_dung_cu_the']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | LẤY DANH SÁCH
        |--------------------------------------------------------------------------
        */

        $thanhViens = $query->get();


        if ($thanhViens->isEmpty()) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Không tìm thấy thành viên phù hợp với đối tượng đã chọn.'
                );
        }


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
        | TÊN ĐỐI TƯỢNG
        |--------------------------------------------------------------------------
        */

        $hangText = match (
            $data['doi_tuong_nhan']
        ) {

            'all' =>
                'tất cả thành viên',

            'hang_thanh_vien' =>
                match (
                    $data['hang_thanh_vien'] ?? null
                ) {

                    'member' =>
                        'Thành viên',

                    'silver' =>
                        'Bạc',

                    'gold' =>
                        'Vàng',

                    'platinum' =>
                        'Bạch kim',

                    default =>
                        'hạng không xác định',
                },

            'nguoi_dung_cu_the' =>
                'người dùng được chọn',

            default =>
                'đối tượng được chọn',
        };


        /*
        |--------------------------------------------------------------------------
        | XỬ LÝ TỪNG THÀNH VIÊN
        |--------------------------------------------------------------------------
        */

        foreach ($thanhViens as $thanhVien) {

            $nguoiDung =
                $thanhVien->nguoiDung;


            if (!$nguoiDung) {

                Log::warning(
                    'Bỏ qua thành viên vì không có người dùng liên kết.',
                    [
                        'thanh_vien_id' =>
                            $thanhVien->id,
                    ]
                );

                $soLuongBoQua++;

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | EMAIL CHƯA XÁC THỰC
            |--------------------------------------------------------------------------
            */

            if (
                is_null(
                    $nguoiDung->email_verified_at
                )
            ) {

                $soLuongBoQua++;

                Log::info(
                    'Bỏ qua thành viên chưa xác thực email.',
                    [
                        'thanh_vien_id' =>
                            $thanhVien->id,

                        'nguoi_dung_id' =>
                            $nguoiDung->id,

                        'email' =>
                            $nguoiDung->email,
                    ]
                );

                continue;
            }


            $email = trim(
                (string) $nguoiDung->email
            );

            $hoTen =
                $nguoiDung->ho_ten
                ?: 'Quý khách';


            /*
            |--------------------------------------------------------------------------
            | TẶNG ĐIỂM
            |--------------------------------------------------------------------------
            */

            if (
                $data['loai'] === 'tang'
            ) {

                if (
                    (int) (
                        $data['tinh_vao_hang'] ?? 0
                    ) === 1
                ) {

                    $thanhVien->congDiem(
                        (int) $data['so_diem'],
                        null,
                        'Admin tặng điểm cho bạn: '
                        . $noiDung
                    );

                } else {

                    $thanhVien->congDiemKhongXetHang(
                        (int) $data['so_diem'],
                        'Admin tặng điểm không xét hạng: '
                        . $noiDung
                    );
                }


                $thanhVien->refresh();


                /*
                |------------------------------------------------------------------
                | THÔNG BÁO WEBSITE
                |------------------------------------------------------------------
                */

                ThongBaoCaNhan::create([

                    'nguoi_dung_id' =>
                        $nguoiDung->id,

                    'tieu_de' =>
                        '🎁 Bạn được tặng điểm',

                    'noi_dung' =>
                        'Bạn vừa được Admin tặng '
                        . number_format(
                            $data['so_diem']
                        )
                        . ' điểm dành cho '
                        . $hangText
                        . '. '
                        . $noiDung,

                    'loai_thong_bao' =>
                        'diem',

                    'duong_dan' =>
                        route(
                            'user.thanh-vien.index'
                        ),

                    'da_doc' =>
                        false,

                    'doc_luc' =>
                        null,
                ]);


                /*
                |------------------------------------------------------------------
                | EMAIL
                |------------------------------------------------------------------
                */

                if ($email !== '') {

                    try {

                        Mail::to($email)->send(

                            new DiemThanhVienMail(
                                'tang',
                                (int) $data['so_diem'],
                                $noiDung,
                                (int) $thanhVien->diem_hien_tai,
                                $hoTen
                            )
                        );

                        $soEmailThanhCong++;

                    } catch (\Throwable $e) {

                        $soEmailLoi++;

                        Log::error(
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
            | THU HỒI
            |--------------------------------------------------------------------------
            */

            else {

                /*
                |------------------------------------------------------------------
                | KHÔNG ĐỦ ĐIỂM
                |------------------------------------------------------------------
                */

                if (
                    (int) $data['so_diem']
                    >
                    (int) $thanhVien->diem_hien_tai
                ) {

                    $soLuongBoQua++;

                    continue;
                }


                $soDiemThuHoi =
                    (int) $data['so_diem'];


                if ($soDiemThuHoi <= 0) {

                    $soLuongBoQua++;

                    continue;
                }


                $thanhVien->thuHoiDiem(
                    $soDiemThuHoi,
                    'Admin thu hồi điểm của bạn: '
                    . $noiDung
                );


                $thanhVien->refresh();


                /*
                |------------------------------------------------------------------
                | THÔNG BÁO
                |------------------------------------------------------------------
                */

                ThongBaoCaNhan::create([

                    'nguoi_dung_id' =>
                        $nguoiDung->id,

                    'tieu_de' =>
                        '🔄 Điểm thành viên được cập nhật',

                    'noi_dung' =>
                        'Admin đã thu hồi '
                        . number_format(
                            $soDiemThuHoi
                        )
                        . ' điểm từ tài khoản của bạn. '
                        . $noiDung,

                    'loai_thong_bao' =>
                        'diem',

                    'duong_dan' =>
                        route(
                            'user.thanh-vien.index'
                        ),

                    'da_doc' =>
                        false,

                    'doc_luc' =>
                        null,
                ]);


                /*
                |------------------------------------------------------------------
                | EMAIL
                |------------------------------------------------------------------
                */

                if ($email !== '') {

                    try {

                        Mail::to($email)->send(

                            new DiemThanhVienMail(
                                'thu_hoi',
                                $soDiemThuHoi,
                                $noiDung,
                                (int) $thanhVien->diem_hien_tai,
                                $hoTen
                            )
                        );

                        $soEmailThanhCong++;

                    } catch (\Throwable $e) {

                        $soEmailLoi++;

                        Log::error(
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
        | THÔNG BÁO ADMIN
        |--------------------------------------------------------------------------
        */

        if (
            $data['loai'] === 'tang'
        ) {

            AdminNotificationService::push(

                '🎁 Admin đã tặng điểm hàng loạt',

                'Admin vừa tặng '
                . number_format(
                    $data['so_diem']
                )
                . ' điểm cho '
                . $soLuong
                . ' thành viên thuộc '
                . $hangText
                . '. Email thành công: '
                . $soEmailThanhCong
                . '. Email lỗi: '
                . $soEmailLoi
                . '. Bỏ qua: '
                . $soLuongBoQua
                . '. Nội dung: '
                . $noiDung,

                'Success'
            );

        } else {

            AdminNotificationService::push(

                '🔄 Admin đã thu hồi điểm hàng loạt',

                'Admin vừa thu hồi '
                . number_format(
                    $data['so_diem']
                )
                . ' điểm của '
                . $soLuong
                . ' thành viên thuộc '
                . $hangText
                . '. Email thành công: '
                . $soEmailThanhCong
                . '. Email lỗi: '
                . $soEmailLoi
                . '. Bỏ qua: '
                . $soLuongBoQua
                . '. Nội dung: '
                . $noiDung,

                'Warning'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | MESSAGE
        |--------------------------------------------------------------------------
        */

        if (
            $data['loai'] === 'tang'
        ) {

            $message =
                'Đã tặng '
                . number_format(
                    $data['so_diem']
                )
                . ' điểm cho '
                . $soLuong
                . ' thành viên. '
                . 'Đã gửi '
                . $soEmailThanhCong
                . ' email.';

        } else {

            $message =
                'Đã thu hồi '
                . number_format(
                    $data['so_diem']
                )
                . ' điểm của '
                . $soLuong
                . ' thành viên. '
                . 'Đã gửi '
                . $soEmailThanhCong
                . ' email.';
        }


        if ($soLuongBoQua > 0) {

            $message .=
                ' Bỏ qua '
                . $soLuongBoQua
                . ' thành viên.';
        }


        return redirect()
            ->route(
                'admin.thanh-vien.diem-tat-ca'
            )
            ->with(
                'success',
                $message    
            );
    }
}