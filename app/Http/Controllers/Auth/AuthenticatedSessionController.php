<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Hiển thị giao diện màn hình đăng nhập công cộng.
     */
    public function create(): View
    {
        return view('auth.dang_nhap');
    }

    /**
     * Xử lý xác thực thông tin đăng nhập và điều hướng phân quyền tài khoản.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // 1. Thực hiện kiểm tra thông tin tài khoản và mật khẩu
        $request->authenticate();

        // 2. Tái tạo lại mã Session bảo mật chống tấn công giả mạo phiên
        $request->session()->regenerate();

        $user = Auth::user();

        /**
         * ĐIỀU HƯỚNG BAN VẬN HÀNH (ADMIN / QUẢN LÝ HỆ THỐNG / QUẢN LÝ RẠP / NHÂN VIÊN VÀO BACKEND)
         * Khắc phục hoàn toàn lỗi RouteNotFoundException bằng cách hướng toàn bộ nhân sự rạp phim
         * về trang admin.dashboard chung để nhận diện phân loại menu thông minh.
         */
        if (
            $user->hasRole('Quản trị viên') || 
            $user->hasRole('Quản lý hệ thống') || 
            $user->hasRole('Quản lý') || 
            $user->hasRole('Nhân viên') || 
            $user->vai_tro === 'admin' || 
            $user->vai_tro === 'nhan_vien'
        ) {
            return redirect()->route('admin.dashboard');
        }

        // Khách hàng mặc định sẽ được hướng thẳng ra trang chủ công cộng ngoài Frontend
        return redirect()->intended(route('home', absolute: false));
    }

    /**
     * Xử lý đăng xuất phá hủy phiên làm việc của tài khoản.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
