<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Phims;
use App\Models\SuatChieu;
use App\Models\ThongBaoPush;
use App\Models\ThongBaoPushNguoiDung;
use App\Models\NguoiDung;
use App\Models\VeXemPhim;
use App\Models\FoodInvoice;
use App\Traits\Loggable;
use App\Services\AdminNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ThungRacController extends Controller
{
    use Loggable;

    /**
     * =========================================================
     * THÙNG RÁC CHUNG
     * =========================================================
     */
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | TAB ĐANG CHỌN
        |--------------------------------------------------------------------------
        */
        $tab = $request->get('tab', 'suat_chieu');

        /*
        |--------------------------------------------------------------------------
        | BỘ LỌC CHUNG
        |--------------------------------------------------------------------------
        */
        $search = $request->get(
            'tim_kiem',
            $request->get('search')
        );

        $tuNgay = $request->get('tu_ngay');
        $denNgay = $request->get('den_ngay');

        /*
        |--------------------------------------------------------------------------
        | BỘ LỌC RIÊNG CHO THÔNG BÁO PUSH
        |--------------------------------------------------------------------------
        */
        $pushSearch = trim(
            (string) $request->get('search', '')
        );

        $pushLoai = $request->get('loai');
        $pushTrangThai = $request->get('trang_thai');
        $pushDoiTuongNhan = $request->get('doi_tuong_nhan');
        $pushHangThanhVien = $request->get('hang_thanh_vien');

        $pushNguoiDung = trim(
            (string) $request->get('nguoi_dung', '')
        );

        /*
        |--------------------------------------------------------------------------
        | OPTION HẠNG THÀNH VIÊN
        |--------------------------------------------------------------------------
        */
        $hangThanhVienOptions = [
            'member'   => 'Member',
            'silver'   => 'Silver',
            'gold'     => 'Gold',
            'platinum' => 'Platinum',
        ];

        /*
        |--------------------------------------------------------------------------
        | 1. THỐNG KÊ THÙNG RÁC
        |--------------------------------------------------------------------------
        */
        $stats = [

            'phim' => Phims::onlyTrashed()->count(),

            'suat_chieu' => SuatChieu::onlyTrashed()->count(),

            'khach_hang' => NguoiDung::onlyTrashed()
                ->where('vai_tro', 'khach_hang')
                ->count(),

            'nhan_vien' => NguoiDung::onlyTrashed()
                ->whereIn('vai_tro', [
                    'admin',
                    'nhan_vien',
                    'quan_ly',
                    'super_admin',
                ])
                ->count(),

            'thong_bao' => ThongBaoPush::onlyTrashed()->count(),

        ];

        $totalTrash = array_sum($stats);

        /*
        |--------------------------------------------------------------------------
        | 2. DỮ LIỆU THEO TAB
        |--------------------------------------------------------------------------
        */
        $items = match ($tab) {

            /*
            |--------------------------------------------------------------------------
            | PHIM
            |--------------------------------------------------------------------------
            */
            'phim' => Phims::onlyTrashed()

                ->when(
                    $search,
                    function ($q) use ($search) {
                        $q->where(
                            'ten_phim',
                            'like',
                            "%{$search}%"
                        );
                    }
                )

                ->when(
                    $tuNgay,
                    function ($q) use ($tuNgay) {
                        $q->whereDate(
                            'deleted_at',
                            '>=',
                            $tuNgay
                        );
                    }
                )

                ->when(
                    $denNgay,
                    function ($q) use ($denNgay) {
                        $q->whereDate(
                            'deleted_at',
                            '<=',
                            $denNgay
                        );
                    }
                )

                ->latest('deleted_at')
                ->paginate(10)
                ->withQueryString(),

            /*
            |--------------------------------------------------------------------------
            | SUẤT CHIẾU
            |--------------------------------------------------------------------------
            */
            'suat_chieu' => SuatChieu::onlyTrashed()

                ->with([
                    'phim',
                    'phongChieu',
                ])

                ->when(
                    $search,
                    function ($q) use ($search) {

                        $q->whereHas(
                            'phim',
                            function ($p) use ($search) {

                                $p->where(
                                    'ten_phim',
                                    'like',
                                    "%{$search}%"
                                );
                            }
                        );
                    }
                )

                ->when(
                    $tuNgay,
                    function ($q) use ($tuNgay) {
                        $q->whereDate(
                            'deleted_at',
                            '>=',
                            $tuNgay
                        );
                    }
                )

                ->when(
                    $denNgay,
                    function ($q) use ($denNgay) {
                        $q->whereDate(
                            'deleted_at',
                            '<=',
                            $denNgay
                        );
                    }
                )

                ->latest('deleted_at')
                ->paginate(10)
                ->withQueryString(),

            /*
            |--------------------------------------------------------------------------
            | KHÁCH HÀNG
            |--------------------------------------------------------------------------
            */
            'khach_hang' => NguoiDung::onlyTrashed()

                ->where(
                    'vai_tro',
                    'khach_hang'
                )

                ->when(
                    $search,
                    function ($q) use ($search) {

                        $q->where(
                            function ($sub) use ($search) {

                                $sub
                                    ->where(
                                        'ho_ten',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'email',
                                        'like',
                                        "%{$search}%"
                                    );
                            }
                        );
                    }
                )

                ->when(
                    $tuNgay,
                    function ($q) use ($tuNgay) {
                        $q->whereDate(
                            'deleted_at',
                            '>=',
                            $tuNgay
                        );
                    }
                )

                ->when(
                    $denNgay,
                    function ($q) use ($denNgay) {
                        $q->whereDate(
                            'deleted_at',
                            '<=',
                            $denNgay
                        );
                    }
                )

                ->latest('deleted_at')
                ->paginate(10)
                ->withQueryString(),

            /*
            |--------------------------------------------------------------------------
            | NHÂN VIÊN
            |--------------------------------------------------------------------------
            */
            'nhan_vien' => NguoiDung::onlyTrashed()

                ->whereIn(
                    'vai_tro',
                    [
                        'admin',
                        'nhan_vien',
                        'quan_ly',
                        'super_admin',
                    ]
                )

                ->when(
                    $search,
                    function ($q) use ($search) {

                        $q->where(
                            function ($sub) use ($search) {

                                $sub
                                    ->where(
                                        'ho_ten',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'email',
                                        'like',
                                        "%{$search}%"
                                    );
                            }
                        );
                    }
                )

                ->when(
                    $tuNgay,
                    function ($q) use ($tuNgay) {
                        $q->whereDate(
                            'deleted_at',
                            '>=',
                            $tuNgay
                        );
                    }
                )

                ->when(
                    $denNgay,
                    function ($q) use ($denNgay) {
                        $q->whereDate(
                            'deleted_at',
                            '<=',
                            $denNgay
                        );
                    }
                )

                ->latest('deleted_at')
                ->paginate(10)
                ->withQueryString(),

            /*
            |--------------------------------------------------------------------------
            | THÔNG BÁO PUSH
            |--------------------------------------------------------------------------
            */
            'thong_bao' => ThongBaoPush::onlyTrashed()

                ->with('nguoiTao')

                ->when(
                    $pushSearch !== '',
                    function ($q) use ($pushSearch) {

                        $q->where(
                            'tieu_de',
                            'like',
                            "%{$pushSearch}%"
                        );
                    }
                )

                ->when(
                    filled($pushLoai),
                    function ($q) use ($pushLoai) {

                        $q->where(
                            'loai',
                            $pushLoai
                        );
                    }
                )

                ->when(
                    filled($pushTrangThai),
                    function ($q) use ($pushTrangThai) {

                        $q->where(
                            'trang_thai',
                            $pushTrangThai
                        );
                    }
                )

                ->when(
                    filled($pushDoiTuongNhan),
                    function ($q) use ($pushDoiTuongNhan) {

                        $q->where(
                            'doi_tuong_nhan',
                            $pushDoiTuongNhan
                        );
                    }
                )

                ->when(
                    $pushDoiTuongNhan === 'hang_thanh_vien'
                    && filled($pushHangThanhVien),
                    function ($q) use ($pushHangThanhVien) {

                        $q->where(
                            'hang_thanh_vien',
                            $pushHangThanhVien
                        );
                    }
                )

                ->when(
                    $pushDoiTuongNhan === 'nguoi_dung_cu_the'
                    && $pushNguoiDung !== '',
                    function ($q) use ($pushNguoiDung) {

                        $q->whereExists(
                            function ($subQuery) use ($pushNguoiDung) {

                                $subQuery
                                    ->select(DB::raw(1))
                                    ->from(
                                        'thong_bao_push_nguoi_dungs as tbnd'
                                    )
                                    ->join(
                                        'nguoi_dungs as nd',
                                        'nd.id',
                                        '=',
                                        'tbnd.nguoi_dung_id'
                                    )
                                    ->whereColumn(
                                        'tbnd.thong_bao_push_id',
                                        'thong_bao_pushs.id'
                                    )
                                    ->where(
                                        function ($userQuery) use ($pushNguoiDung) {

                                            $userQuery
                                                ->where(
                                                    'nd.ho_ten',
                                                    'like',
                                                    "%{$pushNguoiDung}%"
                                                )
                                                ->orWhere(
                                                    'nd.email',
                                                    'like',
                                                    "%{$pushNguoiDung}%"
                                                );
                                        }
                                    );
                            }
                        );
                    }
                )

                ->latest('deleted_at')
                ->paginate(10)
                ->withQueryString(),

            /*
            |--------------------------------------------------------------------------
            | MẶC ĐỊNH
            |--------------------------------------------------------------------------
            */
            default => SuatChieu::onlyTrashed()
                ->paginate(10)
                ->withQueryString(),
        };

        /*
        |--------------------------------------------------------------------------
        | TRẢ VIEW
        |--------------------------------------------------------------------------
        */
        return view(
            'admin.thung_rac.index',
            compact(
                'tab',
                'stats',
                'totalTrash',
                'items',
                'hangThanhVienOptions'
            )
        );
    }

    /**
     * =========================================================
     * KHÔI PHỤC TẤT CẢ
     * =========================================================
     */
    public function restoreAll(
        Request $request,
        string $type
    ) {
        $count = 0;

        /*
        |--------------------------------------------------------------------------
        | PHIM
        |--------------------------------------------------------------------------
        */
        if (
            $type === 'phim'
            && class_exists(Phims::class)
        ) {

            $count = Phims::onlyTrashed()->count();

            Phims::onlyTrashed()->restore();
        }

        /*
        |--------------------------------------------------------------------------
        | SUẤT CHIẾU
        |--------------------------------------------------------------------------
        */
        elseif (
            $type === 'suat_chieu'
            && class_exists(SuatChieu::class)
        ) {

            $count = SuatChieu::onlyTrashed()->count();

            SuatChieu::onlyTrashed()->restore();
        }

        /*
        |--------------------------------------------------------------------------
        | KHÁCH HÀNG
        |--------------------------------------------------------------------------
        */
        elseif (
            $type === 'khach_hang'
            && class_exists(NguoiDung::class)
        ) {

            $count = NguoiDung::onlyTrashed()
                ->where(
                    'vai_tro',
                    'khach_hang'
                )
                ->count();

            NguoiDung::onlyTrashed()
                ->where(
                    'vai_tro',
                    'khach_hang'
                )
                ->restore();
        }

        /*
        |--------------------------------------------------------------------------
        | NHÂN VIÊN
        |--------------------------------------------------------------------------
        */
        elseif (
            $type === 'nhan_vien'
            && class_exists(NguoiDung::class)
        ) {

            $staffs = NguoiDung::onlyTrashed()
                ->whereIn(
                    'vai_tro',
                    [
                        'admin',
                        'nhan_vien',
                        'quan_ly',
                        'super_admin',
                    ]
                )
                ->get();

            $count = $staffs->count();

            foreach ($staffs as $staff) {

                $staff->restore();

                $staff->update([
                    'trang_thai_hoat_dong' => true,
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | THÔNG BÁO PUSH
        |--------------------------------------------------------------------------
        */
        elseif (
            $type === 'thong_bao'
            && class_exists(ThongBaoPush::class)
        ) {

            $count = ThongBaoPush::onlyTrashed()->count();

            ThongBaoPush::onlyTrashed()->restore();
        }

        /*
        |--------------------------------------------------------------------------
        | GHI NHẬT KÝ
        |--------------------------------------------------------------------------
        */
        $this->ghiNhatKy(
            $request,
            'Khôi phục tất cả thùng rác',
            'Quản trị hệ thống',
            "Đã khôi phục toàn bộ {$count} bản ghi thuộc danh mục {$type}"
        );

        /*
        |--------------------------------------------------------------------------
        | THÔNG BÁO ADMIN
        |--------------------------------------------------------------------------
        */
        AdminNotificationService::push(
            '♻️ Đã khôi phục dữ liệu',
            "Đã khôi phục tất cả {$count} bản ghi rác thuộc mục {$type}",
            'Success'
        );

        return redirect()
            ->back()
            ->with(
                'success',
                "Đã khôi phục thành công tất cả {$count} bản ghi!"
            );
    }

    /**
     * =========================================================
     * KHÔI PHỤC 1 BẢN GHI
     * =========================================================
     */
    public function restore(
        Request $request,
        string $type,
        int $id
    ) {

        /*
        |--------------------------------------------------------------------------
        | THÔNG BÁO
        |--------------------------------------------------------------------------
        */
        if ($type === 'thong_bao') {

            $thongBao = ThongBaoPush::onlyTrashed()
                ->findOrFail($id);

            $thongBao->restore();

            return back()->with(
                'success',
                'Đã khôi phục thông báo thành công!'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | PHIM
        |--------------------------------------------------------------------------
        */
        if ($type === 'phim') {

            $phim = Phims::onlyTrashed()
                ->findOrFail($id);

            $phim->restore();

            return back()->with(
                'success',
                'Đã khôi phục phim thành công!'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SUẤT CHIẾU
        |--------------------------------------------------------------------------
        */
        if ($type === 'suat_chieu') {

            $suatChieu = SuatChieu::onlyTrashed()
                ->findOrFail($id);

            $suatChieu->restore();

            return back()->with(
                'success',
                'Đã khôi phục suất chiếu thành công!'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | KHÁCH HÀNG
        |--------------------------------------------------------------------------
        */
        if ($type === 'khach_hang') {

            $khachHang = NguoiDung::onlyTrashed()
                ->where('vai_tro', 'khach_hang')
                ->findOrFail($id);

            $khachHang->restore();

            return back()->with(
                'success',
                'Đã khôi phục khách hàng thành công!'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | NHÂN VIÊN
        |--------------------------------------------------------------------------
        */
        if ($type === 'nhan_vien') {

            $nhanVien = NguoiDung::onlyTrashed()
                ->whereIn(
                    'vai_tro',
                    [
                        'admin',
                        'nhan_vien',
                        'quan_ly',
                        'super_admin',
                    ]
                )
                ->findOrFail($id);

            $nhanVien->restore();

            $nhanVien->update([
                'trang_thai_hoat_dong' => true,
            ]);

            return back()->with(
                'success',
                'Đã khôi phục nhân viên thành công!'
            );
        }

        return back()->with(
            'error',
            'Loại dữ liệu không hợp lệ!'
        );
    }

    /**
     * =========================================================
     * XÓA VĨNH VIỄN 1 BẢN GHI
     * =========================================================
     */
    public function forceDelete(
        Request $request,
        string $type,
        int $id
    ) {

        /*
        |--------------------------------------------------------------------------
        | THÔNG BÁO PUSH
        |--------------------------------------------------------------------------
        */
        if ($type === 'thong_bao') {

            $thongBao = ThongBaoPush::onlyTrashed()
                ->findOrFail($id);

            ThongBaoPushNguoiDung::where(
                'thong_bao_push_id',
                $thongBao->id
            )->delete();

            $thongBao->forceDelete();

            return back()->with(
                'success',
                'Đã xóa vĩnh viễn thông báo thành công!'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | KHÁCH HÀNG
        |--------------------------------------------------------------------------
        |
        | QUAN TRỌNG:
        | Không được xóa khách hàng nếu đã phát sinh vé.
        |
        */
        if ($type === 'khach_hang') {

            $khachHang = NguoiDung::onlyTrashed()
                ->where(
                    'vai_tro',
                    'khach_hang'
                )
                ->findOrFail($id);

            /*
            |--------------------------------------------------------------------------
            | KIỂM TRA VÉ
            |--------------------------------------------------------------------------
            */
            $daMuaVe = VeXemPhim::where(
                'nguoi_dung_id',
                $khachHang->id
            )->exists();

            if ($daMuaVe) {

                return back()->with(
                    'error',
                    'Không thể xóa vĩnh viễn khách hàng này vì tài khoản đã phát sinh vé xem phim.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | CHƯA CÓ VÉ -> CHO PHÉP XÓA
            |--------------------------------------------------------------------------
            */
            $khachHang->forceDelete();

            return back()->with(
                'success',
                'Đã xóa vĩnh viễn khách hàng thành công!'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | NHÂN VIÊN
        |--------------------------------------------------------------------------
        */
        if ($type === 'nhan_vien') {

            $nhanVien = NguoiDung::onlyTrashed()
                ->whereIn(
                    'vai_tro',
                    [
                        'admin',
                        'nhan_vien',
                        'quan_ly',
                        'super_admin',
                    ]
                )
                ->findOrFail($id);

            /*
            |--------------------------------------------------------------------------
            | KIỂM TRA ĐÃ BÁN VÉ
            |--------------------------------------------------------------------------
            */
            $daBanVe = VeXemPhim::where(
                'nhan_vien_id',
                $nhanVien->id
            )->exists();

            /*
            |--------------------------------------------------------------------------
            | KIỂM TRA ĐÃ TẠO HÓA ĐƠN ĐỒ ĂN
            |--------------------------------------------------------------------------
            */
            $daTaoHoaDon = FoodInvoice::where(
                'user_id',
                $nhanVien->id
            )->exists();

            if ($daBanVe || $daTaoHoaDon) {

                return back()->with(
                    'error',
                    'Không thể xóa vĩnh viễn nhân viên vì tài khoản đã phát sinh dữ liệu công việc.'
                );
            }

            $nhanVien->forceDelete();

            return back()->with(
                'success',
                'Đã xóa vĩnh viễn nhân viên thành công!'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | PHIM
        |--------------------------------------------------------------------------
        */
        if ($type === 'phim') {

            $phim = Phims::onlyTrashed()
                ->findOrFail($id);

            /*
            | Không xóa phim nếu còn suất chiếu
            */
            if ($phim->showtimes()->exists()) {

                return back()->with(
                    'error',
                    'Không thể xóa vĩnh viễn phim vì vẫn còn suất chiếu liên quan.'
                );
            }

            if (method_exists($phim, 'genres')) {
                $phim->genres()->detach();
            }

            $phim->forceDelete();

            return back()->with(
                'success',
                'Đã xóa vĩnh viễn phim thành công!'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SUẤT CHIẾU
        |--------------------------------------------------------------------------
        */
        if ($type === 'suat_chieu') {

            $suatChieu = SuatChieu::onlyTrashed()
                ->findOrFail($id);

            $suatChieu->forceDelete();

            return back()->with(
                'success',
                'Đã xóa vĩnh viễn suất chiếu thành công!'
            );
        }

        return back()->with(
            'error',
            'Loại dữ liệu không hợp lệ!'
        );
    }

    /**
     * =========================================================
     * DỌN SẠCH THÙNG RÁC
     * =========================================================
     */
    public function emptyTrash(
        Request $request,
        string $type
    ) {

        $count = 0;

        /*
        |--------------------------------------------------------------------------
        | PHIM
        |--------------------------------------------------------------------------
        */
        if (
            $type === 'phim'
            && class_exists(Phims::class)
        ) {

            $trashedMovies = Phims::onlyTrashed()->get();

            foreach ($trashedMovies as $phim) {

                /*
                | Không xóa nếu vẫn còn suất chiếu
                */
                if (
                    $phim
                        ->showtimes()
                        ->exists()
                ) {
                    continue;
                }

                /*
                | Xóa quan hệ thể loại
                */
                if (method_exists($phim, 'genres')) {

                    $phim
                        ->genres()
                        ->detach();
                }

                $phim->forceDelete();

                $count++;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | SUẤT CHIẾU
        |--------------------------------------------------------------------------
        */
        elseif (
            $type === 'suat_chieu'
            && class_exists(SuatChieu::class)
        ) {

            /*
            | Chỉ xóa các suất chiếu đã soft-delete.
            */
            $trashedShowtimes = SuatChieu::onlyTrashed()->get();

            foreach ($trashedShowtimes as $suatChieu) {

                $suatChieu->forceDelete();

                $count++;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | KHÁCH HÀNG
        |--------------------------------------------------------------------------
        |
        | ĐÂY LÀ PHẦN QUAN TRỌNG ĐÃ SỬA.
        |
        | Không được dùng:
        |
        | NguoiDung::onlyTrashed()
        |     ->where('vai_tro', 'khach_hang')
        |     ->forceDelete();
        |
        | Vì câu lệnh đó xóa tất cả khách hàng bất kể
        | họ đã từng mua vé hay chưa.
        |
        */
        elseif (
            $type === 'khach_hang'
            && class_exists(NguoiDung::class)
        ) {

            $trashedCustomers = NguoiDung::onlyTrashed()
                ->where(
                    'vai_tro',
                    'khach_hang'
                )
                ->get();

            foreach ($trashedCustomers as $khachHang) {

                /*
                |--------------------------------------------------------------------------
                | KIỂM TRA KHÁCH HÀNG ĐÃ TỪNG PHÁT SINH VÉ
                |--------------------------------------------------------------------------
                |
                | Chỉ cần có 1 vé liên quan đến người dùng
                | thì không được xóa vĩnh viễn.
                |
                */
                $daMuaVe = false;

                if (class_exists(VeXemPhim::class)) {

                    $daMuaVe = VeXemPhim::where(
                        'nguoi_dung_id',
                        $khachHang->id
                    )->exists();
                }

                /*
                |--------------------------------------------------------------------------
                | CÓ VÉ -> GIỮ LẠI
                |--------------------------------------------------------------------------
                */
                if ($daMuaVe) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | KHÔNG CÓ VÉ -> XÓA VĨNH VIỄN
                |--------------------------------------------------------------------------
                */
                $khachHang->forceDelete();

                $count++;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | NHÂN VIÊN
        |--------------------------------------------------------------------------
        */
        elseif (
            $type === 'nhan_vien'
            && class_exists(NguoiDung::class)
        ) {

            $trashedStaffs = NguoiDung::onlyTrashed()
                ->whereIn(
                    'vai_tro',
                    [
                        'admin',
                        'nhan_vien',
                        'quan_ly',
                        'super_admin',
                    ]
                )
                ->get();

            foreach ($trashedStaffs as $staff) {

                /*
                |--------------------------------------------------------------------------
                | KIỂM TRA ĐÃ BÁN VÉ
                |--------------------------------------------------------------------------
                */
                $daBanVe = false;

                if (class_exists(VeXemPhim::class)) {

                    $daBanVe = VeXemPhim::where(
                        'nhan_vien_id',
                        $staff->id
                    )->exists();
                }

                /*
                |--------------------------------------------------------------------------
                | KIỂM TRA ĐÃ TẠO HÓA ĐƠN ĐỒ ĂN
                |--------------------------------------------------------------------------
                */
                $daTaoHoaDon = false;

                if (class_exists(FoodInvoice::class)) {

                    $daTaoHoaDon = FoodInvoice::where(
                        'user_id',
                        $staff->id
                    )->exists();
                }

                /*
                |--------------------------------------------------------------------------
                | CÓ PHÁT SINH -> KHÔNG XÓA
                |--------------------------------------------------------------------------
                */
                if (
                    $daBanVe
                    || $daTaoHoaDon
                ) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | KHÔNG PHÁT SINH -> XÓA
                |--------------------------------------------------------------------------
                */
                $staff->forceDelete();

                $count++;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | THÔNG BÁO PUSH
        |--------------------------------------------------------------------------
        */
        elseif (
            $type === 'thong_bao'
            && class_exists(ThongBaoPush::class)
        ) {

            $trashedNotifications =
                ThongBaoPush::onlyTrashed()->get();

            foreach ($trashedNotifications as $thongBao) {

                /*
                | Xóa danh sách người nhận trước
                */
                if (
                    class_exists(
                        ThongBaoPushNguoiDung::class
                    )
                ) {

                    ThongBaoPushNguoiDung::where(
                        'thong_bao_push_id',
                        $thongBao->id
                    )->delete();
                }

                /*
                | Xóa thông báo
                */
                $thongBao->forceDelete();

                $count++;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | GHI NHẬT KÝ
        |--------------------------------------------------------------------------
        */
        $this->ghiNhatKy(
            $request,
            'Dọn dẹp thùng rác',
            'Quản trị hệ thống',
            "Đã xóa vĩnh viễn {$count} bản ghi rác thuộc danh mục {$type}"
        );

        /*
        |--------------------------------------------------------------------------
        | THÔNG BÁO ADMIN
        |--------------------------------------------------------------------------
        */
        AdminNotificationService::push(
            '🧹 Đã dọn dẹp thùng rác',
            "Đã xóa vĩnh viễn {$count} bản ghi rác thuộc mục {$type}",
            'Warning'
        );

        /*
        |--------------------------------------------------------------------------
        | TRẢ VỀ
        |--------------------------------------------------------------------------
        */
        return redirect()
            ->back()
            ->with(
                'success',
                "Đã dọn dẹp thành công {$count} bản ghi rác!"
            );
    }
}
