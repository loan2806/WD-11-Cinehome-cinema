@extends('layouts.admin')

@section('title', 'Thong bao')
@section('page-title', 'Thong bao')
@section('page-subtitle', 'Tao va quan ly thong bao gui den nguoi dung')

@section('content')
<div class="admin-panel">
    <div class="panel-header">
        <div><h5>Danh sach thong bao</h5><small>{{ $notifications->total() }} thong bao</small></div>
        <a href="{{ route('admin.notifications.create') }}" class="btn-admin"><i class="fa-solid fa-plus"></i> Tao thong bao</a>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th>Tieu de</th>
                <th>Doi tuong</th>
                <th>Loai</th>
                <th>Ngay tao</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($notifications as $notification)
                <tr>
                    <td><strong>{{ $notification->title }}</strong><br><small>{{ $notification->message }}</small></td>
                    <td>{{ $notification->target_role ?: ($notification->user?->email ?? 'Tat ca') }}</td>
                    <td><span class="status-badge status-coming">{{ $notification->type }}</span></td>
                    <td>{{ $notification->created_at->format('d/m/Y H:i') }}</td>
                    <td class="text-end">
                        <form method="POST" action="{{ route('admin.notifications.destroy', $notification) }}" onsubmit="return confirm('Xoa thong bao nay?')">
                            @csrf
                            @method('DELETE')
                            <button class="action-btn action-delete"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-gray-400">Chua co thong bao.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">{{ $notifications->links() }}</div>
</div>
@endsection
