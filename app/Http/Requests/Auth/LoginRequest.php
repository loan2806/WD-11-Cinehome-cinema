<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
            'mat_khau' => ['required', 'string'],
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

        $email = $this->boolean('email') ? $this->email : $this->string('email');
        $password = $this->string('mat_khau');

        // 1. Kiểm tra tài khoản bao gồm cả tài khoản nằm trong thùng rác (Soft Delete)
        $userModel = config('auth.providers.users.model', \App\Models\User::class);
        $user = $userModel::withTrashed()->where('email', $email)->first();

        // 2. Nếu tài khoản tồn tại và đang ở trạng thái bị xóa tạm thời (trashed)
        if ($user && $user->trashed()) {
            if (Hash::check($password, $user->password)) {
                // Kiểm tra xem thời gian xóa có nằm trong vòng 14 ngày không
                if ($user->deleted_at && $user->deleted_at->gte(now()->subDays(14))) {
                    // Tự động khôi phục tài khoản
                    $user->restore();

                    // Thực hiện đăng nhập
                    Auth::login($user, $this->boolean('remember'));
                } else {
                    // Quá 14 ngày -> Xóa vĩnh viễn tài khoản
                    $user->forceDelete();

                    RateLimiter::hit($this->throttleKey());

                    throw ValidationException::withMessages([
                        'email' => 'Tài khoản của bạn đã vượt quá thời hạn 14 ngày khôi phục và đã bị xóa vĩnh viễn.',
                    ]);
                }
            } else {
                // Mật khẩu không chính xác
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'email' => __('auth.failed'),
                ]);
            }
        } else {
            // 3. Nếu tài khoản hoạt động bình thường, tiến hành Auth::attempt chuẩn của Laravel
            $credentials = [
                'email' => $email,
                'password' => $password,
            ];

            if (! Auth::attempt($credentials, $this->boolean('remember'))) {
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'email' => __('auth.failed'),
                ]);
            }
        }

        // 4. Lịch trình kiểm tra cũ: Tài khoản do Admin tạo nhưng chưa xác thực email
        if (
            Auth::user()->bat_buoc_xac_thuc_email &&
            is_null(Auth::user()->email_verified_at)
        ) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Vui lòng xác thực email trước khi đăng nhập.',
            ]);
        }

        // 5. Lịch trình kiểm tra cũ: Nếu tài khoản bị khóa thì đăng xuất ngay
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