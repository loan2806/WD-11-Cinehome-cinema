<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Showtime;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ShowtimeController extends Controller
{
    public function index(Request $request)
    {
        $now = Carbon::now('Asia/Ho_Chi_Minh');

        /*
        |--------------------------------------------------------------------------
        | CINEMAS
        |--------------------------------------------------------------------------
        */
        $cinemas = Cinema::orderBy('name')->get();

        /*
        |--------------------------------------------------------------------------
        | MOVIES
        |--------------------------------------------------------------------------
        */
        $movies = Movie::visibleToUsers()
            ->orderBy('ten_phim')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | SHOWTIMES
        |--------------------------------------------------------------------------
        */
        $showtimes = Showtime::with([
                'movie',
                'cinema'
            ])

            /*
            |--------------------------------------------------------------------------
            | CHỈ LẤY SUẤT CHƯA KẾT THÚC
            |--------------------------------------------------------------------------
            */
            ->whereRaw(
                "
                DATE_ADD(
                    STR_TO_DATE(
                        CONCAT(show_date, ' ', show_time),
                        '%Y-%m-%d %H:%i:%s'
                    ),
                    INTERVAL (
                        SELECT thoi_luong
                        FROM movies
                        WHERE movies.id = showtimes.movie_id
                    ) MINUTE
                ) >= ?
                ",
                [$now->format('Y-m-d H:i:s')]
            )

            /*
            |--------------------------------------------------------------------------
            | FILTER CINEMA
            |--------------------------------------------------------------------------
            */
            ->when($request->cinema_id, function ($query) use ($request) {

                $query->where(
                    'cinema_id',
                    $request->cinema_id
                );
            })

            /*
            |--------------------------------------------------------------------------
            | FILTER MOVIE
            |--------------------------------------------------------------------------
            */
            ->when($request->movie_id, function ($query) use ($request) {

                $query->where(
                    'movie_id',
                    $request->movie_id
                );
            })

            /*
            |--------------------------------------------------------------------------
            | FILTER DATE
            |--------------------------------------------------------------------------
            */
            ->when($request->show_date, function ($query) use ($request) {

                $query->whereDate(
                    'show_date',
                    $request->show_date
                );
            })

            /*
            |--------------------------------------------------------------------------
            | ORDER
            |--------------------------------------------------------------------------
            */
            ->orderBy('show_date')

            ->orderBy('show_time')

            ->get();

        return view(
            'user.showtimes.index',
            compact(
                'showtimes',
                'cinemas',
                'movies',
                'now'
            )
        );
    }

    public function show(Showtime $showtime)
    {
        $showtime->load([
            'movie',
            'cinema'
        ]);

        return view(
            'user.showtimes.show',
            compact('showtime')
        );
    }
}