<?php $__env->startSection('title', 'Vé của tôi'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $statusMeta = [
        'cho_thanh_toan' => [
            'label' => 'Chờ thanh toán',
            'icon' => 'fa-solid fa-clock',
            'class' => 'is-pending',
            'description' => 'Vé đã chọn đang chờ thanh toán',
        ],
        'da_thanh_toan' => [
            'label' => 'Đã thanh toán',
            'icon' => 'fa-solid fa-circle-check',
            'class' => 'is-paid',
            'description' => 'Sẵn sàng vào rạp',
        ],
        'da_su_dung' => [
            'label' => 'Đã sử dụng',
            'icon' => 'fa-solid fa-check-double',
            'class' => 'is-used',
            'description' => 'Vé đã được soát',
        ],
        'da_huy' => [
            'label' => 'Đã hủy',
            'icon' => 'fa-solid fa-circle-xmark',
            'class' => 'is-cancelled',
            'description' => 'Vé không còn hiệu lực',
        ],
        'het_han' => [
            'label' => 'Hết hạn',
            'icon' => 'fa-solid fa-clock-rotate-left',
            'class' => 'is-cancelled',
            'description' => 'Vé đã quá giờ chiếu',
        ],
    ];

    $filterItems = [
        ['status' => null, 'label' => 'Tất cả', 'count' => $ticketStats['total'] ?? $veXemPhims->total()],
        ['status' => 'cho_thanh_toan', 'label' => 'Chờ thanh toán', 'count' => $ticketStats['pending'] ?? 0],
        ['status' => 'da_thanh_toan', 'label' => 'Đã thanh toán', 'count' => $ticketStats['paid'] ?? 0],
        ['status' => 'da_su_dung', 'label' => 'Đã sử dụng', 'count' => $ticketStats['used'] ?? 0],
        ['status' => 'het_han', 'label' => 'Hết hạn', 'count' => $ticketStats['expired'] ?? 0],
        ['status' => 'da_huy', 'label' => 'Đã hủy', 'count' => $ticketStats['cancelled'] ?? 0],
    ];
?>

<section class="mytickets-page">
    <div class="mytickets-shell">
        <div class="mytickets-hero">
            <div class="mytickets-hero-copy">
                <span class="mytickets-eyebrow">
                    <i class="fa-solid fa-ticket"></i>
                    CineHome e-ticket
                </span>
                <h1>Vé của tôi</h1>
                <p>Quản lý vé đã đặt, kiểm tra QR soát vé và theo dõi lịch chiếu phim dễ dàng.</p>

                <div class="mytickets-hero-actions">
                    <a href="<?php echo e(route('dat_ve.chon_phim')); ?>" class="mytickets-primary-link">
                        <i class="fa-solid fa-plus"></i>
                        Đặt vé mới
                    </a>
                    <a href="<?php echo e(route('user.thanh-vien.index')); ?>" class="mytickets-secondary-link">
                        <i class="fa-solid fa-crown"></i>
                        Thẻ thành viên & điểm
                    </a>
                </div>
            </div>

            <aside class="mytickets-next-card">
                <span>Suất gần nhất</span>
                <?php if($nextTicket): ?>
                    <strong><?php echo e($nextTicket->ten_phim); ?></strong>
                    <p><?php echo e($nextTicket->thoi_gian_chieu?->format('H:i - d/m/Y')); ?></p>
                    <small>
                        <i class="fa-solid fa-location-dot"></i>
                        <?php echo e($nextTicket->ten_rap ?? 'CineHome Cinema'); ?>

                    </small>
                    <a href="<?php echo e(route('user.ve_xem_phim.show', $nextTicket)); ?>">
                        Mở vé điện tử
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                <?php else: ?>
                    <strong>Chưa có suất sắp tới</strong>
                    <p>Đặt vé mới để lịch xem phim của bạn xuất hiện tại đây.</p>
                    <small>
                        <i class="fa-solid fa-clock"></i>
                        Vui lòng đến rạp trước suất chiếu 15 phút.
                    </small>
                <?php endif; ?>
            </aside>
        </div>

        <?php if(session('success')): ?>
            <div class="mytickets-alert is-success">
                <i class="fa-solid fa-circle-check"></i>
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="mytickets-alert is-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>

        <div class="mytickets-stats" style="grid-template-columns: repeat(5, 1fr) !important;">
            <article>
                <span>Tổng vé</span>
                <strong><?php echo e(number_format($ticketStats['total'] ?? $veXemPhims->total())); ?></strong>
                <small>Tất cả giao dịch</small>
            </article>
            <article>
                <span>Đang hiệu lực</span>
                <strong><?php echo e(number_format($ticketStats['paid'] ?? 0)); ?></strong>
                <small>Có thể mở QR</small>
            </article>
            <article>
                <span>Đã dùng</span>
                <strong><?php echo e(number_format($ticketStats['used'] ?? 0)); ?></strong>
                <small>Lịch sử xem phim</small>
            </article>
            <article>
                <span>Hết hạn</span>
                <strong style="color: #9ca3af;"><?php echo e(number_format($ticketStats['expired'] ?? 0)); ?></strong>
                <small>Vé quá giờ chiếu</small>
            </article>
            <article>
                <span>Đã hủy</span>
                <strong><?php echo e(number_format($ticketStats['cancelled'] ?? 0)); ?></strong>
                <small>Lịch sử vé đã hủy</small>
            </article>
        </div>

        <section class="mytickets-board">
            <div class="mytickets-board-head">
                <div>
                    <span>Danh sách vé</span>
                    <h2><?php echo e($activeStatus ? ($statusMeta[$activeStatus]['label'] ?? 'Vé đã lọc') : 'Tất cả vé đã đặt'); ?></h2>
                </div>

                <nav class="mytickets-filter" aria-label="Lọc vé theo trạng thái">
                    <?php $__currentLoopData = $filterItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a
                            href="<?php echo e($item['status'] ? route('user.ve_xem_phim.index', ['trang_thai' => $item['status']]) : route('user.ve_xem_phim.index')); ?>"
                            class="<?php echo e($activeStatus === $item['status'] ? 'is-active' : ''); ?>"
                        >
                            <?php echo e($item['label']); ?>

                            <b><?php echo e(number_format($item['count'])); ?></b>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </nav>
            </div>

            <div class="mytickets-list">
                <?php $__empty_1 = true; $__currentLoopData = $veXemPhims; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $veXemPhim): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $meta = $statusMeta[$veXemPhim->trang_thai] ?? [
                            'label' => $veXemPhim->trang_thai,
                            'icon' => 'fa-solid fa-circle-info',
                            'class' => 'is-neutral',
                            'description' => 'Đang cập nhật',
                        ];
                        $seats = collect(explode(',', (string) $veXemPhim->ma_ghe))
                            ->map(fn ($seat) => trim($seat))
                            ->filter()
                            ->values();
                    ?>

                    <article class="myticket-card <?php echo e($meta['class']); ?>">
                        <div class="myticket-code">
                            <span>Mã vé</span>
                            <?php if($veXemPhim->trang_thai === 'cho_thanh_toan'): ?>
                                <strong>Đang chờ thanh toán</strong>
                                <small>Vui lòng hoàn tất thanh toán để nhận mã vé.</small>
                            <?php else: ?>
                                <strong><?php echo e($veXemPhim->ma_ve); ?></strong>
                                <small><?php echo e($veXemPhim->created_at?->format('d/m/Y H:i')); ?></small>
                            <?php endif; ?>
                        </div>

                        <div class="myticket-movie">
                            <h3><?php echo e($veXemPhim->ten_phim); ?></h3>
                            <p>
                                <i class="fa-solid fa-location-dot"></i>
                                <?php echo e($veXemPhim->ten_rap ?? 'CineHome Cinema'); ?>

                            </p>
                            <p>
                                <i class="fa-solid fa-door-open"></i>
                                <?php echo e($veXemPhim->ten_phong ?? 'Phòng chiếu'); ?>

                            </p>
                            
                            <?php if(!empty($veXemPhim->food_items)): ?>
                                <p style="margin-top: 8px; font-size: 13px; color: #facc15; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                    <i class="fa-solid fa-cookie-bite" style="font-size: 14px;"></i>
                                    <span style="color: #9ca3af; font-weight: 600;">Đồ ăn kèm:</span>
                                    <?php $__currentLoopData = $veXemPhim->food_items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span style="background: rgba(250, 204, 21, 0.1); border: 1px solid rgba(250, 204, 21, 0.2); padding: 2px 8px; border-radius: 6px; font-weight: bold;">
                                            <?php echo e($fItem['name'] ?? 'Đồ ăn'); ?> (x<?php echo e($fItem['qty'] ?? $fItem['quantity'] ?? 1); ?>)
                                        </span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="myticket-info-grid">
                            <div>
                                <span>Ghế</span>
                                <strong>
                                    <?php $__empty_2 = true; $__currentLoopData = $seats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $seat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                        <em><?php echo e($seat); ?></em>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                        ---
                                    <?php endif; ?>
                                </strong>
                            </div>
                            <div>
                                <span>Suất chiếu</span>
                                <strong><?php echo e($veXemPhim->thoi_gian_chieu?->format('H:i') ?? '--:--'); ?></strong>
                                <small><?php echo e($veXemPhim->thoi_gian_chieu?->format('d/m/Y') ?? 'Đang cập nhật'); ?></small>
                            </div>
                            <div>
                                <span>Tổng tiền</span>
                                <strong><?php echo e(number_format($veXemPhim->tong_tien, 0, ',', '.')); ?>đ</strong>
                                <?php if($veXemPhim->tien_hoan > 0): ?>
                                    <small class="is-refund">Hoàn <?php echo e(number_format($veXemPhim->tien_hoan, 0, ',', '.')); ?>đ</small>
                                <?php endif; ?>
                            </div>
                            <div>
                                <span>Trạng thái</span>
                                <strong class="myticket-status">
                                    <i class="<?php echo e($meta['icon']); ?>"></i>
                                    <?php echo e($meta['label']); ?>

                                </strong>
                                <small><?php echo e($meta['description']); ?></small>
                            </div>
                        </div>

                        <div class="myticket-actions">
                            <?php if($veXemPhim->trang_thai === 'cho_thanh_toan'): ?>
                                <a href="<?php echo e(route('dat_ve.checkout', ['suat_chieu_id' => $veXemPhim->suat_chieu_id, 'pending_ticket_id' => $veXemPhim->id])); ?>" class="myticket-detail-btn">
                                    <i class="fa-solid fa-credit-card"></i>
                                    Thanh toán
                                </a>
                            <?php else: ?>
                                <a href="<?php echo e(route('user.ve_xem_phim.show', $veXemPhim)); ?>" class="myticket-detail-btn">
                                    <i class="fa-solid fa-qrcode"></i>
                                    Chi tiết
                                </a>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="mytickets-empty">
                        <span>
                            <i class="fa-solid fa-ticket"></i>
                        </span>
                        <h3>Chưa có vé nào</h3>
                        <p>Chọn một bộ phim yêu thích, đặt suất phù hợp và vé điện tử sẽ xuất hiện tại đây.</p>
                        <a href="<?php echo e(route('dat_ve.chon_phim')); ?>" class="mytickets-primary-link">
                            <i class="fa-solid fa-plus"></i>
                            Đặt vé ngay
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <?php if($veXemPhims->hasPages()): ?>
                <div class="mytickets-pagination">
                    <div class="mytickets-page-summary">
                        Hiển thị
                        <strong><?php echo e($veXemPhims->firstItem()); ?></strong>
                        -
                        <strong><?php echo e($veXemPhims->lastItem()); ?></strong>
                        trong
                        <strong><?php echo e($veXemPhims->total()); ?></strong>
                        vé
                    </div>

                    <nav class="mytickets-page-controls" aria-label="Phân trang vé">
                        <?php if($veXemPhims->onFirstPage()): ?>
                            <span class="mytickets-page-link is-disabled">
                                <i class="fa-solid fa-chevron-left"></i>
                                Trước
                            </span>
                        <?php else: ?>
                            <a href="<?php echo e($veXemPhims->previousPageUrl()); ?>" class="mytickets-page-link">
                                <i class="fa-solid fa-chevron-left"></i>
                                Trước
                            </a>
                        <?php endif; ?>

                        <?php $__currentLoopData = $veXemPhims->getUrlRange(max(1, $veXemPhims->currentPage() - 2), min($veXemPhims->lastPage(), $veXemPhims->currentPage() + 2)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($page === $veXemPhims->currentPage()): ?>
                                <span class="mytickets-page-link is-current"><?php echo e($page); ?></span>
                            <?php else: ?>
                                <a href="<?php echo e($url); ?>" class="mytickets-page-link"><?php echo e($page); ?></a>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <?php if($veXemPhims->hasMorePages()): ?>
                            <a href="<?php echo e($veXemPhims->nextPageUrl()); ?>" class="mytickets-page-link">
                                Sau
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        <?php else: ?>
                            <span class="mytickets-page-link is-disabled">
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
<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/user/ve_xem_phim/index.blade.php ENDPATH**/ ?>