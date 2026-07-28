@extends('layouts.admin')

@section('page-title', 'Chi tiết Phòng Chiếu')

@section('content')

@php
    // Tính toán thống kê
    $tongSoGhe = $phongChieu->gheNgois->count();
    $gheThuong = 0;
    $gheVip = 0;
    $gheCouple = 0;
    $gheBaoTri = 0;

    foreach ($phongChieu->gheNgois as $ghe) {
        if ($ghe->trang_thai === 'bao_tri') {
            $gheBaoTri++;
        } else {
            $loaiTen = $ghe->loaiGhe->ten_loai ?? 'Thường';
            if (stripos($loaiTen, 'couple') !== false || stripos($loaiTen, 'đôi') !== false) {
                $gheCouple++;
            } elseif (stripos($loaiTen, 'vip') !== false) {
                $gheVip++;
            } else {
                $gheThuong++;
            }
        }
    }
@endphp

{{-- TOOLBAR --}}
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div>
        <h2 class="text-2xl font-black text-white">
            <i class="fa-solid fa-door-open mr-2 text-[#d99a32]"></i>
            {{ $phongChieu->ten_phong }}
        </h2>
        <p class="mt-1 text-sm text-gray-400">
            {{ $phongChieu->rapChieuPhim->ten_rap ?? 'N/A' }} · {{ ucfirst($phongChieu->loai_phong) }} · {{ $phongChieu->suc_chua }} ghế
        </p>
    </div>
    <div class="flex flex-wrap gap-3">
        <button type="button" id="btnOpenAddSeat" onclick="openAddSeatModalDirect()"
            class="inline-flex items-center gap-2 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-5 py-3 text-sm font-bold text-emerald-300 transition hover:scale-[1.02] hover:bg-emerald-500/20 hover:border-emerald-500/50">
            <i class="fa-solid fa-plus"></i>
            Thêm ghế
        </button>
        <button type="button" id="btnOpenAddRow" onclick="openAddRowModalDirect()"
            class="inline-flex items-center gap-2 rounded-2xl border border-[#d99a32]/30 bg-[#d99a32]/10 px-5 py-3 text-sm font-bold text-[#d99a32] transition hover:scale-[1.02] hover:bg-[#d99a32]/20 hover:border-[#d99a32]/50">
            <i class="fa-solid fa-layer-group"></i>
            Thêm hàng
        </button>
        <a href="{{ route('admin.phong-chieus.edit', $phongChieu) }}"
            class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-[#d99a32]/20 transition hover:scale-[1.02] hover:shadow-xl hover:shadow-[#d99a32]/30">
            <i class="fa-solid fa-pen-to-square"></i>
            Chỉnh sửa
        </a>
        <a href="{{ route('admin.phong-chieus.index') }}"
            class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/10 hover:border-white/20">
            <i class="fa-solid fa-arrow-left"></i>
            Quay lại
        </a>
    </div>
</div>

{{-- BULK ACTIONS TOOLBAR --}}
<div id="bulkActionsToolbar" class="hidden mb-6 rounded-2xl border-2 border-[#d99a32]/60 bg-[#0f0f0f] p-4 shadow-lg shadow-[#d99a32]/20">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-1 items-center gap-3 min-w-0">
            <div class="flex items-center gap-2 shrink-0">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#d99a32]/30">
                    <i class="fa-solid fa-check text-sm text-[#d99a32]"></i>
                </div>
                <div>
                    <span class="text-sm font-bold text-white"><span id="selectedCount">0</span> ghế</span>
                    <span class="ml-1 text-xs text-gray-500">đã chọn</span>
                </div>
            </div>
            {{-- Danh sách tên ghế đã chọn (chips) --}}
            <div id="selectedSeatsList" class="flex flex-1 flex-wrap items-center gap-1.5 min-w-0 max-h-24 overflow-y-auto">
                {{-- Tự render bằng JS --}}
            </div>
            <button type="button" id="clearSelection" class="shrink-0 rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-gray-300 hover:text-white hover:bg-white/10 transition">
                <i class="fa-solid fa-xmark mr-1"></i>Bỏ chọn
            </button>
        </div>
        <div class="flex flex-wrap items-center gap-3 shrink-0">
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-400">Đổi loại:</label>
                <div class="relative" id="bulkLoaiGheWrapper" style="min-width: 200px;">
                    <div class="custom-select custom-select--simple" data-select-id="bulkLoaiGhe" style="width: 200px;">
                        <div class="custom-select__trigger" onclick="toggleCustomSelect(this)">
                            <div class="custom-select__value">
                                <span class="custom-select__text custom-select__text--placeholder">-- Chọn loại ghế --</span>
                            </div>
                            <div class="custom-select__arrow">
                                <i class="fa-solid fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                        <div class="custom-select__dropdown">
                            @foreach(\App\Models\LoaiGhe::all() as $loai)
                                <div class="custom-select__option" data-value="{{ $loai->id }}" data-color="{{ $loai->mau_sac ?? '#666' }}" data-la-couple="{{ $loai->la_couple ? '1' : '0' }}" onclick="selectCustomOption(this)">
                                    <div class="custom-select__option-color" style="background-color: {{ $loai->mau_sac ?? '#666' }}"></div>
                                    <div class="custom-select__option-content">
                                        <div class="custom-select__option-label">{{ $loai->ten_loai }}</div>
                                        <div class="custom-select__option-meta">+{{ number_format($loai->phu_thu) }}đ</div>
                                    </div>
                                    <div class="custom-select__option-check">
                                        <i class="fa-solid fa-check text-white text-[10px]"></i>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <select id="bulkLoaiGhe" class="hidden" onchange="handleBulkLoaiGheChange(this)">
                        <option value="">-- Chọn loại ghế --</option>
                        @foreach(\App\Models\LoaiGhe::all() as $loai)
                            <option value="{{ $loai->id }}" data-color="{{ $loai->mau_sac ?? '#666' }}">
                                {{ $loai->ten_loai }} (+{{ number_format($loai->phu_thu) }}đ)
                            </option>
                        @endforeach
                    </select>
                    <div id="bulkColorPreview" class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 rounded-lg border border-white/10 shadow-sm" style="display: none;"></div>
                </div>
            </div>
            <button type="button" id="btnToggleMaintenance" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-white/10">
                <i class="fa-solid fa-wrench mr-1.5"></i>Bảo trì
            </button>
            <button type="button" id="btnBulkDeleteSeats" class="rounded-xl border border-red-500/40 bg-red-500/10 px-4 py-2.5 text-sm font-medium text-red-300 transition hover:bg-red-500/25 hover:border-red-500/60">
                <i class="fa-solid fa-trash-can mr-1.5"></i>Xóa ghế
            </button>
            <button type="button" id="applyBulkAction" class="rounded-xl bg-[#d99a32] px-6 py-2.5 text-sm font-bold text-black transition hover:bg-[#e5a847] hover:scale-[1.02]">
                <i class="fa-solid fa-check mr-1.5"></i>Áp dụng
            </button>
        </div>
    </div>
</div>

{{-- STATS CARDS --}}
<div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-5">
    <div class="group rounded-2xl border border-white/10 bg-gradient-to-br from-[#0f0f0f] to-[#151515] p-5 transition hover:border-white/20 hover:shadow-lg hover:shadow-white/5">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white/10">
                <i class="fa-solid fa-chair text-lg text-white"></i>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Tổng ghế</p>
                <p class="text-2xl font-black text-white">{{ $tongSoGhe }}</p>
            </div>
        </div>
    </div>
    <div class="group rounded-2xl border border-white/10 bg-gradient-to-br from-[#0f0f0f] to-[#151515] p-5 transition hover:border-gray-400/30 hover:shadow-lg hover:shadow-gray-500/5">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gray-500/20">
                <i class="fa-solid fa-user text-lg text-gray-300"></i>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Thường</p>
                <p class="text-2xl font-black text-gray-200">{{ $gheThuong }}</p>
            </div>
        </div>
    </div>
    <div class="group rounded-2xl border border-white/10 bg-gradient-to-br from-[#0f0f0f] to-[#151515] p-5 transition hover:border-[#d99a32]/30 hover:shadow-lg hover:shadow-[#d99a32]/5">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#d99a32]/20">
                <i class="fa-solid fa-crown text-lg text-[#d99a32]"></i>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-400">VIP</p>
                <p class="text-2xl font-black text-[#d99a32]">{{ $gheVip }}</p>
            </div>
        </div>
    </div>
    <div class="group rounded-2xl border border-white/10 bg-gradient-to-br from-[#0f0f0f] to-[#151515] p-5 transition hover:border-pink-500/30 hover:shadow-lg hover:shadow-pink-500/5">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-pink-500/20">
                <i class="fa-solid fa-heart text-lg text-pink-400"></i>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Couple</p>
                <p class="text-2xl font-black text-pink-400">{{ $gheCouple }}</p>
            </div>
        </div>
    </div>
    <div class="group rounded-2xl border border-white/10 bg-gradient-to-br from-[#0f0f0f] to-[#151515] p-5 transition hover:border-red-500/30 hover:shadow-lg hover:shadow-red-500/5">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-red-500/20">
                <i class="fa-solid fa-wrench text-lg text-red-400"></i>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Bảo trì</p>
                <p class="text-2xl font-black text-red-400">{{ $gheBaoTri }}</p>
            </div>
        </div>
    </div>
</div>

{{-- LICH BAO TRI --}}
<div id="lichBaoTriContainer">
@if($lichBaoTris->count() > 0)
    <div class="mb-6 rounded-2xl border border-amber-500/30 bg-gradient-to-br from-[#0f0f0f] to-[#151515] p-5">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wider text-amber-400">
                <i class="fa-solid fa-calendar-days"></i>
                Lịch bảo trì sắp tới (<span id="lichBaoTriCount">{{ $lichBaoTris->count() }}</span>)
            </h3>
            <span class="text-xs text-gray-500">Click để hủy</span>
        </div>
        <div id="lichBaoTriList" class="space-y-2 max-h-48 overflow-y-auto">
        @foreach($lichBaoTris as $lich)
            <div class="lich-bao-tri-item group flex items-center justify-between rounded-xl border border-white/5 bg-white/5 p-3 transition hover:border-amber-500/30 hover:bg-amber-500/5" data-lich-id="{{ $lich->id }}">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-500/20">
                        <i class="fa-solid fa-couch text-sm text-amber-400"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-white">{{ $lich->gheNgoi->ma_ghe ?? 'N/A' }}</p>
                        <p class="text-xs text-gray-400">
                            {{ $lich->thoi_gian_bat_dau->format('d/m/Y H:i') }}
                            @if($lich->thoi_gian_bat_dau->isFuture())
                                <span class="ml-1 text-amber-400">({{ $lich->thoi_gian_bat_dau->diffForHumans() }})</span>
                            @endif
                            @if($lich->ly_do)
                                - {{ $lich->ly_do }}
                            @endif
                        </p>
                    </div>
                </div>
                <form action="{{ route('admin.lich-bao-tri-ghe-ngois.cancel', $lich) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="button"
                        onclick="openModalHuyBaoTri('{{ route('admin.lich-bao-tri-ghe-ngois.cancel', $lich) }}', '{{ $lich->gheNgoi->ma_ghe ?? 'này' }}')"
                        class="rounded-lg border border-red-500/30 bg-red-500/10 px-3 py-1.5 text-xs font-medium text-red-300 transition hover:border-red-500/60 hover:bg-red-500/20">
                        <i class="fa-solid fa-xmark mr-1"></i>Hủy
                    </button>
                </form>
            </div>
        @endforeach
        </div>
    </div>
@endif
</div>

{{-- MAIN GRID --}}
<div class="grid gap-6 lg:grid-cols-12">

    {{-- LEFT SIDEBAR --}}
    <div class="space-y-5 lg:col-span-3">

        {{-- Thong tin phong --}}
        <div class="rounded-2xl border border-white/10 bg-[#0f0f0f] p-5">
            <h6 class="mb-4 text-sm font-bold uppercase tracking-wider text-[#d99a32]">
                <i class="fa-solid fa-circle-info mr-2"></i>Thông tin phòng
            </h6>
            <div class="space-y-3">
                <div class="flex justify-between border-b border-white/5 pb-3">
                    <span class="text-gray-400">Rạp Chiếu</span>
                    <span class="text-white font-medium">{{ $phongChieu->rapChieuPhim->ten_rap ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between border-b border-white/5 pb-3">
                    <span class="text-gray-400">Tên Phòng</span>
                    <span class="text-white font-bold">{{ $phongChieu->ten_phong }}</span>
                </div>
                <div class="flex justify-between border-b border-white/5 pb-3">
                    <span class="text-gray-400">Loại Phòng</span>
                    <span class="inline-flex items-center rounded-full bg-[#d99a32]/15 px-3 py-1 text-xs font-bold text-[#d99a32]">
                        @php
                            $loaiLabels = ['2d' => '2D', '3d' => '3D', 'imax' => 'IMAX', '4dx' => '4DX'];
                        @endphp
                        {{ $loaiLabels[$phongChieu->loai_phong] ?? strtoupper($phongChieu->loai_phong) }}
                    </span>
                </div>
                <div class="flex justify-between border-b border-white/5 pb-3">
                    <span class="text-gray-400">Sức Chứa</span>
                    <span class="text-white font-semibold">{{ $phongChieu->suc_chua }} ghế</span>
                </div>
                <div class="flex justify-between pt-1">
                    <span class="text-gray-400">Trạng Thái</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold
                        @if($phongChieu->trang_thai === 'hoat_dong') bg-green-500/15 text-green-400
                        @elseif($phongChieu->trang_thai === 'bao_tri') bg-yellow-500/15 text-yellow-400
                        @else bg-gray-500/15 text-gray-400
                        @endif">
                        @php
                            $trangThaiLabels = [
                                'hoat_dong' => 'Hoạt động',
                                'bao_tri' => 'Bảo trì',
                                'ngung_hoat_dong' => 'Ngừng hoạt động'
                            ];
                        @endphp
                        @if($phongChieu->trang_thai === 'hoat_dong') <i class="fa-solid fa-circle-check"></i>
                        @elseif($phongChieu->trang_thai === 'bao_tri') <i class="fa-solid fa-triangle-exclamation"></i>
                        @else <i class="fa-solid fa-circle-xmark"></i>
                        @endif
                        {{ $trangThaiLabels[$phongChieu->trang_thai] ?? $phongChieu->trang_thai }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Tao ghe tu dong --}}
        <div class="rounded-2xl border border-white/10 bg-[#0f0f0f] p-5">
            <h6 class="mb-4 text-sm font-bold uppercase tracking-wider text-[#d99a32]">
                <i class="fa-solid fa-wand-magic-sparkles mr-2"></i>Tạo ghế tự động
            </h6>
            @php
                $currentSeats = $phongChieu->gheNgois;
                $hangGheMax = 0;
                $cotMax = 0;
                $totalCapacity = 0;

                if ($currentSeats->count() > 0) {
                    $hangGheMax = $currentSeats->pluck('hangGhe.ten_hang')->unique()->count();
                    $cotMax = $currentSeats->max('vi_tri_cot') ?? 0;
                    $totalCapacity = $hangGheMax * $cotMax;
                }
            @endphp

            @if($currentSeats->count() >= $totalCapacity && $totalCapacity > 0)
                {{-- PHÒNG ĐÃ ĐẦY GHẾ --}}
                <div class="rounded-xl border border-green-500/30 bg-green-500/10 p-4 text-center">
                    <div class="mb-2 text-4xl">
                        <i class="fa-solid fa-circle-check text-green-400"></i>
                    </div>
                    <h4 class="mb-1 text-base font-bold text-green-400">Phòng Đã Đầy Ghế</h4>
                    <p class="text-sm text-gray-400">
                        {{ $currentSeats->count() }} ghế / {{ $totalCapacity }} chỗ
                        ({{ $hangGheMax }} hàng × {{ $cotMax }} cột)
                    </p>
                </div>

                <div class="mt-4 space-y-3">
                    <button type="button" onclick="confirmResetSeats()"
                        class="flex w-full items-center justify-center gap-2 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-2.5 text-sm font-medium text-red-400 transition hover:bg-red-500/20">
                        <i class="fa-solid fa-trash-can"></i>
                        Reset Phòng (Xóa toàn bộ ghế)
                    </button>
                </div>
            @elseif($currentSeats->count() > 0)
                {{-- PHÒNG CÓ GHẾ NHƯNG CHƯA ĐẦY --}}
                <div class="mb-4 rounded-xl border border-yellow-500/30 bg-yellow-500/10 p-3 text-sm text-yellow-400">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                    Phòng đã có <strong>{{ $currentSeats->count() }}</strong> ghế
                    ({{ $hangGheMax }} hàng × {{ $cotMax }} cột).
                        Tạo mới sẽ xóa toàn bộ ghế cũ.
                </div>

                <form action="{{ route('admin.phong-chieus.generate-seats', $phongChieu) }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-2 block text-xs font-medium text-gray-400">Số Hàng</label>
                                <input type="number" name="so_hang" value="8" min="1" max="20" required
                                    class="w-full rounded-xl border border-white/10 bg-[#151515] px-4 py-2.5 text-sm text-white outline-none transition focus:border-[#d99a32] focus:ring-1 focus:ring-[#d99a32]/30">
                            </div>
                            <div>
                                <label class="mb-2 block text-xs font-medium text-gray-400">Số Cột</label>
                                <input type="number" name="so_cot" value="10" min="1" max="20" required
                                    class="w-full rounded-xl border border-white/10 bg-[#151515] px-4 py-2.5 text-sm text-white outline-none transition focus:border-[#d99a32] focus:ring-1 focus:ring-[#d99a32]/30">
                            </div>
                        </div>

                        {{-- SEAT TYPE CARDS --}}
                        <div class="space-y-4">
                            {{-- Ghế Thường --}}
                            <div>
                                <label class="mb-2 flex items-center gap-2 text-xs font-medium text-gray-400">
                                    <i class="fa-solid fa-couch text-[#d99a32]"></i>
                                    Loại ghế Thường
                                    <span class="ml-1 rounded bg-[#d99a32]/20 px-1.5 py-0.5 text-[10px] text-[#d99a32]">BẮT BUỘC</span>
                                </label>
                                <div class="flex flex-wrap gap-2">
                                    @php $firstNormal = true; @endphp
                                    @foreach(\App\Models\LoaiGhe::where('la_couple', false)->where('ten_loai', 'not like', '%vip%')->get() as $loai)
                                        <label class="seat-type-pill {{ $firstNormal ? 'active' : '' }}" data-value="{{ $loai->id }}" data-hidden="loai_ghe_thuong_id">
                                            <input type="radio" name="loai_ghe_thuong_radio" value="{{ $loai->id }}" {{ $firstNormal ? 'checked' : '' }} class="sr-only">
                                            <span class="pill-color" style="background-color: {{ $loai->mau_sac ?? '#666' }}"></span>
                                            <span class="pill-text">{{ $loai->ten_loai }}</span>
                                            <span class="pill-price">+{{ number_format($loai->phu_thu) }}đ</span>
                                        </label>
                                        @php $firstNormal = false; @endphp
                                    @endforeach
                                </div>
                                <input type="hidden" name="loai_ghe_thuong_id" id="loai_ghe_thuong_id" value="{{ \App\Models\LoaiGhe::where('la_couple', false)->first()->id ?? '' }}">
                            </div>

                            {{-- Ghế VIP --}}
                            <div>
                                <label class="mb-2 flex items-center gap-2 text-xs font-medium text-gray-400">
                                    <i class="fa-solid fa-star text-yellow-400"></i>
                                    Loại ghế VIP
                                    <span class="ml-1 rounded bg-gray-500/20 px-1.5 py-0.5 text-[10px] text-gray-500">TÙY CHỌN</span>
                                </label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach(\App\Models\LoaiGhe::where('la_couple', false)->where('ten_loai', 'like', '%vip%')->get() as $loai)
                                        <label class="seat-type-pill" data-value="{{ $loai->id }}" data-hidden="loai_ghe_vip_id">
                                            <input type="radio" name="loai_ghe_vip_radio" value="{{ $loai->id }}" class="sr-only">
                                            <span class="pill-color" style="background-color: {{ $loai->mau_sac ?? '#666' }}"></span>
                                            <span class="pill-text">{{ $loai->ten_loai }}</span>
                                            <span class="pill-price">+{{ number_format($loai->phu_thu) }}đ</span>
                                        </label>
                                    @endforeach
                                </div>
                                <input type="hidden" name="loai_ghe_vip_id" id="loai_ghe_vip_id" value="">
                            </div>

                            {{-- Ghế Couple --}}
                            <div>
                                <label class="mb-2 flex items-center gap-2 text-xs font-medium text-gray-400">
                                    <i class="fa-solid fa-heart text-pink-400"></i>
                                    Loại ghế Couple
                                    <span class="ml-1 rounded bg-gray-500/20 px-1.5 py-0.5 text-[10px] text-gray-500">TÙY CHỌN</span>
                                </label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach(\App\Models\LoaiGhe::where('la_couple', true)->get() as $loai)
                                        <label class="seat-type-pill" data-value="{{ $loai->id }}" data-hidden="loai_ghe_couple_id">
                                            <input type="radio" name="loai_ghe_couple_radio" value="{{ $loai->id }}" class="sr-only">
                                            <span class="pill-color" style="background-color: {{ $loai->mau_sac ?? '#666' }}"></span>
                                            <span class="pill-text">{{ $loai->ten_loai }}</span>
                                            <span class="pill-price">+{{ number_format($loai->phu_thu) }}đ</span>
                                        </label>
                                    @endforeach
                                </div>
                                <input type="hidden" name="loai_ghe_couple_id" id="loai_ghe_couple_id" value="">
                            </div>
                        </div>
                    </div>
                    <button type="submit"
                        class="mt-5 w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-4 py-3 text-sm font-bold text-white shadow-lg shadow-[#d99a32]/20 transition hover:scale-[1.02] hover:shadow-xl hover:shadow-[#d99a32]/30">
                        <i class="fa-solid fa-couch"></i>
                        Tạo Ghế Mới (Xóa ghế cũ)
                    </button>
                </form>
            @else
                {{-- PHÒNG TRỐNG - CHƯA CÓ GHẾ --}}
                <form action="{{ route('admin.phong-chieus.generate-seats', $phongChieu) }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="mb-2 block text-xs font-medium text-gray-400">Số Hàng</label>
                            <input type="number" name="so_hang" value="8" min="1" max="20" required
                                class="w-full rounded-xl border border-white/10 bg-[#151515] px-4 py-2.5 text-sm text-white outline-none transition focus:border-[#d99a32] focus:ring-1 focus:ring-[#d99a32]/30">
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-medium text-gray-400">Số Cột</label>
                            <input type="number" name="so_cot" value="10" min="1" max="20" required
                                class="w-full rounded-xl border border-white/10 bg-[#151515] px-4 py-2.5 text-sm text-white outline-none transition focus:border-[#d99a32] focus:ring-1 focus:ring-[#d99a32]/30">
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-medium text-gray-400">Loại ghế Thường</label>
                            <select name="loai_ghe_thuong_id" required
                                class="w-full rounded-xl border border-white/10 bg-[#151515] px-4 py-2.5 text-sm text-white outline-none transition focus:border-[#d99a32] focus:ring-1 focus:ring-[#d99a32]/30">
                                @foreach(\App\Models\LoaiGhe::where('la_couple', false)->get() as $loai)
                                    <option value="{{ $loai->id }}">{{ $loai->ten_loai }} (+{{ number_format($loai->phu_thu) }}đ)</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-medium text-gray-400">Loại ghế VIP</label>
                            <select name="loai_ghe_vip_id"
                                class="w-full rounded-xl border border-white/10 bg-[#151515] px-4 py-2.5 text-sm text-white outline-none transition focus:border-[#d99a32] focus:ring-1 focus:ring-[#d99a32]/30">
                                <option value="">-- Không có VIP --</option>
                                @foreach(\App\Models\LoaiGhe::where('la_couple', false)->where('ten_loai', 'like', '%vip%')->get() as $loai)
                                    <option value="{{ $loai->id }}">{{ $loai->ten_loai }} (+{{ number_format($loai->phu_thu) }}đ)</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-medium text-gray-400">Loại ghế Couple</label>
                            <select name="loai_ghe_couple_id"
                                class="w-full rounded-xl border border-white/10 bg-[#151515] px-4 py-2.5 text-sm text-white outline-none transition focus:border-[#d99a32] focus:ring-1 focus:ring-[#d99a32]/30">
                                @foreach(\App\Models\LoaiGhe::where('la_couple', true)->get() as $loai)
                                    <option value="{{ $loai->id }}">{{ $loai->ten_loai }} (+{{ number_format($loai->phu_thu) }}đ)</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit"
                        class="mt-5 w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-4 py-3 text-sm font-bold text-white shadow-lg shadow-[#d99a32]/20 transition hover:scale-[1.02] hover:shadow-xl hover:shadow-[#d99a32]/30">
                        <i class="fa-solid fa-couch"></i>
                        Tạo Ghế Tự Động
                    </button>
                </form>
            @endif
        </div>

        {{-- Huong dan su dung --}}
        <div class="rounded-2xl border border-white/10 bg-[#0f0f0f] p-5">
            <h6 class="mb-4 text-sm font-bold uppercase tracking-wider text-[#d99a32]">
                <i class="fa-solid fa-lightbulb mr-2"></i>Hướng dẫn
            </h6>
            <ul class="space-y-3 text-xs text-gray-400">
                <li class="flex items-start gap-3">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-[#d99a32]/15 text-[10px] font-bold text-[#d99a32]">1</span>
                    <span>Click ghế để xem tùy chọn chỉnh sửa</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-[#d99a32]/15 text-[10px] font-bold text-[#d99a32]">2</span>
                    <span>Ctrl + Click để chọn nhiều ghế cùng lúc</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-[#d99a32]/15 text-[10px] font-bold text-[#d99a32]">3</span>
                    <span>Click vào mã hàng (A, B, C...) để đổi loại cả hàng</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-[#d99a32]/15 text-[10px] font-bold text-[#d99a32]">4</span>
                    <span>Click chuột phải vào ghế để bật/tắt bảo trì</span>
                </li>
            </ul>
        </div>

    </div>

    {{-- RIGHT: SEAT MAP + ROW TABLE --}}
    <div class="space-y-6 lg:col-span-9">

        {{-- So do ghe --}}
        <div class="rounded-2xl border border-white/10 bg-[#0f0f0f] p-6 lg:p-8">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h6 class="text-sm font-bold uppercase tracking-wider text-gray-400">
                        Sơ đồ Ghế
                    </h6>
                    <p class="mt-1 text-xs text-gray-500">
                        {{ $soHang }} hàng × {{ $soCot }} cột = <span class="text-white font-semibold">{{ $phongChieu->gheNgois->count() }}</span> ghế
                    </p>
                </div>
            </div>

            @if(count($seatMap) > 0)
                {{-- Legend --}}
                <div class="mb-8 flex flex-wrap items-center justify-center gap-5">
                    @foreach(\App\Models\LoaiGhe::all() as $loai)
                        <div class="flex items-center gap-2">
                            <span class="inline-block h-4 w-5 rounded-md shadow-sm" style="background-color: {{ $loai->mau_sac ?? '#666' }};"></span>
                            <span class="text-xs font-medium text-gray-300">{{ $loai->ten_loai }}</span>
                        </div>
                    @endforeach
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-4 w-5 items-center justify-center rounded-md bg-red-500/80 text-[10px] font-bold text-white">M</span>
                        <span class="text-xs font-medium text-gray-300">Bảo trì</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-block h-4 w-5 rounded-md border-2 border-[#d99a32] bg-[#d99a32]/30"></span>
                        <span class="text-xs font-medium text-gray-300">Đã chọn</span>
                    </div>
                </div>

                {{-- Seat Map Container --}}
                <div class="seat-map-container overflow-x-auto pb-4">
                    <div class="seat-map-inner mx-auto inline-flex min-w-[600px] flex-col items-center">

                        {{-- SCREEN --}}
                        <div class="screen-glow mb-8 flex w-full items-center justify-center">
                            <div class="screen-bar relative flex items-center justify-center overflow-hidden rounded-t-2xl bg-gradient-to-b from-[#d99a32] via-[#b8832a] to-[#8a5a1a] px-16 py-3 shadow-lg shadow-[#d99a32]/30">
                                <div class="absolute inset-0 bg-gradient-to-b from-white/20 to-transparent"></div>
                                <span class="relative text-sm font-black uppercase tracking-[0.3em] text-[#1a0b04]">Màn Hình</span>
                                <div class="absolute -bottom-3 left-1/2 h-3 w-24 -translate-x-1/2 rounded-full bg-[#d99a32]/30 blur-xl"></div>
                            </div>
                        </div>

                        {{-- Seat Rows --}}
                        @foreach($seatMap as $tenHang => $cotGhe)
                            @php
                                $hangGhe = $phongChieu->hangGhes->where('ten_hang', $tenHang)->first();
                                $rowIndex = $loop->iteration;
                            @endphp
                            <div class="seat-row mb-3" data-hang-ghe-id="{{ $hangGhe->id ?? '' }}" data-hang="{{ $tenHang }}">
                                <span class="seat-row__label seat-row__label--clickable min-w-[52px] cursor-pointer" title="Hàng {{ $rowIndex }} - Click để đổi cả hàng">
                                    <span class="seat-row__letter">{{ $tenHang }}</span>
                                </span>
                                <div class="seat-row__seats">
                                    @php
                                        $j = 1;
                                    @endphp
                                    @while($j <= $soCot)
                                        @if(isset($cotGhe[$j]))
                                            @php
                                                $ghe = $cotGhe[$j];
                                                $bgColor = $ghe['mau_sac'] ?? '#666666';
                                                $isMaintenance = $ghe['trang_thai'] === 'bao_tri';
                                                $isCouple = !empty($ghe['is_couple']) && $ghe['is_couple'] === true;
                                                $lichId = $ghe['lich_id'] ?? null;
                                                $thoiGianKetThuc = $ghe['thoi_gian_ket_thuc'] ?? null;
                                                $isUnlimitedMaintenance = $ghe['is_unlimited_maintenance'] ?? false;
                                            @endphp
                                            @if($isCouple && $ghe['couple_position'] === 'left')
                                                {{-- Couple LEFT: render 1 block to duy nhất chứa 2 nhãn ghế --}}
                                                @php
                                                    $gheRight = $cotGhe[$ghe['cot_end']] ?? null;
                                                    $coupleGroupId = $ghe['couple_group_id'] ?? '';
                                                    $siblings = $ghe['couple_siblings'] ?? [];
                                                @endphp
                                                <div class="seat-chip seat-interactive seat-chip--couple
                                                    {{ $isMaintenance ? 'seat-chip--maintenance' : '' }}"
                                                    @if(!$isMaintenance) style="background-color: {{ $bgColor }}; color: #1a0b04 !important;" @else style="color: #991b1b !important;" @endif
                                                    data-ghe-id="{{ $ghe['id'] }}"
                                                    data-ma-ghe="{{ $ghe['ma_ghe'] }}"
                                                    data-loai-ghe="{{ $ghe['loai_ghe'] ?? 'Couple' }}"
                                                    data-loai-ghe-id="{{ $ghe['loai_ghe_id'] ?? '' }}"
                                                    data-mau-sac="{{ $ghe['mau_sac'] ?? '#666666' }}"
                                                    data-phu-thu="{{ $ghe['phu_thu'] ?? 0 }}"
                                                    data-trang-thai="{{ $ghe['trang_thai'] }}"
                                                    data-cot="{{ $j }}"
                                                    data-couple-group="{{ $coupleGroupId }}"
                                                    data-couple-siblings='@json($siblings)'
                                                    data-couple-position="left"
                                                    @if($lichId) data-lich-id="{{ $lichId }}" @endif
                                                    @if($thoiGianKetThuc) data-thoi-gian-ket-thuc="{{ $thoiGianKetThuc }}" @endif
                                                    @if($isUnlimitedMaintenance) data-is-unlimited-maintenance="1" @endif
                                                    title="{{ $ghe['ma_ghe'] }} - {{ $ghe['loai_ghe'] ?? 'Couple' }} ({{ number_format($ghe['phu_thu'] ?? 0) }}đ)">
                                                    <span class="seat-couple-left seat-label" style="color: #1a0b04 !important; font-weight: 900 !important; z-index: 10 !important; position: relative !important;">{{ $tenHang }}{{ $ghe['display_number'] }}</span>
                                                    @if($gheRight)
                                                        <span class="seat-couple-sep" style="color: rgba(0,0,0,0.45) !important; font-weight: 900 !important; z-index: 10 !important; position: relative !important; margin: 0 2px !important; user-select: none;">|</span>
                                                        <span class="seat-couple-right seat-label" style="color: #1a0b04 !important; font-weight: 900 !important; z-index: 10 !important; position: relative !important;">{{ $tenHang }}{{ $gheRight['display_number'] }}</span>
                                                    @endif
                                                </div>
                                                @php $j = ($ghe['cot_end'] ?? ($j+1)) + 1; @endphp
                                            @elseif($isCouple && $ghe['couple_position'] === 'right')
                                                {{-- Bỏ qua - đã render cùng ghế left --}}
                                                @php $j++; @endphp
                                            @else
                                                {{-- Normal seat: single seat with column number --}}
                                                <div class="seat-chip seat-interactive
                                                    {{ $isMaintenance ? 'seat-chip--maintenance' : '' }}"
                                                    @if(!$isMaintenance) style="background-color: {{ $bgColor }}; color: #1a0b04 !important;" @else style="color: #991b1b !important;" @endif
                                                    data-ghe-id="{{ $ghe['id'] }}"
                                                    data-ma-ghe="{{ $ghe['ma_ghe'] }}"
                                                    data-loai-ghe="{{ $ghe['loai_ghe'] ?? 'Thường' }}"
                                                    data-loai-ghe-id="{{ $ghe['loai_ghe_id'] ?? '' }}"
                                                    data-mau-sac="{{ $ghe['mau_sac'] ?? '#666666' }}"
                                                    data-phu-thu="{{ $ghe['phu_thu'] ?? 0 }}"
                                                    data-trang-thai="{{ $ghe['trang_thai'] }}"
                                                    @if($lichId) data-lich-id="{{ $lichId }}" @endif
                                                    @if($thoiGianKetThuc) data-thoi-gian-ket-thuc="{{ $thoiGianKetThuc }}" @endif
                                                    @if($isUnlimitedMaintenance) data-is-unlimited-maintenance="1" @endif
                                                    title="{{ $ghe['ma_ghe'] }} - {{ $ghe['loai_ghe'] ?? 'Thường' }} ({{ number_format($ghe['phu_thu'] ?? 0) }}đ)">
                                                    <span class="seat-label" style="color: #1a0b04 !important; font-weight: 900 !important; font-size: 14px !important; text-shadow: 0 1px 0 rgba(255,255,255,0.8), 1px 0 0 rgba(255,255,255,0.5), -1px 0 0 rgba(255,255,255,0.5) !important; z-index: 10 !important; position: relative !important; display: inline-block !important;">{{ $tenHang }}{{ $j }}</span>
                                                </div>
                                                @php $j++; @endphp
                                            @endif
                                        @else
                                            <div class="seat-chip seat-chip--empty"></div>
                                            @php $j++; @endphp
                                        @endif
                                    @endwhile
                                </div>
                                <span class="seat-row__label seat-row__label--clickable min-w-[52px] cursor-pointer" title="Hàng {{ $rowIndex }} - Click để đổi cả hàng">
                                    <span class="seat-row__letter">{{ $tenHang }}</span>
                                </span>
                            </div>
                        @endforeach

                    </div>
                </div>
            @else
                <div class="py-16 text-center">
                    <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-2xl bg-white/5">
                        <i class="fa-solid fa-chair text-3xl text-gray-600"></i>
                    </div>
                    <p class="text-gray-400">Phòng chưa có ghế. Vui lòng tạo ghế tự động.</p>
                </div>
            @endif

        </div>

        {{-- Danh sach hang ghe --}}
        <div class="rounded-2xl border border-white/10 bg-[#0f0f0f] p-6 lg:p-8">
            <h6 class="mb-5 text-sm font-bold uppercase tracking-wider text-[#d99a32]">
                <i class="fa-solid fa-table-list mr-2"></i>Danh sách hàng ghế
            </h6>
            @if($phongChieu->hangGhes->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-white/10 text-left text-xs uppercase tracking-wider text-gray-500">
                                <th class="pb-4 pr-4 font-semibold">Hàng</th>
                                <th class="pb-4 pr-4 font-semibold text-center">Tổng ghế</th>
                                <th class="pb-4 pr-4 font-semibold text-center">Thường</th>
                                <th class="pb-4 pr-4 font-semibold text-center">VIP</th>
                                <th class="pb-4 pr-4 font-semibold text-center">Couple</th>
                                <th class="pb-4 pr-4 font-semibold text-center">Bảo trì</th>
                                <th class="pb-4 font-semibold text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @foreach($phongChieu->hangGhes as $hang)
                                @php
                                    $tongGhe = $hang->gheNgois->count();
                                    $gheHoatDong = $hang->gheNgois->where('trang_thai', 'hoat_dong');
                                    $gheThuongRow = 0;
                                    $gheVipRow = 0;
                                    $gheCoupleRow = 0;
                                    foreach ($gheHoatDong as $ghe) {
                                        $loaiTen = $ghe->loaiGhe->ten_loai ?? 'Thường';
                                        if (stripos($loaiTen, 'couple') !== false || stripos($loaiTen, 'đôi') !== false) {
                                            $gheCoupleRow++;
                                        } elseif (stripos($loaiTen, 'vip') !== false) {
                                            $gheVipRow++;
                                        } else {
                                            $gheThuongRow++;
                                        }
                                    }
                                    $gheBaoTriRow = $hang->gheNgois->where('trang_thai', 'bao_tri')->count();
                                @endphp
                                <tr class="group transition hover:bg-white/[0.02]">
                                    <td class="py-4 pr-4">
                                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-[#d99a32]/15 text-sm font-black text-[#d99a32]">
                                            {{ $hang->ten_hang }}
                                        </span>
                                    </td>
                                    <td class="py-4 pr-4 text-center">
                                        <span class="inline-flex h-7 min-w-[28px] items-center justify-center rounded-lg bg-white/10 px-2 text-xs font-bold text-white">
                                            {{ $tongGhe }}
                                        </span>
                                    </td>
                                    <td class="py-4 pr-4 text-center text-gray-300">{{ $gheThuongRow }}</td>
                                    <td class="py-4 pr-4 text-center text-[#d99a32]">{{ $gheVipRow }}</td>
                                    <td class="py-4 pr-4 text-center text-pink-400">{{ $gheCoupleRow }}</td>
                                    <td class="py-4 pr-4 text-center text-red-400">{{ $gheBaoTriRow }}</td>
                                    <td class="py-4 text-center">
                                        <button type="button"
                                            data-hang-ghe-id="{{ $hang->id }}"
                                            data-hang="{{ $hang->ten_hang }}"
                                            data-row-trigger
                                            class="inline-flex items-center gap-1.5 rounded-xl border border-[#d99a32]/30 bg-[#d99a32]/10 px-3 py-2 text-xs font-bold text-[#d99a32] transition hover:bg-[#d99a32]/20 hover:border-[#d99a32]/50">
                                            <i class="fa-solid fa-palette"></i>
                                            Đổi loại
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-center text-gray-500 py-8">Chưa có hàng ghế nào.</p>
            @endif
        </div>

    </div>

</div>

{{-- ROW CHANGE MODAL --}}
<div id="rowChangeModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/75 backdrop-blur-[6px]">
    <div class="w-full max-w-md mx-4 rounded-2xl border border-white/10 bg-gradient-to-b from-[#1a1a2e] to-[#0f0f1a] p-0 shadow-2xl shadow-black/60 overflow-hidden animate-modal-in">
        {{-- Header với gradient --}}
        <div class="relative px-6 pt-6 pb-5 bg-gradient-to-r from-[#d99a32]/10 via-[#d99a32]/5 to-transparent border-b border-white/5">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-[#d99a32] via-[#e5a847] to-transparent"></div>
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-[#d99a32]/20 to-[#d99a32]/5 border border-[#d99a32]/20">
                        <i class="fa-solid fa-palette text-xl text-[#d99a32]"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white leading-tight">
                            Đổi loại ghế hàng <span id="rowChangeModalTenHang" class="text-[#d99a32]"></span>
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5"><span id="rowChangeModalRowIndex"></span></p>
                    </div>
                </div>
                <button type="button" id="rowChangeModalCancelHeader" class="text-gray-500 hover:text-white hover:bg-white/10 rounded-lg p-1.5 transition-all">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
        </div>

        {{-- Body --}}
        <div class="px-6 py-5">
            {{-- Chọn loại ghế --}}
            <div class="mb-5">
                <label class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-gray-400">
                    <span class="h-px flex-1 bg-white/5"></span>
                    Chọn loại ghế mới
                    <span class="h-px flex-1 bg-white/5"></span>
                </label>
                <div class="relative" id="rowChangeModalLoaiGheWrapper">
                    <div class="custom-select" data-select-id="rowChangeModalLoaiGhe">
                        <div class="custom-select__trigger" onclick="toggleCustomSelect(this)">
                            <div class="custom-select__value">
                                <div class="custom-select__color-dot" id="rowModalColorDot" style="background-color: #666"></div>
                                <span class="custom-select__text custom-select__text--placeholder">-- Chọn loại ghế --</span>
                            </div>
                            <div class="custom-select__arrow">
                                <i class="fa-solid fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                        <div class="custom-select__dropdown">
                            @foreach(\App\Models\LoaiGhe::all() as $loai)
                                <div class="custom-select__option" data-value="{{ $loai->id }}" data-color="{{ $loai->mau_sac ?? '#666' }}" data-la-couple="{{ $loai->la_couple ? '1' : '0' }}" onclick="selectCustomOption(this)">
                                    <div class="custom-select__option-color" style="background-color: {{ $loai->mau_sac ?? '#666' }}"></div>
                                    <div class="custom-select__option-content">
                                        <div class="custom-select__option-label">{{ $loai->ten_loai }}</div>
                                        <div class="custom-select__option-meta">+{{ number_format($loai->phu_thu) }}đ</div>
                                    </div>
                                    <div class="custom-select__option-check">
                                        <i class="fa-solid fa-check text-white text-[10px]"></i>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <select id="rowChangeModalLoaiGhe" class="hidden">
                        @foreach(\App\Models\LoaiGhe::all() as $loai)
                            <option value="{{ $loai->id }}" data-color="{{ $loai->mau_sac ?? '#666' }}" data-la-couple="{{ $loai->la_couple ? '1' : '0' }}" class="bg-[#1a1a2e]">
                                {{ $loai->ten_loai }} — +{{ number_format($loai->phu_thu) }}đ
                            </option>
                        @endforeach
                    </select>
                </div>
                <p class="mt-2 text-xs text-gray-600 text-center">Thay đổi áp dụng cho <span class="text-gray-400 font-medium">tất cả ghế</span> trong hàng này</p>
                <div id="rowTypeRestrictionWarning" class="hidden mt-3 rounded-xl border border-amber-500/30 bg-gradient-to-r from-amber-500/10 to-orange-500/5 p-3.5">
                    <div class="flex items-start gap-2.5">
                        <div class="flex h-6 w-6 items-center justify-center rounded-lg bg-amber-500/20 mt-0.5">
                            <i class="fa-solid fa-triangle-exclamation text-xs text-amber-400"></i>
                        </div>
                        <div class="text-xs text-amber-300/90 leading-relaxed">
                            <strong class="font-semibold">Hàng ghế không được phép đổi loại.</strong><br>
                            <span id="rowRestrictionDetail" class="text-amber-400/80"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bảo trì cả hàng --}}
            <div class="mb-5 rounded-xl border border-white/8 bg-gradient-to-r from-white/[0.03] to-white/[0.01] p-4 backdrop-blur-sm">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-orange-500/20 to-orange-500/5 border border-orange-500/15">
                            <i class="fa-solid fa-wrench text-sm text-orange-400"></i>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-white">Bảo trì cả hàng</div>
                            <div class="text-[11px] text-gray-500 mt-0.5">
                                <span id="rowMaintenanceStats">--</span>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="rowMaintenanceBtn" onclick="window.__rowMaintClick && window.__rowMaintClick(event)" class="rounded-xl border border-orange-500/30 bg-gradient-to-r from-orange-500/10 to-orange-500/5 px-4 py-2 text-xs font-bold text-orange-300 transition-all hover:from-orange-500/20 hover:to-orange-500/10 hover:border-orange-500/50 hover:shadow-lg hover:shadow-orange-500/10 active:scale-95">
                        <i class="fa-solid fa-wrench mr-1.5"></i><span id="rowMaintenanceBtnLabel">Bảo trì</span>
                    </button>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex gap-2.5">
                <button type="button" id="rowChangeModalDelete" class="rounded-xl border border-red-500/25 bg-red-500/8 px-4 py-3 text-xs font-bold text-red-400/90 transition-all hover:from-red-500/15 hover:to-red-500/5 hover:border-red-500/40 active:scale-[0.98]">
                    <i class="fa-solid fa-trash-can mr-1.5"></i>Xóa hàng
                </button>
                <button type="button" id="rowChangeModalCancel" class="flex-1 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-medium text-gray-300 transition-all hover:bg-white/10 hover:text-white active:scale-[0.98]">
                    <i class="fa-solid fa-xmark mr-1.5"></i>Hủy
                </button>
                <button type="button" id="rowChangeModalApply" class="flex-1 rounded-xl bg-gradient-to-r from-[#d99a32] to-[#e5a847] px-4 py-3 text-sm font-bold text-black shadow-lg shadow-[#d99a32]/20 transition-all hover:from-[#e5a847] hover:to-[#d99a32] hover:shadow-[#d99a32]/30 active:scale-[0.98]">
                    <i class="fa-solid fa-check mr-1.5"></i>Áp dụng
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL: BẢO TRÌ GHẾ (VÔ THỜI HẠN / CÓ THỜI HẠN) --}}
<div id="maintenanceModal" class="hidden fixed inset-0 z-[99999] flex items-center justify-center bg-black/75 backdrop-blur-[6px]">
    <div class="w-full max-w-lg mx-4 rounded-2xl border border-white/10 bg-gradient-to-b from-[#1a1a2e] to-[#0f0f1a] p-0 shadow-2xl shadow-black/60 overflow-hidden animate-modal-in">
        {{-- Header --}}
        <div class="relative px-6 pt-6 pb-5 bg-gradient-to-r from-orange-500/10 via-orange-500/5 to-transparent border-b border-white/5">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-orange-500 via-amber-500 to-orange-500"></div>
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-orange-500/20 to-orange-500/5 border border-orange-500/20">
                        <i class="fa-solid fa-wrench text-xl text-orange-400"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white leading-tight">
                            Bảo trì ghế <span id="maintenanceModalSeatCount" class="text-orange-400"></span>
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">Chọn loại bảo trì phù hợp</p>
                    </div>
                </div>
                <button type="button" id="maintenanceModalCloseHeader" class="text-gray-500 hover:text-white hover:bg-white/10 rounded-lg p-1.5 transition-all">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
        </div>

        {{-- Body --}}
        <div class="px-6 py-5">
            {{-- Hiển thị lỗi --}}
            <div id="maintenanceErrors" class="hidden mb-4 p-3 bg-red-500/10 border border-red-500/30 rounded-xl">
                <div class="flex items-start gap-2">
                    <i class="fa-solid fa-circle-exclamation text-red-400 mt-0.5"></i>
                    <div id="maintenanceErrorsList" class="text-sm text-red-300"></div>
                </div>
            </div>

            {{-- Chọn loại bảo trì --}}
            <div class="mb-5">
                <label class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-gray-400">
                    <span class="h-px flex-1 bg-white/5"></span>
                    Loại bảo trì
                    <span class="h-px flex-1 bg-white/5"></span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="maintenance-type-option cursor-pointer group">
                        <input type="radio" name="maintenance_type" value="unlimited" class="peer hidden" checked>
                        <div class="relative rounded-xl border-2 border-white/10 bg-gradient-to-b from-white/[0.04] to-white/[0.01] p-4 transition-all duration-200 peer-checked:border-orange-500 peer-checked:from-orange-500/12 peer-checked:to-orange-500/5 hover:border-white/20 overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-br from-orange-500/5 to-transparent opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                            <div class="relative flex flex-col items-center text-center gap-2">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-orange-500/20 to-orange-500/5 border border-orange-500/20 group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-infinity text-orange-400"></i>
                                </div>
                                <span class="font-bold text-white text-sm">Vô thời hạn</span>
                                <span class="text-[11px] text-gray-500 leading-relaxed">Khóa đến khi<br>Admin mở lại</span>
                            </div>
                            <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 transition-opacity">
                                <div class="flex h-5 w-5 items-center justify-center rounded-full bg-orange-500">
                                    <i class="fa-solid fa-check text-[9px] text-white"></i>
                                </div>
                            </div>
                        </div>
                    </label>
                    <label class="maintenance-type-option cursor-pointer group">
                        <input type="radio" name="maintenance_type" value="scheduled" class="peer hidden">
                        <div class="relative rounded-xl border-2 border-white/10 bg-gradient-to-b from-white/[0.04] to-white/[0.01] p-4 transition-all duration-200 peer-checked:border-blue-500 peer-checked:from-blue-500/12 peer-checked:to-blue-500/5 hover:border-white/20 overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                            <div class="relative flex flex-col items-center text-center gap-2">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500/20 to-blue-500/5 border border-blue-500/20 group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-stopwatch text-blue-400"></i>
                                </div>
                                <span class="font-bold text-white text-sm">Có thời hạn</span>
                                <span class="text-[11px] text-gray-500 leading-relaxed">Tự động mở<br>khi hết hạn</span>
                            </div>
                            <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 transition-opacity">
                                <div class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-500">
                                    <i class="fa-solid fa-check text-[9px] text-white"></i>
                                </div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Form bảo trì có thời hạn --}}
            <div id="scheduledMaintenanceForm" class="hidden mb-5 space-y-4 rounded-xl border border-blue-500/20 bg-gradient-to-b from-blue-500/8 to-blue-500/3 p-5 backdrop-blur-sm">
                <div class="flex items-center gap-2.5 mb-1">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-500/20">
                        <i class="fa-solid fa-clock text-xs text-blue-400"></i>
                    </div>
                    <span class="text-sm font-bold text-blue-300">Thông tin bảo trì có thời hạn</span>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    {{-- Ngày bắt đầu --}}
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-xs font-semibold text-gray-300">
                            <span class="flex h-5 w-5 items-center justify-center rounded-md bg-blue-500/20">
                                <i class="fa-solid fa-play text-[9px] text-blue-400"></i>
                            </span>
                            Bắt đầu <span class="text-red-400">*</span>
                        </label>
                        <div class="relative group/datetime">
                            <input type="datetime-local" id="maintenanceStartTime"
                                class="w-full rounded-xl border-2 border-white/10 bg-[#0a0a14] px-4 py-3 pr-12 text-sm text-white outline-none transition-all placeholder:text-gray-600 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15 cursor-pointer dark:[color-scheme:dark]"
                                min="">
                            <button type="button" onclick="document.getElementById('maintenanceStartTime').showPicker()" 
                                class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-blue-400/50 hover:text-blue-400 transition-all group-hover/datetime:text-blue-300 cursor-pointer">
                                <i class="fa-solid fa-calendar-days"></i>
                            </button>
                        </div>
                    </div>
                    
                    {{-- Ngày kết thúc --}}
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-xs font-semibold text-gray-300">
                            <span class="flex h-5 w-5 items-center justify-center rounded-md bg-blue-500/20">
                                <i class="fa-solid fa-stop text-[9px] text-blue-400"></i>
                            </span>
                            Kết thúc <span class="text-gray-500 font-normal">(Tùy chọn)</span>
                        </label>
                        <div class="relative group/datetime">
                            <input type="datetime-local" id="maintenanceEndTime"
                                class="w-full rounded-xl border-2 border-white/10 bg-[#0a0a14] px-4 py-3 pr-12 text-sm text-white outline-none transition-all placeholder:text-gray-600 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15 cursor-pointer dark:[color-scheme:dark]"
                                min="">
                            <button type="button" onclick="document.getElementById('maintenanceEndTime').showPicker()"
                                class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-blue-400/50 hover:text-blue-400 transition-all group-hover/datetime:text-blue-300 cursor-pointer">
                                <i class="fa-solid fa-calendar-days"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                {{-- Lý do bảo trì --}}
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-xs font-semibold text-gray-300">
                        <span class="flex h-5 w-5 items-center justify-center rounded-md bg-blue-500/20">
                            <i class="fa-solid fa-message-lines text-[9px] text-blue-400"></i>
                        </span>
                        Lý do bảo trì <span class="text-red-400">*</span>
                    </label>
                    <textarea id="maintenanceReason" rows="2"
                        class="w-full rounded-xl border-2 border-white/10 bg-[#0a0a14] px-4 py-3 text-sm text-white outline-none transition-all placeholder:text-gray-600 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15 resize-none"
                        placeholder="VD: Thay đệm ghế, sửa cơ chế ngả..."></textarea>
                </div>
                
                {{-- Thông báo --}}
                <div class="flex items-start gap-3 rounded-xl border border-blue-500/20 bg-blue-500/10 px-4 py-3">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-500/20">
                        <i class="fa-solid fa-circle-info text-sm text-blue-400"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-blue-300">Tự động kích hoạt lại</p>
                        <p class="mt-0.5 text-xs text-gray-400">Khi hết thời gian bảo trì, ghế sẽ tự động hoạt động trở lại mà không cần thao tác thủ công.</p>
                    </div>
                </div>
            </div>

            {{-- Lý do bảo trì vô thời hạn --}}
            <div id="unlimitedMaintenanceReason" class="mb-5 space-y-2">
                <label class="flex items-center gap-2 text-xs font-semibold text-gray-300">
                    <span class="flex h-5 w-5 items-center justify-center rounded-md bg-orange-500/20">
                        <i class="fa-solid fa-message-lines text-[9px] text-orange-400"></i>
                    </span>
                    Lý do bảo trì <span class="text-gray-500 font-normal">(Tùy chọn)</span>
                </label>
                <input type="text" id="maintenanceReasonUnlimited"
                    class="w-full rounded-xl border-2 border-white/10 bg-[#0a0a14] px-4 py-3 text-sm text-white outline-none transition-all placeholder:text-gray-600 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/15"
                    placeholder="VD: Thay đệm ghế, sửa cơ chế ngả...">
            </div>

            {{-- Actions --}}
            <div class="flex gap-2.5">
                <button type="button" id="maintenanceModalCancel" class="flex-1 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-medium text-gray-300 transition-all hover:bg-white/10 hover:text-white active:scale-[0.98]">
                    <i class="fa-solid fa-xmark mr-1.5"></i>Hủy
                </button>
                <button type="button" id="maintenanceModalApply" class="flex-1 rounded-xl bg-gradient-to-r from-orange-500 to-amber-500 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-orange-500/20 transition-all hover:from-orange-400 hover:to-amber-400 hover:shadow-orange-500/30 active:scale-[0.98]">
                    <i class="fa-solid fa-wrench mr-1.5"></i>Bảo trì
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL: THÊM GHẾ --}}
<div id="addSeatModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/75 backdrop-blur-[6px]">
    <div class="w-full max-w-md mx-4 rounded-2xl border border-white/10 bg-gradient-to-b from-[#1a1a2e] to-[#0f0f1a] p-0 shadow-2xl shadow-black/60 overflow-hidden animate-modal-in">
        {{-- Header --}}
        <div class="relative px-6 pt-6 pb-5 bg-gradient-to-r from-emerald-500/10 via-emerald-500/5 to-transparent border-b border-white/5">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 via-teal-400 to-emerald-500"></div>
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500/20 to-emerald-500/5 border border-emerald-500/20">
                        <i class="fa-solid fa-plus text-xl text-emerald-400"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white leading-tight">
                            Thêm ghế mới
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">Phòng {{ $phongChieu->ten_phong }}</p>
                    </div>
                </div>
                <button type="button" id="addSeatModalClose" onclick="closeAddSeatModalDirect()" class="text-gray-500 hover:text-white hover:bg-white/10 rounded-lg p-1.5 transition-all">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
        </div>

        {{-- Body --}}
        <div class="px-6 py-5">
            <div id="addSeatModalErrors" class="hidden mb-4 rounded-xl border border-red-500/30 bg-red-500/10 p-3 text-sm text-red-300"></div>

            <form id="addSeatForm" class="space-y-4">
                <div class="space-y-1.5">
                    <label class="flex items-center gap-1.5 text-xs font-medium text-gray-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        Hàng ghế <span class="text-red-400">*</span>
                    </label>
                    <div class="custom-select custom-select--green custom-select--simple" data-select-id="addSeatHangGhe">
                        <div class="custom-select__trigger" onclick="toggleCustomSelect(this)">
                            <div class="custom-select__value">
                                <span class="custom-select__text custom-select__text--placeholder">-- Chọn hàng --</span>
                            </div>
                            <div class="custom-select__arrow">
                                <i class="fa-solid fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                        <div class="custom-select__dropdown">
                            @foreach($phongChieu->hangGhes->sortBy('ten_hang') as $hang)
                                <div class="custom-select__option" data-value="{{ $hang->id }}" data-la-couple="{{ $hang->la_hang_couple ? 1 : 0 }}" onclick="selectCustomOption(this)">
                                    <div class="custom-select__option-content">
                                        <div class="custom-select__option-label">{{ $hang->ten_hang }}</div>
                                        <div class="custom-select__option-meta">{{ $hang->la_hang_couple ? 'Hàng Couple' : 'Hàng thường' }}</div>
                                    </div>
                                    <div class="custom-select__option-check">
                                        <i class="fa-solid fa-check text-white text-[10px]"></i>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <select name="hang_ghe_id" id="addSeatHangGhe" class="hidden" required>
                        <option value="">-- Chọn hàng --</option>
                        @foreach($phongChieu->hangGhes->sortBy('ten_hang') as $hang)
                            <option value="{{ $hang->id }}" data-la-couple="{{ $hang->la_hang_couple ? 1 : 0 }}">{{ $hang->ten_hang }}{{ $hang->la_hang_couple ? ' (Couple)' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label class="flex items-center gap-1.5 text-xs font-medium text-gray-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                            Mã ghế <span class="text-red-400">*</span>
                        </label>
                        <input type="text" name="ma_ghe" id="addSeatMaGhe" maxlength="10" required
                            placeholder="VD: A1, B2"
                            class="w-full rounded-xl border border-white/10 bg-[#0a0a14] px-4 py-2.5 text-sm text-white outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                    </div>
                    <div class="space-y-1.5">
                        <label class="flex items-center gap-1.5 text-xs font-medium text-gray-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                            Cột <span class="text-red-400">*</span>
                        </label>
                        <input type="number" name="cot" id="addSeatCot" min="1" required
                            class="w-full rounded-xl border border-white/10 bg-[#0a0a14] px-4 py-2.5 text-sm text-white outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="flex items-center gap-1.5 text-xs font-medium text-gray-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        Loại ghế <span class="text-red-400">*</span>
                    </label>
                    <div class="custom-select custom-select--green" data-select-id="addSeatLoaiGhe">
                        <div class="custom-select__trigger" onclick="toggleCustomSelect(this)">
                            <div class="custom-select__value">
                                <div class="custom-select__color-dot" style="background-color: #666"></div>
                                <span class="custom-select__text custom-select__text--placeholder">-- Chọn loại --</span>
                            </div>
                            <div class="custom-select__arrow">
                                <i class="fa-solid fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                        <div class="custom-select__dropdown">
                            @foreach(\App\Models\LoaiGhe::orderBy('ten_loai')->get() as $loai)
                                <div class="custom-select__option" data-value="{{ $loai->id }}" data-color="{{ $loai->mau_sac ?? '#666' }}" data-la-couple="{{ $loai->la_couple ? '1' : '0' }}" onclick="selectCustomOption(this)">
                                    <div class="custom-select__option-color" style="background-color: {{ $loai->mau_sac ?? '#666' }}"></div>
                                    <div class="custom-select__option-content">
                                        <div class="custom-select__option-label">{{ $loai->ten_loai }}</div>
                                        <div class="custom-select__option-meta">+{{ number_format($loai->phu_thu) }}đ</div>
                                    </div>
                                    <div class="custom-select__option-check">
                                        <i class="fa-solid fa-check text-white text-[10px]"></i>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <select name="loai_ghe_id" id="addSeatLoaiGhe" class="hidden" required>
                        <option value="">-- Chọn loại --</option>
                        @foreach(\App\Models\LoaiGhe::orderBy('ten_loai')->get() as $loai)
                            <option value="{{ $loai->id }}">{{ $loai->ten_loai }} (+{{ number_format($loai->phu_thu) }}đ)</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="flex items-center gap-1.5 text-xs font-medium text-gray-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        Trạng thái <span class="text-red-400">*</span>
                    </label>
                    <div class="custom-select custom-select--green custom-select--simple" data-select-id="addSeatTrangThai">
                        <div class="custom-select__trigger" onclick="toggleCustomSelect(this)">
                            <div class="custom-select__value">
                                <span class="custom-select__text custom-select__text--placeholder">-- Chọn trạng thái --</span>
                            </div>
                            <div class="custom-select__arrow">
                                <i class="fa-solid fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                        <div class="custom-select__dropdown">
                            <div class="custom-select__option" data-value="hoat_dong" onclick="selectCustomOption(this)">
                                <div class="custom-select__option-color" style="background-color: #10b981"></div>
                                <div class="custom-select__option-content">
                                    <div class="custom-select__option-label">Hoạt động</div>
                                </div>
                                <div class="custom-select__option-check">
                                    <i class="fa-solid fa-check text-white text-[10px]"></i>
                                </div>
                            </div>
                            <div class="custom-select__option" data-value="bao_tri" onclick="selectCustomOption(this)">
                                <div class="custom-select__option-color" style="background-color: #f59e0b"></div>
                                <div class="custom-select__option-content">
                                    <div class="custom-select__option-label">Bảo trì</div>
                                </div>
                                <div class="custom-select__option-check">
                                    <i class="fa-solid fa-check text-white text-[10px]"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <select name="trang_thai" id="addSeatTrangThai" class="hidden" required>
                        class="w-full appearance-none rounded-xl border border-white/10 bg-[#0a0a14] px-4 py-2.5 text-sm text-white outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all cursor-pointer">
                        <option value="hoat_dong">Hoạt động</option>
                        <option value="bao_tri">Bảo trì</option>
                    </select>
                </div>

                <div class="flex gap-2.5 pt-2">
                    <button type="button" id="addSeatCancel" onclick="closeAddSeatModalDirect()"
                        class="flex-1 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-medium text-gray-300 transition-all hover:bg-white/10 hover:text-white active:scale-[0.98]">
                        Hủy
                    </button>
                    <button type="submit" id="addSeatSubmit"
                        class="flex-1 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 px-4 py-3 text-sm font-bold text-black shadow-lg shadow-emerald-500/20 transition-all hover:from-emerald-400 hover:to-teal-300 hover:shadow-emerald-500/30 active:scale-[0.98]">
                        <i class="fa-solid fa-plus mr-1.5"></i>Thêm ghế
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL: THÊM HÀNG --}}
<div id="addRowModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/75 backdrop-blur-[6px]">
    <div class="w-full max-w-md mx-4 rounded-2xl border border-white/10 bg-gradient-to-b from-[#1a1a2e] to-[#0f0f1a] p-0 shadow-2xl shadow-black/60 overflow-hidden animate-modal-in">
        {{-- Header --}}
        <div class="relative px-6 pt-6 pb-5 bg-gradient-to-r from-[#d99a32]/10 via-[#d99a32]/5 to-transparent border-b border-white/5">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-[#d99a32] via-[#e5a847] to-[#d99a32]"></div>
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-[#d99a32]/20 to-[#d99a32]/5 border border-[#d99a32]/20">
                        <i class="fa-solid fa-layer-group text-xl text-[#d99a32]"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white leading-tight">
                            Thêm hàng ghế mới
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">Phòng {{ $phongChieu->ten_phong }}</p>
                    </div>
                </div>
                <button type="button" id="addRowModalClose" onclick="closeAddRowModalDirect()" class="text-gray-500 hover:text-white hover:bg-white/10 rounded-lg p-1.5 transition-all">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
        </div>

        {{-- Body --}}
        <div class="px-6 py-5">
            <div id="addRowModalErrors" class="hidden mb-4 rounded-xl border border-red-500/30 bg-red-500/10 p-3 text-sm text-red-300"></div>

            <form id="addRowForm" class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label class="flex items-center gap-1.5 text-xs font-medium text-gray-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-[#d99a32]"></span>
                            Tên hàng <span class="text-red-400">*</span>
                        </label>
                        <input type="text" name="ten_hang" id="addRowTenHang" maxlength="10" required
                            placeholder="VD: A, B, C..."
                            class="w-full rounded-xl border border-white/10 bg-[#0a0a14] px-4 py-2.5 text-sm text-white outline-none focus:border-[#d99a32] focus:ring-2 focus:ring-[#d99a32]/20 transition-all">
                    </div>
                    <div class="space-y-1.5">
                        <label class="flex items-center gap-1.5 text-xs font-medium text-gray-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-[#d99a32] opacity-50"></span>
                            Loại ghế mặc định
                        </label>
                        <div class="custom-select custom-select--simple" data-select-id="addRowLoaiMacDinh">
                            <div class="custom-select__trigger" onclick="toggleCustomSelect(this)">
                                <div class="custom-select__value">
                                    <span class="custom-select__text custom-select__text--placeholder">-- Không đặt --</span>
                                </div>
                                <div class="custom-select__arrow">
                                    <i class="fa-solid fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                            <div class="custom-select__dropdown">
                                @foreach(\App\Models\LoaiGhe::orderBy('ten_loai')->get() as $loai)
                                    <div class="custom-select__option" data-value="{{ $loai->id }}" data-color="{{ $loai->mau_sac ?? '#666' }}" data-la-couple="{{ $loai->la_couple ? '1' : '0' }}" onclick="selectCustomOption(this)">
                                        <div class="custom-select__option-color" style="background-color: {{ $loai->mau_sac ?? '#666' }}"></div>
                                        <div class="custom-select__option-content">
                                            <div class="custom-select__option-label">{{ $loai->ten_loai }}</div>
                                        </div>
                                        <div class="custom-select__option-check">
                                            <i class="fa-solid fa-check text-white text-[10px]"></i>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <select name="loai_ghe_mac_dinh_id" id="addRowLoaiMacDinh" class="hidden">
                            <option value="">-- Không đặt --</option>
                            @foreach(\App\Models\LoaiGhe::orderBy('ten_loai')->get() as $loai)
                                <option value="{{ $loai->id }}">{{ $loai->ten_loai }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-white/8 bg-gradient-to-r from-pink-500/5 to-pink-500/2 p-3.5 transition-all hover:from-pink-500/8 hover:to-pink-500/4">
                    <input type="checkbox" name="la_hang_couple" id="addRowIsCouple" value="1"
                        class="mt-0.5 h-4 w-4 rounded border-white/20 bg-[#0a0a14] text-pink-500 focus:ring-pink-500 accent-pink-500">
                    <div class="text-xs">
                        <div class="font-semibold text-white">Hàng ghép đôi (Couple)</div>
                        <div class="text-gray-500 mt-0.5">Hệ thống tự ghép 2 ghế liền kề thành 1 cặp</div>
                    </div>
                </label>

                <div class="rounded-xl border border-white/8 bg-white/[0.02] p-3.5 backdrop-blur-sm">
                    <label class="flex cursor-pointer items-center gap-3 text-xs text-gray-300">
                        <input type="checkbox" name="tu_dong_tao_ghe" id="addRowAuto" value="1" checked
                            class="h-4 w-4 rounded border-white/20 bg-[#0a0a14] text-[#d99a32] focus:ring-[#d99a32] accent-[#d99a32]">
                        <span>Tự động tạo ghế mẫu cho hàng</span>
                    </label>
                </div>

                <div id="addRowAutoBox" class="space-y-3 rounded-xl border border-white/8 bg-white/[0.02] p-4 backdrop-blur-sm">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label class="flex items-center gap-1.5 text-xs font-medium text-gray-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-[#d99a32]"></span>
                                Số ghế <span class="text-red-400">*</span>
                            </label>
                            <input type="number" name="so_ghe" id="addRowSoGhe" min="1" max="50" value="10"
                                class="w-full rounded-xl border border-white/10 bg-[#0a0a14] px-4 py-2.5 text-sm text-white outline-none focus:border-[#d99a32] focus:ring-2 focus:ring-[#d99a32]/20 transition-all">
                        </div>
                        <div class="space-y-1.5">
                            <label class="flex items-center gap-1.5 text-xs font-medium text-gray-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-[#d99a32] opacity-50"></span>
                                Cột bắt đầu
                            </label>
                            <input type="number" name="cot_bat_dau" id="addRowCotBatDau" min="1" value="1"
                                class="w-full rounded-xl border border-white/10 bg-[#0a0a14] px-4 py-2.5 text-sm text-white outline-none focus:border-[#d99a32] focus:ring-2 focus:ring-[#d99a32]/20 transition-all">
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="flex items-center gap-1.5 text-xs font-medium text-gray-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-[#d99a32]"></span>
                            Loại ghế <span class="text-red-400">*</span>
                        </label>
                        <div class="custom-select" data-select-id="addRowLoaiGhe">
                            <div class="custom-select__trigger" onclick="toggleCustomSelect(this)">
                                <div class="custom-select__value">
                                    <div class="custom-select__color-dot" style="background-color: #666"></div>
                                    <span class="custom-select__text custom-select__text--placeholder">-- Chọn loại --</span>
                                </div>
                                <div class="custom-select__arrow">
                                    <i class="fa-solid fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                            <div class="custom-select__dropdown">
                                @foreach(\App\Models\LoaiGhe::orderBy('ten_loai')->get() as $loai)
                                    <div class="custom-select__option" data-value="{{ $loai->id }}" data-color="{{ $loai->mau_sac ?? '#666' }}" data-la-couple="{{ $loai->la_couple ? '1' : '0' }}" onclick="selectCustomOption(this)">
                                        <div class="custom-select__option-color" style="background-color: {{ $loai->mau_sac ?? '#666' }}"></div>
                                        <div class="custom-select__option-content">
                                            <div class="custom-select__option-label">{{ $loai->ten_loai }}</div>
                                            <div class="custom-select__option-meta">+{{ number_format($loai->phu_thu) }}đ</div>
                                        </div>
                                        <div class="custom-select__option-check">
                                            <i class="fa-solid fa-check text-white text-[10px]"></i>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <select name="loai_ghe_id" id="addRowLoaiGhe" class="hidden">
                            <option value="">-- Chọn loại --</option>
                            @foreach(\App\Models\LoaiGhe::orderBy('ten_loai')->get() as $loai)
                                <option value="{{ $loai->id }}">{{ $loai->ten_loai }} (+{{ number_format($loai->phu_thu) }}đ)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="flex items-center gap-1.5 text-xs font-medium text-gray-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-[#d99a32] opacity-50"></span>
                            Trạng thái ghế
                        </label>
                        <div class="custom-select custom-select--simple" data-select-id="addRowTrangThai">
                            <div class="custom-select__trigger" onclick="toggleCustomSelect(this)">
                                <div class="custom-select__value">
                                    <span class="custom-select__text custom-select__text--placeholder">-- Chọn trạng thái --</span>
                                </div>
                                <div class="custom-select__arrow">
                                    <i class="fa-solid fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                            <div class="custom-select__dropdown">
                                <div class="custom-select__option" data-value="hoat_dong" onclick="selectCustomOption(this)">
                                    <div class="custom-select__option-color" style="background-color: #10b981"></div>
                                    <div class="custom-select__option-content">
                                        <div class="custom-select__option-label">Hoạt động</div>
                                    </div>
                                    <div class="custom-select__option-check">
                                        <i class="fa-solid fa-check text-white text-[10px]"></i>
                                    </div>
                                </div>
                                <div class="custom-select__option" data-value="bao_tri" onclick="selectCustomOption(this)">
                                    <div class="custom-select__option-color" style="background-color: #f59e0b"></div>
                                    <div class="custom-select__option-content">
                                        <div class="custom-select__option-label">Bảo trì</div>
                                    </div>
                                    <div class="custom-select__option-check">
                                        <i class="fa-solid fa-check text-white text-[10px]"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <select name="trang_thai" id="addRowTrangThai" class="hidden">
                            <option value="hoat_dong">Hoạt động</option>
                            <option value="bao_tri">Bảo trì</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2 rounded-lg bg-[#d99a32]/5 border border-[#d99a32]/10 px-3 py-2">
                        <i class="fa-solid fa-eye text-[#d99a32] text-xs"></i>
                        <span class="text-xs text-gray-500">Mã ghế: <span class="text-[#d99a32] font-bold" id="addRowPreview">A1, A2, ...</span></span>
                    </div>
                </div>

                <div class="flex gap-2.5 pt-1">
                    <button type="button" id="addRowCancel" onclick="closeAddRowModalDirect()"
                        class="flex-1 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-medium text-gray-300 transition-all hover:bg-white/10 hover:text-white active:scale-[0.98]">
                        Hủy
                    </button>
                    <button type="submit" id="addRowSubmit"
                        class="flex-1 rounded-xl bg-gradient-to-r from-[#d99a32] to-[#e5a847] px-4 py-3 text-sm font-bold text-black shadow-lg shadow-[#d99a32]/20 transition-all hover:from-[#e5a847] hover:to-[#d99a32] hover:shadow-[#d99a32]/30 active:scale-[0.98]">
                        <i class="fa-solid fa-plus mr-1.5"></i>Thêm hàng
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- HOVER TOOLTIP (giống trang chọn ghế user) --}}
<div id="seatHoverTooltip" class="seat-hover-tooltip">
    <div class="tt-title">
        <i class="fa-solid fa-couch"></i>
        <span id="hoverMaGhe">--</span>
    </div>
    <div class="tt-sub" id="hoverLoaiGhe">--</div>
</div>

{{-- SEAT INFO POPOVER --}}
<div id="seatInfoPopover" class="seat-info-popover">
    {{-- Header: strip màu loại ghế + meta --}}
    <div class="seat-info-popover__strip"></div>
    <div class="seat-info-popover__head">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-chair text-[11px] text-[#d99a32]"></i>
            <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-gray-400">Chi tiết ghế</span>
        </div>
        <span id="popoverHeaderMaGhe" class="rounded-md border border-white/10 bg-white/5 px-2 py-0.5 text-[11px] font-black text-white"></span>
    </div>

    {{-- Hero: mã ghế lớn + loại --}}
    <div class="seat-info-popover__hero">
        <div id="popoverColorBlock" class="seat-info-popover__color-block">
            <div class="seat-info-popover__color-content">
                <div id="popoverMaGhe" class="seat-info-popover__ma-ghe"></div>
                <div id="popoverLoaiGhe" class="seat-info-popover__loai-ghe"></div>
            </div>
        </div>
    </div>

    {{-- Thông tin: Phụ thu + Trạng thái (chia 2 dòng đơn giản) --}}
    <div class="seat-info-popover__meta">
        <div class="seat-info-popover__meta-row">
            <div class="flex items-center gap-2 text-gray-400">
                <i class="fa-solid fa-tag text-[10px]"></i>
                <span class="text-[10px] font-semibold uppercase tracking-wider">Phụ thu</span>
            </div>
            <div id="popoverPhuThu" class="text-[13px] font-black text-white"></div>
        </div>
        <div class="seat-info-popover__status" id="popoverStatus"></div>
        <div class="seat-info-popover__maintenance-info hidden mt-2 pt-2 border-t border-white/10" id="popoverMaintenanceInfo">
            <div class="flex items-start gap-2 text-gray-400">
                <i class="fa-solid fa-clock text-[10px] mt-0.5"></i>
                <div class="text-[10px] leading-relaxed">
                    <div id="popoverMaintenanceTime" class="font-semibold text-orange-300"></div>
                    <div id="popoverMaintenanceReason" class="text-gray-500 mt-0.5"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- (Hành động đã được tích hợp trên thanh bulk action) --}}
</div>

{{-- MODAL XÁC NHẬN HỦY BẢO TRÌ --}}
<div id="modalHuyBaoTri" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" onclick="closeModalHuyBaoTri()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div class="pointer-events-auto w-full max-w-md rounded-3xl border border-white/10 bg-gradient-to-b from-[#1a1a1a] to-[#0f0f0f] p-6 shadow-2xl">
            <div class="text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-500/20">
                    <i class="fa-solid fa-triangle-exclamation text-2xl text-red-400"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-white">Xác nhận hủy bảo trì</h3>
                <p class="mb-1 text-sm text-gray-400">Bạn có chắc muốn hủy lịch bảo trì cho ghế</p>
                <p class="mb-5 text-lg font-bold text-red-400"><span id="modalHuyBaoTriGhe"></span>?</p>
                <p class="mb-6 text-xs text-gray-500">Hành động này không thể hoàn tác.</p>
                <div class="flex gap-3">
                    <button type="button" onclick="closeModalHuyBaoTri()"
                        class="flex-1 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-medium text-white transition hover:bg-white/10">
                        Không, giữ lại
                    </button>
                    <button type="submit" form="formHuyBaoTri"
                        class="flex-1 rounded-xl bg-gradient-to-r from-red-600 to-red-500 px-4 py-3 text-sm font-bold text-white transition hover:scale-[1.02]">
                        Có, hủy bảo trì
                    </button>
                </div>
                <form id="formHuyBaoTri" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openModalHuyBaoTri(url, maGhe) {
    document.getElementById('modalHuyBaoTriGhe').textContent = maGhe;
    document.getElementById('formHuyBaoTri').action = url;
    document.getElementById('modalHuyBaoTri').classList.remove('hidden');
    document.getElementById('modalHuyBaoTri').querySelector('.backdrop-blur-sm').style.opacity = '1';
}

function closeModalHuyBaoTri() {
    document.getElementById('modalHuyBaoTri').classList.add('hidden');
}
</script>

@endsection

@php /* STYLES */ @endphp
<style>
    /* ==================== SEAT MAP STYLES ==================== */

    /* Screen Glow */
    .screen-glow {
        position: relative;
    }
    .screen-glow::after {
        content: '';
        position: absolute;
        bottom: -20px;
        left: 50%;
        transform: translateX(-50%);
        width: 80%;
        height: 40px;
        background: radial-gradient(ellipse at center, rgba(217, 154, 50, 0.25) 0%, transparent 70%);
        pointer-events: none;
        filter: blur(8px);
    }

    /* ==================== CUSTOM SELECT DROPDOWN ==================== */
    .custom-select {
        position: relative;
        width: 100%;
    }

    .custom-select__trigger {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding: 10px 14px;
        background: #0f0f1a;
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .custom-select__trigger:hover {
        border-color: rgba(255, 255, 255, 0.25);
        background: #141425;
    }

    .custom-select__trigger:focus,
    .custom-select__trigger.active {
        outline: none;
        border-color: #d99a32;
        box-shadow: 0 0 0 3px rgba(217, 154, 50, 0.2), 0 0 20px rgba(217, 154, 50, 0.15);
    }

    .custom-select__value {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .custom-select__color-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, 0.2);
        flex-shrink: 0;
        transition: all 0.2s ease;
    }

    .custom-select__text {
        color: #aaa;
        font-size: 14px;
        font-weight: 500;
    }

    .custom-select__text--placeholder {
        color: #666;
    }

    .custom-select__arrow {
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(255, 255, 255, 0.5);
        transition: all 0.3s ease;
    }

    .custom-select__trigger.active .custom-select__arrow {
        transform: rotate(180deg);
        color: #d99a32;
    }

    .custom-select__dropdown {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        right: 0;
        background: #1a1a2e;
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 12px;
        padding: 8px;
        z-index: 9999;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
        max-height: 280px;
        overflow-y: auto;
        display: none !important;
    }

    .custom-select__dropdown::-webkit-scrollbar {
        width: 6px;
    }

    .custom-select__dropdown::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 3px;
    }

    .custom-select__dropdown::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 3px;
    }

    .custom-select__dropdown::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .custom-select__trigger.active + .custom-select__dropdown {
        display: block !important;
    }

    /* === NATIVE SELECT STYLING (Tạo ghế tự động form) === */
    .modal-content select[name="loai_ghe_thuong_id"],
    .modal-content select[name="loai_ghe_vip_id"],
    .modal-content select[name="loai_ghe_couple_id"] {
        width: 100%;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.15);
        background: #1a1a2e !important;
        color: #fff;
        padding: 10px 14px;
        font-size: 14px;
        outline: none;
        cursor: pointer;
        transition: all 0.3s ease;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23888' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: right 12px center !important;
        padding-right: 36px !important;
    }

    .modal-content select[name="loai_ghe_thuong_id"]:hover,
    .modal-content select[name="loai_ghe_vip_id"]:hover,
    .modal-content select[name="loai_ghe_couple_id"]:hover {
        border-color: rgba(255, 255, 255, 0.25);
        background-color: #141425 !important;
    }

    .modal-content select[name="loai_ghe_thuong_id"]:focus,
    .modal-content select[name="loai_ghe_vip_id"]:focus,
    .modal-content select[name="loai_ghe_couple_id"]:focus {
        border-color: #d99a32;
        box-shadow: 0 0 0 3px rgba(217, 154, 50, 0.2);
    }

    .modal-content select option {
        background: #1a1a2e !important;
        color: #fff;
        padding: 10px;
    }

    .modal-content select option:hover,
    .modal-content select option:focus {
        background: rgba(217, 154, 50, 0.3) !important;
    }

    /* === ALL SELECTS IN MODALS === */
    .modal-content select {
        width: 100%;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.15) !important;
        background: #1a1a2e !important;
        color: #fff !important;
        padding: 10px 36px 10px 14px !important;
        font-size: 14px !important;
        outline: none !important;
        cursor: pointer;
        transition: all 0.3s ease;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        appearance: none !important;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23888' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: right 12px center !important;
    }

    .modal-content select:hover {
        border-color: rgba(255, 255, 255, 0.25) !important;
        background-color: #141425 !important;
    }

    .modal-content select:focus {
        border-color: #d99a32 !important;
        box-shadow: 0 0 0 3px rgba(217, 154, 50, 0.2) !important;
    }

    /* Fix select arrow in Firefox */
    @-moz-document url-prefix() {
        .modal-content select {
            padding-right: 36px !important;
        }
    }

    /* Fix select arrow in Firefox */
    @-moz-document url-prefix() {
        .modal-content select {
            padding-right: 36px !important;
        }
    }

    .custom-select__option {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .custom-select__option:hover {
        background: rgba(217, 154, 50, 0.15);
    }

    .custom-select__option.selected {
        background: rgba(217, 154, 50, 0.25);
    }

    .custom-select__option-color {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, 0.2);
        flex-shrink: 0;
    }

    .custom-select__option-content {
        flex: 1;
    }

    .custom-select__option-label {
        color: #fff;
        font-size: 14px;
        font-weight: 500;
    }

    .custom-select__option-meta {
        color: #888;
        font-size: 12px;
        margin-top: 2px;
    }

    .custom-select__option-check {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #d99a32;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transform: scale(0);
        transition: all 0.2s ease;
    }

    .custom-select__option.selected .custom-select__option-check {
        opacity: 1;
        transform: scale(1);
    }

    /* Simple select without color dot */
    .custom-select--simple .custom-select__color-dot {
        display: none;
    }

    /* Green theme variant */
    .custom-select--green .custom-select__trigger:focus,
    .custom-select--green .custom-select__trigger.active {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2), 0 0 20px rgba(16, 185, 129, 0.15);
    }

    .custom-select--green .custom-select__option:hover {
        background: rgba(16, 185, 129, 0.15);
    }

    .custom-select--green .custom-select__option.selected {
        background: rgba(16, 185, 129, 0.25);
    }

    .custom-select--green .custom-select__option-check {
        background: #10b981;
    }

    .custom-select--green .custom-select__trigger.active .custom-select__arrow {
        color: #10b981;
    }

    /* Seat Row Layout */
    .seat-row {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
    }
    .seat-row__label {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-width: 52px;
    }
    .seat-row__letter {
        font-size: 16px;
        font-weight: 900;
        color: #d99a32;
        letter-spacing: 0.5px;
        transition: all 0.2s ease;
    }
    .seat-row__label--clickable:hover .seat-row__letter {
        color: #f4c56a;
        transform: scale(1.15);
    }
    .seat-row__seats {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        overflow: visible;
    }
    .seat-row__seats--header {
        gap: 8px;
    }
    .seat-col-num {
        width: 36px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
        color: #555;
        letter-spacing: 0.5px;
    }

    /* Seat Chip - Hình ghế giống ảnh mẫu */
    /* Seat Chip - Hình ghế giống ảnh mẫu - DÙNG !important ĐỂ KHÔNG BỊ ĐÈ */
    .seat-chip {
        position: relative !important;
        width: 48px !important;
        height: 52px !important;
        display: flex !important;
        align-items: flex-end !important;
        justify-content: center !important;
        padding: 0 0 6px 0 !important;
        border-radius: 14px 14px 6px 6px !important;
        font-size: 14px !important;
        font-weight: 900 !important;
        color: #1a0b04 !important;
        letter-spacing: 0.3px !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow:
            0 3px 0 rgba(0, 0, 0, 0.4),
            0 4px 10px rgba(0, 0, 0, 0.3),
            inset 0 2px 0 rgba(255, 255, 255, 0.6),
            inset 0 -3px 4px rgba(0, 0, 0, 0.2) !important;
        flex-shrink: 0 !important;
        border: 1.5px solid rgba(0, 0, 0, 0.3) !important;
        background-clip: padding-box !important;
    }
    /* Phần tựa lưng (phía trên ghế) */
    .seat-chip::before {
        content: "";
        position: absolute;
        top: 0;
        left: 6px;
        right: 6px;
        height: 18px;
        background: rgba(255, 255, 255, 0.45);
        border-radius: 10px 10px 4px 4px;
        z-index: 1;
        pointer-events: none;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }
    /* Phần đế ghế (phía dưới) - tạo khối */
    .seat-chip::after {
        content: "";
        position: absolute;
        bottom: 2px;
        left: 4px;
        right: 4px;
        height: 4px;
        background: rgba(0, 0, 0, 0.25);
        border-radius: 3px;
        z-index: 1;
        pointer-events: none;
    }
    /* Label ghế - chữ đen đậm với viền sáng */
    .seat-chip .seat-label,
    .seat-chip > span:not(.seat-couple-sep) {
        position: relative;
        z-index: 3;
        font-weight: 900;
        color: #1a0b04;
        text-shadow:
            0 1px 0 rgba(255, 255, 255, 0.7),
            0 -1px 0 rgba(0, 0, 0, 0.2),
            1px 0 0 rgba(255, 255, 255, 0.5),
            -1px 0 0 rgba(255, 255, 255, 0.5);
    }
    /* Ghế couple - label 2 bên */
    .seat-chip .seat-couple-left,
    .seat-chip .seat-couple-right {
        position: relative;
        z-index: 3;
        font-weight: 900;
        color: #1a0b04;
        text-shadow:
            0 1px 0 rgba(255, 255, 255, 0.7),
            0 -1px 0 rgba(0, 0, 0, 0.2);
    }
    .seat-chip .seat-couple-sep {
        position: relative;
        z-index: 3;
        color: rgba(0, 0, 0, 0.4);
        font-weight: 900;
        margin: 0 4px;
    }
    /* Ghế bảo trì */
    .seat-chip.seat-chip--maintenance .seat-label,
    .seat-chip.seat-chip--maintenance > span {
        color: #991b1b !important;
        text-shadow:
            0 1px 0 rgba(255, 255, 255, 0.6),
            0 -1px 0 rgba(0, 0, 0, 0.15);
    }
    .seat-chip--empty {
        width: 48px;
        height: 50px;
        visibility: hidden;
        pointer-events: none;
    }
    .seat-chip--maintenance {
        background: rgba(239, 68, 68, 0.4) !important;
        border: 2px solid #dc2626;
        color: #991b1b;
        font-weight: 900;
        font-size: 11px;
    }
    .seat-chip--maintenance::before {
        background: rgba(255, 255, 255, 0.3) !important;
    }
    /* Ghế sắp bảo trì */
    .seat-chip.seat-chip--pending .seat-label,
    .seat-chip.seat-chip--pending > span {
        color: #92400e !important;
        text-shadow: 0 1px 0 rgba(255, 255, 255, 0.6);
    }
    .seat-chip--pending {
        background: #fef3c7 !important;
        border: 2px solid #f59e0b;
        color: #92400e;
        font-weight: 700;
        font-size: 11px;
    }
    .seat-chip--pending::before {
        background: rgba(255, 255, 255, 0.5) !important;
    }
    /* CSS cũ .seat-chip--couple dòng 785-794 đã xóa - dùng rule mới ở phía dưới */

    /* Alpine.js cloak */
    [x-cloak] { display: none !important; }

    /* Seat Interaction */
    .seat-interactive {
        cursor: pointer;
        user-select: none;
    }
    .seat-interactive:hover {
        transform: scale(1.1) translateY(-3px);
        z-index: 10;
        box-shadow:
            0 6px 0 rgba(0, 0, 0, 0.4),
            0 8px 16px rgba(0, 0, 0, 0.4),
            0 0 14px rgba(244, 197, 106, 0.6),
            inset 0 2px 0 rgba(255, 255, 255, 0.7),
            inset 0 -3px 4px rgba(0, 0, 0, 0.2);
        filter: brightness(1.2) saturate(1.15);
        border-color: #f4c56a !important;
    }
    .seat-interactive:active {
        transform: translateY(0) scale(0.96);
        transition: transform 0.1s ease;
    }
    /* ==== GHẾ ĐANG ĐƯỢC CHỌN ==== */
    /* Hiệu ứng được apply bằng inline style qua syncSeatCheckMark() trong JS
       để đảm bảo không bị CSS rule nào ghi đè. Chỉ giữ @keyframes ở đây. */

    /* Modal animation */
    @keyframes modalSlideIn {
        0% { opacity: 0; transform: scale(0.95) translateY(10px); }
        100% { opacity: 1; transform: scale(1) translateY(0); }
    }
    .animate-modal-in {
        animation: modalSlideIn 0.25s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
    }

    @keyframes seatPulse {
        0%, 100% {
            box-shadow:
                0 0 0 2px rgba(244, 197, 106, 0.95),
                0 0 0 4px rgba(217, 154, 50, 0.5),
                0 0 18px 4px rgba(244, 197, 106, 0.65),
                0 0 35px 8px rgba(217, 154, 50, 0.35),
                0 4px 12px rgba(0, 0, 0, 0.4);
        }
        50% {
            box-shadow:
                0 0 0 2px rgba(244, 197, 106, 1),
                0 0 0 6px rgba(217, 154, 50, 0.7),
                0 0 25px 6px rgba(244, 197, 106, 0.85),
                0 0 45px 12px rgba(217, 154, 50, 0.5),
                0 4px 14px rgba(0, 0, 0, 0.45);
        }
    }

    /* Dấu check ✓ - render bằng JS (class .seat-check-mark) để không xung đột với ::after của couple */
    @keyframes checkAppear {
        0% { transform: scale(0) rotate(-180deg); opacity: 0; }
        60% { transform: scale(1.3) rotate(20deg); opacity: 1; }
        100% { transform: scale(1) rotate(0); opacity: 1; }
    }
    .seat-check-mark {
        position: absolute !important;
        top: 4px !important;
        right: 4px !important;
        width: 18px !important;
        height: 18px !important;
        border-radius: 50% !important;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        color: #ffffff !important;
        font-size: 11px !important;
        font-weight: 900 !important;
        line-height: 18px !important;
        text-align: center !important;
        border: 2px solid #ffffff !important;
        box-shadow:
            0 0 10px rgba(16, 185, 129, 0.95),
            0 2px 4px rgba(0, 0, 0, 0.6) !important;
        z-index: 9999 !important;
        pointer-events: none !important;
        animation: checkAppear 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) forwards !important;
    }
    .seat-check-mark::before {
        content: "✓" !important;
    }

    /* Couple: 1 div to duy nhất chứa 2 nhãn ghế (H1 | H2) */
    .seat-chip--couple {
        width: 105px !important;
        height: 50px     !important;
        gap: 0;
        padding: 0 6px !important;
        overflow: visible !important;
    }
    /* Vạch dashed ở giữa ghế couple */
    .seat-chip--couple::after {
        content: '';
        position: absolute;
        top: 18%;
        bottom: 10px;
        left: 50%;
        width: 0;
        border-left: 2px dashed rgba(0, 0, 0, 0.45);
        transform: translateX(-50%);
        pointer-events: none;
        z-index: 2;
    }
    .seat-chip--couple .seat-couple-left,
    .seat-chip--couple .seat-couple-right {
        flex: 1;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        min-width: 0;
        z-index: 5;
        position: relative;
        font-size: 12px !important;
    }
    .seat-chip--couple .seat-couple-sep {
        display: flex;
        align-items: flex-end;
        justify-content: center;
        color: rgba(0, 0, 0, 0.4);
        font-weight: 900;
        font-size: 13px;
        padding-bottom: 1px;
        z-index: 5;
        position: relative;
        margin: 0 2px !important;
    }

    /* ==================== POPOVER STYLES ==================== */
    .seat-info-popover {
        position: fixed;
        z-index: 9999;
        width: 280px;
        border-radius: 16px;
        background: #0f0f0f;
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.7);
        color: #ffffff;
        padding: 0;
        overflow: hidden;
        opacity: 0;
        transform: translateY(6px);
        pointer-events: none;
        transition: opacity .18s ease, transform .18s ease;
    }
    .seat-info-popover.is-visible {
        opacity: 1;
        transform: translateY(0);
        pointer-events: auto;
    }

    /* Strip màu loại ghế — chỉ 3px trên cùng, không chiếm diện tích */
    .seat-info-popover__strip {
        height: 3px;
        background: var(--popover-color, #666);
    }

    /* Head: label + mã ghế nhỏ */
    .seat-info-popover__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 14px 8px;
    }

    /* Hero: mã ghế + loại trong khối màu lớn */
    .seat-info-popover__hero {
        padding: 0 14px;
    }
    .seat-info-popover__color-block {
        position: relative;
        height: 86px;
        border-radius: 12px;
        background: var(--popover-color, #666);
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .seat-info-popover__color-content {
        text-align: center;
        color: #fff;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
    }
    .seat-info-popover__ma-ghe {
        font-size: 26px;
        font-weight: 800;
        letter-spacing: 1.5px;
        line-height: 1;
        margin-bottom: 4px;
    }
    .seat-info-popover__loai-ghe {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        opacity: 0.9;
    }

    /* Meta: Phụ thu + Status — đơn giản, không box riêng */
    .seat-info-popover__meta {
        padding: 12px 14px 4px;
    }
    .seat-info-popover__meta-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 6px 0;
    }
    .seat-info-popover__status {
        margin-top: 10px;
        text-align: center;
    }

    /* Thông tin bên dưới: phụ thu + trạng thái (compact, không còn actions) */
    .seat-info-popover__meta {
        padding: 12px 14px 14px;
    }

    .seat-info-popover__type-selector {
        margin: 0 16px 16px;
        padding: 14px 0 0;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
    }

    /* ==================== HOVER TOOLTIP (giống trang chọn ghế của user) ==================== */
    .seat-hover-tooltip {
        position: fixed;
        z-index: 9998;
        background: #1a1a1a;
        border: 1px solid rgba(217, 154, 50, .55);
        color: #fff;
        padding: 8px 12px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.4;
        white-space: nowrap;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .55), 0 0 0 4px rgba(217, 154, 50, .08);
        pointer-events: none;
        opacity: 0;
        transform: translateY(4px);
        transition: opacity .12s ease, transform .12s ease;
    }
    .seat-hover-tooltip.is-visible {
        opacity: 1;
        transform: translateY(0);
    }
    .seat-hover-tooltip .tt-title {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #f4c56a;
        font-weight: 900;
        font-size: 13px;
    }
    .seat-hover-tooltip .tt-sub {
        margin-top: 2px;
        color: #b8b8b8;
        font-size: 11px;
    }

    /* Bulk toolbar */
    .seat-color-dot {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 4px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        margin-right: 8px;
        vertical-align: middle;
        flex-shrink: 0;
    }

    /* Seat type pills */
    .seat-type-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 12px;
        color: #9ca3af;
    }

    .seat-type-pill:hover {
        background: rgba(217, 154, 50, 0.1);
        border-color: rgba(217, 154, 50, 0.3);
        color: #e5e7eb;
    }

    .seat-type-pill.active {
        background: rgba(217, 154, 50, 0.15);
        border-color: #d99a32;
        color: #f4c56a;
        box-shadow: 0 0 12px rgba(217, 154, 50, 0.2);
    }

    .seat-type-pill.opt-none {
        opacity: 0.6;
    }

    .seat-type-pill.opt-none.active {
        background: rgba(100, 100, 100, 0.15);
        border-color: #888;
        color: #aaa;
        box-shadow: none;
    }

    .pill-color {
        width: 12px;
        height: 12px;
        border-radius: 4px;
        flex-shrink: 0;
    }

    .pill-text {
        font-weight: 500;
    }

    .pill-price {
        font-size: 10px;
        color: #d99a32;
        opacity: 0.8;
    }

    .seat-type-pill.active .pill-price {
        opacity: 1;
    }
