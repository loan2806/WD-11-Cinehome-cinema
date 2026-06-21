<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\VeXemPhim;
use Illuminate\Support\Facades\Auth;

class VeXemPhimController extends Controller
{
    public function index()
    {
        $veXemPhims = VeXemPhim::where('nguoi_dung_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('user.ve_xem_phim.index', compact('veXemPhims'));
    }

    public function show(VeXemPhim $veXemPhim)
    {
        abort_if($veXemPhim->nguoi_dung_id !== Auth::id(), 403);

        return view('user.ve_xem_phim.show', compact('veXemPhim'));
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

        return redirect()
            ->route('user.ve_xem_phim.index')
            ->with('success', 'Hủy vé thành công. Bạn được hoàn ' . $refundPercent . '% giá trị vé.');
    }
}
