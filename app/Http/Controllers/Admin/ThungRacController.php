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
        | BỘ LỌC CHUNG CHO PHIM / SUẤT CHIẾU / KHÁCH HÀNG / NHÂN VIÊN
        |--------------------------------------------------------------------------
        |
        | Các tab này vẫn dùng:
        | - tim_kiem
        | - tu_ngay
        | - den_ngay
        |
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
        |
        | Không dùng tu_ngay / den_ngay ở tab thông báo.
        |
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
        | CÁC OPTION CỦA THÔNG BÁO PUSH
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
        | 1. THỐNG KÊ SỐ LƯỢNG TRONG THÙNG RÁC
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

            'thong_bao' => ThongBaoPush::onlyTrashed()
                ->count(),

        ];


        $totalTrash = array_sum($stats);


        /*
        |--------------------------------------------------------------------------
        | 2. TẢI DỮ LIỆU THEO TAB
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
            |
            | ĐÂY LÀ PHẦN ĐƯỢC SỬA
            |
            */
            'thong_bao' => ThongBaoPush::onlyTrashed()

                /*
                |--------------------------------------------------------------------------
                | Người tạo
                |--------------------------------------------------------------------------
                */
                ->with('nguoiTao')


                /*
                |--------------------------------------------------------------------------
                | TÌM KIẾM TIÊU ĐỀ
                |--------------------------------------------------------------------------
                */
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


                /*
                |--------------------------------------------------------------------------
                | LỌC LOẠI
                |--------------------------------------------------------------------------
                */
                ->when(
                    filled($pushLoai),
                    function ($q) use ($pushLoai) {

                        $q->where(
                            'loai',
                            $pushLoai
                        );

                    }
                )


                /*
                |--------------------------------------------------------------------------
                | LỌC TRẠNG THÁI
                |--------------------------------------------------------------------------
                */
                ->when(
                    filled($pushTrangThai),
                    function ($q) use ($pushTrangThai) {

                        $q->where(
                            'trang_thai',
                            $pushTrangThai
                        );

                    }
                )


                /*
                |--------------------------------------------------------------------------
                | LỌC NGƯỜI NHẬN
                |--------------------------------------------------------------------------
                */
                ->when(
                    filled($pushDoiTuongNhan),
                    function ($q) use ($pushDoiTuongNhan) {

                        $q->where(
                            'doi_tuong_nhan',
                            $pushDoiTuongNhan
                        );

                    }
                )


                /*
                |--------------------------------------------------------------------------
                | LỌC HẠNG THÀNH VIÊN
                |--------------------------------------------------------------------------
                |
                | Chỉ áp dụng khi người nhận là:
                | hang_thanh_vien
                |
                */
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


                /*
                |--------------------------------------------------------------------------
                | LỌC NGƯỜI DÙNG CỤ THỂ
                |--------------------------------------------------------------------------
                |
                | Tìm theo:
                | - ho_ten
                | - email
                |
                | thông qua:
                | thong_bao_push_nguoi_dungs
                |
                */
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


                /*
                |--------------------------------------------------------------------------
                | SẮP XẾP THEO NGÀY XÓA
                |--------------------------------------------------------------------------
                */
                ->latest('deleted_at')


                /*
                |--------------------------------------------------------------------------
                | PHÂN TRANG
                |--------------------------------------------------------------------------
                */
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
        | 3. TRẢ VIEW
        |--------------------------------------------------------------------------
        |
        | Có thêm:
        | - hangThanhVienOptions
        |
        | để Blade dùng khi cần.
        |
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

            $count =
                Phims::onlyTrashed()->count();

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

            $count =
                SuatChieu::onlyTrashed()->count();

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

            $count =
                NguoiDung::onlyTrashed()
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

            $staffs =
                NguoiDung::onlyTrashed()
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

            $count =
                $staffs->count();


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

            $count =
                ThongBaoPush::onlyTrashed()->count();

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
public function restore(Request $request, string $type, int $id)
{
    if ($type === 'thong_bao') {
        $thongBao = ThongBaoPush::onlyTrashed()->findOrFail($id);

        $thongBao->restore();

        return back()->with(
            'success',
            'Đã khôi phục thông báo thành công!'
        );
    }

    return back()->with('error', 'Loại dữ liệu không hợp lệ!');
}

public function forceDelete(Request $request, string $type, int $id)
{
    if ($type === 'thong_bao') {
        $thongBao = ThongBaoPush::onlyTrashed()->findOrFail($id);

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

    return back()->with('error', 'Loại dữ liệu không hợp lệ!');
}

    /**
     * =========================================================
     * XÓA VĨNH VIỄN / DỌN THÙNG RÁC
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

            $trashedMovies =
                Phims::onlyTrashed()->get();


            foreach ($trashedMovies as $phim) {

                /*
                | Không xóa phim nếu vẫn còn suất chiếu
                */
                if (
                    !$phim
                        ->showtimes()
                        ->exists()
                ) {

                    if (
                        method_exists(
                            $phim,
                            'genres'
                        )
                    ) {

                        $phim
                            ->genres()
                            ->detach();

                    }


                    $phim->forceDelete();

                    $count++;

                }

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

            $count =
                SuatChieu::onlyTrashed()
                    ->forceDelete();

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

            $count =
                NguoiDung::onlyTrashed()
                    ->where(
                        'vai_tro',
                        'khach_hang'
                    )
                    ->forceDelete();

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

            $trashedStaffs =
                NguoiDung::onlyTrashed()
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

                $daBanVe =
                    class_exists(VeXemPhim::class)
                        ? VeXemPhim::where(
                            'nhan_vien_id',
                            $staff->id
                        )->exists()
                        : false;


                $daTaoHoaDon =
                    class_exists(FoodInvoice::class)
                        ? FoodInvoice::where(
                            'user_id',
                            $staff->id
                        )->exists()
                        : false;


                /*
                | Chỉ xóa vĩnh viễn nếu không có
                | dữ liệu công việc liên quan.
                */
                if (
                    !$daBanVe
                    && !$daTaoHoaDon
                ) {

                    $staff->forceDelete();

                    $count++;

                }

            }

        }


        /*
        |--------------------------------------------------------------------------
        | THÔNG BÁO PUSH
        |--------------------------------------------------------------------------
        |
        | Chỉ tại đây mới xóa người nhận.
        |
        | Soft delete:
        |     ThongBaoPush -> deleted_at
        |
        | Force delete:
        |     Xóa ThongBaoPushNguoiDung
        |     + Xóa ThongBaoPush
        |
        */
        elseif (
            $type === 'thong_bao'
            && class_exists(ThongBaoPush::class)
        ) {

            $trashedNotifications =
                ThongBaoPush::onlyTrashed()
                    ->get();


            foreach (
                $trashedNotifications
                as $thongBao
            ) {

                /*
                |--------------------------------------------------------------------------
                | Xóa các bản ghi người nhận
                |--------------------------------------------------------------------------
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
                |--------------------------------------------------------------------------
                | Xóa thông báo vĩnh viễn
                |--------------------------------------------------------------------------
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


        return redirect()
            ->back()
            ->with(
                'success',
                "Đã dọn dẹp thành công {$count} bản ghi rác!"
            );
    }
}