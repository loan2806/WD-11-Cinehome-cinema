<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;

class VerifyEmailController extends Controller
{
    /**
     * Xác thực email cho tài khoản chưa đăng nhập.
     */
    public function __invoke(Request $request, $id, $hash): RedirectResponse
    {
        // Tìm tài khoản
        $user = NguoiDung::findOrFail($id);

        // Kiểm tra hash email có đúng không
        if (! hash_equals(
            sha1($user->getEmailForVerification()),
            $hash
        )) {
            abort(403, 'Liên kết xác thực email không hợp lệ.');
        }

        // Nếu đã xác thực rồi
        if ($user->hasVerifiedEmail()) {
            return redirect()
                ->route('login')
                ->with('success', 'Email đã được xác thực trước đó. Bạn có thể đăng nhập.');
        }

        // Đánh dấu email đã xác thực
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()
            ->route('login')
            ->with(
                'success',
                'Xác thực email thành công! Bạn có thể đăng nhập ngay bây giờ.'
            );
    }
}