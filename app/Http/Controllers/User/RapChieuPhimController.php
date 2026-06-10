<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\RapChieuPhim;
use App\Models\SuatChieu;
use Carbon\Carbon;

class RapChieuPhimController extends Controller
{
    public function index()
    {
        $rapChieuPhims = RapChieuPhim::latest()->get();
        $cinemas = $rapChieuPhims;

        return view('user.cinemas.index', compact('rapChieuPhims', 'cinemas'));
    }

    public function show(RapChieuPhim $rapChieuPhim)
    {
        $now = Carbon::now('Asia/Ho_Chi_Minh');
        $today = $now->toDateString();

        $suatChieusHomNay = SuatChieu::where('rap_chieu_phim_id', $rapChieuPhim->id)
            ->whereDate('thoi_gian_chieu', $today)

            // Không lấy suất chiếu trước ngày khởi chiếu phim
            ->whereRaw(
                "thoi_gian_chieu >= (
                    SELECT ngay_khoi_chieu
                    FROM phims
                    WHERE phims.id = suat_chieus.phim_id
                )"
            )

            // Chỉ lấy phim đã tới ngày khởi chiếu
            ->whereHas('phim', function ($query) use ($today) {
                $query->whereDate('ngay_khoi_chieu', '<=', $today);
            })

            // Không lấy suất chiếu đã kết thúc (thoi_gian_chieu + thoi_luong >= hien_tai)
            ->whereRaw(
                "DATE_ADD(thoi_gian_chieu, INTERVAL (
                    SELECT thoi_luong
                    FROM phims
                    WHERE phims.id = suat_chieus.phim_id
                ) MINUTE) >= ?",
                [$now->format('Y-m-d H:i:s')]
            );

        $showtimeCount = (clone $suatChieusHomNay)->count();

        $movieCount = (clone $suatChieusHomNay)
            ->select('phim_id')
            ->distinct()
            ->count();

        return view('user.cinemas.show', compact(
            'rapChieuPhim',
            'showtimeCount',
            'movieCount'
        ));
    }
}
