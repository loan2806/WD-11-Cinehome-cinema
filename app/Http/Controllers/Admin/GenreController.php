<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LIST
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = Genre::query();

        // Search
        if ($request->filled('search')) {
            $query->where('ten_the_loai', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('trang_thai', $request->status);
        }

        $genres = $query->latest()->paginate(20);

        return view('admin.genres.index', compact('genres'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE FORM
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        return view('admin.genres.create');
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'ten_the_loai' => 'required|string|max:255|unique:the_loais',
            'mo_ta' => 'nullable|string|max:500',
            'trang_thai' => 'required|boolean',
        ]);

        Genre::create($request->validated());

        return redirect()
            ->route('admin.genres.index')
            ->with('success', 'Thêm thể loại thành công');
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT FORM
    |--------------------------------------------------------------------------
    */
    public function edit(Genre $genre)
    {
        return view('admin.genres.edit', compact('genre'));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Genre $genre)
    {
        $request->validate([
            'ten_the_loai' => 'required|string|max:255|unique:the_loais,ten_the_loai,' . $genre->id,
            'mo_ta' => 'nullable|string|max:500',
            'trang_thai' => 'required|boolean',
        ]);

        $genre->update($request->validated());

        return redirect()
            ->route('admin.genres.index')
            ->with('success', 'Cập nhật thể loại thành công');
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    public function destroy(Genre $genre)
    {
        $genre->delete();

        return redirect()
            ->route('admin.genres.index')
            ->with('success', 'Xóa thể loại thành công');
    }
}
