<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SystemNotification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $notifications = SystemNotification::query()
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere(function ($inner) use ($user) {
                        $inner->whereNull('user_id')
                            ->where(function ($roleQuery) use ($user) {
                                $roleQuery->whereNull('target_role')
                                    ->orWhere('target_role', $user->role);
                            });
                    });
            })
            ->latest()
            ->paginate(12);

        return view('user.notifications.index', compact('notifications'));
    }

    public function markRead(SystemNotification $notification)
    {
        $user = Auth::user();

        abort_if($notification->user_id && $notification->user_id !== $user->id, 403);

        $notification->update(['read_at' => now()]);

        return back();
    }
}
