@extends('layouts.admin')

@section('page-title', 'Báo cáo doanh thu')
@section('page-subtitle', 'Tổng hợp doanh thu vé và đồ ăn theo khoảng ngày')

@section('content')
@php
    $ticketStatusLabels = [
        'da_thanh_toan' => 'Đã thanh toán',
        'da_su_dung' => 'Đã sử dụng',
        'da_huy' => 'Đã hủy',
    ];

    $ticketStatusTones = [
        'da_thanh_toan' => 'is-paid',
        'da_su_dung' => 'is-used',
        'da_huy' => 'is-cancelled',
    ];

    $foodStatusLabels = [
        'pending' => 'Chờ thanh toán',
        'paid' => 'Đã thanh toán',
        'cancelled' => 'Đã hủy',
    ];

    $foodStatusTones = [
        'pending' => 'is-pending',
        'paid' => 'is-paid',
        'cancelled' => 'is-cancelled',
    ];

    $totalRevenue = (float) $summary['total_revenue'];
    $ticketRevenue = (float) $summary['ticket_revenue'];
    $foodRevenue = (float) $summary['food_revenue'];
    $ticketShare = $totalRevenue > 0 ? round(($ticketRevenue / $totalRevenue) * 100) : 0;
    $foodShare = $totalRevenue > 0 ? 100 - $ticketShare : 0;
    $paidOrders = (int) $summary['tickets_sold'] + (int) $summary['food_invoices'];
    $reportDays = max(1, (int) $from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay()) + 1);
@endphp

