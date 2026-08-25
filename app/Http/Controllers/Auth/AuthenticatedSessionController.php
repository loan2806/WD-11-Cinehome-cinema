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
        // Kiểm tra email + mật khẩu, tài khoản bị khóa, và bắt buộc xác thực
        // email (nếu có) — LoginRequest::authenticate() tự ném
        // ValidationException và đăng xuất ngay khi các điều kiện này không
        // thỏa, nên tới được đây nghĩa là tài khoản đã hợp lệ hoàn toàn.
        $request->authenticate();

        // Tạo session mới
        $request->session()->regenerate();

        // Điều hướng theo vai trò
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
