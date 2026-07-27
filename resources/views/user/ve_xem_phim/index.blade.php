@extends('layouts.user')

@section('title', 'Vé của tôi')

@section('content')
<style>
    /* 🌟 ANIMATION NHỊP ĐẬP CHO NÚT THANH TOÁN & TRẠNG THÁI CHỜ */
    @keyframes pulsePayGlow {
        0% { box-shadow: 0 0 0 0 rgba(250, 204, 21, 0.6); }
        70% { box-shadow: 0 0 0 10px rgba(250, 204, 21, 0); }
        100% { box-shadow: 0 0 0 0 rgba(250, 204, 21, 0); }
    }
    @keyframes badgeSoftPulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.75; transform: scale(0.97); }
    }
    .animate-pay-pulse {
        animation: pulsePayGlow 2s infinite;
    }
    .animate-badge-pulse {
        animation: badgeSoftPulse 1.8s infinite ease-in-out;
    }

    /* 🌟 NÚT CHI TIẾT THIẾT KẾ MỚI SANG TRỌNG */
    .btn-ticket-detail-custom {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.18);
        color: #f3f4f6 !important;
        font-weight: 700;
        font-size: 13px;
        padding: 10px 20px;
        border-radius: 10px;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        backdrop-filter: blur(8px);
    }
    .btn-ticket-detail-custom:hover {
        background: rgba(250, 204, 21, 0.15);
        border-color: #facc15;
        color: #facc15 !important;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(250, 204, 21, 0.25);
    }
</style>

