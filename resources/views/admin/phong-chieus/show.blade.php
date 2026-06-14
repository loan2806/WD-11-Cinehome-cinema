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
                <div class="relative">
                    <select id="bulkLoaiGhe" class="appearance-none rounded-xl border border-white/10 bg-[#151515] pr-10 pl-4 py-2.5 text-sm text-white outline-none focus:border-[#d99a32] cursor-pointer min-w-[180px]">
                        <option value="">-- Chọn loại ghế --</option>
                        @foreach(\App\Models\LoaiGhe::all() as $loai)
                            <option value="{{ $loai->id }}" data-color="{{ $loai->mau_sac ?? '#666' }}">
                                {{ $loai->ten_loai }} (+{{ number_format($loai->phu_thu) }}đ)
                            </option>
                        @endforeach
                    </select>
                    <div id="bulkColorPreview" class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 rounded-lg border border-white/10 shadow-sm"></div>
                    <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-500 pointer-events-none"></i>
                </div>
            </div>
            <button type="button" id="btnToggleMaintenance" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-white/10">
                <i class="fa-solid fa-wrench mr-1.5"></i>Bảo trì
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
            @if($phongChieu->gheNgois->count() > 0)
                <div class="mb-4 rounded-xl border border-yellow-500/30 bg-yellow-500/10 p-3 text-sm text-yellow-400">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                    Phòng đã có <strong>{{ $phongChieu->gheNgois->count() }}</strong> ghế. Tạo mới sẽ xóa toàn bộ ghế cũ.
                </div>
            @endif
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
                            @foreach(\App\Models\LoaiGhe::all() as $loai)
                                <option value="{{ $loai->id }}">{{ $loai->ten_loai }} (+{{ number_format($loai->phu_thu) }}đ)</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-medium text-gray-400">Loại ghế VIP</label>
                        <select name="loai_ghe_vip_id"
                            class="w-full rounded-xl border border-white/10 bg-[#151515] px-4 py-2.5 text-sm text-white outline-none transition focus:border-[#d99a32] focus:ring-1 focus:ring-[#d99a32]/30">
                            <option value="">-- Không có VIP --</option>
                            @foreach(\App\Models\LoaiGhe::all() as $loai)
                                <option value="{{ $loai->id }}">{{ $loai->ten_loai }} (+{{ number_format($loai->phu_thu) }}đ)</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-medium text-gray-400">Loại ghế Couple</label>
                        <select name="loai_ghe_couple_id"
                            class="w-full rounded-xl border border-white/10 bg-[#151515] px-4 py-2.5 text-sm text-white outline-none transition focus:border-[#d99a32] focus:ring-1 focus:ring-[#d99a32]/30">
                            <option value="">-- Không có Couple --</option>
                            @foreach(\App\Models\LoaiGhe::all() as $loai)
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
                                                    data-couple-group="{{ $coupleGroupId }}"
                                                    data-couple-siblings='@json($siblings)'
                                                    data-couple-position="left"
                                                    title="{{ $ghe['ma_ghe'] }} - {{ $ghe['loai_ghe'] ?? 'Couple' }} ({{ number_format($ghe['phu_thu'] ?? 0) }}đ)">
                                                    <span class="seat-couple-left seat-label" style="color: #1a0b04 !important; font-weight: 900 !important; font-size: 13px !important; z-index: 10 !important; position: relative !important;">{{ $tenHang }}{{ $ghe['display_number'] }}</span>
                                                    @if($gheRight)
                                                        <span class="seat-couple-sep" style="color: rgba(0,0,0,0.45) !important; font-weight: 900 !important; font-size: 14px !important; z-index: 10 !important; position: relative !important; margin: 0 2px !important; user-select: none;">|</span>
                                                        <span class="seat-couple-right seat-label" style="color: #1a0b04 !important; font-weight: 900 !important; font-size: 13px !important; z-index: 10 !important; position: relative !important;">{{ $tenHang }}{{ $gheRight['display_number'] }}</span>
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
<div id="rowChangeModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/70 backdrop-blur-sm">
    <div class="w-full max-w-md rounded-2xl border border-white/10 bg-[#111111] p-6 shadow-2xl">
        <div class="mb-5 flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#d99a32]/15">
                <i class="fa-solid fa-palette text-xl text-[#d99a32]"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-white">
                    Đổi loại ghế hàng <span id="rowChangeModalTenHang" class="text-[#d99a32]"></span>
                </h3>
                <p class="text-xs text-gray-500"><span id="rowChangeModalRowIndex"></span></p>
            </div>
        </div>
        <div class="mb-4">
            <label class="mb-3 block text-xs font-medium uppercase tracking-wider text-gray-500">Chọn loại ghế mới</label>
            <div class="relative">
                <select id="rowChangeModalLoaiGhe" class="w-full appearance-none rounded-xl border border-white/10 bg-[#0f0f0f] px-4 py-3.5 pl-12 text-white outline-none focus:border-[#d99a32] cursor-pointer">
                    @foreach(\App\Models\LoaiGhe::all() as $loai)
                        <option value="{{ $loai->id }}" data-color="{{ $loai->mau_sac ?? '#666' }}">
                            {{ $loai->ten_loai }} — {{ number_format($loai->phu_thu) }}đ phụ thu
                        </option>
                    @endforeach
                </select>
                <div id="rowModalColorPreview" class="pointer-events-none absolute left-3.5 top-1/2 h-6 w-6 -translate-y-1/2 rounded-lg border border-white/10 shadow-sm"></div>
                <i class="fa-solid fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-gray-500 pointer-events-none"></i>
            </div>
            <p class="mt-2 text-xs text-gray-600">Thay đổi sẽ áp dụng cho <strong class="text-gray-400">tất cả ghế</strong> trong hàng này.</p>
        </div>

        {{-- Bảo trì cả hàng --}}
        <div class="mb-5 rounded-xl border border-white/10 bg-white/5 p-3">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-500/15">
                        <i class="fa-solid fa-wrench text-sm text-orange-400"></i>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-white">Bảo trì cả hàng</div>
                        <div class="text-[11px] text-gray-500">
                            <span id="rowMaintenanceStats">--</span>
                        </div>
                    </div>
                </div>
                <button type="button" id="rowMaintenanceBtn" onclick="window.__rowMaintClick && window.__rowMaintClick(event)" class="rounded-lg border border-orange-500/30 bg-orange-500/10 px-3 py-1.5 text-xs font-bold text-orange-300 transition hover:bg-orange-500/25 hover:border-orange-500/60">
                    <i class="fa-solid fa-wrench mr-1"></i><span id="rowMaintenanceBtnLabel">Bảo trì</span>
                </button>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="button" id="rowChangeModalDelete" class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm font-bold text-red-400 transition hover:bg-red-500/20 hover:border-red-500/50">
                <i class="fa-solid fa-trash-can mr-1.5"></i>Xóa hàng
            </button>
            <button type="button" id="rowChangeModalCancel" class="flex-1 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-medium text-white transition hover:bg-white/10">
                <i class="fa-solid fa-xmark mr-1.5"></i>Hủy
            </button>
            <button type="button" id="rowChangeModalApply" class="flex-1 rounded-xl bg-[#d99a32] px-4 py-3 text-sm font-bold text-black transition hover:bg-[#e5a847] hover:scale-[1.01]">
                <i class="fa-solid fa-check mr-1.5"></i>Áp dụng
            </button>
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
    {{-- Header gradient theo màu loại ghế --}}
    <div id="popoverHeader" class="seat-info-popover__header">
        <div class="seat-info-popover__header-overlay">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/20 backdrop-blur-sm">
                        <i class="fa-solid fa-chair text-white text-xs"></i>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-white/80">Ghế</span>
                </div>
                <span id="popoverHeaderMaGhe" class="rounded-md bg-black/30 px-2 py-0.5 text-[11px] font-black text-white backdrop-blur-sm"></span>
            </div>
        </div>
    </div>

    {{-- Hero: khối màu lớn + mã ghế + loại --}}
    <div class="seat-info-popover__hero">
        <div id="popoverColorBlock" class="seat-info-popover__color-block">
            <div class="seat-info-popover__color-shine"></div>
            <div class="seat-info-popover__color-content">
                <div id="popoverMaGhe" class="seat-info-popover__ma-ghe"></div>
                <div id="popoverLoaiGhe" class="seat-info-popover__loai-ghe"></div>
            </div>
        </div>
    </div>

    {{-- Phụ thu --}}
    <div class="seat-info-popover__phuthu-row">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-tag text-[#d99a32] text-xs"></i>
            <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-500">Phụ thu</span>
        </div>
        <div id="popoverPhuThu" class="text-sm font-black text-[#d99a32]"></div>
    </div>

    {{-- Status badge --}}
    <div class="seat-info-popover__status" id="popoverStatus"></div>

    {{-- Actions --}}
    <div class="seat-info-popover__actions">
        <button type="button" id="popoverBtnChangeType" class="seat-popover-btn seat-popover-btn--primary">
            <i class="fa-solid fa-repeat mr-1.5"></i>Đổi loại
        </button>
        <button type="button" id="popoverBtnMaintenance" class="seat-popover-btn seat-popover-btn--secondary">
            <i class="fa-solid fa-wrench mr-1.5"></i>Bảo trì
        </button>
        <button type="button" id="popoverBtnDelete" class="seat-popover-btn seat-popover-btn--danger">
            <i class="fa-solid fa-trash-can mr-1.5"></i>Xóa ghế
        </button>
    </div>
    <div id="popoverTypeSelector" class="seat-info-popover__type-selector hidden">
        <label class="mb-2 block text-xs font-medium uppercase tracking-wider text-gray-500">Chọn loại ghế</label>
        <div class="relative">
            <select id="popoverLoaiGheSelect" class="w-full appearance-none rounded-xl border border-white/10 bg-[#0f0f0f] px-4 py-3 pl-10 text-sm text-white outline-none focus:border-[#d99a32] cursor-pointer">
                @foreach(\App\Models\LoaiGhe::all() as $loai)
                    <option value="{{ $loai->id }}" data-color="{{ $loai->mau_sac ?? '#666' }}">
                        {{ $loai->ten_loai }} (+{{ number_format($loai->phu_thu) }}đ)
                    </option>
                @endforeach
            </select>
            <div id="popoverColorPreview" class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 rounded-lg border border-white/10 shadow-sm"></div>
        </div>
        <div class="mt-3 flex gap-2">
            <button type="button" id="popoverCancelType" class="flex-1 rounded-lg border border-white/10 bg-white/5 py-2 text-xs font-medium text-gray-400 transition hover:bg-white/10">Hủy</button>
            <button type="button" id="popoverConfirmType" class="flex-1 rounded-lg bg-[#d99a32] py-2 text-xs font-bold text-black transition hover:bg-[#e5a847]">Xác nhận</button>
        </div>
    </div>
</div>

@endsection

@push('styles')
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
    /* (CSS cũ .seat-chip--couple dòng 785-794 đã xóa - dùng rule mới ở phía dưới) */

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
    /* ==== GHẾ ĐANG ĐƯỢC CHỌN - HIỆU ỨNG GLOW + VIỀN SÁNG ==== */
    @keyframes seatPulse {
        0%, 100% {
            box-shadow:
                0 0 0 3px rgba(244, 197, 106, 0.95),
                0 0 0 5px rgba(217, 154, 50, 0.5),
                0 0 18px 4px rgba(244, 197, 106, 0.65),
                0 0 35px 8px rgba(217, 154, 50, 0.35),
                0 4px 12px rgba(0, 0, 0, 0.4);
        }
        50% {
            box-shadow:
                0 0 0 3px rgba(244, 197, 106, 1),
                0 0 0 7px rgba(217, 154, 50, 0.7),
                0 0 25px 6px rgba(244, 197, 106, 0.85),
                0 0 45px 12px rgba(217, 154, 50, 0.5),
                0 4px 14px rgba(0, 0, 0, 0.45);
        }
    }
    .seat-chip--selected,
    .seat-interactive.selected {
        position: relative;
        outline: none !important;
        transform: scale(1.08) translateY(-2px) !important;
        animation: seatPulse 1.4s ease-in-out infinite;
        z-index: 10 !important;
        border: 3px solid #fde68a !important;
        box-shadow:
            0 0 0 2px rgba(244, 197, 106, 0.6),
            0 0 0 5px rgba(217, 154, 50, 0.4),
            0 0 18px 4px rgba(244, 197, 106, 0.7) !important;
    }
    /* Vòng sáng bao quanh ghế - 3 lớp */
    .seat-interactive.selected::before {
        content: "";
        position: absolute;
        inset: -6px;
        border-radius: 18px 18px 10px 10px;
        background: radial-gradient(ellipse at center,
            rgba(244, 197, 106, 0.6) 0%,
            rgba(217, 154, 50, 0.4) 40%,
            rgba(217, 154, 50, 0) 70%);
        z-index: -1;
        pointer-events: none;
        animation: seatPulse 1.4s ease-in-out infinite;
    }
    /* Dấu check ✓ góc phải trên - hiệu ứng xuất hiện */
    @keyframes checkAppear {
        0% { transform: scale(0) rotate(-180deg); opacity: 0; }
        60% { transform: scale(1.3) rotate(20deg); opacity: 1; }
        100% { transform: scale(1) rotate(0); opacity: 1; }
    }
    .seat-interactive.selected::after {
        content: "✓";
        position: absolute;
        top: -10px;
        right: -10px;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: linear-gradient(135deg, #fde68a 0%, #f4c56a 50%, #d99a32 100%);
        color: #1a0b04;
        font-size: 13px;
        font-weight: 900;
        line-height: 22px;
        text-align: center;
        border: 2px solid #141414;
        box-shadow:
            0 0 10px rgba(244, 197, 106, 0.9),
            0 2px 6px rgba(0, 0, 0, 0.6);
        z-index: 11;
        animation: checkAppear 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
    }
    /* Ghế couple được chọn - vẫn giữ hiệu ứng */
    .seat-chip--couple.selected::after {
        right: -10px;
    }

    /* Couple: 1 div to duy nhất chứa 2 nhãn ghế (H1 | H2)
       Width = 208px (gấp đôi 104px trước) - dãn gấp đôi cả row H */
    .seat-chip--couple {
        width: 208px !important;
        gap: 0;
        padding: 0 16px 6px 16px !important;
        overflow: hidden;
    }
    /* Vạch dashed ở giữa ghế couple */
    .seat-chip--couple::after {
        content: '';
        position: absolute;
        top: 18%;
        bottom: 8px;
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
        font-size: 16px !important;
    }
    .seat-chip--couple .seat-couple-sep {
        display: flex;
        align-items: flex-end;
        justify-content: center;
        color: rgba(0, 0, 0, 0.4);
        font-weight: 900;
        font-size: 18px;
        padding-bottom: 1px;
        z-index: 5;
        position: relative;
        margin: 0 6px !important;
    }

    /* ==================== POPOVER STYLES ==================== */
    .seat-info-popover {
        position: fixed;
        z-index: 9999;
        width: 280px;
        border-radius: 20px;
        background: linear-gradient(180deg, #1a1a1a 0%, #0d0d0d 100%);
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow:
            0 30px 70px rgba(0, 0, 0, 0.8),
            0 0 0 1px rgba(217, 154, 50, 0.05),
            inset 0 1px 0 rgba(255, 255, 255, 0.05);
        color: #ffffff;
        padding: 0;
        overflow: hidden;
        opacity: 0;
        transform: translateY(8px) scale(0.95);
        pointer-events: none;
        transition: opacity .25s cubic-bezier(.16,1,.3,1), transform .25s cubic-bezier(.16,1,.3,1);
    }
    .seat-info-popover.is-visible {
        opacity: 1;
        transform: translateY(0) scale(1);
        pointer-events: auto;
    }

    /* Header gradient theo màu loại ghế */
    .seat-info-popover__header {
        position: relative;
        height: 70px;
        background: linear-gradient(135deg, var(--popover-color, #666) 0%, rgba(0, 0, 0, 0.4) 100%);
        overflow: hidden;
    }
    .seat-info-popover__header::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.15) 0%, transparent 50%),
            radial-gradient(circle at 20% 80%, rgba(0, 0, 0, 0.3) 0%, transparent 50%);
    }
    .seat-info-popover__header-overlay {
        position: relative;
        z-index: 1;
        padding: 14px 16px;
    }

    /* Hero block - khối màu lớn */
    .seat-info-popover__hero {
        padding: 0 16px;
        margin-top: -28px;
        position: relative;
        z-index: 2;
    }
    .seat-info-popover__color-block {
        position: relative;
        height: 90px;
        border-radius: 16px;
        background: var(--popover-color, #666);
        overflow: hidden;
        box-shadow:
            0 12px 30px rgba(0, 0, 0, 0.5),
            0 0 0 1px rgba(255, 255, 255, 0.1),
            inset 0 1px 0 rgba(255, 255, 255, 0.15);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .seat-info-popover__color-shine {
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.25) 0%, transparent 60%),
            radial-gradient(circle at 70% 80%, rgba(0, 0, 0, 0.2) 0%, transparent 60%);
        pointer-events: none;
    }
    .seat-info-popover__color-content {
        position: relative;
        z-index: 1;
        text-align: center;
        color: #fff;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
    }
    .seat-info-popover__ma-ghe {
        font-size: 28px;
        font-weight: 900;
        letter-spacing: 1.5px;
        line-height: 1;
        margin-bottom: 4px;
    }
    .seat-info-popover__loai-ghe {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        opacity: 0.95;
    }

    /* Phụ thu row */
    .seat-info-popover__phuthu-row {
        margin: 14px 16px 0;
        padding: 12px 14px;
        background: rgba(217, 154, 50, 0.08);
        border: 1px solid rgba(217, 154, 50, 0.15);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* Status badge */
    .seat-info-popover__status {
        margin: 14px 16px 0;
        text-align: center;
    }

    /* Actions */
    .seat-info-popover__actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 14px 16px 16px;
    }
    .seat-popover-btn {
        width: 100%;
        border-radius: 12px;
        padding: 11px 14px;
        font-size: 13px;
        font-weight: 700;
        border: none;
        cursor: pointer;
        transition: all .2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .seat-popover-btn i {
        font-size: 12px;
    }
    .seat-popover-btn--primary {
        background: linear-gradient(135deg, #d99a32 0%, #e5a847 100%);
        color: #000;
        box-shadow: 0 4px 12px rgba(217, 154, 50, 0.25);
    }
    .seat-popover-btn--primary:hover {
        background: linear-gradient(135deg, #e5a847 0%, #f0b95a 100%);
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(217, 154, 50, 0.4);
    }
    .seat-popover-btn--secondary {
        background: rgba(255, 255, 255, 0.06);
        color: #e0e0e0;
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .seat-popover-btn--secondary:hover {
        background: rgba(255, 255, 255, 0.12);
        border-color: rgba(255, 255, 255, 0.15);
    }
    .seat-popover-btn--danger {
        background: rgba(239, 68, 68, 0.1);
        color: #fca5a5;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }
    .seat-popover-btn--danger:hover {
        background: rgba(239, 68, 68, 0.22);
        color: #fff;
        border-color: rgba(239, 68, 68, 0.55);
        transform: translateY(-1px);
    }
    .seat-popover-btn--active {
        background: linear-gradient(135deg, rgba(34, 197, 94, 0.18) 0%, rgba(34, 197, 94, 0.1) 100%) !important;
        color: #4ade80 !important;
        border: 1px solid rgba(34, 197, 94, 0.35) !important;
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
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const phongChieuId = {{ $phongChieu->id }};
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
        if (isCouple) className += ' seat-chip--couple';
        if (isSelected) className += ' selected';
        seatEl.className = className;

        // Reset style + content
        if (trangThai === 'bao_tri') {
            seatEl.style.backgroundColor = '';
            seatEl.style.color = '#991b1b';
        } else {
            seatEl.style.backgroundColor = mauSac;
            seatEl.style.color = '#1a0b04';
        }
        // Tất cả ghế đều dùng chữ đen - không cần áp dụng auto contrast nữa
        // Đảm bảo label span bên trong giữ màu đen
        const labelSpans = seatEl.querySelectorAll('.seat-label, .seat-couple-left, .seat-couple-right');
        labelSpans.forEach(s => {
            s.style.color = trangThai === 'bao_tri' ? '#991b1b' : '#1a0b04';
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
                        if (!stillSelected) dom.classList.remove('selected');
                    } else {
                        if (!selectedSeats.has(dom.dataset.gheId)) dom.classList.remove('selected');
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

        // Status badge
        const statusEl = document.getElementById('popoverStatus');
        if (trangThai === 'bao_tri') {
            statusEl.innerHTML = '<span class="inline-flex items-center gap-1.5 rounded-full bg-red-500/15 px-3 py-1 text-xs font-bold text-red-400"><i class="fa-solid fa-wrench"></i> Đang bảo trì</span>';
        } else {
            statusEl.innerHTML = '<span class="inline-flex items-center gap-1.5 rounded-full bg-green-500/15 px-3 py-1 text-xs font-bold text-green-400"><i class="fa-solid fa-check-circle"></i> Hoạt động</span>';
        }

        // Maintenance button state
        const maintBtn = document.getElementById('popoverBtnMaintenance');
        if (trangThai === 'bao_tri') {
            maintBtn.innerHTML = '<i class="fa-solid fa-rotate mr-1.5"></i>Kích hoạt ghế';
            maintBtn.classList.add('seat-popover-btn--active');
        } else {
            maintBtn.innerHTML = '<i class="fa-solid fa-wrench mr-1.5"></i>Bảo trì';
            maintBtn.classList.remove('seat-popover-btn--active');
        }

        // Hide type selector
        document.getElementById('popoverTypeSelector').classList.add('hidden');
        document.getElementById('popoverBtnChangeType').classList.remove('hidden');
        document.getElementById('popoverBtnMaintenance').classList.remove('hidden');
        document.getElementById('popoverBtnDelete').classList.remove('hidden');

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
        document.querySelectorAll('.seat-interactive.selected').forEach(el => el.classList.remove('selected'));
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
            alert('Vui lòng chọn loại ghế để thay đổi.');
            return;
        }

        setBtnLoading(this, true);

        fetch(`/admin/phong-chieus/${phongChieuId}/bulk-update-seats`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
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
                document.querySelectorAll('.seat-interactive.selected').forEach(el => el.classList.remove('selected'));
                updateBulkToolbar();
                bulkLoaiGheSelect.value = '';
            }
        })
        .catch(err => { console.error(err); alert('Có lỗi xảy ra'); })
        .finally(() => { setBtnLoading(this, false, '<i class="fa-solid fa-check mr-1.5"></i>Áp dụng'); });
    });

    btnToggleMaintenance?.addEventListener('click', function() {
        if (selectedSeats.size === 0) return;
        const action = this.dataset.bulkAction || 'maintenance';
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
                action: action
            })
        })
        .then(async res => ({ status: res.status, ct: res.headers.get('content-type') || '', body: await res.text() }))
        .then(({ status, ct, body }) => {
            const data = ct.includes('application/json') ? JSON.parse(body) : { success: false, message: body };
            if (data.success && data.updated_seats) {
                data.updated_seats.forEach(item => {
                    const el = findSeatEl(item.id);
                    if (el) updateSeatDOM(el, item.loai_ghe, item.mau_sac, item.trang_thai, item.phu_thu);
                });
                selectedSeats.clear();
                document.querySelectorAll('.seat-interactive.selected').forEach(el => el.classList.remove('selected'));
                updateBulkToolbar();
            } else {
                alert('Có lỗi: ' + (data.message || status));
            }
        })
        .catch(err => { console.error(err); alert('Có lỗi xảy ra'); })
        .finally(() => { updateBulkMaintenanceBtnLabel(); setBtnLoading(this, false); });
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
            const arr = JSON.parse(raw);
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
            }
        });
        return Array.from(expanded);
    }

    document.querySelectorAll('.seat-interactive').forEach(seat => {
        seat.addEventListener('mouseenter', function() {
            showHoverTooltip(this);
        });
        seat.addEventListener('mouseleave', function() {
            hideHoverTooltip();
        });

        seat.addEventListener('click', function(e) {
            // Lấy tất cả ID ghế cùng cặp (1 hoặc 2 id)
            const siblingIds = getCoupleSiblings(this);
            const seatId = this.dataset.gheId;
            const isCoupleSeat = siblingIds.length > 1;

            if (e.ctrlKey || e.metaKey) {
                // Multi-select
                if (isCoupleSeat) {
                    // Toggle nguyên cặp: nếu đã chọn thì bỏ, ngược lại chọn
                    if (selectedSeats.has(seatId)) {
                        siblingIds.forEach(id => selectedSeats.delete(id));
                        this.classList.remove('selected');
                    } else {
                        siblingIds.forEach(id => selectedSeats.add(id));
                        this.classList.add('selected');
                    }
                } else {
                    if (selectedSeats.has(seatId)) {
                        selectedSeats.delete(seatId);
                        this.classList.remove('selected');
                    } else {
                        selectedSeats.add(seatId);
                        this.classList.add('selected');
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
    document.getElementById('popoverBtnChangeType').addEventListener('click', function() {
        const typeSelector = document.getElementById('popoverTypeSelector');
        const selectEl = document.getElementById('popoverLoaiGheSelect');
        typeSelector.classList.remove('hidden');
        this.classList.add('hidden');
        document.getElementById('popoverBtnMaintenance').classList.add('hidden');
        document.getElementById('popoverBtnDelete').classList.add('hidden');

        // Set current type
        if (currentSeatEl) {
            selectEl.value = currentSeatEl.dataset.loaiGheId || '';
        }
        const selected = selectEl.options[selectEl.selectedIndex];
        const preview = document.getElementById('popoverColorPreview');
        if (preview) preview.style.backgroundColor = selected?.dataset?.color || '#666';

        selectEl.focus();
    });

    document.getElementById('popoverLoaiGheSelect').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const preview = document.getElementById('popoverColorPreview');
        if (preview) preview.style.backgroundColor = selected?.dataset?.color || '#666';
    });

    document.getElementById('popoverCancelType').addEventListener('click', function() {
        document.getElementById('popoverTypeSelector').classList.add('hidden');
        document.getElementById('popoverBtnChangeType').classList.remove('hidden');
        document.getElementById('popoverBtnMaintenance').classList.remove('hidden');
        document.getElementById('popoverBtnDelete').classList.remove('hidden');
    });

    document.getElementById('popoverConfirmType').addEventListener('click', function() {
        const loaiGheId = document.getElementById('popoverLoaiGheSelect').value;
        const btn = this;
        setBtnLoading(btn, true);

        fetch(`/admin/phong-chieus/${phongChieuId}/update-seat-type`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                ghe_id: currentSeatSiblings.length > 1 ? null : currentSeatId,
                ghe_ids: currentSeatSiblings.length > 1 ? currentSeatSiblings : null,
                loai_ghe_id: loaiGheId
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Cập nhật tất cả ghế cùng cặp couple
                if (data.updated_seats) {
                    data.updated_seats.forEach(s => {
                        const el = findSeatEl(s.id);
                        if (el) updateSeatDOM(el, s.loai_ghe, s.mau_sac, s.trang_thai, s.phu_thu);
                    });
                } else {
                    const seatEl = findSeatEl(currentSeatId);
                    if (seatEl) updateSeatDOM(seatEl, data.loai_ghe, data.mau_sac, data.trang_thai, data.phu_thu);
                }
                document.getElementById('popoverTypeSelector').classList.add('hidden');
                document.getElementById('popoverBtnChangeType').classList.remove('hidden');
                document.getElementById('popoverBtnMaintenance').classList.remove('hidden');
                document.getElementById('popoverBtnDelete').classList.remove('hidden');
                const seatEl = findSeatEl(currentSeatId);
                showPopover(seatEl);
            }
        })
        .catch(err => { console.error(err); alert('Có lỗi xảy ra'); })
        .finally(() => { setBtnLoading(btn, false, 'Xác nhận'); });
    });

    document.getElementById('popoverBtnMaintenance').addEventListener('click', function() {
        const btn = this;
        setBtnLoading(btn, true);

        fetch(`/admin/phong-chieus/${phongChieuId}/toggle-seat-maintenance`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                ghe_id: currentSeatSiblings.length > 1 ? null : currentSeatId,
                ghe_ids: currentSeatSiblings.length > 1 ? currentSeatSiblings : null,
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Cập nhật tất cả ghế cùng cặp couple
                if (data.updated_seats) {
                    data.updated_seats.forEach(s => {
                        const el = findSeatEl(s.id);
                        if (el) updateSeatDOM(el, s.loai_ghe, s.mau_sac, s.trang_thai, s.phu_thu);
                    });
                }
                const seatEl = findSeatEl(currentSeatId);
                showPopover(seatEl);
            }
        })
        .catch(err => { console.error(err); alert('Có lỗi xảy ra'); })
        .finally(() => { setBtnLoading(btn, false, '<i class="fa-solid fa-wrench mr-1.5"></i>Bảo trì'); });
    });

    // --- Xóa ghế (đơn hoặc cả cặp couple) ---
    document.getElementById('popoverBtnDelete').addEventListener('click', function() {
        if (!currentSeatId) return;
        const btn = this;
        const targetCount = currentSeatSiblings.length > 1 ? currentSeatSiblings.length : 1;
        const label = currentSeatSiblings.length > 1
            ? 'Xóa cả cặp ghế couple này?'
            : 'Xóa ghế này?';
        if (!confirm(label + ' Hành động này không thể hoàn tác.')) return;

        setBtnLoading(btn, true);

        fetch(`/admin/phong-chieus/${phongChieuId}/bulk-update-seats`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                ghe_ids: currentSeatSiblings.length > 1 ? currentSeatSiblings : [currentSeatId],
                action: 'delete',
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Reload để lấy seatMap mới
                location.reload();
            } else {
                alert(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(err => { console.error(err); alert('Có lỗi xảy ra'); })
        .finally(() => { setBtnLoading(btn, false, '<i class="fa-solid fa-trash-can mr-1.5"></i>Xóa ghế'); });
    });

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

            rowChangeModal.classList.remove('hidden');
        });
    });

    document.getElementById('rowChangeModalCancel').addEventListener('click', function() {
        rowChangeModal.classList.add('hidden');
    });

    document.getElementById('rowChangeModalApply').addEventListener('click', function() {
        const loaiGheId = rowModalLoaiGhe.value;
        const btn = this;
        setBtnLoading(btn, true);

        fetch(`/admin/phong-chieus/${phongChieuId}/update-row-seats`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
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
            }
        })
        .catch(err => { console.error(err); alert('Có lỗi xảy ra'); })
        .finally(() => { setBtnLoading(btn, false, '<i class="fa-solid fa-check mr-1.5"></i>Áp dụng'); rowChangeModal.classList.add('hidden'); });
    });

    rowChangeModal.addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });

    // --- Bảo trì cả hàng (toggle) ---
    // Lấy CSRF token: ưu tiên meta, fallback cookie XSRF-TOKEN (Laravel tự sinh)
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
    console.log('[DEBUG] CSRF token =', getCsrfToken().substring(0, 10) + '...');

    // Hàm được gọi bởi onclick inline trên nút #rowMaintenanceBtn
    // (Đặt trên window để inline onclick truy cập được)
    window.__rowMaintClick = async function(ev) {
        // ev có thể là event từ icon bên trong button
        const btn = ev.target.closest('#rowMaintenanceBtn') || document.getElementById('rowMaintenanceBtn');
        if (!btn) return;

        console.log('[rowMaintenanceBtn] click | hang=', currentRowHangId);

        if (!currentRowHangId) {
            alert('Vui lòng chọn hàng ghế trước.');
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
                alert('Server trả về HTML (status ' + res.status + '). Có thể CSRF token sai hoặc session hết hạn. Xem Console.');
                btn.innerHTML = originalHtml;
                return;
            }
            console.log('[rowMaintenanceBtn] data', data);

            if (!data.success) {
                alert(data.message || 'Có lỗi xảy ra');
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
            alert('Có lỗi xảy ra: ' + (err.message || err));
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
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ hang_ghe_id: currentRowHangId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(err => { console.error(err); alert('Có lỗi xảy ra'); })
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

    document.addEventListener('mouseover', function(e) {
        const seat = e.target.closest('.seat-interactive');
        if (!seat) return;
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
        tooltip.style.opacity = '0';
    });
})();
</script>
@endpush
