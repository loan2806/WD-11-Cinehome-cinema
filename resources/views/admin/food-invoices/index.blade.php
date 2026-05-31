@extends('layouts.admin')

@section('title', 'Chi tiết hóa đơn đồ ăn')
@section('page-title', 'Chi tiết hóa đơn đồ ăn')
@section('page-subtitle', 'Tạo và theo dõi hóa đơn bắp nước, combo đồ ăn')

@section('content')
@include('admin.partials.flash')

<div class="grid gap-6 xl:grid-cols-[420px_1fr]">
    <form method="POST" action="{{ route('admin.food-invoices.store') }}" class="admin-panel">
        @csrf
        <div class="panel-header"><h5>Tạo hóa đơn</h5></div>
        <div class="panel-body space-y-4">
            <input name="customer_name" class="admin-input" placeholder="Tên khách hàng">
            <input name="customer_phone" class="admin-input" placeholder="Số điện thoại">
            <div class="grid grid-cols-2 gap-3">
                <input name="items[0][food_name]" class="admin-input" placeholder="Tên món" required>
                <input name="items[0][quantity]" type="number" min="1" value="1" class="admin-input" required>
                <input name="items[0][unit_price]" type="number" min="0" class="admin-input col-span-2" placeholder="Đơn giá" required>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <input name="discount" type="number" min="0" value="0" class="admin-input" placeholder="Giảm giá">
                <select name="payment_status" class="admin-input">
                    <option value="pending">Chờ thanh toán</option>
                    <option value="paid">Đã thanh toán</option>
                    <option value="cancelled">Đã hủy</option>
                </select>
            </div>
            <input name="payment_method" class="admin-input" placeholder="Phương thức thanh toán">
            <textarea name="note" class="admin-input min-h-[90px]" placeholder="Ghi chú"></textarea>
            <button class="btn-admin w-full" type="submit">Lưu hóa đơn</button>
        </div>
    </form>

    <div class="admin-panel">
        <div class="panel-header"><h5>Danh sách hóa đơn</h5></div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead><tr><th>Mã</th><th>Khách hàng</th><th>Món</th><th>Tổng tiền</th><th>Trạng thái</th><th></th></tr></thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->invoice_code }}</td>
                            <td>{{ $invoice->customer_name ?? 'Khách lẻ' }}<br><small>{{ $invoice->customer_phone }}</small></td>
                            <td>
                                @foreach ($invoice->items as $item)
                                    <div>{{ $item->food_name }} x {{ $item->quantity }}</div>
                                @endforeach
                            </td>
                            <td>{{ number_format($invoice->total, 0, ',', '.') }}đ</td>
                            <td><span class="status-badge status-coming">{{ $invoice->payment_status }}</span></td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('admin.food-invoices.destroy', $invoice) }}">
                                    @csrf @method('DELETE')
                                    <button class="action-btn action-delete" type="submit"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">Chưa có hóa đơn đồ ăn.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $invoices->links() }}</div>
    </div>
</div>
@endsection
