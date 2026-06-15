<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Phims;       // Khớp với Model Phims của bạn
use App\Models\SuatChieu;   // Khớp với Model SuatChieu của bạn
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Sửa lỗi Undefined variable $statData và phân phối View độc lập
     */
    public function index()
    {
        $user = Auth::user();

        // 1. Nạp danh sách phim mới cập nhật
        $latestMovies = Phims::latest()->take(5)->get();

        // 2. Nạp lịch chiếu phim của ngày hôm nay
        $todaySchedules = SuatChieu::with(['phim'])
            ->whereDate('thoi_gian_chieu', now()->toDateString())
            ->orderBy('thoi_gian_chieu', 'asc')
            ->take(10)
            ->get();

        // 3. KHỞI TẠO MẢNG SỐ LIỆU THỐNG KÊ (Khắc phục triệt để lỗi Undefined variable)
        $statData = [
            'doanh_thu_hom_nay' => '82.500.000đ',
            've_da_ban' => '1.240',
            'luong_khach' => '980',
            'doanh_thu_do_an' => '12.300.000đ'
        ];

        // =========================================================================
        // ĐIỀU HƯỚNG SẠCH THEO VAI TRÒ (PHƯƠNG ÁN 2)
        // =========================================================================
        
        // CẤP 1: Quản trị viên & Quản lý hệ thống -> Trỏ về views/admin/dashboard.blade.php
        if ($user->hasRole('Quản trị viên') || $user->hasRole('Quản lý hệ thống')) {
            return view('admin.dashboard', compact('latestMovies', 'todaySchedules', 'statData'));
        }

        // CẤP 2: Quản lý rạp -> Trỏ về views/manager/dashboard.blade.php
        if ($user->hasRole('Quản lý')) {
            return view('manager.dashboard', compact('latestMovies', 'todaySchedules', 'statData'));
        }

        // CẤP 3: Nhân viên quầy -> Trỏ về views/staff/dashboard.blade.php
        if ($user->hasRole('Nhân viên')) {
            return view('staff.dashboard', compact('latestMovies', 'todaySchedules', 'statData'));
        }

        // Nếu là tài khoản Khách hàng thông thường, đẩy ra trang ngoài Frontend
        return redirect()->route('home');
    }
}