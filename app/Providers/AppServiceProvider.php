<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        /**
         * THIẾT LẬP QUYỀN TỐI CAO (SUPER ADMIN BYPASS)
         * Đảm bảo tài khoản giữ vai trò "Quản trị viên" hoặc có giá trị cột vai_tro là 'admin'
         * luôn vượt qua tất cả các chốt chặn middleware permission mà không bao giờ bị dính lỗi 403 hay vỡ layout.
         */
        Gate::before(function ($user, $ability) {
            return ($user->hasRole('Quản trị viên') || $user->vai_tro === 'admin') ? true : null;
        });
    }
}