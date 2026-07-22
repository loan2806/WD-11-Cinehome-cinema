<?php $__env->startSection('page-title', 'Nhật ký hệ thống'); ?>
<?php $__env->startSection('page-subtitle', 'Theo dõi thao tác người dùng và quản trị trên hệ thống CineHome'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $hasFilters = request()->hasAny(['keyword', 'chuc_nang', 'from', 'to']);
    $statCards = [
        ['label' => 'Bản ghi phù hợp', 'value' => $summary['filtered'], 'icon' => 'fa-clipboard-list', 'tone' => 'is-total'],
        ['label' => 'Hôm nay', 'value' => $summary['today'], 'icon' => 'fa-calendar-day', 'tone' => 'is-today'],
        ['label' => 'Chức năng', 'value' => $summary['modules'], 'icon' => 'fa-layer-group', 'tone' => 'is-module'],
        ['label' => 'Người thao tác', 'value' => $summary['actors'], 'icon' => 'fa-users-gear', 'tone' => 'is-actor'],
    ];
?>

<div class="activity-log-page">
    <section class="activity-log-hero">
        <div>
            <span class="activity-log-kicker">
                <i class="fa-solid fa-shield-halved"></i>
                Audit Trail
            </span>
            <h2>Nhật ký hoạt động hệ thống</h2>
            <p>Kiểm tra nhanh ai đã thao tác, thao tác ở module nào, thời điểm nào và từ địa chỉ IP nào.</p>

            <div class="activity-log-meta">
                <span><i class="fa-solid fa-database"></i> <?php echo e(number_format($logs->total())); ?> bản ghi đang lọc</span>
                <span><i class="fa-regular fa-clock"></i> Sắp xếp mới nhất trước</span>
                <span><i class="fa-solid fa-filter"></i> <?php echo e($hasFilters ? 'Đang áp dụng bộ lọc' : 'Chưa lọc dữ liệu'); ?></span>
            </div>
        </div>

        <form method="GET" action="<?php echo e(route('admin.activity-logs.index')); ?>" class="activity-log-filter">
            <label class="activity-log-search">
                <span>Tìm kiếm</span>
                <div>
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input name="keyword" value="<?php echo e(request('keyword')); ?>" placeholder="Hành động, mô tả, email, IP...">
                </div>
            </label>

            <label>
                <span>Chức năng</span>
                <select name="chuc_nang">
                    <option value="">Tất cả chức năng</option>
                    <?php $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($module); ?>" <?php if(request('chuc_nang') === $module): echo 'selected'; endif; ?>><?php echo e($module); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>

            <label>
                <span>Từ ngày</span>
                <input type="date" name="from" value="<?php echo e(request('from')); ?>">
            </label>

            <label>
                <span>Đến ngày</span>
                <input type="date" name="to" value="<?php echo e(request('to')); ?>">
            </label>

            <div class="activity-log-filter-actions">
                <button type="submit">
                    <i class="fa-solid fa-filter"></i>
                    Lọc nhật ký
                </button>
                <?php if($hasFilters): ?>
                    <a href="<?php echo e(route('admin.activity-logs.index')); ?>" title="Xóa bộ lọc">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <section class="activity-log-stats">
        <?php $__currentLoopData = $statCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <article class="activity-log-stat <?php echo e($card['tone']); ?>">
                <span><i class="fa-solid <?php echo e($card['icon']); ?>"></i></span>
                <div>
                    <small><?php echo e($card['label']); ?></small>
                    <strong><?php echo e(number_format($card['value'])); ?></strong>
                </div>
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </section>

    <section class="activity-log-panel">
        <div class="activity-log-panel-head">
            <div>
                <span class="activity-log-kicker">Danh sách</span>
                <h3>Dòng thời gian thao tác</h3>
                <p>Mỗi bản ghi gồm người thao tác, module, hành động, mô tả và IP để hỗ trợ rà soát nhanh.</p>
            </div>
            <span class="activity-log-count">
                <i class="fa-solid fa-list-check"></i>
                <?php echo e(number_format($logs->total())); ?> bản ghi
            </span>
        </div>

        <div class="activity-log-table-wrap">
            <table class="activity-log-table">
                <thead>
                    <tr>
                        <th>Thời gian</th>
                        <th>Người thao tác</th>
                        <th>Chức năng</th>
                        <th>Hành động & mô tả</th>
                        <th>Địa chỉ IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $actorName = $log->nguoiDung?->ho_ten ?: 'Hệ thống';
                            $actorEmail = $log->nguoiDung?->email ?: 'Tác vụ tự động';
                            $initial = mb_strtoupper(mb_substr($actorName, 0, 1));
                            $actionText = mb_strtolower($log->hanh_dong ?? '');
                            $tone = 'is-neutral';

                            if (str_contains($actionText, 'xóa') || str_contains($actionText, 'hủy') || str_contains($actionText, 'khóa')) {
                                $tone = 'is-danger';
                            } elseif (str_contains($actionText, 'thêm') || str_contains($actionText, 'tạo')) {
                                $tone = 'is-create';
                            } elseif (str_contains($actionText, 'cập nhật') || str_contains($actionText, 'sửa')) {
                                $tone = 'is-update';
                            }
                        ?>
                        <tr>
                            <td data-label="Thời gian">
                                <span class="activity-log-time"><?php echo e($log->created_at->format('d/m/Y H:i:s')); ?></span>
                                <small><?php echo e($log->created_at->diffForHumans()); ?></small>
                            </td>

                            <td data-label="Người thao tác">
                                <div class="activity-log-actor">
                                    <span><?php echo e($initial); ?></span>
                                    <div>
                                        <strong><?php echo e($actorName); ?></strong>
                                        <small><?php echo e($actorEmail); ?></small>
                                    </div>
                                </div>
                            </td>

                            <td data-label="Chức năng">
                                <span class="activity-log-module">
                                    <i class="fa-solid fa-cube"></i>
                                    <?php echo e($log->chuc_nang ?: 'Không xác định'); ?>

                                </span>
                            </td>

                            <td data-label="Hành động">
                                <div class="activity-log-action">
                                    <span class="<?php echo e($tone); ?>"><?php echo e($log->hanh_dong ?: 'Không rõ hành động'); ?></span>
                                    <p><?php echo e($log->mo_ta ?: 'Không có mô tả chi tiết.'); ?></p>
                                </div>
                            </td>

                            <td data-label="Địa chỉ IP">
                                <span class="activity-log-ip">
                                    <i class="fa-solid fa-network-wired"></i>
                                    <?php echo e($log->dia_chi_ip ?: '-'); ?>

                                </span>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5">
                                <div class="activity-log-empty">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                    <h3>Chưa có nhật ký phù hợp</h3>
                                    <p>Thử thay đổi từ khóa, chức năng hoặc khoảng ngày để xem thêm bản ghi.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="activity-log-pagination">
            <span>
                Hiển thị <?php echo e($logs->firstItem() ?? 0); ?> - <?php echo e($logs->lastItem() ?? 0); ?>

                trên <?php echo e(number_format($logs->total())); ?> bản ghi
            </span>
            <?php echo e($logs->links()); ?>

        </div>
    </section>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/admin/activity-logs/index.blade.php ENDPATH**/ ?>