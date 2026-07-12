<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Phims;
use App\Models\RapChieuPhim;
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
        $weekdayShort = [
            'Sun' => 'CN',
            'Mon' => 'T2',
            'Tue' => 'T3',
            'Wed' => 'T4',
            'Thu' => 'T5',
            'Fri' => 'T6',
            'Sat' => 'T7',
        ];

        $dateOptions = collect(range(0, 10))->map(function ($offset) use ($today, $weekdayShort) {
            $date = $today->copy()->addDays($offset);

            return [
                'value' => $date->toDateString(),
                'label' => $offset === 0
                    ? 'Hôm nay'
                    : ($weekdayShort[$date->format('D')] ?? $date->format('D')),
                'day' => $date->format('d/m'),
                'active' => request('ngay_chieu') === $date->toDateString(),
            ];
        });

        // Danh sách phim có suất chiếu trong 10 ngày tới
        $movies = Phims::whereHas('showtimes', function ($q) use ($today, $limitDay) {
                $q->whereDate('thoi_gian_chieu', '>=', $today)
                    ->whereDate('thoi_gian_chieu', '<=', $limitDay);
            })
            ->orderBy('ten_phim')
            ->get();

        $suatChieusQuery = SuatChieu::with(['phim.genres', 'rapChieuPhim', 'phongChieu'])
            ->whereHas('phim', function ($movieQuery) use ($today, $limitDay) {
                $movieQuery->whereHas('showtimes', function ($q) use ($today, $limitDay) {
                    $q->whereDate('thoi_gian_chieu', '>=', $today)
                        ->whereDate('thoi_gian_chieu', '<=', $limitDay);
                });
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
            });

        // Lọc theo trạng thái
        if ($request->trang_thai === 'dang_chieu') {
            $suatChieusQuery->whereDate('thoi_gian_chieu', $today);
        } elseif ($request->trang_thai === 'sap_chieu') {
            $suatChieusQuery->whereDate('thoi_gian_chieu', '>', $today)
                ->whereDate('thoi_gian_chieu', '<=', $limitDay);
        }

        $suatChieus = $suatChieusQuery
            ->orderBy('thoi_gian_chieu')
            ->get();

        return view('user.showtimes.index', compact(
            'suatChieus',
            'rapChieuPhims',
            'movies',
            'now',
            'dateOptions'
        ));
    }

    public function show(SuatChieu $suatChieu)
    {
        $suatChieu->load(['phim.genres', 'rapChieuPhim', 'phongChieu']);

        return view('user.showtimes.show', compact('suatChieu'));
    }
}
