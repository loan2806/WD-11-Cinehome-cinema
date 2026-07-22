<?php $__env->startSection('title', 'Bán vé tại quầy'); ?>
<?php $__env->startSection('page-title', 'Bán vé tại quầy'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $showtimeCollection = collect($showtimes);
        $dates = $showtimeCollection
            ->map(fn ($item) => $item->thoi_gian_chieu?->format('Y-m-d'))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $selectedDate = request('ngay_chieu') ?: $dates->first();
        $keyword = trim((string) request('q', ''));
        $keywordLower = \Illuminate\Support\Str::lower($keyword);

        $dayShowtimes = $showtimeCollection
            ->filter(fn ($item) => $selectedDate && $item->thoi_gian_chieu?->format('Y-m-d') === $selectedDate)
            ->filter(function ($item) use ($keyword, $keywordLower) {
                if ($keyword === '') {
                    return true;
                }

                $haystack = \Illuminate\Support\Str::lower(implode(' ', [
                    $item->phim?->ten_phim,
                    $item->rapChieuPhim?->ten_rap,
                    $item->phongChieu?->ten_phong,
                    $item->trang_thai,
                ]));

                return str_contains($haystack, $keywordLower);
            })
            ->sortBy('thoi_gian_chieu')
            ->values();

        $movieGroups = $dayShowtimes->groupBy('phim_id');
        $selectedCarbon = $selectedDate ? \Carbon\Carbon::parse($selectedDate) : null;
        $nextShowtime = $dayShowtimes->first();
        $roomCount = $dayShowtimes->pluck('phong_chieu_id')->filter()->unique()->count();
        $soldTickets = $dayShowtimes->sum(fn ($item) => (int) ($item->sold_tickets_count ?? 0));

        $statusLabels = [
            'sap_ra_mat' => 'Sắp ra mắt',
            'sap_chieu' => 'Sắp chiếu',
            'dang_chieu' => 'Đang chiếu',
            'da_chieu' => 'Đã chiếu',
            'huy' => 'Đã hủy',
        ];
    ?>

    <div class="counter-sale-page">
        <?php echo $__env->make('admin.partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <section class="counter-sale-hero">
            <div class="counter-sale-hero-copy">
                <span class="counter-sale-kicker">
                    <i class="fa-solid fa-ticket"></i>
                    Quầy vé CineHome
                </span>
                <h1>Bán vé tại quầy</h1>
                <p>
                    Chọn ngày, tìm phim hoặc phòng chiếu, sau đó vào suất chiếu để chọn ghế, đồ ăn và thanh toán cho khách.
                    Giao diện này ưu tiên tốc độ thao tác trong ca vận hành.
                </p>

                <div class="counter-sale-hero-meta">
                    <span><i class="fa-solid fa-calendar-day"></i> <?php echo e($selectedCarbon?->format('d/m/Y') ?? 'Chưa có lịch'); ?></span>
                    <span><i class="fa-solid fa-clock"></i> Suất gần nhất: <?php echo e($nextShowtime?->thoi_gian_chieu?->format('H:i') ?? '--:--'); ?></span>
                    <span><i class="fa-solid fa-chair"></i> <?php echo e(number_format($soldTickets)); ?> vé đã bán</span>
                </div>
            </div>

            <div class="counter-sale-hero-actions">
                <a href="<?php echo e(route('staff.lich-su-ve.index')); ?>" class="movie-action-btn is-soft">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Lịch sử vé
                </a>
                <a href="<?php echo e(route('staff.ban-ve.index')); ?>" class="movie-action-btn is-ghost">
                    <i class="fa-solid fa-rotate-left"></i>
                    Làm mới
                </a>
            </div>
        </section>

        <section class="counter-sale-stats" aria-label="Tổng quan bán vé tại quầy">
            <article>
                <span class="counter-sale-stat-icon is-showtime"><i class="fa-solid fa-calendar-check"></i></span>
                <div>
                    <small>Suất trong ngày</small>
                    <strong><?php echo e(number_format($dayShowtimes->count())); ?></strong>
                </div>
            </article>
            <article>
                <span class="counter-sale-stat-icon is-movie"><i class="fa-solid fa-film"></i></span>
                <div>
                    <small>Phim đang bán</small>
                    <strong><?php echo e(number_format($movieGroups->count())); ?></strong>
                </div>
            </article>
            <article>
                <span class="counter-sale-stat-icon is-room"><i class="fa-solid fa-door-open"></i></span>
                <div>
                    <small>Phòng chiếu</small>
                    <strong><?php echo e(number_format($roomCount)); ?></strong>
                </div>
            </article>
            <article>
                <span class="counter-sale-stat-icon is-ticket"><i class="fa-solid fa-ticket-simple"></i></span>
                <div>
                    <small>Vé đã bán</small>
                    <strong><?php echo e(number_format($soldTickets)); ?></strong>
                </div>
            </article>
        </section>

        <section class="counter-sale-filter">
            <div>
                <span class="counter-sale-kicker">
                    <i class="fa-solid fa-calendar-days"></i>
                    Chọn ngày xem
                </span>
                <div class="counter-date-list">
                    <?php $__empty_1 = true; $__currentLoopData = $dates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $carbonDate = \Carbon\Carbon::parse($date);
                            $dateShowtimeCount = $showtimeCollection
                                ->filter(fn ($item) => $item->thoi_gian_chieu?->format('Y-m-d') === $date)
                                ->count();
                        ?>

                        <a
                            href="<?php echo e(route('staff.ban-ve.index', array_filter(['ngay_chieu' => $date, 'q' => $keyword ?: null]))); ?>"
                            class="counter-date-item <?php echo e($selectedDate === $date ? 'is-active' : ''); ?>"
                        >
                            <span><?php echo e($carbonDate->translatedFormat('D')); ?></span>
                            <strong><?php echo e($carbonDate->format('d')); ?></strong>
                            <small><?php echo e($carbonDate->format('m/Y')); ?></small>
                            <em><?php echo e($dateShowtimeCount); ?> suất</em>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="counter-date-empty">Chưa có ngày chiếu sắp tới.</div>
                    <?php endif; ?>
                </div>
            </div>

            <form method="GET" action="<?php echo e(route('staff.ban-ve.index')); ?>" class="counter-sale-search">
                <input type="hidden" name="ngay_chieu" value="<?php echo e($selectedDate); ?>">
                <label>
                    <span>Tìm nhanh</span>
                    <div>
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="q" value="<?php echo e($keyword); ?>" placeholder="Tên phim, rạp hoặc phòng chiếu...">
                    </div>
                </label>
                <button type="submit" class="movie-action-btn is-primary">
                    <i class="fa-solid fa-filter"></i>
                    Lọc
                </button>
            </form>
        </section>

        <section class="counter-movie-section">
            <div class="counter-section-head">
                <div>
                    <span class="counter-sale-kicker">
                        <i class="fa-solid fa-clapperboard"></i>
                        Suất chiếu khả dụng
                    </span>
                    <h2><?php echo e($selectedCarbon ? 'Lịch ngày ' . $selectedCarbon->format('d/m/Y') : 'Chưa có lịch chiếu'); ?></h2>
                </div>
                <span><?php echo e(number_format($dayShowtimes->count())); ?> suất</span>
            </div>

            <div class="counter-movie-list">
                <?php $__empty_1 = true; $__currentLoopData = $movieGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movieId => $movieShowtimes): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $movie = $movieShowtimes->first()->phim;
                        $poster = $movie?->poster
                            ? asset('storage/movies/' . $movie->poster)
                            : asset('images/no-poster.jpg');
                    ?>

                    <article class="counter-movie-card">
                        <div class="counter-movie-poster">
                            <img src="<?php echo e($poster); ?>" alt="<?php echo e($movie?->ten_phim ?? 'Poster phim'); ?>">
                        </div>

                        <div class="counter-movie-body">
                            <div class="counter-movie-info">
                                <div>
                                    <h3><?php echo e($movie?->ten_phim ?? 'Phim chưa cập nhật'); ?></h3>
                                    <p>
                                        <i class="fa-solid fa-location-dot"></i>
                                        <?php echo e($movieShowtimes->first()->rapChieuPhim?->ten_rap ?? 'CineHome'); ?>

                                    </p>
                                </div>

                                <div class="counter-movie-tags">
                                    <?php if($movie?->do_tuoi): ?>
                                        <span class="is-age"><?php echo e($movie->do_tuoi); ?></span>
                                    <?php endif; ?>
                                    <span>2D</span>
                                    <span><?php echo e($movieShowtimes->count()); ?> suất</span>
                                </div>
                            </div>

                            <div class="counter-showtime-grid">
                                <?php $__currentLoopData = $movieShowtimes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $showtime): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $statusClass = match ($showtime->trang_thai) {
                                            'dang_chieu' => 'is-live',
                                            'huy' => 'is-cancelled',
                                            default => 'is-upcoming',
                                        };
                                    ?>

                                    <a href="<?php echo e(route('staff.ban-ve.show', $showtime->id)); ?>" class="counter-showtime-card">
                                        <strong><?php echo e($showtime->thoi_gian_chieu?->format('H:i') ?? '--:--'); ?></strong>
                                        <span>
                                            <i class="fa-solid fa-door-open"></i>
                                            <?php echo e($showtime->phongChieu?->ten_phong ?? 'Phòng chiếu'); ?>

                                        </span>
                                        <span>
                                            <i class="fa-solid fa-ticket-simple"></i>
                                            <?php echo e(number_format((int) ($showtime->sold_tickets_count ?? 0))); ?> vé
                                        </span>
                                        <em class="<?php echo e($statusClass); ?>">
                                            <?php echo e($statusLabels[$showtime->trang_thai] ?? 'Sắp chiếu'); ?>

                                        </em>
                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="counter-sale-empty">
                        <span><i class="fa-solid fa-calendar-xmark"></i></span>
                        <h3>Không có suất chiếu phù hợp</h3>
                        <p>Thử chọn ngày khác hoặc bỏ từ khóa tìm kiếm để xem toàn bộ lịch bán vé.</p>
                        <a href="<?php echo e(route('staff.ban-ve.index')); ?>" class="movie-action-btn is-primary">
                            <i class="fa-solid fa-rotate-left"></i>
                            Xem tất cả
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        localStorage.removeItem("staff_food_cart");
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\laragon\www\Cinema\WD-11-Cinehome-cinema\resources\views/staff/ban-ve/index.blade.php ENDPATH**/ ?>