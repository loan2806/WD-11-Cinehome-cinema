@extends('layouts.admin')

@section('title', 'Dashboard Quản lý - CineHome')
@section('page-title', 'Dashboard quản lý')
@section('page-subtitle', 'Theo dõi doanh thu, vé bán, suất chiếu và hoạt động vận hành')

@section('content')
<div class="space-y-6">

    {{-- 1. TRUNG TÂM ĐIỀU HÀNH (HERO BANNER) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 rounded-3xl bg-gradient-to-r from-[#1c0c08] via-[#121215] to-[#18181c] border border-white/10 p-8 flex flex-col justify-between relative overflow-hidden">
            <div class="space-y-3 z-10">
                <span class="text-xs font-black tracking-widest text-[#d99a32] uppercase flex items-center gap-2">
                    <i class="fa-solid fa-bolt text-[#d99a32]"></i> Trung tâm điều hành
                </span>
                <h2 class="text-3xl font-black text-white">
                    Xin chào, {{ Auth::user()->ho_ten ?? 'Admin CineHome' }}
                </h2>
                <p class="text-gray-400 text-sm max-w-xl">
                    Nắm nhanh tình hình vận hành trong ngày, theo dõi phim mới, suất chiếu và các tác vụ quan trọng của hệ thống rạp CineHome.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3 mt-6 z-10">
                <a href="{{ route('staff.ban-ve.index') }}" class="px-5 py-2.5 rounded-xl bg-[#e50914] hover:bg-[#b80710] text-white font-bold text-sm transition flex items-center gap-2 no-underline">
                    <i class="fa-solid fa-ticket"></i> Bán vé tại quầy
                </a>
                <a href="{{ route('admin.suat-chieus.index') }}" class="px-5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-sm transition flex items-center gap-2 no-underline">
                    <i class="fa-solid fa-calendar-days"></i> Quản lý suất chiếu
                </a>
                <a href="{{ route('admin.thong-ke.index') }}" class="px-5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-sm transition flex items-center gap-2 no-underline">
                    <i class="fa-solid fa-chart-pie"></i> Báo cáo doanh thu
                </a>
            </div>
        </div>

        {{-- WIDGET PHIÊN VẬN HÀNH THỜI GIAN THỰC --}}
        <div class="rounded-3xl bg-[#141417] border border-white/10 p-6 flex flex-col justify-between space-y-4">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase">Phiên vận hành</span>
                <div class="text-2xl font-black text-white mt-1">
                    {{ now()->format('d/m/Y') }}
                </div>
            </div>

            <div class="space-y-2">
                <div class="flex items-center justify-between bg-white/5 border border-white/5 rounded-2xl p-3.5">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-[#d99a32]/10 text-[#d99a32] flex items-center justify-center">
                            <i class="fa-solid fa-film"></i>
                        </div>
                        <span class="text-xs text-gray-300 font-medium">Phim mới cập nhật</span>
                    </div>
                    <span class="text-lg font-black text-white">{{ $phimMoiCapNhatCount }}</span>
                </div>

                <div class="flex items-center justify-between bg-white/5 border border-white/5 rounded-2xl p-3.5">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <span class="text-xs text-gray-300 font-medium">Suất chiếu hôm nay</span>
                    </div>
                    <span class="text-lg font-black text-white">{{ $suatChieuHomNayCount }}</span>
                </div>

                <div class="flex items-center justify-between bg-white/5 border border-white/5 rounded-2xl p-3.5">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                            <i class="fa-solid fa-signal"></i>
                        </div>
                        <span class="text-xs text-gray-300 font-medium">Trạng thái hệ thống</span>
                    </div>
                    <span class="text-xs font-bold text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-full border border-emerald-500/20">
                        Live
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. BỘ 4 CARD THỐNG KÊ DOANH THU THỜI GIAN THỰC --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

        {{-- CARD 1: DOANH THU HÔM NAY --}}
        <div class="rounded-3xl bg-[#141417] border border-white/10 p-5 relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <div class="w-11 h-11 rounded-2xl bg-red-500/10 text-red-500 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
                @if($phanTramTangTruong >= 0)
                    <span class="text-xs font-bold text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-full border border-emerald-500/20">
                        +{{ $phanTramTangTruong }}%
                    </span>
                @else
                    <span class="text-xs font-bold text-red-400 bg-red-500/10 px-2.5 py-1 rounded-full border border-red-500/20">
                        {{ $phanTramTangTruong }}%
                    </span>
                @endif
            </div>

            <div class="mt-4">
                <span class="text-xs text-gray-400 font-medium">Doanh thu vé hôm nay</span>
                <div class="text-2xl font-black text-white mt-1">
                    {{ number_format($doanhThuVeHomNay, 0, ',', '.') }}đ
                </div>
                <span class="text-[11px] text-gray-500 mt-1 block">So với hôm qua</span>
            </div>
        </div>

        {{-- CARD 2: VÉ ĐÃ BÁN --}}
        <div class="rounded-3xl bg-[#141417] border border-white/10 p-5 relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <div class="w-11 h-11 rounded-2xl bg-red-500/10 text-red-500 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-ticket"></i>
                </div>
                <span class="text-xs font-bold text-gray-400 bg-white/5 px-2.5 py-1 rounded-full border border-white/10">Vé</span>
            </div>

            <div class="mt-4">
                <span class="text-xs text-gray-400 font-medium">Vé đã bán</span>
                <div class="text-2xl font-black text-white mt-1">
                    {{ number_format($veDaBanHomNay, 0, ',', '.') }}
                </div>
                <span class="text-[11px] text-gray-500 mt-1 block">Giao dịch trong ngày</span>
            </div>
        </div>

        {{-- CARD 3: LƯỢNG KHÁCH --}}
        <div class="rounded-3xl bg-[#141417] border border-white/10 p-5 relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <div class="w-11 h-11 rounded-2xl bg-red-500/10 text-red-500 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-users"></i>
                </div>
                <span class="text-xs font-bold text-gray-400 bg-white/5 px-2.5 py-1 rounded-full border border-white/10">Khách</span>
            </div>

            <div class="mt-4">
                <span class="text-xs text-gray-400 font-medium">Lượng khách</span>
                <div class="text-2xl font-black text-white mt-1">
                    {{ number_format($luongKhachHomNay, 0, ',', '.') }}
                </div>
                <span class="text-[11px] text-gray-500 mt-1 block">Khách vào rạp</span>
            </div>
        </div>

        {{-- CARD 4: DOANH THU ĐỒ ĂN --}}
        <div class="rounded-3xl bg-[#141417] border border-white/10 p-5 relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <div class="w-11 h-11 rounded-2xl bg-red-500/10 text-red-500 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-burger"></i>
                </div>
                <span class="text-xs font-bold text-gray-400 bg-white/5 px-2.5 py-1 rounded-full border border-white/10">F&B</span>
            </div>

            <div class="mt-4">
                <span class="text-xs text-gray-400 font-medium">Doanh thu đồ ăn</span>
                <div class="text-2xl font-black text-white mt-1">
                    {{ number_format($doanhThuDoAnHomNay, 0, ',', '.') }}đ
                </div>
                <span class="text-[11px] text-gray-500 mt-1 block">Bắp nước & combo</span>
            </div>
        </div>

    </div>

    {{-- 3. BẢNG PHIM MỚI CẬP NHẬT & TÁC VỤ NHANH --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- DANH SÁCH PHIM MỚI --}}
        <div class="lg:col-span-2 rounded-3xl bg-[#141417] border border-white/10 p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-[#d99a32] uppercase">Thư viện phim</span>
                    <h3 class="text-lg font-black text-white mt-0.5">Phim mới cập nhật</h3>
                </div>
                <a href="{{ route('admin.phims.index') }}" class="text-xs font-bold text-[#d99a32] hover:underline flex items-center gap-1 no-underline">
                    Quản lý phim <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-white/10 text-xs text-gray-400 uppercase">
                            <th class="py-3 px-2">Phim</th>
                            <th class="py-3 px-2">Thể loại</th>
                            <th class="py-3 px-2">Thời lượng</th>
                            <th class="py-3 px-2 text-right">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($danhSachPhimMoi as $phim)
                            <tr class="hover:bg-white/5 transition">
                                <td class="py-3 px-2 flex items-center gap-3">
                                    {{-- ĐÃ SỬA: NGẮN VÒNG LẶP ONERROR BẰNG THIS.ONERROR=NULL VA DUNG SVG SAFE FALLBACK --}}
                                    <img src="{{ !empty($phim->poster) ? asset('storage/' . $phim->poster) : 'data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'40\' height=\'48\' viewBox=\'0 0 40 48\'><rect width=\'100%\' height=\'100%\' fill=\'%2328282e\'/><text x=\'50%\' y=\'50%\' fill=\'%23d99a32\' font-size=\'10\' font-weight=\'bold\' text-anchor=\'middle\' dy=\'.3em\'>CINE</text></svg>' }}" 
                                         alt="{{ $phim->ten_phim }}" 
                                         class="w-10 h-12 object-cover rounded-lg bg-zinc-800" 
                                         onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'40\' height=\'48\' viewBox=\'0 0 40 48\'><rect width=\'100%\' height=\'100%\' fill=\'%2328282e\'/><text x=\'50%\' y=\'50%\' fill=\'%23d99a32\' font-size=\'10\' font-weight=\'bold\' text-anchor=\'middle\' dy=\'.3em\'>CINE</text></svg>';">
                                    <span class="font-bold text-white max-w-[200px] truncate">{{ $phim->ten_phim }}</span>
                                </td>
                                <td class="py-3 px-2 text-gray-300">
                                    @if(!empty($phim->theLoais) && is_iterable($phim->theLoais))
                                        {{ $phim->theLoais->pluck('ten_the_loai')->implode(', ') ?: 'Chưa xếp' }}
                                    @elseif(!empty($phim->theLoai) && is_iterable($phim->theLoai))
                                        {{ $phim->theLoai->pluck('ten_the_loai')->implode(', ') ?: 'Chưa xếp' }}
                                    @else
                                        Chưa xếp
                                    @endif
                                </td>
                                <td class="py-3 px-2 text-gray-300 font-medium">
                                    {{ $phim->thoi_luong ?? 120 }} phút
                                </td>
                                <td class="py-3 px-2 text-right">
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-full border bg-emerald-500/10 text-emerald-400 border-emerald-500/20">
                                        Đang chiếu
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-gray-500">Chưa có dữ liệu phim mới.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TÁC VỤ NHANH --}}
        <div class="rounded-3xl bg-[#141417] border border-white/10 p-6 space-y-4 flex flex-col justify-between">
            <div>
                <span class="text-xs font-bold text-[#d99a32] uppercase">Tác vụ nhanh</span>
                <h3 class="text-lg font-black text-white mt-0.5">Lối tắt vận hành</h3>

                <div class="space-y-3 mt-4">
                    <a href="{{ route('admin.phims.create') }}" class="flex items-center gap-3 p-3.5 rounded-2xl bg-white/5 hover:bg-white/10 border border-white/5 transition text-white no-underline">
                        <div class="w-9 h-9 rounded-xl bg-[#d99a32]/20 text-[#d99a32] flex items-center justify-center font-bold">
                            <i class="fa-solid fa-plus"></i>
                        </div>
                        <div>
                            <div class="font-bold text-sm">Thêm phim mới</div>
                            <div class="text-xs text-gray-400">Cập nhật poster, thể loại, trailer</div>
                        </div>
                    </a>

                    <a href="{{ route('admin.suat-chieus.create') }}" class="flex items-center gap-3 p-3.5 rounded-2xl bg-white/5 hover:bg-white/10 border border-white/5 transition text-white no-underline">
                        <div class="w-9 h-9 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center font-bold">
                            <i class="fa-solid fa-calendar-plus"></i>
                        </div>
                        <div>
                            <div class="font-bold text-sm">Tạo lịch chiếu</div>
                            <div class="text-xs text-gray-400">Lên lịch chiếu đơn hoặc hàng loạt</div>
                        </div>
                    </a>

                    <a href="{{ route('staff.ban-ve.index') }}" class="flex items-center gap-3 p-3.5 rounded-2xl bg-white/5 hover:bg-white/10 border border-white/5 transition text-white no-underline">
                        <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">
                            <i class="fa-solid fa-cash-register"></i>
                        </div>
                        <div>
                            <div class="font-bold text-sm">Quầy bán vé</div>
                            <div class="text-xs text-gray-400">Mở giao diện quầy POS bán tại rạp</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection