<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CapnhatQuocGiaRequest;
use App\Http\Requests\ThemmoiQuocGiaRequest;
use App\Models\QuocGia;
use App\Services\AdminNotificationService;
use App\Traits\Loggable;
use Illuminate\Http\Request;

class QuocGiaController extends Controller
{
    use Loggable;

    /**
     * Danh sách quốc gia
     */
    public function index(Request $request)
    {
        $query = QuocGia::query();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('ten_quoc_gia', 'like', '%' . $request->search . '%')
                    ->orWhere('ma_quoc_gia', 'like', '%' . $request->search . '%');
            });
        }

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
        $quocGia = QuocGia::create($request->validated());

        // NOTIFICATION SYSTEM
        AdminNotificationService::push(
            '🌍 Quốc gia mới được thêm',
            'Đã thêm quốc gia ' . $quocGia->ten_quoc_gia,
            'Success'
        );

        // LOG SYSTEM
        $this->ghiNhatKy(
            $request,
            'Thêm quốc gia',
            'Quản lý phim & lịch chiếu',
            "Thêm quốc gia: {$quocGia->ten_quoc_gia}"
        );

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

        // NOTIFICATION SYSTEM
        AdminNotificationService::push(
            '✏️ Quốc gia đã được cập nhật',
            'Đã cập nhật quốc gia ' . $quocGia->ten_quoc_gia,
            'Success'
        );

        // LOG SYSTEM
        $this->ghiNhatKy(
            $request,
            'Cập nhật quốc gia',
            'Quản lý phim & lịch chiếu',
            "Cập nhật quốc gia: {$quocGia->ten_quoc_gia}"
        );

        return redirect()
            ->route('admin.quoc-gias.index')
            ->with('success', 'Cập nhật quốc gia thành công');
    }

    /**
     * Xóa quốc gia
     */
    public function destroy(Request $request, QuocGia $quocGia)
    {
        if ($quocGia->phims()->exists()) {
            return redirect()
                ->route('admin.quoc-gias.index')
                ->with('error', 'Không thể xóa quốc gia vì đang có phim liên kết');
        }

        $tenQuocGia = $quocGia->ten_quoc_gia;

        $quocGia->delete();

        // NOTIFICATION SYSTEM
        AdminNotificationService::push(
            '🗑️ Quốc gia đã bị xóa',
            'Đã xóa quốc gia ' . $tenQuocGia,
            'Warning'
        );

        // LOG SYSTEM
        $this->ghiNhatKy(
            $request,
            'Xóa quốc gia',
            'Quản lý phim & lịch chiếu',
            "Xóa quốc gia: {$tenQuocGia}"
        );

        return redirect()
            ->route('admin.quoc-gias.index')
            ->with('success', 'Xóa quốc gia thành công');
    }
}