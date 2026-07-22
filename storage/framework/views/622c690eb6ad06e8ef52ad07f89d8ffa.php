<?php $__env->startSection('page-title', 'Báo cáo doanh thu'); ?>
<?php $__env->startSection('page-subtitle', 'Tổng hợp doanh thu vé và đồ ăn theo khoảng ngày'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $ticketStatusLabels = [
        'da_thanh_toan' => 'Đã thanh toán',
        'da_su_dung' => 'Đã sử dụng',
        'da_huy' => 'Đã hủy',
    ];

    $foodStatusLabels = [
        'pending' => 'Chờ thanh toán',
        'paid' => 'Đã thanh toán',
        'cancelled' => 'Đã hủy',
    ];
?>

<form method="GET" class="admin-panel mb-6">
    <div class="panel-body grid gap-3 md:grid-cols-[1fr_1fr_auto]">
        <input type="date" name="from" value="<?php echo e($from->format('Y-m-d')); ?>" class="admin-input">
        <input type="date" name="to" value="<?php echo e($to->format('Y-m-d')); ?>" class="admin-input">
        <button class="btn-admin" type="submit">Lọc báo cáo</button>
    </div>
</form>

<div class="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
    <div class="stat-card">
        <div class="stat-label">Doanh thu vé</div>
        <div class="stat-value"><?php echo e(number_format((float) $summary['ticket_revenue'], 0, ',', '.')); ?>đ</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Doanh thu đồ ăn</div>
        <div class="stat-value"><?php echo e(number_format((float) $summary['food_revenue'], 0, ',', '.')); ?>đ</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Tổng doanh thu</div>
        <div class="stat-value"><?php echo e(number_format((float) $summary['total_revenue'], 0, ',', '.')); ?>đ</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Vé / hóa đơn</div>
        <div class="stat-value"><?php echo e($summary['tickets_sold']); ?> / <?php echo e($summary['food_invoices']); ?></div>
    </div>
</div>

<div class="grid gap-6 xl:grid-cols-2">
    <div class="admin-panel">
        <div class="panel-header">
            <h5>Doanh thu vé</h5>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Mã vé</th>
                        <th>Phim</th>
                        <th>Trạng thái</th>
                        <th>Tổng tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($ticket->ma_ve); ?></td>
                            <td><?php echo e($ticket->ten_phim ?? 'Không rõ phim'); ?></td>
                            <td><?php echo e($ticketStatusLabels[$ticket->trang_thai] ?? $ticket->trang_thai); ?></td>
                            <td><?php echo e(number_format((float) $ticket->tong_tien, 0, ',', '.')); ?>đ</td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4">Không có vé trong khoảng ngày này.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-panel">
        <div class="panel-header">
            <h5>Doanh thu đồ ăn</h5>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Mã hóa đơn</th>
                        <th>Khách hàng</th>
                        <th>Trạng thái</th>
                        <th>Tổng tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $foodInvoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($invoice->invoice_code); ?></td>
                            <td><?php echo e($invoice->customer_name ?? 'Khách lẻ'); ?></td>
                            <td><?php echo e($foodStatusLabels[$invoice->payment_status] ?? $invoice->payment_status); ?></td>
                            <td><?php echo e(number_format((float) $invoice->total, 0, ',', '.')); ?>đ</td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4">Không có hóa đơn đồ ăn trong khoảng ngày này.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/admin/revenue-reports/index.blade.php ENDPATH**/ ?>