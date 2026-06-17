<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TheLoaiRequest;
use App\Models\TheLoai;
use App\Services\AdminNotificationService;
use Illuminate\Http\Request;

class TheloaisController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LIST
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = TheLoai::withCount('phims');

        // Search
        if ($request->filled('search')) {
            $query->where('ten_the_loai', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('trang_thai', $request->status);
        }

        $theLoais = $query->latest()->paginate(20);

        return view('admin.the-loais.index', compact('theLoais'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE FORM
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
        // Mặc định trạng thái = 1 (kích hoạt) khi tạo mới
        $data['trang_thai'] = $data['trang_thai'] ?? 1;

        $theLoai = TheLoai::create($data);

        AdminNotificationService::push(
            '🎭 Thể loại mới được thêm',
            'Đã thêm thể loại ' . $theLoai->ten_the_loai,
            'Success'
        );

        return redirect()
            ->route('admin.the-loais.index');
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT FORM
    |--------------------------------------------------------------------------
    */
    public function edit(TheLoai $theLoai)
    {
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



        return redirect()
            ->route('admin.the-loais.index');
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
                ->with('error', 'Không thể xóa thể loại này vì đang có phim liên kết. Vui lòng xóa hoặc cập nhật các phim trước.');
        }

        $tenTheLoai = $theLoai->ten_the_loai;

        $theLoai->delete();


        AdminNotificationService::push(
            '🗑️ Thể loại đã bị xóa',
            'Đã xóa thể loại ' . $tenTheLoai,
            'Danger'
        );



        return redirect()
            ->route('admin.the-loais.index');
    }
}
