<?php $__env->startSection('title', 'Tài khoản khách hàng'); ?>
<?php $__env->startSection('page-title', 'Tài khoản khách hàng'); ?>
<?php $__env->startSection('page-subtitle', 'Quản lý hồ sơ, trạng thái và hoạt động tài khoản khách hàng'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $summary = [
        'total' => $tongKhachHang ?? $khachHangs->total(),
        'active' => $tongDangHoatDong ?? 0,
        'locked' => $tongBiKhoa ?? 0,
        'new_this_month' => $tongMoiTrongThang ?? 0,
        'members' => $tongCoTheThanhVien ?? 0,
    ];

    $activeFilters = collect([
        request('tim_kiem'),
        request('trang_thai'),
    ])->filter(fn ($value) => filled($value))->count();
?>

<div class="customer-account-page">
    <?php echo $__env->make('admin.partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <section class="customer-account-hero">
        <div>
            <span class="customer-account-kicker">
                <i class="fa-solid fa-users"></i>
                Hồ sơ khách hàng
            </span>
            <h2>Quản lý tài khoản khách hàng</h2>
            <p>Tìm nhanh khách theo tên, email hoặc số điện thoại; kiểm tra hạng thành viên, số vé đã mua và trạng thái hoạt động của tài khoản.</p>
            <div class="customer-account-hero-meta">
                <span><i class="fa-solid fa-user-check"></i> <?php echo e(number_format($summary['active'])); ?> đang hoạt động</span>
                <span><i class="fa-solid fa-crown"></i> <?php echo e(number_format($summary['members'])); ?> có thẻ thành viên</span>
                <span><i class="fa-solid fa-calendar-plus"></i> <?php echo e(number_format($summary['new_this_month'])); ?> mới trong tháng</span>
            </div>
        </div>

        <a href="<?php echo e(route('admin.khach-hang.create')); ?>" class="customer-account-primary-btn">
            <i class="fa-solid fa-user-plus"></i>
            Thêm khách hàng
        </a>
    </section>

    <section class="customer-account-stats">
        <article class="customer-account-stat">
            <span class="is-total"><i class="fa-solid fa-users"></i></span>
            <div>
                <small>Tổng khách hàng</small>
                <strong><?php echo e(number_format($summary['total'])); ?></strong>
            </div>
        </article>
        <article class="customer-account-stat">
            <span class="is-active"><i class="fa-solid fa-user-check"></i></span>
            <div>
                <small>Đang hoạt động</small>
                <strong><?php echo e(number_format($summary['active'])); ?></strong>
            </div>
        </article>
        <article class="customer-account-stat">
            <span class="is-locked"><i class="fa-solid fa-user-lock"></i></span>
            <div>
                <small>Bị khóa</small>
                <strong><?php echo e(number_format($summary['locked'])); ?></strong>
            </div>
        </article>
        <article class="customer-account-stat">
            <span class="is-member"><i class="fa-solid fa-crown"></i></span>
            <div>
                <small>Có thẻ thành viên</small>
                <strong><?php echo e(number_format($summary['members'])); ?></strong>
            </div>
        </article>
    </section>

    <section class="customer-account-panel">
        <div class="customer-account-panel-head">
            <div>
                <span class="customer-account-kicker">Danh sách</span>
                <h3>Tài khoản khách hàng</h3>
                <p>Đang hiển thị <?php echo e($khachHangs->count()); ?> / <?php echo e($khachHangs->total()); ?> khách hàng theo bộ lọc hiện tại.</p>
            </div>
        </div>

        <form method="GET" action="<?php echo e(route('admin.khach-hang.index')); ?>" class="customer-account-filter">
            <label class="customer-account-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input
                    type="text"
                    name="tim_kiem"
                    value="<?php echo e(request('tim_kiem')); ?>"
                    placeholder="Tìm tên, email hoặc số điện thoại..."
                >
            </label>

            <select name="trang_thai" class="admin-input">
                <option value="">Tất cả trạng thái</option>
                <option value="1" <?php if(request('trang_thai') === '1'): echo 'selected'; endif; ?>>Đang hoạt động</option>
                <option value="0" <?php if(request('trang_thai') === '0'): echo 'selected'; endif; ?>>Bị khóa</option>
            </select>

            <button type="submit" class="customer-account-filter-btn">
                <i class="fa-solid fa-filter"></i>
                Lọc
                <?php if($activeFilters): ?>
                    <span><?php echo e($activeFilters); ?></span>
                <?php endif; ?>
            </button>

            <?php if($activeFilters): ?>
                <a href="<?php echo e(route('admin.khach-hang.index')); ?>" class="customer-account-reset-btn" title="Xóa bộ lọc">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            <?php endif; ?>
        </form>

        <div class="customer-account-table-wrap">
            <table class="customer-account-table">
                <thead>
                    <tr>
                        <th>Khách hàng</th>
                        <th>Liên hệ</th>
                        <th>Thành viên</th>
                        <th>Vé đã mua</th>
                        <th>Ngày sinh</th>
                        <th>Trạng thái</th>
                        <th class="is-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $khachHangs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $isActive = (bool) $item->trang_thai_hoat_dong;
                            $memberName = $item->thanhVien?->ten_hang;
                        ?>

                        <tr>
                            <td>
                                <div class="customer-profile-cell">
                                    <span class="customer-avatar">
                                        <i class="fa-solid fa-user"></i>
                                    </span>
                                    <div>
                                        <strong><?php echo e($item->ho_ten); ?></strong>
                                        <small>
                                            <i class="fa-regular fa-calendar"></i>
                                            Tạo <?php echo e($item->created_at?->format('d/m/Y') ?? '-'); ?>

                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="customer-contact">
                                    <strong><?php echo e($item->email); ?></strong>
                                    <small>
                                        <i class="fa-solid fa-phone"></i>
                                        <?php echo e($item->so_dien_thoai ?: 'Chưa có SĐT'); ?>

                                    </small>
                                </div>
                            </td>
                            <td>
                                <span class="customer-member-badge <?php echo e($memberName ? 'is-member' : 'is-empty'); ?>">
                                    <i class="fa-solid <?php echo e($memberName ? 'fa-crown' : 'fa-circle-minus'); ?>"></i>
                                    <?php echo e($memberName ? strtoupper($memberName) : 'Chưa có'); ?>

                                </span>
                            </td>
                            <td>
                                <span class="customer-ticket-count">
                                    <i class="fa-solid fa-ticket"></i>
                                    <?php echo e(number_format($item->ve_xem_phims_count)); ?>

                                </span>
                            </td>
                            <td>
                                <span class="customer-date">
                                    <i class="fa-solid fa-cake-candles"></i>
                                    <?php echo e($item->ngay_sinh?->format('d/m/Y') ?? '-'); ?>

                                </span>
                            </td>
                            <td>
                                <span class="customer-status <?php echo e($isActive ? 'is-active' : 'is-locked'); ?>">
                                    <i class="fa-solid <?php echo e($isActive ? 'fa-circle-check' : 'fa-lock'); ?>"></i>
                                    <?php echo e($isActive ? 'Hoạt động' : 'Bị khóa'); ?>

                                </span>
                            </td>
                            <td>
                                <div class="customer-actions">
                                    <a href="<?php echo e(route('admin.khach-hang.show', $item)); ?>" class="customer-action-btn is-view" title="Chi tiết">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="<?php echo e(route('admin.khach-hang.edit', $item)); ?>" class="customer-action-btn is-edit" title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form method="POST" action="<?php echo e(route('admin.khach-hang.toggle-status', $item)); ?>" onsubmit="return confirm('Bạn có chắc muốn thay đổi trạng thái tài khoản này?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PATCH'); ?>
                                        <button type="submit" class="customer-action-btn <?php echo e($isActive ? 'is-lock' : 'is-unlock'); ?>" title="<?php echo e($isActive ? 'Khóa tài khoản' : 'Mở khóa tài khoản'); ?>">
                                            <i class="fa-solid <?php echo e($isActive ? 'fa-lock' : 'fa-lock-open'); ?>"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7">
                                <div class="customer-account-empty">
                                    <i class="fa-solid fa-user-group"></i>
                                    <h3>Chưa có khách hàng phù hợp</h3>
                                    <p>Thử đổi bộ lọc hoặc tạo tài khoản khách hàng mới để hỗ trợ đặt vé tại quầy.</p>
                                    <a href="<?php echo e(route('admin.khach-hang.create')); ?>" class="customer-account-primary-btn">
                                        <i class="fa-solid fa-user-plus"></i>
                                        Thêm khách hàng
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="customer-account-pagination">
            <?php echo e($khachHangs->links()); ?>

        </div>
    </section>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\laragon\www\Cinema\WD-11-Cinehome-cinema\resources\views/admin/khach_hang/index.blade.php ENDPATH**/ ?>