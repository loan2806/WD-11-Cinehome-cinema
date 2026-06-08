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
<div id="bulkActionsToolbar" class="hidden mb-6 rounded-2xl border border-[#d99a32]/30 bg-[#0f0f0f] p-4">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#d99a32]/20">
                    <i class="fa-solid fa-check text-sm text-[#d99a32]"></i>
                </div>
                <div>
                    <span class="text-sm font-bold text-white"><span id="selectedCount">0</span> ghế</span>
                    <span class="ml-1 text-sm text-gray-500">đã chọn</span>
                </div>
            </div>
            <button type="button" id="clearSelection" class="text-xs text-gray-500 hover:text-white transition">
                <i class="fa-solid fa-xmark mr-1"></i>Bỏ chọn
            </button>
        </div>
        <div class="flex flex-wrap items-center gap-3">
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
                                            @if($isCouple)
                                                {{-- Couple seat: 2 seats merged into 1 wide block with | separator --}}
                                                <div class="seat-chip seat-interactive seat-chip--couple {{ $isMaintenance ? 'seat-chip--maintenance' : '' }}"
                                                    @if(!$isMaintenance) style="background-color: {{ $bgColor }};" @endif
                                                    data-ghe-id="{{ $ghe['id'] }}"
                                                    data-ma-ghe="{{ $ghe['ma_ghe'] }}"
                                                    data-loai-ghe="{{ $ghe['loai_ghe'] ?? 'Couple' }}"
                                                    data-loai-ghe-id="{{ $ghe['loai_ghe_id'] ?? '' }}"
                                                    data-mau-sac="{{ $ghe['mau_sac'] ?? '#666666' }}"
                                                    data-phu-thu="{{ $ghe['phu_thu'] ?? 0 }}"
                                                    data-trang-thai="{{ $ghe['trang_thai'] }}"
                                                    title="{{ $ghe['ma_ghe'] }} - {{ $ghe['loai_ghe'] ?? 'Couple' }} ({{ number_format($ghe['phu_thu'] ?? 0) }}đ)">
                                                    <span class="seat-couple-left">{{ $tenHang }}{{ $j }}</span>
                                                    <span class="seat-couple-sep">|</span>
                                                    <span class="seat-couple-right">{{ $tenHang }}{{ $ghe['cot_end'] }}</span>
                                                </div>
                                                @php $j = ($ghe['cot_end'] ?? ($j+1)) + 1; @endphp
                                            @else
                                                {{-- Normal seat: single seat with column number --}}
                                                <div class="seat-chip seat-interactive
                                                    {{ $isMaintenance ? 'seat-chip--maintenance' : '' }}"
                                                    @if(!$isMaintenance) style="background-color: {{ $bgColor }};" @endif
                                                    data-ghe-id="{{ $ghe['id'] }}"
                                                    data-ma-ghe="{{ $ghe['ma_ghe'] }}"
                                                    data-loai-ghe="{{ $ghe['loai_ghe'] ?? 'Thường' }}"
                                                    data-loai-ghe-id="{{ $ghe['loai_ghe_id'] ?? '' }}"
                                                    data-mau-sac="{{ $ghe['mau_sac'] ?? '#666666' }}"
                                                    data-phu-thu="{{ $ghe['phu_thu'] ?? 0 }}"
                                                    data-trang-thai="{{ $ghe['trang_thai'] }}"
                                                    title="{{ $ghe['ma_ghe'] }} - {{ $ghe['loai_ghe'] ?? 'Thường' }} ({{ number_format($ghe['phu_thu'] ?? 0) }}đ)">
                                                    {{ $tenHang }}{{ $j }}
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
        <div class="mb-5">
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
        <div class="flex gap-3">
            <button type="button" id="rowChangeModalCancel" class="flex-1 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-medium text-white transition hover:bg-white/10">
                <i class="fa-solid fa-xmark mr-1.5"></i>Hủy
            </button>
            <button type="button" id="rowChangeModalApply" class="flex-1 rounded-xl bg-[#d99a32] px-4 py-3 text-sm font-bold text-black transition hover:bg-[#e5a847] hover:scale-[1.01]">
                <i class="fa-solid fa-check mr-1.5"></i>Áp dụng
            </button>
        </div>
    </div>
</div>

