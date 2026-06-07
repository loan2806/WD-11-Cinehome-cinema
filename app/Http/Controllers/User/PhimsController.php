<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Phims;
use App\Models\TheLoai;
use App\Models\QuocGia;
use App\Models\SuatChieu;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PhimsController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | HOME PAGE
    |--------------------------------------------------------------------------
    */
    public function home()
    {
        $movies = Phims::with([
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
        $query = Phims::with([
            'showtimes',
            'genres',
            'country'
        ])->visibleToUsers();

        // SEARCH
        if ($request->filled('tim_kiem')) {

            $query->where(
                'ten_phim',
                'like',
                '%' . $request->tim_kiem . '%'
            );
        }


        if ($request->filled('the_loai')) {

            $query->whereHas('genres', function ($q) use ($request) {

                $q->where(
                    'ten_the_loai',
                    $request->the_loai
                );
            });
        }


        if ($request->filled('quoc_gia')) {

            $query->whereHas('country', function ($q) use ($request) {

                $q->where(
                    'ten_quoc_gia',
                    $request->quoc_gia
                );
            });
        }

        $movies = $query
            ->where('schedule_status', '!=', 'Đã kết thúc')
            ->orderBy('created_at', 'desc')
            ->get();

        $genres = TheLoai::where('trang_thai', 1)->get();
        $countries = QuocGia::where('trang_thai', 1)->get();

        return view('user.phims.index', compact(
            'movies',
            'genres',
            'countries'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | MOVIE DETAIL
    |--------------------------------------------------------------------------
    */
    public function show(Phims $movie)
    {
        $now = Carbon::now('Asia/Ho_Chi_Minh');

        /*
        |--------------------------------------------------------------------------
        | SHOWTIMES
        |--------------------------------------------------------------------------
        */
        $showtimes = SuatChieu::with([
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
            'user.phims.show',
            compact(
                'movie',
                'showtimes',
                'now'
            )
        );
    }
}
