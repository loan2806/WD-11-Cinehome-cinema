<?php $__env->startSection('title', 'Quản lý nhân viên'); ?>
<?php $__env->startSection('page-title', 'Quản lý nhân viên'); ?>
<?php $__env->startSection('page-subtitle', 'Theo dõi tài khoản, trạng thái và thao tác nhanh với nhân sự vận hành rạp'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $summary = $summary ?? [
        'total' => $nhanViens->total(),
        'active' => 0,
        'locked' => 0,
        'new_this_month' => 0,
    ];

    $activeFilters = collect([
        request('keyword'),
        request('status'),
    ])->filter()->count();
?>

<div class="staff-list-page">
    <?php echo $__env->make('admin.partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <section class="staff-list-hero">
        <div class="staff-list-hero-copy">
            <span class="staff-list-kicker">
                <i class="fa-solid fa-user-shield"></i>
                Quản trị nhân sự
            </span>
            <h2>Danh sách nhân viên CineHome</h2>
            <p>Quản lý tài khoản nhân viên, kiểm tra trạng thái hoạt động và xử lý khóa/mở khóa nhanh trong cùng một màn hình.</p>
            <div class="staff-list-hero-meta">
                <span><i class="fa-solid fa-users"></i> <?php echo e(number_format($summary['total'])); ?> nhân viên</span>
                <span><i class="fa-solid fa-circle-check"></i> <?php echo e(number_format($summary['active'])); ?> đang hoạt động</span>
                <span><i class="fa-solid fa-calendar-plus"></i> <?php echo e(number_format($summary['new_this_month'])); ?> mới trong tháng</span>
            </div>
        </div>

        <a href="<?php echo e(route('admin.nhanviens.create')); ?>" class="staff-list-primary-btn">
            <i class="fa-solid fa-plus"></i>
            Thêm nhân viên
        </a>
    </section>

    <section class="staff-list-stats">
        <article class="staff-list-stat">
            <span class="staff-list-stat-icon is-total"><i class="fa-solid fa-id-badge"></i></span>
            <div>
                <small>Tổng nhân viên</small>
                <strong><?php echo e(number_format($summary['total'])); ?></strong>
            </div>
        </article>
        <article class="staff-list-stat">
            <span class="staff-list-stat-icon is-active"><i class="fa-solid fa-user-check"></i></span>
            <div>
                <small>Đang hoạt động</small>
                <strong><?php echo e(number_format($summary['active'])); ?></strong>
            </div>
        </article>
        <article class="staff-list-stat">
            <span class="staff-list-stat-icon is-locked"><i class="fa-solid fa-user-lock"></i></span>
            <div>
                <small>Đã khóa</small>
                <strong><?php echo e(number_format($summary['locked'])); ?></strong>
            </div>
        </article>
        <article class="staff-list-stat">
            <span class="staff-list-stat-icon is-new"><i class="fa-solid fa-calendar-plus"></i></span>
            <div>
                <small>Mới trong tháng</small>
                <strong><?php echo e(number_format($summary['new_this_month'])); ?></strong>
            </div>
        </article>
    </section>

    <section class="staff-list-panel">
        <div class="staff-list-panel-head">
            <div>
                <span class="staff-list-kicker">Danh sách</span>
                <h3>Nhân viên hệ thống</h3>
                <p>Đang hiển thị <?php echo e($nhanViens->count()); ?> / <?php echo e($nhanViens->total()); ?> nhân viên theo bộ lọc hiện tại.</p>
            </div>
        </div>

        <form method="GET" action="<?php echo e(route('admin.nhanviens.index')); ?>" class="staff-list-filter">
            <label class="staff-list-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input
                    type="text"
                    name="keyword"
                    value="<?php echo e(request('keyword')); ?>"
                    placeholder="Tìm theo tên hoặc email..."
                >
            </label>

            <select name="status" class="admin-input">
                <option value="">Tất cả trạng thái</option>
                <option value="active" <?php if(request('status') === 'active'): echo 'selected'; endif; ?>>Đang hoạt động</option>
                <option value="locked" <?php if(request('status') === 'locked'): echo 'selected'; endif; ?>>Đã khóa</option>
            </select>

            <button class="staff-list-filter-btn" type="submit">
                <i class="fa-solid fa-filter"></i>
                Lọc
                <?php if($activeFilters): ?>
                    <span><?php echo e($activeFilters); ?></span>
                <?php endif; ?>
            </button>

            <?php if($activeFilters): ?>
                <a href="<?php echo e(route('admin.nhanviens.index')); ?>" class="staff-list-reset-btn" title="Xóa bộ lọc">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            <?php endif; ?>
        </form>

        <div class="staff-list-table-wrap">
            <table class="staff-list-table">
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Nhân viên</th>
                        <th>Email</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th class="is-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $nhanViens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nhanVien): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $isActive = (bool) $nhanVien->trang_thai_hoat_dong;
                            $isSelf = auth()->id() === $nhanVien->id;
                        ?>

                        <tr>
                            <td>
                                <span class="staff-id-badge">#<?php echo e($nhanVien->id); ?></span>
                            </td>
                            <td>
                                <div class="staff-profile-cell">
                                    <span class="staff-avatar">
                                        <i class="fa-solid fa-user-tie"></i>
                                    </span>
                                    <div>
                                        <strong><?php echo e($nhanVien->ho_ten); ?></strong>
                                        <small>
                                            <i class="fa-solid fa-briefcase"></i>
                                            Nhân viên hệ thống
                                            <?php if($isSelf): ?>
                                                <em>Tài khoản của bạn</em>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="staff-email"><?php echo e($nhanVien->email); ?></span>
                            </td>
                            <td>
                                <span class="staff-status <?php echo e($isActive ? 'is-active' : 'is-locked'); ?>">
                                    <i class="fa-solid <?php echo e($isActive ? 'fa-circle-check' : 'fa-lock'); ?>"></i>
                                    <?php echo e($isActive ? 'Hoạt động' : 'Đã khóa'); ?>

                                </span>
                            </td>
                            <td>
                                <span class="staff-date">
                                    <i class="fa-regular fa-calendar"></i>
                                    <?php echo e($nhanVien->created_at?->format('d/m/Y') ?? '-'); ?>

                                </span>
                            </td>
                            <td>
                                <div class="staff-actions">
                                    <a href="<?php echo e(route('admin.nhanviens.edit', $nhanVien)); ?>" class="staff-action-btn is-edit" title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>

                                    <form method="POST" action="<?php echo e(route('admin.nhanviens.toggle-status', $nhanVien)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PATCH'); ?>
                                        <button
                                            class="staff-action-btn <?php echo e($isActive ? 'is-lock' : 'is-unlock'); ?>"
                                            type="submit"
                                            title="<?php echo e($isActive ? 'Khóa tài khoản' : 'Mở khóa tài khoản'); ?>"
                                            <?php if($isSelf): echo 'disabled'; endif; ?>
                                        >
                                            <i class="fa-solid <?php echo e($isActive ? 'fa-lock' : 'fa-lock-open'); ?>"></i>
                                        </button>
                                    </form>

                                    <form method="POST" action="<?php echo e(route('admin.nhanviens.destroy', $nhanVien)); ?>" onsubmit="return confirm('Xóa nhân viên <?php echo e($nhanVien->ho_ten); ?>?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button class="staff-action-btn is-delete" type="submit" title="Xóa nhân viên" <?php if($isSelf): echo 'disabled'; endif; ?>>
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6">
                                <div class="staff-list-empty">
                                    <i class="fa-solid fa-user-group"></i>
                                    <h3>Chưa có nhân viên phù hợp</h3>
                                    <p>Thử đổi bộ lọc hoặc tạo tài khoản nhân viên mới để bắt đầu phân quyền vận hành.</p>
                                    <a href="<?php echo e(route('admin.nhanviens.create')); ?>" class="staff-list-primary-btn">
                                        <i class="fa-solid fa-plus"></i>
                                        Thêm nhân viên
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="staff-list-pagination">
            <?php echo e($nhanViens->links()); ?>

        </div>
    </section>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/admin/nhanviens/index.blade.php ENDPATH**/ ?>