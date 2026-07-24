<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KiemTraQuyen
{
    /**
     * Chặn đứng mọi truy cập trực tiếp qua URL nếu không có quyền
     */
    public function handle(Request $request, Closure $next, string $maQuyen): Response
    {
        if (!coQuyen($maQuyen)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tài khoản của bạn không có quyền thực hiện thao tác này.'
                ], 403);
            }

            return redirect()->route('admin.dashboard')
                ->with('error', 'Tài khoản của bạn không có quyền truy cập chức năng này!');
        }

        return $next($request);
    }
}