<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Phims;
use App\Models\SuatChieu;
use App\Models\ThongBaoPush;
use App\Models\User;
use App\Traits\Loggable;
use App\Services\AdminNotificationService;
use Illuminate\Http\Request;

class ThungRacController extends Controller
{
    use Loggable;

    public function index(Request $request)
    {
        $tab = $request->query('tab', 'phim');
        $tuNgay = $request->query('tu_ngay');
        $denNgay = $request->query('den_ngay');
        $timKiem = $request->query('tim_kiem');

        // Tự động điều chỉnh nếu người dùng chọn Từ ngày > Đến ngày
        if (!empty($tuNgay) && !empty($denNgay) && $tuNgay > $denNgay) {
            $temp = $tuNgay;
            $tuNgay = $denNgay;
            $denNgay = $temp;
        }

        $applyFilters = function ($query) use ($tuNgay, $denNgay) {
            if (!empty($tuNgay)) {
                $query->whereDate('deleted_at', '>=', $tuNgay);
            }
            if (!empty($denNgay)) {
                $query->whereDate('deleted_at', '<=', $denNgay);
            }
            return $query;
        };

        $stats = [
            'phim'       => class_exists(Phims::class) ? $applyFilters(Phims::onlyTrashed())->count() : 0,
            'suat_chieu' => class_exists(SuatChieu::class) ? $applyFilters(SuatChieu::onlyTrashed())->count() : 0,
            'khach_hang' => class_exists(User::class) ? $applyFilters(User::onlyTrashed()->where('vai_tro', 'khach_hang'))->count() : 0,
            'nhan_vien'  => class_exists(User::class) ? $applyFilters(User::onlyTrashed()->whereIn('vai_tro', ['admin', 'nhan_vien', 'quan_ly', 'super_admin']))->count() : 0,
            'thong_bao'  => class_exists(ThongBaoPush::class) ? $applyFilters(ThongBaoPush::onlyTrashed())->count() : 0,
        ];

        $totalTrash = array_sum($stats);
        $items = collect();

        if ($tab === 'phim' && class_exists(Phims::class)) {
            $query = Phims::onlyTrashed()->with(['country', 'genres', 'showtimes']);
            $applyFilters($query);
            if (!empty($timKiem)) {
                $query->where('ten_phim', 'like', '%' . $timKiem . '%');
            }
            $items = $query->latest('deleted_at')->paginate(10)->withQueryString();

        } elseif ($tab === 'suat_chieu' && class_exists(SuatChieu::class)) {
            $query = SuatChieu::onlyTrashed()->with(['phim', 'phongChieu']);
            $applyFilters($query);
            if (!empty($timKiem)) {
                $query->whereHas('phim', function($q) use ($timKiem) {
                    $q->where('ten_phim', 'like', '%' . $timKiem . '%');
                });
            }
            $items = $query->latest('deleted_at')->paginate(10)->withQueryString();

        } elseif ($tab === 'khach_hang' && class_exists(User::class)) {
            $query = User::onlyTrashed()->where('vai_tro', 'khach_hang');
            $applyFilters($query);
            if (!empty($timKiem)) {
                $query->where(function($q) use ($timKiem) {
                    $q->where('ho_ten', 'like', '%' . $timKiem . '%')
                      ->orWhere('name', 'like', '%' . $timKiem . '%')
                      ->orWhere('email', 'like', '%' . $timKiem . '%')
                      ->orWhere('so_dien_thoai', 'like', '%' . $timKiem . '%');
                });
            }
            $items = $query->latest('deleted_at')->paginate(10)->withQueryString();

        } elseif ($tab === 'nhan_vien' && class_exists(User::class)) {
            $query = User::onlyTrashed()->whereIn('vai_tro', ['admin', 'nhan_vien', 'quan_ly', 'super_admin']);
            $applyFilters($query);
            if (!empty($timKiem)) {
                $query->where(function($q) use ($timKiem) {
                    $q->where('ho_ten', 'like', '%' . $timKiem . '%')
                      ->orWhere('name', 'like', '%' . $timKiem . '%')
                      ->orWhere('email', 'like', '%' . $timKiem . '%');
                });
            }
            $items = $query->latest('deleted_at')->paginate(10)->withQueryString();

        } elseif ($tab === 'thong_bao' && class_exists(ThongBaoPush::class)) {
            $query = ThongBaoPush::onlyTrashed();
            $applyFilters($query);
            if (!empty($timKiem)) {
                $query->where('tieu_de', 'like', '%' . $timKiem . '%');
            }
            $items = $query->latest('deleted_at')->paginate(10)->withQueryString();
        }

        return view('admin.thung_rac.index', compact('stats', 'totalTrash', 'tab', 'items', 'tuNgay', 'denNgay', 'timKiem'));
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
        } elseif ($type === 'khach_hang' && class_exists(User::class)) {
            $count = User::onlyTrashed()->where('vai_tro', 'khach_hang')->count();
            User::onlyTrashed()->where('vai_tro', 'khach_hang')->restore();
        } elseif ($type === 'nhan_vien' && class_exists(User::class)) {
            $count = User::onlyTrashed()->whereIn('vai_tro', ['admin', 'nhan_vien', 'quan_ly', 'super_admin'])->count();
            User::onlyTrashed()->whereIn('vai_tro', ['admin', 'nhan_vien', 'quan_ly', 'super_admin'])->restore();
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
        } elseif ($type === 'khach_hang' && class_exists(User::class)) {
            $count = User::onlyTrashed()->where('vai_tro', 'khach_hang')->forceDelete();
        } elseif ($type === 'nhan_vien' && class_exists(User::class)) {
            $count = User::onlyTrashed()->whereIn('vai_tro', ['admin', 'nhan_vien', 'quan_ly', 'super_admin'])->forceDelete();
        } elseif ($type === 'thong_bao' && class_exists(ThongBaoPush::class)) {
            $count = ThongBaoPush::onlyTrashed()->forceDelete();
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