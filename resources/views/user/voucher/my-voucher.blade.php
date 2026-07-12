@extends('layouts.user')

@section('title', 'Voucher của tôi')

@section('content')
@php
    $filterItems = [
        ['status' => null, 'label' => 'Tất cả', 'count' => $voucherStats['total'] ?? $vouchers->total()],
        ['status' => 'kha_dung', 'label' => 'Khả dụng', 'count' => $voucherStats['available'] ?? 0],
        ['status' => 'da_su_dung', 'label' => 'Đã dùng', 'count' => $voucherStats['used'] ?? 0],
        ['status' => 'het_han', 'label' => 'Hết hạn', 'count' => $voucherStats['expired'] ?? 0],
    ];
@endphp

<section class="myvoucher-page">
    <div class="myvoucher-shell">
        <div class="myvoucher-hero">
            <div class="myvoucher-hero-copy">
                <span class="myvoucher-eyebrow">
                    <i class="fa-solid fa-ticket"></i>
                    My CineHome vouchers
                </span>
                <h1>Voucher của tôi</h1>
                <p>Quản lý các voucher đã đổi, copy mã nhanh và dùng khi đặt vé. Voucher khả dụng sẽ được ưu tiên hiển thị để bạn thao tác dễ hơn.</p>

                <div class="myvoucher-hero-actions">
                    <a href="{{ route('user.voucher.index') }}" class="myvoucher-primary-link">
                        <i class="fa-solid fa-gift"></i>
                        Đổi thêm voucher
                    </a>
                    <a href="{{ route('dat_ve.chon_phim') }}" class="myvoucher-secondary-link">
                        <i class="fa-solid fa-ticket"></i>
                        Đặt vé sử dụng
                    </a>
                </div>
            </div>

            <aside class="myvoucher-highlight-card">
                <span>Sắp hết hạn</span>
                @if($expiringVoucher)
                    <strong>{{ $expiringVoucher->voucher?->ten_voucher ?? 'Voucher CineHome' }}</strong>
                    <p>{{ $expiringVoucher->ma_voucher_ca_nhan }}</p>
                    <small>
                        <i class="fa-solid fa-clock"></i>
                        Hết hạn {{ $expiringVoucher->ngay_het_han?->format('d/m/Y') }}
                    </small>
                @else
                    <strong>Chưa có voucher cần dùng gấp</strong>
                    <p>Đổi thêm voucher để ưu đãi mới xuất hiện tại đây.</p>
                    <small>
                        <i class="fa-solid fa-circle-check"></i>
                        Kho voucher đang gọn gàng
                    </small>
                @endif
            </aside>
        </div>

        @if(session('success'))
            <div class="myvoucher-alert is-success">
                <i class="fa-solid fa-circle-check"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="myvoucher-alert is-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                {{ session('error') }}
            </div>
        @endif

        <div class="myvoucher-stats">
            <article>
                <span>Tổng voucher</span>
                <strong>{{ number_format($voucherStats['total'] ?? $vouchers->total()) }}</strong>
                <small>Tất cả mã đã nhận</small>
            </article>
            <article>
                <span>Khả dụng</span>
                <strong>{{ number_format($voucherStats['available'] ?? 0) }}</strong>
                <small>Có thể dùng khi đặt vé</small>
            </article>
            <article>
                <span>Đã sử dụng</span>
                <strong>{{ number_format($voucherStats['used'] ?? 0) }}</strong>
                <small>Lịch sử ưu đãi</small>
            </article>
            <article>
                <span>Hết hạn</span>
                <strong>{{ number_format($voucherStats['expired'] ?? 0) }}</strong>
                <small>Cần đổi mã mới</small>
            </article>
        </div>

        <section class="myvoucher-board">
            <div class="myvoucher-board-head">
                <div>
                    <span>Kho voucher</span>
                    <h2>{{ $activeStatus ? collect($filterItems)->firstWhere('status', $activeStatus)['label'] ?? 'Voucher đã lọc' : 'Tất cả voucher' }}</h2>
                </div>

                <nav class="myvoucher-filter" aria-label="Lọc voucher theo trạng thái">
                    @foreach($filterItems as $item)
                        <a
                            href="{{ $item['status'] ? route('user.voucher.my', ['trang_thai' => $item['status']]) : route('user.voucher.my') }}"
                            class="{{ $activeStatus === $item['status'] ? 'is-active' : '' }}"
                        >
                            {{ $item['label'] }}
                            <b>{{ number_format($item['count']) }}</b>
                        </a>
                    @endforeach
                </nav>
            </div>

            <div class="myvoucher-grid">
                @forelse($vouchers as $item)
                    @php
                        $isExpired = !$item->da_su_dung && $item->ngay_het_han && $item->ngay_het_han->lt(now());
                        $isAvailable = !$item->da_su_dung && !$isExpired;
                        $statusClass = $item->da_su_dung ? 'is-used' : ($isExpired ? 'is-expired' : 'is-available');
                        $statusLabel = $item->da_su_dung ? 'Đã sử dụng' : ($isExpired ? 'Hết hạn' : 'Khả dụng');
                        $statusIcon = $item->da_su_dung ? 'fa-solid fa-check-double' : ($isExpired ? 'fa-solid fa-clock-rotate-left' : 'fa-solid fa-circle-check');
                    @endphp

                    <article class="myvoucher-card {{ $statusClass }}">
                        <div class="myvoucher-card-top">
                            <span>
                                <i class="fa-solid fa-ticket"></i>
                            </span>
                            <em>
                                <i class="{{ $statusIcon }}"></i>
                                {{ $statusLabel }}
                            </em>
                        </div>

                        <div class="myvoucher-code-box">
                            <small>Mã voucher</small>
                            <strong>{{ $item->ma_voucher_ca_nhan }}</strong>
                            <button type="button" data-copy-voucher="{{ $item->ma_voucher_ca_nhan }}">
                                <i class="fa-solid fa-copy"></i>
                                Copy mã
                            </button>
                        </div>

                        <h3>{{ $item->voucher?->ten_voucher ?? 'Voucher CineHome' }}</h3>

                        <div class="myvoucher-value">
                            <span>Giảm giá</span>
                            <strong>{{ number_format($item->voucher?->gia_tri_giam ?? 0, 0, ',', '.') }}đ</strong>
                        </div>

                        <div class="myvoucher-meta">
                            <div>
                                <i class="fa-solid fa-calendar-plus"></i>
                                <span>Ngày nhận</span>
                                <strong>{{ $item->ngay_nhan?->format('d/m/Y') ?? '---' }}</strong>
                            </div>
                            <div>
                                <i class="fa-solid fa-calendar-xmark"></i>
                                <span>Hạn dùng</span>
                                <strong>{{ $item->ngay_het_han?->format('d/m/Y') ?? 'Không giới hạn' }}</strong>
                            </div>
                            <div>
                                <i class="fa-solid fa-receipt"></i>
                                <span>Ngày sử dụng</span>
                                <strong>{{ $item->ngay_su_dung?->format('d/m/Y') ?? 'Chưa dùng' }}</strong>
                            </div>
                        </div>

                        <div class="myvoucher-actions">
                            @if($isAvailable)
                                <a href="{{ route('dat_ve.chon_phim') }}" class="myvoucher-use-btn">
                                    <i class="fa-solid fa-ticket"></i>
                                    Dùng khi đặt vé
                                </a>
                            @else
                                <span class="myvoucher-disabled-btn">
                                    <i class="{{ $statusIcon }}"></i>
                                    {{ $statusLabel }}
                                </span>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="myvoucher-empty">
                        <span>
                            <i class="fa-solid fa-ticket"></i>
                        </span>
                        <h3>Bạn chưa có voucher nào</h3>
                        <p>Hãy dùng điểm thành viên để đổi voucher giảm giá cho những lần đặt vé tiếp theo.</p>
                        <a href="{{ route('user.voucher.index') }}" class="myvoucher-primary-link">
                            <i class="fa-solid fa-gift"></i>
                            Đổi điểm ngay
                        </a>
                    </div>
                @endforelse
            </div>

            @if($vouchers->hasPages())
                <div class="myvoucher-pagination">
                    <div class="myvoucher-page-summary">
                        Hiển thị
                        <strong>{{ $vouchers->firstItem() }}</strong>
                        -
                        <strong>{{ $vouchers->lastItem() }}</strong>
                        trong
                        <strong>{{ $vouchers->total() }}</strong>
                        voucher
                    </div>

                    <nav class="myvoucher-page-controls" aria-label="Phân trang voucher">
                        @if($vouchers->onFirstPage())
                            <span class="myvoucher-page-link is-disabled">
                                <i class="fa-solid fa-chevron-left"></i>
                                Trước
                            </span>
                        @else
                            <a href="{{ $vouchers->previousPageUrl() }}" class="myvoucher-page-link">
                                <i class="fa-solid fa-chevron-left"></i>
                                Trước
                            </a>
                        @endif

                        @foreach($vouchers->getUrlRange(max(1, $vouchers->currentPage() - 2), min($vouchers->lastPage(), $vouchers->currentPage() + 2)) as $page => $url)
                            @if($page === $vouchers->currentPage())
                                <span class="myvoucher-page-link is-current">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="myvoucher-page-link">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if($vouchers->hasMorePages())
                            <a href="{{ $vouchers->nextPageUrl() }}" class="myvoucher-page-link">
                                Sau
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        @else
                            <span class="myvoucher-page-link is-disabled">
                                Sau
                                <i class="fa-solid fa-chevron-right"></i>
                            </span>
                        @endif
                    </nav>
                </div>
            @endif
        </section>

        <section class="myvoucher-rule-panel">
            <div>
                <span>Mẹo sử dụng</span>
                <h2>Dùng voucher nhanh hơn</h2>
            </div>
            <ul>
                <li>
                    <i class="fa-solid fa-copy"></i>
                    Copy mã voucher trước khi vào bước thanh toán để thao tác nhanh.
                </li>
                <li>
                    <i class="fa-solid fa-clock"></i>
                    Ưu tiên dùng voucher sắp hết hạn để không bỏ lỡ ưu đãi.
                </li>
                <li>
                    <i class="fa-solid fa-gift"></i>
                    Hết voucher thì quay lại kho đổi điểm để nhận mã mới.
                </li>
            </ul>
        </section>
    </div>
</section>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-copy-voucher]').forEach(function (button) {
            button.addEventListener('click', function () {
                const code = button.getAttribute('data-copy-voucher');

                if (!navigator.clipboard) return;

                navigator.clipboard.writeText(code).then(function () {
                    const originalText = button.innerHTML;
                    button.innerHTML = '<i class="fa-solid fa-check"></i> Đã copy';

                    setTimeout(function () {
                        button.innerHTML = originalText;
                    }, 1400);
                });
            });
        });
    });
</script>
@endsection
