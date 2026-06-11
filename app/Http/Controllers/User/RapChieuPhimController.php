<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Phims;
use App\Models\RapChieuPhim;
use App\Models\SuatChieu;
use Carbon\Carbon;

class RapChieuPhimController extends Controller
{
    public function index()
    {
        $rapChieuPhim = RapChieuPhim::query()->latest()->first();

        abort_if(!$rapChieuPhim, 404, 'Chưa có dữ liệu rạp.');

        $now = Carbon::now('Asia/Ho_Chi_Minh');
        $today = $now->toDateString();

        $suatChieusHomNay = SuatChieu::query()
            ->where('rap_chieu_phim_id', $rapChieuPhim->id)
            ->whereDate('thoi_gian_chieu', $today)
            ->where('thoi_gian_chieu', '>=', $now->format('Y-m-d H:i:s'))
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

        $hotMovies = Phims::with(['genres', 'country', 'showtimes'])
            ->visibleToUsers()
            ->orderByDesc('created_at')
            ->take(4)
            ->get();

        return view('user.cinemas.index', compact(
            'rapChieuPhim',
            'showtimeCount',
            'movieCount',
            'hotMovies'
        ));
    }

    public function show(RapChieuPhim $rapChieuPhim)
    {
        return redirect()->route('user.cinemas.index');
    }
}