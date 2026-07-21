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
        'pending' => 'food-status-pending',
        'paid' => 'food-status-paid',
        'cancelled' => 'food-status-cancelled',
    ];

?>

<?php echo $__env->make('admin.partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php if($errors->any()): ?>
    <div class="mb-5 rounded-2xl border border-red-500/30 bg-red-500/10 px-5 py-4 text-red-200">
        <?php echo e($errors->first()); ?>

    </div>
<?php endif; ?>

<div class="mb-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
    <div class="stat-card">
        <div class="stat-label">Hóa đơn</div>
        <div class="stat-value"><?php echo e($summary['count']); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Đã thanh toán</div>
        <div class="stat-value"><?php echo e($summary['paid_count']); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Chờ thanh toán</div>
        <div class="stat-value"><?php echo e($summary['pending_count']); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Doanh thu đã thu</div>
        <div class="stat-value"><?php echo e(number_format((float) $summary['paid_total'], 0, ',', '.')); ?>đ</div>
    </div>
</div>

<?php if(($lowStockFoods ?? collect())->isNotEmpty()): ?>
    <div class="mb-5 rounded-2xl border border-yellow-500/30 bg-yellow-500/10 px-5 py-4 text-sm text-yellow-100">
        <strong class="text-[#f4c56a]">Cảnh báo kho:</strong>
        <?php $__currentLoopData = $lowStockFoods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $food): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <span class="ml-2"><?php echo e($food->name); ?> còn <?php echo e($food->stock_quantity); ?></span>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route('admin.foods.index', ['status' => 'low'])); ?>" class="ml-3 font-black text-[#f4c56a] no-underline">Mở kho</a>
    </div>
<?php endif; ?>

<div class="grid gap-6 xl:grid-cols-[460px_1fr]">
    <form method="POST" action="<?php echo e(route('admin.food-invoices.store')); ?>" class="admin-panel" id="foodInvoiceForm">
        <?php echo csrf_field(); ?>
        <div class="panel-header">
            <h5>Tạo hóa đơn mới</h5>
        </div>

        <div class="panel-body space-y-5">
            <div class="grid gap-3 md:grid-cols-2">
                <input name="customer_name" value="<?php echo e(old('customer_name')); ?>" class="admin-input" placeholder="Tên khách hàng">
                <input name="customer_phone" value="<?php echo e(old('customer_phone')); ?>" class="admin-input" placeholder="Số điện thoại">
            </div>

            <div>
                <div class="mb-2 flex items-center justify-between gap-3">
                    <label class="text-sm font-black text-[#f4c56a]">Món trong hóa đơn</label>
                    <button type="button" class="food-mini-btn" id="addItemBtn">
                        <i class="fa-solid fa-plus"></i>
                        Thêm món
                    </button>
                </div>

                <div class="mb-3 flex flex-wrap gap-2">
                    <?php $__empty_1 = true; $__currentLoopData = $quickFoods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $food): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <button
                            type="button"
                            class="food-chip"
                            data-food-id="<?php echo e($food->id); ?>"
                            data-food-name="<?php echo e($food->name); ?>"
                            data-food-price="<?php echo e($food->price); ?>"
                            title="Còn <?php echo e($food->stock_quantity); ?> phần"
                        >
                            <?php echo e($food->name); ?>

                            <span class="ml-1 text-white/60">(<?php echo e($food->stock_quantity); ?>)</span>
                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <a href="<?php echo e(route('admin.foods.index')); ?>" class="food-chip no-underline">
                            Chưa có món trong kho, mở cấu hình menu
                        </a>
                    <?php endif; ?>
                </div>

                <div id="foodItems" class="space-y-3"></div>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-4">
                <div class="grid gap-3 md:grid-cols-2">
                    <input name="discount" id="discountInput" type="number" min="0" value="<?php echo e(old('discount', 0)); ?>" class="admin-input" placeholder="Giảm giá">
                    <select name="payment_status" class="admin-input">
                        <?php $__currentLoopData = $statusLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($value); ?>" <?php if(old('payment_status', 'pending') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="mt-3 grid gap-3 md:grid-cols-2">
                    <select name="payment_method" class="admin-input">
                        <option value="">Chọn phương thức</option>
                        <option value="tiền mặt" <?php if(old('payment_method') === 'tiền mặt'): echo 'selected'; endif; ?>>Tiền mặt</option>
                        <option value="chuyển khoản" <?php if(old('payment_method') === 'chuyển khoản'): echo 'selected'; endif; ?>>Chuyển khoản</option>
                        <option value="thẻ" <?php if(old('payment_method') === 'thẻ'): echo 'selected'; endif; ?>>Thẻ</option>
                    </select>
                    <div class="food-total-box">
                        <span>Tổng thanh toán</span>
                        <strong id="grandTotal">0đ</strong>
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-3 text-sm text-gray-300">
                    <div>Tạm tính: <strong id="subtotalText" class="text-white">0đ</strong></div>
                    <div>Giảm giá: <strong id="discountText" class="text-white">0đ</strong></div>
                </div>
            </div>

            <textarea name="note" class="admin-input min-h-[90px]" placeholder="Ghi chú"><?php echo e(old('note')); ?></textarea>

            <button class="btn-admin w-full" type="submit">
                <i class="fa-solid fa-floppy-disk"></i>
                Lưu hóa đơn
            </button>
        </div>
    </form>

    <div class="admin-panel">
        <div class="panel-header">
            <div>
                <h5>Danh sách hóa đơn</h5>
                <p class="m-0 mt-1 text-sm text-gray-400">Tìm nhanh theo mã, khách hàng, số điện thoại hoặc tên món</p>
            </div>
        </div>

        <form method="GET" class="panel-body grid gap-3 border-b border-white/10 md:grid-cols-[1.2fr_.7fr_.7fr_.7fr_auto]">
            <input name="q" value="<?php echo e(request('q')); ?>" class="admin-input" placeholder="Tìm hóa đơn, khách hàng, món...">
            <select name="payment_status" class="admin-input">
                <option value="">Tất cả trạng thái</option>
                <?php $__currentLoopData = $statusLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($value); ?>" <?php if(request('payment_status') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <input type="date" name="from" value="<?php echo e(request('from')); ?>" class="admin-input">
            <input type="date" name="to" value="<?php echo e(request('to')); ?>" class="admin-input">
            <button class="btn-admin" type="submit">
                <i class="fa-solid fa-filter"></i>
                Lọc
            </button>
        </form>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Khách hàng</th>
                        <th>Món</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <strong><?php echo e($invoice->invoice_code); ?></strong>
                                <div class="text-xs text-gray-500"><?php echo e($invoice->created_at->format('d/m/Y H:i')); ?></div>
                            </td>
                            <td>
                                <strong><?php echo e($invoice->customer_name ?: 'Khách lẻ'); ?></strong>
                                <div class="text-xs text-gray-500"><?php echo e($invoice->customer_phone ?: 'Không có SĐT'); ?></div>
                            </td>
                            <td>
                                <div class="space-y-1">
                                    <?php $__currentLoopData = $invoice->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div>
                                            <?php echo e($item->food_name); ?>

                                            <span class="text-gray-500">x<?php echo e($item->quantity); ?></span>
                                            <span class="text-gray-500">· <?php echo e(number_format((float) $item->total_price, 0, ',', '.')); ?>đ</span>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </td>
                            <td>
                                <strong><?php echo e(number_format((float) $invoice->total, 0, ',', '.')); ?>đ</strong>
                                <?php if((float) $invoice->discount > 0): ?>
                                    <div class="text-xs text-gray-500">Giảm <?php echo e(number_format((float) $invoice->discount, 0, ',', '.')); ?>đ</div>
                                <?php endif; ?>
                            </td>
                            <td>
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
                            </td>
                            <td class="text-end">
                                <form method="POST" action="<?php echo e(route('admin.food-invoices.destroy', $invoice)); ?>" onsubmit="return confirm('Xóa hóa đơn <?php echo e($invoice->invoice_code); ?>?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button class="action-btn action-delete" type="submit" title="Xóa hóa đơn">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="text-center text-gray-400">Chưa có hóa đơn đồ ăn phù hợp.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="p-4"><?php echo e($invoices->links()); ?></div>
    </div>
