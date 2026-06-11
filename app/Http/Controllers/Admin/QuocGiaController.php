<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CapnhatQuocGiaRequest;
use App\Http\Requests\ThemmoiQuocGiaRequest;
use App\Models\QuocGia;
use Illuminate\Http\Request;

class QuocGiaController extends Controller
{
    /**
     * Danh sách quốc gia
     */
   public function index(Request $request)
{
    $query = QuocGia::query();

    // SEARCH
    if ($request->search) {
        $query->where(function ($q) use ($request) {
            $q->where('ten_quoc_gia', 'like', '%' . $request->search . '%')
              ->orWhere('ma_quoc_gia', 'like', '%' . $request->search . '%');
        });
    }

    // SORT NEWEST
    $countries = $query->latest()->paginate(10);

    return view('admin.quoc-gias.index', compact('countries'));
}

    /**
     * Form tạo quốc gia
     */
    public function create()
    {
        return view('admin.quoc-gias.create');
    }

    /**
     * Lưu quốc gia mới
     */
public function store(ThemmoiQuocGiaRequest $request)
{
    QuocGia::create($request->validated());

    return redirect()
        ->route('admin.quoc-gias.index')
        ->with('success', 'Thêm quốc gia thành công');
}

    /**
     * Form chỉnh sửa
     */
    public function edit(QuocGia $quocGia)
    {
        return view('admin.quoc-gias.edit', compact('quocGia'));
    }

    /**
     * Cập nhật quốc gia
     */
 public function update(CapnhatQuocGiaRequest $request, QuocGia $quocGia)
{
    $quocGia->update($request->validated());

    return redirect()
        ->route('admin.quoc-gias.index')
        ->with('success', 'Cập nhật quốc gia thành công');
}

    /**
     * Xóa quốc gia
     */
public function destroy(QuocGia $quocGia)
{
    if ($quocGia->phims()->exists()) {
        return redirect()
            ->route('admin.quoc-gias.index')
            ->with('error', 'Không thể xóa quốc gia vì đang có phim liên kết');
    }

    $quocGia->delete();

    return redirect()
        ->route('admin.quoc-gias.index')
        ->with('success', 'Xóa quốc gia thành công');
}
}