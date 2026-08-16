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
use Illuminate\Support\Facades\Http;

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

        if (!empty($data['trailer'])) {
            $response = Http::get(
                'https://www.youtube.com/oembed',
                [
                    'url' => $data['trailer'],
                    'format' => 'json'
                ]
            );

            if (!$response->successful()) {
                return back()
                    ->withErrors([
                        'trailer' => 'Trailer không tồn tại hoặc đã bị xóa.'
                    ])
                    ->withInput();
            }
        }

        if ($request->hasFile('poster')) {
            $path = $data['poster'] = $request->file('poster')->store('movies', 'public');
            $data['poster'] = basename($path);
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
        $phim->load(['country', 'genres', 'showtimes.rapChieuPhim', 'showtimes.phongChieu']);

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

        if (!empty($data['trailer'])) {
            $response = Http::get(
                'https://www.youtube.com/oembed',
                [
                    'url' => $data['trailer'],
                    'format' => 'json'
                ]
            );

            if (!$response->successful()) {
                return back()
                    ->withErrors([
                        'trailer' => 'Trailer không tồn tại hoặc đã bị xóa.'
                    ])
                    ->withInput();
            }
        }

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
    | DELETE (XÓA MỀM)
    |--------------------------------------------------------------------------
    */
    public function destroy(Request $request, Phims $phim)
    {
        // 1. Kiểm tra ràng buộc: Nếu đã có suất chiếu thì CHẶN xóa
        if ($phim->showtimes()->exists()) {
            return redirect()
                ->route('admin.phims.index')
                ->with('error', 'Không thể xóa phim này vì đã có suất chiếu!');
        }

        $tenPhim = $phim->ten_phim;

        // 2. Thực hiện xóa mềm (không dùng $phim->genres()->detach() để giữ quan hệ khi khôi phục)
        $phim->delete();

        $this->ghiNhatKy(
            $request,
            'Xóa mềm phim',
            'Quản lý phim & lịch chiếu',
            "Xóa mềm phim: {$tenPhim}"
        );

        AdminNotificationService::push(
            '🗑️ Phim đã được xóa',
            'Đã xóa mềm phim ' . $tenPhim,
            'Danger'
        );

        return redirect()
            ->route('admin.phims.index')
            ->with('success', 'Xóa phim thành công!');
    }
    /**
     * Khôi phục phim từ thùng rác
     */
    public function restore($id)
    {
        $phim = Phims::onlyTrashed()->findOrFail($id);
        $phim->restore();

        if (method_exists($this, 'ghiNhatKy')) {
            $this->ghiNhatKy(
                request(),
                'Khôi phục phim',
                'Quản lý nội dung phim',
                "Khôi phục phim: {$phim->ten_phim} (ID: #{$id})"
            );
        }

        return redirect()->back()->with('success', "Đã khôi phục phim \"{$phim->ten_phim}\" thành công!");
    }

    /**
     * Xóa vĩnh viễn phim khỏi cơ sở dữ liệu
     */
    public function forceDelete($id)
    {
        $phim = Phims::onlyTrashed()->findOrFail($id);

        // Kiểm tra nếu phim còn suất chiếu liên kết
        if (method_exists($phim, 'showtimes') && $phim->showtimes()->exists()) {
            return redirect()->back()->with('error', 'Không thể xóa vĩnh viễn! Phim này vẫn còn lịch sử suất chiếu.');
        }

        // Gỡ bỏ liên kết thể loại nếu có
        if (method_exists($phim, 'genres')) {
            $phim->genres()->detach();
        }

        $tenPhim = $phim->ten_phim;
        $phim->forceDelete();

        if (method_exists($this, 'ghiNhatKy')) {
            $this->ghiNhatKy(
                request(),
                'Xóa vĩnh viễn phim',
                'Quản trị hệ thống',
                "Xóa vĩnh viễn phim: {$tenPhim} (ID: #{$id})"
            );
        }

        return redirect()->back()->with('success', "Đã xóa vĩnh viễn phim \"{$tenPhim}\" khỏi hệ thống!");
    }
}