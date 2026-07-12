@extends('layouts.user')

@section('title', 'Thẻ thành viên & Điểm')

@section('content')
@php
    $user = Auth::user();
    $rankLabel = $currentRank['label'] ?? $thanhVien->ten_hang;
    $nextRankLabel = match ($currentRankKey) {
        'member' => 'Silver',
        'silver' => 'Gold',
        'gold' => 'Platinum',
        default => null,
    };
@endphp

<section class="member-page">
    <div class="member-shell">
        <div class="member-hero">
            <div class="member-hero-copy">
                <span class="member-eyebrow">
                    <i class="fa-solid fa-crown"></i>
                    CineHome loyalty
                </span>
                <h1>Thẻ thành viên & điểm thưởng</h1>
                <p>Theo dõi hạng thành viên, điểm khả dụng, mã giới thiệu và lịch sử cộng/trừ điểm sau mỗi lần đặt vé.</p>

                <div class="member-hero-actions">
                    <a href="{{ route('user.voucher.index') }}" class="member-primary-link">
                        <i class="fa-solid fa-gift"></i>
                        Đổi điểm lấy voucher
                    </a>
                    <a href="{{ route('user.ve_xem_phim.index') }}" class="member-secondary-link">
                        <i class="fa-solid fa-ticket"></i>
                        Vé của tôi
                    </a>
                </div>
            </div>

            <aside class="member-mini-summary">
                <span>Điểm khả dụng</span>
                <strong>{{ number_format($thanhVien->diem_hien_tai) }}</strong>
                <p>Hạng hiện tại: {{ $rankLabel }}</p>
                <small>
                    <i class="fa-solid fa-chart-line"></i>
                    Hệ số tích điểm x{{ number_format($thanhVien->heSoTichDiem(), 2) }}
                </small>
            </aside>
        </div>

        <div class="member-top-grid">
            <article class="member-card-visual is-{{ $currentRankKey }}">
                <div class="member-card-top">
                    <div>
                        <span>CineHome member</span>
                        <strong>{{ $rankLabel }}</strong>
                    </div>
                    <i class="{{ $currentRank['icon'] ?? 'fa-solid fa-crown' }}"></i>
                </div>

                <div class="member-card-center">
                    <small>Mã thành viên</small>
                    <h2>{{ $thanhVien->ma_thanh_vien }}</h2>
                </div>

                <div class="member-card-bottom">
                    <div>
                        <span>Chủ thẻ</span>
                        <strong>{{ $user->ho_ten ?? 'Thành viên CineHome' }}</strong>
                    </div>
                    <div>
                        <span>Ngày tham gia</span>
                        <strong>{{ $thanhVien->ngay_tham_gia?->format('d/m/Y') ?? 'Đang cập nhật' }}</strong>
                    </div>
                </div>
            </article>

            <section class="member-points-panel">
                <div class="member-panel-head">
                    <span>Tổng quan điểm</span>
                    <h2>{{ number_format($thanhVien->diem_hien_tai) }} điểm</h2>
                </div>

                <div class="member-rank-progress">
                    <div class="member-rank-row">
                        <span>{{ $rankLabel }}</span>
                        <strong>
                            @if($nextRankLabel)
                                Còn {{ number_format($pointsToNextRank) }} điểm lên {{ $nextRankLabel }}
                            @else
                                Bạn đang ở hạng cao nhất
                            @endif
                        </strong>
                    </div>
                    <div class="member-progress-track" aria-label="Tiến độ hạng thành viên">
                        <span style="width: {{ number_format($rankProgress, 2, '.', '') }}%"></span>
                    </div>
                    <small>{{ number_format($thanhVien->tong_diem_tich_luy) }} điểm tích lũy trọn đời</small>
                </div>

                <div class="member-stat-grid">
                    <article>
                        <i class="fa-solid fa-star"></i>
                        <span>Điểm hiện tại</span>
                        <strong>{{ number_format($thanhVien->diem_hien_tai) }}</strong>
                    </article>
                    <article>
                        <i class="fa-solid fa-arrow-trend-up"></i>
                        <span>Tổng tích lũy</span>
                        <strong>{{ number_format($thanhVien->tong_diem_tich_luy) }}</strong>
                    </article>
                    <article>
                        <i class="fa-solid fa-plus"></i>
                        <span>Đã cộng</span>
                        <strong>{{ number_format($pointSummary['earned'] ?? 0) }}</strong>
                    </article>
                    <article>
                        <i class="fa-solid fa-minus"></i>
                        <span>Đã dùng/trừ</span>
                        <strong>{{ number_format($pointSummary['spent'] ?? 0) }}</strong>
                    </article>
                </div>
            </section>
        </div>

        <section class="member-rank-section">
            <div class="member-section-head">
                <span>Quyền lợi theo hạng</span>
                <h2>Lộ trình thành viên</h2>
            </div>

            <div class="member-rank-grid">
                @foreach($rankConfig as $rankKey => $rank)
                    <article class="member-rank-card {{ $rankKey === $currentRankKey ? 'is-active' : '' }}">
                        <span>
                            <i class="{{ $rank['icon'] }}"></i>
                        </span>
                        <h3>{{ $rank['label'] }}</h3>
                        <strong>{{ $rank['range'] }}</strong>
                        <p>{{ $rank['benefit'] }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <div class="member-lower-grid">
            <section class="member-referral-card">
                <div class="member-section-head">
                    <span>Giới thiệu bạn bè</span>
                    <h2>Mã giới thiệu của bạn</h2>
                </div>

                <div class="member-referral-code">
                    <strong>{{ $thanhVien->ma_gioi_thieu }}</strong>
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $thanhVien->ma_gioi_thieu }}')">
                        <i class="fa-solid fa-copy"></i>
                        Copy
                    </button>
                </div>

                <p>Chia sẻ mã này cho bạn bè khi đăng ký tài khoản để nhận thêm điểm thưởng từ CineHome.</p>

                <div class="member-referral-stats">
                    <article>
                        <span>Người đã giới thiệu</span>
                        <strong>{{ number_format($nguoiDaGioiThieu->count()) }}</strong>
                    </article>
                    <article>
                        <span>Điểm thưởng</span>
                        <strong>+{{ number_format($pointSummary['referral_points'] ?? 0) }}</strong>
                    </article>
                </div>
            </section>

            <section class="member-tips-card">
                <div class="member-section-head">
                    <span>Mẹo tích điểm</span>
                    <h2>Dùng điểm thông minh hơn</h2>
                </div>

                <ul>
                    <li>
                        <i class="fa-solid fa-ticket"></i>
                        Đặt vé trực tuyến để điểm tự cộng vào thẻ sau thanh toán.
                    </li>
                    <li>
                        <i class="fa-solid fa-gift"></i>
                        Đổi điểm lấy voucher trước khi checkout để tiết kiệm hơn.
                    </li>
                    <li>
                        <i class="fa-solid fa-user-group"></i>
                        Chia sẻ mã giới thiệu để nhận thêm điểm thưởng.
                    </li>
                </ul>
            </section>
        </div>

        <section class="member-history-board">
            <div class="member-board-head">
                <div>
                    <span>Lịch sử điểm</span>
                    <h2>{{ number_format($pointSummary['transactions'] ?? $lichSuDiem->total()) }} giao dịch</h2>
                </div>
                <a href="{{ route('dat_ve.chon_phim') }}" class="member-secondary-link">
                    <i class="fa-solid fa-plus"></i>
                    Đặt vé tích điểm
                </a>
            </div>

            <div class="member-history-list">
                @forelse($lichSuDiem as $item)
                    @php
                        $isEarn = $item->loai_giao_dich === 'cong_diem';
                    @endphp

                    <article class="member-history-item {{ $isEarn ? 'is-earned' : 'is-spent' }}">
                        <div class="member-history-icon">
                            <i class="fa-solid {{ $isEarn ? 'fa-plus' : 'fa-minus' }}"></i>
                        </div>

                        <div class="member-history-copy">
                            <span>{{ $item->created_at?->format('H:i - d/m/Y') }}</span>
                            <h3>{{ $isEarn ? 'Cộng điểm' : 'Trừ điểm' }}</h3>
                            <p>{{ $item->noi_dung }}</p>
                        </div>

                        <strong>
                            {{ $isEarn ? '+' : '-' }}{{ number_format($item->so_diem) }}
                        </strong>
                    </article>
                @empty
                    <div class="member-empty-history">
                        <span>
                            <i class="fa-solid fa-receipt"></i>
                        </span>
                        <h3>Chưa có lịch sử điểm</h3>
                        <p>Đặt vé hoặc đổi voucher để các giao dịch điểm xuất hiện tại đây.</p>
                        <a href="{{ route('dat_ve.chon_phim') }}" class="member-primary-link">
                            <i class="fa-solid fa-ticket"></i>
                            Đặt vé ngay
                        </a>
                    </div>
                @endforelse
            </div>

            @if($lichSuDiem->hasPages())
                <div class="member-pagination">
                    <div class="member-page-summary">
                        Hiển thị
                        <strong>{{ $lichSuDiem->firstItem() }}</strong>
                        -
                        <strong>{{ $lichSuDiem->lastItem() }}</strong>
                        trong
                        <strong>{{ $lichSuDiem->total() }}</strong>
                        giao dịch
                    </div>

                    <nav class="member-page-controls" aria-label="Phân trang lịch sử điểm">
                        @if($lichSuDiem->onFirstPage())
                            <span class="member-page-link is-disabled">
                                <i class="fa-solid fa-chevron-left"></i>
                                Trước
                            </span>
                        @else
                            <a href="{{ $lichSuDiem->previousPageUrl() }}" class="member-page-link">
                                <i class="fa-solid fa-chevron-left"></i>
                                Trước
                            </a>
                        @endif

                        @foreach($lichSuDiem->getUrlRange(max(1, $lichSuDiem->currentPage() - 2), min($lichSuDiem->lastPage(), $lichSuDiem->currentPage() + 2)) as $page => $url)
                            @if($page === $lichSuDiem->currentPage())
                                <span class="member-page-link is-current">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="member-page-link">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if($lichSuDiem->hasMorePages())
                            <a href="{{ $lichSuDiem->nextPageUrl() }}" class="member-page-link">
                                Sau
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        @else
                            <span class="member-page-link is-disabled">
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
