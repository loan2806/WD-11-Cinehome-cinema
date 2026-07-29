@extends('layouts.admin')

@section('page-title', 'Chi tiết suất chiếu')
@section('page-subtitle', 'Sơ đồ ghế và thông số lấp đầy phòng thời gian thực')

@push('styles')
<style>
    /* CSS CẤU TRÚC SƠ ĐỒ GHẾ RẠP CHIẾU THEO HÀNG NGANG CHUẨN MA TRẬN */
    .seat-map-wrapper {
        width: 100%;
        overflow-x: auto;
        padding: 20px 0;
        display: flex;
        justify-content: center;
    }

    .seat-map-inner {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        min-width: fit-content;
    }

    .screen-arch {
        width: 100%;
        max-width: 650px;
        height: 32px;
        border-top: 4px solid #facc15;
        border-radius: 50% 50% 0 0 / 100% 100% 0 0;
        text-align: center;
        color: #facc15;
        font-weight: 900;
        font-size: 11px;
        letter-spacing: 4px;
        padding-top: 4px;
        background: linear-gradient(180deg, rgba(250, 204, 21, 0.2), transparent);
        margin-bottom: 30px;
    }

    .seat-grid-row {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 16px;
        width: 100%;
    }

    .seat-row-label {
        color: #facc15;
        font-weight: 800;
        font-size: 14px;
        width: 24px;
        text-align: center;
        flex-shrink: 0;
    }

    .seat-row-cells {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .seat-box {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 800;
        color: #ffffff;
        background: #27272a;
        border: 1px solid rgba(255,255,255,0.15);
        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        flex-shrink: 0;
    }

    .seat-box--vip {
        background: #ca8a04 !important;
        border-color: #facc15 !important;
        color: #000 !important;
    }

    .seat-box--couple {
        width: 80px !important;
        background: #db2777 !important;
        border-color: #f472b6 !important;
    }

    .seat-box--sold {
        background: rgba(220, 38, 38, 0.85) !important;
        border-color: #ef4444 !important;
        color: #ffffff !important;
        text-decoration: line-through;
        opacity: 0.65;
    }

    .seat-box--maintenance {
        background: #3f3f46 !important;
        color: #71717a !important;
    }
</style>
@endpush

@section('content')
<div class="admin-panel">
    <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4" style="margin-bottom: 24px;">
        <div>
            <h5 class="text-2xl font-black text-white">Chi tiết suất chiếu</h5>
            <small class="text-gray-400">Thông tin suất chiếu và sơ đồ đặt ghế thời gian thực</small>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('admin.suat-chieus.edit', $suatChieu) }}"
               class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 py-3 text-sm font-bold text-white shadow-lg transition hover:scale-[1.02]">
                <i class="fa-solid fa-pen"></i> Sửa
            </a>
            <a href="{{ route('admin.suat-chieus.index') }}"
               class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/10">
                <i class="fa-solid fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <!-- 🌟 4 CARD THỐNG KÊ GHẾ PHÒNG CHIẾU -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="rounded-2xl border border-white/10 bg-[#151515] p-5">
            <span class="text-xs text-gray-400 block mb-1 font-semibold">Tổng số ghế</span>
            <strong class="text-2xl font-black text-white">{{ number_format($tongSoGhe) }}</strong>
            <small class="block text-[11px] text-gray-500 mt-1">Sức chứa tối đa phòng</small>
        </div>

        <div class="rounded-2xl border border-red-500/30 bg-red-500/10 p-5">
            <span class="text-xs text-red-300 block mb-1 font-semibold">Đã được đặt</span>
            <strong class="text-2xl font-black text-red-400">{{ number_format($soGheDaDat) }}</strong>
            <small class="block text-[11px] text-red-300/70 mt-1">Vé đã thanh toán/giữ</small>
        </div>

        <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-5">
            <span class="text-xs text-emerald-300 block mb-1 font-semibold">Ghế trống</span>
            <strong class="text-2xl font-black text-emerald-400">{{ number_format($soGheTrong) }}</strong>
            <small class="block text-[11px] text-emerald-300/70 mt-1">Sẵn sàng đón khách</small>
        </div>

        <div class="rounded-2xl border border-amber-500/30 bg-amber-500/10 p-5">
            <span class="text-xs text-amber-300 block mb-1 font-semibold">Tỷ lệ lấp đầy</span>
            <strong class="text-2xl font-black text-amber-400">{{ $tyLeLapDay }}%</strong>
            <small class="block text-[11px] text-amber-300/70 mt-1">Hiệu suất kinh doanh</small>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <!-- THÔNG TIN SUẤT CHIẾU -->
        <div class="lg:col-span-4 rounded-2xl border border-white/10 bg-[#151515] p-6">
            <h6 class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-400">Thông tin suất chiếu</h6>
            
            <div class="space-y-4">
                <div>
                    <small class="text-xs text-gray-500">Phim</small>
                    <p class="text-lg font-bold text-white">{{ $suatChieu->phim->ten_phim ?? 'N/A' }}</p>
                </div>
                <div>
                    <small class="text-xs text-gray-500">Rạp chiếu</small>
                    <p class="text-white">{{ $suatChieu->rapChieuPhim->ten_rap ?? 'CineHome Cinema' }}</p>
                </div>
                <div>
                    <small class="text-xs text-gray-500">Phòng chiếu</small>
                    <p class="text-white font-semibold">{{ $suatChieu->phongChieu->ten_phong ?? 'N/A' }} ({{ strtoupper($suatChieu->phongChieu->loai_phong ?? '2D') }})</p>
                </div>
                <div>
                    <small class="text-xs text-gray-500">Ngày & Giờ chiếu</small>
                    <p class="text-amber-400 font-bold">
                        {{ $suatChieu->thoi_gian_chieu ? \Carbon\Carbon::parse($suatChieu->thoi_gian_chieu)->format('H:i - d/m/Y') : '—' }}
                    </p>
                </div>
                <div>
                    <small class="text-xs text-gray-500">Giá vé niêm yết</small>
                    <p class="text-xl font-black text-red-400">{{ number_format($suatChieu->gia_ve) }}đ</p>
                </div>
                <div>
                    <small class="text-xs text-gray-500">Trạng thái vận hành</small>
                    <p class="mt-1">
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                            {{ \App\Models\SuatChieu::TRANG_THAI_LIST[$suatChieu->trang_thai] ?? $suatChieu->trang_thai }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <!-- 🌟 SƠ ĐỒ GHẾ TRẢI THEO HÀNG NGANG CHUẨN MA TRẬN RẠP -->
        <div class="lg:col-span-8 rounded-2xl border border-white/10 bg-[#151515] p-6">
            <h6 class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-400 text-center">
                Sơ đồ Ghế - {{ $suatChieu->phongChieu->ten_phong ?? 'Phòng chiếu' }}
            </h6>

            @if ($suatChieu->phongChieu && $suatChieu->phongChieu->gheNgois->count() > 0)
                <div class="seat-map-wrapper">
                    <div class="seat-map-inner">
                        <div class="screen-arch">MÀN HÌNH</div>

                        @php
                            $hangGhes = $suatChieu->phongChieu->hangGhes->sortBy('ten_hang');
                        @endphp

                        @foreach ($hangGhes as $hang)
                            <div class="seat-grid-row">
                                <span class="seat-row-label">{{ $hang->ten_hang }}</span>

                                <div class="seat-row-cells">
                                    @php $j = 1; @endphp
                                    @while($j <= $soCot)
                                        @if(isset($seatMap[$hang->ten_hang][$j]))
                                            @php
                                                $ghe = $seatMap[$hang->ten_hang][$j];
                                                $boxClass = 'seat-box';
                                                
                                                if (($ghe['loai_ghe'] ?? '') === 'VIP') $boxClass .= ' seat-box--vip';
                                                if (($ghe['loai_ghe'] ?? '') === 'Couple') $boxClass .= ' seat-box--couple';
                                                if (($ghe['trang_thai'] ?? '') === 'da_dat') $boxClass .= ' seat-box--sold';
                                                if (($ghe['trang_thai'] ?? '') === 'bao_tri') $boxClass .= ' seat-box--maintenance';

                                                $label = $j;
                                                if (!empty($ghe['is_couple']) && !empty($ghe['cot_end'])) {
                                                    $label = $j . '-' . $ghe['cot_end'];
                                                    $j = (int) $ghe['cot_end'];
                                                }
                                            @endphp

                                            <div class="{{ $boxClass }}" title="{{ $ghe['ma_ghe'] }} ({{ $ghe['loai_ghe'] }})">
                                                {{ $label }}
                                            </div>
                                            @php $j++; @endphp
                                        @else
                                            <div class="seat-box" style="opacity: 0; pointer-events: none;"></div>
                                            @php $j++; @endphp
                                        @endif
                                    @endwhile
                                </div>

                                <span class="seat-row-label">{{ $hang->ten_hang }}</span>
                            </div>
                        @endforeach

                        <!-- CHÚ THÍCH TRẠNG THÁI GHẾ -->
                        <div class="flex flex-wrap justify-center gap-4 mt-6 text-xs text-gray-400 border-t border-white/10 pt-4 w-full">
                            <div class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-[#27272a] border border-white/10 inline-block"></span> Ghế thường</div>
                            <div class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-[#ca8a04] inline-block"></span> Ghế VIP</div>
                            <div class="flex items-center gap-1.5"><span class="w-6 h-4 rounded bg-[#db2777] inline-block"></span> Ghế Couple</div>
                            <div class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-red-600 opacity-70 line-through text-[10px] text-white flex items-center justify-center inline-block">x</span> Đã bán</div>
                        </div>
                    </div>
                </div>
            @else
                <p class="py-12 text-center text-gray-500">Phòng chiếu này chưa được cấu hình danh sách ghế.</p>
            @endif
        </div>
    </div>
</div>
@endsection