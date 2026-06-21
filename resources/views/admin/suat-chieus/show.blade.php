@extends('layouts.admin')
@section('content')

    <div class="admin-panel">

        <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

            <div>

                <h5 class="text-2xl font-black text-white">
                    Chi tiết suất chiếu
                </h5>

                <small class="text-gray-400">
                    Thông tin chi tiết suất chiếu và trạng thái đặt vé thời gian thực
                </small>

            </div>

            <div class="flex gap-3">

                <a href="{{ route('admin.suat-chieus.edit', $suatChieu) }}"
                    class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 py-3 text-sm font-bold text-white shadow-lg transition hover:scale-[1.02]">

                    <i class="fa-solid fa-pen"></i>

                    Sửa

                </a>

                <a href="{{ route('admin.suat-chieus.index') }}"
                    class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/10">

                    <i class="fa-solid fa-arrow-left"></i>

                    Quay lại

                </a>

            </div>

        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">

            <div class="rounded-2xl border border-white/10 bg-[#151515] p-6">

                <h6 class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-400">
                    Thông tin suất chiếu
                </h6>

                <div class="space-y-4">

                    <div>
                        <small class="text-xs text-gray-500">Phim</small>
                        <p class="text-lg font-bold text-white">{{ $suatChieu->phim->ten_phim ?? 'N/A' }}</p>
                    </div>

                    <div>
                        <small class="text-xs text-gray-500">Rạp</small>
                        <p class="text-white">{{ $suatChieu->rapChieuPhim->ten_rap ?? 'N/A' }}</p>
                    </div>

                    <div>
                        <small class="text-xs text-gray-500">Phòng Chiếu</small>
                        <p class="text-white">{{ $suatChieu->phongChieu->ten_phong ?? 'N/A' }}</p>
                    </div>

                    <div>
                        <small class="text-xs text-gray-500">Ngày Chiếu</small>
                        <p class="text-white">{{ $suatChieu->thoi_gian_chieu ? \Carbon\Carbon::parse($suatChieu->thoi_gian_chieu)->format('d/m/Y') : '—' }}</p>
                    </div>

                    <div>
                        <small class="text-xs text-gray-500">Giờ Bắt Đầu</small>
                        <p class="text-white">{{ $suatChieu->thoi_gian_chieu ? \Carbon\Carbon::parse($suatChieu->thoi_gian_chieu)->format('H:i') : '—' }}</p>
                    </div>

                    <div>
                        <small class="text-xs text-gray-500">Giờ Kết Thúc</small>
                        <p class="text-white">{{ $suatChieu->thoi_gian_ket_thuc ? \Carbon\Carbon::parse($suatChieu->thoi_gian_ket_thuc)->format('H:i') : '-' }}</p>
                    </div>

                    <div>
                        <small class="text-xs text-gray-500">Thời Lượng</small>
                        <p class="text-white">{{ $suatChieu->thoi_luong ?? 'N/A' }} phút</p>
                    </div>

                    <div>
                        <small class="text-xs text-gray-500">Giá Vé</small>
                        <p class="text-xl font-bold text-red-400">{{ number_format($suatChieu->gia_ve) }}đ</p>
                    </div>

                    <div>
                        <small class="text-xs text-gray-500">Trạng Thái</small>
                        <p class="text-white">
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold
                                {{ $suatChieu->trang_thai === 'dang_chieu' ? 'bg-green-500/15 text-green-300' : '' }}
                                {{ $suatChieu->trang_thai === 'sap_chieu' ? 'bg-blue-500/15 text-blue-300' : '' }}
                                {{ $suatChieu->trang_thai === 'da_chieu' ? 'bg-gray-500/15 text-gray-300' : '' }}
                                {{ $suatChieu->trang_thai === 'huy' ? 'bg-red-500/15 text-red-300' : '' }}">

                                {{ \App\Models\SuatChieu::TRANG_THAI_LIST[$suatChieu->trang_thai] ?? $suatChieu->trang_thai }}

                            </span>
                        </p>
                    </div>

                    <div>
                        <small class="text-xs text-gray-500">Ngày Tạo</small>
                        <p class="text-white">{{ $suatChieu->created_at ? $suatChieu->created_at->format('d/m/Y H:i') : '—' }}</p>
                    </div>

                </div>

            </div>

            <div class="rounded-2xl border border-white/10 bg-[#151515] p-6">

                <h6 class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-400">
                    Sơ đồ Ghế - Phòng {{ $suatChieu->phongChieu->ten_phong ?? '' }}
                </h6>

                @if ($suatChieu->phongChieu && $suatChieu->phongChieu->gheNgois->count() > 0)
                    <div class="text-center">

                        <div class="seat-map-wrapper">
                            <div class="seat-map-inner">

                                <div class="screen-bar">
                                    <span class="screen-bar__label">MÀN HÌNH</span>
                                </div>

                                @php
                                    $hangGhes = $suatChieu->phongChieu->hangGhes->sortBy('ten_hang');
                                    
                                    // BẢO VỆ AN TOÀN DUY TRÌ MA TRẬN KHÔNG LỖI: Tự động khởi tạo cấu trúc trống nếu chưa gộp Controller
                                    if (!isset($seatMap)) {
                                        $seatMap = [];
                                        foreach ($suatChieu->phongChieu->gheNgois as $g) {
                                            $tHang = $g->hangGhe->ten_hang ?? '';
                                            $vCot = (int)($g->vi_tri_cot ?? 1);
                                            if ($tHang) {
                                                $seatMap[$tHang][$vCot] = [
                                                    'ma_ghe' => $g->ma_ghe,
                                                    'loai_ghe' => $g->loaiGhe->ten_loai ?? 'Regular',
                                                    'trang_thai' => $g->trang_thai,
                                                    'is_couple' => ($g->loaiGhe->ten_loai ?? '') === 'Couple',
                                                    'cot_end' => $g->cot_end ?? null
                                                ];
                                            }
                                        }
                                    }

                                    if (!isset($soCot) || empty($soCot)) {
                                        $soCot = (int)$suatChieu->phongChieu->gheNgois->max('vi_tri_cot') ?: 12;
                                    }
                                @endphp

                                @foreach ($hangGhes as $hang)
                                    <div class="seat-row">

                                        <span class="seat-row__label">{{ $hang->ten_hang }}</span>

                                        <div class="seat-row__seats">
                                            @php
                                                $j = 1;
                                            @endphp
                                            @while($j <= $soCot)
                                                @if(isset($seatMap[$hang->ten_hang][$j]))
                                                    @php
                                                        $ghe = $seatMap[$hang->ten_hang][$j];
                                                        
                                                        // 1. Phân định lớp CSS cơ bản dựa trên loại thiết kế vật lý của ghế
                                                        $seatClass = 'seat-chip seat-chip--regular';
                                                        if (($ghe['loai_ghe'] ?? '') === 'VIP') $seatClass = 'seat-chip seat-chip--vip';
                                                        if (($ghe['loai_ghe'] ?? '') === 'Couple') $seatClass = 'seat-chip seat-chip--couple seat-chip--wide';
                                                        if (($ghe['trang_thai'] ?? '') === 'bao_tri') $seatClass = 'seat-chip seat-chip--maintenance';
                                                        
                                                        // 2. GHI ĐÈ TRẠNG THÁI LIÊN KẾT ĐẶT VÉ CỦA USER (Nâng cấp thông minh)
                                                        $titleText = ($ghe['ma_ghe'] ?? '') . ' - ' . ($ghe['loai_ghe'] ?? '');
                                                        
                                                        if (($ghe['trang_thai'] ?? '') === 'da_dat') {
                                                            // Đổi màu đỏ thẫm/khóa hiển thị mờ khi ghế đã thanh toán
                                                            $seatClass = 'seat-chip bg-red-600/80 text-white font-normal line-through opacity-50 cursor-not-allowed shadow-none border border-red-500';
                                                            $titleText .= ' (ĐÃ BÁN)';
                                                        } elseif (($ghe['trang_thai'] ?? '') === 'dang_giu') {
                                                            // Đổi màu cam nhấp nháy khi có user đang chọn giữ ghế tạm thời ngoài frontend
                                                            $seatClass = 'seat-chip bg-orange-500 text-white font-bold animate-pulse border border-orange-400';
                                                            $titleText .= ' (ĐANG GIỮ CHỖ)';
                                                        }

                                                        $label = $j;
                                                        if (!empty($ghe['is_couple']) && !empty($ghe['cot_end'])) {
                                                            $label = $j . '-' . $ghe['cot_end'];
                                                            $j = (int) $ghe['cot_end'];
                                                        }
                                                    @endphp
                                                    <div class="{{ $seatClass }}" title="{{ $titleText }}">
                                                        {{ $label }}
                                                    </div>
                                                    @php $j++; @endphp
                                                @else
                                                    <div class="seat-chip seat-chip--empty"></div>
                                                    @php $j++; @endphp
                                                @endif
                                            @endwhile
                                        </div>

                                        <span class="seat-row__label">{{ $hang->ten_hang }}</span>

                                    </div>
                                @endforeach

                            </div>
                        </div>

                        {{-- DANH MỤC CHÚ THÍCH: Mở rộng đồng bộ các trạng thái kinh doanh thực tế --}}
                        <div class="seat-legend mt-5 flex flex-wrap justify-center gap-x-4 gap-y-2 text-xs text-gray-400">
                            <span class="seat-legend__item flex items-center gap-1.5">
                                <span class="inline-block w-4 h-4 rounded text-center text-[10px] leading-4 bg-gray-600 text-white">1</span>
                                Ghế thường
                            </span>
                            <span class="seat-legend__item flex items-center gap-1.5">
                                <span class="inline-block w-4 h-4 rounded text-center text-[10px] leading-4 bg-yellow-600 text-white">1</span>
                                Ghế VIP
                            </span>
                            <span class="seat-legend__item flex items-center gap-1.5">
                                <span class="inline-block w-7 h-4 rounded text-center text-[10px] leading-4 bg-pink-600 text-white">1-2</span>
                                Ghế Couple
                            </span>
                            <span class="seat-legend__item flex items-center gap-1.5">
                                <span class="inline-block w-4 h-4 rounded text-center text-[10px] leading-4 bg-red-700 text-white">M</span>
                                Bảo trì
                            </span>
                            <span class="seat-legend__item flex items-center gap-1.5">
                                <span class="inline-block w-4 h-4 rounded bg-red-600/80 border border-red-500 line-through opacity-50"></span>
                                <b class="text-red-400">Ghế đã bán (User đặt)</b>
                            </span>
                            <span class="seat-legend__item flex items-center gap-1.5">
                                <span class="inline-block w-4 h-4 rounded bg-orange-500 border border-orange-400 animate-pulse"></span>
                                <b class="text-orange-400">Đang giữ ghế</b>
                            </span>
                        </div>

                    </div>
                @else
                    <p class="py-8 text-center text-gray-500">Phòng chưa có cấu hình danh sách ghế vật lý.</p>
                @endif

            </div>

        </div>

    </div>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6 bg-white/5 p-6 rounded-xl border border-white/5">
        <div>
            <p class="text-gray-400">Phim trình chiếu: <span class="text-white font-semibold">{{ $suatChieu->phim->ten_phim ?? 'N/A' }}</span></p>
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

@endsection