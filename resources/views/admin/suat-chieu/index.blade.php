@extends('layouts.admin')

@section('title', 'Suat chieu')
@section('page-title', 'Quan ly suat chieu')
@section('page-subtitle', 'Tao lich chieu theo phim, rap va khung gio')

@section('content')
@include('admin.partials.flash')

<div class="admin-panel">
    <div class="panel-header">
        <div>
            <h5>Danh sach suat chieu</h5>
            <small>{{ $suatChieus->total() }} suat chieu</small>
        </div>
        <a href="{{ route('admin.suat-chieu.create') }}" class="btn-admin">
            <i class="fa-solid fa-plus"></i> Them suat chieu
        </a>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th>Phim</th>
                <th>Rap</th>
                <th>Ngay gio chieu</th>
                <th>Gia ve</th>
            </tr>
        </thead>
        <tbody>
            @forelse($suatChieus as $suatChieu)
                <tr>
                    <td>
                        <strong>{{ $suatChieu->phim->ten_phim ?? 'Phim da xoa' }}</strong>
                        <br>
                        <small>{{ $suatChieu->phim?->thoi_luong ?? 0 }} phut</small>
                    </td>
                    <td>{{ $suatChieu->rapChieuPhim->ten_rap ?? 'Rap da xoa' }}</td>
                    <td>{{ $suatChieu->thoi_gian_chieu->format('H:i d/m/Y') }}</td>
                    <td>{{ number_format($suatChieu->gia_ve, 0, ',', '.') }} VND</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-gray-400">Chua co suat chieu.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">{{ $suatChieus->links() }}</div>
</div>
@endsection
