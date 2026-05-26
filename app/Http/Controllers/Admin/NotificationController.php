<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SystemNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = SystemNotification::with('user')->latest()->paginate(15);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function create()
    {
        return view('admin.notifications.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'type' => ['required', 'in:info,success,warning,danger'],
            'target_role' => ['nullable', 'in:user,staff,admin'],
        ]);

        $notification = SystemNotification::create($data);

        ActivityLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'create_notification',
            'module' => 'notifications',
            'description' => 'Tao thong bao ' . $notification->title,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'properties' => ['notification_id' => $notification->id],
        ]);

        return redirect()->route('admin.notifications.index')->with('success', 'Da tao thong bao.');
    }

    public function destroy(Request $request, SystemNotification $notification)
    {
        $title = $notification->title;
        $notification->delete();

        ActivityLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'delete_notification',
            'module' => 'notifications',
            'description' => 'Xoa thong bao ' . $title,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        return back()->with('success', 'Da xoa thong bao.');
    }
}
