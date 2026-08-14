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
        // 1. Kiểm tra email + mật khẩu
        $request->authenticate();

        // 2. Lấy tài khoản vừa đăng nhập
        $user = Auth::user();

        // 3. Nếu tài khoản bắt buộc xác thực email nhưng chưa xác thực
        if (
            $user->bat_buoc_xac_thuc_email &&
            ! $user->hasVerifiedEmail()
        ) {
            // Đăng xuất ngay, không cho vào hệ thống
            Auth::logout();

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Tài khoản chưa được xác thực email. Vui lòng kiểm tra email và nhấn vào liên kết xác thực.'
                );
        }

        // 4. Tạo session mới
        $request->session()->regenerate();

        // 5. Điều hướng theo vai trò
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
