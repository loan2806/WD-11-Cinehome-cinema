<?php $__env->startSection('title', 'Voucher của tôi'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $filterItems = [
        ['status' => null, 'label' => 'Tất cả', 'count' => $voucherStats['total'] ?? $vouchers->total()],
        ['status' => 'kha_dung', 'label' => 'Khả dụng', 'count' => $voucherStats['available'] ?? 0],
        ['status' => 'da_su_dung', 'label' => 'Đã dùng', 'count' => $voucherStats['used'] ?? 0],
        ['status' => 'het_han', 'label' => 'Hết hạn', 'count' => $voucherStats['expired'] ?? 0],
    ];
?>

<section class="myvoucher-page">
    <div class="myvoucher-shell">
        <div class="myvoucher-hero">
            <div class="myvoucher-hero-copy">
                <span class="myvoucher-eyebrow">
                    <i class="fa-solid fa-ticket"></i>
                    My CineHome vouchers
                </span>
                <h1>Voucher của tôi</h1>
                <p>Quản lý các voucher đã đổi, copy mã nhanh và dùng khi đặt vé. Voucher khả dụng sẽ được ưu tiên hiển thị để bạn thao tác dễ hơn.</p>

                <div class="myvoucher-hero-actions">
                    <a href="<?php echo e(route('user.voucher.index')); ?>" class="myvoucher-primary-link">
                        <i class="fa-solid fa-gift"></i>
                        Đổi thêm voucher
                    </a>
                    <a href="<?php echo e(route('dat_ve.chon_phim')); ?>" class="myvoucher-secondary-link">
                        <i class="fa-solid fa-ticket"></i>
                        Đặt vé sử dụng
                    </a>
                </div>
            </div>

            <aside class="myvoucher-highlight-card">
                <span>Sắp hết hạn</span>
                <?php if($expiringVoucher): ?>
                    <strong><?php echo e($expiringVoucher->voucher?->ten_voucher ?? 'Voucher CineHome'); ?></strong>
                    <p><?php echo e($expiringVoucher->ma_voucher_ca_nhan); ?></p>
                    <small>
                        <i class="fa-solid fa-clock"></i>
                        Hết hạn <?php echo e($expiringVoucher->ngay_het_han?->format('d/m/Y')); ?>

                    </small>
                <?php else: ?>
                    <strong>Chưa có voucher cần dùng gấp</strong>
                    <p>Đổi thêm voucher để ưu đãi mới xuất hiện tại đây.</p>
                    <small>
                        <i class="fa-solid fa-circle-check"></i>
                        Kho voucher đang gọn gàng
                    </small>
                <?php endif; ?>
            </aside>
        </div>

        <?php if(session('success')): ?>
            <div class="myvoucher-alert is-success">
                <i class="fa-solid fa-circle-check"></i>
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="myvoucher-alert is-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>

        <div class="myvoucher-stats">
            <article>
                <span>Tổng voucher</span>
                <strong><?php echo e(number_format($voucherStats['total'] ?? $vouchers->total())); ?></strong>
                <small>Tất cả mã đã nhận</small>
            </article>
            <article>
                <span>Khả dụng</span>
                <strong><?php echo e(number_format($voucherStats['available'] ?? 0)); ?></strong>
                <small>Có thể dùng khi đặt vé</small>
            </article>
            <article>
                <span>Đã sử dụng</span>
                <strong><?php echo e(number_format($voucherStats['used'] ?? 0)); ?></strong>
                <small>Lịch sử ưu đãi</small>
            </article>
            <article>
                <span>Hết hạn</span>
                <strong><?php echo e(number_format($voucherStats['expired'] ?? 0)); ?></strong>
                <small>Cần đổi mã mới</small>
            </article>
        </div>

        <section class="myvoucher-board">
            <div class="myvoucher-board-head">
                <div>
                    <span>Kho voucher</span>
                    <h2><?php echo e($activeStatus ? collect($filterItems)->firstWhere('status', $activeStatus)['label'] ?? 'Voucher đã lọc' : 'Tất cả voucher'); ?></h2>
                </div>

                <nav class="myvoucher-filter" aria-label="Lọc voucher theo trạng thái">
                    <?php $__currentLoopData = $filterItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a
                            href="<?php echo e($item['status'] ? route('user.voucher.my', ['trang_thai' => $item['status']]) : route('user.voucher.my')); ?>"
                            class="<?php echo e($activeStatus === $item['status'] ? 'is-active' : ''); ?>"
                        >
                            <?php echo e($item['label']); ?>

                            <b><?php echo e(number_format($item['count'])); ?></b>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </nav>
            </div>

            <div class="myvoucher-grid">
                <?php $__empty_1 = true; $__currentLoopData = $vouchers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $isExpired = !$item->da_su_dung && $item->ngay_het_han && $item->ngay_het_han->lt(now());
                        $isAvailable = !$item->da_su_dung && !$isExpired;
                        $statusClass = $item->da_su_dung ? 'is-used' : ($isExpired ? 'is-expired' : 'is-available');
                        $statusLabel = $item->da_su_dung ? 'Đã sử dụng' : ($isExpired ? 'Hết hạn' : 'Khả dụng');
                        $statusIcon = $item->da_su_dung ? 'fa-solid fa-check-double' : ($isExpired ? 'fa-solid fa-clock-rotate-left' : 'fa-solid fa-circle-check');
                    ?>

                    <article class="myvoucher-card <?php echo e($statusClass); ?>">
                        <div class="myvoucher-card-top">
                            <span>
                                <i class="fa-solid fa-ticket"></i>
                            </span>
                            <em>
                                <i class="<?php echo e($statusIcon); ?>"></i>
                                <?php echo e($statusLabel); ?>

                            </em>
                        </div>

                        <div class="myvoucher-code-box">
                            <small>Mã voucher</small>
                            <strong><?php echo e($item->ma_voucher_ca_nhan); ?></strong>
                            <button type="button" data-copy-voucher="<?php echo e($item->ma_voucher_ca_nhan); ?>">
                                <i class="fa-solid fa-copy"></i>
                                Copy mã
                            </button>
                        </div>

                        <h3><?php echo e($item->voucher?->ten_voucher ?? 'Voucher CineHome'); ?></h3>

                        <div class="myvoucher-value">
                            <span>Giảm giá</span>
                            <strong><?php echo e(number_format($item->voucher?->gia_tri_giam ?? 0, 0, ',', '.')); ?>đ</strong>
                        </div>

                        <div class="myvoucher-meta">
                            <div>
                                <i class="fa-solid fa-calendar-plus"></i>
                                <span>Ngày nhận</span>
                                <strong><?php echo e($item->ngay_nhan?->format('d/m/Y') ?? '---'); ?></strong>
                            </div>
                            <div>
                                <i class="fa-solid fa-calendar-xmark"></i>
                                <span>Hạn dùng</span>
                                <strong><?php echo e($item->ngay_het_han?->format('d/m/Y') ?? 'Không giới hạn'); ?></strong>
                            </div>
                            <div>
                                <i class="fa-solid fa-receipt"></i>
                                <span>Ngày sử dụng</span>
                                <strong><?php echo e($item->ngay_su_dung?->format('d/m/Y') ?? 'Chưa dùng'); ?></strong>
                            </div>
                        </div>

                        <div class="myvoucher-actions">
                            <?php if($isAvailable): ?>
                                <a href="<?php echo e(route('dat_ve.chon_phim')); ?>" class="myvoucher-use-btn">
                                    <i class="fa-solid fa-ticket"></i>
                                    Dùng khi đặt vé
                                </a>
                            <?php else: ?>
                                <span class="myvoucher-disabled-btn">
                                    <i class="<?php echo e($statusIcon); ?>"></i>
                                    <?php echo e($statusLabel); ?>

                                </span>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="myvoucher-empty">
                        <span>
                            <i class="fa-solid fa-ticket"></i>
                        </span>
                        <h3>Bạn chưa có voucher nào</h3>
                        <p>Hãy dùng điểm thành viên để đổi voucher giảm giá cho những lần đặt vé tiếp theo.</p>
                        <a href="<?php echo e(route('user.voucher.index')); ?>" class="myvoucher-primary-link">
                            <i class="fa-solid fa-gift"></i>
                            Đổi điểm ngay
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <?php if($vouchers->hasPages()): ?>
                <div class="myvoucher-pagination">
                    <div class="myvoucher-page-summary">
                        Hiển thị
                        <strong><?php echo e($vouchers->firstItem()); ?></strong>
                        -
                        <strong><?php echo e($vouchers->lastItem()); ?></strong>
                        trong
                        <strong><?php echo e($vouchers->total()); ?></strong>
                        voucher
                    </div>

                    <nav class="myvoucher-page-controls" aria-label="Phân trang voucher">
                        <?php if($vouchers->onFirstPage()): ?>
                            <span class="myvoucher-page-link is-disabled">
                                <i class="fa-solid fa-chevron-left"></i>
                                Trước
                            </span>
                        <?php else: ?>
                            <a href="<?php echo e($vouchers->previousPageUrl()); ?>" class="myvoucher-page-link">
                                <i class="fa-solid fa-chevron-left"></i>
                                Trước
                            </a>
                        <?php endif; ?>

                        <?php $__currentLoopData = $vouchers->getUrlRange(max(1, $vouchers->currentPage() - 2), min($vouchers->lastPage(), $vouchers->currentPage() + 2)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($page === $vouchers->currentPage()): ?>
                                <span class="myvoucher-page-link is-current"><?php echo e($page); ?></span>
                            <?php else: ?>
                                <a href="<?php echo e($url); ?>" class="myvoucher-page-link"><?php echo e($page); ?></a>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <?php if($vouchers->hasMorePages()): ?>
                            <a href="<?php echo e($vouchers->nextPageUrl()); ?>" class="myvoucher-page-link">
                                Sau
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        <?php else: ?>
                            <span class="myvoucher-page-link is-disabled">
                                Sau
                                <i class="fa-solid fa-chevron-right"></i>
                            </span>
                        <?php endif; ?>
                    </nav>
                </div>
            <?php endif; ?>
        </section>

        <section class="myvoucher-rule-panel">
            <div>
                <span>Mẹo sử dụng</span>
                <h2>Dùng voucher nhanh hơn</h2>
            </div>
            <ul>
                <li>
                    <i class="fa-solid fa-copy"></i>
                    Copy mã voucher trước khi vào bước thanh toán để thao tác nhanh.
                </li>
                <li>
                    <i class="fa-solid fa-clock"></i>
                    Ưu tiên dùng voucher sắp hết hạn để không bỏ lỡ ưu đãi.
                </li>
                <li>
                    <i class="fa-solid fa-gift"></i>
                    Hết voucher thì quay lại kho đổi điểm để nhận mã mới.
                </li>
            </ul>
        </section>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-copy-voucher]').forEach(function (button) {
            button.addEventListener('click', function () {
                const code = button.getAttribute('data-copy-voucher');

                if (!navigator.clipboard) return;

                navigator.clipboard.writeText(code).then(function () {
                    const originalText = button.innerHTML;
                    button.innerHTML = '<i class="fa-solid fa-check"></i> Đã copy';

                    setTimeout(function () {
                        button.innerHTML = originalText;
                    }, 1400);
                });
            });
        });
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\laragon\www\Cinema\WD-11-Cinehome-cinema\resources\views/user/voucher/my-voucher.blade.php ENDPATH**/ ?>