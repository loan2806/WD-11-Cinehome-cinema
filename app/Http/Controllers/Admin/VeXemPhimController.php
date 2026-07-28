<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\VeXemPhim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class VeXemPhimController extends Controller
{
    public function index(Request $request)
    {
        // 1. Cập nhật các vé đã hết giờ chiếu
        VeXemPhim::where('trang_thai', 'da_thanh_toan')
            ->where('thoi_gian_chieu', '<', now('Asia/Ho_Chi_Minh'))
            ->update(['trang_thai' => 'het_han']);

        // 2. Dọn dẹp & XÓA SẠCH các vé chờ thanh toán đã quá thời hạn 7 phút
        $expiredPendingTickets = VeXemPhim::where('trang_thai', 'cho_thanh_toan')
            ->whereNotNull('thoi_gian_het_han')
            ->where('thoi_gian_het_han', '<', now())
            ->get();

        foreach ($expiredPendingTickets as $pendingTicket) {
            $seats = explode(',', (string) $pendingTicket->ma_ghe);
            foreach ($seats as $seat) {
                $seatCode = strtoupper(trim($seat));
                if ($seatCode) {
                    Cache::forget("seat_lock:suat:{$pendingTicket->suat_chieu_id}:seat:{$seatCode}");
                }
            }
            // Xóa cứng khỏi CSDL để không hiển thị thành đơn hủy ở Vé của tôi
            $pendingTicket->delete();
        }

        $allowedStatuses = [
            'cho_thanh_toan',
            'da_thanh_toan',
            'da_su_dung',
            'da_huy',
            'het_han'
        ];

        $activeStatus = in_array(
            $request->query('trang_thai'),
            $allowedStatuses,
            true
        )
            ? $request->query('trang_thai')
            : null;

        $baseQuery = VeXemPhim::where(
            'nguoi_dung_id',
            Auth::id()
        );

        $ticketStats = [
            'total' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)
                ->where('trang_thai', 'cho_thanh_toan')
                ->count(),
            'paid' => (clone $baseQuery)
                ->where('trang_thai', 'da_thanh_toan')
                ->count(),
            'used' => (clone $baseQuery)
                ->where('trang_thai', 'da_su_dung')
                ->count(),
            'cancelled' => (clone $baseQuery)
                ->where('trang_thai', 'da_huy')
                ->count(),
            'expired' => (clone $baseQuery)
                ->where('trang_thai', 'het_han')
                ->count(),
        ];

        $nextTicket = (clone $baseQuery)
            ->whereIn('trang_thai', [
                'cho_thanh_toan',
                'da_thanh_toan'
            ])
            ->whereNotNull('thoi_gian_chieu')
            ->where('thoi_gian_chieu', '>=', now())
            ->orderBy('thoi_gian_chieu')
            ->first();

        $cancelMinutes = (int) SystemSetting::getValue(
            'ticket_cancel_minutes',
            5
        );

        $veXemPhims = (clone $baseQuery)
            ->when(
                $activeStatus,
                fn($query) => $query->where(
                    'trang_thai',
                    $activeStatus
                )
            )
            ->latest()
            ->paginate(8)
            ->withQueryString();

        $veXemPhims->getCollection()->transform(function ($ve) {
            $ve->food_items = Cache::get(
                "ve_foods:{$ve->id}",
                []
            );

            return $ve;
        });

        return view('user.ve_xem_phim.index', compact(
            'veXemPhims',
            'ticketStats',
            'nextTicket',
            'activeStatus',
            'cancelMinutes'
        ));
    }

    public function show(VeXemPhim $veXemPhim)
    {
        abort_if(
            $veXemPhim->nguoi_dung_id !== Auth::id(),
            403
        );

        if (
            $veXemPhim->trang_thai === 'cho_thanh_toan'
            && $veXemPhim->thoi_gian_het_han
            && now()->gt($veXemPhim->thoi_gian_het_han)
        ) {
            $veXemPhim->delete();

            return redirect()
                ->route('user.ve_xem_phim.index')
                ->with(
                    'error',
                    'Phiên thanh toán đã hết hạn.'
                );
        }

        if (
            $veXemPhim->trang_thai === 'da_thanh_toan'
            && $veXemPhim->thoi_gian_chieu->lt(
                now('Asia/Ho_Chi_Minh')
            )
        ) {
            $veXemPhim->update([
                'trang_thai' => 'het_han'
            ]);

            $veXemPhim->trang_thai = 'het_han';
        }

        $cancelMinutes = (int) SystemSetting::getValue(
            'ticket_cancel_minutes',
            5
        );

        $foodItems = Cache::get(
            "ve_foods:{$veXemPhim->id}",
            []
        );

        return view(
            'user.ve_xem_phim.show',
            compact(
                'veXemPhim',
                'cancelMinutes',
                'foodItems'
            )
        );
    }
}
