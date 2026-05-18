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
        $today = Carbon::today('Asia/Ho_Chi_Minh');

        $bannerMovies = Movie::with('showtimes')
            ->whereDate('release_date', '>=', $today)
            ->latest()
            ->take(5)
            ->get();

        $nowShowingMovies = Movie::with('showtimes')
            ->whereDate('release_date', $today)
            ->latest()
            ->take(12)
            ->get();

        $comingSoonMovies = Movie::with('showtimes')
            ->whereDate('release_date', '>', $today)
            ->whereDate('release_date', '<=', $today->copy()->addDays(10))
            ->latest()
            ->take(12)
            ->get();

        $comingLaterMovies = Movie::with('showtimes')
            ->whereDate('release_date', '>', $today->copy()->addMonth())
            ->latest()
            ->take(12)
            ->get();

        return view('user.home', compact(
            'bannerMovies',
            'nowShowingMovies',
            'comingSoonMovies',
            'comingLaterMovies'
        ));
    }

    public function index(Request $request)
    {
        $today = Carbon::today('Asia/Ho_Chi_Minh');

        $query = Movie::with('showtimes')
            ->whereDate('release_date', '>=', $today);

        if ($request->filled('keyword')) {
            $query->where('title', 'like', '%' . $request->keyword . '%');
        }

        if ($request->filled('genre')) {
            $query->where('genre', $request->genre);
        }

        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        if ($request->filled('status')) {
            if ($request->status === 'now_showing') {
                $query->whereDate('release_date', $today);
            }

            if ($request->status === 'coming_soon') {
                $query->whereDate('release_date', '>', $today)
                    ->whereDate('release_date', '<=', $today->copy()->addDays(10));
            }

            if ($request->status === 'coming_later') {
                $query->whereDate('release_date', '>', $today->copy()->addMonth());
            }
        }

        if ($request->filled('release_date')) {
            $query->whereDate('release_date', $request->release_date);
        }

        $movies = $query
            ->latest()
            ->paginate(12)
            ->withQueryString();

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

        if ($movie->release_date && $movie->release_date->lt($today)) {
            abort(404);
        }

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