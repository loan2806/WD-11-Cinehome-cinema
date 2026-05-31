@extends('layouts.admin')

@section('title', 'Hoa don do an')
@section('page-title', 'Chi tiet hoa don do an')
@section('page-subtitle', 'Tra cuu hoa don combo, bap nuoc va trang thai phuc vu')

@section('content')
<div class="admin-panel">
    <div class="panel-header">
        <div>
            <h5>Hoa don do an</h5>
            <small>{{ $orders->total() }} hoa don</small>
        </div>
    </div>

    <form class="mb-4 grid gap-3 md:grid-cols-3" method="GET">
        <input name="keyword" value="{{ request('keyword') }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-white" placeholder="Ma hoa don, ten, so dien thoai">
        <select name="payment_status" class="rounded-xl border border-white/10 bg-[#111] px-4 py-3 text-white">
            <option value="">Tat ca thanh toan</option>
            @foreach(['pending' => 'Cho thanh toan', 'paid' => 'Da thanh toan', 'cancelled' => 'Da huy'] as $value => $label)
                <option value="{{ $value }}" @selected(request('payment_status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn-admin"><i class="fa-solid fa-filter"></i> Loc</button>
    </form>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Hoa don</th>
                    <th>Khach hang</th>
                    <th>Thanh toan</th>
                    <th>Phuc vu</th>
                    <th class="text-end">Tong tien</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td><strong>{{ $order->invoice_code }}</strong><br><small>{{ $order->created_at->format('d/m/Y H:i') }}</small></td>
                        <td>{{ $order->customer_name ?? $order->user?->name ?? 'Khach le' }}<br><small>{{ $order->customer_phone }}</small></td>
                        <td><span class="status-badge status-coming">{{ $order->payment_status }}</span></td>
                        <td><span class="status-badge status-showing">{{ $order->fulfillment_status }}</span></td>
                        <td class="text-end">{{ number_format($order->total_amount) }}d</td>
                        <td class="text-end"><a class="btn-admin-outline" href="{{ route('admin.food-orders.show', $order) }}">Chi tiet</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-gray-400">Chua co hoa don do an.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
</div>
@endsection
