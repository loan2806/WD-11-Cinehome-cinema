<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Phims;       // Model Phim của bạn
use App\Models\SuatChieu;   // Model Suất chiếu tiếng Việt mới
use App\Models\RapChieuPhim; // Hãy đổi tên thành Model Rạp tương ứng của bạn nếu có

class StaffDashboardController extends Controller
{
    public function index()
    {
        // 1. Đếm tổng số lượng phim
        $totalMovies = Phims::count();

        // 2. Đếm tổng số lượng rạp chiếu (Tạm thời dùng count, hãy đổi đúng tên Model Rạp của bạn nếu đã Việt hóa)
        $totalCinemas = class_exists(\App\Models\RapChieuPhim::class) ? RapChieuPhim::count() : 0;

        // 3. Đếm tổng số suất chiếu của ngày hôm nay
        $todayShowtimes = SuatChieu::whereDate('thoi_gian_chieu', now()->toDateString())->count();

        // 4. Lấy danh sách 10 suất chiếu sắp tới (kèm mối quan hệ 'phim')
        $upcomingShowtimes = SuatChieu::with(['phim'])
            ->where('thoi_gian_chieu', '>=', now())
            ->orderBy('thoi_gian_chieu', 'asc')
            ->take(10)
            ->get();

        return view('staff.dashboard', compact(
            'totalMovies',
            'totalCinemas',
            'todayShowtimes',
            'upcomingShowtimes'
        ));
    }
}