<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Phims;
use App\Models\SuatChieu;
use App\Models\RapChieuPhim;
use App\Models\VeXemPhim;

class StaffDashboardController extends Controller
{
    /**
     * Hiển thị dashboard tổng quan cho nhân viên.
     * Phần này chỉ thống kê các dữ liệu cần thiết cho Staff,
     * không thống kê toàn hệ thống như Admin.
     */
    public function index()
    {
        // Tổng số phim trong hệ thống
        $totalMovies = Phims::count();

        // Tổng số rạp chiếu
        $totalCinemas = RapChieuPhim::count();

        // Tổng số suất chiếu trong ngày hôm nay
        $todayShowtimes = SuatChieu::whereDate('thoi_gian_chieu', today())->count();

        // Số vé nhân viên bán tại quầy hôm nay
        $todaySoldTickets = VeXemPhim::where('loai_ve', 'tai_quay')
            ->whereDate('created_at', today())
            ->count();

        // Doanh thu vé tại quầy hôm nay
        $todayRevenue = VeXemPhim::where('loai_ve', 'tai_quay')
            ->whereDate('created_at', today())
            ->whereIn('trang_thai', ['da_thanh_toan', 'da_su_dung'])
            ->sum('tong_tien');

        // Số vé đã được nhân viên soát hôm nay
        $todayCheckedTickets = VeXemPhim::where('trang_thai', 'da_su_dung')
            ->whereDate('updated_at', today())
            ->count();

        // Lấy 10 suất chiếu sắp tới để nhân viên dễ theo dõi
        $upcomingShowtimes = SuatChieu::with(['phim', 'rapChieuPhim', 'phongChieu'])
            ->where('thoi_gian_chieu', '>=', now())
            ->orderBy('thoi_gian_chieu', 'asc')
            ->take(10)
            ->get();

        return view('staff.dashboard', compact(
            'totalMovies',
            'totalCinemas',
            'todayShowtimes',
            'todaySoldTickets',
            'todayRevenue',
            'todayCheckedTickets',
            'upcomingShowtimes'
        ));
    }
}