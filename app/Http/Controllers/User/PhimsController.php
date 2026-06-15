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
    /**
     * Lấy trạng thái từ suất chiếu gần nhất
     */
    private function getStatus($movie)
    {
        return optional(
            $movie->showtimes
                ->sortBy('thoi_gian_chieu')
                ->first()
        )?->trang_thai;
    }

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

        $nowShowingMovies = $movies->filter(
            fn($movie) =>
            $this->getStatus($movie) === SuatChieu::TRANG_THAI_DANG_CHIEU
        );

        $comingSoonMovies = $movies->filter(
            fn($movie) =>
            $this->getStatus($movie) === SuatChieu::TRANG_THAI_SAP_CHIEU
        );

        $comingLaterMovies = $movies->filter(
            fn($movie) =>
            $this->getStatus($movie) === SuatChieu::TRANG_THAI_SAP_RA_MAT
        );

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
            $query->where('ten_phim', 'like', '%' . $request->tim_kiem . '%');
        }

        if ($request->filled('the_loai')) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('ten_the_loai', $request->the_loai);
            });
        }

        if ($request->filled('quoc_gia')) {
            $query->whereHas('country', function ($q) use ($request) {
                $q->where('ten_quoc_gia', $request->quoc_gia);
            });
        }

        $movies = $query->orderBy('created_at', 'desc')
            ->get()
            ->filter(fn($movie) =>
                $this->getStatus($movie) !== SuatChieu::TRANG_THAI_DA_CHIEU
            );

        // FILTER STATUS
        if ($request->filled('status')) {

            if (in_array($request->status, [
                SuatChieu::TRANG_THAI_DANG_CHIEU,
                SuatChieu::TRANG_THAI_SAP_CHIEU,
                SuatChieu::TRANG_THAI_SAP_RA_MAT,
            ], true)) {

                $query->whereHas('showtimes', function ($q) use ($request) {
                    $q->where('trang_thai', $request->status);
                });

                $movies = $query->orderBy('created_at', 'desc')
                    ->get()
                    ->filter(fn($movie) =>
                        $this->getStatus($movie) !== SuatChieu::TRANG_THAI_DA_CHIEU
                    );
            }
        }

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

        $showtimes = SuatChieu::with([
            'rapChieuPhim',
            'phongChieu',
            'phim'
        ])
            ->where('phim_id', $movie->id)
            ->orderBy('thoi_gian_chieu')
            ->get()
            ->filter(function ($showtime) use ($movie, $now) {

                if (!$showtime->thoi_gian_chieu) {
                    return false;
                }

                $start = Carbon::parse($showtime->thoi_gian_chieu);

                $end = $start->copy()
                    ->addMinutes((int) $movie->thoi_luong);

                return $end->gte($now);
            });

        $relatedMovies = Phims::with(['genres', 'country', 'showtimes'])
            ->where('id', '!=', $movie->id)
            ->whereHas('genres', function ($query) use ($movie) {
                $query->whereIn('id', $movie->genres->pluck('id'));
            })
            ->visibleToUsers()
            ->distinct()
            ->take(6)
            ->get();

        return view('user.phims.show', compact(
            'movie',
            'showtimes',
            'now',
            'relatedMovies'
        ));
    }
}