</style>
@php /* STYLES END */ @endphp

<script>
/* Show phong chieu - last updated: {{ date('Y-m-d H:i:s') }} */
// Global fallback CSRF helper - định nghĩa ngoài DOMContentLoaded để form submit handler (line ~5173) và mọi nơi đều truy cập được
window.getCsrfToken = function() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    if (meta && meta.getAttribute('content')) {
        return meta.getAttribute('content');
    }
    var match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);
    if (match) {
        return decodeURIComponent(match[1]);
    }
    return '';
};

/**
 * Hiển thị toast notification đẹp (theo style Toast component của dự án).
 * Loại: 'error' | 'success' | 'warning' | 'info'
 */
window.showToast = function(message, type = 'error') {
    var variant = {
        error:   { bg: 'bg-red-500/15', border: 'border-red-500/40', icon: 'fa-circle-exclamation', iconColor: 'text-red-300', text: 'text-red-100' },
        success: { bg: 'bg-emerald-500/15', border: 'border-emerald-500/40', icon: 'fa-circle-check', iconColor: 'text-emerald-300', text: 'text-emerald-100' },
        warning: { bg: 'bg-amber-500/15', border: 'border-amber-500/40', icon: 'fa-triangle-exclamation', iconColor: 'text-amber-300', text: 'text-amber-100' },
        info:    { bg: 'bg-white/10', border: 'border-white/20', icon: 'fa-circle-info', iconColor: 'text-[#d99a32]', text: 'text-white' },
    };
    var v = variant[type] || variant.info;

    var toast = document.createElement('div');
    toast.setAttribute('data-admin-toast', '');
    toast.className = 'admin-toast';
    toast.innerHTML =
        '<div class="flex items-start gap-3 rounded-2xl border ' + v.border + ' ' + v.bg + ' bg-[#101010]/95 px-4 py-3 shadow-2xl shadow-black/40 backdrop-blur-xl">' +
            '<div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/10">' +
                '<i class="fa-solid ' + v.icon + ' ' + v.iconColor + ' text-lg"></i>' +
            '</div>' +
            '<p class="' + v.text + ' min-w-0 flex-1 break-words pt-1 text-sm font-bold leading-snug">' + message + '</p>' +
            '<button type="button" data-toast-close class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/10 text-white/70 transition hover:bg-white/20 hover:text-white">' +
                '<i class="fa-solid fa-xmark text-sm"></i>' +
            '</button>' +
        '</div>';

    var container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'fixed top-4 right-4 z-[99999] flex flex-col gap-2 pointer-events-none';
        document.body.appendChild(container);
    }
    container.appendChild(toast);

    // Animate in
    requestAnimationFrame(function() {
        toast.style.animation = 'adminToastIn 0.28s ease both';
    });

    function dismiss() {
        toast.classList.add('is-hiding');
        setTimeout(function() {
            if (toast.parentElement) toast.remove();
        }, 300);
    }

    toast.querySelector('[data-toast-close]').addEventListener('click', dismiss);
    setTimeout(dismiss, 4000);
};

