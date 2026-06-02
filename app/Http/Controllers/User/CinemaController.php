<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use App\Models\Showtime;
use Carbon\Carbon;

class CinemaController extends Controller
{
    public function index()
    {
        $cinemas = Cinema::latest()->get();

        return view('user.cinemas.index', compact('cinemas'));
    }

    public function show(Cinema $cinema)
    {
        $now = Carbon::now('Asia/Ho_Chi_Minh');
        $today = $now->toDateString();

        $todayShowtimes = Showtime::where('cinema_id', $cinema->id)
            ->whereDate('show_date', $today)

            // Không lấy suất chiếu trước ngày khởi chiếu phim
            ->whereRaw(
                "show_date >= (
                    SELECT release_date
                    FROM phims
                    WHERE phims.id = showtimes.movie_id
                )"
            )

            // Chỉ lấy phim đã tới ngày khởi chiếu
            ->whereHas('movie', function ($query) use ($today) {
                $query->whereDate('release_date', '<=', $today);
            })

            // Không lấy suất chiếu đã kết thúc
            ->whereRaw(
                "DATE_ADD(
                    STR_TO_DATE(CONCAT(show_date, ' ', show_time), '%Y-%m-%d %H:%i:%s'),
                    INTERVAL (
                        SELECT duration
                        FROM phims
                        WHERE phims.id = showtimes.movie_id
                    ) MINUTE
                ) >= ?",
                [$now->format('Y-m-d H:i:s')]
            );

        $showtimeCount = (clone $todayShowtimes)->count();

        $movieCount = (clone $todayShowtimes)
            ->select('movie_id')
            ->distinct()
            ->count();

        return view('user.cinemas.show', compact(
            'cinema',
            'showtimeCount',
            'movieCount'
        ));
    }
}
