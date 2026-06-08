<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Phims;       // Khớp với Model Phims có chữ "s" của bạn
use App\Models\SuatChieu;   // Khớp với Model SuatChieu tiếng Việt mới
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Lấy danh sách phim mới cập nhật (Khớp với các trường tiếng Việt mới của bạn)
        $latestMovies = Phims::latest()->take(5)->get();

        // 2. Lấy các suất chiếu của ngày hôm nay dựa trên trường thoi_gian_chieu tiếng Việt
        $todaySchedules = SuatChieu::with(['phim']) // load quan hệ phim (Phims)
            ->whereDate('thoi_gian_chieu', now()->toDateString())
            ->orderBy('thoi_gian_chieu', 'asc')
            ->take(10)
            ->get();

        // Trả dữ liệu ra giao diện quản trị Admin
        return view('admin.dashboard', compact('latestMovies', 'todaySchedules'));
    }
}