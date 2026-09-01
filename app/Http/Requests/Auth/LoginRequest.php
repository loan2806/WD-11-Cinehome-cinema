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
     * Determine if the user is authorized to make the request.
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

        /*
        |--------------------------------------------------------------------------
        | Lấy thông tin đăng nhập
        |--------------------------------------------------------------------------
        */

        $email = trim((string) $this->input('email'));
        $password = (string) $this->input('mat_khau');

        /*
        |--------------------------------------------------------------------------
        | XÓA trạng thái email chưa xác thực cũ
        |--------------------------------------------------------------------------
        |
        | Rất quan trọng:
        | Nếu trước đó Đạt Bình chưa xác thực và có session
        | unverified_email thì khi người dùng nhập một email khác,
        | không được giữ lại email cũ.
        |
        */

        session()->forget('unverified_email');

        /*
        |--------------------------------------------------------------------------
        | Tìm tài khoản
        |--------------------------------------------------------------------------
        |
        | Có withTrashed() để vẫn xử lý được tài khoản đang trong
        | thời gian 14 ngày khôi phục.
        |
        */

        $userModel = config(
            'auth.providers.users.model',
            \App\Models\User::class
        );

        $user = $userModel::withTrashed()
            ->where('email', $email)
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Tài khoản không tồn tại
        |--------------------------------------------------------------------------
        */

        if (! $user) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Tài khoản hoặc mật khẩu không chính xác.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Kiểm tra mật khẩu
        |--------------------------------------------------------------------------
        */

        if (! Hash::check($password, $user->password)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Tài khoản hoặc mật khẩu không chính xác.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | TÀI KHOẢN ĐANG Ở THÙNG RÁC
        |--------------------------------------------------------------------------
        */

        if ($user->trashed()) {

            /*
            |--------------------------------------------------------------
            | Còn trong 14 ngày -> khôi phục
            |--------------------------------------------------------------
            */

            if (
                $user->deleted_at &&
                $user->deleted_at->gte(now()->subDays(14))
            ) {
                $user->restore();

                Auth::login(
                    $user,
                    $this->boolean('remember')
                );
            }

            /*
            |--------------------------------------------------------------
            | Quá 14 ngày -> xóa vĩnh viễn
            |--------------------------------------------------------------
            */

            else {

                $user->forceDelete();

                RateLimiter::hit(
                    $this->throttleKey()
                );

                throw ValidationException::withMessages([
                    'email' =>
                        'Tài khoản của bạn đã vượt quá thời hạn 14 ngày khôi phục và đã bị xóa vĩnh viễn.',
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | TÀI KHOẢN BÌNH THƯỜNG
        |--------------------------------------------------------------------------
        */

        else {

            Auth::login(
                $user,
                $this->boolean('remember')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Kiểm tra đăng nhập thành công
        |--------------------------------------------------------------------------
        */

        if (! Auth::check()) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Tài khoản hoặc mật khẩu không chính xác.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | EMAIL CHƯA XÁC THỰC
        |--------------------------------------------------------------------------
        |
        | Đây là phần quan trọng nhất.
        |
        | Nếu tài khoản vừa nhập chưa xác thực:
        |
        | 1. Lấy chính email vừa đăng nhập.
        | 2. Logout.
        | 3. Lưu chính email đó vào session.
        | 4. Login page sẽ dùng session này để hiện nút gửi lại.
        |
        */

        if (
            $user->bat_buoc_xac_thuc_email &&
            is_null($user->email_verified_at)
        ) {

            /*
            | Chỉ lưu email của tài khoản vừa submit.
            */
            session()->flash(
                'unverified_email',
                $user->email
            );

            Auth::logout();

            throw ValidationException::withMessages([
                'email' =>
                    'Vui lòng xác thực email trước khi đăng nhập.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | TÀI KHOẢN BỊ KHÓA
        |--------------------------------------------------------------------------
        */

        if (! $user->trang_thai_hoat_dong) {

            Auth::logout();

            /*
            | Không cho hiển thị nút gửi email xác thực
            | khi tài khoản bị khóa.
            */
            session()->forget('unverified_email');

            throw ValidationException::withMessages([
                'email' =>
                    'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Đăng nhập thành công
        |--------------------------------------------------------------------------
        |
        | Nếu login thành công thì chắc chắn không được giữ
        | unverified_email của tài khoản trước.
        |
        */

        session()->forget('unverified_email');

        RateLimiter::clear(
            $this->throttleKey()
        );
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (
            ! RateLimiter::tooManyAttempts(
                $this->throttleKey(),
                5
            )
        ) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn(
            $this->throttleKey()
        );

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
        return Str::transliterate(
            Str::lower(
                $this->string('email')
            )
            . '|'
            . $this->ip()
        );
    }
}