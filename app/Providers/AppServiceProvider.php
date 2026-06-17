<?php

namespace App\Providers;

use App\Models\AdminNotification;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
         * Đảm bảo tài khoản giữ vai trò "Quản trị viên" luôn được phép truy cập mọi liên kết URL,
         * vượt qua tất cả các chốt chặn middleware permission mà không bao giờ bị dính lỗi 403 Forbidden.
         */
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Quản trị viên') ? true : null;
        });

         View::composer('layouts.admin', function ($view) {

        $notifications = AdminNotification::latest()
            ->take(5)
            ->get();

        $count = AdminNotification::count();

        $view->with([
            'adminNotifications' => $notifications,
            'notificationCount' => $count,
        ]);
    });
    }
}