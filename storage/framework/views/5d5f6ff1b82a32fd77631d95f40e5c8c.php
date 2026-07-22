<?php $__env->startSection('title', 'Dashboard Admin - CineHome'); ?>
<?php $__env->startSection('page-title', 'Dashboard quản lý'); ?>
<?php $__env->startSection('page-subtitle', 'Theo dõi doanh thu, vé bán, suất chiếu và hoạt động vận hành'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $user = Auth::user();
        $canSeeRevenue = $user->can('thong_ke_doanh_thu');
        $canSellTicket = $user->can('ban_ve_tai_quay');
        $canManageMovie = $user->can('quan_ly_phim_suat_chieu');
        $canScanTicket = $user->can('soat_ve_vao_cua');
        $canManageCustomer = $user->can('quan_ly_khach_hang');

        $posterUrl = function (?string $poster): string {
            if (blank($poster)) {
                return 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=360&q=80';
            }

            $poster = ltrim($poster, '/');

            if (\Illuminate\Support\Str::startsWith($poster, ['http://', 'https://'])) {
                return $poster;
            }

            if (\Illuminate\Support\Str::startsWith($poster, 'storage/')) {
                return asset($poster);
            }

            if (\Illuminate\Support\Str::startsWith($poster, 'movies/')) {
                return asset('storage/' . $poster);
            }

            return asset('storage/movies/' . $poster);
        };

        $showingCount = $latestMovies->filter(
            fn($movie) => $movie->showtimes->contains('trang_thai', \App\Models\SuatChieu::TRANG_THAI_DANG_CHIEU),
        )->count();

        $comingCount = $latestMovies->filter(
            fn($movie) => $movie->showtimes->contains('trang_thai', \App\Models\SuatChieu::TRANG_THAI_SAP_CHIEU) ||
                $movie->showtimes->contains('trang_thai', \App\Models\SuatChieu::TRANG_THAI_SAP_RA_MAT),
        )->count();
    ?>

    <div class="admin-dashboard">
        <section class="admin-dashboard-hero">
            <div class="admin-dashboard-hero__copy">
                <span class="admin-dashboard-eyebrow">
                    <i class="fa-solid fa-chart-line"></i>
                    Trung tâm điều hành
                </span>
                <h2>Xin chào, <?php echo e($user->ho_ten ?? $user->name ?? 'Admin CineHome'); ?></h2>
                <p>
                    Nắm nhanh tình hình vận hành trong ngày, theo dõi phim mới, suất chiếu và các tác vụ quan trọng của hệ thống rạp CineHome.
                </p>

                <div class="admin-dashboard-actions">
                    <?php if($canSellTicket): ?>
                        <a href="<?php echo e(route('staff.ban-ve.index')); ?>" class="dashboard-primary-action">
                            <i class="fa-solid fa-ticket"></i>
                            Bán vé tại quầy
                        </a>
                    <?php endif; ?>

                    <?php if($canManageMovie): ?>
                        <a href="<?php echo e(route('admin.suat-chieus.index')); ?>" class="dashboard-secondary-action">
                            <i class="fa-solid fa-calendar-plus"></i>
                            Quản lý suất chiếu
                        </a>
                    <?php endif; ?>

                    <?php if($canSeeRevenue): ?>
                        <a href="<?php echo e(route('admin.thong-ke.index')); ?>" class="dashboard-secondary-action">
                            <i class="fa-solid fa-chart-pie"></i>
                            Báo cáo doanh thu
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="admin-dashboard-hero__panel">
                <span>Phiên vận hành</span>
                <strong><?php echo e(now()->format('d/m/Y')); ?></strong>
                <div class="hero-operation-list">
                    <div>
                        <i class="fa-solid fa-film"></i>
                        <p>
                            <b><?php echo e($latestMovies->count()); ?></b>
                            phim mới cập nhật
                        </p>
                    </div>
                    <div>
                        <i class="fa-solid fa-clock"></i>
                        <p>
                            <b><?php echo e($todaySchedules->count()); ?></b>
                            suất chiếu hôm nay
                        </p>
                    </div>
                    <div>
                        <i class="fa-solid fa-bolt"></i>
                        <p>
                            <b><?php echo e($canSeeRevenue ? 'Live' : 'Ready'); ?></b>
                            trạng thái hệ thống
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="dashboard-stat-grid">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('thong_ke_doanh_thu')): ?>
                <article class="dashboard-stat-card stat-revenue">
                    <div class="dashboard-stat-top">
                        <span><i class="fa-solid fa-sack-dollar"></i></span>
                        <small class="dashboard-stat-badge is-up">+12%</small>
                    </div>
                    <p>Doanh thu hôm nay</p>
                    <strong><?php echo e($statData['doanh_thu_hom_nay']); ?></strong>
                    <div class="dashboard-stat-foot">
                        <span>So với hôm qua</span>
                        <i class="fa-solid fa-arrow-trend-up"></i>
                    </div>
                </article>
            <?php endif; ?>

            <?php if($canSeeRevenue || $canSellTicket): ?>
                <article class="dashboard-stat-card">
                    <div class="dashboard-stat-top">
                        <span><i class="fa-solid fa-ticket"></i></span>
                        <small class="dashboard-stat-badge">Vé</small>
                    </div>
                    <p>Vé đã bán</p>
                    <strong><?php echo e($statData['ve_da_ban']); ?></strong>
                    <div class="dashboard-stat-foot">
                        <span>Giao dịch trong ngày</span>
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                </article>
            <?php endif; ?>

            <?php if($canSeeRevenue || $canSellTicket): ?>
                <article class="dashboard-stat-card">
                    <div class="dashboard-stat-top">
                        <span><i class="fa-solid fa-users"></i></span>
                        <small class="dashboard-stat-badge">Khách</small>
                    </div>
                    <p>Lượng khách</p>
                    <strong><?php echo e($statData['luong_khach']); ?></strong>
                    <div class="dashboard-stat-foot">
                        <span>Khách vào rạp</span>
                        <i class="fa-solid fa-person-walking"></i>
                    </div>
                </article>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('thong_ke_doanh_thu')): ?>
                <article class="dashboard-stat-card">
                    <div class="dashboard-stat-top">
                        <span><i class="fa-solid fa-burger"></i></span>
                        <small class="dashboard-stat-badge">F&B</small>
                    </div>
                    <p>Doanh thu đồ ăn</p>
                    <strong><?php echo e($statData['doanh_thu_do_an']); ?></strong>
                    <div class="dashboard-stat-foot">
                        <span>Bắp nước & combo</span>
                        <i class="fa-solid fa-mug-hot"></i>
                    </div>
                </article>
            <?php endif; ?>
        </section>

        <?php if (! ($canSeeRevenue)): ?>
            <section class="dashboard-staff-note">
                <div>
                    <span><i class="fa-solid fa-wand-magic-sparkles"></i></span>
                    <div>
                        <h4>Chế độ vận hành quầy</h4>
                        <p>
                            Tài khoản của bạn đang được phân quyền theo nghiệp vụ. Các tác vụ khả dụng sẽ hiển thị ngay bên dưới để thao tác nhanh hơn.
                        </p>
                    </div>
                </div>

                <div class="dashboard-staff-actions">
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ban_ve_tai_quay')): ?>
                        <a href="<?php echo e(route('staff.ban-ve.index')); ?>">Bán vé trực tiếp</a>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('soat_ve_vao_cua')): ?>
                        <a href="<?php echo e(route('admin.soat-ve.index')); ?>">Soát vé QR</a>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <section class="dashboard-command-grid">
            <div class="admin-panel dashboard-panel dashboard-movie-panel">
                <div class="panel-header dashboard-panel-header">
                    <div>
                        <span class="dashboard-section-kicker">Thư viện phim</span>
                        <h5>Phim mới cập nhật</h5>
                        <small><?php echo e($showingCount); ?> đang chiếu, <?php echo e($comingCount); ?> sắp chiếu trong danh sách gần nhất</small>
                    </div>

                    <?php if($canManageMovie): ?>
                        <a href="<?php echo e(route('admin.phims.index')); ?>" class="dashboard-panel-link">
                            Quản lý phim
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    <?php endif; ?>
                </div>

                <div class="table-responsive">
                    <table class="admin-table dashboard-table">
                        <thead>
                            <tr>
                                <th>Phim</th>
                                <th>Thể loại</th>
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
                                        $statusText = 'Chưa có suất';
                                        $statusClass = 'status-stop';
                                    }
                                ?>
                                <tr>
                                    <td>
                                        <div class="dashboard-movie-cell">
                                            <img src="<?php echo e($posterUrl($movie->poster)); ?>" alt="<?php echo e($movie->ten_phim); ?>">
                                            <div>
                                                <strong><?php echo e($movie->ten_phim); ?></strong>
                                                <small><?php echo e($movie->country?->ten_quoc_gia ?? 'Đang cập nhật quốc gia'); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo e($movie->genres->pluck('ten_the_loai')->take(2)->join(', ') ?: 'Điện ảnh'); ?></td>
                                    <td><?php echo e($movie->thoi_luong); ?> phút</td>
                                    <td>
                                        <span class="status-badge <?php echo e($statusClass); ?>"><?php echo e($statusText); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4">
                                        <div class="dashboard-empty-state">
                                            <i class="fa-solid fa-film"></i>
                                            <strong>Chưa có phim nào</strong>
                                            <span>Thêm phim mới để bắt đầu vận hành lịch chiếu.</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <aside class="dashboard-side-stack">
                <section class="admin-panel dashboard-panel">
                    <div class="panel-header dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">Tác vụ nhanh</span>
                            <h5>Lối tắt vận hành</h5>
                        </div>
                    </div>

                    <div class="dashboard-quick-actions">
                        <?php if($canManageMovie): ?>
                            <a href="<?php echo e(route('admin.phims.create')); ?>">
                                <span><i class="fa-solid fa-plus"></i></span>
                                <div>
                                    <strong>Thêm phim mới</strong>
                                    <small>Cập nhật poster, thể loại, trailer</small>
                                </div>
                            </a>

                            <a href="<?php echo e(route('admin.suat-chieus.create')); ?>">
                                <span><i class="fa-solid fa-calendar-plus"></i></span>
                                <div>
                                    <strong>Tạo suất chiếu</strong>
                                    <small>Lên lịch theo phòng và khung giờ</small>
                                </div>
                            </a>
                        <?php endif; ?>

                        <?php if($canSellTicket): ?>
                            <a href="<?php echo e(route('staff.ban-ve.index')); ?>">
                                <span><i class="fa-solid fa-cash-register"></i></span>
                                <div>
                                    <strong>Bán vé tại quầy</strong>
                                    <small>Chọn suất, ghế và thanh toán</small>
                                </div>
                            </a>
                        <?php endif; ?>

                        <?php if($canScanTicket): ?>
                            <a href="<?php echo e(route('admin.soat-ve.index')); ?>">
                                <span><i class="fa-solid fa-qrcode"></i></span>
                                <div>
                                    <strong>Soát vé QR</strong>
                                    <small>Xác thực vé trước cửa phòng</small>
                                </div>
                            </a>
                        <?php endif; ?>

                        <?php if($canManageCustomer): ?>
                            <a href="<?php echo e(route('admin.khach-hang.index')); ?>">
                                <span><i class="fa-solid fa-user-group"></i></span>
                                <div>
                                    <strong>Khách hàng</strong>
                                    <small>Hồ sơ, thành viên và voucher</small>
                                </div>
                            </a>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="admin-panel dashboard-panel">
                    <div class="panel-header dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">Hôm nay</span>
                            <h5>Suất chiếu gần nhất</h5>
                            <small><?php echo e($todaySchedules->count()); ?> suất đang trong lịch</small>
                        </div>

                        <?php if($canManageMovie): ?>
                            <a href="<?php echo e(route('admin.suat-chieus.index')); ?>" class="dashboard-panel-link compact">
                                Tất cả
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="dashboard-showtime-list">
                        <?php $__empty_1 = true; $__currentLoopData = $todaySchedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $startTime = \Carbon\Carbon::parse($schedule->thoi_gian_chieu);
                            ?>
                            <article class="dashboard-showtime-card">
                                <time><?php echo e($startTime->format('H:i')); ?></time>
                                <div>
                                    <strong><?php echo e($schedule->phim?->ten_phim ?? 'Phim đang cập nhật'); ?></strong>
                                    <span>
                                        <?php echo e($schedule->phongChieu?->ten_phong ?? 'Phòng chiếu'); ?>

                                        <?php if($schedule->rapChieuPhim?->ten_rap): ?>
                                            · <?php echo e($schedule->rapChieuPhim->ten_rap); ?>

                                        <?php endif; ?>
                                    </span>
                                </div>
                                <b><?php echo e(number_format((float) $schedule->gia_ve)); ?>đ</b>
                            </article>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="dashboard-empty-state compact">
                                <i class="fa-regular fa-calendar"></i>
                                <strong>Chưa có suất chiếu hôm nay</strong>
                                <span>Lên lịch suất chiếu để hệ thống bắt đầu nhận đặt vé.</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </aside>
        </section>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\laragon\www\Cinema\WD-11-Cinehome-cinema\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>