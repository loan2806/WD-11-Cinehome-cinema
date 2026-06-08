<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\VeXemPhim;
use Illuminate\Http\Request;

class LichSuVeController extends Controller
{
    /**
     * Hiển thị danh sách lịch sử vé.
     * Nhân viên có thể tìm kiếm theo mã vé, tên phim, mã ghế
     * và lọc theo loại vé hoặc trạng thái vé.
     */
    public function index(Request $request)
    {
        // Khởi tạo query lấy danh sách vé
        $query = VeXemPhim::query()
            ->with(['nguoiDung', 'nhanVien', 'suatChieu']);

        // Tìm kiếm theo mã vé, tên phim hoặc mã ghế
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);

            $query->where(function ($q) use ($keyword) {
                $q->where('ma_ve', 'like', '%' . $keyword . '%')
                    ->orWhere('ten_phim', 'like', '%' . $keyword . '%')
                    ->orWhere('ma_ghe', 'like', '%' . $keyword . '%');
            });
        }

        // Lọc theo loại vé: trực tuyến hoặc tại quầy
        if ($request->filled('loai_ve')) {
            $query->where('loai_ve', $request->loai_ve);
        }

        // Lọc theo trạng thái vé
        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }

        // Lấy danh sách vé mới nhất trước, phân trang 10 vé mỗi trang
        $tickets = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('staff.lich-su-ve.index', compact('tickets'));
    }
}