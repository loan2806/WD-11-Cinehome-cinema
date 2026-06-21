@extends('layouts.admin')
@section('content')

    <div class="admin-panel">

        <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

            <div>

                <h5 class="text-2xl font-black text-white">
                    Chi tiết suất chiếu
                </h5>

                <small class="text-gray-400">
                    Thông tin chi tiết suất chiếu
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
                        <p class="text-white">{{ $suatChieu->thoi_gian_chieu->format('d/m/Y') }}</p>
                    </div>

                    <div>
                        <small class="text-xs text-gray-500">Giờ Bắt Đầu</small>
                        <p class="text-white">{{ $suatChieu->thoi_gian_chieu->format('H:i') }}</p>
                    </div>

                    <div>
                        <small class="text-xs text-gray-500">Giờ Kết Thúc</small>
                        <p class="text-white">{{ $suatChieu->thoi_gian_ket_thuc ? $suatChieu->thoi_gian_ket_thuc->format('H:i') : '-' }}</p>
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
                                                        $seatClass = 'seat-chip seat-chip--regular';
                                                        if ($ghe['loai_ghe'] === 'VIP') $seatClass = 'seat-chip seat-chip--vip';
                                                        if ($ghe['loai_ghe'] === 'Couple') $seatClass = 'seat-chip seat-chip--couple seat-chip--wide';
                                                        if ($ghe['trang_thai'] === 'bao_tri') $seatClass = 'seat-chip seat-chip--maintenance';
                                                        $label = $j;
                                                        if ($ghe['is_couple'] && !empty($ghe['cot_end'])) {
                                                            $label = $j . '-' . $ghe['cot_end'];
                                                            $j = (int) $ghe['cot_end'];
                                                        }
                                                    @endphp
                                                    <div class="{{ $seatClass }}"
                                                         title="{{ $ghe['ma_ghe'] }} - {{ $ghe['loai_ghe'] }}">
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

                        <div class="seat-legend">
                            <span class="seat-legend__item">
                                <span class="seat-chip seat-chip--regular">A</span>
                                Ghế thường
                            </span>
                            <span class="seat-legend__item">
                                <span class="seat-chip seat-chip--vip">V</span>
                                Ghế VIP
                            </span>
                            <span class="seat-legend__item">
                                <span class="seat-chip seat-chip--couple">C</span>
                                Ghế Couple
                            </span>
                            <span class="seat-legend__item">
                                <span class="seat-chip seat-chip--maintenance">M</span>
                                Bảo trì
                            </span>
                        </div>

                    </div>
                @else
                    <p class="py-8 text-center text-gray-500">Phòng chưa có ghế.</p>
                @endif

            </div>

        </div>

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