<?php $__env->startSection('title', 'Dashboard Admin - CineHome'); ?>
<?php $__env->startSection('page-title', 'Dashboard quản lý'); ?>
<?php $__env->startSection('page-subtitle', 'Theo dõi doanh thu, vé bán, lịch chiếu và hoạt động hệ thống'); ?>

<?php $__env->startSection('content'); ?>


<div class="row g-4 mb-4">

    
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('thong_ke_doanh_thu')): ?>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
                <div class="stat-label">Doanh thu hôm nay</div>
                <div class="stat-value"><?php echo e($statData['doanh_thu_hom_nay']); ?></div>
                <div class="stat-change up">
                    <i class="fa-solid fa-arrow-up"></i>
                    Tăng 12% so với hôm qua
                </div>
            </div>
        </div>
    <?php endif; ?>

    
    <?php if(auth()->user()->can('thong_ke_doanh_thu') || auth()->user()->can('ban_ve_tai_quay')): ?>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-ticket"></i>
                </div>
                <div class="stat-label">Vé đã bán</div>
                <div class="stat-value"><?php echo e($statData['ve_da_ban']); ?></div>
            </div>
        </div>
    <?php endif; ?>

    
    <?php if(auth()->user()->can('thong_ke_doanh_thu') || auth()->user()->can('ban_ve_tai_quay')): ?>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="stat-label">Lượng khách</div>
                <div class="stat-value"><?php echo e($statData['luong_khach']); ?></div>
            </div>
        </div>
    <?php endif; ?>

    
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('thong_ke_doanh_thu')): ?>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-burger"></i>
                </div>
                <div class="stat-label">Doanh thu đồ ăn</div>
                <div class="stat-value"><?php echo e($statData['doanh_thu_do_an']); ?></div>
            </div>
        </div>
    <?php endif; ?>

</div>



<?php if(!auth()->user()->can('thong_ke_doanh_thu')): ?>
    <div class="rounded-2xl border border-white/10 bg-gradient-to-r from-[#1a0b04] to-[#2b1208] p-5 mb-4 shadow-xl">
        <div class="max-w-3xl">
            <h4 class="text-lg font-black text-white flex items-center gap-2">
                <i class="fa-solid fa-wand-magic-sparkles text-[#d99a32]"></i> Xin chào, <?php echo e(Auth::user()->ho_ten); ?>!
            </h4>
            <p class="text-sm text-gray-300 mt-1.5 leading-relaxed">
                Chào mừng bạn đến với hệ thống quản trị rạp phim <strong>CineHome</strong>. Tài khoản của bạn đã được phân quyền vận hành nghiệp vụ tại quầy. Bạn có thể sử dụng các menu được cấp ở thanh điều hướng bên cạnh hoặc sử dụng nhanh các nút tác vụ bên dưới:
            </p>
            <div class="flex flex-wrap items-center gap-3 mt-4">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ban_ve_tai_quay')): ?>
                    <a href="#" class="inline-flex h-9 items-center justify-center gap-2 rounded-xl bg-[#d99a32] px-4 text-xs font-black text-[#2b1208] transition hover:opacity-90 no-underline shadow-lg shadow-[#d99a32]/10">
                        <i class="fa-solid fa-desktop"></i> Đi tới màn hình bán vé tại quầy
                    </a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('soat_ve_vao_cua')): ?>
                    <a href="<?php echo e(route('admin.soat-ve.index')); ?>" class="inline-flex h-9 items-center justify-center gap-2 rounded-xl bg-white/10 px-4 text-xs font-black text-white transition hover:bg-white/15 no-underline border border-white/5">
                        <i class="fa-solid fa-qrcode"></i> Quét mã soát vé phòng chiếu
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>


<div class="row g-4">

    
    <div class="col-xl-7">
        <div class="admin-panel">
            <div class="panel-header">
                <div>
                    <h5>Phim mới cập nhật</h5>
                    <small>Danh sách phim đang chiếu và sắp chiếu</small>
                </div>
            </div>

            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Phim</th>
                            <th>Thời lượng</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $latestMovies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movie): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $statuses = $movie->showtimes->pluck('trang_thai');

                                if ($statuses->contains(\App\Models\SuatChieu::TRANG_THAI_DANG_CHIEU)) {
                                    $statusText = 'Đang chiếu';
                                    $statusClass = 'status-showing';
                                } elseif ($statuses->contains(\App\Models\SuatChieu::TRANG_THAI_SAP_CHIEU)) {
                                    $statusText = 'Sắp chiếu';
                                    $statusClass = 'status-coming';
                                } elseif ($statuses->contains(\App\Models\SuatChieu::TRANG_THAI_SAP_RA_MAT)) {
                                    $statusText = 'Sắp ra mắt';
                                    $statusClass = 'status-coming';
                                } else {
                                    $statusText = 'Không có suất';
                                    $statusClass = 'status-coming';
                                }
                            ?>
                            <tr>
                                <td>
                                    <div class="table-movie">
                                        <img src="<?php echo e($movie->poster); ?>" alt="<?php echo e($movie->ten_phim); ?>">
                                        <div>
                                            <strong><?php echo e($movie->ten_phim); ?></strong>
                                            <small><?php echo e($movie->genres ? $movie->genres->pluck('ten_the_loai')->join(', ') : 'Thể loại'); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo e($movie->thoi_luong); ?> phút</td>
                                <td>
                                    <span class="status-badge <?php echo e($statusClass); ?>">
                                        <?php echo e($statusText); ?>

                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="3" class="text-center py-4">Chưa có phim nào</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    
    <div class="col-xl-5">
        <div class="admin-panel">
            <div class="panel-header">
                <div>
                    <h5>Lịch chiếu hôm nay</h5>
                    <small>Theo dõi suất chiếu và số vé bán</small>
                </div>
            </div>

            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Phim</th>
                            <th>Giờ</th>
                            <th>Giá vé</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $todaySchedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <strong><?php echo e($schedule->phim->ten_phim); ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <?php echo e($schedule->phongChieu->ten_phong ?? 'Phòng chiếu'); ?>

                                    </small>
                                </td>
                                <td>
                                    <span class="status-badge status-coming">
                                        <?php echo e(\Carbon\Carbon::parse($schedule->thoi_gian_chieu)->format('H:i')); ?>

                                    </span>
                                </td>
                                <td>
                                    <?php echo e(number_format($schedule->price ?? 0)); ?>đ
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="3" class="text-center py-4">Chưa có lịch chiếu hôm nay</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\WD-11-Cinehome-cinema\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>