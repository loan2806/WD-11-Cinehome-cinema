<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ThongBaoCaNhan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Danh sách tất cả thông báo của nhân viên đang đăng nhập.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        abort_unless(
            $user && ($user->hasRole('Nhân viên') || $user->vai_tro === 'nhan_vien'),
            403,
            'Bạn không có quyền truy cập khu vực thông báo nhân viên.'
        );

        $allowedTypes = [
            'he_thong',
            've',
            'diem',
            'voucher',
            'hang_thanh_vien',
            'tai_khoan',
        ];

        $activeType = in_array($request->query('loai'), $allowedTypes, true)
            ? $request->query('loai')
            : null;

        $baseQuery = ThongBaoCaNhan::where('nguoi_dung_id', $user->id);

        $notificationStats = [
            'total' => (clone $baseQuery)->count(),
            'unread' => (clone $baseQuery)->where('da_doc', false)->count(),
            'read' => (clone $baseQuery)->where('da_doc', true)->count(),
        ];

        $thongBaos = (clone $baseQuery)
            ->when(
                $activeType,
                fn ($query) => $query->where('loai_thong_bao', $activeType)
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Khi vào trang "Xem tất cả", coi các thông báo hiện có là đã đọc.
        ThongBaoCaNhan::where('nguoi_dung_id', $user->id)
            ->where('da_doc', false)
            ->update([
                'da_doc' => true,
                'doc_luc' => now(),
            ]);

        return view('staff.notifications.index', compact(
            'thongBaos',
            'notificationStats',
            'activeType'
        ));
    }

    /**
     * Đánh dấu toàn bộ thông báo của CHÍNH nhân viên hiện tại là đã đọc.
     */
    public function markAllRead()
    {
        $user = Auth::user();

        abort_unless(
            $user && ($user->hasRole('Nhân viên') || $user->vai_tro === 'nhan_vien'),
            403
        );

        ThongBaoCaNhan::where('nguoi_dung_id', $user->id)
            ->where('da_doc', false)
            ->update([
                'da_doc' => true,
                'doc_luc' => now(),
            ]);

        return response()->json([
            'success' => true,
        ]);
    }
}