<div class="revenue-report-page">
    <section class="revenue-hero">
        <div>
            <span class="revenue-kicker">
                <i class="fa-solid fa-chart-pie"></i>
                Trung tâm báo cáo
            </span>
            <h2>{{ number_format($totalRevenue, 0, ',', '.') }}đ</h2>
            <p>Tổng doanh thu hợp lệ từ vé xem phim và hóa đơn đồ ăn trong khoảng {{ $from->format('d/m/Y') }} - {{ $to->format('d/m/Y') }}.</p>

            <div class="revenue-hero-meta">
                <span>
                    <i class="fa-solid fa-ticket"></i>
                    {{ number_format($summary['tickets_sold']) }} vé đã thanh toán
                </span>
                <span>
                    <i class="fa-solid fa-burger"></i>
                    {{ number_format($summary['food_invoices']) }} hóa đơn đồ ăn
                </span>
                <span>
                    <i class="fa-regular fa-calendar"></i>
                    {{ $reportDays }} ngày báo cáo
                </span>
            </div>
        </div>

        <form method="GET" class="revenue-filter">
            <label>
                <span>Từ ngày</span>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}">
            </label>
            <label>
                <span>Đến ngày</span>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}">
            </label>
            <button type="submit">
                <i class="fa-solid fa-filter"></i>
                Lọc báo cáo
            </button>
        </form>
    </section>

    <section class="revenue-stat-grid">
        <article class="revenue-stat-card is-ticket">
            <span><i class="fa-solid fa-ticket"></i></span>
            <div>
                <small>Doanh thu vé</small>
                <strong>{{ number_format($ticketRevenue, 0, ',', '.') }}đ</strong>
                <p>{{ $ticketShare }}% tổng doanh thu</p>
            </div>
        </article>

        <article class="revenue-stat-card is-food">
            <span><i class="fa-solid fa-burger"></i></span>
            <div>
                <small>Doanh thu đồ ăn</small>
                <strong>{{ number_format($foodRevenue, 0, ',', '.') }}đ</strong>
                <p>{{ $foodShare }}% tổng doanh thu</p>
            </div>
        </article>

        <article class="revenue-stat-card is-total">
            <span><i class="fa-solid fa-sack-dollar"></i></span>
            <div>
                <small>Tổng doanh thu</small>
                <strong>{{ number_format($totalRevenue, 0, ',', '.') }}đ</strong>
                <p>Chỉ tính giao dịch hợp lệ</p>
            </div>
        </article>

        <article class="revenue-stat-card is-orders">
            <span><i class="fa-solid fa-receipt"></i></span>
            <div>
                <small>Giao dịch hợp lệ</small>
                <strong>{{ number_format($paidOrders) }}</strong>
                <p>Vé và hóa đơn đã thanh toán</p>
            </div>
        </article>
    </section>

    <section class="revenue-split-panel">
        <div class="revenue-panel-head">
            <div>
                <span class="revenue-kicker">Cơ cấu doanh thu</span>
                <h3>Tỷ trọng vé và đồ ăn</h3>
                <p>Giúp nhận biết nguồn doanh thu chính trong kỳ báo cáo.</p>
            </div>
        </div>

        <div class="revenue-share-track" aria-label="Tỷ trọng doanh thu">
            <span class="is-ticket" style="width: {{ $ticketShare }}%"></span>
            <span class="is-food" style="width: {{ $foodShare }}%"></span>
        </div>

        <div class="revenue-share-legend">
            <span><i class="is-ticket"></i> Vé: {{ $ticketShare }}%</span>
            <span><i class="is-food"></i> Đồ ăn: {{ $foodShare }}%</span>
        </div>
    </section>

    <section class="revenue-report-grid">
        <article class="revenue-table-panel">
            <div class="revenue-panel-head">
                <div>
                    <span class="revenue-kicker">Vé xem phim</span>
                    <h3>Doanh thu vé</h3>
                    <p>Danh sách vé phát sinh trong khoảng ngày đã chọn.</p>
                </div>
                <strong>{{ number_format($tickets->total()) }} vé</strong>
            </div>

            <div class="revenue-table-wrap">
                <table class="revenue-table">
                    <thead>
                        <tr>
                            <th>Mã vé</th>
                            <th>Phim</th>
                            <th>Trạng thái</th>
                            <th class="is-right">Tổng tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tickets as $ticket)
                            <tr>
                                <td data-label="Mã vé">
                                    <span class="revenue-code">{{ $ticket->ma_ve }}</span>
                                    <small>{{ $ticket->created_at?->format('d/m/Y H:i') }}</small>
                                </td>
                                <td data-label="Phim">
                                    <strong>{{ $ticket->ten_phim ?? 'Không rõ phim' }}</strong>
                                    <small>{{ $ticket->ten_phong ?? 'Chưa rõ phòng' }}{{ $ticket->ma_ghe ? ' · Ghế ' . $ticket->ma_ghe : '' }}</small>
                                </td>
                                <td data-label="Trạng thái">
                                    <span class="revenue-status {{ $ticketStatusTones[$ticket->trang_thai] ?? 'is-pending' }}">
                                        {{ $ticketStatusLabels[$ticket->trang_thai] ?? $ticket->trang_thai }}
                                    </span>
                                </td>
                                <td data-label="Tổng tiền" class="is-right">
                                    <span class="revenue-money">{{ number_format((float) $ticket->tong_tien, 0, ',', '.') }}đ</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="revenue-empty">
                                        <i class="fa-solid fa-ticket"></i>
                                        <h3>Không có vé trong khoảng ngày này</h3>
                                        <p>Thử mở rộng khoảng ngày hoặc kiểm tra lại dữ liệu bán vé.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="revenue-pagination">
                <span>
                    Hiển thị {{ $tickets->firstItem() ?? 0 }} - {{ $tickets->lastItem() ?? 0 }}
                    trên {{ number_format($tickets->total()) }} vé
                </span>
                @if($tickets->hasPages())
                    @php
                        $ticketStartPage = max(1, $tickets->currentPage() - 1);
                        $ticketEndPage = min($tickets->lastPage(), $tickets->currentPage() + 1);
                    @endphp
                    <nav class="revenue-page-controls" aria-label="Phân trang doanh thu vé">
                        @if($tickets->onFirstPage())
                            <span class="revenue-page-btn is-disabled">
                                <i class="fa-solid fa-chevron-left"></i>
                                Trước
                            </span>
                        @else
                            <a href="{{ $tickets->previousPageUrl() }}" class="revenue-page-btn">
                                <i class="fa-solid fa-chevron-left"></i>
                                Trước
                            </a>
                        @endif

                        <div class="revenue-page-numbers">
                            @if($ticketStartPage > 1)
                                <a href="{{ $tickets->url(1) }}" class="revenue-page-number">1</a>
                                @if($ticketStartPage > 2)
                                    <span class="revenue-page-ellipsis">...</span>
                                @endif
                            @endif

                            @for($page = $ticketStartPage; $page <= $ticketEndPage; $page++)
                                @if($page === $tickets->currentPage())
                                    <span class="revenue-page-number is-active">{{ $page }}</span>
                                @else
                                    <a href="{{ $tickets->url($page) }}" class="revenue-page-number">{{ $page }}</a>
                                @endif
                            @endfor

                            @if($ticketEndPage < $tickets->lastPage())
                                @if($ticketEndPage < $tickets->lastPage() - 1)
                                    <span class="revenue-page-ellipsis">...</span>
                                @endif
                                <a href="{{ $tickets->url($tickets->lastPage()) }}" class="revenue-page-number">{{ $tickets->lastPage() }}</a>
                            @endif
                        </div>

                        @if($tickets->hasMorePages())
                            <a href="{{ $tickets->nextPageUrl() }}" class="revenue-page-btn">
                                Sau
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        @else
                            <span class="revenue-page-btn is-disabled">
                                Sau
                                <i class="fa-solid fa-chevron-right"></i>
                            </span>
                        @endif
                    </nav>
                @endif
            </div>
        </article>

        <article class="revenue-table-panel">
            <div class="revenue-panel-head">
                <div>
                    <span class="revenue-kicker">Đồ ăn & combo</span>
                    <h3>Doanh thu đồ ăn</h3>
                    <p>Danh sách hóa đơn bắp nước, combo và đồ ăn kèm.</p>
                </div>
                <strong>{{ number_format($foodInvoices->total()) }} hóa đơn</strong>
            </div>

            <div class="revenue-table-wrap">
                <table class="revenue-table">
                    <thead>
                        <tr>
                            <th>Mã hóa đơn</th>
                            <th>Khách hàng</th>
                            <th>Trạng thái</th>
                            <th class="is-right">Tổng tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($foodInvoices as $invoice)
                            <tr>
                                <td data-label="Mã hóa đơn">
                                    <span class="revenue-code">{{ $invoice->invoice_code }}</span>
                                    <small>{{ $invoice->created_at?->format('d/m/Y H:i') }}</small>
                                </td>
                                <td data-label="Khách hàng">
                                    <strong>{{ $invoice->customer_name ?: 'Khách lẻ' }}</strong>
                                    <small>{{ $invoice->customer_phone ?: ($invoice->payment_method ?: 'Chưa có thông tin') }}</small>
                                </td>
                                <td data-label="Trạng thái">
                                    <span class="revenue-status {{ $foodStatusTones[$invoice->payment_status] ?? 'is-pending' }}">
                                        {{ $foodStatusLabels[$invoice->payment_status] ?? $invoice->payment_status }}
                                    </span>
                                </td>
                                <td data-label="Tổng tiền" class="is-right">
                                    <span class="revenue-money">{{ number_format((float) $invoice->total, 0, ',', '.') }}đ</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="revenue-empty">
                                        <i class="fa-solid fa-burger"></i>
                                        <h3>Không có hóa đơn đồ ăn</h3>
                                        <p>Thử mở rộng khoảng ngày hoặc kiểm tra lại dữ liệu quầy đồ ăn.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="revenue-pagination">
                <span>
                    Hiển thị {{ $foodInvoices->firstItem() ?? 0 }} - {{ $foodInvoices->lastItem() ?? 0 }}
                    trên {{ number_format($foodInvoices->total()) }} hóa đơn
                </span>
                @if($foodInvoices->hasPages())
                    @php
                        $foodStartPage = max(1, $foodInvoices->currentPage() - 1);
                        $foodEndPage = min($foodInvoices->lastPage(), $foodInvoices->currentPage() + 1);
                    @endphp
                    <nav class="revenue-page-controls" aria-label="Phân trang hóa đơn đồ ăn">
                        @if($foodInvoices->onFirstPage())
                            <span class="revenue-page-btn is-disabled">
                                <i class="fa-solid fa-chevron-left"></i>
                                Trước
                            </span>
                        @else
                            <a href="{{ $foodInvoices->previousPageUrl() }}" class="revenue-page-btn">
                                <i class="fa-solid fa-chevron-left"></i>
                                Trước
                            </a>
                        @endif

                        <div class="revenue-page-numbers">
                            @if($foodStartPage > 1)
                                <a href="{{ $foodInvoices->url(1) }}" class="revenue-page-number">1</a>
                                @if($foodStartPage > 2)
                                    <span class="revenue-page-ellipsis">...</span>
                                @endif
                            @endif

                            @for($page = $foodStartPage; $page <= $foodEndPage; $page++)
                                @if($page === $foodInvoices->currentPage())
                                    <span class="revenue-page-number is-active">{{ $page }}</span>
                                @else
                                    <a href="{{ $foodInvoices->url($page) }}" class="revenue-page-number">{{ $page }}</a>
                                @endif
                            @endfor

                            @if($foodEndPage < $foodInvoices->lastPage())
                                @if($foodEndPage < $foodInvoices->lastPage() - 1)
                                    <span class="revenue-page-ellipsis">...</span>
                                @endif
                                <a href="{{ $foodInvoices->url($foodInvoices->lastPage()) }}" class="revenue-page-number">{{ $foodInvoices->lastPage() }}</a>
                            @endif
                        </div>

                        @if($foodInvoices->hasMorePages())
                            <a href="{{ $foodInvoices->nextPageUrl() }}" class="revenue-page-btn">
                                Sau
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        @else
                            <span class="revenue-page-btn is-disabled">
                                Sau
                                <i class="fa-solid fa-chevron-right"></i>
                            </span>
                        @endif
                    </nav>
                @endif
            </div>
        </article>
    </section>
</div>
@endsection
