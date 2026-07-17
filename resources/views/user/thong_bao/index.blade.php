@extends('layouts.user')

@section('title', 'Thông báo của tôi')

@section('content')
@php
    $typeMeta = [
        'he_thong' => [
            'label' => 'Hệ thống',
            'icon' => 'fa-solid fa-circle-info',
            'class' => 'is-system',
        ],
        've' => [
            'label' => 'Vé xem phim',
            'icon' => 'fa-solid fa-ticket',
            'class' => 'is-ticket',
        ],
        'diem' => [
            'label' => 'Điểm thưởng',
            'icon' => 'fa-solid fa-star',
            'class' => 'is-point',
        ],
        'voucher' => [
            'label' => 'Voucher',
            'icon' => 'fa-solid fa-gift',
            'class' => 'is-voucher',
        ],
        'hang_thanh_vien' => [
            'label' => 'Hạng thành viên',
            'icon' => 'fa-solid fa-crown',
            'class' => 'is-rank',
        ],
    ];

    $filterItems = [
        ['type' => null, 'label' => 'Tất cả', 'count' => $notificationStats['total'] ?? $thongBaos->total()],
        ['type' => 've', 'label' => 'Vé', 'count' => $notificationStats['ve'] ?? 0],
        ['type' => 'diem', 'label' => 'Điểm', 'count' => $notificationStats['diem'] ?? 0],
        ['type' => 'voucher', 'label' => 'Voucher', 'count' => $notificationStats['voucher'] ?? 0],
        ['type' => 'hang_thanh_vien', 'label' => 'Hạng', 'count' => $notificationStats['hang_thanh_vien'] ?? 0],
        ['type' => 'he_thong', 'label' => 'Hệ thống', 'count' => $notificationStats['he_thong'] ?? 0],
    ];

    $currentFilter = collect($filterItems)->firstWhere('type', $activeType);
    $latestMeta = $latestUnread
        ? ($typeMeta[$latestUnread->loai_thong_bao] ?? $typeMeta['he_thong'])
        : $typeMeta['he_thong'];
@endphp

