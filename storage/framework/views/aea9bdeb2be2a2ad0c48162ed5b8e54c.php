<?php $__env->startSection('title', $movie->ten_phim . ' - CineHome'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $posterUrl = asset('storage/movies/' . $movie->poster);
        $genres = $movie->genres->pluck('ten_the_loai')->filter()->values();
        $actors = collect(explode(',', (string) $movie->dien_vien))
            ->map(fn($actor) => trim($actor))
            ->filter()
            ->values();
        $nextShowtime = $showtimes->sortBy('thoi_gian_chieu')->first();
        $showtimeGroups = $showtimes->groupBy(
            fn($showtime) => \Carbon\Carbon::parse($showtime->thoi_gian_chieu)->format('Y-m-d'),
        );
        $releaseDate = $movie->ngay_khoi_chieu
            ? \Carbon\Carbon::parse($movie->ngay_khoi_chieu)->format('d/m/Y')
            : 'Đang cập nhật';
        $canBook = in_array(
            $status,
            [\App\Models\SuatChieu::TRANG_THAI_DANG_CHIEU, \App\Models\SuatChieu::TRANG_THAI_SAP_CHIEU],
            true,
        ) && $showtimes->isNotEmpty();
        $statusInfo = match ($status) {
            \App\Models\SuatChieu::TRANG_THAI_DANG_CHIEU => [
                'label' => 'Đang chiếu',
                'class' => 'is-live',
                'icon' => 'fa-circle-play',
            ],
            \App\Models\SuatChieu::TRANG_THAI_SAP_CHIEU => [
                'label' => 'Sắp chiếu',
                'class' => 'is-soon',
                'icon' => 'fa-calendar-check',
            ],
            \App\Models\SuatChieu::TRANG_THAI_SAP_RA_MAT => [
                'label' => 'Sắp ra mắt',
                'class' => 'is-later',
                'icon' => 'fa-star',
            ],
            default => [
                'label' => 'Đã chiếu',
                'class' => 'is-ended',
                'icon' => 'fa-clock-rotate-left',
            ],
        };
        $backUrl = url()->previous() !== url()->current() ? url()->previous() : route('user.phims.index');
        $detailShots = collect([
            ['image' => $posterUrl, 'label' => 'Poster chính'],
            [
                'image' => 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=900&q=80',
                'label' => 'Không gian rạp',
            ],
            [
                'image' => 'https://images.unsplash.com/photo-1524985069026-dd778a71c7b4?auto=format&fit=crop&w=900&q=80',
                'label' => 'Trải nghiệm điện ảnh',
            ],
        ]);
    ?>

    <section class="movie-detail-page">
        <section class="movie-detail-hero">
            <div class="movie-detail-backdrop" style="background-image: url('<?php echo e($posterUrl); ?>');"></div>

            <div class="container-fluid px-5 movie-detail-hero-inner">
                <a href="<?php echo e($backUrl); ?>" class="detail-back-link">
                    <i class="fa-solid fa-arrow-left"></i>
                    Quay lại
                </a>

                <div class="movie-detail-layout">
                    <aside class="detail-poster-card reveal-on-scroll">
                        <div class="detail-poster-frame">
                            <img src="<?php echo e($posterUrl); ?>" alt="<?php echo e($movie->ten_phim); ?>">
                            <span class="detail-age-badge"><?php echo e($movie->gioi_han_tuoi); ?></span>
                        </div>

                        <div class="detail-poster-actions">
                            <?php if($movie->trailer): ?>
                                <a href="<?php echo e($movie->trailer); ?>" target="_blank" rel="noopener noreferrer"
                                    class="detail-action-btn detail-action-btn--ghost">
                                    <i class="fa-solid fa-play"></i>
                                    Xem trailer
                                </a>
                            <?php endif; ?>

                            <?php if($canBook): ?>
                                <a href="<?php echo e(route('dat_ve.chon_ghe', $movie->slug)); ?>"
                                    class="booking-link detail-action-btn detail-action-btn--primary">
                                    <i class="fa-solid fa-ticket"></i>
                                    Đặt vé ngay
                                </a>
                            <?php else: ?>
                                <button type="button" class="detail-action-btn detail-action-btn--disabled" disabled>
                                    <i class="fa-solid fa-ban"></i>
                                    Chưa mở đặt vé
                                </button>
                            <?php endif; ?>
                        </div>
                    </aside>

                    <div class="detail-hero-copy reveal-on-scroll">
                        <span class="detail-status <?php echo e($statusInfo['class']); ?>">
                            <i class="fa-solid <?php echo e($statusInfo['icon']); ?>"></i>
                            <?php echo e($statusInfo['label']); ?>

                        </span>

                        <h1><?php echo e($movie->ten_phim); ?></h1>

                        <p class="detail-hero-desc">
                            <?php echo e(\Illuminate\Support\Str::limit($movie->mo_ta, 230)); ?>

                        </p>

                        <div class="detail-meta-grid">
                            <span>
                                <i class="fa-solid fa-clock"></i>
                                <?php echo e($movie->thoi_luong); ?> phút
                            </span>
                            <span>
                                <i class="fa-solid fa-earth-asia"></i>
                                <?php echo e(optional($movie->country)->ten_quoc_gia ?? 'Đang cập nhật'); ?>

                            </span>
                            <span>
                                <i class="fa-solid fa-language"></i>
                                <?php echo e($movie->ngon_ngu); ?>

                            </span>
                            <span>
                                <i class="fa-solid fa-tags"></i>
                                <?php echo e($genres->join(', ') ?: 'Điện ảnh'); ?>

                            </span>
                        </div>

                        <?php if($nextShowtime): ?>
                            <div class="detail-next-showtime">
                                <span>
                                    <i class="fa-solid fa-calendar-day"></i>
                                    Suất gần nhất
                                </span>
                                <strong><?php echo e(\Carbon\Carbon::parse($nextShowtime->thoi_gian_chieu)->format('H:i d/m/Y')); ?></strong>
                                <small>
                                    <?php echo e($nextShowtime->rapChieuPhim?->ten_rap ?? 'CineHome'); ?>

                                    · <?php echo e($nextShowtime->phongChieu?->ten_phong ?? 'Phòng chiếu'); ?>

                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <main class="movie-detail-main">
            <div class="container-fluid px-5">
                <section class="detail-tabs-shell reveal-on-scroll" data-detail-tabs>
                    <div class="detail-tabs">
                        <button type="button" class="active" data-detail-tab="overview">
                            <i class="fa-solid fa-film"></i>
                            Tổng quan
                        </button>
                        <button type="button" data-detail-tab="showtimes">
                            <i class="fa-solid fa-calendar-days"></i>
                            Lịch chiếu
                        </button>
                        <button type="button" data-detail-tab="cast">
                            <i class="fa-solid fa-users"></i>
                            Diễn viên
                        </button>
                    </div>

                    <div class="detail-tab-panel active" data-detail-panel="overview">
                        <div class="detail-overview-grid">
                            <div class="detail-story-card">
                                <span class="detail-section-kicker">Nội dung phim</span>
                                <h2><?php echo e($movie->ten_phim); ?></h2>
                                <p><?php echo e($movie->mo_ta); ?></p>

                                <div class="detail-fact-grid">
                                    <div>
                                        <small>Đạo diễn</small>
                                        <strong><?php echo e($movie->dao_dien); ?></strong>
                                    </div>
                                    <div>
                                        <small>Khởi chiếu</small>
                                        <strong><?php echo e($releaseDate); ?></strong>
                                    </div>
                                    <div>
                                        <small>Giới hạn tuổi</small>
                                        <strong><?php echo e($movie->gioi_han_tuoi); ?></strong>
                                    </div>
                                    <div>
                                        <small>Giá vé từ</small>
                                        <strong>
                                            <?php echo e($nextShowtime ? number_format((float) $nextShowtime->gia_ve, 0, ',', '.') . 'đ' : 'Đang cập nhật'); ?>

                                        </strong>
                                    </div>
                                </div>
                            </div>

                            <div class="detail-gallery">
                                <?php $__currentLoopData = $detailShots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <figure class="<?php echo e($loop->first ? 'large' : ''); ?>">
                                        <img src="<?php echo e($shot['image']); ?>" alt="<?php echo e($shot['label']); ?>">
                                        <figcaption><?php echo e($shot['label']); ?></figcaption>
                                    </figure>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>

                    <div class="detail-tab-panel" data-detail-panel="showtimes">
                        <div class="detail-panel-head">
                            <span class="detail-section-kicker">Chọn suất chiếu</span>
                            <h2>Lịch chiếu sắp tới</h2>
                        </div>

                        <?php $__empty_1 = true; $__currentLoopData = $showtimeGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <section class="detail-showtime-day">
                                <div class="detail-showtime-date">
                                    <strong><?php echo e(\Carbon\Carbon::parse($date)->format('d/m/Y')); ?></strong>
                                    <span><?php echo e($items->count()); ?> suất chiếu</span>
                                </div>

                                <div class="detail-showtime-list">
                                    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $showtime): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <a href="<?php echo e(route('dat_ve.chon_ghe', $showtime->id)); ?>"
                                            class="booking-link detail-showtime-card">
                                            <strong><?php echo e(\Carbon\Carbon::parse($showtime->thoi_gian_chieu)->format('H:i')); ?></strong>
                                            <span><?php echo e($showtime->rapChieuPhim?->ten_rap ?? 'CineHome'); ?></span>
                                            <small>
                                                <?php echo e($showtime->phongChieu?->ten_phong ?? 'Phòng chiếu'); ?>

                                                · <?php echo e(number_format((float) $showtime->gia_ve, 0, ',', '.')); ?>đ
                                            </small>
                                            <i class="fa-solid fa-arrow-right"></i>
                                        </a>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </section>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="detail-empty-state">
                                <i class="fa-regular fa-calendar"></i>
                                <strong>Lịch chiếu đang được cập nhật</strong>
                                <span>Quay lại sau để chọn suất chiếu phù hợp nhất.</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="detail-tab-panel" data-detail-panel="cast">
                        <div class="detail-cast-layout">
                            <div class="detail-director-card">
                                <span class="detail-section-kicker">Đạo diễn</span>
                                <div class="detail-person-avatar">
                                    <?php echo e(mb_strtoupper(mb_substr($movie->dao_dien, 0, 1))); ?>

                                </div>
                                <h2><?php echo e($movie->dao_dien); ?></h2>
                                <p>Người dẫn dắt phong cách hình ảnh và nhịp kể của bộ phim.</p>
                            </div>

                            <div class="detail-cast-grid">
                                <?php $__empty_1 = true; $__currentLoopData = $actors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $actor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <article class="detail-cast-card">
                                        <span><?php echo e(mb_strtoupper(mb_substr($actor, 0, 1))); ?></span>
                                        <div>
                                            <strong><?php echo e($actor); ?></strong>
                                            <small>Diễn viên</small>
                                        </div>
                                    </article>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <div class="detail-empty-state">
                                        <i class="fa-solid fa-user"></i>
                                        <strong>Diễn viên đang cập nhật</strong>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </section>

                <?php if(isset($relatedMovies) && $relatedMovies->count()): ?>
                    <section class="booking-section detail-related-section reveal-on-scroll">
                        <div class="booking-section-head">
                            <div>
                                <p>Gợi ý tiếp theo</p>
                                <h2>Phim liên quan</h2>
                            </div>
                            <a href="<?php echo e(route('user.phims.index')); ?>" class="detail-related-link">
                                Xem tất cả
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>

                        <?php echo $__env->make('partials.movie-section', [
                            'movies' => $relatedMovies,
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </section>
                <?php endif; ?>
            </div>
        </main>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\DATN\WD-11-Cinehome-cinema\resources\views/user/phims/show.blade.php ENDPATH**/ ?>