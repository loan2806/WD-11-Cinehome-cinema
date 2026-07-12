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

                    @if($veXemPhim->trang_thai === 'da_thanh_toan' && $veXemPhim->canCancel())
                        <form method="POST" action="{{ route('user.ve_xem_phim.cancel', $veXemPhim) }}" onsubmit="return confirm('Hủy vé này và hoàn tiền theo chính sách?');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="myticket-cancel-btn">
                                <i class="fa-solid fa-ban"></i>
                                Hủy vé
                            </button>
                        </form>
                    @endif
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

        <div class="myticket-policy-card">
            <i class="fa-solid fa-circle-info"></i>
            <p>Vé chỉ được hủy trong vòng {{ $cancelMinutes }} phút sau khi đặt và khi chưa được sử dụng. Khi đến rạp, hãy mở QR vé này để nhân viên soát vé nhanh hơn.</p>
        </div>
    </div>
</section>
@endsection