// Global functions for modal buttons
window.openAddSeatModalDirect = function() {
    var modal = document.getElementById('addSeatModal');
    var form = document.getElementById('addSeatForm');
    var errors = document.getElementById('addSeatModalErrors');
    if (!modal) return;
    if (form) form.reset();
    if (errors) {
        errors.classList.add('hidden');
        errors.innerHTML = '';
    }
    // Auto-suggest cột kế tiếp
    var hangSelect = document.getElementById('addSeatHangGhe');
    if (hangSelect && hangSelect.value) {
        hangSelect.dispatchEvent(new Event('change'));
    }
    modal.classList.remove('hidden');
};

window.closeAddSeatModalDirect = function() {
    var modal = document.getElementById('addSeatModal');
    if (modal) modal.classList.add('hidden');
};

window.openAddRowModalDirect = function() {
    var modal = document.getElementById('addRowModal');
    var form = document.getElementById('addRowForm');
    var errors = document.getElementById('addRowModalErrors');
    if (!modal) return;
    if (form) form.reset();
    if (errors) {
        errors.classList.add('hidden');
        errors.innerHTML = '';
    }
    // Update preview
    if (typeof updateAddRowPreviewDirect === 'function') {
        updateAddRowPreviewDirect();
    }
    modal.classList.remove('hidden');
};

