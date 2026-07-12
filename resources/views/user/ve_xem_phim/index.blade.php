@extends('layouts.user')

@section('title', 'Vé của tôi')

@section('content')
@php
    $statusMeta = [
        'da_thanh_toan' => [
            'label' => 'Đã thanh toán',
            'icon' => 'fa-solid fa-circle-check',
            'class' => 'is-paid',
            'description' => 'Sẵn sàng vào rạp',
        ],
        'da_su_dung' => [
            'label' => 'Đã sử dụng',
            'icon' => 'fa-solid fa-check-double',
            'class' => 'is-used',
            'description' => 'Vé đã được soát',
        ],
        'da_huy' => [
            'label' => 'Đã hủy',
            'icon' => 'fa-solid fa-circle-xmark',
            'class' => 'is-cancelled',
            'description' => 'Vé không còn hiệu lực',
        ],
    ];

    $filterItems = [
        ['status' => null, 'label' => 'Tất cả', 'count' => $ticketStats['total'] ?? $veXemPhims->total()],
        ['status' => 'da_thanh_toan', 'label' => 'Đã thanh toán', 'count' => $ticketStats['paid'] ?? 0],
        ['status' => 'da_su_dung', 'label' => 'Đã sử dụng', 'count' => $ticketStats['used'] ?? 0],
        ['status' => 'da_huy', 'label' => 'Đã hủy', 'count' => $ticketStats['cancelled'] ?? 0],
    ];
@endphp

