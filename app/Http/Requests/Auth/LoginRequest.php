<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): bool|array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'mat_khau' => ['required', 'string'], // Đã sửa từ 'password' thành 'mat_khau'
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // CHÚ Ý: Hệ thống Auth của Laravel yêu cầu mảng chứng thực phải có key là 'password'.
        // Chúng ta lấy dữ liệu từ ô 'mat_khau' và gán vào key 'password' để gửi cho Laravel đối chiếu với DB.
        $credentials = [
            'email' => $this->boolean('email') ? $this->email : $this->string('email'),
            'password' => $this->string('mat_khau'),
        ];

        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }
        // Tài khoản do Admin tạo nhưng chưa xác thực email
        if (
            Auth::user()->bat_buoc_xac_thuc_email &&
            is_null(Auth::user()->email_verified_at)
        ) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Vui lòng xác thực email trước khi đăng nhập.',
            ]);
        }

        // Nếu tài khoản bị khóa thì đăng xuất ngay
        if (! Auth::user()->trang_thai_hoat_dong) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')) . '|' . $this->ip());
    }
}
