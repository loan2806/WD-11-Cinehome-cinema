@extends('layouts.admin')

@section('page-title', 'Chi tiết suất chiếu')
@section('page-subtitle', 'Sơ đồ ghế và thông số lấp đầy phòng thời gian thực')

@push('styles')
<style>
    /* 🌟 CẤU TRÚC SƠ ĐỒ GHẾ ĐỒNG BỘ 100% VỚI GIAO DIỆN NGƯỜI DÙNG */
    .booking-theater {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
        padding: 10px 0;
    }

    /* Màn hình cong */
    .booking-screen-wrap {
        width: 100%;
        max-width: 580px;
        margin: 0 auto 28px auto;
        text-align: center;
    }

    .screen-line {
        height: 32px;
        border-top: 4px solid #f7b84b;
        border-radius: 50% 50% 0 0 / 100% 100% 0 0;
        background: linear-gradient(180deg, rgba(247, 184, 75, 0.35) 0%, transparent 100%);
        box-shadow: 0 -4px 16px rgba(247, 184, 75, 0.3);
    }

    .booking-screen-wrap span {
        display: block;
        color: #f7b84b;
        font-weight: 900;
        font-size: 11px;
        letter-spacing: 4px;
        text-transform: uppercase;
        margin-top: -18px;
    }

    /* Khung cuộn ngang sơ đồ ghế */
    .booking-seat-scroll {
        width: 100%;
        overflow-x: auto;
        padding: 10px 0 18px 0;
        display: flex;
        justify-content: center;
    }

    /* Custom Scrollbar cao cấp */
    .booking-seat-scroll::-webkit-scrollbar {
        height: 6px;
    }

    .booking-seat-scroll::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 999px;
        margin: 0 40px;
    }

    .booking-seat-scroll::-webkit-scrollbar-thumb {
        background: linear-gradient(90deg, #f7b84b, #e11d48);
        border-radius: 999px;
    }

    .booking-seat-scroll::-webkit-scrollbar-thumb:hover {
        background: #f7b84b;
    }

    /* Ma trận ghế */
    .booking-seat-grid {
        display: inline-flex;
        flex-direction: column;
        gap: 10px;
        min-width: fit-content;
        align-items: center;
    }

    .seat-grid-row {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        width: 100%;
    }

    .seat-row-label {
        color: #f7b84b;
        font-weight: 800;
        font-size: 13px;
        width: 20px;
        text-align: center;
        flex-shrink: 0;
    }

    .seat-row-cells {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Ô ghế cơ sở */
    .seat-box {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
        flex-shrink: 0;
        transition: transform 0.15s ease;
        user-select: none;
    }

    .seat-box:hover {
        transform: scale(1.08);
    }

    /* 1. Ghế Thường (Màu xám đậm chuẩn) */
    .seat-box--normal {
        background: #374151;
        color: #ffffff;
        border: 1px solid #4b5563;
    }

    /* 2. Ghế VIP (Màu vàng Gold) */
    .seat-box--vip {
        background: #eab308;
        color: #000000;
        font-weight: 800;
        border: 1px solid #fde047;
    }

    /* 3. Ghế Couple (Màu hồng đỏ gộp đôi) */
    .seat-box--couple {
        width: 86px !important;
        background: #e11d48;
        color: #ffffff;
        font-weight: 800;
        border: 1px solid #fda4af;
        font-size: 10px;
    }

    /* 4. Ghế Đã đặt (Tối màu có dấu X giống ảnh người dùng) */
    .seat-box--booked {
        background: #1e293b;
        color: #64748b;
        border: 1px solid #334155;
        cursor: not-allowed;
    }

    /* 5. Ghế Bảo trì */
    .seat-box--maintenance {
        background: #18181b;
        color: #52525b;
        border: 1px solid #27272a;
    }

    .seat-box--empty-space {
        opacity: 0;
        pointer-events: none;
    }

    /* Chú thích trạng thái bên dưới */
    .booking-seat-legend {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 24px;
        margin-top: 24px;
        padding-top: 18px;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        width: 100%;
        color: #9ca3af;
        font-size: 12px;
        font-weight: 600;
    }

    .booking-seat-legend span {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .seat-swatch {
        display: inline-block;
        width: 18px;
        height: 18px;
        border-radius: 5px;
    }

    .seat-swatch.is-empty {
        background: #374151;
        border: 1px solid #4b5563;
    }

    .seat-swatch.is-vip {
        background: #eab308;
        border: 1px solid #fde047;
    }

    .seat-swatch.is-couple {
        background: #e11d48;
        width: 26px;
        border: 1px solid #fda4af;
    }

    .seat-swatch.is-booked {
        background: #1e293b;
        border: 1px solid #334155;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        font-size: 10px;
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
                    <p class="text-xl font-black text-red-400">{{ number_format($suatChieu->gia_ve_cuoi_cung) }}đ</p>
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

        <!-- 🌟 SƠ ĐỒ GHẾ - ĐỒNG BỘ CHÍNH XÁC VỚI TRANG ĐẶT VÉ NGƯỜI DÙNG -->
        <div class="lg:col-span-8 rounded-2xl border border-white/10 bg-[#151515] p-6">
            <h6 class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-400 text-center">
                Sơ đồ Ghế - {{ $suatChieu->phongChieu->ten_phong ?? 'Phòng chiếu' }}
            </h6>

            @if ($suatChieu->phongChieu && $suatChieu->phongChieu->gheNgois->count() > 0)
                <div class="booking-theater">
                    <div class="booking-screen-wrap" aria-hidden="true">
                        <div class="screen-line"></div>
                        <span>MÀN HÌNH</span>
                    </div>

                    <div class="booking-seat-scroll">
                        <div class="booking-seat-grid">
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
                                                    $type = mb_strtoupper($ghe['loai_ghe'] ?? 'THƯỜNG');
                                                    $isVip = str_contains($type, 'VIP');
                                                    $isCouple = !empty($ghe['is_couple']) || str_contains($type, 'COUPLE') || str_contains($type, 'ĐÔI') || str_contains($type, 'DOI');
                                                    $isBooked = ($ghe['trang_thai'] ?? '') === 'da_dat';
                                                    $isMaintenance = ($ghe['trang_thai'] ?? '') === 'bao_tri';

                                                    // Mã ghế hiển thị dạng A1, A2, E4...
                                                    $seatCode = $ghe['ma_ghe'] ?? ($hang->ten_hang . $j);

                                                    // Phân loại Class
                                                    $boxClass = 'seat-box';
                                                    if ($isBooked) {
                                                        $boxClass .= ' seat-box--booked';
                                                    } elseif ($isMaintenance) {
                                                        $boxClass .= ' seat-box--maintenance';
                                                    } elseif ($isCouple) {
                                                        $boxClass .= ' seat-box--couple';
                                                    } elseif ($isVip) {
                                                        $boxClass .= ' seat-box--vip';
                                                    } else {
                                                        $boxClass .= ' seat-box--normal';
                                                    }

                                                    // Xử lý nhãn gộp Ghế Đôi (vd: H1 | H2)
                                                    $displayLabel = $seatCode;
                                                    if ($isCouple) {
                                                        $endCol = $ghe['cot_end'] ?? ($j + 1);
                                                        $nextCode = $hang->ten_hang . $endCol;
                                                        $displayLabel = $seatCode . ' | ' . $nextCode;
                                                        $j = (int)$endCol; // Bỏ qua cột tiếp theo vì đã gộp
                                                    }
                                                @endphp

                                                <div class="{{ $boxClass }}" title="{{ $seatCode }} ({{ $ghe['loai_ghe'] ?? 'Ghế' }})">
                                                    @if($isBooked)
                                                        <i class="fa-solid fa-xmark" style="font-size: 13px;"></i>
                                                    @else
                                                        {{ $displayLabel }}
                                                    @endif
                                                </div>
                                                @php $j++; @endphp
                                            @else
                                                <div class="seat-box seat-box--empty-space"></div>
                                                @php $j++; @endphp
                                            @endif
                                        @endwhile
                                    </div>

                                    <span class="seat-row-label">{{ $hang->ten_hang }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- CHÚ THÍCH TRẠNG THÁI GHẾ -->
                    <div class="booking-seat-legend">
                        <span><i class="seat-swatch is-empty"></i> Ghế trống</span>
                        <span><i class="seat-swatch is-vip"></i> Ghế VIP</span>
                        <span><i class="seat-swatch is-couple"></i> Ghế Couple</span>
                        <span><i class="seat-swatch is-booked"><i class="fa-solid fa-xmark"></i></i> Đã đặt / đang giữ</span>
                    </div>
                </div>
            @else
                <p class="py-12 text-center text-gray-500">Phòng chiếu này chưa được cấu hình danh sách ghế.</p>
            @endif
        </div>
    </div>
</div>
@endsection