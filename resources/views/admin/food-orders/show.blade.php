@extends('layouts.admin')

@section('title', 'Chi tiet hoa don do an')
@section('page-title', 'Hoa don ' . $foodOrder->invoice_code)
@section('page-subtitle', 'Thong tin mon an, thanh toan va phuc vu')

@section('content')
<div class="grid gap-4 xl:grid-cols-[1fr_360px]">
    <div class="admin-panel">
        <div class="panel-header">
            <div>
                <h5>Danh sach mon</h5>
                <small>{{ $foodOrder->items->count() }} dong hoa don</small>
            </div>
            <a href="{{ route('admin.food-orders.index') }}" class="btn-admin-outline">Quay lai</a>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Mon</th>
                    <th>So luong</th>
                    <th>Don gia</th>
                    <th class="text-end">Thanh tien</th>
                </tr>
            </thead>
            <tbody>
                @forelse($foodOrder->items as $item)
                    <tr>
                        <td><strong>{{ $item->item_name }}</strong></td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->unit_price) }}d</td>
                        <td class="text-end">{{ number_format($item->line_total) }}d</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-gray-400">Hoa don chua co mon.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="admin-panel">
        <div class="panel-header"><h5>Thong tin hoa don</h5></div>
        <div class="space-y-3 text-sm">
            <div class="flex justify-between"><span class="text-gray-400">Khach hang</span><strong>{{ $foodOrder->customer_name ?? $foodOrder->user?->name ?? 'Khach le' }}</strong></div>
            <div class="flex justify-between"><span class="text-gray-400">So dien thoai</span><strong>{{ $foodOrder->customer_phone ?? '-' }}</strong></div>
            <div class="flex justify-between"><span class="text-gray-400">Tam tinh</span><strong>{{ number_format($foodOrder->subtotal) }}d</strong></div>
            <div class="flex justify-between"><span class="text-gray-400">Giam gia</span><strong>{{ number_format($foodOrder->discount_amount) }}d</strong></div>
            <div class="flex justify-between text-lg"><span>Tong tien</span><strong class="text-[#d99a32]">{{ number_format($foodOrder->total_amount) }}d</strong></div>
        </div>

        <form method="POST" action="{{ route('admin.food-orders.status', $foodOrder) }}" class="mt-6">
            @csrf
            @method('PATCH')
            <label class="mb-2 block text-sm font-bold text-gray-300">Trang thai phuc vu</label>
            <select name="fulfillment_status" class="w-full rounded-xl border border-white/10 bg-[#111] px-4 py-3 text-white">
                @foreach(['waiting' => 'Cho lam', 'preparing' => 'Dang chuan bi', 'completed' => 'Hoan thanh', 'cancelled' => 'Da huy'] as $value => $label)
                    <option value="{{ $value }}" @selected($foodOrder->fulfillment_status === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="btn-admin mt-4 w-full">Cap nhat</button>
        </form>
    </div>
</div>
@endsection
