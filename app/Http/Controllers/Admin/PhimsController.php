<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CapnhatPhims;
use App\Http\Requests\ThemmoiPhimsRequest;
use App\Models\Phims;
use App\Models\QuocGia;
use App\Models\TheLoai;
use App\Traits\Loggable;
use App\Services\AdminNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PhimsController extends Controller
{
    use Loggable;

    /*
    |--------------------------------------------------------------------------
    | LIST
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = Phims::with(['country', 'genres', 'showtimes']);

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

        $movies = $query->latest()->paginate(10)->withQueryString();

        $genres = TheLoai::where('trang_thai', 1)->get();
        $countries = QuocGia::where('trang_thai', 1)->get();

        return view('admin.phims.index', compact('movies', 'genres', 'countries'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $genres = TheLoai::where('trang_thai', 1)->get();
        $countries = QuocGia::where('trang_thai', 1)->get();

        return view('admin.phims.create', compact('genres', 'countries'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */
    public function store(ThemmoiPhimsRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('poster')) {
            $data['poster'] = $request->file('poster')->store('movies', 'public');
        }

        $data['slug'] = Str::slug($data['ten_phim']) . '-' . uniqid();

        $movie = Phims::create($data);

        $this->ghiNhatKy(
            $request,
            'Thêm phim mới',
            'Quản lý phim & lịch chiếu',
            "Thêm phim: {$data['ten_phim']}"
        );

        if (!empty($data['the_loai_id'])) {
            $movie->genres()->sync($data['the_loai_id']);
        }

        AdminNotificationService::push(
            '🎬 Phim mới được thêm',
            'Đã thêm phim ' . $movie->ten_phim,
            'Success'
        );

        return redirect()
            ->route('admin.phims.index')
            ->with('success', 'Thêm phim thành công');
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */
    public function show(Phims $phim)
    {
        $phim->load(['country', 'genres', 'showtimes']);

        return view('admin.phims.show', compact('phim'));
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
    public function edit(Phims $phim)
    {
        $genres = TheLoai::where('trang_thai', 1)->get();
        $countries = QuocGia::where('trang_thai', 1)->get();

        $selectedGenreIds = $phim->genres()
            ->pluck('the_loais.id')
            ->toArray();

        return view('admin.phims.edit', compact(
            'phim',
            'genres',
            'countries',
            'selectedGenreIds'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(CapnhatPhims $request, Phims $phim)
    {
        $data = $request->validated();

        if ($request->hasFile('poster')) {
            $data['poster'] = $request->file('poster')->store('movies', 'public');
        }

        $phim->update($data);

        $this->ghiNhatKy(
            $request,
            'Cập nhật phim',
            'Quản lý phim & lịch chiếu',
            "Cập nhật phim: {$phim->ten_phim}"
        );

        if (!empty($data['the_loai_id'])) {
            $phim->genres()->sync($data['the_loai_id']);
        } else {
            $phim->genres()->detach();
        }

        AdminNotificationService::push(
            '✏️ Phim đã được cập nhật',
            'Vừa cập nhật phim ' . $phim->ten_phim,
            'Success'
        );

        return redirect()
            ->route('admin.phims.index')
            ->with('success', 'Cập nhật phim thành công');
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    public function destroy(Request $request, Phims $phim)
    {
        if ($phim->showtimes()->exists()) {
            return redirect()
                ->route('admin.phims.index')
                ->with('error', 'Không thể xóa phim đã có suất chiếu');
        }

        $tenPhim = $phim->ten_phim;

        $phim->genres()->detach();
        $phim->delete();

        $this->ghiNhatKy(
            $request,
            'Xóa phim',
            'Quản lý phim & lịch chiếu',
            "Xóa phim: {$tenPhim}"
        );

        AdminNotificationService::push(
            '🗑️ Phim đã được xóa',
            'Đã xóa phim ' . $tenPhim,
            'Danger'
        );

        return redirect()
            ->route('admin.phims.index')
            ->with('success', 'Xóa phim thành công');
    }
}