window.closeAddRowModalDirect = function() {
    var modal = document.getElementById('addRowModal');
    if (modal) modal.classList.add('hidden');
};

// === RESET SEATS FUNCTION ===
window.confirmResetSeats = function() {
    if (confirm('Bạn có chắc muốn xóa toàn bộ ghế trong phòng này?\n\nHành động này không thể hoàn tác!')) {
        // Tạo form ẩn để submit DELETE request
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('admin.phong-chieus.generate-seats', $phongChieu) }}';

        var csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        form.appendChild(csrfInput);

        var methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);

        // Thêm input để trigger xóa toàn bộ
        var resetInput = document.createElement('input');
        resetInput.type = 'hidden';
        resetInput.name = 'reset_all';
        resetInput.value = '1';
        form.appendChild(resetInput);

        document.body.appendChild(form);
        form.submit();
    }
};

window.updateAddRowPreviewDirect = function() {
    var ten = (document.getElementById('addRowTenHang')?.value || 'A').trim();
    var so = parseInt(document.getElementById('addRowSoGhe')?.value, 10) || 0;
    var start = parseInt(document.getElementById('addRowCotBatDau')?.value, 10) || 1;
    var preview = document.getElementById('addRowPreview');
    if (!preview) return;
    if (so <= 0) { preview.textContent = ten + '1, ' + ten + '2, ...'; return; }
    if (so <= 3) {
        var codes = [];
        for (var i = 0; i < so; i++) codes.push(ten + (start + i));
        preview.textContent = codes.join(', ');
    } else {
        preview.textContent = ten + start + ', ' + ten + (start + 1) + ', ... ' + ten + (start + so - 1);
    }
};

