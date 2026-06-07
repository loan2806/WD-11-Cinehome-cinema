@extends('layouts.admin')

@section('page-title', 'Báo cáo doanh thu')
@section('page-title', 'Báo cáo doanh thu')
@section('page-subtitle', 'Tổng hợp doanh thu vé và đồ ăn theo khoảng ngày')

@section('content')
<form method="GET" class="admin-panel mb-6">
    <div class="panel-body grid gap-3 md:grid-cols-[1fr_1fr_auto]">
        <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="admin-input">
        <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="admin-input">
        <button class="btn-admin" type="submit">Lọc báo cáo</button>
    </div>
</form>

<div class="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
    <div class="stat-card"><div class="stat-label">Doanh thu vé</div><div class="stat-value">{{ number_format($summary['ticket_revenue'], 0, ',', '.') }}đ</div></div>
    <div class="stat-card"><div class="stat-label">Doanh thu đồ ăn</div><div class="stat-value">{{ number_format($summary['food_revenue'], 0, ',', '.') }}đ</div></div>
    <div class="stat-card"><div class="stat-label">Tổng doanh thu</div><div class="stat-value">{{ number_format($summary['total_revenue'], 0, ',', '.') }}đ</div></div>
    <div class="stat-card"><div class="stat-label">Vé / hóa đơn</div><div class="stat-value">{{ $summary['tickets_sold'] }} / {{ $summary['food_invoices'] }}</div></div>
</div>

<div class="grid gap-6 xl:grid-cols-2">
    <div class="admin-panel">
        <div class="panel-header"><h5>Doanh thu vé</h5></div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead><tr><th>Mã vé</th><th>Phim</th><th>Trạng thái</th><th>Tổng tiền</th></tr></thead>
                <tbody>
                    @forelse ($tickets as $ticket)
                        <tr><td>{{ $ticket->ticket_code }}</td><td>{{ $ticket->movie_title }}</td><td>{{ $ticket->status }}</td><td>{{ number_format($ticket->total_price, 0, ',', '.') }}đ</td></tr>
                    @empty
                        <tr><td colspan="4">Không có vé trong khoảng ngày này.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="admin-panel">
        <div class="panel-header"><h5>Doanh thu đồ ăn</h5></div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead><tr><th>Mã hóa đơn</th><th>Khách hàng</th><th>Trạng thái</th><th>Tổng tiền</th></tr></thead>
                <tbody>
                    @forelse ($foodInvoices as $invoice)
                        <tr><td>{{ $invoice->invoice_code }}</td><td>{{ $invoice->customer_name ?? 'Khách lẻ' }}</td><td>{{ $invoice->payment_status }}</td><td>{{ number_format($invoice->total, 0, ',', '.') }}đ</td></tr>
                    @empty
                        <tr><td colspan="4">Không có hóa đơn đồ ăn trong khoảng ngày này.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

