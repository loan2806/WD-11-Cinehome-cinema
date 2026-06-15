<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClearBrowserCache
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Ép trình duyệt xóa toàn bộ bộ nhớ đệm tĩnh khi điều hướng quay lại (Dùng Class an toàn cho Laravel 12)
        if (method_exists($response, 'header')) {
            return $response->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
                             ->header('Pragma', 'no-cache')
                             ->header('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');
        }

        return $response;
    }
}