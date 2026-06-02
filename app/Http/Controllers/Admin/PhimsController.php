<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CapnhatPhims;
use App\Http\Requests\ThemmoiPhimsRequest;
use App\Http\Requests\UpdateMovieRequest;
use App\Models\Phims;
use App\Models\QuocGia;
use App\Models\TheLoai;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PhimsController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LIST
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = Phims::with([
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
        $genres = TheLoai::where(
            'trang_thai',
            1
        )->get();

        /*
        |--------------------------------------------------------------------------
        | GET COUNTRIES
        |--------------------------------------------------------------------------
        */
        $countries = QuocGia::where(
            'trang_thai',
            1
        )->get();

        return view(
            'admin.phims.index',
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
        $genres = TheLoai::where(
            'trang_thai',
            1
        )->get();

        $countries = QuocGia::where(
            'trang_thai',
            1
        )->get();

        return view(
            'admin.phims.create',
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
    public function store(ThemmoiPhimsRequest $request)
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
        $movie = Phims::create($data);

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
            ->route('admin.phims.index')
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
    public function show(Phims $movie)
    {
        $movie->load([
            'country',
            'genres',
            'showtimes'
        ]);

        return view(
            'admin.phims.show',
            compact('movie')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT FORM
    |--------------------------------------------------------------------------
    */
    public function edit(Phims $movie)
    {
        $genres = TheLoai::where(
            'trang_thai',
            1
        )->get();

        $countries = QuocGia::where(
            'trang_thai',
            1
        )->get();

        $selectedGenreIds = $movie->genres()
            ->pluck('genres.id')
            ->toArray();

        return view(
            'admin.phims.edit',
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
        CapnhatPhims $request,
        Phims $movie
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
            ->route('admin.phims.index')
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
    public function destroy(Phims $movie)
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
            ->route('admin.phims.index')
            ->with(
                'success',
                'Xóa phim thành công'
            );
    }
}