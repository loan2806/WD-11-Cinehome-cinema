<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\KiemTraQuyen;
use App\Http\Middleware\Cors;
use Illuminate\Http\Request;

require_once __DIR__.'/../app/helpers.php';

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // KHAI BÁO ALIAS 'quyen' CHO LARAVEL NHẬN DIỆN
        $middleware->alias([
            'quyen' => KiemTraQuyen::class,
            'cors' => Cors::class,
        ]);

        // CORS cho tất cả requests
        $middleware->append(Cors::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Return JSON for API requests
        $exceptions->shouldRenderJsonWhen(function ($request, Throwable $e) {
            return $request->is('admin/*') || $request->expectsJson();
        });
    })->create();
