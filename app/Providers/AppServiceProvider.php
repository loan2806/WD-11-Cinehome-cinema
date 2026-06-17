<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /**
         * THIẾT LẬP QUYỀN TỐI CAO (SUPER ADMIN BYPASS - MERGED PERFECTLY)
         * Đảm bảo tài khoản giữ vai trò "Quản trị viên", "Quản lý hệ thống" 
         * hoặc giữ giá trị cột vai_tro là 'admin' / 'quan_ly_he_thong' 
         * luôn vượt qua tất cả các chốt chặn middleware permission mà không bao giờ bị dính lỗi 403 Forbidden.
         */
        Gate::before(function ($user, $ability) {
            if (
                $user->hasRole('Quản trị viên') || 
                $user->hasRole('Quản lý hệ thống') || 
                $user->vai_tro === 'admin' || 
                $user->vai_tro === 'quan_ly_he_thong'
            ) {
                return true;
            }
            
            return null;
        });
    }
}