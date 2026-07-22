<?php $__env->startSection('title', 'Thẻ thành viên & Điểm'); ?>

<?php $__env->startSection('content'); ?>
<?php
$user = Auth::user();
$rankLabel = $currentRank['label'] ?? $thanhVien->ten_hang;
$nextRankLabel = match ($currentRankKey) {
'member' => 'Silver',
'silver' => 'Gold',
'gold' => 'Platinum',
default => null,
};
?>

<section class="member-page">
    <div class="member-shell">
        <div class="member-hero">
            <div class="member-hero-copy">
                <span class="member-eyebrow">
                    <i class="fa-solid fa-crown"></i>
                    CineHome loyalty
                </span>
                <h1>Thẻ thành viên & điểm thưởng</h1>
                <p>Theo dõi hạng thành viên, điểm khả dụng, mã giới thiệu và lịch sử cộng/trừ điểm sau mỗi lần đặt vé.</p>

                <div class="member-hero-actions">
                    <a href="<?php echo e(route('user.voucher.index')); ?>" class="member-primary-link">
                        <i class="fa-solid fa-gift"></i>
                        Đổi điểm lấy voucher
                    </a>
                    <a href="<?php echo e(route('user.ve_xem_phim.index')); ?>" class="member-secondary-link">
                        <i class="fa-solid fa-ticket"></i>
                        Vé của tôi
                    </a>
                </div>
            </div>

            <aside class="member-mini-summary">
                <span>Điểm khả dụng</span>
                <strong><?php echo e(number_format($thanhVien->diem_hien_tai)); ?></strong>
                <p>Hạng hiện tại: <?php echo e($rankLabel); ?></p>
                <small>
                    <i class="fa-solid fa-chart-line"></i>
                    Hệ số tích điểm x<?php echo e(number_format($thanhVien->heSoTichDiem(), 2)); ?>

                </small>
            </aside>
        </div>

        <div class="member-top-grid">
            <article class="member-card-visual is-<?php echo e($currentRankKey); ?>">
                <div class="member-card-top">
                    <div>
                        <span>CineHome member</span>
                        <strong><?php echo e($rankLabel); ?></strong>
                    </div>
                    <i class="<?php echo e($currentRank['icon'] ?? 'fa-solid fa-crown'); ?>"></i>
                </div>

                <div class="member-card-center">
                    <small>Mã thành viên</small>
                    <h2><?php echo e($thanhVien->ma_thanh_vien); ?></h2>
                </div>

                <div class="member-card-bottom">
                    <div>
                        <span>Chủ thẻ</span>
                        <strong><?php echo e($user->ho_ten ?? 'Thành viên CineHome'); ?></strong>
                    </div>
                    <div>
                        <span>Ngày tham gia</span>
                        <strong><?php echo e($thanhVien->ngay_tham_gia?->format('d/m/Y') ?? 'Đang cập nhật'); ?></strong>
                    </div>
                </div>
            </article>

            <section class="member-points-panel">
                <div class="member-panel-head">
                    <span>Tổng quan điểm</span>
                    <h2><?php echo e(number_format($thanhVien->diem_hien_tai)); ?> điểm</h2>
                </div>

                <div class="member-rank-progress">
                    <div class="member-rank-row">
                        <span><?php echo e($rankLabel); ?></span>
                        <strong>
                            <?php if($nextRankLabel): ?>
                            Còn <?php echo e(number_format($pointsToNextRank)); ?> điểm lên <?php echo e($nextRankLabel); ?>

                            <?php else: ?>
                            Bạn đang ở hạng cao nhất
                            <?php endif; ?>
                        </strong>
                    </div>
                    <div class="member-progress-track" aria-label="Tiến độ hạng thành viên">
                        <span style="width: <?php echo e(number_format($rankProgress, 2, '.', '')); ?>%"></span>
                    </div>
                    <small><?php echo e(number_format($thanhVien->tong_diem_tich_luy)); ?> điểm tích lũy trọn đời</small>
                </div>

                <div class="member-stat-grid">
                    <article>
                        <i class="fa-solid fa-star"></i>
                        <span>Điểm hiện tại</span>
                        <strong><?php echo e(number_format($thanhVien->diem_hien_tai)); ?></strong>
                    </article>
                    <article>
                        <i class="fa-solid fa-arrow-trend-up"></i>
                        <span>Tổng tích lũy</span>
                        <strong><?php echo e(number_format($thanhVien->tong_diem_tich_luy)); ?></strong>
                    </article>
                    <article>
                        <i class="fa-solid fa-plus"></i>
                        <span>Đã cộng</span>
                        <strong><?php echo e(number_format($pointSummary['earned'] ?? 0)); ?></strong>
                    </article>
                    <article>
                        <i class="fa-solid fa-minus"></i>
                        <span>Đã dùng/trừ</span>
                        <strong><?php echo e(number_format($pointSummary['spent'] ?? 0)); ?></strong>
                    </article>
                </div>
            </section>
        </div>

        <section class="member-rank-section">
            <div class="member-section-head">
                <span>Quyền lợi theo hạng</span>
                <h2>Lộ trình thành viên</h2>
            </div>

            <div class="member-rank-grid">
                <?php $__currentLoopData = $rankConfig; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rankKey => $rank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <article class="member-rank-card <?php echo e($rankKey === $currentRankKey ? 'is-active' : ''); ?>">
                    <span>
                        <i class="<?php echo e($rank['icon']); ?>"></i>
                    </span>
                    <h3><?php echo e($rank['label']); ?></h3>
                    <strong><?php echo e($rank['range']); ?></strong>
                    <p><?php echo e($rank['benefit']); ?></p>
                </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>

        <div class="member-lower-grid">
            <section class="member-referral-card">
                <div class="member-section-head">
                    <span>Giới thiệu bạn bè</span>
                    <h2>Mã giới thiệu của bạn</h2>
                </div>

                <div class="member-referral-code">
                    <strong><?php echo e($thanhVien->ma_gioi_thieu); ?></strong>
                    <button type="button" onclick="navigator.clipboard.writeText('<?php echo e($thanhVien->ma_gioi_thieu); ?>')">
                        <i class="fa-solid fa-copy"></i>
                        Copy
                    </button>
                </div>

                <p>Chia sẻ mã này cho bạn bè khi đăng ký tài khoản để nhận thêm điểm thưởng từ CineHome.</p>

                <div class="member-referral-stats">
                    <article>
                        <span>Đã giới thiệu</span>
                        <strong><?php echo e(number_format($nguoiDaGioiThieu->count())); ?></strong>
                    </article>

                    <article>
                        <span>Điểm thưởng</span>
                        <strong>+<?php echo e(number_format($pointSummary['referral_points'] ?? 0)); ?></strong>
                    </article>
                </div>

                <hr style="margin:20px 0;border:none;border-top:1px solid #ececec;">

                <div class="member-section-head" style="margin-bottom:12px">
                    <span>Thông tin liên kết</span>
                    <h2>Người đã giới thiệu bạn</h2>
                </div>

                <?php if($thanhVien->nguoiGioiThieu): ?>

                <div class="member-referral-info">

                    <div style="font-size:18px;font-weight:600">
                        <?php echo e($thanhVien->nguoiGioiThieu->nguoiDung->ho_ten ?? 'Không xác định'); ?>

                    </div>

                    <div style="margin-top:8px;color:#666">
                        Mã giới thiệu:
                        <strong>
                            <?php echo e($thanhVien->nguoiGioiThieu->ma_gioi_thieu); ?>

                        </strong>
                    </div>

                    <div style="margin-top:10px;color:#16a34a;font-weight:600">
                        <i class="fa-solid fa-circle-check"></i>
                        Đã liên kết
                    </div>

                </div>

                <?php else: ?>

                <div class="member-referral-info">

                    <div style="color:#888">
                        <i class="fa-solid fa-user-slash"></i>
                        Bạn chưa liên kết mã giới thiệu.
                    </div>

                </div>

                <?php endif; ?>
            </section>

            <section class="member-tips-card">
                <div class="member-section-head">
                    <span>Mẹo tích điểm</span>
                    <h2>Dùng điểm thông minh hơn</h2>
                </div>

                <ul>
                    <li>
                        <i class="fa-solid fa-ticket"></i>
                        Đặt vé trực tuyến để điểm tự cộng vào thẻ sau thanh toán.
                    </li>
                    <li>
                        <i class="fa-solid fa-gift"></i>
                        Đổi điểm lấy voucher trước khi checkout để tiết kiệm hơn.
                    </li>
                    <li>
                        <i class="fa-solid fa-user-group"></i>
                        Chia sẻ mã giới thiệu để nhận thêm điểm thưởng.
                    </li>
                </ul>
            </section>
        </div>

        <section class="member-history-board">
            <div class="member-board-head">
                <div>
                    <span>Lịch sử điểm</span>
                    <h2><?php echo e(number_format($pointSummary['transactions'] ?? $lichSuDiem->total())); ?> giao dịch</h2>
                </div>
                <a href="<?php echo e(route('dat_ve.chon_phim')); ?>" class="member-secondary-link">
                    <i class="fa-solid fa-plus"></i>
                    Đặt vé tích điểm
                </a>
            </div>

            <div class="member-history-list">
                <?php $__empty_1 = true; $__currentLoopData = $lichSuDiem; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                $isEarn = $item->loai_giao_dich === 'cong_diem';
                ?>

                <article class="member-history-item <?php echo e($isEarn ? 'is-earned' : 'is-spent'); ?>">
                    <div class="member-history-icon">
                        <i class="fa-solid <?php echo e($isEarn ? 'fa-plus' : 'fa-minus'); ?>"></i>
                    </div>

                    <div class="member-history-copy">
                        <span><?php echo e($item->created_at?->format('H:i - d/m/Y')); ?></span>
                        <h3><?php echo e($isEarn ? 'Cộng điểm' : 'Trừ điểm'); ?></h3>
                        <p><?php echo e($item->noi_dung); ?></p>
                    </div>

                    <strong>
                        <?php echo e($isEarn ? '+' : '-'); ?><?php echo e(number_format($item->so_diem)); ?>

                    </strong>
                </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="member-empty-history">
                    <span>
                        <i class="fa-solid fa-receipt"></i>
                    </span>
                    <h3>Chưa có lịch sử điểm</h3>
                    <p>Đặt vé hoặc đổi voucher để các giao dịch điểm xuất hiện tại đây.</p>
                    <a href="<?php echo e(route('dat_ve.chon_phim')); ?>" class="member-primary-link">
                        <i class="fa-solid fa-ticket"></i>
                        Đặt vé ngay
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <?php if($lichSuDiem->hasPages()): ?>
            <div class="member-pagination">
                <div class="member-page-summary">
                    Hiển thị
                    <strong><?php echo e($lichSuDiem->firstItem()); ?></strong>
                    -
                    <strong><?php echo e($lichSuDiem->lastItem()); ?></strong>
                    trong
                    <strong><?php echo e($lichSuDiem->total()); ?></strong>
                    giao dịch
                </div>

                <nav class="member-page-controls" aria-label="Phân trang lịch sử điểm">
                    <?php if($lichSuDiem->onFirstPage()): ?>
                    <span class="member-page-link is-disabled">
                        <i class="fa-solid fa-chevron-left"></i>
                        Trước
                    </span>
                    <?php else: ?>
                    <a href="<?php echo e($lichSuDiem->previousPageUrl()); ?>" class="member-page-link">
                        <i class="fa-solid fa-chevron-left"></i>
                        Trước
                    </a>
                    <?php endif; ?>

                    <?php $__currentLoopData = $lichSuDiem->getUrlRange(max(1, $lichSuDiem->currentPage() - 2), min($lichSuDiem->lastPage(), $lichSuDiem->currentPage() + 2)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($page === $lichSuDiem->currentPage()): ?>
                    <span class="member-page-link is-current"><?php echo e($page); ?></span>
                    <?php else: ?>
                    <a href="<?php echo e($url); ?>" class="member-page-link"><?php echo e($page); ?></a>
                    <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <?php if($lichSuDiem->hasMorePages()): ?>
                    <a href="<?php echo e($lichSuDiem->nextPageUrl()); ?>" class="member-page-link">
                        Sau
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                    <?php else: ?>
                    <span class="member-page-link is-disabled">
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

<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/user/thanh_vien/index.blade.php ENDPATH**/ ?>