@php
    $statusMeta = [
        'cho_thanh_toan' => [
            'label' => 'Chờ thanh toán',
            'icon' => 'fa-solid fa-clock',
            'class' => 'is-pending',
            'description' => 'Vé đã chọn đang chờ thanh toán',
        ],
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
        'het_han' => [
            'label' => 'Hết hạn',
            'icon' => 'fa-solid fa-clock-rotate-left',
            'class' => 'is-cancelled',
            'description' => 'Vé đã quá giờ chiếu',
        ],
    ];

    $filterItems = [
        ['status' => null, 'label' => 'Tất cả', 'count' => $ticketStats['total'] ?? $veXemPhims->total()],
        ['status' => 'cho_thanh_toan', 'label' => 'Chờ thanh toán', 'count' => $ticketStats['pending'] ?? 0],
        ['status' => 'da_thanh_toan', 'label' => 'Đã thanh toán', 'count' => $ticketStats['paid'] ?? 0],
        ['status' => 'da_su_dung', 'label' => 'Đã sử dụng', 'count' => $ticketStats['used'] ?? 0],
        ['status' => 'het_han', 'label' => 'Hết hạn', 'count' => $ticketStats['expired'] ?? 0],
        ['status' => 'da_huy', 'label' => 'Đã hủy', 'count' => $ticketStats['cancelled'] ?? 0],
    ];

    // Lấy vé chờ thanh toán gần nhất (nếu có)
    $pendingTicketAlert = $veXemPhims->firstWhere('trang_thai', 'cho_thanh_toan');
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
                <p>Quản lý vé đã đặt, kiểm tra QR soát vé và theo dõi lịch chiếu phim dễ dàng.</p>

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
                        Vui lòng đến rạp trước suất chiếu 15 phút.
                    </small>
                @endif
            </aside>
        </div>

        <div class="mytickets-stats" style="grid-template-columns: repeat(5, 1fr) !important;">
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
                <span>Hết hạn</span>
                <strong style="color: #9ca3af;">{{ number_format($ticketStats['expired'] ?? 0) }}</strong>
                <small>Vé quá giờ chiếu</small>
            </article>
            <article>
                <span>Đã hủy</span>
                <strong>{{ number_format($ticketStats['cancelled'] ?? 0) }}</strong>
                <small>Lịch sử vé đã hủy</small>
            </article>
        </div>

        {{-- 🌟 THÔNG BÁO CẢNH BÁO LỖI / CHUYỂN HƯỚNG ĐẶT VÉ KHÁC --}}
        @if(session('error'))
            <div style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.25), rgba(153, 27, 27, 0.35)); border: 1px solid rgba(239, 68, 68, 0.6); color: #ffffff; padding: 16px 20px; border-radius: 16px; margin-bottom: 24px; display: flex; align-items: center; gap: 14px; box-shadow: 0 8px 25px rgba(239, 68, 68, 0.2); backdrop-filter: blur(10px);">
                <div style="background: #ef4444; color: #fff; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 20px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div style="flex: 1; font-size: 15px; font-weight: 600; line-height: 1.5; color: #fecdd3;">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        @if(session('success'))
            <div style="background: linear-gradient(135deg, rgba(34, 197, 94, 0.25), rgba(21, 128, 61, 0.35)); border: 1px solid rgba(34, 197, 94, 0.6); color: #ffffff; padding: 16px 20px; border-radius: 16px; margin-bottom: 24px; display: flex; align-items: center; gap: 14px; box-shadow: 0 8px 25px rgba(34, 197, 94, 0.2); backdrop-filter: blur(10px);">
                <div style="background: #22c55e; color: #fff; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 20px; box-shadow: 0 4px 12px rgba(34, 197, 94, 0.4);">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div style="flex: 1; font-size: 15px; font-weight: 600; line-height: 1.5; color: #dcfce7;">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        {{-- 🌟 BANNER CẢNH BÁO NẾU ĐANG CÓ ĐƠN CHỜ THANH TOÁN --}}
        @if($pendingTicketAlert && method_exists($pendingTicketAlert, 'isExpired') && !$pendingTicketAlert->isExpired())
            <div style="background: rgba(234, 179, 8, 0.12); border: 1px solid #eab308; color: #fef08a; padding: 18px 24px; border-radius: 18px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; gap: 15px; flex-wrap: wrap; box-shadow: 0 10px 30px -5px rgba(234, 179, 8, 0.25);">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="background: #eab308; color: #000; width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 900; flex-shrink: 0;" class="animate-pay-pulse">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <div>
                        <strong style="display: block; color: #fff; font-size: 16px; font-weight: 800; margin-bottom: 3px;">
                            Bạn đang có đơn hàng chờ thanh toán: {{ $pendingTicketAlert->ten_phim }}
                        </strong>
                        <span style="font-size: 13px; color: #cbd5e1;">
                            Ghế giữ: <b style="color: #facc15;">{{ $pendingTicketAlert->ma_ghe }}</b> • Tổng tiền: <b style="color: #facc15;">{{ number_format($pendingTicketAlert->tong_tien, 0, ',', '.') }}đ</b>
                        </span>
                    </div>
                </div>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <a href="{{ route('dat_ve.checkout', ['suat_chieu_id' => $pendingTicketAlert->suat_chieu_id, 'pending_ticket_id' => $pendingTicketAlert->id]) }}" style="background: #eab308; color: #000; padding: 10px 20px; border-radius: 10px; font-weight: 800; text-decoration: none; font-size: 13px; transition: 0.2s;" class="myticket-detail-btn">
                        Thanh toán ngay <i class="fa-solid fa-arrow-right " style="margin-left: 4px;"></i>
                    </a>
                    <form action="{{ route('dat_ve.huy_ve_pending', $pendingTicketAlert->id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này để chọn suất chiếu mới?');">
                        @csrf
                        <button type="submit" style="background: rgba(239, 68, 68, 0.2); color: #f87171; padding: 10px 16px; border-radius: 10px; font-weight: 700; border: 1px solid rgba(239, 68, 68, 0.4); font-size: 13px; cursor: pointer;" class="myticket-detail-btn">
                            Hủy đơn
                        </button>
                    </form>
                </div>
            </div>
        @endif

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
                            @if($veXemPhim->trang_thai === 'cho_thanh_toan')
                                <strong class="text-warning animate-badge-pulse">Chờ thanh toán</strong>
                                <small>Vui lòng hoàn tất thanh toán để nhận mã vé.</small>
                            @else
                                <strong>{{ $veXemPhim->ma_ve }}</strong>
                                <small>{{ $veXemPhim->created_at?->format('d/m/Y H:i') }}</small>
                            @endif
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
                            
                            @if(!empty($veXemPhim->food_items))
                                <p style="margin-top: 8px; font-size: 13px; color: #facc15; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                    <i class="fa-solid fa-cookie-bite" style="font-size: 14px;"></i>
                                    <span style="color: #9ca3af; font-weight: 600;">Đồ ăn kèm:</span>
                                    @foreach($veXemPhim->food_items as $fItem)
                                        <span style="background: rgba(250, 204, 21, 0.1); border: 1px solid rgba(250, 204, 21, 0.2); padding: 2px 8px; border-radius: 6px; font-weight: bold;">
                                            {{ $fItem['name'] ?? 'Đồ ăn' }} (x{{ $fItem['qty'] ?? $fItem['quantity'] ?? 1 }})
                                        </span>
                                    @endforeach
                                </p>
                            @endif
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
                                <strong class="myticket-status {{ $veXemPhim->trang_thai === 'cho_thanh_toan' ? 'animate-badge-pulse' : '' }}">
                                    <i class="{{ $meta['icon'] }}"></i>
                                    {{ $meta['label'] }}
                                </strong>
                                <small>{{ $meta['description'] }}</small>

                                {{-- 🌟 NÚT THANH TOÁN & HỦY ĐƠN VÉ PENDING --}}
                                @if($veXemPhim->trang_thai === 'cho_thanh_toan')
                                    <div class="myticket-status-action" style="margin-top: 12px; width: 100%;">
                                        @if(method_exists($veXemPhim, 'isExpired') && $veXemPhim->isExpired())
                                            <span style="color: #ef4444; font-size: 13px; font-weight: 600; display: block; background: rgba(239, 68, 68, 0.12); padding: 8px 12px; border-radius: 8px; border: 1px solid rgba(239, 68, 68, 0.25); text-align: center;">
                                                <i class="fa-solid fa-clock-rotate-left me-1"></i> Đã quá hạn 7 phút
                                            </span>
                                        @else
                                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; width: 100%;">
                                                <a href="{{ route('dat_ve.checkout', ['suat_chieu_id' => $veXemPhim->suat_chieu_id, 'pending_ticket_id' => $veXemPhim->id]) }}" 
                                                   class="myticket-detail-btn" 
                                                   style="background: linear-gradient(135deg, #facc15, #eab308); color: #000000 !important; font-weight: 800; font-size: 13px; text-transform: uppercase; padding: 10px 12px; border-radius: 10px; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.2s ease;">
                                                    <i class="fa-solid fa-credit-card" style="color: #000000 !important;"></i>
                                                    <span style="color: #000000 !important;">Thanh toán</span>
                                                </a>

                                                <form action="{{ route('dat_ve.huy_ve_pending', $veXemPhim->id) }}" 
                                                      method="POST" 
                                                      style="margin: 0; display: flex;" 
                                                      onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này để nhả lại ghế trống?');">
                                                    @csrf
                                                    <button type="submit"
                                                            class="myticket-detail-btn"
                                                            style="width: 100%; background: rgba(239, 68, 68, 0.12); color: #fca5a5 !important; border: 1px solid rgba(239, 68, 68, 0.35); font-weight: 700; font-size: 13px; padding: 10px 12px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.2s ease;">
                                                        <i class="fa-solid fa-xmark"></i>
                                                        <span>Hủy đơn</span>
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- 🌟 NÚT CHI TIẾT VÉ CHO VÉ ĐÃ THANH TOÁN / ĐÃ SỬ DỤNG / HẾT HẠN --}}
                        @if($veXemPhim->trang_thai !== 'cho_thanh_toan')
                            <div class="myticket-actions" style="display: flex; justify-content: flex-end; align-items: center;">
                                <a href="{{ route('user.ve_xem_phim.show', $veXemPhim) }}" class="btn-ticket-detail-custom">
                                    <i class="fa-solid fa-qrcode" style="font-size: 14px;"></i>
                                    <span>Xem chi tiết</span>
                                </a>
                            </div>
                        @endif
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