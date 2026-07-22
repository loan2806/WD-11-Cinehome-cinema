<?php $__env->startSection('title', 'Lịch chiếu - CineHome'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $showtimeGroups = $suatChieus->groupBy(
            fn($showtime) => \Carbon\Carbon::parse($showtime->thoi_gian_chieu)->format('Y-m-d'),
        );
        $activeFilters = collect([
            request('phim_id'),
            request('rap_chieu_phim_id'),
            request('ngay_chieu'),
            request('trang_thai'),
        ])->filter()->count();
        $totalMovies = $suatChieus->pluck('phim_id')->unique()->count();
        $totalCinemas = $suatChieus->pluck('rap_chieu_phim_id')->unique()->count();
        $firstShowtime = $suatChieus->first();
    ?>

    <section class="schedule-page">
        <section class="schedule-hero">
            <div class="container-fluid px-5">
                <div class="schedule-hero-grid">
                    <div class="schedule-hero-copy reveal-on-scroll">
                        <span class="schedule-kicker">
                            <i class="fa-solid fa-calendar-days"></i>
                            CineHome Showtime
                        </span>
                        <h1>Lịch chiếu phim</h1>
                        <p>Chọn nhanh phim, rạp và ngày chiếu để tìm suất phù hợp. Giao diện được tối ưu cho thao tác đặt vé nhanh.</p>

                        <div class="schedule-hero-stats">
                            <div>
                                <strong><?php echo e($suatChieus->count()); ?></strong>
                                <span>Suất chiếu</span>
                            </div>
                            <div>
                                <strong><?php echo e($totalMovies); ?></strong>
                                <span>Phim có lịch</span>
                            </div>
                            <div>
                                <strong><?php echo e($totalCinemas); ?></strong>
                                <span>Rạp đang mở</span>
                            </div>
                        </div>
                    </div>

                    <div class="schedule-spotlight reveal-on-scroll">
                        <?php if($firstShowtime): ?>
                            <img src="<?php echo e(asset('storage/movies/' . $firstShowtime->phim->poster)); ?>"
                                alt="<?php echo e($firstShowtime->phim->ten_phim); ?>">
                            <div>
                                <span>Suất gần nhất</span>
                                <h2><?php echo e($firstShowtime->phim->ten_phim); ?></h2>
                                <p>
                                    <?php echo e($firstShowtime->thoi_gian_chieu->format('H:i d/m')); ?>

                                    · <?php echo e($firstShowtime->rapChieuPhim?->ten_rap ?? 'CineHome'); ?>

                                </p>
                            </div>
                        <?php else: ?>
                            <div class="schedule-spotlight-empty">
                                <i class="fa-regular fa-calendar"></i>
                                <strong>Đang cập nhật lịch chiếu</strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <main class="schedule-main">
            <div class="container-fluid px-5">
                <section class="schedule-filter-panel reveal-on-scroll">
                    <form method="GET" action="<?php echo e(route('user.showtimes.index')); ?>" class="schedule-filter-form">
                        <label>
                            <span>Phim</span>
                            <select name="phim_id">
                                <option value="">Tất cả phim</option>
                                <?php $__currentLoopData = $movies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movie): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($movie->id); ?>" <?php if(request('phim_id') == $movie->id): echo 'selected'; endif; ?>>
                                        <?php echo e($movie->ten_phim); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </label>

                        <label>
                            <span>Rạp</span>
                            <select name="rap_chieu_phim_id">
                                <option value="">Tất cả rạp</option>
                                <?php $__currentLoopData = $rapChieuPhims; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rap): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($rap->id); ?>" <?php if(request('rap_chieu_phim_id') == $rap->id): echo 'selected'; endif; ?>>
                                        <?php echo e($rap->ten_rap); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </label>

                        <label>
                            <span>Ngày</span>
                            <input type="date" name="ngay_chieu" value="<?php echo e(request('ngay_chieu')); ?>">
                        </label>

                        <label>
                            <span>Trạng thái</span>
                            <select name="trang_thai">
                                <option value="">Tất cả lịch</option>
                                <option value="dang_chieu" <?php if(request('trang_thai') === 'dang_chieu'): echo 'selected'; endif; ?>>Hôm nay</option>
                                <option value="sap_chieu" <?php if(request('trang_thai') === 'sap_chieu'): echo 'selected'; endif; ?>>Sắp chiếu</option>
                            </select>
                        </label>

                        <div class="schedule-filter-actions">
                            <button type="submit">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                Tìm lịch
                            </button>
                            <a href="<?php echo e(route('user.showtimes.index')); ?>" aria-label="Xóa bộ lọc">
                                <i class="fa-solid fa-rotate-left"></i>
                            </a>
                        </div>
                    </form>
                </section>

                <section class="schedule-date-strip reveal-on-scroll" aria-label="Chọn nhanh ngày chiếu">
                    <a href="<?php echo e(route('user.showtimes.index', request()->except('ngay_chieu'))); ?>"
                        class="<?php echo e(request('ngay_chieu') ? '' : 'active'); ?>">
                        <span>Tất cả</span>
                        <strong>10 ngày</strong>
                    </a>

                    <?php $__currentLoopData = $dateOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dateOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('user.showtimes.index', array_merge(request()->except('ngay_chieu'), ['ngay_chieu' => $dateOption['value']]))); ?>"
                            class="<?php echo e($dateOption['active'] ? 'active' : ''); ?>">
                            <span><?php echo e($dateOption['label']); ?></span>
                            <strong><?php echo e($dateOption['day']); ?></strong>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </section>

                <section class="schedule-results reveal-on-scroll">
                    <div class="schedule-results-head">
                        <div>
                            <span><?php echo e($activeFilters ? $activeFilters . ' bộ lọc đang áp dụng' : 'Tất cả lịch chiếu'); ?></span>
                            <h2><?php echo e($suatChieus->count()); ?> suất chiếu phù hợp</h2>
                        </div>
                    </div>

                    <?php $__empty_1 = true; $__currentLoopData = $showtimeGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="schedule-day-group">
                            <div class="schedule-day-heading">
                                <strong><?php echo e(\Carbon\Carbon::parse($date)->format('d/m/Y')); ?></strong>
                                <span><?php echo e($items->count()); ?> suất</span>
                            </div>

                            <div class="schedule-card-grid">
                                <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $suatChieu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <article class="schedule-card">
                                        <a href="<?php echo e(route('user.movies.show', $suatChieu->phim->slug)); ?>"
                                            class="schedule-card-poster">
                                            <img src="<?php echo e(asset('storage/movies/' . $suatChieu->phim->poster)); ?>"
                                                alt="<?php echo e($suatChieu->phim->ten_phim); ?>">
                                            <span><?php echo e($suatChieu->phim->gioi_han_tuoi); ?></span>
                                        </a>

                                        <div class="schedule-card-body">
                                            <div class="schedule-card-time">
                                                <strong><?php echo e($suatChieu->thoi_gian_chieu->format('H:i')); ?></strong>
                                                <span><?php echo e($suatChieu->thoi_gian_chieu->format('d/m')); ?></span>
                                            </div>

                                            <h3><?php echo e($suatChieu->phim->ten_phim); ?></h3>
                                            <p>
                                                <i class="fa-solid fa-building"></i>
                                                <?php echo e($suatChieu->rapChieuPhim?->ten_rap ?? 'CineHome'); ?>

                                            </p>
                                            <p>
                                                <i class="fa-solid fa-couch"></i>
                                                <?php echo e($suatChieu->phongChieu?->ten_phong ?? 'Phòng chiếu'); ?>

                                            </p>
                                            <p>
                                                <i class="fa-solid fa-tags"></i>
                                                <?php echo e($suatChieu->phim->genres->pluck('ten_the_loai')->take(2)->join(', ') ?: 'Điện ảnh'); ?>

                                            </p>

                                            <div class="schedule-card-footer">
                                                <span><?php echo e(number_format((float) $suatChieu->gia_ve, 0, ',', '.')); ?>đ</span>
                                                <div>
                                                    <a href="<?php echo e(route('user.showtimes.show', $suatChieu)); ?>"
                                                        class="schedule-detail-btn">Chi tiết</a>
                                                    <a href="<?php echo e(route('dat_ve.chon_ghe', $suatChieu->id)); ?>"
                                                        class="booking-link schedule-book-btn">
                                                        Đặt vé
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="schedule-empty-state">
                            <i class="fa-regular fa-calendar-xmark"></i>
                            <strong>Không có lịch chiếu phù hợp</strong>
                            <span>Hãy thử đổi phim, rạp hoặc ngày chiếu khác.</span>
                            <a href="<?php echo e(route('user.showtimes.index')); ?>">Xóa bộ lọc</a>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </main>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/user/showtimes/index.blade.php ENDPATH**/ ?>