<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TheLoaiRequest;
use App\Models\TheLoai;
use App\Services\AdminNotificationService;
use App\Traits\Loggable;
use Illuminate\Http\Request;

class TheloaisController extends Controller
{
    use Loggable;

    /*
    |--------------------------------------------------------------------------
    | LIST
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = TheLoai::withCount('phims');

        if ($request->filled('search')) {
            $query->where('ten_the_loai', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('trang_thai', $request->status);
        }

        $theLoais = $query->latest()->paginate(20)->withQueryString();

        $summary = [
            'total' => TheLoai::count(),
            'active' => TheLoai::where('trang_thai', 1)->count(),
            'inactive' => TheLoai::where('trang_thai', 0)->count(),
            'with_movies' => TheLoai::has('phims')->count(),
        ];

        return view('admin.the-loais.index', compact('theLoais', 'summary'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        return view('admin.the-loais.create');
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */
    public function store(TheLoaiRequest $request)
    {
        $data = $request->validated();
        $data['trang_thai'] = $data['trang_thai'] ?? 1;

        $theLoai = TheLoai::create($data);

        AdminNotificationService::push(
            '🎭 Thể loại mới được thêm',
            'Đã thêm thể loại ' . $theLoai->ten_the_loai,
            'Success'
        );

        $this->ghiNhatKy(
            $request,
            'Thêm thể loại phim',
            'Quản lý phim & lịch chiếu',
            "Thêm thể loại: {$theLoai->ten_the_loai}"
        );

        return redirect()->route('admin.the-loais.index')
            ->with('success', 'Thêm thể loại thành công');
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
    public function edit(TheLoai $theLoai)
    {
        $theLoai->loadCount('phims');

        return view('admin.the-loais.edit', compact('theLoai'));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(TheLoaiRequest $request, TheLoai $theLoai)
    {
        $theLoai->update($request->validated());

        AdminNotificationService::push(
            '✏️ Thể loại đã được cập nhật',
            'Đã cập nhật thể loại ' . $theLoai->ten_the_loai,
            'Success'
        );

        $this->ghiNhatKy(
            $request,
            'Cập nhật thể loại phim',
            'Quản lý phim & lịch chiếu',
            "Cập nhật thể loại: {$theLoai->ten_the_loai}"
        );

        return redirect()->route('admin.the-loais.index')
            ->with('success', 'Cập nhật thể loại thành công');
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    public function destroy(TheLoai $theLoai)
    {
        if ($theLoai->phims()->exists()) {
            return redirect()
                ->route('admin.the-loais.index')
                ->with('error', 'Không thể xóa thể loại này vì đang có phim liên kết.');
        }

        $tenTheLoai = $theLoai->ten_the_loai;

        $theLoai->delete();

        AdminNotificationService::push(
            '🗑️ Thể loại đã bị xóa',
            'Đã xóa thể loại ' . $tenTheLoai,
            'Danger'
        );

        $this->ghiNhatKy(
            request(),
            'Xóa thể loại phim',
            'Quản lý phim & lịch chiếu',
            "Xóa thể loại: {$tenTheLoai}"
        );

        return redirect()->route('admin.the-loais.index')
            ->with('success', 'Xóa thể loại thành công');
    }
}
