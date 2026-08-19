<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
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
    })
    ->create();