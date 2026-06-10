<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Phims;
use App\Models\SuatChieu;
use App\Models\TheLoai;
use App\Models\QuocGia;
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

            // Tam thoi bo loc the loai vi bang trung gian movie_genre chua co migration.
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
        $genres = TheLoai::where(
            'trang_thai',
            1
        )->get();

        $countries = QuocGia::where(
            'trang_thai',
            1
        )->get();

        return view(
            'user.phims.index',
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
    public function show(Phims $movie)
    {
        $now = Carbon::now('Asia/Ho_Chi_Minh');

        /*
        |--------------------------------------------------------------------------
        | SHOWTIMES
        |--------------------------------------------------------------------------
        */
        $showtimes = SuatChieu::with(['phim', 'rapChieuPhim'])
            ->where('phim_id', $movie->id)
            ->whereRaw(
                "DATE_ADD(thoi_gian_chieu, INTERVAL ? MINUTE) >= ?",
                [(int) $movie->thoi_luong, $now->format('Y-m-d H:i:s')]
            )
            ->orderBy('thoi_gian_chieu')
            ->get();

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