<section class="mytickets-page">
    <div class="mytickets-shell">
        <div class="mytickets-hero">
            <div class="mytickets-hero-copy">
                <span class="mytickets-eyebrow">
                    <i class="fa-solid fa-ticket"></i>
                    CineHome e-ticket
                </span>
                <h1>Vé của tôi</h1>
                <p>Quản lý vé đã đặt, kiểm tra QR soát vé, theo dõi suất chiếu và hủy vé trong thời gian cho phép.</p>

                <div class="mytickets-hero-actions">
                    <a href="{{ route('dat_ve.chon_phim') }}" class="mytickets-primary-link">
                        <i class="fa-solid fa-plus"></i>
                        Đặt vé mới
                    </a>
                    <a href="{{ route('user.thanh-vien.index') }}" class="mytickets-secondary-link">
                        <i class="fa-solid fa-crown"></i>
                        Thẻ thành viên & điểm
                    </a>
                </div>
            </div>

            <aside class="mytickets-next-card">
                <span>Suất gần nhất</span>
                @if($nextTicket)
                    <strong>{{ $nextTicket->ten_phim }}</strong>
                    <p>{{ $nextTicket->thoi_gian_chieu?->format('H:i - d/m/Y') }}</p>
                    <small>
                        <i class="fa-solid fa-location-dot"></i>
                        {{ $nextTicket->ten_rap ?? 'CineHome Cinema' }}
                    </small>
                    <a href="{{ route('user.ve_xem_phim.show', $nextTicket) }}">
                        Mở vé điện tử
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                @else
                    <strong>Chưa có suất sắp tới</strong>
                    <p>Đặt vé mới để lịch xem phim của bạn xuất hiện tại đây.</p>
                    <small>
                        <i class="fa-solid fa-clock"></i>
                        Hủy vé trong {{ $cancelMinutes }} phút sau khi đặt
                    </small>
                @endif
            </aside>
        </div>

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

        <div class="mytickets-stats">
            <article>
                <span>Tổng vé</span>
                <strong>{{ number_format($ticketStats['total'] ?? $veXemPhims->total()) }}</strong>
                <small>Tất cả giao dịch</small>
            </article>
            <article>
                <span>Đang hiệu lực</span>
                <strong>{{ number_format($ticketStats['paid'] ?? 0) }}</strong>
                <small>Có thể mở QR</small>
            </article>
            <article>
                <span>Đã dùng</span>
                <strong>{{ number_format($ticketStats['used'] ?? 0) }}</strong>
                <small>Lịch sử xem phim</small>
            </article>
            <article>
                <span>Đã hủy</span>
                <strong>{{ number_format($ticketStats['cancelled'] ?? 0) }}</strong>
                <small>Đã xử lý hoàn tiền</small>
            </article>
        </div>

        <section class="mytickets-board">
            <div class="mytickets-board-head">
                <div>
                    <span>Danh sách vé</span>
                    <h2>{{ $activeStatus ? ($statusMeta[$activeStatus]['label'] ?? 'Vé đã lọc') : 'Tất cả vé đã đặt' }}</h2>
                </div>

                <nav class="mytickets-filter" aria-label="Lọc vé theo trạng thái">
                    @foreach($filterItems as $item)
                        <a
                            href="{{ $item['status'] ? route('user.ve_xem_phim.index', ['trang_thai' => $item['status']]) : route('user.ve_xem_phim.index') }}"
                            class="{{ $activeStatus === $item['status'] ? 'is-active' : '' }}"
                        >
                            {{ $item['label'] }}
                            <b>{{ number_format($item['count']) }}</b>
                        </a>
                    @endforeach
                </nav>
            </div>

            <div class="mytickets-list">
                @forelse ($veXemPhims as $veXemPhim)
                    @php
                        $meta = $statusMeta[$veXemPhim->trang_thai] ?? [
                            'label' => $veXemPhim->trang_thai,
                            'icon' => 'fa-solid fa-circle-info',
                            'class' => 'is-neutral',
                            'description' => 'Đang cập nhật',
                        ];
                        $seats = collect(explode(',', (string) $veXemPhim->ma_ghe))
                            ->map(fn ($seat) => trim($seat))
                            ->filter()
                            ->values();
                    @endphp

                    <article class="myticket-card {{ $meta['class'] }}">
                        <div class="myticket-code">
                            <span>Mã vé</span>
                            <strong>{{ $veXemPhim->ma_ve }}</strong>
                            <small>{{ $veXemPhim->created_at?->format('d/m/Y H:i') }}</small>
                        </div>

                        <div class="myticket-movie">
                            <h3>{{ $veXemPhim->ten_phim }}</h3>
                            <p>
                                <i class="fa-solid fa-location-dot"></i>
                                {{ $veXemPhim->ten_rap ?? 'CineHome Cinema' }}
                            </p>
                            <p>
                                <i class="fa-solid fa-door-open"></i>
                                {{ $veXemPhim->ten_phong ?? 'Phòng chiếu' }}
                            </p>
                        </div>

                        <div class="myticket-info-grid">
                            <div>
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
                                <span>Suất chiếu</span>
                                <strong>{{ $veXemPhim->thoi_gian_chieu?->format('H:i') ?? '--:--' }}</strong>
                                <small>{{ $veXemPhim->thoi_gian_chieu?->format('d/m/Y') ?? 'Đang cập nhật' }}</small>
                            </div>
                            <div>
                                <span>Tổng tiền</span>
                                <strong>{{ number_format($veXemPhim->tong_tien, 0, ',', '.') }}đ</strong>
                                @if($veXemPhim->tien_hoan > 0)
                                    <small class="is-refund">Hoàn {{ number_format($veXemPhim->tien_hoan, 0, ',', '.') }}đ</small>
                                @endif
                            </div>
                            <div>
                                <span>Trạng thái</span>
                                <strong class="myticket-status">
                                    <i class="{{ $meta['icon'] }}"></i>
                                    {{ $meta['label'] }}
                                </strong>
                                <small>{{ $meta['description'] }}</small>
                            </div>
                        </div>

                        <div class="myticket-actions">
                            <a href="{{ route('user.ve_xem_phim.show', $veXemPhim) }}" class="myticket-detail-btn">
                                <i class="fa-solid fa-qrcode"></i>
                                Chi tiết
                            </a>

                            @if($veXemPhim->trang_thai === 'da_thanh_toan' && $veXemPhim->canCancel())
                                <form method="POST" action="{{ route('user.ve_xem_phim.cancel', $veXemPhim) }}" onsubmit="return confirm('Bạn có chắc muốn hủy vé này không?');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="myticket-cancel-btn">
                                        <i class="fa-solid fa-ban"></i>
                                        Hủy vé
                                    </button>
                                </form>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="mytickets-empty">
                        <span>
                            <i class="fa-solid fa-ticket"></i>
                        </span>
                        <h3>Chưa có vé nào</h3>
                        <p>Chọn một bộ phim yêu thích, đặt suất phù hợp và vé điện tử sẽ xuất hiện tại đây.</p>
                        <a href="{{ route('dat_ve.chon_phim') }}" class="mytickets-primary-link">
                            <i class="fa-solid fa-plus"></i>
                            Đặt vé ngay
                        </a>
                    </div>
                @endforelse
            </div>

            @if($veXemPhims->hasPages())
                <div class="mytickets-pagination">
                    <div class="mytickets-page-summary">
                        Hiển thị
                        <strong>{{ $veXemPhims->firstItem() }}</strong>
                        -
                        <strong>{{ $veXemPhims->lastItem() }}</strong>
                        trong
                        <strong>{{ $veXemPhims->total() }}</strong>
                        vé
                    </div>

                    <nav class="mytickets-page-controls" aria-label="Phân trang vé">
                        @if($veXemPhims->onFirstPage())
                            <span class="mytickets-page-link is-disabled">
                                <i class="fa-solid fa-chevron-left"></i>
                                Trước
                            </span>
                        @else
                            <a href="{{ $veXemPhims->previousPageUrl() }}" class="mytickets-page-link">
                                <i class="fa-solid fa-chevron-left"></i>
                                Trước
                            </a>
                        @endif

                        @foreach($veXemPhims->getUrlRange(max(1, $veXemPhims->currentPage() - 2), min($veXemPhims->lastPage(), $veXemPhims->currentPage() + 2)) as $page => $url)
                            @if($page === $veXemPhims->currentPage())
                                <span class="mytickets-page-link is-current">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="mytickets-page-link">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if($veXemPhims->hasMorePages())
                            <a href="{{ $veXemPhims->nextPageUrl() }}" class="mytickets-page-link">
                                Sau
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        @else
                            <span class="mytickets-page-link is-disabled">
                                Sau
                                <i class="fa-solid fa-chevron-right"></i>
                            </span>
                        @endif
                    </nav>
                </div>
            @endif
        </section>
    </div>
</section>
@endsection
