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
        /*
        |--------------------------------------------------------------------------
        | XÓA EMAIL CHƯA XÁC THỰC CŨ
        |--------------------------------------------------------------------------
        |
        | Tránh trường hợp:
        |
        | Tài khoản A chưa xác thực
        | -> có nút gửi lại email A
        |
        | Sau đó người dùng đăng nhập tài khoản B
        | -> vẫn còn nút gửi lại email A.
        |
        */
        session()->forget('unverified_email');

        /*
        |--------------------------------------------------------------------------
        | LẤY EMAIL VÀ MẬT KHẨU
        |--------------------------------------------------------------------------
        */

        $email = trim((string) $request->input('email'));

        $password = (string) (
            $request->input('password')
            ?? $request->input('mat_khau')
        );

        /*
        |--------------------------------------------------------------------------
        | TÌM TÀI KHOẢN KỂ CẢ SOFT DELETE
        |--------------------------------------------------------------------------
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
        | TÀI KHOẢN ĐANG Ở THÙNG RÁC
        |--------------------------------------------------------------------------
        */

        if ($user && $user->trashed()) {

            /*
            |--------------------------------------------------------------------------
            | KIỂM TRA MẬT KHẨU
            |--------------------------------------------------------------------------
            */

            if (Hash::check($password, $user->password)) {

                $deletedAt = Carbon::parse($user->deleted_at);

                /*
                |--------------------------------------------------------------------------
                | CÒN TRONG 14 NGÀY -> KHÔI PHỤC
                |--------------------------------------------------------------------------
                */

                if ($deletedAt->gte(now()->subDays(14))) {

                    $user->restore();

                    Auth::login(
                        $user,
                        $request->boolean('remember')
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | KIỂM TRA EMAIL CHƯA XÁC THỰC
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $user->bat_buoc_xac_thuc_email &&
                        !$user->hasVerifiedEmail()
                    ) {

                        $emailChuaXacThuc = $user->email;

                        Auth::logout();

                        /*
                        |--------------------------------------------------------------------------
                        | LƯU ĐÚNG EMAIL CHƯA XÁC THỰC
                        |--------------------------------------------------------------------------
                        */

                        session()->flash(
                            'unverified_email',
                            $emailChuaXacThuc
                        );

                        return redirect()
                            ->route('login')
                            ->with(
                                'error',
                                'Tài khoản chưa được xác thực email. Vui lòng kiểm tra email và nhấn vào liên kết xác thực.'
                            );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | ĐĂNG NHẬP THÀNH CÔNG
                    |--------------------------------------------------------------------------
                    */

                    $request->session()->regenerate();

                    return redirect()
                        ->route('dashboard')
                        ->with(
                            'success',
                            'Tài khoản của bạn đã được khôi phục thành công!'
                        );
                }

                /*
                |--------------------------------------------------------------------------
                | QUÁ 14 NGÀY -> XÓA VĨNH VIỄN
                |--------------------------------------------------------------------------
                */

                $user->forceDelete();

                return redirect()
                    ->route('login')
                    ->with(
                        'error',
                        'Tài khoản của bạn đã vượt quá thời hạn 14 ngày khôi phục và đã bị xóa vĩnh viễn.'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | SAI MẬT KHẨU
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route('login')
                ->withInput([
                    'email' => $email,
                    'auth_modal' => 'login',
                ])
                ->with(
                    'error',
                    'Tài khoản hoặc mật khẩu không chính xác.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | TÀI KHOẢN BÌNH THƯỜNG
        |--------------------------------------------------------------------------
        |
        | LoginRequest sẽ xử lý:
        |
        | - Sai tài khoản/mật khẩu
        | - Chưa xác thực email
        | - Tài khoản bị khóa
        | - Rate limit
        |
        */

        $request->authenticate();

        /*
        |--------------------------------------------------------------------------
        | TẠO SESSION MỚI
        |--------------------------------------------------------------------------
        */

        $request->session()->regenerate();

        /*
        |--------------------------------------------------------------------------
        | ĐIỀU HƯỚNG
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('dashboard');
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