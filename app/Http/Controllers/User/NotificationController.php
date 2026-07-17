<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ThongBaoCaNhan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $allowedTypes = ['he_thong', 've', 'diem', 'voucher', 'hang_thanh_vien'];
        $activeType = in_array($request->query('loai'), $allowedTypes, true)
            ? $request->query('loai')
            : null;

        $baseQuery = ThongBaoCaNhan::where('nguoi_dung_id', $user->id);

        $notificationStats = [
            'total' => (clone $baseQuery)->count(),
            'unread' => (clone $baseQuery)->where('da_doc', false)->count(),
            'read' => (clone $baseQuery)->where('da_doc', true)->count(),
            'he_thong' => (clone $baseQuery)->where('loai_thong_bao', 'he_thong')->count(),
            've' => (clone $baseQuery)->where('loai_thong_bao', 've')->count(),
            'diem' => (clone $baseQuery)->where('loai_thong_bao', 'diem')->count(),
            'voucher' => (clone $baseQuery)->where('loai_thong_bao', 'voucher')->count(),
            'hang_thanh_vien' => (clone $baseQuery)->where('loai_thong_bao', 'hang_thanh_vien')->count(),
        ];

        $latestUnread = (clone $baseQuery)
            ->where('da_doc', false)
            ->latest()
            ->first();

        $thongBaos = (clone $baseQuery)
            ->when($activeType, fn ($query) => $query->where('loai_thong_bao', $activeType))
            ->latest()
            ->paginate(8)
            ->withQueryString();

        ThongBaoCaNhan::where('nguoi_dung_id', $user->id)
            ->where('da_doc', false)
            ->update([
                'da_doc' => true,
                'doc_luc' => now(),
            ]);

        return view('user.thong_bao.index', compact(
            'thongBaos',
            'notificationStats',
            'activeType',
            'latestUnread'
        ));
    }
}
