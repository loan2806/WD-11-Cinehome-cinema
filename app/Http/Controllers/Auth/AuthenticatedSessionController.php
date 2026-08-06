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

        // ÉP CHUYỂN HƯỚNG TRỰC TIẾP VỀ /dashboard (Không dùng intended để tránh bị đẩy ngược lại trang chủ)
        return redirect()->route('dashboard');
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