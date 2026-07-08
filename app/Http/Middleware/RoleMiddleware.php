<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // 1. SỬA: Đổi từ is_active sang trang_thai_hoat_dong tiếng Việt
        if (!$user->trang_thai_hoat_dong) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Tài khoản của bạn đã bị khóa.');
        }

        // Mẹo thông minh: Tạo mảng ánh xạ để nếu route gọi tiếng Anh cũ ('staff' hoặc 'user') 
        // thì hệ thống tự hiểu sang tiếng Việt ('nhan_vien', 'khach_hang') mà bạn không cần sửa lại file route/web.php
        $mappedRoles = [];
        foreach ($roles as $role) {
            if ($role === 'staff') {
                $mappedRoles[] = 'nhan_vien';
            } elseif ($role === 'user') {
                $mappedRoles[] = 'khach_hang';
            } else {
                $mappedRoles[] = $role; // Giữ nguyên nếu là 'admin'
            }
        }

        // 2. Ưu tiên vai_trò tiếng Việt; nếu không khớp thì bổ sung kiểm tra vai trò Spatie
        $hasRole = in_array($user->vai_tro, $mappedRoles)
            || $user->hasAnyRole($mappedRoles);

        if (!$hasRole) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        return $next($request);
    }
}
