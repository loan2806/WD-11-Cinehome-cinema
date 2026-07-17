@extends('layouts.user')

@section('title', 'Chi tiết vé')

@section('content')
@php
    $statusMeta = [
        'da_thanh_toan' => [
            'label' => 'Đã thanh toán',
            'icon' => 'fa-solid fa-circle-check',
            'class' => 'is-paid',
        ],
        'da_su_dung' => [
            'label' => 'Đã sử dụng',
            'icon' => 'fa-solid fa-check-double',
            'class' => 'is-used',
        ],
        'da_huy' => [
            'label' => 'Đã hủy',
            'icon' => 'fa-solid fa-circle-xmark',
            'class' => 'is-cancelled',
        ],
        // 🌟 BỔ SUNG: Trạng thái "Hết hạn" hiển thị trong chi tiết vé
        'het_han' => [
            'label' => 'Hết hạn',
            'icon' => 'fa-solid fa-clock-rotate-left',
            'class' => 'is-cancelled',
        ],
    ];

    $meta = $statusMeta[$veXemPhim->trang_thai] ?? [
        'label' => $veXemPhim->trang_thai,
        'icon' => 'fa-solid fa-circle-info',
        'class' => 'is-neutral',
    ];
    $seats = collect(explode(',', (string) $veXemPhim->ma_ghe))
        ->map(fn ($seat) => trim($seat))
        ->filter()
        ->values();
@endphp

<section class="mytickets-page mytickets-detail-page">
    <div class="mytickets-shell is-narrow">
        @if(session('success'))
            <div class="mytickets-alert is-success">
                <i class="fa-solid fa-circle-check"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mytickets-alert is-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                {{ session('error') }}
            </div>
        @endif

        <a href="{{ route('user.ve_xem_phim.index') }}" class="mytickets-back-link">
            <i class="fa-solid fa-arrow-left"></i>
            Quay lại vé của tôi
        </a>

        <article class="myticket-detail-card {{ $meta['class'] }}">
            <div class="myticket-detail-qr">
                <div>
                    <img
                        src="https://api.qrserver.com/v1/create-qr-code/?size=240x240&data={{ urlencode($veXemPhim->ma_ve) }}"
                        alt="QR vé {{ $veXemPhim->ma_ve }}"
                    >
                </div>
                <span>Đưa mã này cho nhân viên soát vé</span>
            </div>

            <div class="myticket-detail-copy">
                <span class="myticket-status-badge">
                    <i class="{{ $meta['icon'] }}"></i>
                    {{ $meta['label'] }}
                </span>
                <h1>{{ $veXemPhim->ma_ve }}</h1>
                <p>{{ $veXemPhim->ten_phim }}</p>

                <div class="myticket-code-box">
                    <span>Mã soát vé</span>
                    <strong>{{ $veXemPhim->ma_ve }}</strong>
                </div>

                <div class="myticket-detail-actions">
                    <a href="{{ route('dat_ve.chon_phim') }}" class="mytickets-secondary-link">
                        <i class="fa-solid fa-plus"></i>
                        Đặt thêm vé
                    </a>
                </div>
            </div>
        </article>

        <section class="myticket-detail-grid">
            <div>
                <i class="fa-solid fa-film"></i>
                <span>Phim</span>
                <strong>{{ $veXemPhim->ten_phim }}</strong>
            </div>
            <div>
                <i class="fa-solid fa-location-dot"></i>
                <span>Rạp</span>
                <strong>{{ $veXemPhim->ten_rap ?? 'CineHome Cinema' }}</strong>
            </div>
            <div>
                <i class="fa-solid fa-door-open"></i>
                <span>Phòng</span>
                <strong>{{ $veXemPhim->ten_phong ?? 'Đang cập nhật' }}</strong>
            </div>
            <div>
                <i class="fa-solid fa-couch"></i>
                <span>Ghế</span>
                <strong>
                    @forelse($seats as $seat)
                        <em>{{ $seat }}</em>
                    @empty
                        ---
                    @endforelse
                </strong>
            </div>
            <div>
                <i class="fa-solid fa-clock"></i>
                <span>Suất chiếu</span>
                <strong>{{ $veXemPhim->thoi_gian_chieu?->format('H:i d/m/Y') ?? 'Đang cập nhật' }}</strong>
            </div>
            <div>
                <i class="fa-solid fa-money-bill-wave"></i>
                <span>Tổng tiền</span>
                <strong>{{ number_format($veXemPhim->tong_tien, 0, ',', '.') }}đ</strong>
            </div>
            <div>
                <i class="fa-solid fa-credit-card"></i>
                <span>Loại vé</span>
                <strong>{{ $veXemPhim->loai_ve === 'tai_quay' ? 'Tại quầy' : 'Trực tuyến' }}</strong>
            </div>
            <div>
                <i class="fa-solid fa-calendar-plus"></i>
                <span>Ngày đặt</span>
                <strong>{{ $veXemPhim->created_at?->format('H:i d/m/Y') }}</strong>
            </div>

            @if($veXemPhim->trang_thai === 'da_huy')
                <div class="is-refund-card">
                    <i class="fa-solid fa-rotate-left"></i>
                    <span>Tiền hoàn</span>
                    <strong>{{ number_format($veXemPhim->tien_hoan, 0, ',', '.') }}đ</strong>
                </div>
            @endif
        </section>

        @if(!empty($foodItems))
            <section class="myticket-detail-foods" style="margin-top: 24px; background: #141414; border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 20px; padding: 24px;">
                <h3 style="color: #facc15; font-size: 16px; font-weight: 800; text-transform: uppercase; margin-top: 0; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; letter-spacing: 0.5px;">
                    <i class="fa-solid fa-cookie-bite" style="color: #facc15;"></i> Đồ ăn & Combo mua kèm
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px;">
                    @foreach($foodItems as $item)
                        @php
                            $qty = $item['qty'] ?? $item['quantity'] ?? 1;
                            $price = $item['price'] ?? 0;
                            $name = $item['name'] ?? 'Đồ ăn';
                        @endphp
                        <div style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 16px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong style="color: #fff; display: block; font-size: 14px; font-weight: 700;">{{ $name }}</strong>
                                <span style="color: #9ca3af; font-size: 12px; display: block; margin-top: 4px;">Đơn giá: {{ number_format($price) }}đ</span>
                            </div>
                            <div style="text-align: right;">
                                <span style="background: #facc15; color: #000; font-weight: 900; padding: 4px 10px; border-radius: 8px; font-size: 13px;">x{{ $qty }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</section>
@endsection