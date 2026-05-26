@extends('layouts.admin')

@section('title', 'Danh gia phim')
@section('page-title', 'Danh gia phim')
@section('page-subtitle', 'Kiem duyet va theo doi phan hoi cua khach hang')

@section('content')
<div class="admin-panel">
    <div class="panel-header">
        <div><h5>Danh gia</h5><small>{{ $reviews->total() }} danh gia</small></div>
        <form method="GET">
            <select name="status" onchange="this.form.submit()" class="rounded-xl border border-white/10 bg-[#111] px-4 py-3 text-white">
                <option value="">Tat ca</option>
                @foreach(['pending', 'approved', 'hidden'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th>Phim</th>
                <th>Nguoi dung</th>
                <th>Diem</th>
                <th>Noi dung</th>
                <th>Trang thai</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($reviews as $review)
                <tr>
                    <td><strong>{{ $review->movie?->title }}</strong></td>
                    <td>{{ $review->user?->name }}<br><small>{{ $review->user?->email }}</small></td>
                    <td class="text-[#d99a32]">{{ str_repeat('★', $review->rating) }}</td>
                    <td>{{ $review->content ?: '-' }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.reviews.update', $review) }}">
                            @csrf
                            @method('PATCH')
                            <select name="status" onchange="this.form.submit()" class="rounded-lg border border-white/10 bg-[#111] px-3 py-2 text-white">
                                @foreach(['pending', 'approved', 'hidden'] as $status)
                                    <option value="{{ $status }}" @selected($review->status === $status)>{{ $status }}</option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                    <td class="text-end">
                        <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" onsubmit="return confirm('Xoa danh gia nay?')">
                            @csrf
                            @method('DELETE')
                            <button class="action-btn action-delete"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-gray-400">Chua co danh gia phim.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">{{ $reviews->links() }}</div>
</div>
@endsection
