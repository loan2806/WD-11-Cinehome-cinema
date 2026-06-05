@extends('layouts.admin')

@section('title', 'Suất chiếu')
@section('page-title', 'Quản lý suất chiếu')
@section('page-subtitle', 'Tạo lịch chiếu theo phim, rạp, phòng và khung giờ')

@section('content')
@include('admin.partials.flash')

<div class="admin-panel">
    <div class="panel-header">
        <div>
            <h5>Danh sách suất chiếu</h5>
            <small>{{ $suatChieus->total() }} suất chiếu</small>
        </div>
        <a href="{{ route('admin.suat-chieu.create') }}" class="btn-admin">
            <i class="fa-solid fa-plus"></i> Thêm suất chiếu
        </a>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th>Phim</th>
                <th>Rạp</th>
                <th>Phòng</th>
                <th>Ngày giờ chiếu</th>
                <th>Giá vé</th>
            </tr>
        </thead>
        <tbody>
            @forelse($suatChieus as $suatChieu)
                <tr>
                    <td>
                        <strong>{{ $suatChieu->movie->title ?? 'Phim đã xóa' }}</strong>
                        <br>
                        <small>{{ $suatChieu->movie?->duration ?? 0 }} phút</small>
                    </td>
                    <td>{{ $suatChieu->cinema->name ?? 'Rạp đã xóa' }}</td>
                    <td>{{ $suatChieu->room_name }}</td>
                    <td>
                        {{ \Carbon\Carbon::parse($suatChieu->show_date)->format('d/m/Y') }}
                        {{ \Carbon\Carbon::parse($suatChieu->show_time)->format('H:i') }}
                    </td>
                    <td>{{ number_format($suatChieu->price, 0, ',', '.') }} VND</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-gray-400">Chưa có suất chiếu.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">{{ $suatChieus->links() }}</div>
</div>
@endsection
