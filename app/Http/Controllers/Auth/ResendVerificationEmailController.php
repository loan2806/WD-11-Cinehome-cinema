<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class ResendVerificationEmailController extends Controller
{
    /**
     * Gửi lại email xác thực cho một tài khoản CHƯA đăng nhập được (vì
     * chưa xác thực email nên không thể tạo phiên đăng nhập để dùng route
     * "verification.send" — route đó yêu cầu middleware auth).
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $throttleKey = 'resend-verification:' . strtolower($request->email);

        if (RateLimiter::tooManyAttempts($throttleKey, 1)) {
            return redirect()
                ->route('login')
                ->with('error', 'Bạn vừa yêu cầu gửi lại email xác thực. Vui lòng đợi ít phút rồi thử lại.');
        }

        RateLimiter::hit($throttleKey, 60);

        $user = NguoiDung::where('email', $request->email)->first();

        // Không tiết lộ tài khoản có tồn tại hay không; luôn trả về cùng
        // một thông báo trung lập.
        if ($user && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        return redirect()
            ->route('login')
            ->with('success', 'Nếu email đó tồn tại và chưa xác thực, chúng tôi đã gửi lại liên kết xác thực mới.');
    }
}
