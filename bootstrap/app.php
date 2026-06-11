<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Cấu hình đẩy khách chưa đăng nhập về trang chủ
        $middleware->redirectGuestsTo('/');
        
        // Đăng ký Middleware phân quyền hệ thống CineHome
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        // CHUẨN HÓA LARAVEL 12: Đưa vào mảng cấu hình nhóm web bằng Class tham chiếu
        // Giúp bộ xóa cache chạy ngay sau khi Session đã được thiết lập an toàn
        $middleware->web(append: [
            \App\Http\Middleware\ClearBrowserCache::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();