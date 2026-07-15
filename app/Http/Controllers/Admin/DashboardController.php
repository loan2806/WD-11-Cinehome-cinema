<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Phims;       // Khớp với Model Phims của bạn
use App\Models\SuatChieu;   // Khớp với Model SuatChieu của bạn
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Dashboard dành riêng cho Quản trị viên & Quản lý hệ thống.
     * Các vai trò khác sẽ được điều hướng ở route /dashboard trước khi vào đây.
     */
    public function index()
    {
        $latestMovies = Phims::with(['country', 'genres', 'showtimes'])
            ->latest()
            ->take(5)
            ->get();

        $todaySchedules = SuatChieu::with(['phim', 'phongChieu', 'rapChieuPhim'])
            ->whereDate('thoi_gian_chieu', now()->toDateString())
            ->orderBy('thoi_gian_chieu', 'asc')
            ->take(10)
            ->get();

        $statData = [
            'doanh_thu_hom_nay' => '82.500.000đ',
            've_da_ban' => '1.240',
            'luong_khach' => '980',
            'doanh_thu_do_an' => '12.300.000đ',
        ];

        return view('admin.dashboard', compact('latestMovies', 'todaySchedules', 'statData'));
    }
}
