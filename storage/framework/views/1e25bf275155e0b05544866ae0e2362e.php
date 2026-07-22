<?php $__env->startSection('title', 'Đổi điểm lấy voucher'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $availablePoints = $availablePoints ?? (int) ($thanhVien?->diem_hien_tai ?? 0);
    $totalVouchers = $vouchers->count();
?>

<section class="voucher-exchange-page">
    <div class="voucher-exchange-shell">
        <div class="voucher-exchange-hero">
            <div class="voucher-exchange-copy">
                <span class="voucher-exchange-eyebrow">
                    <i class="fa-solid fa-gift"></i>
                    CineHome rewards
                </span>
                <h1>Đổi điểm lấy voucher</h1>
                <p>Dùng điểm thành viên để đổi mã giảm giá cho lần đặt vé tiếp theo. Voucher sau khi đổi sẽ nằm trong kho voucher cá nhân của bạn.</p>

                <div class="voucher-exchange-actions">
                    <a href="<?php echo e(route('user.voucher.my')); ?>" class="voucher-exchange-primary">
                        <i class="fa-solid fa-ticket"></i>
                        Voucher của tôi
                    </a>
                    <a href="<?php echo e(route('user.thanh-vien.index')); ?>" class="voucher-exchange-secondary">
                        <i class="fa-solid fa-crown"></i>
                        Xem điểm thành viên
                    </a>
                </div>
            </div>

            <aside class="voucher-points-card">
                <span>Điểm hiện tại</span>
                <strong><?php echo e(number_format($availablePoints)); ?></strong>
                <p><?php echo e(number_format($affordableCount ?? 0)); ?> voucher có thể đổi ngay</p>
                <small>
                    <i class="fa-solid fa-bolt"></i>
                    <?php if($nextVoucher): ?>
                        Còn <?php echo e(number_format($pointsNeededForNext)); ?> điểm để đổi "<?php echo e($nextVoucher->ten_voucher); ?>"
                    <?php else: ?>
                        Bạn đã đủ điểm cho tất cả voucher đang mở
                    <?php endif; ?>
                </small>
            </aside>
        </div>

        <?php if(session('success')): ?>
            <div class="voucher-alert is-success">
                <i class="fa-solid fa-circle-check"></i>
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="voucher-alert is-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>

        <div class="voucher-overview-grid">
            <article>
                <span>Voucher đang mở</span>
                <strong><?php echo e(number_format($totalVouchers)); ?></strong>
                <small>Cập nhật theo hạn dùng</small>
            </article>
            <article>
                <span>Đổi được ngay</span>
                <strong><?php echo e(number_format($affordableCount ?? 0)); ?></strong>
                <small>Dựa trên điểm hiện tại</small>
            </article>
            <article>
                <span>Điểm khả dụng</span>
                <strong><?php echo e(number_format($availablePoints)); ?></strong>
                <small>Không làm tụt hạng khi đổi</small>
            </article>
        </div>

        <section class="voucher-market-board">
            <div class="voucher-market-head">
                <div>
                    <span>Kho ưu đãi</span>
                    <h2>Chọn voucher phù hợp</h2>
                </div>

                <div class="voucher-filter-tabs" data-voucher-filter-tabs>
                    <button type="button" class="is-active" data-voucher-filter="all">
                        Tất cả
                        <b><?php echo e(number_format($totalVouchers)); ?></b>
                    </button>
                    <button type="button" data-voucher-filter="affordable">
                        Đủ điểm
                        <b><?php echo e(number_format($affordableCount ?? 0)); ?></b>
                    </button>
                    <button type="button" data-voucher-filter="locked">
                        Cần thêm
                        <b><?php echo e(number_format(max(0, $totalVouchers - ($affordableCount ?? 0)))); ?></b>
                    </button>
                </div>
            </div>

            <div class="voucher-grid" data-voucher-grid>
                <?php $__empty_1 = true; $__currentLoopData = $vouchers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $voucher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $canExchange = $availablePoints >= $voucher->diem_can_doi;
                        $missingPoints = max(0, $voucher->diem_can_doi - $availablePoints);
                        $progress = $voucher->diem_can_doi > 0
                            ? min(100, ($availablePoints / max(1, $voucher->diem_can_doi)) * 100)
                            : 100;
                    ?>

                    <article class="voucher-reward-card <?php echo e($canExchange ? 'is-affordable' : 'is-locked'); ?>" data-voucher-state="<?php echo e($canExchange ? 'affordable' : 'locked'); ?>">
                        <div class="voucher-reward-top">
                            <span>
                                <i class="fa-solid fa-ticket"></i>
                            </span>
                            <em><?php echo e($voucher->ma_voucher); ?></em>
                        </div>

                        <h3><?php echo e($voucher->ten_voucher); ?></h3>

                        <div class="voucher-value-box">
                            <span>Giá trị giảm</span>
                            <strong><?php echo e(number_format($voucher->gia_tri_giam, 0, ',', '.')); ?>đ</strong>
                        </div>

                        <div class="voucher-meta-list">
                            <div>
                                <i class="fa-solid fa-star"></i>
                                <span>Điểm cần đổi</span>
                                <strong><?php echo e(number_format($voucher->diem_can_doi)); ?> điểm</strong>
                            </div>
                            <div>
                                <i class="fa-solid fa-calendar-day"></i>
                                <span>Hạn voucher mẫu</span>
                                <strong><?php echo e($voucher->ngay_het_han?->format('d/m/Y') ?? 'Không giới hạn'); ?></strong>
                            </div>
                        </div>

                        <div class="voucher-progress">
                            <div>
                                <span><?php echo e($canExchange ? 'Đủ điểm để đổi' : 'Tiến độ điểm'); ?></span>
                                <strong>
                                    <?php if($canExchange): ?>
                                        Sẵn sàng
                                    <?php else: ?>
                                        Cần thêm <?php echo e(number_format($missingPoints)); ?>

                                    <?php endif; ?>
                                </strong>
                            </div>
                            <div class="voucher-progress-track">
                                <span style="width: <?php echo e(number_format($progress, 2, '.', '')); ?>%"></span>
                            </div>
                        </div>

                        <form method="POST" action="<?php echo e(route('user.voucher.exchange', $voucher)); ?>" class="voucher-exchange-form">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="voucher-exchange-button <?php echo e($canExchange ? '' : 'is-disabled'); ?>" <?php echo e($canExchange ? '' : 'disabled'); ?>>
                                <?php if($canExchange): ?>
                                    <i class="fa-solid fa-gift"></i>
                                    Đổi voucher ngay
                                <?php else: ?>
                                    <i class="fa-solid fa-lock"></i>
                                    Chưa đủ điểm
                                <?php endif; ?>
                            </button>
                        </form>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="voucher-empty">
                        <span>
                            <i class="fa-solid fa-gift"></i>
                        </span>
                        <h3>Chưa có voucher đang mở</h3>
                        <p>Kho voucher sẽ được cập nhật khi rạp mở thêm chương trình ưu đãi mới.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="voucher-rule-panel">
            <div>
                <span>Quy định đổi điểm</span>
                <h2>Lưu ý trước khi đổi voucher</h2>
            </div>
            <ul>
                <li>
                    <i class="fa-solid fa-circle-check"></i>
                    Điểm đổi voucher chỉ trừ khỏi điểm hiện tại, không làm tụt hạng thành viên.
                </li>
                <li>
                    <i class="fa-solid fa-clock"></i>
                    Voucher cá nhân sau khi đổi có hạn sử dụng 30 ngày.
                </li>
                <li>
                    <i class="fa-solid fa-ban"></i>
                    Voucher đã đổi không thể hoàn lại điểm sau khi được sử dụng.
                </li>
            </ul>
        </section>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filterButtons = document.querySelectorAll('[data-voucher-filter]');
        const cards = document.querySelectorAll('[data-voucher-state]');

        filterButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const filter = button.getAttribute('data-voucher-filter');

                filterButtons.forEach(function (item) {
                    item.classList.toggle('is-active', item === button);
                });

                cards.forEach(function (card) {
                    const state = card.getAttribute('data-voucher-state');
                    const shouldShow = filter === 'all' || filter === state;
                    card.classList.toggle('is-hidden', !shouldShow);
                });
            });
        });
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/user/voucher/index.blade.php ENDPATH**/ ?>