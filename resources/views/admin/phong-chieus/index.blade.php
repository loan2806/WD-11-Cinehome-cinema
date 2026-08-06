@extends('layouts.admin')

@section('title', 'Quản lý Phòng Chiếu - CineHome')
@section('page-title', 'Quản lý Phòng Chiếu')

@push('styles')
<style>
    .room-filter-select {
        position: relative;
    }

    .room-filter-trigger {
        display: flex;
        align-items: center;
        gap: 10px;
        height: 44px;
        min-width: 200px;
        padding: 0 14px;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        background: #1c1c1c;
        color: #ffffff;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }

    .room-filter-trigger .label.is-placeholder {
        color: #8a8f98;
        font-weight: 500;
    }

    .room-filter-trigger:hover {
        background: #232323;
    }

    .room-filter-select.is-open .room-filter-trigger {
        border-color: #e50914;
        box-shadow: 0 0 0 3px rgba(229, 9, 20, 0.16);
    }

    .room-filter-trigger .label {
        flex: 1;
        text-align: left;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .room-filter-trigger .chevron {
        color: #8a8f98;
        font-size: 11px;
        transition: transform 0.2s ease;
    }

    .room-filter-select.is-open .chevron {
        transform: rotate(180deg);
    }

    .room-filter-menu {
        position: fixed;
        z-index: 3000;
        overflow: hidden;
        overflow-y: auto;
        max-height: 280px;
        border-radius: 14px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        background: #191919;
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.55);
    }

    .room-filter-option {
        display: block;
        width: 100%;
        padding: 11px 14px;
        border: 0;
        background: transparent;
        color: #c3c9d6;
        font-size: 13.5px;
        font-weight: 600;
        text-align: left;
        cursor: pointer;
        transition: background 0.15s ease, color 0.15s ease;
    }

    .room-filter-option:not(:last-child) {
        border-bottom: 1px solid rgba(255, 255, 255, 0.07);
    }

    .room-filter-option:hover {
        background: rgba(255, 255, 255, 0.06);
        color: #ffffff;
    }

    .room-filter-option.is-selected {
        background: rgba(229, 9, 20, 0.14);
        color: #ffffff;
    }
</style>
@endpush

@section('content')

    @php
        // Tính toán các chỉ số thống kê nhanh từ DB
        $totalRooms = \App\Models\PhongChieu::count();
        $activeRooms = \App\Models\PhongChieu::where('trang_thai', 'hoat_dong')->count();
        $maintenanceRooms = \App\Models\PhongChieu::where('trang_thai', 'bao_tri')->count();
        $totalSeats = \App\Models\PhongChieu::sum('suc_chua');
    @endphp

    <div class="admin-panel space-y-6">

        {{-- HEADER --}}
        <div class="panel-header flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-white/5 pb-6">
            <div>
                <h5 class="text-3xl font-black text-white tracking-wide">
                    Danh sách phòng chiếu
                </h5>
                <p class="text-sm text-gray-400 mt-1">
                    Quản lý quy mô phòng chiếu, định dạng chiếu (IMAX, 3D, 2D) và cấu trúc sơ đồ ghế ngồi
                </p>
            </div>

            <a href="{{ route('admin.phong-chieus.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-[#e50914] to-[#ff3b46] px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-red-500/10 hover:shadow-red-500/20 hover:scale-[1.02] active:scale-[0.98] transition duration-200">
                <i class="fa-solid fa-plus text-base"></i>
                Thêm phòng chiếu mới
            </a>
        </div>

        {{-- KPI STATS PANEL --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Thẻ 1: Tổng số phòng -->
            <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-gradient-to-br from-[#1e1e1e] to-[#121212] p-5 shadow-xl transition-all duration-300 hover:scale-[1.02] hover:border-red-500/40 group">
                <div class="absolute -right-4 -bottom-4 text-white/5 text-8xl transition-transform duration-500 group-hover:scale-110 group-hover:rotate-6">
                    <i class="fa-solid fa-door-open"></i>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Tổng phòng chiếu</p>
                        <h3 class="text-4xl font-black text-white mt-2">{{ $totalRooms }}</h3>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-500/10 text-red-500 border border-red-500/20">
                        <i class="fa-solid fa-door-open text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-1.5 text-xs text-gray-400">
                    <i class="fa-solid fa-circle text-[6px] text-gray-500"></i>
                    <span>Tổng số phòng thuộc các rạp</span>
                </div>
            </div>

            <!-- Thẻ 2: Đang hoạt động -->
            <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-gradient-to-br from-[#1e1e1e] to-[#121212] p-5 shadow-xl transition-all duration-300 hover:scale-[1.02] hover:border-green-500/40 group">
                <div class="absolute -right-4 -bottom-4 text-white/5 text-8xl transition-transform duration-500 group-hover:scale-110 group-hover:rotate-6">
                    <i class="fa-solid fa-video"></i>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Đang hoạt động</p>
                        <h3 class="text-4xl font-black text-green-400 mt-2">{{ $activeRooms }}</h3>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-500/10 text-green-400 border border-green-500/20">
                        <i class="fa-solid fa-circle-check text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-1.5 text-xs text-green-400/80">
                    <span class="inline-block h-2 w-2 rounded-full bg-green-500 animate-pulse"></span>
                    <span>Sẵn sàng đón tiếp khách hàng</span>
                </div>
            </div>

            <!-- Thẻ 3: Đang bảo trì -->
            <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-gradient-to-br from-[#1e1e1e] to-[#121212] p-5 shadow-xl transition-all duration-300 hover:scale-[1.02] hover:border-yellow-500/40 group">
                <div class="absolute -right-4 -bottom-4 text-white/5 text-8xl transition-transform duration-500 group-hover:scale-110 group-hover:rotate-6">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Bảo trì / Dừng chạy</p>
                        <h3 class="text-4xl font-black text-yellow-500 mt-2">{{ $maintenanceRooms }}</h3>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-yellow-500/10 text-yellow-500 border border-yellow-500/20">
                        <i class="fa-solid fa-screwdriver-wrench text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-1.5 text-xs text-yellow-500/80">
                    @if($maintenanceRooms > 0)
                        <span class="inline-block h-2 w-2 rounded-full bg-yellow-500 animate-ping"></span>
                        <span>Đang sửa chữa thiết bị</span>
                    @else
                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-gray-500"></span>
                        <span>Không có phòng nào sự cố</span>
                    @endif
                </div>
            </div>

            <!-- Thẻ 4: Tổng số ghế -->
            <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-gradient-to-br from-[#1e1e1e] to-[#121212] p-5 shadow-xl transition-all duration-300 hover:scale-[1.02] hover:border-red-500/40 group">
                <div class="absolute -right-4 -bottom-4 text-white/5 text-8xl transition-transform duration-500 group-hover:scale-110 group-hover:rotate-6">
                    <i class="fa-solid fa-couch"></i>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Tổng sức chứa</p>
                        <h3 class="text-4xl font-black text-red-500 mt-2">{{ number_format($totalSeats) }}</h3>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-500/10 text-red-500 border border-red-500/20">
                        <i class="fa-solid fa-couch text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-1.5 text-xs text-gray-400">
                    <i class="fa-solid fa-circle text-[6px] text-gray-500"></i>
                    <span>Tổng số ghế ngồi đã lắp đặt</span>
                </div>
            </div>
        </div>

        {{-- FILTER & VIEW SWITCHER BAR --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-[#151515] border border-white/10 rounded-2xl p-4 shadow-md">
            
            {{-- Form Lọc --}}
            <form method="GET" action="{{ route('admin.phong-chieus.index') }}" class="flex flex-wrap items-center gap-3 flex-grow">
                
                @php
                    $rapOptions = ['' => '-- Tất cả Rạp --'] + $rapChieuPhims->pluck('ten_rap', 'id')->toArray();
                    $currentRapId = (string) request('rap_chieu_phim_id', '');
                    if (!isset($rapOptions[$currentRapId]) && !isset($rapOptions[(int) $currentRapId])) {
                        $currentRapId = '';
                    }

                    $trangThaiOptions = [
                        '' => '-- Trạng thái --',
                        'hoat_dong' => 'Hoạt động',
                        'bao_tri' => 'Bảo trì',
                        'ngung_hoat_dong' => 'Ngừng hoạt động',
                    ];
                    $currentTrangThai = (string) request('trang_thai', '');
                    if (!isset($trangThaiOptions[$currentTrangThai])) {
                        $currentTrangThai = '';
                    }
                @endphp

                {{-- Lọc Rạp --}}
                <div class="room-filter-select" data-value="{{ $currentRapId }}">
                    <input type="hidden" name="rap_chieu_phim_id" value="{{ $currentRapId }}">

                    <button type="button" class="room-filter-trigger">
                        <span class="label {{ $currentRapId === '' ? 'is-placeholder' : '' }}">{{ $rapOptions[$currentRapId] ?? $rapOptions[(int) $currentRapId] }}</span>
                        <i class="fa-solid fa-chevron-down chevron"></i>
                    </button>

                    <div class="room-filter-menu hidden">
                        @foreach ($rapOptions as $value => $label)
                            <button
                                type="button"
                                class="room-filter-option {{ (string) $value === $currentRapId ? 'is-selected' : '' }}"
                                data-value="{{ $value }}"
                                data-label="{{ $label }}"
                            >
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Lọc Trạng thái --}}
                <div class="room-filter-select" data-value="{{ $currentTrangThai }}">
                    <input type="hidden" name="trang_thai" value="{{ $currentTrangThai }}">

                    <button type="button" class="room-filter-trigger">
                        <span class="label {{ $currentTrangThai === '' ? 'is-placeholder' : '' }}">{{ $trangThaiOptions[$currentTrangThai] }}</span>
                        <i class="fa-solid fa-chevron-down chevron"></i>
                    </button>

                    <div class="room-filter-menu hidden">
                        @foreach ($trangThaiOptions as $value => $label)
                            <button
                                type="button"
                                class="room-filter-option {{ $value === $currentTrangThai ? 'is-selected' : '' }}"
                                data-value="{{ $value }}"
                                data-label="{{ $label }}"
                            >
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <button type="submit"
                    class="h-11 rounded-xl bg-gradient-to-r from-[#e50914] to-[#ff3b46] px-5 text-sm font-bold text-white shadow-lg transition hover:opacity-90 active:scale-95 flex items-center gap-2">
                    <i class="fa-solid fa-filter"></i>
                    Lọc
                </button>

                <a href="{{ route('admin.phong-chieus.index') }}"
                    class="h-11 flex items-center justify-center rounded-xl border border-white/10 bg-white/5 px-5 text-sm font-bold text-white transition hover:bg-white/10">
                    Reset
                </a>
            </form>

            {{-- Bộ Chuyển Đổi Chế Độ Xem --}}
            <div class="flex items-center gap-1 bg-black/35 p-1 rounded-xl border border-white/5 self-end lg:self-auto">
                <button id="btnGridView"
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-sm transition-all duration-200 text-gray-400 hover:text-white"
                    title="Chế độ ô lưới">
                    <i class="fa-solid fa-grip"></i>
                </button>
                <button id="btnTableView"
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-sm transition-all duration-200 text-gray-400 hover:text-white"
                    title="Chế độ bảng biểu">
                    <i class="fa-solid fa-list"></i>
                </button>
            </div>

        </div>

        {{-- 1. CHẾ ĐỘ XEM Ô LƯỚI (GRID CARD VIEW) --}}
        <div id="roomGridView" class="hidden grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($phongChieus as $phongChieu)
                <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-white/10 bg-gradient-to-b from-[#1c1c1c] to-[#121212] transition-all duration-300 hover:-translate-y-1 hover:border-red-500/30 hover:shadow-[0_10px_35px_rgba(229,9,20,0.15)]">
                    
                    {{-- Visual Màn Chiếu Cong Thu Nhỏ (Cinema Graphic Area) --}}
                    <div class="relative h-16 bg-black/40 flex items-center justify-center border-b border-white/5 overflow-hidden">
                        {{-- Màn chiếu cong --}}
                        <div class="absolute top-2 w-[85%] h-5 bg-red-500/5 border-t border-red-500/40 rounded-[50%/10px_10px_0_0] text-[9px] text-red-500/70 font-semibold tracking-[0.25em] flex items-center justify-center uppercase">
                            MÀN HÌNH CHÍNH
                        </div>
                        
                        {{-- Sơ đồ ghế chấm giả lập --}}
                        <div class="absolute bottom-2 flex flex-col gap-1 items-center justify-center w-full opacity-20">
                            <div class="flex gap-1">
                                <span class="w-1 h-1 rounded-full bg-gray-400"></span>
                                <span class="w-1 h-1 rounded-full bg-gray-400"></span>
                                <span class="w-1 h-1 rounded-full bg-red-500"></span>
                                <span class="w-1 h-1 rounded-full bg-red-500"></span>
                                <span class="w-1 h-1 rounded-full bg-gray-400"></span>
                                <span class="w-1 h-1 rounded-full bg-gray-400"></span>
                            </div>
                            <div class="flex gap-1">
                                <span class="w-1 h-1 rounded-full bg-gray-400"></span>
                                <span class="w-1 h-1 rounded-full bg-red-500"></span>
                                <span class="w-1 h-1 rounded-full bg-red-500"></span>
                                <span class="w-1 h-1 rounded-full bg-red-500"></span>
                                <span class="w-1 h-1 rounded-full bg-red-500"></span>
                                <span class="w-1 h-1 rounded-full bg-gray-400"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Thân Card --}}
                    <div class="p-5 flex flex-col flex-grow">
                        
                        {{-- Tên và Loại Phòng --}}
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h4 class="text-xl font-extrabold text-white tracking-wide group-hover:text-red-500 transition duration-200">
                                    {{ $phongChieu->ten_phong }}
                                </h4>
                                <span class="text-xs text-gray-400 mt-1 flex items-center gap-1.5">
                                    <i class="fa-solid fa-film text-red-500/70"></i>
                                    {{ $phongChieu->rapChieuPhim->ten_rap ?? 'N/A' }}
                                </span>
                            </div>

                            @php
                                $typeColors = [
                                    '2d' => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
                                    '3d' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                                    'imax' => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20',
                                    '4dx' => 'bg-pink-500/10 text-pink-400 border-pink-500/20'
                                ];
                                $typeLabel = strtoupper($phongChieu->loai_phong);
                                $badgeColor = $typeColors[strtolower($phongChieu->loai_phong)] ?? 'bg-white/5 text-white/60 border-white/10';
                            @endphp
                            <span class="px-2.5 py-0.5 rounded-lg text-xs font-black tracking-wider border {{ $badgeColor }}">
                                {{ $typeLabel }}
                            </span>
                        </div>

                        {{-- Tiến trình Sức chứa --}}
                        <div class="mt-5">
                            <div class="flex items-center justify-between text-xs text-gray-400 mb-1.5">
                                <span class="flex items-center gap-1">
                                    <i class="fa-solid fa-users text-xs"></i>
                                    Sức chứa
                                </span>
                                <span class="font-bold text-white">{{ $phongChieu->suc_chua }} ghế</span>
                            </div>
                            <div class="h-1.5 w-full bg-white/5 rounded-full overflow-hidden">
                                @php
                                    $percent = min(100, max(12, ($phongChieu->suc_chua / 220) * 100));
                                @endphp
                                <div class="h-full bg-gradient-to-r from-[#e50914] to-[#ff3b46] rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>

                        {{-- Thống kê hàng & Trạng thái --}}
                        <div class="mt-5 grid grid-cols-2 gap-2 text-xs border-t border-white/5 pt-4">
                            <div class="bg-white/5 rounded-xl p-2.5 flex flex-col justify-center border border-white/5">
                                <span class="text-gray-500 text-[10px] uppercase font-bold tracking-wider">Hàng ghế</span>
                                <span class="text-white font-extrabold mt-0.5 flex items-center gap-1.5 text-sm">
                                    <i class="fa-solid fa-couch text-gray-400 text-xs"></i>
                                    {{ $phongChieu->hangGhes->count() }} hàng
                                </span>
                            </div>
                            <div class="bg-white/5 rounded-xl p-2.5 flex flex-col justify-center border border-white/5">
                                <span class="text-gray-500 text-[10px] uppercase font-bold tracking-wider">Trạng thái</span>
                                @php
                                    $statusColors = [
                                        'hoat_dong' => 'text-green-400 font-extrabold flex items-center gap-1.5 mt-0.5 text-xs',
                                        'bao_tri' => 'text-yellow-500 font-extrabold flex items-center gap-1.5 mt-0.5 text-xs',
                                        'ngung_hoat_dong' => 'text-red-500 font-extrabold flex items-center gap-1.5 mt-0.5 text-xs'
                                    ];
                                    $statusDots = [
                                        'hoat_dong' => '<span class="h-2 w-2 rounded-full bg-green-400 animate-pulse"></span>',
                                        'bao_tri' => '<span class="h-2 w-2 rounded-full bg-yellow-500 animate-ping"></span>',
                                        'ngung_hoat_dong' => '<span class="h-2 w-2 rounded-full bg-red-500"></span>'
                                    ];
                                    $statusLabels = [
                                        'hoat_dong' => 'Hoạt động',
                                        'bao_tri' => 'Bảo trì',
                                        'ngung_hoat_dong' => 'Ngừng chạy'
                                    ];
                                    $currStatus = $phongChieu->trang_thai;
                                @endphp
                                <div class="{{ $statusColors[$currStatus] ?? 'text-gray-400' }}">
                                    {!! $statusDots[$currStatus] ?? '' !!}
                                    {{ $statusLabels[$currStatus] ?? $currStatus }}
                                </div>
                            </div>
                        </div>

                        {{-- Hành động --}}
                        <div class="mt-6 pt-4 border-t border-white/5 grid grid-cols-2 gap-2 mt-auto">
                            <a href="{{ route('admin.phong-chieus.show', $phongChieu) }}"
                                class="flex items-center justify-center gap-2 rounded-xl bg-red-500/10 py-3 text-xs font-bold text-red-400 border border-red-500/25 hover:bg-red-500/20 active:scale-95 transition duration-200">
                                <i class="fa-solid fa-couch"></i>
                                Sơ đồ ghế
                            </a>
                            <a href="{{ route('admin.phong-chieus.edit', $phongChieu) }}"
                                class="flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/5 py-3 text-xs font-bold text-white hover:bg-white/10 active:scale-95 transition duration-200">
                                <i class="fa-solid fa-pen-to-square"></i>
                                Sửa phòng
                            </a>
                        </div>

                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center text-gray-500 bg-[#151515]/50 rounded-3xl border border-white/5">
                    <i class="fa-solid fa-folder-open text-4xl text-gray-600 mb-3"></i>
                    <p>Chưa có phòng chiếu nào trong hệ thống khớp với bộ lọc</p>
                </div>
            @endforelse
        </div>

        {{-- 2. CHẾ ĐỘ XEM BẢNG BIỂU (TABLE LIST VIEW) --}}
        <div id="roomTableView" class="hidden overflow-hidden rounded-3xl border border-white/10 bg-[#121212] shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left border-collapse">
                    
                    {{-- Tên Cột --}}
                    <thead class="bg-white/5 text-xs uppercase tracking-wider text-gray-400 border-b border-white/10">
                        <tr>
                            <th class="px-6 py-4.5 font-bold">STT</th>
                            <th class="px-6 py-4.5 font-bold">Tên Phòng</th>
                            <th class="px-6 py-4.5 font-bold">Rạp Chiếu</th>
                            <th class="px-6 py-4.5 font-bold">Loại Phòng</th>
                            <th class="px-6 py-4.5 font-bold">Sức Chứa</th>
                            <th class="px-6 py-4.5 font-bold">Số Hàng Ghế</th>
                            <th class="px-6 py-4.5 font-bold">Trạng Thái</th>
                            <th class="px-6 py-4.5 text-center font-bold">Hành động</th>
                        </tr>
                    </thead>

                    {{-- Nội dung dòng --}}
                    <tbody class="divide-y divide-white/5">
                        @forelse ($phongChieus as $key => $phongChieu)
                            <tr class="transition duration-200 hover:bg-white/5">
                                
                                {{-- STT --}}
                                <td class="px-6 py-4 text-gray-500 text-sm font-semibold">
                                    #{{ $phongChieus->firstItem() + $key }}
                                </td>

                                {{-- TÊN PHÒNG --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-500/10 text-red-500 border border-red-500/20">
                                            <i class="fa-solid fa-door-open text-sm"></i>
                                        </div>
                                        <span class="text-white font-extrabold text-base tracking-wide">{{ $phongChieu->ten_phong }}</span>
                                    </div>
                                </td>

                                {{-- RẠP --}}
                                <td class="px-6 py-4 text-gray-300 text-sm">
                                    <div class="flex items-center gap-1.5">
                                        <i class="fa-solid fa-film text-gray-500 text-xs"></i>
                                        {{ $phongChieu->rapChieuPhim->ten_rap ?? 'N/A' }}
                                    </div>
                                </td>

                                {{-- LOẠI PHÒNG --}}
                                <td class="px-6 py-4">
                                    @php
                                        $typeColors = [
                                            '2d' => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
                                            '3d' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                                            'imax' => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20',
                                            '4dx' => 'bg-pink-500/10 text-pink-400 border-pink-500/20'
                                        ];
                                        $typeLabel = strtoupper($phongChieu->loai_phong);
                                        $badgeColor = $typeColors[strtolower($phongChieu->loai_phong)] ?? 'bg-white/5 text-white/60 border-white/10';
                                    @endphp
                                    <span class="inline-block rounded-lg border px-2.5 py-0.5 text-xs font-black tracking-wider {{ $badgeColor }}">
                                        {{ $typeLabel }}
                                    </span>
                                </td>

                                {{-- SỨC CHỨA --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1.5 text-gray-300 text-sm font-semibold">
                                        <i class="fa-solid fa-users text-gray-500 text-xs"></i>
                                        {{ $phongChieu->suc_chua }} ghế
                                    </div>
                                </td>

                                {{-- SỐ HÀNG --}}
                                <td class="px-6 py-4 text-gray-300 text-sm">
                                    <div class="flex items-center gap-1.5">
                                        <i class="fa-solid fa-couch text-gray-500 text-xs"></i>
                                        {{ $phongChieu->hangGhes->count() }} hàng
                                    </div>
                                </td>

                                {{-- TRẠNG THÁI --}}
                                <td class="px-6 py-4">
                                    @php
                                        $trangThaiClasses = [
                                            'hoat_dong' => 'bg-green-500/10 text-green-400 border-green-500/20',
                                            'bao_tri' => 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20',
                                            'ngung_hoat_dong' => 'bg-red-500/10 text-red-500 border-red-500/20'
                                        ];
                                        $trangThaiLabels = [
                                            'hoat_dong' => 'Hoạt động',
                                            'bao_tri' => 'Bảo trì',
                                            'ngung_hoat_dong' => 'Ngừng hoạt động'
                                        ];
                                        $statusPulse = [
                                            'hoat_dong' => '<span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-green-400 animate-pulse"></span>',
                                            'bao_tri' => '<span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-yellow-400 animate-ping"></span>',
                                            'ngung_hoat_dong' => '<span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-red-500"></span>'
                                        ];
                                        $st = $phongChieu->trang_thai;
                                    @endphp
                                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-bold {{ $trangThaiClasses[$st] ?? 'bg-gray-500/10 text-gray-400 border-gray-500/20' }}">
                                        {!! $statusPulse[$st] ?? '' !!}
                                        {{ $trangThaiLabels[$st] ?? $st }}
                                    </span>
                                </td>

                                {{-- HÀNH ĐỘNG --}}
                                <td class="px-6 py-4 align-middle">
                                    <div class="flex items-center justify-center gap-2.5">
                                        
                                        {{-- Nút Sơ đồ ghế --}}
                                        <a href="{{ route('admin.phong-chieus.show', $phongChieu) }}"
                                            class="flex items-center gap-1.5 rounded-xl bg-red-500/10 border border-red-500/20 px-3.5 py-2 text-xs font-bold text-red-400 hover:bg-red-500/25 active:scale-95 transition"
                                            title="Quản lý sơ đồ ghế ngồi">
                                            <i class="fa-solid fa-couch"></i>
                                            Ghế
                                        </a>

                                        {{-- Nút Sửa --}}
                                        <a href="{{ route('admin.phong-chieus.edit', $phongChieu) }}"
                                            class="flex aspect-square h-9 w-9 items-center justify-center rounded-xl bg-white/5 border border-white/10 text-gray-300 hover:bg-white/10 hover:text-white active:scale-95 transition"
                                            title="Sửa phòng chiếu">
                                            <i class="fa-solid fa-pen text-sm"></i>
                                        </a>

                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-16 text-center text-gray-500">
                                    <i class="fa-solid fa-folder-open text-4xl text-gray-600 mb-3 block"></i>
                                    Chưa có phòng chiếu nào trong hệ thống
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-8 flex justify-center">
            {{ $phongChieus->links() }}
        </div>

    </div>

    {{-- VIEW MODE JS INTERACTION --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnGrid = document.getElementById('btnGridView');
            const btnTable = document.getElementById('btnTableView');
            const gridView = document.getElementById('roomGridView');
            const tableView = document.getElementById('roomTableView');

            // Hàm chuyển đổi
            function toggleView(mode) {
                if (mode === 'grid') {
                    // Hiển thị Grid
                    gridView.classList.remove('hidden');
                    tableView.classList.add('hidden');
                    
                    // Style Active cho Grid Button
                    btnGrid.classList.add('bg-gradient-to-r', 'from-[#e50914]', 'to-[#ff3b46]', 'text-white', 'shadow-md');
                    btnGrid.classList.remove('text-gray-400');
                    
                    // Style Inactive cho Table Button
                    btnTable.classList.remove('bg-gradient-to-r', 'from-[#e50914]', 'to-[#ff3b46]', 'text-white', 'shadow-md');
                    btnTable.classList.add('text-gray-400');
                } else {
                    // Hiển thị Table
                    gridView.classList.add('hidden');
                    tableView.classList.remove('hidden');
                    
                    // Style Active cho Table Button
                    btnTable.classList.add('bg-gradient-to-r', 'from-[#e50914]', 'to-[#ff3b46]', 'text-white', 'shadow-md');
                    btnTable.classList.remove('text-gray-400');
                    
                    // Style Inactive cho Grid Button
                    btnGrid.classList.remove('bg-gradient-to-r', 'from-[#e50914]', 'to-[#ff3b46]', 'text-white', 'shadow-md');
                    btnGrid.classList.add('text-gray-400');
                }
                
                // Lưu lựa chọn người dùng vào LocalStorage
                localStorage.setItem('adminPhongChieuViewMode', mode);
            }

            // Đọc cấu hình từ LocalStorage, mặc định là 'grid'
            const savedMode = localStorage.getItem('adminPhongChieuViewMode') || 'grid';
            toggleView(savedMode);

            // Gắn sự kiện click
            btnGrid.addEventListener('click', () => toggleView('grid'));
            btnTable.addEventListener('click', () => toggleView('table'));
        });
    </script>

    {{-- CUSTOM FILTER DROPDOWNS --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.room-filter-select').forEach(function (wrap) {
                const trigger = wrap.querySelector('.room-filter-trigger');
                const menu = wrap.querySelector('.room-filter-menu');
                const hiddenInput = wrap.querySelector('input[type="hidden"]');
                const labelEl = trigger.querySelector('.label');
                const options = wrap.querySelectorAll('.room-filter-option');

                // Đưa menu ra làm con trực tiếp của <body> để tránh bị các
                // container cha (overflow/backdrop-filter) làm lệch hoặc cắt mất.
                document.body.appendChild(menu);

                function closeMenu() {
                    wrap.classList.remove('is-open');
                    menu.classList.add('hidden');
                }

                function positionMenu() {
                    const rect = trigger.getBoundingClientRect();
                    menu.style.left = rect.left + 'px';
                    menu.style.width = rect.width + 'px';

                    const menuHeight = menu.offsetHeight;
                    const spaceBelow = window.innerHeight - rect.bottom;
                    const spaceAbove = rect.top;

                    if (spaceBelow < menuHeight + 8 && spaceAbove > spaceBelow) {
                        menu.style.top = (rect.top - menuHeight - 8) + 'px';
                    } else {
                        menu.style.top = (rect.bottom + 8) + 'px';
                    }
                }

                trigger.addEventListener('click', function (event) {
                    event.stopPropagation();
                    const willOpen = menu.classList.contains('hidden');
                    closeMenu();
                    if (willOpen) {
                        wrap.classList.add('is-open');
                        menu.classList.remove('hidden');
                        positionMenu();
                    }
                });

                window.addEventListener('scroll', closeMenu, true);
                window.addEventListener('resize', closeMenu);

                options.forEach(function (opt) {
                    opt.addEventListener('click', function () {
                        hiddenInput.value = opt.dataset.value;
                        labelEl.textContent = opt.dataset.label;
                        labelEl.classList.toggle('is-placeholder', opt.dataset.value === '');

                        options.forEach((o) => o.classList.toggle('is-selected', o === opt));
                        closeMenu();
                    });
                });

                document.addEventListener('click', function (event) {
                    if (!wrap.contains(event.target) && !menu.contains(event.target)) {
                        closeMenu();
                    }
                });
            });
        });
    </script>

@endsection