<section class="notification-page">
    <div class="notification-shell">
        <div class="notification-hero">
            <div class="notification-hero-copy">
                <span class="notification-eyebrow">
                    <i class="fa-solid fa-bell"></i>
                    Trung tâm thông báo
                </span>

                <h1>Thông báo của tôi</h1>
                <p>Theo dõi vé đã đặt, điểm thưởng, voucher và các cập nhật quan trọng từ CineHome trong một giao diện gọn, dễ quét và thao tác nhanh.</p>

                <div class="notification-hero-actions">
                    <a href="{{ route('user.ve_xem_phim.index') }}" class="notification-primary-link">
                        <i class="fa-solid fa-ticket"></i>
                        Xem vé của tôi
                    </a>
                    <a href="{{ route('user.voucher.my') }}" class="notification-secondary-link">
                        <i class="fa-solid fa-gift"></i>
                        Kho voucher
                    </a>
                </div>
            </div>

            <aside class="notification-highlight-card {{ $latestMeta['class'] }}">
                <span>
                    <i class="{{ $latestMeta['icon'] }}"></i>
                    {{ ($notificationStats['unread'] ?? 0) > 0 ? 'Thông báo mới' : 'Hộp thư đã gọn' }}
                </span>

                @if($latestUnread)
                    <strong>{{ $latestUnread->tieu_de }}</strong>
                    <p>{{ $latestUnread->noi_dung }}</p>
                    <small>
                        <i class="fa-solid fa-clock"></i>
                        {{ $latestUnread->created_at?->diffForHumans() }}
                    </small>
                @else
                    <strong>Không có thông báo chưa đọc</strong>
                    <p>Khi có thay đổi về vé, điểm hoặc voucher, CineHome sẽ đưa thông tin nổi bật lên đây.</p>
                    <small>
                        <i class="fa-solid fa-circle-check"></i>
                        Tất cả đã được cập nhật
                    </small>
                @endif
            </aside>
        </div>

        <div class="notification-stats">
            <article>
                <span>Tất cả</span>
                <strong>{{ number_format($notificationStats['total'] ?? $thongBaos->total()) }}</strong>
                <small>Tổng thông báo</small>
            </article>
            <article>
                <span>Chưa đọc</span>
                <strong>{{ number_format($notificationStats['unread'] ?? 0) }}</strong>
                <small>Tự đánh dấu sau khi mở trang</small>
            </article>
            <article>
                <span>Vé & voucher</span>
                <strong>{{ number_format(($notificationStats['ve'] ?? 0) + ($notificationStats['voucher'] ?? 0)) }}</strong>
                <small>Ưu tiên kiểm tra trước</small>
            </article>
            <article>
                <span>Điểm & hạng</span>
                <strong>{{ number_format(($notificationStats['diem'] ?? 0) + ($notificationStats['hang_thanh_vien'] ?? 0)) }}</strong>
                <small>Lịch sử thành viên</small>
            </article>
        </div>

        <section class="notification-board">
            <div class="notification-board-head">
                <div>
                    <span>Danh sách thông báo</span>
                    <h2>{{ $currentFilter['label'] ?? 'Tất cả thông báo' }}</h2>
                </div>

                <nav class="notification-filter" aria-label="Lọc thông báo theo loại">
                    @foreach($filterItems as $item)
                        <a
                            href="{{ $item['type'] ? route('user.notifications.index', ['loai' => $item['type']]) : route('user.notifications.index') }}"
                            class="{{ $activeType === $item['type'] ? 'is-active' : '' }}"
                        >
                            {{ $item['label'] }}
                            <b>{{ number_format($item['count']) }}</b>
                        </a>
                    @endforeach
                </nav>
            </div>

            <div class="notification-list">
                @forelse($thongBaos as $thongBao)
                    @php
                        $meta = $typeMeta[$thongBao->loai_thong_bao] ?? $typeMeta['he_thong'];
                        $isUnread = ! $thongBao->da_doc;
                    @endphp

                    <article class="notification-card {{ $meta['class'] }} {{ $isUnread ? 'is-unread' : 'is-read' }}">
                        <div class="notification-card-icon">
                            <i class="{{ $meta['icon'] }}"></i>
                        </div>

                        <div class="notification-card-body">
                            <div class="notification-card-top">
                                <div>
                                    <span>{{ $meta['label'] }}</span>
                                    <h3>{{ $thongBao->tieu_de }}</h3>
                                </div>

                                <div class="notification-time">
                                    <strong>{{ $isUnread ? 'Mới' : 'Đã đọc' }}</strong>
                                    <small>{{ $thongBao->created_at?->format('d/m/Y H:i') }}</small>
                                </div>
                            </div>

                            <p>{{ $thongBao->noi_dung }}</p>

                            <div class="notification-card-actions">
                                @if($thongBao->duong_dan)
                                    <a href="{{ $thongBao->duong_dan }}" class="notification-detail-link">
                                        Xem chi tiết
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </a>
                                @else
                                    <span>
                                        <i class="fa-solid fa-check"></i>
                                        Đã lưu trong tài khoản
                                    </span>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="notification-empty">
                        <span>
                            <i class="fa-solid fa-bell-slash"></i>
                        </span>
                        <h3>{{ $activeType ? 'Chưa có thông báo phù hợp' : 'Bạn chưa có thông báo nào' }}</h3>
                        <p>{{ $activeType ? 'Thử chọn bộ lọc khác hoặc quay lại tất cả thông báo để xem đầy đủ lịch sử.' : 'Khi có cập nhật về vé, điểm thưởng, voucher hoặc hạng thành viên, hệ thống sẽ hiển thị tại đây.' }}</p>
                        <a href="{{ route('user.notifications.index') }}" class="notification-secondary-link">
                            <i class="fa-solid fa-layer-group"></i>
                            Xem tất cả
                        </a>
                    </div>
                @endforelse
            </div>

            @if($thongBaos->hasPages())
                <div class="notification-pagination">
                    <div class="notification-page-summary">
                        Hiển thị
                        <strong>{{ $thongBaos->firstItem() }}</strong>
                        -
                        <strong>{{ $thongBaos->lastItem() }}</strong>
                        trong
                        <strong>{{ $thongBaos->total() }}</strong>
                        thông báo
                    </div>

                    <nav class="notification-page-controls" aria-label="Phân trang thông báo">
                        @if($thongBaos->onFirstPage())
                            <span class="notification-page-link is-disabled">
                                <i class="fa-solid fa-chevron-left"></i>
                                Trước
                            </span>
                        @else
                            <a href="{{ $thongBaos->previousPageUrl() }}" class="notification-page-link">
                                <i class="fa-solid fa-chevron-left"></i>
                                Trước
                            </a>
                        @endif

                        @foreach($thongBaos->getUrlRange(max(1, $thongBaos->currentPage() - 2), min($thongBaos->lastPage(), $thongBaos->currentPage() + 2)) as $page => $url)
                            @if($page === $thongBaos->currentPage())
                                <span class="notification-page-link is-current">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="notification-page-link">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if($thongBaos->hasMorePages())
                            <a href="{{ $thongBaos->nextPageUrl() }}" class="notification-page-link">
                                Sau
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        @else
                            <span class="notification-page-link is-disabled">
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
