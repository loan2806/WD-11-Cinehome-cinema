<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DanhGiaPhim;
use App\Models\Phims;
use App\Traits\Loggable;
use Illuminate\Http\Request;

class DanhGiaPhimController extends Controller
{
    use Loggable;
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

        $this->ghiNhatKy($request, 'Thêm đánh giá phim', 'Quản lý đánh giá phim', "Thêm đánh giá phim #{$danhGia->id}");

        return back()->with('success', 'Đã thêm đánh giá phim.');
    }

    public function update(Request $request, DanhGiaPhim $danhGiaPhim)
    {
        $data = $request->validate([
            'trang_thai' => ['required', 'in:cho_duyet,hien_thi,an'],
        ]);

        $danhGiaPhim->update($data);

        $this->ghiNhatKy($request, 'Cập nhật đánh giá phim', 'Quản lý đánh giá phim', "Cập nhật đánh giá #{$danhGiaPhim->id} sang {$data['trang_thai']}");

        return back()->with('success', 'Đã cập nhật trạng thái đánh giá.');
    }

    public function destroy(Request $request, DanhGiaPhim $danhGiaPhim)
    {
        $id = $danhGiaPhim->id;
        $danhGiaPhim->delete();

        $this->ghiNhatKy($request, 'Xóa đánh giá phim', 'Quản lý đánh giá phim', "Xóa đánh giá #{$id}");

        return back()->with('success', 'Đã xóa đánh giá phim.');
    }
}