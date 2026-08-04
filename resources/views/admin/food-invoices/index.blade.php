@extends('layouts.admin')

@section('title', 'Hóa đơn đồ ăn & combo')
@section('page-title', 'Hóa đơn đồ ăn & combo')
@section('page-subtitle', 'Tạo hóa đơn bắp nước, combo đồ ăn và theo dõi thanh toán tại quầy')

@section('content')
@php
    $statusLabels = [
        'pending' => 'Chờ thanh toán',
        'paid' => 'Đã thanh toán',
        'cancelled' => 'Đã hủy',
    ];

    $statusClasses = [
        'pending' => 'is-pending',
        'paid' => 'is-paid',
        'cancelled' => 'is-cancelled',
    ];

    $paymentMethods = [
        'tiền mặt' => 'Tiền mặt',
        'chuyển khoản' => 'Chuyển khoản',
        'thẻ' => 'Thẻ',
    ];

    $activeFilters = collect([
        request('q'),
        request('payment_status'),
        request('from'),
        request('to'),
    ])->filter()->count();
@endphp

<div class="food-invoice-page">
    @include('admin.partials.flash')

    @if ($errors->any())
        <div class="food-invoice-alert is-error">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <section class="food-invoice-hero food-invoice-clean-hero">
        <div class="food-invoice-hero-copy">
            <span class="food-invoice-eyebrow">
                <i class="fa-solid fa-burger"></i>
                Quầy đồ ăn
            </span>
            <h2>Hóa đơn đồ ăn & combo</h2>
            <p>
                Tạo hóa đơn, chọn món nhanh và theo dõi thanh toán tại quầy.
            </p>
        </div>

        <div class="food-invoice-hero-actions">
            <a href="{{ route('admin.foods.index') }}" class="food-invoice-action is-soft">
                <i class="fa-solid fa-boxes-stacked"></i>
                Kho đồ ăn
            </a>
            <a href="{{ route('admin.foods.create') }}" class="food-invoice-action">
                <i class="fa-solid fa-plus"></i>
                Thêm món
            </a>
        </div>
    </section>

    <section class="food-invoice-stats food-invoice-clean-stats">
        <div class="food-invoice-stat">
            <span>Hóa đơn</span>
            <strong>{{ $summary['count'] }}</strong>
        </div>
        <div class="food-invoice-stat">
            <span>Đã thanh toán</span>
            <strong>{{ $summary['paid_count'] }}</strong>
        </div>
        <div class="food-invoice-stat">
            <span>Chờ thanh toán</span>
            <strong>{{ $summary['pending_count'] }}</strong>
        </div>
        <div class="food-invoice-stat">
            <span>Doanh thu đã thu</span>
            <strong>{{ number_format((float) $summary['paid_total'], 0, ',', '.') }}đ</strong>
        </div>
    </section>

    @if (($lowStockFoods ?? collect())->isNotEmpty())
        <section class="food-stock-alert">
            <div>
                <i class="fa-solid fa-triangle-exclamation"></i>
                <strong>Cảnh báo kho thấp</strong>
            </div>
            <p>
                @foreach ($lowStockFoods as $food)
                    <span>{{ $food->name }} còn {{ $food->stock_quantity }}</span>
                @endforeach
            </p>
            <a href="{{ route('admin.foods.index', ['status' => 'low']) }}">
                Mở kho
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </section>
    @endif

    <div class="food-invoice-workspace">
        <form method="POST" action="{{ route('admin.food-invoices.store') }}" class="food-invoice-builder" id="foodInvoiceForm">
            @csrf

            <div class="food-panel-head">
                <div>
                    <span class="food-invoice-eyebrow">Tạo mới</span>
                    <h3>Hóa đơn tại quầy</h3>
                    <p>Chọn món, nhập số lượng và lưu hóa đơn.</p>
                </div>
            </div>

            <div class="food-builder-section">
                <div class="food-section-title">
                    <span>Khách hàng</span>
                    <small>Có thể bỏ trống với khách lẻ.</small>
                </div>
                <div class="food-form-grid">
                    <label class="food-field">
                        <span>Tên khách</span>
                        <input name="customer_name" value="{{ old('customer_name') }}" class="admin-input" placeholder="VD: Nguyễn Văn A">
                    </label>
                    <label class="food-field">
                        <span>Số điện thoại</span>
                        <input name="customer_phone" value="{{ old('customer_phone') }}" class="admin-input" placeholder="VD: 0987654321">
                    </label>
                </div>
            </div>

            <div class="food-builder-section">
                <div class="food-section-title">
                    <span>Món trong hóa đơn</span>
                    <button type="button" class="food-mini-btn" id="addItemBtn">
                        <i class="fa-solid fa-plus"></i>
                        Thêm dòng
                    </button>
                </div>

                <div class="food-quick-rail">
                    @forelse ($quickFoods as $food)
                        <button
                            type="button"
                            class="food-chip"
                            data-food-id="{{ $food['food_id'] }}"
                            data-variant-id="{{ $food['variant_id'] }}"
                            data-food-name="{{ $food['label'] }}"
                            data-food-price="{{ $food['price'] }}"
                            title="Còn {{ $food['stock'] }} phần"
                        >
                            <span>{{ $food['label'] }}</span>
                            <em>{{ $food['stock'] }}</em>
                        </button>
                    @empty
                        <a href="{{ route('admin.foods.index') }}" class="food-chip is-empty">
                            Chưa có món đang bán, mở cấu hình menu
                        </a>
                    @endforelse
                </div>

                <div id="foodItems" class="food-item-list"></div>
            </div>

            <div class="food-builder-section">
                <div class="food-section-title">
                    <span>Thanh toán</span>
                    <small>Chọn trạng thái đúng để hệ thống trừ kho.</small>
                </div>
                <div class="food-payment-grid">
                    <label class="food-field">
                        <span>Giảm giá</span>
                        <input name="discount" id="discountInput" type="number" min="0" value="{{ old('discount', 0) }}" class="admin-input" placeholder="0">
                    </label>
                    <label class="food-field">
                        <span>Trạng thái</span>
                        <select name="payment_status" class="admin-input">
                            @foreach ($statusLabels as $value => $label)
                                <option value="{{ $value }}" @selected(old('payment_status', 'pending') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="food-field">
                        <span>Phương thức</span>
                        <select name="payment_method" class="admin-input">
                            <option value="">Chọn phương thức</option>
                            @foreach ($paymentMethods as $value => $label)
                                <option value="{{ $value }}" @selected(old('payment_method') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="food-total-box">
                        <span>Tổng thanh toán</span>
                        <strong id="grandTotal">0đ</strong>
                    </div>
                </div>

                <div class="food-total-breakdown">
                    <span>Tạm tính: <strong id="subtotalText">0đ</strong></span>
                    <span>Giảm giá: <strong id="discountText">0đ</strong></span>
                </div>
            </div>

            <label class="food-field">
                <span>Ghi chú</span>
                <textarea name="note" class="admin-input food-note-input" placeholder="Ghi chú thêm cho hóa đơn">{{ old('note') }}</textarea>
            </label>

            <button class="food-save-btn" type="submit">
                <i class="fa-solid fa-floppy-disk"></i>
                Lưu hóa đơn
            </button>
        </form>

        <section class="food-invoice-list-panel">
            <div class="food-panel-head">
                <div>
                    <span class="food-invoice-eyebrow">Danh sách</span>
                    <h3>Hóa đơn gần đây</h3>
                    <p>Theo dõi các hóa đơn đã tạo.</p>
                </div>
                <div class="food-list-counter">
                    <strong>{{ $invoices->total() }}</strong>
                    <span>kết quả</span>
                </div>
            </div>

            <form method="GET" class="food-filter-bar">
                <label class="food-search-field">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input name="q" value="{{ request('q') }}" placeholder="Tìm hóa đơn, khách hàng, món...">
                </label>
                <select name="payment_status" class="admin-input">
                    <option value="">Tất cả trạng thái</option>
                    @foreach ($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected(request('payment_status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="date" name="from" value="{{ request('from') }}" class="admin-input">
                <input type="date" name="to" value="{{ request('to') }}" class="admin-input">
                <button class="food-filter-btn" type="submit">
                    <i class="fa-solid fa-filter"></i>
                    Lọc
                    @if ($activeFilters)
                        <span>{{ $activeFilters }}</span>
                    @endif
                </button>
                @if ($activeFilters)
                    <a href="{{ route('admin.food-invoices.index') }}" class="food-reset-btn">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </form>

            <div class="food-invoice-list">
                @forelse ($invoices as $invoice)
                    <article class="food-invoice-card">
                        <div class="food-invoice-card-main">
                            <div class="food-invoice-code">
                                <span>Mã hóa đơn</span>
                                <strong>{{ $invoice->invoice_code }}</strong>
                                <small>{{ $invoice->created_at->format('d/m/Y H:i') }}</small>
                            </div>

                            <div class="food-invoice-customer">
                                <span>Khách hàng</span>
                                <strong>{{ $invoice->customer_name ?: 'Khách lẻ' }}</strong>
                                <small>{{ $invoice->customer_phone ?: 'Không có SĐT' }}</small>
                            </div>

                            <div class="food-invoice-total">
                                <span>Tổng tiền</span>
                                <strong>{{ number_format((float) $invoice->total, 0, ',', '.') }}đ</strong>
                                @if ((float) $invoice->discount > 0)
                                    <small>Giảm {{ number_format((float) $invoice->discount, 0, ',', '.') }}đ</small>
                                @else
                                    <small>Không giảm giá</small>
                                @endif
                            </div>

                            <!-- 🌟 TRẠNG THÁI HIỂN THỊ DẠNG BADGE CỐ ĐỊNH (CHỈ ĐỌC) -->
                            <div class="food-status-badge {{ $statusClasses[$invoice->payment_status] ?? '' }}">
                                <span>{{ $statusLabels[$invoice->payment_status] ?? $invoice->payment_status }}</span>
                            </div>

                            <form method="POST" action="{{ route('admin.food-invoices.destroy', $invoice) }}" onsubmit="return confirm('Xóa hóa đơn {{ $invoice->invoice_code }}?')">
                                @csrf
                                @method('DELETE')
                                <button class="food-delete-btn" type="submit" title="Xóa hóa đơn">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>

                        <div class="food-invoice-items">
                            @foreach ($invoice->items->take(3) as $item)
                                <span class="food-invoice-item-pill">
                                    <b>{{ $item->food_name }}</b>
                                    <em>x{{ $item->quantity }}</em>
                                    <small>{{ number_format((float) $item->total_price, 0, ',', '.') }}đ</small>
                                </span>
                            @endforeach
                            @if ($invoice->items->count() > 3)
                                <span class="food-invoice-item-pill is-more">
                                    +{{ $invoice->items->count() - 3 }} món khác
                                </span>
                            @endif
                        </div>

                        @if ($invoice->payment_method || $invoice->note)
                            <div class="food-invoice-footnote">
                                @if ($invoice->payment_method)
                                    <span><i class="fa-solid fa-credit-card"></i>{{ $invoice->payment_method }}</span>
                                @endif
                                @if ($invoice->note)
                                    <span><i class="fa-solid fa-note-sticky"></i>{{ $invoice->note }}</span>
                                @endif
                            </div>
                        @endif
                    </article>
                @empty
                    <div class="food-invoice-empty">
                        <i class="fa-solid fa-receipt"></i>
                        <h3>Chưa có hóa đơn phù hợp</h3>
                        <p>Thử đổi bộ lọc hoặc tạo hóa đơn bắp nước đầu tiên ở khung bên trái.</p>
                    </div>
                @endforelse
            </div>

            <div class="admin-food-pagination">
                {{ $invoices->links() }}
            </div>
        </section>
    </div>
</div>

<style>
    /* 🌟 CSS BADGE TRẠNG THÁI CỐ ĐỊNH CHO HÓA ĐƠN ĐỒ ĂN */
    .food-status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .food-status-badge.is-paid {
        background: rgba(34, 197, 94, .15);
        color: #4ade80;
        border: 1px solid rgba(34, 197, 94, .3);
    }

    .food-status-badge.is-pending {
        background: rgba(234, 179, 8, .15);
        color: #facc15;
        border: 1px solid rgba(234, 179, 8, .3);
    }

    .food-status-badge.is-cancelled {
        background: rgba(239, 68, 68, .15);
        color: #f87171;
        border: 1px solid rgba(239, 68, 68, .3);
    }
</style>

<template id="foodItemTemplate">
    <div class="food-item-row">
        <input data-field="food_id" type="hidden" class="food-id">
        <input data-field="food_variant_id" type="hidden" class="food-variant-id">
        <label class="food-item-name">
            <span>Tên món</span>
            <input data-field="food_name" class="admin-input food-name" placeholder="Tên món" required>
        </label>
        <label>
            <span>SL</span>
            <input data-field="quantity" type="number" min="1" value="1" class="admin-input food-quantity" required>
        </label>
        <label>
            <span>Đơn giá</span>
            <input data-field="unit_price" type="number" min="0" class="admin-input food-price" placeholder="0" required>
        </label>
        <div class="food-line-total-box">
            <span>Thành tiền</span>
            <strong class="food-line-total">0đ</strong>
        </div>
        <button type="button" class="food-remove-btn" title="Xóa món">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
</template>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const itemsBox = document.getElementById('foodItems');
    const template = document.getElementById('foodItemTemplate');
    const addItemBtn = document.getElementById('addItemBtn');
    const discountInput = document.getElementById('discountInput');
    const subtotalText = document.getElementById('subtotalText');
    const discountText = document.getElementById('discountText');
    const grandTotal = document.getElementById('grandTotal');
    const oldItems = Object.values(@json(old('items', [])) || {});

    function money(value) {
        return Number(value || 0).toLocaleString('vi-VN') + 'đ';
    }

    function renumberRows() {
        [...itemsBox.querySelectorAll('.food-item-row')].forEach((row, index) => {
            row.querySelectorAll('[data-field]').forEach((input) => {
                input.name = `items[${index}][${input.dataset.field}]`;
            });
        });
    }

    function calculate() {
        let subtotal = 0;

        itemsBox.querySelectorAll('.food-item-row').forEach((row) => {
            const quantity = Number(row.querySelector('.food-quantity').value || 0);
            const price = Number(row.querySelector('.food-price').value || 0);
            const lineTotal = quantity * price;
            subtotal += lineTotal;
            row.querySelector('.food-line-total').textContent = money(lineTotal);
        });

        const discount = Math.max(0, Number(discountInput.value || 0));
        const total = Math.max(subtotal - discount, 0);

        subtotalText.textContent = money(subtotal);
        discountText.textContent = money(discount);
        grandTotal.textContent = money(total);
    }

    function fillRow(row, food = {}) {
        row.querySelector('.food-id').value = food.food_id || food.id || '';
        row.querySelector('.food-variant-id').value = food.food_variant_id || food.variant_id || '';
        row.querySelector('.food-name').value = food.food_name || food.name || '';
        row.querySelector('.food-quantity').value = food.quantity || 1;
        row.querySelector('.food-price').value = food.unit_price || food.price || '';
    }

    function findBlankRow() {
        return [...itemsBox.querySelectorAll('.food-item-row')].find((row) => {
            return !row.querySelector('.food-id').value
                && !row.querySelector('.food-name').value
                && !row.querySelector('.food-price').value;
        });
    }

    function addRow(food = {}) {
        const hasFoodData = Boolean(food.food_id || food.id || food.food_name || food.name);
        const blankRow = hasFoodData ? findBlankRow() : null;

        if (blankRow) {
            fillRow(blankRow, food);
            renumberRows();
            calculate();
            return;
        }

        const fragment = template.content.cloneNode(true);
        const row = fragment.querySelector('.food-item-row');

        fillRow(row, food);

        itemsBox.appendChild(fragment);
        renumberRows();
        calculate();

        if (!food.food_name && !food.name) {
            row.querySelector('.food-name').focus();
        }
    }

    addItemBtn.addEventListener('click', function () {
        addRow();
    });

    document.querySelectorAll('[data-food-name]').forEach((button) => {
        button.addEventListener('click', function () {
            addRow({
                id: button.dataset.foodId,
                variant_id: button.dataset.variantId || '',
                name: button.dataset.foodName,
                price: button.dataset.foodPrice,
                quantity: 1,
            });
        });
    });

    itemsBox.addEventListener('input', calculate);
    itemsBox.addEventListener('click', function (event) {
        const removeBtn = event.target.closest('.food-remove-btn');

        if (!removeBtn) {
            return;
        }

        removeBtn.closest('.food-item-row').remove();

        if (!itemsBox.querySelector('.food-item-row')) {
            addRow();
        }

        renumberRows();
        calculate();
    });

    discountInput.addEventListener('input', calculate);

    if (oldItems.length) {
        oldItems.forEach((item) => addRow(item));
    } else {
        addRow();
    }
});
</script>
@endpush