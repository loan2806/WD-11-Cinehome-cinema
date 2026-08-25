<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Middleware\KiemTraQuyen;
use App\Http\Middleware\Cors;

require_once __DIR__ . '/../app/helpers.php';

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'quyen' => KiemTraQuyen::class,
            'cors' => Cors::class,
        ]);

        $middleware->append(Cors::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Chỉ trả JSON khi client thực sự yêu cầu JSON (API, Ajax...)
        $exceptions->shouldRenderJsonWhen(function ($request) {
            return $request->expectsJson();
        });

        // Phiên làm việc/CSRF token hết hạn (form mở quá lâu, hoặc đăng xuất
        // sau khi session đã hết hạn) -> thay vì hiện trang lỗi 419 khô khan,
        // đưa người dùng quay lại trang trước đó (hoặc trang chủ) kèm thông
        // báo dễ hiểu để họ thử lại thao tác.
        //
        // LƯU Ý: Illuminate\Session\TokenMismatchException bị Laravel tự
        // chuyển thành Symfony HttpException(419, ...) ở prepareException()
        // TRƯỚC KHI các callback render() được đối chiếu — nên phải bắt
        // HttpException rồi tự lọc theo mã 419, bắt thẳng
        // TokenMismatchException sẽ không bao giờ khớp được.
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Phiên làm việc đã hết hạn, vui lòng tải lại trang và thử lại.',
                ], 419);
            }

            $trangTruoc = url()->previous();
            $veTrangChu = $trangTruoc && $trangTruoc !== $request->fullUrl() ? $trangTruoc : route('home');

            // Key riêng "phien_het_han" (không dùng "error") để tránh trùng
            // với các banner session('error') mà nhiều trang tự hiển thị
            // riêng — layouts.admin/user chỉ lắng nghe đúng key này cho
            // thông báo hết phiên, tránh hiện 2 lần hoặc sai ngữ cảnh.
            return redirect($veTrangChu)
                ->with('phien_het_han', 'Phiên làm việc đã hết hạn, vui lòng thử lại thao tác.');
        });
    })
    ->create();