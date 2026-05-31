@extends('layouts.admin')

@section('title', 'Đánh giá phim')
@section('page-title', 'Đánh giá phim')
@section('page-subtitle', 'Quản lý đánh giá, điểm sao và trạng thái hiển thị')

@section('content')
@include('admin.partials.flash')

<div class="grid gap-6 xl:grid-cols-[420px_1fr]">
    <form method="POST" action="{{ route('admin.movie-reviews.store') }}" class="admin-panel">
        @csrf
        <div class="panel-header"><h5>Thêm đánh giá</h5></div>
        <div class="panel-body space-y-4">
            <select name="movie_id" class="admin-input" required>
                <option value="">Chọn phim</option>
                @foreach ($movies as $movie)
                    <option value="{{ $movie->id }}">{{ $movie->title }}</option>
                @endforeach
            </select>
            <input name="reviewer_name" class="admin-input" placeholder="Tên người đánh giá" required>
            <div class="grid grid-cols-2 gap-3">
                <input name="rating" type="number" min="1" max="5" value="5" class="admin-input" required>
                <select name="status" class="admin-input">
                    <option value="pending">Chờ duyệt</option>
                    <option value="approved">Hiển thị</option>
                    <option value="hidden">Ẩn</option>
                </select>
            </div>
            <textarea name="content" class="admin-input min-h-[120px]" placeholder="Nội dung đánh giá"></textarea>
            <button class="btn-admin w-full" type="submit">Lưu đánh giá</button>
        </div>
    </form>

    <div class="admin-panel">
        <div class="panel-header"><h5>Danh sách đánh giá</h5></div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead><tr><th>Phim</th><th>Người đánh giá</th><th>Sao</th><th>Nội dung</th><th>Trạng thái</th><th></th></tr></thead>
                <tbody>
                    @forelse ($reviews as $review)
                        <tr>
                            <td>{{ $review->movie->title ?? 'Không rõ' }}</td>
                            <td>{{ $review->reviewer_name }}</td>
                            <td>{{ $review->rating }}/5</td>
                            <td>{{ \Illuminate\Support\Str::limit($review->content, 80) }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.movie-reviews.update', $review) }}">
                                    @csrf @method('PATCH')
                                    <select name="status" class="admin-input" onchange="this.form.submit()">
                                        <option value="pending" @selected($review->status === 'pending')>Chờ duyệt</option>
                                        <option value="approved" @selected($review->status === 'approved')>Hiển thị</option>
                                        <option value="hidden" @selected($review->status === 'hidden')>Ẩn</option>
                                    </select>
                                </form>
                            </td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('admin.movie-reviews.destroy', $review) }}">
                                    @csrf @method('DELETE')
                                    <button class="action-btn action-delete" type="submit"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">Chưa có đánh giá phim.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $reviews->links() }}</div>
    </div>
</div>
@endsection
