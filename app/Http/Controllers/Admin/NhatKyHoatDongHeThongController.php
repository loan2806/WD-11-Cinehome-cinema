<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NhatKyHoatDongHeThong; // Đã đổi Model sang tiếng Việt
use Illuminate\Http\Request;

class NhatKyHoatDongHeThongController extends Controller
{
    public function index(Request $request)
    {
        // ĐÃ SỬA: Thực hiện câu lệnh lọc tìm kiếm bằng các trường dữ liệu tiếng Việt mới
        $logs = NhatKyHoatDongHeThong::with('nguoiDung')
            ->when($request->filled('chuc_nang'), fn ($query) => $query->where('chuc_nang', $request->chuc_nang))
            ->when($request->filled('keyword'), function ($query) use ($request) {
                $keyword = '%' . $request->keyword . '%';

                $query->where(function ($inner) use ($keyword) {
                    $inner->where('hanh_dong', 'like', $keyword)
                        ->orWhere('mo_ta', 'like', $keyword);
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $modules = NhatKyHoatDongHeThong::query()->whereNotNull('chuc_nang')->distinct()->pluck('chuc_nang');

        // Tạm thời vẫn trỏ về thư mục view cũ, lát nữa đổi tên view sau
        return view('admin.activity-logs.index', compact('logs', 'modules'));
    }
}