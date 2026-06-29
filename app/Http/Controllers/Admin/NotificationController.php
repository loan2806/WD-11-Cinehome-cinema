<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Traits\Loggable;
use Illuminate\Http\Request;

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

    public function create()
    {
        return view('admin.notifications.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:info,success,warning,danger',
        ]);

        AdminNotification::create([
            'tieu_de' => $validated['title'],
            'noi_dung' => $validated['content'],
            'loai' => $validated['type'],
            'doi_tuong' => 'admin',
            'da_doc' => false,
        ]);

        $this->logAction('Tạo thông báo: ' . $validated['title']);

        return redirect()->route('admin.notifications.index')
            ->with('success', 'Thông báo đã được tạo thành công.');
    }

    public function destroy(AdminNotification $notification)
    {
        $this->logAction('Xóa thông báo: ' . $notification->tieu_de);
        $notification->delete();

        return redirect()->route('admin.notifications.index')
            ->with('success', 'Thông báo đã được xóa.');
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