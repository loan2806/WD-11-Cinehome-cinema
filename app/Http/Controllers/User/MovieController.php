<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Showtime;
use App\Models\Genre;
use App\Models\Country;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MovieController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | HOME PAGE
    |--------------------------------------------------------------------------
    */
    public function home()
    {
        $movies = Movie::with([
            'showtimes',
            'genres',
            'country'
        ])
        ->visibleToUsers()
        ->orderBy('created_at', 'desc')
        ->get();

        /*
        |--------------------------------------------------------------------------
        | NOW SHOWING
        |--------------------------------------------------------------------------
        */
        $nowShowingMovies = $movies->filter(
            fn($movie)
                => $movie->schedule_status === 'Đang chiếu'
        );

        /*
        |--------------------------------------------------------------------------
        | COMING SOON
        |--------------------------------------------------------------------------
        */
        $comingSoonMovies = $movies->filter(
            fn($movie)
                => $movie->schedule_status === 'Sắp chiếu'
        );

        /*
        |--------------------------------------------------------------------------
        | COMING LATER
        |--------------------------------------------------------------------------
        */
        $comingLaterMovies = $movies->filter(
            fn($movie)
                => $movie->schedule_status === 'Sắp ra mắt'
        );

        /*
        |--------------------------------------------------------------------------
        | BANNER
        |--------------------------------------------------------------------------
        */
        $bannerMovies = $nowShowingMovies->take(5);

        return view(
            'user.home',
            compact(
                'bannerMovies',
                'nowShowingMovies',
                'comingSoonMovies',
                'comingLaterMovies'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | MOVIE LIST
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = Movie::with([
            'showtimes',
            'genres',
            'country'
        ])
        ->visibleToUsers();

        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */
        if ($request->filled('keyword')) {

            $query->where(
                'ten_phim',
                'like',
                '%' . $request->keyword . '%'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER GENRE
        |--------------------------------------------------------------------------
        */
        if ($request->filled('genre_id')) {

            $query->whereHas(
                'genres',
                function ($q) use ($request) {

                    $q->where(
                        'genre_id',
                        $request->genre_id
                    );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER COUNTRY
        |--------------------------------------------------------------------------
        */
        if ($request->filled('quoc_gia_id')) {

            $query->where(
                'quoc_gia_id',
                $request->quoc_gia_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | GET MOVIES
        |--------------------------------------------------------------------------
        */
        $movies = $query
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(
                fn($movie) => $movie->schedule_status !== 'Đã kết thúc'
            );

        /*
        |--------------------------------------------------------------------------
        | FILTER STATUS
        |--------------------------------------------------------------------------
        */
        if ($request->filled('status')) {

            if ($request->status === 'now_showing') {

                $movies = $movies->filter(
                    fn($movie)
                        => $movie->schedule_status === 'Đang chiếu'
                );
            }

            elseif ($request->status === 'coming_soon') {

                $movies = $movies->filter(
                    fn($movie)
                        => $movie->schedule_status === 'Sắp chiếu'
                );
            }

            elseif ($request->status === 'coming_later') {

                $movies = $movies->filter(
                    fn($movie)
                        => $movie->schedule_status === 'Sắp ra mắt'
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | DROPDOWN DATA
        |--------------------------------------------------------------------------
        */
        $genres = Genre::where(
            'trang_thai',
            1
        )->get();

        $countries = Country::where(
            'trang_thai',
            1
        )->get();

        return view(
            'user.movies.index',
            compact(
                'movies',
                'genres',
                'countries'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | MOVIE DETAIL
    |--------------------------------------------------------------------------
    */
    public function show(Movie $movie)
    {
        $now = Carbon::now('Asia/Ho_Chi_Minh');

        /*
        |--------------------------------------------------------------------------
        | SHOWTIMES
        |--------------------------------------------------------------------------
        */
        $showtimes = Showtime::with([
            'cinema',
            'movie'
        ])
        ->where('movie_id', $movie->id)
        ->orderBy('show_date')
        ->orderBy('show_time')
        ->get()
        ->filter(function ($showtime) use (
            $movie,
            $now
        ) {

            /*
            |--------------------------------------------------------------------------
            | FIX DATE + TIME
            |--------------------------------------------------------------------------
            */
            $date = Carbon::parse(
                $showtime->show_date
            )->format('Y-m-d');

            /*
            |--------------------------------------------------------------------------
            | START TIME
            |--------------------------------------------------------------------------
            */
            $start = Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $date . ' ' . $showtime->show_time
            );

            /*
            |--------------------------------------------------------------------------
            | END TIME
            |--------------------------------------------------------------------------
            */
            $end = $start
                ->copy()
                ->addMinutes(
                    (int) $movie->thoi_luong
                );

            /*
            |--------------------------------------------------------------------------
            | ONLY SHOW NOT ENDED
            |--------------------------------------------------------------------------
            */
            return $end->gte($now);
        });

        return view(
            'user.movies.show',
            compact(
                'movie',
                'showtimes',
                'now'
            )
        );
    }
}