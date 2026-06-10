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
     * Display the login view.
     */
    public function create(): View
    {
        // Gọi đúng file dang_nhap.blade.php tiếng Việt của bạn
        return view('auth.dang_nhap');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // GIẢI PHÁP ĐA NĂNG: Kiểm tra song song cả 'admin' và 'quan_tri_vien' để không bao giờ bị lệch dữ liệu nhóm
        if ($user->vai_tro === 'admin' || $user->vai_tro === 'quan_tri_vien') {
            return redirect()->route('admin.dashboard');
        }

        // Kiểm tra song song cả 'nhan_vien' và 'staff' cho phân hệ nhân viên
        if ($user->vai_tro === 'nhan_vien' || $user->vai_tro === 'staff') {
            return redirect()->route('staff.dashboard');
        }

        return redirect()->intended(route('home'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}