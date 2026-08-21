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

class ThungRacController extends Controller
{
    use Loggable;

    public function index(Request $request)
    {
        $tab = $request->get('tab', 'suat_chieu');
        $search = $request->get('tim_kiem', $request->get('search'));
        $tuNgay = $request->get('tu_ngay');
        $denNgay = $request->get('den_ngay');

        // 1. Thống kê số lượng bản ghi trong thùng rác cho từng Tab
        $stats = [
            'phim'       => Phims::onlyTrashed()->count(),
            'suat_chieu' => SuatChieu::onlyTrashed()->count(),
            'khach_hang' => NguoiDung::onlyTrashed()->where('vai_tro', 'khach_hang')->count(),
            'nhan_vien'  => NguoiDung::onlyTrashed()->whereIn('vai_tro', ['admin', 'nhan_vien', 'quan_ly', 'super_admin'])->count(),
            'thong_bao'  => ThongBaoPush::onlyTrashed()->count(),
        ];

        $totalTrash = array_sum($stats);

        // 2. Tải dữ liệu phân trang theo Tab được chọn
        $items = match ($tab) {
            'phim' => Phims::onlyTrashed()
                ->when($search, fn($q) => $q->where('ten_phim', 'like', "%{$search}%"))
                ->when($tuNgay, fn($q) => $q->whereDate('deleted_at', '>=', $tuNgay))
                ->when($denNgay, fn($q) => $q->whereDate('deleted_at', '<=', $denNgay))
                ->latest('deleted_at')
                ->paginate(10)
                ->withQueryString(),

            'suat_chieu' => SuatChieu::onlyTrashed()
                ->with(['phim', 'phongChieu'])
                ->when($search, function ($q) use ($search) {
                    $q->whereHas('phim', fn($p) => $p->where('ten_phim', 'like', "%{$search}%"));
                })
                ->when($tuNgay, fn($q) => $q->whereDate('deleted_at', '>=', $tuNgay))
                ->when($denNgay, fn($q) => $q->whereDate('deleted_at', '<=', $denNgay))
                ->latest('deleted_at')
                ->paginate(10)
                ->withQueryString(),

            'khach_hang' => NguoiDung::onlyTrashed()
                ->where('vai_tro', 'khach_hang')
                ->when($search, fn($q) => $q->where(fn($sub) => $sub->where('ho_ten', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))
                ->when($tuNgay, fn($q) => $q->whereDate('deleted_at', '>=', $tuNgay))
                ->when($denNgay, fn($q) => $q->whereDate('deleted_at', '<=', $denNgay))
                ->latest('deleted_at')
                ->paginate(10)
                ->withQueryString(),

            'nhan_vien' => NguoiDung::onlyTrashed()
                ->whereIn('vai_tro', ['admin', 'nhan_vien', 'quan_ly', 'super_admin'])
                ->when($search, fn($q) => $q->where(fn($sub) => $sub->where('ho_ten', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))
                ->when($tuNgay, fn($q) => $q->whereDate('deleted_at', '>=', $tuNgay))
                ->when($denNgay, fn($q) => $q->whereDate('deleted_at', '<=', $denNgay))
                ->latest('deleted_at')
                ->paginate(10)
                ->withQueryString(),

            'thong_bao' => ThongBaoPush::onlyTrashed()
                ->when($search, fn($q) => $q->where('tieu_de', 'like', "%{$search}%"))
                ->when($tuNgay, fn($q) => $q->whereDate('deleted_at', '>=', $tuNgay))
                ->when($denNgay, fn($q) => $q->whereDate('deleted_at', '<=', $denNgay))
                ->latest('deleted_at')
                ->paginate(10)
                ->withQueryString(),

            default => SuatChieu::onlyTrashed()->paginate(10)->withQueryString(),
        };

        return view('admin.thung_rac.index', compact('tab', 'stats', 'totalTrash', 'items'));
    }

    public function restoreAll(Request $request, string $type)
    {
        $count = 0;

        if ($type === 'phim' && class_exists(Phims::class)) {
            $count = Phims::onlyTrashed()->count();
            Phims::onlyTrashed()->restore();
        } elseif ($type === 'suat_chieu' && class_exists(SuatChieu::class)) {
            $count = SuatChieu::onlyTrashed()->count();
            SuatChieu::onlyTrashed()->restore();
        } elseif ($type === 'khach_hang' && class_exists(NguoiDung::class)) {
            $count = NguoiDung::onlyTrashed()->where('vai_tro', 'khach_hang')->count();
            NguoiDung::onlyTrashed()->where('vai_tro', 'khach_hang')->restore();
        } elseif ($type === 'nhan_vien' && class_exists(NguoiDung::class)) {
            $staffs = NguoiDung::onlyTrashed()->whereIn('vai_tro', ['admin', 'nhan_vien', 'quan_ly', 'super_admin'])->get();
            $count = $staffs->count();
            foreach ($staffs as $staff) {
                $staff->restore();
                $staff->update(['trang_thai_hoat_dong' => true]);
            }
        } elseif ($type === 'thong_bao' && class_exists(ThongBaoPush::class)) {
            $count = ThongBaoPush::onlyTrashed()->count();
            ThongBaoPush::onlyTrashed()->restore();
        }

        $this->ghiNhatKy(
            $request,
            'Khôi phục tất cả thùng rác',
            'Quản trị hệ thống',
            "Đã khôi phục toàn bộ {$count} bản ghi thuộc danh mục {$type}"
        );

        AdminNotificationService::push(
            '♻️ Đã khôi phục dữ liệu',
            "Đã khôi phục tất cả {$count} bản ghi rác thuộc mục {$type}",
            'Success'
        );

        return redirect()->back()->with('success', "Đã khôi phục thành công tất cả {$count} bản ghi!");
    }

    public function emptyTrash(Request $request, string $type)
    {
        $count = 0;

        if ($type === 'phim' && class_exists(Phims::class)) {
            $trashedMovies = Phims::onlyTrashed()->get();
            foreach ($trashedMovies as $phim) {
                if (!$phim->showtimes()->exists()) {
                    if (method_exists($phim, 'genres')) {
                        $phim->genres()->detach();
                    }
                    $phim->forceDelete();
                    $count++;
                }
            }
        } elseif ($type === 'suat_chieu' && class_exists(SuatChieu::class)) {
            $count = SuatChieu::onlyTrashed()->forceDelete();
        } elseif ($type === 'khach_hang' && class_exists(NguoiDung::class)) {
            $count = NguoiDung::onlyTrashed()->where('vai_tro', 'khach_hang')->forceDelete();
        } elseif ($type === 'nhan_vien' && class_exists(NguoiDung::class)) {
            $trashedStaffs = NguoiDung::onlyTrashed()->whereIn('vai_tro', ['admin', 'nhan_vien', 'quan_ly', 'super_admin'])->get();
            foreach ($trashedStaffs as $staff) {
                $daBanVe = class_exists(VeXemPhim::class) ? VeXemPhim::where('nhan_vien_id', $staff->id)->exists() : false;
                $daTaoHoaDon = class_exists(FoodInvoice::class) ? FoodInvoice::where('user_id', $staff->id)->exists() : false;

                if (!$daBanVe && !$daTaoHoaDon) {
                    $staff->forceDelete();
                    $count++;
                }
            }
        } elseif ($type === 'thong_bao' && class_exists(ThongBaoPush::class)) {
            $trashedNotifications = ThongBaoPush::onlyTrashed()->get();
            foreach ($trashedNotifications as $tb) {
                if (class_exists(ThongBaoPushNguoiDung::class)) {
                    ThongBaoPushNguoiDung::where('thong_bao_push_id', $tb->id)->delete();
                }
                $tb->forceDelete();
                $count++;
            }
        }

        $this->ghiNhatKy(
            $request,
            'Dọn dẹp thùng rác',
            'Quản trị hệ thống',
            "Đã xóa vĩnh viễn {$count} bản ghi rác thuộc danh mục {$type}"
        );

        AdminNotificationService::push(
            '🧹 Đã dọn dẹp thùng rác',
            "Đã xóa vĩnh viễn {$count} bản ghi rác thuộc mục {$type}",
            'Warning'
        );

        return redirect()->back()->with('success', "Đã dọn dẹp thành công {$count} bản ghi rác!");
    }
}