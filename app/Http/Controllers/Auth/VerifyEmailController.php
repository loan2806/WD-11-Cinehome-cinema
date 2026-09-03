<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    /**
     * Xác thực email cho tài khoản chưa đăng nhập — sau khi xác thực thành
     * công, tự động đăng nhập luôn thay vì bắt quay lại nhập mật khẩu lần
     * nữa (liên kết đã được ký + đối chiếu hash email nên đủ tin cậy để
     * xem như một lần đăng nhập hợp lệ).
     */
    public function __invoke(Request $request, $id, $hash): RedirectResponse
    {
        // Tìm tài khoản
        $user = NguoiDung::findOrFail($id);
        // Kiểm tra link xác thực còn hạn và chữ ký hợp lệ
        if (! $request->hasValidSignature()) {
            abort(403, 'Liên kết xác thực email đã hết hạn hoặc không hợp lệ.');
        }
        // Kiểm tra hash email có đúng không
        if (! hash_equals(
            sha1($user->getEmailForVerification()),
            $hash
        )) {
            abort(403, 'Liên kết xác thực email không hợp lệ.');
        }

        // Tài khoản bị khóa thì vẫn không cho vào thẳng, dù link hợp lệ
        if (! $user->trang_thai_hoat_dong) {
            return redirect()
                ->route('login')
                ->with('error', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.');
        }

        $daXacThucTruoDo = $user->hasVerifiedEmail();

        // Đánh dấu email đã xác thực (nếu chưa)
        if (! $daXacThucTruoDo && $user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // Tự động đăng nhập luôn, không bắt quay lại trang đăng nhập
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('dashboard')
            ->with(
                'success',
                $daXacThucTruoDo
                    ? 'Email đã được xác thực trước đó. Chào mừng bạn quay lại!'
                    : 'Xác thực email thành công! Chào mừng bạn đến với CineHome.'
            );
    }
}
