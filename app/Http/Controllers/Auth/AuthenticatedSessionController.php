<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        // 1. Lấy thông tin email và mật khẩu từ request (hỗ trợ cả 'password' và 'mat_khau')
        $email = trim((string) $request->input('email'));
        $password = (string) ($request->input('password') ?? $request->input('mat_khau'));

        // 2. Tìm tài khoản trong CSDL (bao gồm cả tài khoản đang ở trong thùng rác)
        $userModel = config('auth.providers.users.model', \App\Models\User::class);
        $user = $userModel::withTrashed()->where('email', $email)->first();

        // 3. Nếu tìm thấy tài khoản và mật khẩu chính xác
        if ($user && Hash::check($password, $user->password)) {
            // Kiểm tra xem tài khoản có đang trong trạng thái chờ xóa (Soft Deleted) không
            if ($user->trashed()) {
                $deletedAt = Carbon::parse($user->deleted_at);

                // Nếu còn trong thời hạn 14 ngày
                if ($deletedAt->gte(now()->subDays(14))) {
                    // Khôi phục tài khoản
                    $user->restore();

                    // Đăng nhập tài khoản
                    Auth::login($user, $request->boolean('remember'));

                    // Kiểm tra xác thực email nếu hệ thống yêu cầu
                    if ($user->bat_buoc_xac_thuc_email && ! $user->hasVerifiedEmail()) {
                        Auth::logout();

                        return redirect()
                            ->route('login')
                            ->with('error', 'Tài khoản chưa được xác thực email. Vui lòng kiểm tra email và nhấn vào liên kết xác thực.');
                    }

                    $request->session()->regenerate();

                    return redirect()
                        ->route('dashboard')
                        ->with('success', 'Tài khoản của bạn đã được khôi phục thành công!');
                } else {
                    // Đã quá 14 ngày -> Thực hiện xóa vĩnh viễn
                    $user->forceDelete();

                    return redirect()
                        ->route('login')
                        ->with('error', 'Tài khoản của bạn đã vượt quá thời hạn 14 ngày khôi phục và đã bị xóa vĩnh viễn.');
                }
            }
        }

        // 4. Nếu tài khoản không bị xóa, tiến hành đăng nhập bình thường qua Laravel Auth
        $request->authenticate();

        // 5. Lấy tài khoản vừa đăng nhập thành công
        $user = Auth::user();

        // 6. Kiểm tra xác thực email đối với tài khoản bình thường
        if (
            $user->bat_buoc_xac_thuc_email &&
            ! $user->hasVerifiedEmail()
        ) {
            Auth::logout();

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Tài khoản chưa được xác thực email. Vui lòng kiểm tra email và nhấn vào liên kết xác thực.'
                );
        }

        // 7. Tạo session mới
        $request->session()->regenerate();

        // 8. Điều hướng theo vai trò
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