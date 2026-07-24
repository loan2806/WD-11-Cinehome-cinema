<?php $__env->startSection('title', 'Hóa đơn đồ ăn & combo'); ?>
<?php $__env->startSection('page-title', 'Hóa đơn đồ ăn & combo'); ?>
<?php $__env->startSection('page-subtitle', 'Tạo hóa đơn bắp nước, combo đồ ăn và theo dõi thanh toán tại quầy'); ?>

<?php $__env->startSection('content'); ?>
<?php
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
?>

<div class="food-invoice-page">
    <?php echo $__env->make('admin.partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if($errors->any()): ?>
        <div class="food-invoice-alert is-error">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span><?php echo e($errors->first()); ?></span>
        </div>
    <?php endif; ?>

    <section class="food-invoice-hero food-invoice-clean-hero">
        <div class="food-invoice-hero-copy">
            <span class="food-invoice-eyebrow">
                <i class="fa-solid fa-burger"></i>
                Quầy đồ ăn
            </span>
            <h2>Hóa đơn đồ ăn & combo</h2>
            <p>
                Tạo hóa đơn, chọn món nhanh và cập nhật trạng thái thanh toán tại quầy.
            </p>
        </div>

        <div class="food-invoice-hero-actions">
            <a href="<?php echo e(route('admin.foods.index')); ?>" class="food-invoice-action is-soft">
                <i class="fa-solid fa-boxes-stacked"></i>
                Kho đồ ăn
            </a>
            <a href="<?php echo e(route('admin.foods.create')); ?>" class="food-invoice-action">
                <i class="fa-solid fa-plus"></i>
                Thêm món
            </a>
        </div>
    </section>

    <section class="food-invoice-stats food-invoice-clean-stats">
        <div class="food-invoice-stat">
            <span>Hóa đơn</span>
            <strong><?php echo e($summary['count']); ?></strong>
        </div>
        <div class="food-invoice-stat">
            <span>Đã thanh toán</span>
            <strong><?php echo e($summary['paid_count']); ?></strong>
        </div>
        <div class="food-invoice-stat">
            <span>Chờ thanh toán</span>
            <strong><?php echo e($summary['pending_count']); ?></strong>
        </div>
        <div class="food-invoice-stat">
            <span>Doanh thu đã thu</span>
            <strong><?php echo e(number_format((float) $summary['paid_total'], 0, ',', '.')); ?>đ</strong>
        </div>
    </section>

    <?php if(($lowStockFoods ?? collect())->isNotEmpty()): ?>
        <section class="food-stock-alert">
            <div>
                <i class="fa-solid fa-triangle-exclamation"></i>
                <strong>Cảnh báo kho thấp</strong>
            </div>
            <p>
                <?php $__currentLoopData = $lowStockFoods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $food): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span><?php echo e($food->name); ?> còn <?php echo e($food->stock_quantity); ?></span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </p>
            <a href="<?php echo e(route('admin.foods.index', ['status' => 'low'])); ?>">
                Mở kho
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </section>
    <?php endif; ?>

    <div class="food-invoice-workspace">
        <form method="POST" action="<?php echo e(route('admin.food-invoices.store')); ?>" class="food-invoice-builder" id="foodInvoiceForm">
            <?php echo csrf_field(); ?>

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
                        <input name="customer_name" value="<?php echo e(old('customer_name')); ?>" class="admin-input" placeholder="VD: Nguyễn Văn A">
                    </label>
                    <label class="food-field">
                        <span>Số điện thoại</span>
                        <input name="customer_phone" value="<?php echo e(old('customer_phone')); ?>" class="admin-input" placeholder="VD: 0987654321">
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
                    <?php $__empty_1 = true; $__currentLoopData = $quickFoods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $food): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <button
                            type="button"
                            class="food-chip"
                            data-food-id="<?php echo e($food->id); ?>"
                            data-food-name="<?php echo e($food->name); ?>"
                            data-food-price="<?php echo e($food->price); ?>"
                            title="Còn <?php echo e($food->stock_quantity); ?> phần"
                        >
                            <span><?php echo e($food->name); ?></span>
                            <em><?php echo e($food->stock_quantity); ?></em>
                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <a href="<?php echo e(route('admin.foods.index')); ?>" class="food-chip is-empty">
                            Chưa có món đang bán, mở cấu hình menu
                        </a>
                    <?php endif; ?>
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
                        <input name="discount" id="discountInput" type="number" min="0" value="<?php echo e(old('discount', 0)); ?>" class="admin-input" placeholder="0">
                    </label>
                    <label class="food-field">
                        <span>Trạng thái</span>
                        <select name="payment_status" class="admin-input">
                            <?php $__currentLoopData = $statusLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($value); ?>" <?php if(old('payment_status', 'pending') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </label>
                    <label class="food-field">
                        <span>Phương thức</span>
                        <select name="payment_method" class="admin-input">
                            <option value="">Chọn phương thức</option>
                            <?php $__currentLoopData = $paymentMethods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($value); ?>" <?php if(old('payment_method') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                <textarea name="note" class="admin-input food-note-input" placeholder="Ghi chú thêm cho hóa đơn"><?php echo e(old('note')); ?></textarea>
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
                    <p>Theo dõi và cập nhật trạng thái thanh toán.</p>
                </div>
                <div class="food-list-counter">
                    <strong><?php echo e($invoices->total()); ?></strong>
                    <span>kết quả</span>
                </div>
            </div>

            <form method="GET" class="food-filter-bar">
                <label class="food-search-field">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input name="q" value="<?php echo e(request('q')); ?>" placeholder="Tìm hóa đơn, khách hàng, món...">
                </label>
                <select name="payment_status" class="admin-input">
                    <option value="">Tất cả trạng thái</option>
                    <?php $__currentLoopData = $statusLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($value); ?>" <?php if(request('payment_status') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <input type="date" name="from" value="<?php echo e(request('from')); ?>" class="admin-input">
                <input type="date" name="to" value="<?php echo e(request('to')); ?>" class="admin-input">
                <button class="food-filter-btn" type="submit">
                    <i class="fa-solid fa-filter"></i>
                    Lọc
                    <?php if($activeFilters): ?>
                        <span><?php echo e($activeFilters); ?></span>
                    <?php endif; ?>
                </button>
                <?php if($activeFilters): ?>
                    <a href="<?php echo e(route('admin.food-invoices.index')); ?>" class="food-reset-btn">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                <?php endif; ?>
            </form>

            <div class="food-invoice-list">
                <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <article class="food-invoice-card">
                        <div class="food-invoice-card-main">
                            <div class="food-invoice-code">
                                <span>Mã hóa đơn</span>
                                <strong><?php echo e($invoice->invoice_code); ?></strong>
                                <small><?php echo e($invoice->created_at->format('d/m/Y H:i')); ?></small>
                            </div>

                            <div class="food-invoice-customer">
                                <span>Khách hàng</span>
                                <strong><?php echo e($invoice->customer_name ?: 'Khách lẻ'); ?></strong>
                                <small><?php echo e($invoice->customer_phone ?: 'Không có SĐT'); ?></small>
                            </div>

                            <div class="food-invoice-total">
                                <span>Tổng tiền</span>
                                <strong><?php echo e(number_format((float) $invoice->total, 0, ',', '.')); ?>đ</strong>
                                <?php if((float) $invoice->discount > 0): ?>
                                    <small>Giảm <?php echo e(number_format((float) $invoice->discount, 0, ',', '.')); ?>đ</small>
                                <?php else: ?>
                                    <small>Không giảm giá</small>
                                <?php endif; ?>
                            </div>

                            <form method="POST" action="<?php echo e(route('admin.food-invoices.update-status', $invoice)); ?>" class="food-status-form">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PATCH'); ?>
                                <details class="food-status-menu">
                                    <summary class="food-status-trigger <?php echo e($statusClasses[$invoice->payment_status] ?? ''); ?>">
                                        <span><?php echo e($statusLabels[$invoice->payment_status] ?? $invoice->payment_status); ?></span>
                                        <i class="fa-solid fa-chevron-down"></i>
                                    </summary>
                                    <div class="food-status-panel">
                                        <?php $__currentLoopData = $statusLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <button
                                                type="submit"
                                                name="payment_status"
                                                value="<?php echo e($value); ?>"
                                                class="food-status-option <?php echo e($statusClasses[$value] ?? ''); ?> <?php if($invoice->payment_status === $value): ?> is-current <?php endif; ?>"
                                            >
                                                <span><?php echo e($label); ?></span>
                                                <?php if($invoice->payment_status === $value): ?>
                                                    <i class="fa-solid fa-check"></i>
                                                <?php endif; ?>
                                            </button>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </details>
                            </form>

                            <form method="POST" action="<?php echo e(route('admin.food-invoices.destroy', $invoice)); ?>" onsubmit="return confirm('Xóa hóa đơn <?php echo e($invoice->invoice_code); ?>?')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button class="food-delete-btn" type="submit" title="Xóa hóa đơn">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>

                        <div class="food-invoice-items">
                            <?php $__currentLoopData = $invoice->items->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="food-invoice-item-pill">
                                    <b><?php echo e($item->food_name); ?></b>
                                    <em>x<?php echo e($item->quantity); ?></em>
                                    <small><?php echo e(number_format((float) $item->total_price, 0, ',', '.')); ?>đ</small>
                                </span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php if($invoice->items->count() > 3): ?>
                                <span class="food-invoice-item-pill is-more">
                                    +<?php echo e($invoice->items->count() - 3); ?> món khác
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if($invoice->payment_method || $invoice->note): ?>
                            <div class="food-invoice-footnote">
                                <?php if($invoice->payment_method): ?>
                                    <span><i class="fa-solid fa-credit-card"></i><?php echo e($invoice->payment_method); ?></span>
                                <?php endif; ?>
                                <?php if($invoice->note): ?>
                                    <span><i class="fa-solid fa-note-sticky"></i><?php echo e($invoice->note); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="food-invoice-empty">
                        <i class="fa-solid fa-receipt"></i>
                        <h3>Chưa có hóa đơn phù hợp</h3>
                        <p>Thử đổi bộ lọc hoặc tạo hóa đơn bắp nước đầu tiên ở khung bên trái.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="admin-food-pagination">
                <?php echo e($invoices->links()); ?>

            </div>
        </section>
    </div>
</div>

<template id="foodItemTemplate">
    <div class="food-item-row">
        <input data-field="food_id" type="hidden" class="food-id">
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
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const itemsBox = document.getElementById('foodItems');
    const template = document.getElementById('foodItemTemplate');
    const addItemBtn = document.getElementById('addItemBtn');
    const discountInput = document.getElementById('discountInput');
    const subtotalText = document.getElementById('subtotalText');
    const discountText = document.getElementById('discountText');
    const grandTotal = document.getElementById('grandTotal');
    const oldItems = Object.values(<?php echo json_encode(old('items', []), 512) ?> || {});

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
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\DATN\WD-11-Cinehome-cinema\resources\views/admin/food-invoices/index.blade.php ENDPATH**/ ?>