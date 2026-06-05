<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Phim;
use App\Models\DanhGiaPhim; // Đã đổi từ MovieReview sang DanhGiaPhim
use App\Models\Phims;
use Illuminate\Http\Request;

class DanhGiaPhimController extends Controller
{
    public function index()
    {
        // ĐÃ SỬA: Nạp các mối quan hệ liên kết bằng tiếng Việt (phim, nguoiDung)
        $danhGias = DanhGiaPhim::with(['phim', 'nguoiDung'])->latest()->paginate(12);
        $phims = Phims::orderBy('ten_phim')->get(['id', 'ten_phim']);

        return view('admin.danh_gia_phim.index', compact('danhGias', 'phims'));
    }

    public function store(Request $request)
    {
        // ĐÃ SỬA: Áp dụng tập luật validate cho cấu trúc database mới
        $data = $request->validate([
            'phim_id' => ['required', 'exists:phims,id'],
            'diem_danh_gia' => ['required', 'integer', 'min:1', 'max:5'],
            'noi_dung' => ['nullable', 'string'],
            'trang_thai' => ['required', 'in:cho_duyet,hien_thi,an'], // Map đúng tập giá trị enum tiếng Việt
        ]);

        // Gán ID của Admin trực tiếp tạo đánh giá này
        $data['nguoi_dung_id'] = auth()->id();

        $danhGia = DanhGiaPhim::create($data);

        if (class_exists(\App\Models\ActivityLog::class)) {
            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'module' => 'movie_reviews',
                'description' => 'Thêm đánh giá phim #' . $danhGia->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return back()->with('success', 'Đã thêm đánh giá phim.');
    }

    public function update(Request $request, DanhGiaPhim $danhGiaPhim)
    {
        $data = $request->validate([
            'trang_thai' => ['required', 'in:cho_duyet,hien_thi,an'],
        ]);

        $danhGiaPhim->update($data);

        if (class_exists(\App\Models\ActivityLog::class)) {
            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'module' => 'movie_reviews',
                'description' => 'Cập nhật đánh giá phim #' . $danhGiaPhim->id . ' sang ' . $data['trang_thai'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return back()->with('success', 'Đã cập nhật trạng thái đánh giá.');
    }

    public function destroy(Request $request, DanhGiaPhim $danhGiaPhim)
    {
        $id = $danhGiaPhim->id;
        $danhGiaPhim->delete();

        if (class_exists(\App\Models\ActivityLog::class)) {
            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'module' => 'movie_reviews',
                'description' => 'Xóa đánh giá phim #' . $id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return back()->with('success', 'Đã xóa đánh giá phim.');
    }
}