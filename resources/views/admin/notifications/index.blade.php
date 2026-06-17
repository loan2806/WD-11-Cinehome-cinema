@extends('layouts.admin')

@section('page-title', 'Thong bao')
@section('page-subtitle', 'Tao va quan ly thong bao gui den nguoi dung')

@section('content')
<div class="admin-panel">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Tiêu đề</th>
                <th>Loại</th>
                <th>Ngày tạo</th>
                <th>Hành Động</th>
            </tr>
        </thead>
        <tbody>
            @forelse($notifications as $notification)
                <tr class="{{ !$notification->da_doc ? 'bg-white/5' : '' }}">
                    <td><strong>{{ $notification->tieu_de }}</strong><br><small>{{ $notification->message }}</small></td>
                    <td>
                        <span class="status-badge status-coming">{{ $notification->loai }}</span>
                    </td>
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
                <tr><td colspan="4" class="text-center text-gray-400">Chua co thong bao.</td></tr>
            @endforelse
        </tbody>
    </table>


    @include('components.admin-pagination', ['paginator' => $notifications])
</div>
@endsection












