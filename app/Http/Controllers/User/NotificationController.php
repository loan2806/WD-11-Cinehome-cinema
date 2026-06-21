<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Support\Facades\Auth;
use App\Models\ThongBaoCaNhan;

class NotificationController extends Controller
{
    public function index()
{
    $user = Auth::user();

    // Thông báo hệ thống
    $adminNotifications = AdminNotification::query()
        ->where(function ($query) use ($user) {
            $query->where('audience', 'all')
                ->orWhere('audience', $user->role);
        })
        ->whereNotNull('published_at')
        ->latest()
        ->get();

    // Thông báo cá nhân
    $thongBaoCaNhans = ThongBaoCaNhan::where(
        'nguoi_dung_id',
        $user->id
    )
    ->latest()
    ->get();

    return view(
        'user.thong_bao.index',
        compact(
            'adminNotifications',
            'thongBaoCaNhans'
        )
    );
}
}
