<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Showtime;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MovieController extends Controller
{
    public function home()
    {
        $movies = Movie::with('showtimes')
            ->latest()
            ->get();

        $nowShowingMovies = $movies->filter(
            fn($movie) => $movie->schedule_status === 'Đang chiếu'
        );

        $comingSoonMovies = $movies->filter(
            fn($movie) => $movie->schedule_status === 'Sắp chiếu'
        );

        $comingLaterMovies = $movies->filter(
            fn($movie) => $movie->schedule_status === 'Sắp ra mắt'
        );

        $bannerMovies = $nowShowingMovies->take(5);

        return view('user.home', compact(
            'bannerMovies',
            'nowShowingMovies',
            'comingSoonMovies',
            'comingLaterMovies'
        ));
    }
    public function index(Request $request)
    {
        $movies = Movie::with('showtimes')
            ->latest()
            ->get();

        // SEARCH
        if ($request->filled('keyword')) {

            $movies = $movies->filter(function ($movie) use ($request) {

                return str_contains(
                    strtolower($movie->title),
                    strtolower($request->keyword)
                );
            });
        }

        // GENRE
        if ($request->filled('genre')) {

            $movies = $movies->where('genre', $request->genre);
        }

        // COUNTRY
        if ($request->filled('country')) {

            $movies = $movies->where('country', $request->country);
        }

        // STATUS
        if ($request->filled('status')) {

            // ĐANG CHIẾU
            if ($request->status === 'now_showing') {

                $movies = $movies->filter(
                    fn($movie) => $movie->schedule_status === 'Đang chiếu'
                );
            }

            // SẮP CHIẾU
            if ($request->status === 'coming_soon') {

                $movies = $movies->filter(
                    fn($movie) => $movie->schedule_status === 'Sắp chiếu'
                );
            }

            // SẮP RA MẮT
            if ($request->status === 'coming_later') {

                $movies = $movies->filter(
                    fn($movie) => $movie->schedule_status === 'Sắp ra mắt'
                );
            }
        }

        $genres = Movie::select('genre')
            ->whereNotNull('genre')
            ->distinct()
            ->pluck('genre');

        $countries = Movie::select('country')
            ->whereNotNull('country')
            ->distinct()
            ->pluck('country');

        return view('user.movies.index', compact(
            'movies',
            'genres',
            'countries'
        ));
    }

    public function show(Movie $movie)
    {
        $today = Carbon::today('Asia/Ho_Chi_Minh');
        $now = Carbon::now('Asia/Ho_Chi_Minh');


        $showtimes = Showtime::with(['cinema', 'movie'])
            ->where('movie_id', $movie->id)
            ->whereRaw(
                "DATE_ADD(
                    STR_TO_DATE(CONCAT(show_date, ' ', show_time), '%Y-%m-%d %H:%i:%s'),
                    INTERVAL ? MINUTE
                ) >= ?",
                [(int) $movie->duration, $now->format('Y-m-d H:i:s')]
            )
            ->orderBy('show_date')
            ->orderBy('show_time')
            ->get();

        return view('user.movies.show', compact('movie', 'showtimes', 'now'));
    }
}
