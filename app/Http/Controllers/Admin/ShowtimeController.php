<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShowtimeRequest;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Showtime;
use Illuminate\Http\Request;

class ShowtimeController extends Controller
{
    // danh sách
    public function index()
    {
        $showtimes = Showtime::with(['movie', 'cinema'])
            ->orderByDesc('show_date')
            ->orderBy('show_time')
            ->paginate(20);

        return view('admin.showtimes.index', compact('showtimes'));
    }

    // form thêm
    public function create(Request $request)
    {
        $movies = Movie::orderBy('title')->get();

        $cinemas = Cinema::all();

        $selectedMovie = Movie::find($request->query('movie_id'));

        return view('admin.showtimes.create', compact(
            'movies',
            'cinemas',
            'selectedMovie'
        ));
    }

    // lưu
    public function store(StoreShowtimeRequest $request)
    {
        $data = $request->validated();

        Showtime::create($data);

        return redirect()
            ->route('admin.showtimes.index')
            ->with('success', 'Suất chiếu đã được tạo thành công.');
    }

    // form sửa
    public function edit($id)
    {
        $showtime = Showtime::findOrFail($id);

        $movies = Movie::orderBy('title')->get();

        $cinemas = Cinema::all();

        return view('admin.showtimes.edit', compact(
            'showtime',
            'movies',
            'cinemas'
        ));
    }

    // update
    public function update(StoreShowtimeRequest $request, $id)
    {
        $showtime = Showtime::findOrFail($id);

        $data = $request->validated();

        $showtime->update($data);

        return redirect()
            ->route('admin.showtimes.index')
            ->with('success', 'Cập nhật suất chiếu thành công.');
    }

    // xóa
    public function destroy($id)
    {
        $showtime = Showtime::findOrFail($id);

        $showtime->delete();

        return redirect()
            ->route('admin.showtimes.index')
            ->with('success', 'Xóa suất chiếu thành công.');
    }
}