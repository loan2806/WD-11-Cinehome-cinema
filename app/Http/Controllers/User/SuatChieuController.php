<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\RapChieuPhim;
use App\Models\Phims;
use App\Models\SuatChieu;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SuatChieuController extends Controller
{
    public function index(Request $request)
    {
        $now = Carbon::now('Asia/Ho_Chi_Minh');
        $today = Carbon::today('Asia/Ho_Chi_Minh');
        $limitDay = $today->copy()->addDays(10);

        $rapChieuPhims = RapChieuPhim::orderBy('ten_rap')->get();

        $movies = Phims::whereHas('showtimes', function ($query) use ($now) {
                $query->where('thoi_gian_chieu', '>=', $now);
            })
            ->orderBy('ten_phim')
            ->get();

        // Truy vấn danh sách suất chiếu theo các tham số đầu vào thuần Việt
        $suatChieus = SuatChieu::with(['phim', 'rapChieuPhim'])
            ->whereHas('phim', function ($movieQuery) use ($today, $limitDay, $request) {
                if ($request->trang_thai === 'dang_chieu') {
                    $movieQuery->whereDate('ngay_khoi_chieu', '<=', $today);
                }

                if ($request->trang_thai === 'sap_chieu') {
                    $movieQuery->whereDate('ngay_khoi_chieu', '>', $today)
                        ->whereDate('ngay_khoi_chieu', '<=', $limitDay);
                }
            })
            ->whereRaw(
                "DATE_ADD(thoi_gian_chieu, INTERVAL (
                    SELECT thoi_luong 
                    FROM phims 
                    WHERE phims.id = suat_chieus.phim_id
                ) MINUTE) >= ?",
                [$now->format('Y-m-d H:i:s')]
            )
            ->when($request->rap_chieu_phim_id, function ($query) use ($request) {
                $query->where('rap_chieu_phim_id', $request->rap_chieu_phim_id);
            })
            ->when($request->phim_id, function ($query) use ($request) {
                $query->where('phim_id', $request->phim_id);
            })
            ->when($request->ngay_chieu, function ($query) use ($request) {
                $query->whereDate('thoi_gian_chieu', $request->ngay_chieu);
            })
            ->orderBy('thoi_gian_chieu')
            ->get();

        return view('user.showtimes.index', compact(
            'suatChieus',
            'rapChieuPhims',
            'movies',
            'now'
        ));
    }

    public function show(SuatChieu $suatChieu)
    {
        $suatChieu->load(['phim', 'rapChieuPhim']);

        return view('user.showtimes.show', compact('suatChieu'));
    }
}
