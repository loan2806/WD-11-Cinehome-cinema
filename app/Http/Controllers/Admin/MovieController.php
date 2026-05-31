<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMovieRequest;
use App\Http\Requests\UpdateMovieRequest;
use App\Models\Movie;
use App\Models\Genre;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MovieController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LIST
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = Movie::with([
            'country',
            'genres',
            'showtimes'
        ]);

        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search')) {

            $query->where(
                'ten_phim',
                'like',
                '%' . $request->search . '%'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER GENRE
        |--------------------------------------------------------------------------
        */
        if ($request->filled('genre_id')) {

            $query->whereHas('genres', function ($q) use ($request) {

                $q->where(
                    'genres.id',
                    $request->genre_id
                );
            });
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
            ->latest()
            ->get();

        /*
        |--------------------------------------------------------------------------
        | GET GENRES
        |--------------------------------------------------------------------------
        */
        $genres = Genre::where(
            'trang_thai',
            1
        )->get();

        /*
        |--------------------------------------------------------------------------
        | GET COUNTRIES
        |--------------------------------------------------------------------------
        */
        $countries = Country::where(
            'trang_thai',
            1
        )->get();

        return view(
            'admin.movies.index',
            compact(
                'movies',
                'genres',
                'countries'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE FORM
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $genres = Genre::where(
            'trang_thai',
            1
        )->get();

        $countries = Country::where(
            'trang_thai',
            1
        )->get();

        return view(
            'admin.movies.create',
            compact(
                'genres',
                'countries'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */
    public function store(StoreMovieRequest $request)
    {
        $data = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | UPLOAD POSTER
        |--------------------------------------------------------------------------
        */
        if ($request->hasFile('poster')) {

            $data['poster'] = $request
                ->file('poster')
                ->store('movies', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | AUTO SLUG
        |--------------------------------------------------------------------------
        */
        $data['slug'] =
            Str::slug($data['ten_phim']) . '-' . uniqid();

        /*
        |--------------------------------------------------------------------------
        | CREATE MOVIE
        |--------------------------------------------------------------------------
        */
        $movie = Movie::create($data);

        /*
        |--------------------------------------------------------------------------
        | SYNC GENRES
        |--------------------------------------------------------------------------
        */
        if (!empty($data['genre_ids'])) {

            $movie->genres()
                ->sync($data['genre_ids']);
        }

        return redirect()
            ->route('admin.movies.index')
            ->with(
                'success',
                'Thêm phim thành công'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW DETAIL
    |--------------------------------------------------------------------------
    */
    public function show(Movie $movie)
    {
        $movie->load([
            'country',
            'genres',
            'showtimes'
        ]);

        return view(
            'admin.movies.show',
            compact('movie')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT FORM
    |--------------------------------------------------------------------------
    */
    public function edit(Movie $movie)
    {
        $genres = Genre::where(
            'trang_thai',
            1
        )->get();

        $countries = Country::where(
            'trang_thai',
            1
        )->get();

        $selectedGenreIds = $movie->genres()
            ->pluck('genres.id')
            ->toArray();

        return view(
            'admin.movies.edit',
            compact(
                'movie',
                'genres',
                'countries',
                'selectedGenreIds'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(
        UpdateMovieRequest $request,
        Movie $movie
    ) {
        $data = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | UPDATE POSTER
        |--------------------------------------------------------------------------
        */
        if ($request->hasFile('poster')) {

            $data['poster'] = $request
                ->file('poster')
                ->store('movies', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE MOVIE
        |--------------------------------------------------------------------------
        */
        $movie->update($data);

        /*
        |--------------------------------------------------------------------------
        | UPDATE GENRES
        |--------------------------------------------------------------------------
        */
        if (!empty($data['genre_ids'])) {

            $movie->genres()
                ->sync($data['genre_ids']);

        } else {

            $movie->genres()
                ->detach();
        }

        return redirect()
            ->route('admin.movies.index')
            ->with(
                'success',
                'Cập nhật phim thành công'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    public function destroy(Movie $movie)
    {
        /*
        |--------------------------------------------------------------------------
        | DELETE GENRES
        |--------------------------------------------------------------------------
        */
        $movie->genres()->detach();

        /*
        |--------------------------------------------------------------------------
        | DELETE MOVIE
        |--------------------------------------------------------------------------
        */
        $movie->delete();

        return redirect()
            ->route('admin.movies.index')
            ->with(
                'success',
                'Xóa phim thành công'
            );
    }
}