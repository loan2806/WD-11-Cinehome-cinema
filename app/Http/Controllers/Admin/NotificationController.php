<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
// use App\Models\ActivityLog;
use App\Models\AdminNotification;
// use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = AdminNotification::latest()->paginate(15);

        $notificationCount = AdminNotification::where('da_doc', false)->count();

        return view('admin.notifications.index', compact('notifications','notificationCount'));
    }

    public function markAllRead()
{
    AdminNotification::where('da_doc', false)
        ->update(['da_doc' => true]);

    return response()->json(['success' => true]);
}

    // public function create()
    // {
    //     return view('admin.notifications.create');
    // }

    // public function store(Request $request)
    // {
    //     $data = $request->validate([
    //         'title' => ['required', 'string', 'max:255'],
    //         'message' => ['required', 'string'],
    //         'type' => ['required', 'in:info,success,warning,danger'],
    //         'audience' => ['required', 'in:all,user,staff,admin'],
    //     ]);

    //     $notification = AdminNotification::create([
    //         ...$data,
    //         'published_at' => now(),
    //     ]);

    //     ActivityLog::create([
    //         'user_id' => $request->user()?->id,
    //         'action' => 'create_notification',
    //         'module' => 'notifications',
    //         'description' => 'Tao thong bao ' . $notification->title,
    //         'ip_address' => $request->ip(),
    //         'user_agent' => substr((string) $request->userAgent(), 0, 255),
    //         'properties' => ['notification_id' => $notification->id],
    //     ]);

    //     return redirect()->route('admin.notifications.index')->with('success', 'Da tao thong bao.');
    // }

    // public function destroy(Request $request, AdminNotification $notification)
    // {
    //     $title = $notification->title;
    //     $notification->delete();

    //     ActivityLog::create([
    //         'user_id' => $request->user()?->id,
    //         'action' => 'delete_notification',
    //         'module' => 'notifications',
    //         'description' => 'Xoa thong bao ' . $title,
    //         'ip_address' => $request->ip(),
    //         'user_agent' => substr((string) $request->userAgent(), 0, 255),
    //     ]);

    //     return back()->with('success', 'Da xoa thong bao.');
    // }
}
