<?php $__env->startSection('title', 'Thông báo của tôi'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $typeMeta = [
        'he_thong' => [
            'label' => 'Hệ thống',
            'icon' => 'fa-solid fa-circle-info',
            'class' => 'is-system',
        ],
        've' => [
            'label' => 'Vé xem phim',
            'icon' => 'fa-solid fa-ticket',
            'class' => 'is-ticket',
        ],
        'diem' => [
            'label' => 'Điểm thưởng',
            'icon' => 'fa-solid fa-star',
            'class' => 'is-point',
        ],
        'voucher' => [
            'label' => 'Voucher',
            'icon' => 'fa-solid fa-gift',
            'class' => 'is-voucher',
        ],
        'hang_thanh_vien' => [
            'label' => 'Hạng thành viên',
            'icon' => 'fa-solid fa-crown',
            'class' => 'is-rank',
        ],
    ];

    $filterItems = [
        ['type' => null, 'label' => 'Tất cả', 'count' => $notificationStats['total'] ?? $thongBaos->total()],
        ['type' => 've', 'label' => 'Vé', 'count' => $notificationStats['ve'] ?? 0],
        ['type' => 'diem', 'label' => 'Điểm', 'count' => $notificationStats['diem'] ?? 0],
        ['type' => 'voucher', 'label' => 'Voucher', 'count' => $notificationStats['voucher'] ?? 0],
        ['type' => 'hang_thanh_vien', 'label' => 'Hạng', 'count' => $notificationStats['hang_thanh_vien'] ?? 0],
        ['type' => 'he_thong', 'label' => 'Hệ thống', 'count' => $notificationStats['he_thong'] ?? 0],
    ];

    $currentFilter = collect($filterItems)->firstWhere('type', $activeType);
    $latestMeta = $latestUnread
        ? ($typeMeta[$latestUnread->loai_thong_bao] ?? $typeMeta['he_thong'])
        : $typeMeta['he_thong'];
?>

<section class="notification-page">
    <div class="notification-shell">
        <div class="notification-hero">
            <div class="notification-hero-copy">
                <span class="notification-eyebrow">
                    <i class="fa-solid fa-bell"></i>
                    Trung tâm thông báo
                </span>

                <h1>Thông báo của tôi</h1>
                <p>Theo dõi vé đã đặt, điểm thưởng, voucher và các cập nhật quan trọng từ CineHome trong một giao diện gọn, dễ quét và thao tác nhanh.</p>

                <div class="notification-hero-actions">
                    <a href="<?php echo e(route('user.ve_xem_phim.index')); ?>" class="notification-primary-link">
                        <i class="fa-solid fa-ticket"></i>
                        Xem vé của tôi
                    </a>
                    <a href="<?php echo e(route('user.voucher.my')); ?>" class="notification-secondary-link">
                        <i class="fa-solid fa-gift"></i>
                        Kho voucher
                    </a>
                </div>
            </div>

            <aside class="notification-highlight-card <?php echo e($latestMeta['class']); ?>">
                <span>
                    <i class="<?php echo e($latestMeta['icon']); ?>"></i>
                    <?php echo e(($notificationStats['unread'] ?? 0) > 0 ? 'Thông báo mới' : 'Hộp thư đã gọn'); ?>

                </span>

                <?php if($latestUnread): ?>
                    <strong><?php echo e($latestUnread->tieu_de); ?></strong>
                    <p><?php echo e($latestUnread->noi_dung); ?></p>
                    <small>
                        <i class="fa-solid fa-clock"></i>
                        <?php echo e($latestUnread->created_at?->diffForHumans()); ?>

                    </small>
                <?php else: ?>
                    <strong>Không có thông báo chưa đọc</strong>
                    <p>Khi có thay đổi về vé, điểm hoặc voucher, CineHome sẽ đưa thông tin nổi bật lên đây.</p>
                    <small>
                        <i class="fa-solid fa-circle-check"></i>
                        Tất cả đã được cập nhật
                    </small>
                <?php endif; ?>
            </aside>
        </div>

        <div class="notification-stats">
            <article>
                <span>Tất cả</span>
                <strong><?php echo e(number_format($notificationStats['total'] ?? $thongBaos->total())); ?></strong>
                <small>Tổng thông báo</small>
            </article>
            <article>
                <span>Chưa đọc</span>
                <strong><?php echo e(number_format($notificationStats['unread'] ?? 0)); ?></strong>
                <small>Tự đánh dấu sau khi mở trang</small>
            </article>
            <article>
                <span>Vé & voucher</span>
                <strong><?php echo e(number_format(($notificationStats['ve'] ?? 0) + ($notificationStats['voucher'] ?? 0))); ?></strong>
                <small>Ưu tiên kiểm tra trước</small>
            </article>
            <article>
                <span>Điểm & hạng</span>
                <strong><?php echo e(number_format(($notificationStats['diem'] ?? 0) + ($notificationStats['hang_thanh_vien'] ?? 0))); ?></strong>
                <small>Lịch sử thành viên</small>
            </article>
        </div>

        <section class="notification-board">
            <div class="notification-board-head">
                <div>
                    <span>Danh sách thông báo</span>
                    <h2><?php echo e($currentFilter['label'] ?? 'Tất cả thông báo'); ?></h2>
                </div>

                <nav class="notification-filter" aria-label="Lọc thông báo theo loại">
                    <?php $__currentLoopData = $filterItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a
                            href="<?php echo e($item['type'] ? route('user.notifications.index', ['loai' => $item['type']]) : route('user.notifications.index')); ?>"
                            class="<?php echo e($activeType === $item['type'] ? 'is-active' : ''); ?>"
                        >
                            <?php echo e($item['label']); ?>

                            <b><?php echo e(number_format($item['count'])); ?></b>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </nav>
            </div>

            <div class="notification-list">
                <?php $__empty_1 = true; $__currentLoopData = $thongBaos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $thongBao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $meta = $typeMeta[$thongBao->loai_thong_bao] ?? $typeMeta['he_thong'];
                        $isUnread = ! $thongBao->da_doc;
                    ?>

                    <article class="notification-card <?php echo e($meta['class']); ?> <?php echo e($isUnread ? 'is-unread' : 'is-read'); ?>">
                        <div class="notification-card-icon">
                            <i class="<?php echo e($meta['icon']); ?>"></i>
                        </div>

                        <div class="notification-card-body">
                            <div class="notification-card-top">
                                <div>
                                    <span><?php echo e($meta['label']); ?></span>
                                    <h3><?php echo e($thongBao->tieu_de); ?></h3>
                                </div>

                                <div class="notification-time">
                                    <strong><?php echo e($isUnread ? 'Mới' : 'Đã đọc'); ?></strong>
                                    <small><?php echo e($thongBao->created_at?->format('d/m/Y H:i')); ?></small>
                                </div>
                            </div>

                            <p><?php echo e($thongBao->noi_dung); ?></p>

                            <div class="notification-card-actions">
                                <?php if($thongBao->duong_dan): ?>
                                    <a href="<?php echo e($thongBao->duong_dan); ?>" class="notification-detail-link">
                                        Xem chi tiết
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </a>
                                <?php else: ?>
                                    <span>
                                        <i class="fa-solid fa-check"></i>
                                        Đã lưu trong tài khoản
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="notification-empty">
                        <span>
                            <i class="fa-solid fa-bell-slash"></i>
                        </span>
                        <h3><?php echo e($activeType ? 'Chưa có thông báo phù hợp' : 'Bạn chưa có thông báo nào'); ?></h3>
                        <p><?php echo e($activeType ? 'Thử chọn bộ lọc khác hoặc quay lại tất cả thông báo để xem đầy đủ lịch sử.' : 'Khi có cập nhật về vé, điểm thưởng, voucher hoặc hạng thành viên, hệ thống sẽ hiển thị tại đây.'); ?></p>
                        <a href="<?php echo e(route('user.notifications.index')); ?>" class="notification-secondary-link">
                            <i class="fa-solid fa-layer-group"></i>
                            Xem tất cả
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <?php if($thongBaos->hasPages()): ?>
                <div class="notification-pagination">
                    <div class="notification-page-summary">
                        Hiển thị
                        <strong><?php echo e($thongBaos->firstItem()); ?></strong>
                        -
                        <strong><?php echo e($thongBaos->lastItem()); ?></strong>
                        trong
                        <strong><?php echo e($thongBaos->total()); ?></strong>
                        thông báo
                    </div>

                    <nav class="notification-page-controls" aria-label="Phân trang thông báo">
                        <?php if($thongBaos->onFirstPage()): ?>
                            <span class="notification-page-link is-disabled">
                                <i class="fa-solid fa-chevron-left"></i>
                                Trước
                            </span>
                        <?php else: ?>
                            <a href="<?php echo e($thongBaos->previousPageUrl()); ?>" class="notification-page-link">
                                <i class="fa-solid fa-chevron-left"></i>
                                Trước
                            </a>
                        <?php endif; ?>

                        <?php $__currentLoopData = $thongBaos->getUrlRange(max(1, $thongBaos->currentPage() - 2), min($thongBaos->lastPage(), $thongBaos->currentPage() + 2)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($page === $thongBaos->currentPage()): ?>
                                <span class="notification-page-link is-current"><?php echo e($page); ?></span>
                            <?php else: ?>
                                <a href="<?php echo e($url); ?>" class="notification-page-link"><?php echo e($page); ?></a>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <?php if($thongBaos->hasMorePages()): ?>
                            <a href="<?php echo e($thongBaos->nextPageUrl()); ?>" class="notification-page-link">
                                Sau
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        <?php else: ?>
                            <span class="notification-page-link is-disabled">
                                Sau
                                <i class="fa-solid fa-chevron-right"></i>
                            </span>
                        <?php endif; ?>
                    </nav>
                </div>
            <?php endif; ?>
        </section>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/user/thong_bao/index.blade.php ENDPATH**/ ?>