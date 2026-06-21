<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Traits\Loggable;

class NotificationController extends Controller
{
    use Loggable;

    public function index()
    {
        $notifications = AdminNotification::latest()->paginate(15);

        $notificationCount = AdminNotification::where('da_doc', false)->count();

        return view('admin.notifications.index', compact(
            'notifications',
            'notificationCount'
        ));
    }

    public function markAllRead()
    {
        AdminNotification::where('da_doc', false)
            ->update(['da_doc' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Đã đánh dấu tất cả thông báo là đã đọc'
        ]);
    }
}