// === CUSTOM SELECT DROPDOWN (GLOBAL FUNCTIONS) ===
function toggleCustomSelect(trigger) {
    // Prevent default and stop propagation
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    const wrapper = trigger.closest('.custom-select');
    const dropdown = wrapper.querySelector('.custom-select__dropdown');
    const isActive = trigger.classList.contains('active');

    // Close all dropdowns first
    document.querySelectorAll('.custom-select__trigger.active').forEach(t => {
        t.classList.remove('active');
    });
    document.querySelectorAll('.custom-select__dropdown').forEach(d => {
        d.style.display = 'none';
    });

    // Toggle current one
    if (!isActive) {
        trigger.classList.add('active');
        dropdown.style.display = 'block';
    }
}

function selectCustomOption(option) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    const wrapper = option.closest('.custom-select');
    const trigger = wrapper.querySelector('.custom-select__trigger');
    const select = wrapper.querySelector('select');
    const value = option.dataset.value;
    const text = option.querySelector('.custom-select__option-label').textContent;

    // Update trigger display
    const valueDiv = trigger.querySelector('.custom-select__value');
    valueDiv.innerHTML = '';

    // Add color dot if exists
    const colorDot = option.querySelector('.custom-select__option-color');
    if (colorDot) {
        const dot = document.createElement('div');
        dot.className = 'custom-select__color-dot';
        dot.style.backgroundColor = colorDot.style.backgroundColor;
        valueDiv.appendChild(dot);
    }

    const textSpan = document.createElement('span');
    textSpan.className = 'custom-select__text';
    textSpan.textContent = text;
    valueDiv.appendChild(textSpan);

    // Update hidden select
    if (select) {
        select.value = value;
        select.dispatchEvent(new Event('change', { bubbles: true }));
    }

    // Mark selected option
    wrapper.querySelectorAll('.custom-select__option').forEach(o => o.classList.remove('selected'));
    option.classList.add('selected');

    // Close dropdown
    trigger.classList.remove('active');
    const dropdown = wrapper.querySelector('.custom-select__dropdown');
    if (dropdown) dropdown.style.display = 'none';

    // Trigger custom event for specific handlers
    const selectId = wrapper.dataset.selectId;
    if (selectId === 'rowChangeModalLoaiGhe') {
        handleRowLoaiGheChange({ value });
    } else if (selectId === 'bulkLoaiGhe') {
        handleBulkLoaiGheChange({ value });
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.custom-select')) {
        document.querySelectorAll('.custom-select__trigger.active').forEach(t => {
            t.classList.remove('active');
        });
        document.querySelectorAll('.custom-select__dropdown').forEach(d => {
            d.style.display = 'none';
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const phongChieuId = {{ $phongChieu->id }};

    // === SEAT TYPE PILL HANDLING ===
    document.querySelectorAll('.seat-type-pill').forEach(function(pill) {
        pill.addEventListener('click', function(e) {
            // Remove active from siblings in same group
            const container = this.closest('.flex.flex-wrap');
            container.querySelectorAll('.seat-type-pill').forEach(p => p.classList.remove('active'));
            this.classList.add('active');

            // Update hidden input
            const hiddenId = this.dataset.hidden;
            const value = this.dataset.value;
            const hiddenInput = document.getElementById(hiddenId);
            if (hiddenInput) {
                hiddenInput.value = value;
            }
        });
    });

    // === CSRF helper: đặt sớm để mọi handler bên dưới đều dùng được ===
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta && meta.getAttribute('content')) {
            return meta.getAttribute('content');
        }
        const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);
        if (match) {
            return decodeURIComponent(match[1]);
        }
        return '';
    }

    let selectedSeats = new Set();
    let currentSeatId = null;
    let currentRowHangId = null;
    let currentSeatEl = null;
    let currentSeatSiblings = []; // Mảng DOM elements thuộc cùng cặp couple (nếu có)

    // === TỰ ĐỘNG PHÁT HIỆN ĐỘ SÁNG NỀN VÀ CHỌN MÀU CHỮ PHÙ HỢP ===
    function getLuminance(hexColor) {
        // Chuyển hex -> RGB
        const hex = hexColor.replace('#', '');
        const r = parseInt(hex.substr(0, 2), 16);
        const g = parseInt(hex.substr(2, 2), 16);
        const b = parseInt(hex.substr(4, 2), 16);
        // Tính luminance theo công thức WCAG
        const a = [r, g, b].map(v => {
            v /= 255;
            return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
        });
        return 0.2126 * a[0] + 0.7152 * a[1] + 0.0722 * a[2];
    }

    function applyAutoContrast() {
        document.querySelectorAll('.seat-interactive').forEach(seat => {
            // Ghế bảo trì: giữ style mặc định
            if (seat.classList.contains('seat-chip--maintenance')) return;
            const mauSac = seat.dataset.mauSac; 
            if (!mauSac) return;
            const lum = getLuminance(mauSac);
            // Nếu luminance > 0.5 (nền sáng) thì dùng chữ đen
            // Nếu luminance <= 0.5 (nền tối) thì dùng chữ trắng
            if (lum > 0.5) {
                seat.classList.add('seat-light-bg');
                seat.classList.remove('seat-dark-bg');
            } else {
                seat.classList.add('seat-dark-bg');
                seat.classList.remove('seat-light-bg');
            }
        });
    }
    applyAutoContrast();

    // --- Elements ---
    const bulkActionsToolbar = document.getElementById('bulkActionsToolbar');
    const selectedCount = document.getElementById('selectedCount');
    const clearSelection = document.getElementById('clearSelection');
    const bulkLoaiGheSelect = document.getElementById('bulkLoaiGheSelect') || document.getElementById('bulkLoaiGhe');
    const applyBulkAction = document.getElementById('applyBulkAction');
    const btnToggleMaintenance = document.getElementById('btnToggleMaintenance');
    const popover = document.getElementById('seatInfoPopover');
    const rowChangeModal = document.getElementById('rowChangeModal');
    const rowModalLoaiGhe = document.getElementById('rowChangeModalLoaiGhe');

    // --- Helpers ---
    function findSeatEl(gheId) {
        return document.querySelector(`.seat-interactive[data-ghe-id="${gheId}"]`);
    }

    function updateSeatDOM(seatEl, loaiGhe, mauSac, trangThai, phuThu) {
        if (!seatEl) return;
        // Lưu lại mã ghế hiện tại (vì className sẽ bị reset)
        const maGhe = seatEl.dataset.maGhe;
        const isSelected = seatEl.classList.contains('selected');
        const isCouple = seatEl.classList.contains('seat-chip--couple');

        seatEl.dataset.loaiGhe = loaiGhe;
        seatEl.dataset.mauSac = mauSac;
        seatEl.dataset.trangThai = trangThai;
        seatEl.dataset.phuThu = phuThu;
        seatEl.title = maGhe + ' - ' + loaiGhe + ' (' + Number(phuThu).toLocaleString() + 'đ)';

        // Build className
        let className = 'seat-chip seat-interactive';
        if (trangThai === 'bao_tri') className += ' seat-chip--maintenance';
        else if (trangThai === 'sap_bao_tri') className += ' seat-chip--pending';
        if (isCouple) className += ' seat-chip--couple';
        if (isSelected) className += ' selected';
        seatEl.className = className;

        // Reset style + content - xử lý đúng theo trạng thái
        if (trangThai === 'bao_tri') {
            seatEl.style.backgroundColor = '#dc2626';
            seatEl.style.color = '#ffffff';
        } else if (trangThai === 'sap_bao_tri') {
            seatEl.style.backgroundColor = '#fef3c7';
            seatEl.style.color = '#92400e';
        } else {
            seatEl.style.backgroundColor = mauSac;
            seatEl.style.color = '#1a0b04';
        }
        // Đảm bảo label span bên trong giữ màu đúng
        const labelSpans = seatEl.querySelectorAll('.seat-label, .seat-couple-left, .seat-couple-right');
        labelSpans.forEach(s => {
            if (trangThai === 'bao_tri') {
                s.style.color = '#ffffff';
            } else if (trangThai === 'sap_bao_tri') {
                s.style.color = '#92400e';
            } else {
                s.style.color = '#1a0b04';
            }
        });
    }

    function updateBulkToolbar() {
        const count = selectedSeats.size;
        if (count > 0) {
            bulkActionsToolbar.classList.remove('hidden');
            selectedCount.textContent = count;
            renderSelectedSeatsChips();
        } else {
            bulkActionsToolbar.classList.add('hidden');
            renderSelectedSeatsChips();
        }
        updateBulkMaintenanceBtnLabel();
    }

    // Cập nhật danh sách lịch bảo trì sau khi lên lịch thành công
    function updateLichBaoTriList(schedules) {
        const container = document.getElementById('lichBaoTriContainer');
        if (!container) return;

        let wrapper = container.querySelector('.mb-6.rounded-2xl');
        const listContainer = document.getElementById('lichBaoTriList');

        // Nếu chưa có wrapper, tạo mới
        if (!wrapper) {
            wrapper = document.createElement('div');
            wrapper.className = 'mb-6 rounded-2xl border border-amber-500/30 bg-gradient-to-br from-[#0f0f0f] to-[#151515] p-5';
            wrapper.innerHTML = `
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wider text-amber-400">
                        <i class="fa-solid fa-calendar-days"></i>
                        Lịch bảo trì sắp tới (<span id="lichBaoTriCount">0</span>)
                    </h3>
                    <span class="text-xs text-gray-500">Click để hủy</span>
                </div>
                <div id="lichBaoTriList" class="space-y-2 max-h-48 overflow-y-auto"></div>
            `;
            container.appendChild(wrapper);
        }

        // Cập nhật số lượng
        const countSpan = document.getElementById('lichBaoTriCount');
        const newCount = (parseInt(countSpan?.textContent || '0') || 0) + schedules.length;
        if (countSpan) countSpan.textContent = newCount;

        // Thêm các lịch mới vào danh sách
        const list = document.getElementById('lichBaoTriList');
        if (list) {
            schedules.forEach(schedule => {
                // Kiểm tra xem lịch đã tồn tại chưa
                const existing = list.querySelector(`[data-lich-id="${schedule.lich_id}"]`);
                if (existing) return;

                const item = document.createElement('div');
                item.className = 'lich-bao-tri-item group flex items-center justify-between rounded-xl border border-white/5 bg-white/5 p-3 transition hover:border-amber-500/30 hover:bg-amber-500/5';
                item.dataset.lichId = schedule.lich_id;
                item.innerHTML = `
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-500/20">
                            <i class="fa-solid fa-couch text-sm text-amber-400"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-white">${schedule.ma_ghe}</p>
                            <p class="text-xs text-gray-400">
                                ${schedule.thoi_gian_bat_dau}
                                ${schedule.thoi_gian_ket_thuc ? ' - ' + schedule.thoi_gian_ket_thuc : ''}
                            </p>
                        </div>
                    </div>
                    <button type="button"
                        onclick="openModalHuyBaoTri('/admin/lich-bao-tri-ghe-ngois/' + ${schedule.lich_id}, '${schedule.ma_ghe}')"
                        class="rounded-lg border border-red-500/30 bg-red-500/10 px-3 py-1.5 text-xs font-medium text-red-300 transition hover:border-red-500/60 hover:bg-red-500/20">
                        <i class="fa-solid fa-xmark mr-1"></i>Hủy
                    </button>
                `;
                list.appendChild(item);

                // Animation fade in
                item.style.opacity = '0';
                item.style.transform = 'translateY(-10px)';
                requestAnimationFrame(() => {
                    item.style.transition = 'all 0.3s ease';
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                });
            });
        }
    }

    /**
     * Helper: đồng bộ hiệu ứng "đã chọn" với class .selected trên 1 element ghế.
     * Dùng INLINE STYLE để đảm bảo không bị CSS rule nào ghi đè.
     * Không động đến logic bulk action / click handler khác.
     */
    function syncSeatCheckMark(seatEl) {
        if (!seatEl) return;
        const existing = seatEl.querySelector('.seat-check-mark');
        if (seatEl.classList.contains('selected')) {
            if (!existing) {
                const mark = document.createElement('span');
                mark.className = 'seat-check-mark';
                seatEl.appendChild(mark);
            }
            // === Inline style — chắc chắn thắng mọi CSS rule ===
            seatEl.style.setProperty('transform', 'scale(1.15) translateY(-8px)', 'important');
            seatEl.style.setProperty('z-index', '9999', 'important');
            seatEl.style.setProperty('filter', 'brightness(1.35) saturate(1.25) drop-shadow(0 0 8px rgba(244,197,106,0.9))', 'important');
            seatEl.style.setProperty('outline', '3px solid #fde68a', 'important');
            seatEl.style.setProperty('outline-offset', '3px', 'important');
            seatEl.style.setProperty('animation', 'seatPulse 1.4s ease-in-out infinite', 'important');
        } else {
            if (existing) existing.remove();
            // === Xóa inline style để ghế trở về bình thường ===
            seatEl.style.removeProperty('transform');
            seatEl.style.removeProperty('z-index');
            seatEl.style.removeProperty('filter');
            seatEl.style.removeProperty('outline');
            seatEl.style.removeProperty('outline-offset');
            seatEl.style.removeProperty('animation');
        }
    }
    // Đồng bộ cho mọi ghế khi load (phòng trường hợp có .selected sẵn từ server)
    document.querySelectorAll('.seat-interactive').forEach(syncSeatCheckMark);
    // (Đã bỏ debug log sau khi xác nhận hook chạy đúng)

    /**
     * Render danh sách chip tên ghế đã chọn vào #selectedSeatsList
     * - Với ghế đơn: hiển thị tên 1 chip
     * - Với ghế couple: hiển thị 1 chip với cả 2 tên (H1+H2) để user biết cả cặp được chọn
     * - Click vào chip để bỏ chọn ghế đó
     */
    function renderSelectedSeatsChips() {
        const list = document.getElementById('selectedSeatsList');
        if (!list) return;
        list.innerHTML = '';

        // Group theo DOM node (couple = 1 node, thường = 1 node)
        const groupMap = new Map();
        selectedSeats.forEach(id => {
            const el = document.querySelector(`.seat-interactive[data-ghe-id="${id}"]`);
            if (!el) return;
            // Tìm node đại diện: nếu là couple thì dùng node left, nếu là right thì dùng node left
            let key;
            if (el.classList.contains('seat-chip--couple')) {
                // Couple: 1 node = 1 cặp, dùng node đó luôn
                key = el.dataset.gheId + '|couple';
            } else {
                key = el.dataset.gheId;
            }
            if (!groupMap.has(key)) {
                groupMap.set(key, { els: [el], isCouple: el.classList.contains('seat-chip--couple') });
            } else {
                groupMap.get(key).els.push(el);
            }
        });

        // Sắp xếp theo thứ tự: A1, A2, ... rồi tới couple
        const sortedKeys = Array.from(groupMap.keys()).sort((a, b) => {
            const elA = groupMap.get(a).els[0];
            const elB = groupMap.get(b).els[0];
            return elA.getBoundingClientRect().top - elB.getBoundingClientRect().top
                || elA.getBoundingClientRect().left - elB.getBoundingClientRect().left;
        });

        sortedKeys.forEach(key => {
            const group = groupMap.get(key);
            const el = group.els[0];
            const isCouple = group.isCouple;
            const color = el.style.backgroundColor || '#666';
            const siblings = (() => {
                try {
                    const arr = JSON.parse(el.dataset.coupleSiblings || '[]');
                    return Array.isArray(arr) ? arr : [];
                } catch (e) { return []; }
            })();
            const label = isCouple && siblings.length > 1
                ? siblings.map(sid => {
                    const sibEl = document.querySelector(`.seat-interactive[data-ghe-id="${sid}"]`);
                    return sibEl ? sibEl.dataset.maGhe : `#${sid}`;
                }).join(' + ')
                : el.dataset.maGhe;

            const chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'inline-flex items-center gap-1 rounded-full border-2 border-[#f4c56a] bg-[#f4c56a]/15 px-2.5 py-1 text-xs font-bold text-[#f4c56a] hover:bg-[#f4c56a]/30 transition';
            chip.innerHTML = `
                <span class="inline-block h-2 w-2 rounded-full" style="background-color: ${color}"></span>
                <span>${label}</span>
                <i class="fa-solid fa-xmark text-[10px] opacity-70 hover:opacity-100"></i>
            `;
            chip.addEventListener('click', (e) => {
                e.stopPropagation();
                // Bỏ chọn cả cặp nếu là couple
                if (isCouple) {
                    siblings.forEach(sid => selectedSeats.delete(sid));
                } else {
                    selectedSeats.delete(el.dataset.gheId);
                }
                // Cập nhật DOM
                document.querySelectorAll('.seat-interactive.selected').forEach(dom => {
                    const sibArr = (() => {
                        try {
                            const arr = JSON.parse(dom.dataset.coupleSiblings || '[]');
                            return Array.isArray(arr) ? arr : [];
                        } catch (e) { return []; }
                    })();
                    if (sibArr.length > 1) {
                        // Couple: nếu không còn selectedSeats chứa id của nó thì bỏ
                        const stillSelected = sibArr.some(sid => selectedSeats.has(sid));
                        if (!stillSelected) { dom.classList.remove('selected'); syncSeatCheckMark(dom); }
                    } else {
                        if (!selectedSeats.has(dom.dataset.gheId)) { dom.classList.remove('selected'); syncSeatCheckMark(dom); }
                    }
                });
                updateBulkToolbar();
            });
            list.appendChild(chip);
        });
    }

    function setBtnLoading(btn, loading, originalText) {
        if (loading) {
            btn.disabled = true;
            btn.dataset.originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1.5"></i>Đang xử lý...';
        } else {
            btn.disabled = false;
            btn.innerHTML = btn.dataset.originalText || originalText;
        }
    }

    function positionPopover(seatEl) {
        const rect = seatEl.getBoundingClientRect();
        const popoverW = 260;
        const popoverH = 320;
        let left = rect.right + 14;
        let top = rect.top - 20;

        if (left + popoverW > window.innerWidth - 16) {
            left = rect.left - popoverW - 14;
        }
        if (top + popoverH > window.innerHeight - 16) {
            top = window.innerHeight - popoverH - 16;
        }
        if (top < 16) top = 16;

        popover.style.left = left + 'px';
        popover.style.top = top + 'px';
    }

    function showPopover(seatEl) {
        currentSeatEl = seatEl;
        currentSeatId = seatEl.dataset.gheId;
        const maGhe = seatEl.dataset.maGhe;
        const loaiGhe = seatEl.dataset.loaiGhe;
        const mauSac = seatEl.dataset.mauSac || '#666666';
        const phuThu = Number(seatEl.dataset.phuThu || 0);
        const trangThai = seatEl.dataset.trangThai;

        // Fill popover info
        document.getElementById('popoverMaGhe').textContent = maGhe;
        document.getElementById('popoverLoaiGhe').textContent = loaiGhe.toUpperCase();
        document.getElementById('popoverPhuThu').textContent = phuThu > 0 ? '+' + phuThu.toLocaleString() + 'đ' : 'Miễn phí';
        document.getElementById('popoverHeaderMaGhe').textContent = maGhe;

        // Set màu cho header gradient + color block thông qua CSS variable
        popover.style.setProperty('--popover-color', mauSac);

        // Status badge - đồng nhất với style tối giản của trang
        const statusEl = document.getElementById('popoverStatus');
        const maintInfoEl = document.getElementById('popoverMaintenanceInfo');
        const maintTimeEl = document.getElementById('popoverMaintenanceTime');
        const maintReasonEl = document.getElementById('popoverMaintenanceReason');

        if (trangThai === 'bao_tri' || trangThai === 'sap_bao_tri') {
            statusEl.innerHTML = '<span class="inline-flex items-center gap-1.5 rounded-full border border-red-500/30 bg-red-500/10 px-3 py-1 text-[11px] font-bold text-red-400"><i class="fa-solid fa-wrench text-[10px]"></i> Đang bảo trì</span>';

            // Hiển thị thông tin bảo trì có thời hạn nếu có
            const lichId = seatEl.dataset.lichId;
            const thoiGianKetThuc = seatEl.dataset.thoiGianKetThuc || seatEl.dataset.thoiGianKetThucRaw;
            const isUnlimited = seatEl.dataset.isUnlimitedMaintenance === '1';

            if (lichId && thoiGianKetThuc) {
                maintInfoEl?.classList.remove('hidden');
                maintTimeEl.textContent = '⏰ Hết hạn: ' + thoiGianKetThuc;
                maintReasonEl.textContent = 'Bảo trì có thời hạn';
            } else if (lichId || isUnlimited) {
                maintInfoEl?.classList.remove('hidden');
                maintTimeEl.textContent = '⏸️ Bảo trì vô thời hạn';
                maintReasonEl.textContent = 'Chờ Admin kích hoạt';
            } else {
                maintInfoEl?.classList.add('hidden');
            }
        } else {
            statusEl.innerHTML = '<span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-[11px] font-bold text-emerald-400"><i class="fa-solid fa-check-circle text-[10px]"></i> Đang hoạt động</span>';
            maintInfoEl?.classList.add('hidden');
        }

        positionPopover(seatEl);
        popover.classList.add('is-visible');
    }

    function hidePopover() {
        popover.classList.remove('is-visible');
        currentSeatEl = null;
    }

    // --- Bulk Toolbar ---
    clearSelection.addEventListener('click', function() {
        selectedSeats.clear();
        document.querySelectorAll('.seat-interactive.selected').forEach(el => { el.classList.remove('selected'); syncSeatCheckMark(el); });
        updateBulkToolbar();
    });

    // Color preview for bulk select
    if (bulkLoaiGheSelect) {
        bulkLoaiGheSelect.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const color = selected?.dataset?.color || '#666';
            const preview = document.getElementById('bulkColorPreview');
            if (preview) preview.style.backgroundColor = color;
        });
    }

    // Row modal color preview
    if (rowModalLoaiGhe) {
        rowModalLoaiGhe.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const color = selected?.dataset?.color || '#666';
            const preview = document.getElementById('rowModalColorPreview');
            if (preview) preview.style.backgroundColor = color;
        });
        const firstOpt = rowModalLoaiGhe.options[rowModalLoaiGhe.selectedIndex];
        const preview = document.getElementById('rowModalColorPreview');
        if (preview && firstOpt?.dataset?.color) preview.style.backgroundColor = firstOpt.dataset.color;
    }

    applyBulkAction.addEventListener('click', function() {
        if (selectedSeats.size === 0) return;

        const loaiGheId = bulkLoaiGheSelect?.value;
        if (!loaiGheId) {
            showToast('Vui lòng chọn loại ghế để thay đổi.', 'warning');
            return;
        }

        // Get selected loai ghe text
        const selectedOption = bulkLoaiGheSelect.options[bulkLoaiGheSelect.selectedIndex];
        const loaiTen = selectedOption ? selectedOption.textContent.toLowerCase() : '';
        const isThuongSelected = loaiTen.includes('thường') || loaiTen.includes('thuong') || loaiTen.includes('standard');
        const isVipSelected = loaiTen.includes('vip');
        const isCoupleSelected = loaiTen.includes('couple') || loaiTen.includes('đôi');

        // Validate: check selected seats against row restrictions
        const allRows = Array.from(document.querySelectorAll('.seat-row'));
        const totalRows = allRows.length;
        let hasViolation = false;
        let violationMsg = '';

        for (const gheId of selectedSeats) {
            const seatEl = findSeatEl(parseInt(gheId));
            if (!seatEl) continue;
            const rowEl = seatEl.closest('.seat-row');
            if (!rowEl) continue;
            const rowIndex = allRows.indexOf(rowEl);
            const isTopThree = rowIndex >= 0 && rowIndex < 3;
            const isBottomThree = rowIndex >= 0 && rowIndex >= totalRows - 3;

            // 3 hàng đầu: không được VIP/Couple
            if (isTopThree && (isVipSelected || isCoupleSelected)) {
                hasViolation = true;
                violationMsg = '3 hàng gần màn chiếu chỉ được đặt ghế Thường. Không thể đặt ghế VIP hay Couple.';
                break;
            }
            // 3 hàng cuối: không được thường
            if (isBottomThree && isThuongSelected) {
                hasViolation = true;
                violationMsg = '3 hàng cuối phòng chỉ được đặt ghế VIP hoặc Couple. Không thể đặt ghế Thường.';
                break;
            }
        }

        if (hasViolation) {
            showToast(violationMsg, 'error');
            return;
        }

        setBtnLoading(this, true);

        fetch(`/admin/phong-chieus/${phongChieuId}/bulk-update-seats`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                ghe_ids: expandCoupleIds(Array.from(selectedSeats)),
                action: 'update_type',
                loai_ghe_id: loaiGheId
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.updated_seats) {
                data.updated_seats.forEach(item => {
                    const el = findSeatEl(item.id);
                    if (el) updateSeatDOM(el, item.loai_ghe, item.mau_sac, item.trang_thai, item.phu_thu);
                });
                selectedSeats.clear();
                document.querySelectorAll('.seat-interactive.selected').forEach(el => { el.classList.remove('selected'); syncSeatCheckMark(el); });
                updateBulkToolbar();
                bulkLoaiGheSelect.value = '';
                setTimeout(() => location.reload(), 300);
            } else {
                showToast(data.message || 'Có lỗi xảy ra', 'error');
            }
        })
        .catch(err => { console.error(err); showToast('Có lỗi xảy ra', 'error'); })
        .finally(() => { setBtnLoading(this, false, '<i class="fa-solid fa-check mr-1.5"></i>Áp dụng'); });
    });

    // --- Modal Bảo trì ---
    const maintenanceModal = document.getElementById('maintenanceModal');
    const maintenanceStartTime = document.getElementById('maintenanceStartTime');
    const maintenanceEndTime = document.getElementById('maintenanceEndTime');
    const maintenanceReason = document.getElementById('maintenanceReason');
    const maintenanceReasonUnlimited = document.getElementById('maintenanceReasonUnlimited');
    const scheduledForm = document.getElementById('scheduledMaintenanceForm');
    const unlimitedReasonDiv = document.getElementById('unlimitedMaintenanceReason');
    const seatCountSpan = document.getElementById('maintenanceModalSeatCount');

    // Thiết lập min cho datetime-local
    if (maintenanceStartTime) {
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        maintenanceStartTime.min = now.toISOString().slice(0, 16);
    }

    // Toggle form khi chọn loại bảo trì
    document.querySelectorAll('input[name="maintenance_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'scheduled') {
                scheduledForm?.classList.remove('hidden');
                unlimitedReasonDiv?.classList.add('hidden');
            } else {
                scheduledForm?.classList.add('hidden');
                unlimitedReasonDiv?.classList.remove('hidden');
            }
        });
    });

    // Mở modal bảo trì
    let pendingMaintenanceSeats = [];

    btnToggleMaintenance?.addEventListener('click', function() {
        if (selectedSeats.size === 0) return;
        const action = this.dataset.bulkAction || 'maintenance';

        // Nếu là kích hoạt (đang bảo trì) → gọi trực tiếp API
        if (action === 'activate') {
            setBtnLoading(this, true);
            fetch(`/admin/phong-chieus/${phongChieuId}/bulk-update-seats`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    ghe_ids: expandCoupleIds(Array.from(selectedSeats)),
                    action: 'activate'
                })
            })
            .then(response => response.json().then(data => ({ response, data })))
            .then(({ response, data }) => {
                if (response.ok && data.success && data.updated_seats) {
                    data.updated_seats.forEach(item => {
                        const el = findSeatEl(item.id);
                        if (el) updateSeatDOM(el, item.loai_ghe, item.mau_sac, item.trang_thai, item.phu_thu);
                    });
                    selectedSeats.clear();
                    document.querySelectorAll('.seat-interactive.selected').forEach(el => { el.classList.remove('selected'); syncSeatCheckMark(el); });
                    updateBulkToolbar();
                    showToast(data.message || 'Đã kích hoạt ghế.', 'success');
                } else {
                    showToast(data.message || 'Có lỗi xảy ra', 'error');
                }
            })
            .catch(err => { console.error(err); showToast('Có lỗi xảy ra', 'error'); })
            .finally(() => { updateBulkMaintenanceBtnLabel(); setBtnLoading(this, false); });
            return;
        }

        // Mở modal để chọn loại bảo trì
        pendingMaintenanceSeats = expandCoupleIds(Array.from(selectedSeats));
        const soGhe = pendingMaintenanceSeats.length;
        seatCountSpan.textContent = `${soGhe} ghế`;

        // Reset form
        document.querySelector('input[name="maintenance_type"][value="unlimited"]').checked = true;
        scheduledForm?.classList.add('hidden');
        unlimitedReasonDiv?.classList.remove('hidden');
        if (maintenanceStartTime) maintenanceStartTime.value = '';
        if (maintenanceEndTime) maintenanceEndTime.value = '';
        if (maintenanceReason) maintenanceReason.value = '';
        if (maintenanceReasonUnlimited) maintenanceReasonUnlimited.value = '';

        // Clear errors
        const errorsDiv = document.getElementById('maintenanceErrors');
        const errorsList = document.getElementById('maintenanceErrorsList');
        if (errorsDiv) errorsDiv.classList.add('hidden');
        if (errorsList) errorsList.innerHTML = '';

        maintenanceModal?.classList.remove('hidden');
    });

    // Đóng modal
    document.getElementById('maintenanceModalCancel')?.addEventListener('click', function() {
        maintenanceModal?.classList.add('hidden');
        pendingMaintenanceSeats = [];
    });
    document.getElementById('maintenanceModalCloseHeader')?.addEventListener('click', function() {
        maintenanceModal?.classList.add('hidden');
        pendingMaintenanceSeats = [];
    });

    maintenanceModal?.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
            pendingMaintenanceSeats = [];
        }
    });

    // Áp dụng bảo trì
    document.getElementById('maintenanceModalApply')?.addEventListener('click', async function() {
        if (pendingMaintenanceSeats.length === 0) return;

        const loaiBaoTri = document.querySelector('input[name="maintenance_type"]:checked')?.value;
        const btn = this;

        // Validate
        if (loaiBaoTri === 'scheduled') {
            if (!maintenanceStartTime?.value) {
                showToast('Vui lòng chọn thời gian bắt đầu.', 'warning');
                return;
            }
            if (!maintenanceReason?.value.trim()) {
                showToast('Vui lòng nhập lý do bảo trì.', 'warning');
                return;
            }
        }

        setBtnLoading(btn, true, '');

        if (loaiBaoTri === 'unlimited') {
            // Bảo trì vô thời hạn - dùng API cũ
            try {
                const res = await fetch(`/admin/phong-chieus/${phongChieuId}/bulk-update-seats`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        ghe_ids: pendingMaintenanceSeats,
                        action: 'maintenance',
                        ly_do: maintenanceReasonUnlimited?.value || null
                    })
                });
                const data = await res.json();
                if (data.success && data.updated_seats) {
                    data.updated_seats.forEach(item => {
                        const el = findSeatEl(item.id);
                        if (el) updateSeatDOM(el, item.loai_ghe, item.mau_sac, item.trang_thai, item.phu_thu);
                    });
                    selectedSeats.clear();
                    document.querySelectorAll('.seat-interactive.selected').forEach(el => { el.classList.remove('selected'); syncSeatCheckMark(el); });
                    updateBulkToolbar();
                    showToast(data.message || 'Đã chuyển ghế sang bảo trì.', 'success');
                } else {
                    showToast(data.message || 'Có lỗi xảy ra', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('Có lỗi xảy ra', 'error');
            }
        } else {
            // Bảo trì có thời hạn - dùng API mới
            try {
                const res = await fetch(`/admin/phong-chieus/${phongChieuId}/schedule-seat-maintenance`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        ghe_ids: pendingMaintenanceSeats,
                        thoi_gian_bat_dau: maintenanceStartTime?.value,
                        thoi_gian_ket_thuc: maintenanceEndTime?.value || null,
                        ly_do: maintenanceReason?.value.trim()
                    })
                });
                const data = await res.json();
                console.log('Schedule maintenance response:', data);
                if (data.success) {
                    // Cập nhật DOM
                    if (data.updated_seats) {
                        data.updated_seats.forEach(item => {
                            console.log('Updating seat:', item.id, 'to', item.trang_thai);
                            const el = findSeatEl(item.id);
                            if (el) {
                                el.dataset.trangThai = item.trang_thai;
                                // Thêm thuộc tính thời gian bảo trì
                                if (data.schedules) {
                                    const schedule = data.schedules.find(s => s.ghe_id === item.id);
                                    if (schedule) {
                                        el.dataset.lichBaoTri = schedule.lich_id;
                                        el.dataset.thoiGianKetThuc = schedule.thoi_gian_ket_thuc || '';
                                    }
                                }
                                updateSeatDOM(el, item.loai_ghe, item.mau_sac, item.trang_thai, item.phu_thu);
                            }
                        });
                    }
                    selectedSeats.clear();
                    document.querySelectorAll('.seat-interactive.selected').forEach(el => { el.classList.remove('selected'); syncSeatCheckMark(el); });
                    updateBulkToolbar();

                    // Cập nhật danh sách lịch bảo trì
                    if (data.schedules && data.schedules.length > 0) {
                        updateLichBaoTriList(data.schedules);
                    }

                    // Hiện lỗi nếu có
                    const errorsDiv = document.getElementById('maintenanceErrors');
                    const errorsList = document.getElementById('maintenanceErrorsList');
                    if (data.errors && data.errors.length > 0) {
                        if (errorsList) errorsList.innerHTML = data.errors.map(e => `<div class="mb-1">• ${e}</div>`).join('');
                        if (errorsDiv) errorsDiv.classList.remove('hidden');
                        showToast('Một số ghế không thể bảo trì. Xem chi tiết bên dưới.', 'warning');
                    } else {
                        if (errorsDiv) errorsDiv.classList.add('hidden');
                        showToast(data.message || 'Đã lên lịch bảo trì.', 'success');
                    }
                } else {
                    // Có thể có validation errors trong response
                    if (data.errors && data.errors.length > 0) {
                        const errorsDiv = document.getElementById('maintenanceErrors');
                        const errorsList = document.getElementById('maintenanceErrorsList');
                        if (errorsList) errorsList.innerHTML = data.errors.map(e => `<div class="mb-1">• ${e}</div>`).join('');
                        if (errorsDiv) errorsDiv.classList.remove('hidden');
                    }
                    showToast(data.message || 'Có lỗi xảy ra', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('Có lỗi xảy ra', 'error');
            }
        }

        maintenanceModal?.classList.add('hidden');
        pendingMaintenanceSeats = [];
        setBtnLoading(btn, false, '<i class="fa-solid fa-wrench mr-1.5"></i>Bảo trì');
        updateBulkMaintenanceBtnLabel();
    });

    // Hàm kiểm tra ghế hết hạn bảo trì
    async function checkExpiredMaintenance() {
        try {
            const res = await fetch(`/admin/phong-chieus/${phongChieuId}/check-expired-maintenance`);
            const data = await res.json();
            if (data.success && data.updated_seats && data.updated_seats.length > 0) {
                data.updated_seats.forEach(item => {
                    const el = findSeatEl(item.id);
                    if (el) {
                        el.dataset.trangThai = item.trang_thai;
                        updateSeatDOM(el, item.loai_ghe, item.mau_sac, item.trang_thai, item.phu_thu);
                    }
                });
                console.log('Đã tự động cập nhật', data.updated_seats.length, 'ghế hết hạn bảo trì');
            }
        } catch (err) {
            console.error('Lỗi kiểm tra bảo trì hết hạn:', err);
        }
    }

    // Gọi ngay khi load trang
    checkExpiredMaintenance();

    // Tự động kiểm tra mỗi phút
    setInterval(checkExpiredMaintenance, 60000);

    // --- Bulk Xóa ghế ---
    const btnBulkDeleteSeats = document.getElementById('btnBulkDeleteSeats');
    btnBulkDeleteSeats?.addEventListener('click', function() {
        if (selectedSeats.size === 0) {
            showToast('Vui lòng chọn ít nhất 1 ghế để xóa.', 'warning');
            return;
        }

        // Mở rộng couple: nếu chọn 1 trong 2 ghế couple thì xóa cả cặp
        const expandedIds = expandCoupleIds(Array.from(selectedSeats));
        const soGhe = expandedIds.length;

        if (!confirm(
            `Xóa ${soGhe} ghế đã chọn?\n\n` +
            `Hành động này không thể hoàn tác. ` +
            `Các ghế đang có vé đã bán sẽ KHÔNG bị xóa.`
        )) return;

        setBtnLoading(this, true);

        fetch(`/admin/phong-chieus/${phongChieuId}/bulk-update-seats`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                ghe_ids: Array.from(expandedIds),
                action: 'delete'
            })
        })
        .then(async res => ({ status: res.status, ct: res.headers.get('content-type') || '', body: await res.text() }))
        .then(({ status, ct, body }) => {
            const data = ct.includes('application/json') ? JSON.parse(body) : { success: false, message: body };
            if (data.success) {
                // Xóa DOM các ghế đã xóa (server trả updated_seats rỗng cho delete; tự loại theo expandedIds)
                expandedIds.forEach(id => {
                    document.querySelectorAll(`.seat-interactive[data-ghe-id="${id}"]`).forEach(el => el.remove());
                });
                selectedSeats.clear();
                document.querySelectorAll('.seat-interactive.selected').forEach(el => { el.classList.remove('selected'); syncSeatCheckMark(el); });
                updateBulkToolbar();
                showToast(data.message || `Đã xóa ${soGhe} ghế.`, 'success');
            } else {
                showToast(data.message || 'Có lỗi xảy ra', 'error');
            }
        })
        .catch(err => { console.error(err); showToast('Có lỗi xảy ra', 'error'); })
        .finally(() => { setBtnLoading(this, false, '<i class="fa-solid fa-trash-can mr-1.5"></i>Xóa ghế'); });
    });

    // Cập nhật nhãn nút bảo trì/hoạt động trong bulk toolbar theo trạng thái các ghế đã chọn
    function updateBulkMaintenanceBtnLabel() {
        const btn = btnToggleMaintenance;
        if (!btn) return;
        if (selectedSeats.size === 0) {
            btn.innerHTML = '<i class="fa-solid fa-wrench mr-1.5"></i>Bảo trì';
            btn.dataset.bulkAction = '';
            return;
        }
        // Đếm trạng thái các ghế đã chọn (data-trang-thai lưu trên DOM)
        let soBaoTri = 0;
        let soHoatDong = 0;
        selectedSeats.forEach(id => {
            const el = document.querySelector(`.seat-interactive[data-ghe-id="${id}"]`);
            if (!el) return;
            const st = el.dataset.trangThai || 'hoat_dong';
            if (st === 'bao_tri') soBaoTri++;
            else soHoatDong++;
        });
        // Quy tắc nhãn:
        //   - Tất cả bảo trì  → "Kích hoạt" (action = activate)
        //   - Còn lại (đang hoạt động hoặc hỗn hợp) → "Bảo trì" (action = maintenance)
        if (soBaoTri > 0 && soHoatDong === 0) {
            btn.innerHTML = '<i class="fa-solid fa-circle-play mr-1.5"></i>Kích hoạt';
            btn.dataset.bulkAction = 'activate';
            btn.classList.add('border-emerald-500/40', 'text-emerald-300', 'bg-emerald-500/10');
            btn.classList.remove('border-white/10', 'text-white', 'bg-white/5');
        } else {
            btn.innerHTML = '<i class="fa-solid fa-wrench mr-1.5"></i>Bảo trì';
            btn.dataset.bulkAction = 'maintenance';
            btn.classList.remove('border-emerald-500/40', 'text-emerald-300', 'bg-emerald-500/10');
            btn.classList.add('border-white/10', 'text-white', 'bg-white/5');
        }
    }

    // --- Seat Click ---
    const hoverTooltip = document.getElementById('seatHoverTooltip');
    const hoverMaGhe = document.getElementById('hoverMaGhe');
    const hoverLoaiGhe = document.getElementById('hoverLoaiGhe');

    function positionHoverTooltip(seatEl) {
        const rect = seatEl.getBoundingClientRect();
        const tipWidth = 160;
        let left = rect.left + rect.width / 2 - tipWidth / 2;
        let top = rect.top - 50; // hiện phía trên ghế

        // Đảm bảo không tràn mép
        if (left < 8) left = 8;
        if (left + tipWidth > window.innerWidth - 8) left = window.innerWidth - tipWidth - 8;
        if (top < 8) top = rect.bottom + 12; // đảo xuống dưới nếu sát mép trên

        hoverTooltip.style.left = left + 'px';
        hoverTooltip.style.top = top + 'px';
    }

    function showHoverTooltip(seatEl) {
        if (!hoverTooltip) return;
        hoverMaGhe.textContent = seatEl.dataset.maGhe;
        hoverLoaiGhe.textContent = (seatEl.dataset.loaiGhe || 'Ghế').toUpperCase() +
            (seatEl.dataset.trangThai === 'bao_tri' ? ' · Bảo trì' : '');
        positionHoverTooltip(seatEl);
        hoverTooltip.classList.add('is-visible');
    }

    function hideHoverTooltip() {
        if (!hoverTooltip) return;
        hoverTooltip.classList.remove('is-visible');
    }

    /**
     * Lấy tất cả ID ghế thuộc cùng 1 cặp couple (từ data-couple-siblings)
     * Nếu không phải couple, trả về [id hiện tại]
     * Trả về mảng các id DB (1 hoặc 2 phần tử)
     */
    function getCoupleSiblings(seatEl) {
        const raw = seatEl.dataset.coupleSiblings;
        if (!raw) return [seatEl.dataset.gheId];
        try {
            const arr = (JSON.parse(raw) || []).filter(id => id !== null && id !== undefined && id !== '');
            return Array.isArray(arr) && arr.length > 0 ? arr : [seatEl.dataset.gheId];
        } catch (e) {
            return [seatEl.dataset.gheId];
        }
    }

    /**
     * Lấy DOM elements thuộc cùng cặp couple.
     * Vì hiện tại mỗi cặp couple chỉ render 1 DOM node duy nhất, trả về [seatEl].
     * (Giữ hàm này cho tương thích - mọi highlight sẽ áp dụng lên chính node đó)
     */
    function getCoupleDOMElements(seatEl) {
        return [seatEl];
    }

    /**
     * Mở rộng danh sách ghế đã chọn: nếu 1 ghế couple được chọn, tự thêm cả cặp
     * Cũng tự đánh dấu .selected trên DOM tương ứng
     */
    function expandCoupleIds(ids) {
        const expanded = new Set(ids);
        ids.forEach(id => {
            const el = document.querySelector(`.seat-interactive[data-ghe-id="${id}"]`);
            if (!el) return;
            const siblingsIds = getCoupleSiblings(el);
            siblingsIds.forEach(sid => expanded.add(sid));
            // Đánh dấu selected trên chính DOM node couple (1 node = 1 cặp)
            if (siblingsIds.length > 1) {
                el.classList.add('selected');
                syncSeatCheckMark(el);
            }
        });
        return Array.from(expanded);
    }

    document.querySelectorAll('.seat-interactive').forEach(seat => {
        seat.addEventListener('click', function(e) {
            // Lấy tất cả ID ghế cùng cặp (1 hoặc 2 id)
            const siblingIds = getCoupleSiblings(this);
            const seatId = this.dataset.gheId;
            const isCoupleSeat = siblingIds.length > 1;

            if (e.ctrlKey || e.metaKey) {
                // Multi-select
                if (isCoupleSeat) {
                    // Toggle nguyên cặp: kiểm tra xem TẤT CẢ ghế trong cặp đã được chọn chưa
                    const allSelected = siblingIds.every(id => selectedSeats.has(id));
                    if (allSelected) {
                        // Bỏ chọn cả cặp
                        siblingIds.forEach(id => selectedSeats.delete(id));
                        this.classList.remove('selected');
                        syncSeatCheckMark(this);
                    } else {
                        // Chọn cả cặp
                        siblingIds.forEach(id => selectedSeats.add(id));
                        this.classList.add('selected');
                        syncSeatCheckMark(this);
                    }
                } else {
                    if (selectedSeats.has(seatId)) {
                        selectedSeats.delete(seatId);
                        this.classList.remove('selected');
                        syncSeatCheckMark(this);
                    } else {
                        selectedSeats.add(seatId);
                        this.classList.add('selected');
                        syncSeatCheckMark(this);
                    }
                }
                updateBulkToolbar();
            } else {
                // Single click - show popover
                currentSeatId = seatId;
                currentSeatSiblings = siblingIds; // mảng 1 hoặc 2 id
                showPopover(this);
            }
        });

        seat.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            currentSeatId = this.dataset.gheId;
            currentSeatSiblings = getCoupleSiblings(this);
            showPopover(this);
        });
    });

    // --- Popover Actions ---
    // (Đã bỏ: Đổi loại / Bảo trì / Xóa trong popover — dùng thanh bulk action)

    // Hide popover on outside click
    document.addEventListener('click', function(e) {
        if (!popover.contains(e.target) && !e.target.classList.contains('seat-interactive')) {
            hidePopover();
        }
    });

    // Ẩn hover tooltip khi click chuột (vì popover sẽ hiện)
    document.addEventListener('mousedown', hideHoverTooltip);

    // --- Row Click Modal ---
    document.querySelectorAll('.seat-row__label--clickable').forEach(label => {
        label.addEventListener('click', function() {
            const row = this.closest('.seat-row');
            currentRowHangId = row.dataset.hangGheId;
            const tenHang = row.dataset.hang;

            document.getElementById('rowChangeModalTenHang').textContent = tenHang;
            const allRows = Array.from(document.querySelectorAll('.seat-row'));
            const rowIndex = allRows.indexOf(row);
            document.getElementById('rowChangeModalRowIndex').textContent = 'Hàng thứ ' + (rowIndex + 1);

            // Tính thống kê bảo trì trong hàng
            const seatsInRow = row.querySelectorAll('.seat-interactive');
            let soHoatDong = 0, soBaoTri = 0;
            seatsInRow.forEach(s => {
                if (s.dataset.trangThai === 'bao_tri') soBaoTri++;
                else soHoatDong++;
            });
            const total = seatsInRow.length;
            const statsEl = document.getElementById('rowMaintenanceStats');
            const btnEl = document.getElementById('rowMaintenanceBtn');
            if (total === 0) {
                statsEl.innerHTML = 'Hàng chưa có ghế';
            } else {
                statsEl.innerHTML = `<span class="text-emerald-400 font-semibold">${soHoatDong}</span> hoạt động · <span class="text-red-400 font-semibold">${soBaoTri}</span> bảo trì / ${total}`;
            }
            // Reset trạng thái nút (nếu lần trước bị disable)
            btnEl.disabled = false;
            // Nhãn nút: nếu tất cả đang bảo trì → "Kích hoạt" (xanh), ngược lại "Bảo trì" (cam)
            if (total > 0 && soHoatDong === 0) {
                btnEl.innerHTML = '<i class="fa-solid fa-rotate mr-1"></i>Kích hoạt';
                btnEl.className = 'rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 py-1.5 text-xs font-bold text-emerald-300 transition hover:bg-emerald-500/25 hover:border-emerald-500/60';
            } else {
                btnEl.innerHTML = '<i class="fa-solid fa-wrench mr-1"></i>Bảo trì';
                btnEl.className = 'rounded-lg border border-orange-500/30 bg-orange-500/10 px-3 py-1.5 text-xs font-bold text-orange-300 transition hover:bg-orange-500/25 hover:border-orange-500/60';
            }

            // --- Ràng buộc đổi loại: 3 hàng gần màn chiếu chỉ được ghế Thường, 3 hàng cuối chỉ được VIP/Couple ---
            const totalRows = allRows.length;
            const isTopThree = rowIndex < 3;
            const isLastTwo = rowIndex >= totalRows - 2;
            const restrictionWarning = document.getElementById('rowTypeRestrictionWarning');
            const restrictionDetail = document.getElementById('rowRestrictionDetail');
            const applyBtn = document.getElementById('rowChangeModalApply');
            const loaiSelect = document.getElementById('rowChangeModalLoaiGhe');
            const colorPreview = document.getElementById('rowModalColorPreview');
            const textEl = document.getElementById('rowModalLoaiGheText');

            if (restrictionWarning) restrictionWarning.classList.add('hidden');
            if (applyBtn) applyBtn.disabled = false;

            if (isTopThree) {
                if (restrictionWarning) {
                    restrictionWarning.classList.remove('hidden');
                    restrictionDetail.textContent = '3 hàng gần màn chiếu chỉ được đặt ghế Thường.';
                }
                if (applyBtn) applyBtn.disabled = true;
            } else if (isLastTwo) {
                if (restrictionWarning) {
                    restrictionWarning.classList.remove('hidden');
                    restrictionDetail.textContent = '2 hàng cuối chỉ được đặt ghế Couple.';
                }
            } else {
                if (restrictionWarning) restrictionWarning.classList.add('hidden');
            }

            // Reset dropdown về option đầu tiên
            const colorDot = document.getElementById('rowModalColorDot');
            if (loaiSelect && loaiSelect.options.length > 0) {
                loaiSelect.selectedIndex = 0;
                const firstOpt = loaiSelect.options[0];
                if (colorDot) colorDot.style.backgroundColor = firstOpt.dataset.color || '#666';
            }

            rowChangeModal.classList.remove('hidden');
        });
    });

    // Mở modal đổi loại hàng từ nút "Đổi loại" trong bảng danh sách hàng ghế
    document.querySelectorAll('[data-row-trigger]').forEach(btn => {
        btn.addEventListener('click', function() {
            const hangGheId = this.dataset.hangGheId;
            const tenHang = this.dataset.hang;
            if (!hangGheId) return;

            currentRowHangId = hangGheId;
            document.getElementById('rowChangeModalTenHang').textContent = tenHang;
            document.getElementById('rowChangeModalRowIndex').textContent = '';

            // Thống kê bảo trì dựa trên DOM đã render
            const rowEl = document.querySelector(`.seat-row[data-hang-ghe-id="${hangGheId}"]`);
            const seatsInRow = rowEl ? rowEl.querySelectorAll('.seat-interactive') : [];
            let soHoatDong = 0, soBaoTri = 0;
            seatsInRow.forEach(s => {
                if (s.dataset.trangThai === 'bao_tri') soBaoTri++;
                else soHoatDong++;
            });
            const total = seatsInRow.length;
            const statsEl = document.getElementById('rowMaintenanceStats');
            const btnEl = document.getElementById('rowMaintenanceBtn');
            if (total === 0) {
                statsEl.innerHTML = 'Hàng chưa có ghế';
            } else {
                statsEl.innerHTML = `<span class="text-emerald-400 font-semibold">${soHoatDong}</span> hoạt động · <span class="text-red-400 font-semibold">${soBaoTri}</span> bảo trì / ${total}`;
            }
            btnEl.disabled = false;
            if (total > 0 && soHoatDong === 0) {
                btnEl.innerHTML = '<i class="fa-solid fa-rotate mr-1"></i>Kích hoạt';
                btnEl.className = 'rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 py-1.5 text-xs font-bold text-emerald-300 transition hover:bg-emerald-500/25 hover:border-emerald-500/60';
            } else {
                btnEl.innerHTML = '<i class="fa-solid fa-wrench mr-1"></i>Bảo trì';
                btnEl.className = 'rounded-lg border border-orange-500/30 bg-orange-500/10 px-3 py-1.5 text-xs font-bold text-orange-300 transition hover:bg-orange-500/25 hover:border-orange-500/60';
            }

            // --- Ràng buộc đổi loại: 3 hàng gần màn chiếu chỉ được ghế Thường, 2 hàng cuối chỉ được Couple ---
            const allRows = Array.from(document.querySelectorAll('.seat-row'));
            const rowIndex = allRows.findIndex(r => r.dataset.hangGheId === hangGheId);
            const totalRows = allRows.length;
            const isTopThree = rowIndex >= 0 && rowIndex < 3;
            const isLastTwo = rowIndex >= 0 && rowIndex >= totalRows - 2;
            const restrictionWarning = document.getElementById('rowTypeRestrictionWarning');
            const restrictionDetail = document.getElementById('rowRestrictionDetail');
            const applyBtn = document.getElementById('rowChangeModalApply');

            if (restrictionWarning) restrictionWarning.classList.add('hidden');
            if (applyBtn) applyBtn.disabled = false;

            if (isTopThree) {
                if (restrictionWarning) {
                    restrictionWarning.classList.remove('hidden');
                    restrictionDetail.textContent = '3 hàng gần màn chiếu chỉ được đặt ghế Thường.';
                }
            } else if (isLastTwo) {
                if (restrictionWarning) {
                    restrictionWarning.classList.remove('hidden');
                    restrictionDetail.textContent = '2 hàng cuối chỉ được đặt ghế Couple.';
                }
            }

            // Set first option as default
            const select = document.getElementById('rowChangeModalLoaiGhe');
            const colorDot = document.getElementById('rowModalColorDot');
            if (select && select.options.length > 0) {
                select.selectedIndex = 0;
                const firstOpt = select.options[0];
                if (colorDot) colorDot.style.backgroundColor = firstOpt.dataset.color || '#666';
            }

            rowChangeModal.classList.remove('hidden');
        });
    });

    document.getElementById('rowChangeModalCancel').addEventListener('click', function() {
        rowChangeModal.classList.add('hidden');
    });
    document.getElementById('rowChangeModalCancelHeader')?.addEventListener('click', function() {
        rowChangeModal.classList.add('hidden');
    });

    // Select loại ghế - update color dot
    const loaiGheSelect = document.getElementById('rowChangeModalLoaiGhe');
    const colorDot = document.getElementById('rowModalColorDot');

    // Handler for custom select changes
    window.handleRowLoaiGheChange = function(data) {
        const value = data.value || data.target?.value;
        const select = document.getElementById('rowChangeModalLoaiGhe');
        const option = select?.querySelector(`option[value="${value}"]`);
        const color = option?.dataset?.color || '#666';
        if (colorDot) colorDot.style.backgroundColor = color;

        // === Validation: 3 hàng đầu chỉ được ghế Thường ===
        if (currentRowHangId && option) {
            const allRows = Array.from(document.querySelectorAll('.seat-row'));
            const currentRowEl = document.querySelector(`.seat-row[data-hang-ghe-id="${currentRowHangId}"]`);
            const rowIndex = currentRowEl ? allRows.indexOf(currentRowEl) : -1;
            const isTopThree = rowIndex >= 0 && rowIndex < 3;

            if (isTopThree) {
                const isCoupleOption = option?.dataset?.laCouple === 'true' || option?.dataset?.laCouple === '1';
                const loaiTen = option?.textContent?.toLowerCase() || '';
                const isVipSelected = loaiTen.includes('vip');

                if (isCoupleOption) {
                    showToast('3 hàng gần màn chiếu chỉ được đặt ghế Thường. Không thể đặt ghế Couple.', 'error');
                    // Reset về option đầu tiên
                    if (select && select.options.length > 0) {
                        select.selectedIndex = 0;
                        const firstOpt = select.options[0];
                        if (colorDot) colorDot.style.backgroundColor = firstOpt.dataset.color || '#666';
                    }
                    return;
                }
                if (isVipSelected) {
                    showToast('3 hàng gần màn chiếu chỉ được đặt ghế Thường. Không thể đặt ghế VIP.', 'error');
                    // Reset về option đầu tiên
                    if (select && select.options.length > 0) {
                        select.selectedIndex = 0;
                        const firstOpt = select.options[0];
                        if (colorDot) colorDot.style.backgroundColor = firstOpt.dataset.color || '#666';
                    }
                    return;
                }
            }
        }
    };

    window.handleBulkLoaiGheChange = function(data) {
        const value = data.value || data.target?.value;
        const preview = document.getElementById('bulkColorPreview');
        if (preview) {
            const select = document.getElementById('bulkLoaiGhe');
            const option = select?.querySelector(`option[value="${value}"]`);
            preview.style.backgroundColor = option?.dataset?.color || 'transparent';
        }
    };

    if (loaiGheSelect) {
        loaiGheSelect.addEventListener('change', function() {
            const color = this.options[this.selectedIndex]?.dataset?.color || '#666';
            if (colorDot) colorDot.style.backgroundColor = color;
        });
        // Init color
        const initColor = loaiGheSelect.options[loaiGheSelect.selectedIndex]?.dataset?.color || '#666';
        if (colorDot) colorDot.style.backgroundColor = initColor;
    }

    document.getElementById('rowChangeModalApply').addEventListener('click', function() {
        const select = document.getElementById('rowChangeModalLoaiGhe');
        const loaiGheId = loaiGheSelect.value;
        const selectedOption = loaiGheSelect.options[loaiGheSelect.selectedIndex];
        
        // Lấy thông tin từ data attributes
        const isCoupleOption = selectedOption?.dataset?.laCouple === 'true' || selectedOption?.dataset?.laCouple === '1';
        const loaiTen = selectedOption ? selectedOption.textContent.toLowerCase() : '';
        const isVipSelected = loaiTen.includes('vip');

        // --- Validation: 3 hàng gần màn chiếu chỉ được đặt ghế Thường, 2 hàng cuối chỉ được Couple ---
        const allRows = Array.from(document.querySelectorAll('.seat-row'));
        const currentRowEl = document.querySelector(`.seat-row[data-hang-ghe-id="${currentRowHangId}"]`);
        const rowIndex = currentRowEl ? allRows.indexOf(currentRowEl) : -1;
        const totalRows = allRows.length;

        if (rowIndex >= 0) {
            const isTopThree = rowIndex < 3;  // Hàng 1, 2, 3
            const isLastTwo = rowIndex >= totalRows - 2;  // 2 hàng cuối
            const isMiddle = !isTopThree && !isLastTwo;  // Hàng 4 trở đi (không phải 2 hàng cuối)

            // 3 hàng gần màn chiếu: CHỈ được ghế Thường (không phải VIP, không phải Couple)
            if (isTopThree) {
                if (isCoupleOption) {
                    showToast('3 hàng gần màn chiếu chỉ được đặt ghế Thường. Không thể đặt ghế Couple.', 'error');
                    setBtnLoading(this, false);
                    return;
                }
                if (isVipSelected) {
                    showToast('3 hàng gần màn chiếu chỉ được đặt ghế Thường. Không thể đặt ghế VIP.', 'error');
                    setBtnLoading(this, false);
                    return;
                }
            }
            
            // Hàng giữa (hàng 4 trở đi, không tính 2 hàng cuối): CHỈ được ghế VIP
            if (isMiddle) {
                if (isCoupleOption) {
                    showToast('Hàng ghế này chỉ được đặt ghế VIP. Không thể đặt ghế Couple.', 'error');
                    setBtnLoading(this, false);
                    return;
                }
                if (!isVipSelected) {
                    showToast('Hàng ghế này chỉ được đặt ghế VIP. Không thể đặt ghế Thường.', 'error');
                    setBtnLoading(this, false);
                    return;
                }
            }
            
            // 2 hàng cuối: CHỈ được ghế Couple
            if (isLastTwo) {
                if (!isCoupleOption) {
                    if (isVipSelected) {
                        showToast('2 hàng cuối chỉ được đặt ghế Couple. Không thể đặt ghế VIP.', 'error');
                    } else {
                        showToast('2 hàng cuối chỉ được đặt ghế Couple. Không thể đặt ghế Thường.', 'error');
                    }
                    setBtnLoading(this, false);
                    return;
                }
            }
        }

        const btn = this;
        setBtnLoading(btn, true);

        fetch(`/admin/phong-chieus/${phongChieuId}/update-row-seats`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({ hang_ghe_id: currentRowHangId, loai_ghe_id: loaiGheId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.updated_seats) {
                data.updated_seats.forEach(item => {
                    const el = findSeatEl(item.id);
                    if (el) updateSeatDOM(el, item.loai_ghe, item.mau_sac, item.trang_thai, item.phu_thu);
                });
                setTimeout(() => location.reload(), 300);
            } else {
                showToast(data.message || 'Có lỗi xảy ra', 'error');
            }
        })
        .catch(err => { console.error(err); showToast('Có lỗi xảy ra', 'error'); })
        .finally(() => { setBtnLoading(btn, false, '<i class="fa-solid fa-check mr-1.5"></i>Áp dụng'); rowChangeModal.classList.add('hidden'); });
    });

    rowChangeModal.addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });

    // --- Bảo trì cả hàng (toggle) ---
    // CSRF helper đã được định nghĩa ở đầu script (xem trên cùng)
    console.log('[DEBUG] CSRF token =', getCsrfToken().substring(0, 10) + '...');

    // Helper fetch an toàn:
    //   - Tự đính X-CSRF-TOKEN, X-Requested-With, Accept JSON
    //   - Nếu server trả 419 (CSRF token hết hạn) thì reload meta tag từ server (gọi /csrf-token) rồi retry 1 lần
    async function adminFetch(url, options = {}) {
        options.method = options.method || 'POST';
        options.credentials = options.credentials || 'same-origin';
        options.headers = Object.assign({
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest'
        }, options.headers || {});

        let res = await fetch(url, options);

        if (res.status === 419) {
            // CSRF token mismatch → thử lấy token mới rồi gọi lại
            try {
                const refresh = await fetch('/admin/csrf-token', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
                if (refresh.ok) {
                    const data = await refresh.json();
                    if (data && data.csrf_token) {
                        const meta = document.querySelector('meta[name="csrf-token"]');
                        if (meta) meta.setAttribute('content', data.csrf_token);
                    }
                }
            } catch (e) { /* ignore */ }
            options.headers['X-CSRF-TOKEN'] = getCsrfToken();
            res = await fetch(url, options);
        }

        return res;
    }

    // Hàm được gọi bởi onclick inline trên nút #rowMaintenanceBtn
    // (Đặt trên window để inline onclick truy cập được)
    window.__rowMaintClick = async function(ev) {
        // ev có thể là event từ icon bên trong button
        const btn = ev.target.closest('#rowMaintenanceBtn') || document.getElementById('rowMaintenanceBtn');
        if (!btn) return;

        console.log('[rowMaintenanceBtn] click | hang=', currentRowHangId);

        if (!currentRowHangId) {
            showToast('Vui lòng chọn hàng ghế trước.', 'warning');
            return;
        }

            const isAllMaint = /Kích hoạt/i.test(btn.textContent);
            const action = isAllMaint ? 'activate' : 'maintenance';
            const confirmMsg = isAllMaint
                ? 'Kích hoạt lại toàn bộ ghế trong hàng này?'
                : 'Chuyển toàn bộ ghế trong hàng sang bảo trì?';
            if (!confirm(confirmMsg)) return;

        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i>Đang xử lý...';

        try {
            const url = `/admin/phong-chieus/${phongChieuId}/toggle-row-maintenance`;
            console.log('[rowMaintenanceBtn] fetch', url, { hang_ghe_id: currentRowHangId });

            const res = await fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ hang_ghe_id: currentRowHangId, action: action })
            });

            console.log('[rowMaintenanceBtn] response status', res.status);
            const contentType = res.headers.get('content-type') || '';
            let data;
            if (contentType.includes('application/json')) {
                data = await res.json();
            } else {
                // Server trả về HTML (có thể do CSRF/auth/404) → đọc text để debug
                const text = await res.text();
                console.error('[rowMaintenanceBtn] Non-JSON response (status ' + res.status + '):', text.substring(0, 500));
                showToast('Server trả về HTML (status ' + res.status + '). Có thể CSRF token sai hoặc session hết hạn. Xem Console.', 'error');
                btn.innerHTML = originalHtml;
                return;
            }
            console.log('[rowMaintenanceBtn] data', data);

            if (!data.success) {
                showToast(data.message || 'Có lỗi xảy ra', 'error');
                btn.innerHTML = originalHtml;
                return;
            }

            // Cập nhật DOM cho từng ghế
            (data.updated_seats || []).forEach(s => {
                const el = findSeatEl(s.id);
                if (!el) return;
                el.dataset.trangThai = s.trang_thai;
                if (s.trang_thai === 'bao_tri') {
                    el.classList.add('seat-chip--maintenance');
                    el.removeAttribute('style');
                    el.style.color = '#991b1b';
                } else {
                    el.classList.remove('seat-chip--maintenance');
                    const mauSac = el.dataset.mauSac || '#666';
                    el.removeAttribute('style');
                    el.style.backgroundColor = mauSac;
                    el.style.color = '#1a0b04';
                }
            });

            // Cập nhật stats + đổi nhãn nút
            const statsEl = document.getElementById('rowMaintenanceStats');
            const row = document.querySelector(`.seat-row[data-hang-ghe-id="${currentRowHangId}"]`);
            if (statsEl && row) {
                const seatsInRow = row.querySelectorAll('.seat-interactive');
                let soHoatDong = 0, soBaoTri = 0;
                seatsInRow.forEach(s => {
                    if (s.dataset.trangThai === 'bao_tri') soBaoTri++;
                    else soHoatDong++;
                });
                statsEl.innerHTML = `<span class="text-emerald-400 font-semibold">${soHoatDong}</span> hoạt động · <span class="text-red-400 font-semibold">${soBaoTri}</span> bảo trì / ${seatsInRow.length}`;
                if (seatsInRow.length > 0 && soHoatDong === 0) {
                    btn.innerHTML = '<i class="fa-solid fa-rotate mr-1"></i>Kích hoạt';
                    btn.className = 'rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 py-1.5 text-xs font-bold text-emerald-300 transition hover:bg-emerald-500/25 hover:border-emerald-500/60';
                } else {
                    btn.innerHTML = '<i class="fa-solid fa-wrench mr-1"></i>Bảo trì';
                    btn.className = 'rounded-lg border border-orange-500/30 bg-orange-500/10 px-3 py-1.5 text-xs font-bold text-orange-300 transition hover:bg-orange-500/25 hover:border-orange-500/60';
                }
            }
        } catch (err) {
            console.error('[rowMaintenanceBtn] error', err);
            showToast('Có lỗi xảy ra: ' + (err.message || err), 'error');
            btn.innerHTML = originalHtml;
        } finally {
            btn.disabled = false;
        }
    };
    console.log('[OK] window.__rowMaintClick ready');

    // --- Xóa cả hàng ---
    document.getElementById('rowChangeModalDelete').addEventListener('click', function() {
        if (!currentRowHangId) return;
        const btn = this;
        if (!confirm('Xóa cả hàng ghế này? Tất cả ghế trong hàng sẽ bị xóa vĩnh viễn. Hành động này không thể hoàn tác.')) return;

        setBtnLoading(btn, true);

        fetch(`/admin/phong-chieus/${phongChieuId}/delete-row-seats`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({ hang_ghe_id: currentRowHangId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                showToast(data.message || 'Có lỗi xảy ra', 'error');
            }
        })
        .catch(err => { console.error(err); showToast('Có lỗi xảy ra', 'error'); })
        .finally(() => { setBtnLoading(btn, false, '<i class="fa-solid fa-trash-can mr-1.5"></i>Xóa hàng'); });
    });
});
</script>

