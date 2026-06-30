<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.dat_lai_mat_khau', ['request' => $request]);
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. VALIDATION CÁC BIẾN TIẾNG VIỆT TỪ FORM
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'mat_khau' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'token.required' => 'Mã xác thực không hợp lệ.',
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email'    => 'Địa chỉ email không đúng định dạng.',
            'mat_khau.required' => 'Vui lòng nhập mật khẩu mới.',
            'mat_khau.confirmed' => 'Xác nhận mật khẩu mới không trùng khớp.',
        ]);

        $credentials = [
            'token'                 => $request->token,
            'email'                 => $request->email,
            'password'              => $request->mat_khau,
            'password_confirmation' => $request->mat_khau_confirmation,
        ];

        $status = Password::reset(
            $credentials,
            function (NguoiDung $user) use ($request) {
                $user->forceFill([
                    'password'       => Hash::make($request->mat_khau),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return redirect()
                ->route('login')
                ->with('status', 'Mật khẩu của bạn đã được cập nhật thành công! Vui lòng tiến hành đăng nhập.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Đường dẫn xác thực này đã hết hạn hoặc không hợp lệ. Vui lòng gửi lại yêu cầu.']);
    }
}