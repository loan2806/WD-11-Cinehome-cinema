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
        if (! \coQuyen($maQuyen)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tài khoản của bạn không có quyền thực hiện thao tác này.'
                ], 403);
            }

            $user = $request->user();

            // 🌟 Nếu là Nhân viên không có quyền Admin -> Điều hướng về đúng trang Staff
            if ($user && ($user->hasRole('Nhân viên') || $user->vai_tro === 'nhan_vien')) {
                return redirect()->route('staff.dashboard')
                    ->with('error', 'Tài khoản nhân viên không có quyền truy cập trang quản trị Admin!');
            }

            // 🌟 Nếu chuyển hướng từ một trang hợp lệ khác -> Quay lại trang trước
            if (url()->previous() && url()->previous() !== $request->fullUrl()) {
                return redirect()->back()
                    ->with('error', 'Tài khoản của bạn không có quyền truy cập chức năng này!');
            }

            // 🌟 Mặc định chuyển về Trang chủ (Tránh gửi về admin.dashboard gây lặp vô tận)
            return redirect()->route('home')
                ->with('error', 'Tài khoản của bạn không có quyền truy cập chức năng này!');
        }

        return $next($request);
    }
}
