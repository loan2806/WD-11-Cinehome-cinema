@extends('layouts.admin')
@section('content')
<div class="admin-panel space-y-6 bg-[#121212] p-6 text-white rounded-2xl">
    <div class="flex justify-between items-center border-b border-white/10 pb-4">
        <h5 class="text-xl font-bold text-[#d99a32]">Thông tin chi tiết suất chiếu #{{ $suatChieu->id }}</h5>
        <a href="{{ route('admin.suat-chieus.index') }}" class="btn btn-secondary text-sm">Quay lại</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white/5 p-6 rounded-xl">
        <div>
            <p class="text-gray-400">Phim trình chiếu: <span class="text-white font-semibold">{{ $suatChieu->phims->ten_phim ?? 'N/A' }}</span></p>
            <p class="text-gray-400">Rạp chiếu: <span class="text-white font-semibold">{{ $suatChieu->rapChieuPhim->ten_rap ?? 'N/A' }}</span></p>
            <p class="text-gray-400">Phòng chiếu: <span class="text-white font-semibold">{{ $suatChieu->phongChieu->ten_phong ?? 'N/A' }}</span></p>
        </div>
        <div>
            <p class="text-gray-400">Thời gian bắt đầu: <span class="text-white font-semibold">{{ $suatChieu->thoi_gian_chieu }}</span></p>
            <p class="text-gray-400">Giá vé gốc: <span class="text-[#f4c56a] font-bold">{{ number_format($suatChieu->gia_ve) }} VND</span></p>
            <p class="text-gray-400">Trạng thái hiện tại: 
                <span class="px-2 py-1 rounded text-xs font-bold bg-blue-500/20 text-blue-400">
                    {{ strtoupper($suatChieu->trang_thai) }}
                </span>
            </p>
        </div>
    </div>
</div>
@endsection