{{-- TOOLTIP element để hover hiển thị thông tin ghế --}}
<div id="seatTooltip" style="position: fixed; z-index: 99999; background: #141414; color: #fff; padding: 8px 12px; border-radius: 8px; font-size: 12px; pointer-events: none; opacity: 0; transition: opacity 0.15s; border: 1px solid #d99a32; box-shadow: 0 4px 12px rgba(0,0,0,0.5); max-width: 220px;">
    <div id="seatTooltipMaGhe" style="font-weight: 900; font-size: 14px; color: #f4c56a; margin-bottom: 2px;"></div>
    <div id="seatTooltipLoai" style="font-size: 11px; opacity: 0.9;"></div>
    <div id="seatTooltipPhuThu" style="font-size: 11px; opacity: 0.8;"></div>
</div>

<script>
(function() {
    const tooltip = document.getElementById('seatTooltip');
    const ttMaGhe = document.getElementById('seatTooltipMaGhe');
    const ttLoai = document.getElementById('seatTooltipLoai');
    const ttPhuThu = document.getElementById('seatTooltipPhuThu');
    let currentTooltipSeat = null;

    document.addEventListener('mouseover', function(e) {
        const seat = e.target.closest('.seat-interactive');
        if (!seat) return;
        if (seat === currentTooltipSeat) return; // đã hiện rồi, không hiện lại
        currentTooltipSeat = seat;
        const maGhe = seat.dataset.maGhe || '';
        const loaiGhe = seat.dataset.loaiGhe || 'Thường';
        const phuThu = parseInt(seat.dataset.phuThu || 0);
        const trangThai = seat.dataset.trangThai || '';

        ttMaGhe.textContent = 'Ghế ' + maGhe;
        let loaiText = 'Loại: ' + loaiGhe;
        if (trangThai === 'bao_tri') loaiText += ' (Bảo trì)';
        ttLoai.textContent = loaiText;
        ttPhuThu.textContent = 'Phụ thu: ' + phuThu.toLocaleString() + 'đ';

        tooltip.style.opacity = '1';
    });

    document.addEventListener('mousemove', function(e) {
        if (tooltip.style.opacity === '0') return;
        const offsetX = 14;
        const offsetY = 14;
        let x = e.clientX + offsetX;
        let y = e.clientY + offsetY;
        // Đảm bảo tooltip không tràn ra ngoài viewport
        const rect = tooltip.getBoundingClientRect();
        if (x + rect.width > window.innerWidth) x = e.clientX - rect.width - offsetX;
        if (y + rect.height > window.innerHeight) y = e.clientY - rect.height - offsetY;
        tooltip.style.left = x + 'px';
        tooltip.style.top = y + 'px';
    });

    document.addEventListener('mouseout', function(e) {
        const seat = e.target.closest('.seat-interactive');
        if (!seat) return;
        // Chỉ ẩn khi chuột rời hoàn toàn khỏi ghế (không phải di chuyển vào phần tử con)
        const related = e.relatedTarget;
        if (seat.contains(related)) return;
        tooltip.style.opacity = '0';
        currentTooltipSeat = null;
    });

    /* ============================================ */
    /* MODAL: THÊM GHẾ                              */
    /* ============================================ */
    const addSeatModal = document.getElementById('addSeatModal');
    const addSeatForm = document.getElementById('addSeatForm');
    const addSeatErrors = document.getElementById('addSeatModalErrors');
    const addSeatHangGhe = document.getElementById('addSeatHangGhe');
    const addSeatMaGhe = document.getElementById('addSeatMaGhe');
    const addSeatCot = document.getElementById('addSeatCot');
    const addSeatLoaiGhe = document.getElementById('addSeatLoaiGhe');
    const addSeatTrangThai = document.getElementById('addSeatTrangThai');
    const phongChieuIdAddSeat = document.getElementById('btnOpenAddSeat')?.dataset.phongId;

    function openAddSeatModal() {
        if (!addSeatModal) return;
        addSeatForm.reset();
        addSeatErrors.classList.add('hidden');
        addSeatErrors.innerHTML = '';
        addSeatModal.classList.remove('hidden');
        // Auto-suggest cột kế tiếp nếu đã chọn hàng
        addSeatHangGhe.dispatchEvent(new Event('change'));
    }

    function closeAddSeatModal() {
        addSeatModal.classList.add('hidden');
    }

    function showAddSeatErrors(errors) {
        addSeatErrors.classList.remove('hidden');
        const html = Object.values(errors).flat().map(e => `<div>• ${e}</div>`).join('');
        addSeatErrors.innerHTML = html;
    }

    // Tự động gợi ý cột kế tiếp + mã ghế khi chọn hàng
    addSeatHangGhe?.addEventListener('change', async function() {
        const hangId = this.value;
        if (!hangId || !phongChieuIdAddSeat) {
            addSeatCot.value = 1;
            addSeatMaGhe.value = '';
            return;
        }
        // Lấy danh sách hàng để biết tên hàng + số ghế hiện tại
        try {
            const res = await fetch(`/admin/phong-chieus/${phongChieuIdAddSeat}/hang-ghes`, {
                headers: { 'Accept': 'application/json' }
            });
            const json = await res.json();
            const hang = (json.data || []).find(h => String(h.id) === String(hangId));
            if (hang) {
                const nextCol = (hang.so_ghe || 0) + 1;
                addSeatCot.value = nextCol;
                addSeatMaGhe.value = hang.ten_hang + nextCol;
            }
        } catch (e) {
            console.error('Lỗi tải hàng:', e);
        }
    });

    document.getElementById('btnOpenAddSeat')?.addEventListener('click', openAddSeatModal);
    document.getElementById('addSeatModalClose')?.addEventListener('click', closeAddSeatModal);
    document.getElementById('addSeatCancel')?.addEventListener('click', closeAddSeatModal);
    addSeatModal?.addEventListener('click', function(e) {
        if (e.target === addSeatModal) closeAddSeatModal();
    });

    addSeatForm?.addEventListener('submit', async function(e) {
        e.preventDefault();
        addSeatErrors.classList.add('hidden');

        if (!phongChieuIdAddSeat) {
            showAddSeatErrors({ _global: ['Không xác định được ID phòng chiếu. Vui lòng tải lại trang.'] });
            return;
        }

        const payload = {
            hang_ghe_id: addSeatHangGhe.value,
            ma_ghe: addSeatMaGhe.value.trim(),
            cot: parseInt(addSeatCot.value, 10),
            loai_ghe_id: addSeatLoaiGhe.value,
            trang_thai: addSeatTrangThai.value,
        };

        if (!payload.hang_ghe_id || !payload.ma_ghe || !payload.cot || !payload.loai_ghe_id || !payload.trang_thai) {
            showAddSeatErrors({ _global: ['Vui lòng điền đầy đủ thông tin.'] });
            return;
        }

        // Validate: check trùng mã ghế trong DOM
        var existingSeat = document.querySelector(`.seat-interactive[data-ma-ghe="${payload.ma_ghe}"]`);
        if (existingSeat) {
            showAddSeatErrors({ _global: [`Ghế "${payload.ma_ghe}" đã tồn tại trong phòng.`] });
            return;
        }

        // Validate: check trùng cột trong hàng
        var rowForColCheck = document.querySelector(`.seat-row[data-hang-ghe-id="${payload.hang_ghe_id}"]`);
        if (rowForColCheck) {
            var existingCol = rowForColCheck.querySelector(`.seat-interactive[data-cot="${payload.cot}"]`);
            if (existingCol) {
                showAddSeatErrors({ _global: [`Cột ${payload.cot} đã có ghế "${existingCol.dataset.maGhe}" trong hàng này.`] });
                return;
            }
        }

        // Validate: check ràng buộc theo vị trí hàng
        var allRows = Array.from(document.querySelectorAll('.seat-row'));
        var rowEl = document.querySelector(`.seat-row[data-hang-ghe-id="${payload.hang_ghe_id}"]`);
        var rowIndex = rowEl ? allRows.indexOf(rowEl) : -1;
        var totalRows = allRows.length;
        var isTopThree = rowIndex >= 0 && rowIndex < 3;
        var isLastTwo = rowIndex >= 0 && rowIndex >= totalRows - 2;
        var selectedLoai = addSeatLoaiGhe.options[addSeatLoaiGhe.selectedIndex].textContent.toLowerCase();

        if (isTopThree && !selectedLoai.includes('thường')) {
            showAddSeatErrors({ _global: ['3 hàng gần màn chiếu chỉ được đặt ghế Thường.'] });
            return;
        }
        if (isLastTwo && !selectedLoai.includes('couple')) {
            showAddSeatErrors({ _global: ['2 hàng cuối chỉ được đặt ghế Couple.'] });
            return;
        }

        const csrfToken = getCsrfToken();
        if (!csrfToken) {
            showAddSeatErrors({ _global: ['Không tìm thấy CSRF token. Vui lòng tải lại trang (F5).'] });
            return;
        }

        const btn = document.getElementById('addSeatSubmit');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1.5"></i>Đang thêm...';

        try {
            const url = `/admin/phong-chieus/${phongChieuIdAddSeat}/create-seat`;
            console.log('[AddSeat] POST', url, payload);
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload),
                credentials: 'same-origin'
            });
            console.log('[AddSeat] Response status:', res.status);
            const data = await res.json().catch(() => ({ success: false, message: 'Server trả về response không phải JSON' }));
            if (!res.ok || !data.success) {
                if (data.errors) showAddSeatErrors(data.errors);
                else showAddSeatErrors({ _global: [data.message || `Lỗi HTTP ${res.status}`] });
                if (data.message) showToast(data.message, 'error');
                return;
            }
            closeAddSeatModal();
            location.reload();
        } catch (err) {
            console.error('[AddSeat] Fetch error:', err);
            const msg = err && err.message ? err.message : String(err);
            showAddSeatErrors({ _global: [`Lỗi kết nối: ${msg}. Vui lòng kiểm tra mạng và thử lại (F5 nếu cần).`] });
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    });

    /* ============================================ */
    /* MODAL: THÊM HÀNG                             */
    /* ============================================ */
    const addRowModal = document.getElementById('addRowModal');
    const addRowForm = document.getElementById('addRowForm');
    const addRowErrors = document.getElementById('addRowModalErrors');
    const addRowTenHang = document.getElementById('addRowTenHang');
    const addRowIsCouple = document.getElementById('addRowIsCouple');
    const addRowLoaiMacDinh = document.getElementById('addRowLoaiMacDinh');
    const addRowAuto = document.getElementById('addRowAuto');
    const addRowAutoBox = document.getElementById('addRowAutoBox');
    const addRowSoGhe = document.getElementById('addRowSoGhe');
    const addRowCotBatDau = document.getElementById('addRowCotBatDau');
    const addRowLoaiGhe = document.getElementById('addRowLoaiGhe');
    const addRowTrangThai = document.getElementById('addRowTrangThai');
    const addRowPreview = document.getElementById('addRowPreview');
    const phongChieuIdAddRow = document.getElementById('btnOpenAddRow')?.dataset.phongId;

    function updateAddRowPreview() {
        if (!addRowPreview) return;
        const ten = (addRowTenHang.value || 'A').trim();
        const so = parseInt(addRowSoGhe.value, 10) || 0;
        const start = parseInt(addRowCotBatDau.value, 10) || 1;
        if (so <= 0) { addRowPreview.textContent = `${ten}1, ${ten}2, ...`; return; }
        if (so <= 3) {
            const codes = [];
            for (let i = 0; i < so; i++) codes.push(ten + (start + i));
            addRowPreview.textContent = codes.join(', ');
        } else {
            addRowPreview.textContent = `${ten}${start}, ${ten}${start + 1}, ... ${ten}${start + so - 1}`;
        }
    }

    function updateAddRowAutoBox() {
        if (!addRowAutoBox) return;
        if (addRowAuto.checked) {
            addRowAutoBox.classList.remove('opacity-50', 'pointer-events-none');
        } else {
            addRowAutoBox.classList.add('opacity-50', 'pointer-events-none');
        }
    }

    function openAddRowModal() {
        if (!addRowModal) return;
        addRowForm.reset();
        addRowAuto.checked = true;
        addRowErrors.classList.add('hidden');
        addRowErrors.innerHTML = '';
        updateAddRowAutoBox();
        updateAddRowPreview();
        addRowModal.classList.remove('hidden');
    }

    function closeAddRowModal() {
        addRowModal.classList.add('hidden');
    }

    function showAddRowErrors(errors) {
        addRowErrors.classList.remove('hidden');
        const html = Object.values(errors).flat().map(e => `<div>• ${e}</div>`).join('');
        addRowErrors.innerHTML = html;
    }

    document.getElementById('btnOpenAddRow')?.addEventListener('click', openAddRowModal);
    document.getElementById('addRowModalClose')?.addEventListener('click', closeAddRowModal);
    document.getElementById('addRowCancel')?.addEventListener('click', closeAddRowModal);
    addRowModal?.addEventListener('click', function(e) {
        if (e.target === addRowModal) closeAddRowModal();
    });

    addRowTenHang?.addEventListener('input', updateAddRowPreview);
    addRowSoGhe?.addEventListener('input', updateAddRowPreview);
    addRowCotBatDau?.addEventListener('input', updateAddRowPreview);
    addRowAuto?.addEventListener('change', updateAddRowAutoBox);

    addRowForm?.addEventListener('submit', async function(e) {
        e.preventDefault();
        addRowErrors.classList.add('hidden');

        if (!phongChieuIdAddRow) {
            showAddRowErrors({ _global: ['Không xác định được ID phòng chiếu. Vui lòng tải lại trang.'] });
            return;
        }

        const tuDong = addRowAuto.checked;
        const soGhe = parseInt(addRowSoGhe.value, 10) || 0;
        const loaiGheId = addRowLoaiGhe.value;

        if (!addRowTenHang.value.trim()) {
            showAddRowErrors({ ten_hang: ['Vui lòng nhập tên hàng.'] });
            return;
        }

        // Validate: check trùng tên hàng trong DOM
        var tenHangVal = addRowTenHang.value.trim().toUpperCase();
        var existingRow = document.querySelector(`.seat-row[data-hang="'${tenHangVal}'"]`);
        if (existingRow) {
            showAddRowErrors({ ten_hang: [`Hàng "${tenHangVal}" đã tồn tại trong phòng.`] });
            return;
        }

        if (tuDong && (!soGhe || soGhe < 1)) {
            showAddRowErrors({ so_ghe: ['Vui lòng nhập số ghế khi bật tự động tạo.'] });
            return;
        }
        if (tuDong && !loaiGheId) {
            showAddRowErrors({ loai_ghe_id: ['Vui lòng chọn loại ghế khi bật tự động tạo.'] });
            return;
        }

        // Validate: check vị trí hàng mới dựa trên loại ghế
        var allRows = Array.from(document.querySelectorAll('.seat-row'));
        var totalRows = allRows.length;
        if (tuDong && loaiGheId) {
            var selectedLoai = addRowLoaiGhe.options[addRowLoaiGhe.selectedIndex].textContent.toLowerCase();
            var isCouple = selectedLoai.includes('couple');
            var isVip = selectedLoai.includes('vip');
            var isNormal = selectedLoai.includes('thường') || selectedLoai.includes('normal');

            // Hàng mới sẽ ở cuối
            var newRowIndex = totalRows;
            var isTopThree = newRowIndex < 3;
            var isLastTwo = newRowIndex >= totalRows - 2;

            if (isTopThree && !isNormal) {
                showAddRowErrors({ _global: ['3 hàng gần màn chiếu chỉ được đặt ghế Thường. Vui lòng chọn ghế Thường hoặc thêm hàng ở vị trí khác.'] });
                return;
            }
            if (isLastTwo && !isCouple) {
                showAddRowErrors({ _global: ['2 hàng cuối chỉ được đặt ghế Couple. Vui lòng chọn ghế Couple hoặc thêm hàng ở vị trí khác.'] });
                return;
            }
        }

        const payload = {
            ten_hang: addRowTenHang.value.trim(),
            la_hang_couple: addRowIsCouple.checked,
            loai_ghe_mac_dinh_id: addRowLoaiMacDinh.value || null,
            tu_dong_tao_ghe: tuDong,
            so_ghe: tuDong ? soGhe : 0,
            cot_bat_dau: parseInt(addRowCotBatDau.value, 10) || 1,
            loai_ghe_id: tuDong ? loaiGheId : null,
            trang_thai: addRowTrangThai.value || 'hoat_dong',
        };

        const csrfToken = getCsrfToken();
        if (!csrfToken) {
            showAddRowErrors({ _global: ['Không tìm thấy CSRF token. Vui lòng tải lại trang (F5).'] });
            return;
        }

        const btn = document.getElementById('addRowSubmit');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1.5"></i>Đang thêm...';

        try {
            const url = `/admin/phong-chieus/${phongChieuIdAddRow}/create-row`;
            console.log('[AddRow] POST', url, payload);
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload),
                credentials: 'same-origin'
            });
            console.log('[AddRow] Response status:', res.status);
            const data = await res.json().catch(() => ({ success: false, message: 'Server trả về response không phải JSON' }));
            if (!res.ok || !data.success) {
                if (data.errors) showAddRowErrors(data.errors);
                else showAddRowErrors({ _global: [data.message || `Lỗi HTTP ${res.status}`] });
                if (data.message) showToast(data.message, 'error');
                return;
            }
            closeAddRowModal();
            location.reload();
        } catch (err) {
            console.error('[AddRow] Fetch error:', err);
            const msg = err && err.message ? err.message : String(err);
            showAddRowErrors({ _global: [`Lỗi kết nối: ${msg}. Vui lòng kiểm tra mạng và thử lại (F5 nếu cần).`] });
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    });

    // ESC để đóng modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            var addSeatModal = document.getElementById('addSeatModal');
            var addRowModal = document.getElementById('addRowModal');
            var rowChangeModal = document.getElementById('rowChangeModal');
            if (addSeatModal) addSeatModal.classList.add('hidden');
            if (addRowModal) addRowModal.classList.add('hidden');
            if (rowChangeModal) rowChangeModal.classList.add('hidden');
        }
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.custom-select')) {
            document.querySelectorAll('.custom-select__trigger.active').forEach(t => {
                t.classList.remove('active');
            });
            document.querySelectorAll('.custom-select__dropdown').forEach(d => {
                d.style.display = 'none';
            });
        }
    });
</script>