{{-- SEAT INFO POPOVER --}}
<div id="seatInfoPopover" class="seat-info-popover">
    <div class="seat-info-popover__seat">
        <div id="popoverColorDot" class="h-12 w-12 rounded-xl border border-white/15 shadow-lg"></div>
    </div>
    <div class="seat-info-popover__info">
        <div id="popoverMaGhe" class="text-lg font-black text-white"></div>
        <div id="popoverLoaiGhe" class="text-xs font-semibold uppercase tracking-wider text-gray-400"></div>
        <div id="popoverPhuThu" class="text-sm font-bold text-[#d99a32]"></div>
    </div>
    <div class="seat-info-popover__status" id="popoverStatus"></div>
    <div class="seat-info-popover__actions">
        <button type="button" id="popoverBtnChangeType" class="seat-popover-btn seat-popover-btn--primary">
            <i class="fa-solid fa-repeat mr-1.5"></i>Đổi loại
        </button>
        <button type="button" id="popoverBtnMaintenance" class="seat-popover-btn seat-popover-btn--secondary">
            <i class="fa-solid fa-wrench mr-1.5"></i>Bảo trì
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

    /* Seat Chip - LARGER SIZE */
    .seat-chip {
        position: relative;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 800;
        color: #fff;
        letter-spacing: 0.5px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.1);
        flex-shrink: 0;
    }
    .seat-chip--empty {
        width: 44px;
        visibility: hidden;
        pointer-events: none;
    }
    .seat-chip--maintenance {
        background: rgba(239, 68, 68, 0.25) !important;
        border: 2px solid #ef4444;
        color: #fca5a5;
        font-weight: 900;
        font-size: 11px;
    }

    /* Seat Interaction */
    .seat-interactive {
        width: 44px;
        cursor: pointer;
        user-select: none;
    }
    .seat-interactive:hover {
        transform: scale(1.2) translateY(-2px);
        z-index: 10;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4), 0 0 20px rgba(217, 154, 50, 0.3);
    }
    .seat-chip--selected,
    .seat-interactive.selected {
        outline: 3px solid #d99a32 !important;
        outline-offset: 3px;
        transform: scale(1.1);
        box-shadow: 0 0 20px rgba(217, 154, 50, 0.4), 0 4px 15px rgba(0, 0, 0, 0.3);
        z-index: 5;
    }

    /* Couple seats wider - 2 seats merged */
    .seat-chip--couple {
        width: 100px;
        gap: 2px;
        padding: 0 6px;
        border-radius: 12px;
        overflow: hidden;
    }
    .seat-chip--couple::after {
        content: '';
        position: absolute;
        top: 25%;
        bottom: 25%;
        left: 50%;
        width: 2px;
        background: rgba(255, 255, 255, 0.35);
        border-radius: 1px;
        transform: translateX(-50%);
        pointer-events: none;
    }
    .seat-chip--couple .seat-couple-left,
    .seat-chip--couple .seat-couple-right {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 0;
    }
    .seat-chip--couple .seat-couple-sep {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 12px;
        color: rgba(255, 255, 255, 0.55);
        font-weight: 900;
        font-size: 14px;
        flex-shrink: 0;
        user-select: none;
    }

    /* ==================== POPOVER STYLES ==================== */
    .seat-info-popover {
        position: fixed;
        z-index: 9999;
        min-width: 240px;
        border-radius: 20px;
        background: #141414;
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 30px 70px rgba(0, 0, 0, 0.7);
        color: #ffffff;
        padding: 16px;
        opacity: 0;
        transform: translateY(8px) scale(0.95);
        pointer-events: none;
        transition: opacity .2s ease, transform .2s ease;
    }
    .seat-info-popover.is-visible {
        opacity: 1;
        transform: translateY(0) scale(1);
        pointer-events: auto;
    }
    .seat-info-popover__seat {
        display: flex;
        justify-content: center;
        margin-bottom: 14px;
    }
    #popoverColorDot {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .seat-info-popover__info {
        text-align: center;
        margin-bottom: 14px;
        padding-bottom: 14px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }
    #popoverMaGhe {
        font-size: 20px;
        font-weight: 900;
        letter-spacing: 1px;
    }
    #popoverLoaiGhe {
        margin-top: 4px;
        font-size: 11px;
    }
    #popoverPhuThu {
        margin-top: 6px;
        font-size: 13px;
        font-weight: 800;
    }
    .seat-info-popover__status {
        margin-bottom: 14px;
        text-align: center;
    }
    .seat-info-popover__actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .seat-popover-btn {
        width: 100%;
        border-radius: 12px;
        padding: 10px 14px;
        font-size: 13px;
        font-weight: 700;
        border: none;
        cursor: pointer;
        transition: all .2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .seat-popover-btn--primary {
        background: #d99a32;
        color: #000;
    }
    .seat-popover-btn--primary:hover {
        background: #e5a847;
        transform: translateY(-1px);
    }
    .seat-popover-btn--secondary {
        background: rgba(255, 255, 255, 0.07);
        color: #e0e0e0;
    }
    .seat-popover-btn--secondary:hover {
        background: rgba(255, 255, 255, 0.12);
    }
    .seat-popover-btn--active {
        background: rgba(34, 197, 94, 0.15) !important;
        color: #22c55e !important;
        border: 1px solid rgba(34, 197, 94, 0.3) !important;
    }
    .seat-info-popover__type-selector {
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
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
        seatEl.dataset.loaiGhe = loaiGhe;
        seatEl.dataset.mauSac = mauSac;
        seatEl.dataset.trangThai = trangThai;
        seatEl.dataset.phuThu = phuThu;
        seatEl.title = seatEl.dataset.maGhe + ' - ' + loaiGhe + ' (' + Number(phuThu).toLocaleString() + 'đ)';
        if (trangThai === 'bao_tri') {
            seatEl.className = 'seat-chip seat-chip--maintenance seat-interactive';
            seatEl.style.backgroundColor = '';
        } else {
            seatEl.className = 'seat-chip seat-interactive';
            seatEl.style.backgroundColor = mauSac;
        }
    }

    function updateBulkToolbar() {
        const count = selectedSeats.size;
        if (count > 0) {
            bulkActionsToolbar.classList.remove('hidden');
            selectedCount.textContent = count;
        } else {
            bulkActionsToolbar.classList.add('hidden');
        }
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
        document.getElementById('popoverColorDot').style.backgroundColor = mauSac;

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
                ghe_ids: Array.from(selectedSeats),
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
        setBtnLoading(this, true);

        fetch(`/admin/phong-chieus/${phongChieuId}/bulk-update-seats`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                ghe_ids: Array.from(selectedSeats),
                action: 'toggle_maintenance'
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
            }
        })
        .catch(err => { console.error(err); alert('Có lỗi xảy ra'); })
        .finally(() => { setBtnLoading(this, false, '<i class="fa-solid fa-wrench mr-1.5"></i>Bảo trì'); });
    });

    // --- Seat Click ---
    document.querySelectorAll('.seat-interactive').forEach(seat => {
        seat.addEventListener('click', function(e) {
            const seatId = this.dataset.gheId;

            if (e.ctrlKey || e.metaKey) {
                // Multi-select
                if (selectedSeats.has(seatId)) {
                    selectedSeats.delete(seatId);
                    this.classList.remove('selected');
                } else {
                    selectedSeats.add(seatId);
                    this.classList.add('selected');
                }
                updateBulkToolbar();
            } else {
                // Single click - show popover
                currentSeatId = seatId;
                showPopover(this);
            }
        });

        seat.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            currentSeatId = this.dataset.gheId;
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
            body: JSON.stringify({ ghe_id: currentSeatId, loai_ghe_id: loaiGheId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const seatEl = findSeatEl(currentSeatId);
                if (seatEl) updateSeatDOM(seatEl, data.loai_ghe, data.mau_sac, data.trang_thai, data.phu_thu);
                document.getElementById('popoverTypeSelector').classList.add('hidden');
                document.getElementById('popoverBtnChangeType').classList.remove('hidden');
                document.getElementById('popoverBtnMaintenance').classList.remove('hidden');
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
            body: JSON.stringify({ ghe_id: currentSeatId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const seatEl = findSeatEl(currentSeatId);
                if (seatEl) updateSeatDOM(seatEl, seatEl.dataset.loaiGhe, seatEl.dataset.mauSac, data.trang_thai, seatEl.dataset.phuThu);
                showPopover(seatEl);
            }
        })
        .catch(err => { console.error(err); alert('Có lỗi xảy ra'); })
        .finally(() => { setBtnLoading(btn, false, '<i class="fa-solid fa-wrench mr-1.5"></i>Bảo trì'); });
    });

    // Hide popover on outside click
    document.addEventListener('click', function(e) {
        if (!popover.contains(e.target) && !e.target.classList.contains('seat-interactive')) {
            hidePopover();
        }
    });

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
});
</script>
@endpush
