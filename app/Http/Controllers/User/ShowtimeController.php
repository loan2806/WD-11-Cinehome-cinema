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
        $today = Carbon::today('Asia/Ho_Chi_Minh');
        $limitDay = $today->copy()->addDays(10);

        $cinemas = Cinema::orderBy('name')->get();

        $movies = Movie::whereDate('release_date', '>=', $today)
            ->whereDate('release_date', '<=', $limitDay)
            ->orderBy('title')
            ->get();

        $showtimes = Showtime::with(['movie', 'cinema'])
            ->whereHas('movie', function ($movieQuery) use ($today, $limitDay, $request) {
                $movieQuery->whereDate('release_date', '>=', $today)
                    ->whereDate('release_date', '<=', $limitDay);

                if ($request->status === 'now_showing') {
                    $movieQuery->whereDate('release_date', $today);
                }

                if ($request->status === 'coming_soon') {
                    $movieQuery->whereDate('release_date', '>', $today)
                        ->whereDate('release_date', '<=', $limitDay);
                }
            })
            ->whereRaw(
                "DATE_ADD(
                    STR_TO_DATE(CONCAT(show_date, ' ', show_time), '%Y-%m-%d %H:%i:%s'),
                    INTERVAL (
                        SELECT duration 
                        FROM movies 
                        WHERE movies.id = showtimes.movie_id
                    ) MINUTE
                ) >= ?",
                [$now->format('Y-m-d H:i:s')]
            )
            ->when($request->cinema_id, function ($query) use ($request) {
                $query->where('cinema_id', $request->cinema_id);
            })
            ->when($request->movie_id, function ($query) use ($request) {
                $query->where('movie_id', $request->movie_id);
            })
            ->when($request->show_date, function ($query) use ($request) {
                $query->whereDate('show_date', $request->show_date);
            })
            ->orderBy('show_date')
            ->orderBy('show_time')
            ->get();

        return view('user.showtimes.index', compact(
            'showtimes',
            'cinemas',
            'movies',
            'now'
        ));
    }

    public function show(Showtime $showtime)
    {
        $showtime->load(['movie', 'cinema']);

        return view('user.showtimes.show', compact('showtime'));
    }
}