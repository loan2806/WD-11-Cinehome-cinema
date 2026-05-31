<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $notifications = AdminNotification::query()
            ->where(function ($query) use ($user) {
                $query->where('audience', 'all')
                    ->orWhere('audience', $user->role);
            })
            ->whereNotNull('published_at')
            ->latest()
            ->paginate(12);

        return view('user.notifications.index', compact('notifications'));
    }
}
