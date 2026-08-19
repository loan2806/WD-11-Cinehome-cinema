<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ThongBaoCaNhan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $this->kiemTraNhanVien($user);

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

    public function markAllRead()
    {
        $user = Auth::user();

        $this->kiemTraNhanVien($user);

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

    /**
     * API nhẹ để layout Staff tự cập nhật chuông mà không cần F5.
     */
    public function latest()
    {
        $user = Auth::user();

        $this->kiemTraNhanVien($user);

        $baseQuery = ThongBaoCaNhan::where('nguoi_dung_id', $user->id);

        $unread = (clone $baseQuery)
            ->where('da_doc', false)
            ->count();

        $items = (clone $baseQuery)
            ->latest()
            ->take(10)
            ->get()
            ->map(function (ThongBaoCaNhan $item) {
                return [
                    'id' => $item->id,
                    'tieu_de' => $item->tieu_de,
                    'noi_dung' => $item->noi_dung,
                    'loai_thong_bao' => $item->loai_thong_bao,
                    'duong_dan' => $item->duong_dan,
                    'da_doc' => (bool) $item->da_doc,
                    'created_human' => $item->created_at?->diffForHumans(),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'unread' => $unread,
            'items' => $items,
        ]);
    }

    private function kiemTraNhanVien($user): void
    {
        abort_unless(
            $user && (
                ($user->vai_tro ?? null) === 'nhan_vien'
                || (method_exists($user, 'hasRole') && $user->hasRole('Nhân viên'))
            ),
            403,
            'Bạn không có quyền truy cập khu vực thông báo nhân viên.'
        );
    }
}