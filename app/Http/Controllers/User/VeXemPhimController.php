<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\VeXemPhim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VeXemPhimController extends Controller
{
    public function index(Request $request)
    {
        $allowedStatuses = ['da_thanh_toan', 'da_su_dung', 'da_huy'];
        $activeStatus = in_array($request->query('trang_thai'), $allowedStatuses, true)
            ? $request->query('trang_thai')
            : null;

        $baseQuery = VeXemPhim::where('nguoi_dung_id', Auth::id());

        $ticketStats = [
            'total' => (clone $baseQuery)->count(),
            'paid' => (clone $baseQuery)->where('trang_thai', 'da_thanh_toan')->count(),
            'used' => (clone $baseQuery)->where('trang_thai', 'da_su_dung')->count(),
            'cancelled' => (clone $baseQuery)->where('trang_thai', 'da_huy')->count(),
        ];

        $nextTicket = (clone $baseQuery)
            ->where('trang_thai', 'da_thanh_toan')
            ->whereNotNull('thoi_gian_chieu')
            ->where('thoi_gian_chieu', '>=', now())
            ->orderBy('thoi_gian_chieu')
            ->first();

        $cancelMinutes = (int) SystemSetting::getValue('ticket_cancel_minutes', 5);

        $veXemPhims = (clone $baseQuery)
            ->when($activeStatus, fn ($query) => $query->where('trang_thai', $activeStatus))
            ->latest()
            ->paginate(8)
            ->withQueryString();

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
        abort_if($veXemPhim->nguoi_dung_id !== Auth::id(), 403);

        $cancelMinutes = (int) SystemSetting::getValue('ticket_cancel_minutes', 5);

        return view('user.ve_xem_phim.show', compact('veXemPhim', 'cancelMinutes'));
    }

    public function cancel(VeXemPhim $veXemPhim)
    {
        abort_if($veXemPhim->nguoi_dung_id !== Auth::id(), 403);

        if (! $veXemPhim->canCancel()) {
            $minutes = (int) SystemSetting::getValue('ticket_cancel_minutes', 5);

            return back()->with('error', 'Chỉ được hủy vé trong vòng ' . $minutes . ' phút sau khi đặt và khi vé chưa sử dụng.');
        }

        $refundPercent = max(0, min(100, (float) SystemSetting::getValue('refund_percent', 50)));

        $veXemPhim->update([
            'trang_thai' => 'da_huy',
            'tien_hoan' => ((float) $veXemPhim->tong_tien) * $refundPercent / 100,
        ]);

        // Nếu vé đã được cộng điểm trước đó thì trừ lại điểm khi khách hủy vé
        $lichSuCongDiem = $veXemPhim->lichSuDiems()
            ->where('loai_giao_dich', 'cong_diem')
            ->first();

        if ($lichSuCongDiem && $veXemPhim->nguoiDung?->thanhVien) {
            $veXemPhim->nguoiDung->thanhVien->truDiem(
                $lichSuCongDiem->so_diem,
                $veXemPhim,
                'Trừ điểm do hủy vé phim ' . $veXemPhim->ten_phim
            );
        }

        return redirect()
            ->route('user.ve_xem_phim.index')
            ->with('success', 'Hủy vé thành công. Bạn được hoàn ' . $refundPercent . '% giá trị vé.');
    }
}
