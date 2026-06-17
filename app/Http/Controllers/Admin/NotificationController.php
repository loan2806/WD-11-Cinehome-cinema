<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\NhatKyHoatDongHeThong;
use App\Traits\Loggable;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use Loggable;

    public function index()
    {
        $notifications = AdminNotification::latest()->paginate(15);

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
            'audience' => ['required', 'in:all,user,staff,admin'],
        ]);

        $notification = AdminNotification::create([
            ...$data,
            'published_at' => now(),
        ]);

        $this->ghiNhatKy($request, 'Tạo thông báo', 'Quản lý thông báo', "Tạo thông báo: {$notification->title}");

        return redirect()->route('admin.notifications.index')->with('success', 'Da tao thong bao.');
    }

    public function destroy(Request $request, AdminNotification $notification)
    {
        $title = $notification->title;
        $notification->delete();

        $this->ghiNhatKy($request, 'Xóa thông báo', 'Quản lý thông báo', "Xóa thông báo: {$title}");

        return back()->with('success', 'Da xoa thong bao.');
    }
}
