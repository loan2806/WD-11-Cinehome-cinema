<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Đăng ký các dịch vụ ứng dụng hệ thống.
     */
    public function register(): void
    {
        //
    }

    /**
     * Khởi chạy các dịch vụ ứng dụng (Mở khóa đặc quyền Quản trị viên).
     */
    public function boot(): void
    {
        /**
         * THIẾT LẬP QUYỀN TỐI CAO (SUPER ADMIN BYPASS)
         * Đảm bảo tài khoản giữ vai trò "Quản trị viên" hoặc "Quản lý hệ thống"
         * luôn được phép truy cập mọi liên kết URL,
         * vượt qua tất cả các chốt chặn middleware permission mà không bao giờ bị dính lỗi 403 Forbidden.
         */
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('Quản trị viên') || $user->hasRole('Quản lý hệ thống')) {
                return true;
            }
            return null;
        });
    }
}