</div>

<template id="foodItemTemplate">
    <div class="food-item-row grid gap-2 rounded-2xl border border-white/10 bg-white/[0.03] p-3 md:grid-cols-[1fr_90px_130px_36px]">
        <input data-field="food_id" type="hidden" class="food-id">
        <input data-field="food_name" class="admin-input food-name" placeholder="Tên món" required>
        <input data-field="quantity" type="number" min="1" value="1" class="admin-input food-quantity" required>
        <input data-field="unit_price" type="number" min="0" class="admin-input food-price" placeholder="Đơn giá" required>
        <button type="button" class="food-remove-btn" title="Xóa món"><i class="fa-solid fa-xmark"></i></button>
        <div class="md:col-span-4 text-right text-sm text-gray-400">
            Thành tiền: <strong class="food-line-total text-white">0đ</strong>
        </div>
    </div>
</template>

<style>
    .food-chip,
    .food-mini-btn,
    .food-remove-btn {
        border: 0;
        color: #fff;
        font-weight: 900;
        transition: transform .2s ease, opacity .2s ease;
    }

    .food-chip {
        border-radius: 999px;
        background: rgba(217, 154, 50, .14);
        color: #f4c56a;
        padding: 8px 12px;
        font-size: 12px;
    }

    .food-mini-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 12px;
        background: rgba(255, 255, 255, .08);
        padding: 9px 12px;
        font-size: 12px;
    }

    .food-chip:hover,
    .food-mini-btn:hover,
    .food-remove-btn:hover {
        transform: translateY(-1px);
    }

    .food-remove-btn {
        display: grid;
        place-items: center;
        min-height: 42px;
        border-radius: 12px;
        background: rgba(239, 68, 68, .18);
        color: #fecaca;
    }

    .food-total-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 42px;
        border-radius: 12px;
        background: rgba(217, 154, 50, .12);
        padding: 0 14px;
    }

    .food-total-box span {
        color: #d1d5db;
        font-size: 13px;
        font-weight: 800;
    }

    .food-total-box strong {
        color: #f4c56a;
        font-size: 18px;
    }

    .food-status-form {
        position: relative;
        display: inline-block;
    }

    .food-status-menu {
        position: relative;
    }

    .food-status-trigger {
        min-width: 148px;
        min-height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        border: 1px solid rgba(255, 255, 255, .12);
        border-radius: 999px;
        padding: 0 12px;
        color: #f8fafc;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
        list-style: none;
        user-select: none;
        transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
    }

    .food-status-trigger::-webkit-details-marker {
        display: none;
    }

    .food-status-trigger:hover,
    .food-status-menu[open] .food-status-trigger {
        border-color: rgba(217, 154, 50, .65);
        box-shadow: 0 0 0 3px rgba(217, 154, 50, .14);
    }

    .food-status-trigger i {
        font-size: 10px;
        opacity: .8;
        transition: transform .2s ease;
    }

    .food-status-menu[open] .food-status-trigger i {
        transform: rotate(180deg);
    }

    .food-status-panel {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        z-index: 40;
        min-width: 172px;
        padding: 6px;
        border: 1px solid rgba(217, 154, 50, .28);
        border-radius: 16px;
        background: #141414;
        box-shadow: 0 18px 45px rgba(0, 0, 0, .45);
    }

    .food-status-option {
        width: 100%;
        min-height: 36px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        border: 1px solid transparent;
        border-radius: 12px;
        padding: 0 10px;
        color: #d1d5db;
        background: transparent;
        font-size: 12px;
        font-weight: 900;
        text-align: left;
        cursor: pointer;
        transition: background .2s ease, border-color .2s ease, color .2s ease;
    }

    .food-status-option:hover {
        color: #ffffff;
        background: rgba(217, 154, 50, .12);
        border-color: rgba(217, 154, 50, .28);
    }

    .food-status-option + .food-status-option {
        margin-top: 4px;
    }

    .food-status-option.is-current {
        border-color: rgba(217, 154, 50, .38);
    }

    .food-status-pending {
        color: #f4c56a;
        background: rgba(217, 154, 50, .16);
        border-color: rgba(217, 154, 50, .38);
    }

    .food-status-paid {
        color: #86efac;
        background: rgba(34, 197, 94, .15);
        border-color: rgba(34, 197, 94, .32);
    }

    .food-status-cancelled {
        color: #fca5a5;
        background: rgba(239, 68, 68, .14);
        border-color: rgba(239, 68, 68, .32);
    }
</style>
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

    function addRow(food = {}) {
        const fragment = template.content.cloneNode(true);
        const row = fragment.querySelector('.food-item-row');

        row.querySelector('.food-id').value = food.id || '';
        row.querySelector('.food-name').value = food.name || '';
        row.querySelector('.food-quantity').value = food.quantity || 1;
        row.querySelector('.food-price').value = food.price || '';

        itemsBox.appendChild(fragment);
        renumberRows();
        calculate();

        if (!food.name) {
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

    addRow();
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\WD-11-Cinehome-cinema\resources\views/admin/food-invoices/index.blade.php ENDPATH**/ ?>