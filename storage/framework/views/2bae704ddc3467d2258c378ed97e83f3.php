<?php $__env->startSection('title', 'CineHome - Đặt vé xem phim'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $heroMovies = $bannerMovies
            ->merge($comingSoonMovies)
            ->merge($nowShowingMovies)
            ->merge($comingLaterMovies)
            ->unique('id')
            ->take(5);

        $nowShowingRail = $nowShowingMovies->isNotEmpty()
            ? $nowShowingMovies->take(12)
            : $heroMovies->merge($comingSoonMovies)->unique('id')->take(12);
        $comingSoonRail = $comingSoonMovies->merge($comingLaterMovies)->unique('id')->take(12);
        $visualMovies = $heroMovies
            ->merge($nowShowingRail)
            ->merge($comingSoonRail)
            ->unique('id')
            ->take(10);
        $fallbackVisualMovies = collect([
            [
                'title' => 'Bom tấn hành động',
                'image' => 'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?auto=format&fit=crop&w=700&q=80',
            ],
            [
                'title' => 'Đêm phim cảm xúc',
                'image' => 'https://images.unsplash.com/photo-1478720568477-152d9b164e26?auto=format&fit=crop&w=700&q=80',
            ],
            [
                'title' => 'Suất chiếu đặc biệt',
                'image' => 'https://images.unsplash.com/photo-1485846234645-a62644f84728?auto=format&fit=crop&w=700&q=80',
            ],
            [
                'title' => 'Rạp phim cuối tuần',
                'image' => 'https://images.unsplash.com/photo-1535016120720-40c646be5580?auto=format&fit=crop&w=700&q=80',
            ],
            [
                'title' => 'Màn ảnh lớn',
                'image' => 'https://images.unsplash.com/photo-1505686994434-e3cc5abf1330?auto=format&fit=crop&w=700&q=80',
            ],
            [
                'title' => 'Không khí điện ảnh',
                'image' => 'https://images.unsplash.com/photo-1524985069026-dd778a71c7b4?auto=format&fit=crop&w=700&q=80',
            ],
            [
                'title' => 'Ghế ngồi êm ái',
                'image' => 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=700&q=80',
            ],
            [
                'title' => 'Trước giờ chiếu',
                'image' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=700&q=80',
            ],
        ]);
        $cinemaShots = collect([
            [
                'title' => 'Phòng chiếu cao cấp',
                'image' => 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'title' => 'Khoảnh khắc trước giờ chiếu',
                'image' => 'https://images.unsplash.com/photo-1524985069026-dd778a71c7b4?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'title' => 'Màn ảnh lớn',
                'image' => 'https://images.unsplash.com/photo-1505686994434-e3cc5abf1330?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'title' => 'Đồ ăn rạp phim',
                'image' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=900&q=80',
            ],
        ]);
    ?>

    <section class="cinema-home">
        <section class="booking-hero hero-slider" data-home-slider>
            <?php $__empty_1 = true; $__currentLoopData = $heroMovies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movie): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $nextShowtime = $movie->showtimes
                        ->filter(
                            fn($showtime) => $showtime->thoi_gian_chieu &&
                                \Carbon\Carbon::parse($showtime->thoi_gian_chieu)->gte(now()),
                        )
                        ->sortBy('thoi_gian_chieu')
                        ->first();

                    $bookingUrl = $nextShowtime
                        ? route('dat_ve.chon_ghe', $movie)
                        : route('user.movies.show', $movie->slug);
                ?>

                <article class="hero-slide booking-hero-slide <?php echo e($loop->first ? 'active' : ''); ?>"
                    data-slide-index="<?php echo e($loop->index); ?>"
                    style="background-image: url('<?php echo e(asset('storage/movies/' . $movie->poster)); ?>');">
                    <div class="container-fluid px-5 booking-hero-content hero-content">
                        <div class="booking-hero-copy hero-info">
                            <div class="booking-eyebrow">
                                <i class="fa-solid fa-bolt"></i>
                                Đặt vé nhanh tại CineHome
                            </div>

                            <h1 class="booking-hero-title hero-title">
                                <?php echo e($movie->ten_phim); ?>

                            </h1>

                            <p class="booking-hero-desc hero-desc">
                                <?php echo e(\Illuminate\Support\Str::limit($movie->mo_ta, 190)); ?>

                            </p>

                            <div class="booking-hero-meta hero-meta">
                                <span>
                                    <i class="fa-solid fa-film"></i>
                                    <?php echo e($movie->genres->pluck('ten_the_loai')->take(2)->join(', ') ?: 'Điện ảnh'); ?>

                                </span>

                                <span>
                                    <i class="fa-solid fa-clock"></i>
                                    <?php echo e($movie->thoi_luong); ?> phút
                                </span>

                                <span>
                                    <i class="fa-solid fa-user-shield"></i>
                                    <?php echo e($movie->gioi_han_tuoi); ?>

                                </span>
                            </div>

                            <div class="booking-showtime-chip">
                                <i class="fa-solid fa-calendar-check"></i>
                                <?php if($nextShowtime): ?>
                                    Suất gần nhất: <?php echo e(\Carbon\Carbon::parse($nextShowtime->thoi_gian_chieu)->format('H:i d/m')); ?>

                                <?php else: ?>
                                    Suất chiếu đang cập nhật
                                <?php endif; ?>
                            </div>

                            <div class="booking-hero-actions hero-buttons">
                                <a href="<?php echo e($bookingUrl); ?>" class="btn-book booking-primary-btn">
                                    <i class="fa-solid fa-ticket"></i>
                                    Đặt vé ngay
                                </a>

                                <a href="<?php echo e(route('user.movies.show', $movie->slug)); ?>" class="btn-trailer booking-ghost-btn">
                                    <i class="fa-solid fa-circle-info"></i>
                                    Chi tiết phim
                                </a>
                            </div>

                            <div class="booking-hero-stats">
                                <div>
                                    <strong><?php echo e($nowShowingMovies->count()); ?></strong>
                                    <span>Phim đang chiếu</span>
                                </div>
                                <div>
                                    <strong><?php echo e($comingSoonMovies->count() + $comingLaterMovies->count()); ?></strong>
                                    <span>Phim sắp chiếu</span>
                                </div>
                                <div>
                                    <strong>3</strong>
                                    <span>Bước nhận vé</span>
                                </div>
                            </div>
                        </div>

                        <div class="booking-hero-poster reveal-on-scroll">
                            <img src="<?php echo e(asset('storage/movies/' . $movie->poster)); ?>" alt="<?php echo e($movie->ten_phim); ?>">
                            <div class="poster-ticket">
                                <i class="fa-solid fa-ticket"></i>
                                Vé điện tử
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <article class="hero-slide booking-hero-slide active empty-hero"
                    style="background-image: url('https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=1800&q=80');">
                    <div class="container-fluid px-5 booking-hero-content hero-content">
                        <div class="booking-hero-copy hero-info">
                            <div class="booking-eyebrow">
                                <i class="fa-solid fa-bolt"></i>
                                CineHome Cinema
                            </div>
                            <h1 class="booking-hero-title hero-title">Đặt vé xem phim dễ dàng</h1>
                            <p class="booking-hero-desc hero-desc">
                                Khám phá lịch chiếu mới nhất, chọn ghế yêu thích và nhận vé điện tử chỉ trong vài bước.
                            </p>
                            <div class="booking-hero-actions hero-buttons">
                                <a href="<?php echo e(route('dat_ve.chon_phim')); ?>" class="btn-book booking-primary-btn">
                                    <i class="fa-solid fa-ticket"></i>
                                    Đặt vé ngay
                                </a>
                            </div>

                            <div class="booking-hero-stats">
                                <div>
                                    <strong>3</strong>
                                    <span>Bước đặt vé</span>
                                </div>
                                <div>
                                    <strong>QR</strong>
                                    <span>Vé điện tử</span>
                                </div>
                                <div>
                                    <strong>24/7</strong>
                                    <span>Tự chọn ghế</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endif; ?>

            <?php if($heroMovies->count() > 1): ?>
                <div class="booking-hero-controls">
                    <button type="button" class="hero-control" data-slide-prev aria-label="Phim trước">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <button type="button" class="hero-control" data-slide-next aria-label="Phim tiếp theo">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>

                <div class="booking-hero-dots">
                    <?php $__currentLoopData = $heroMovies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movie): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button type="button" class="<?php echo e($loop->first ? 'active' : ''); ?>"
                            data-slide-target="<?php echo e($loop->index); ?>" aria-label="Chọn <?php echo e($movie->ten_phim); ?>">
                            <span></span>
                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </section>

        <main class="main-section booking-main">
            <div class="container-fluid px-5">
                <section class="quick-booking-panel reveal-on-scroll">
                    <a href="<?php echo e(route('dat_ve.chon_phim')); ?>" class="quick-booking-step">
                        <span>01</span>
                        <strong>Chọn phim</strong>
                        <small>Phim hot và suất chiếu mới nhất</small>
                        <i class="fa-solid fa-film"></i>
                    </a>

                    <a href="<?php echo e(route('dat_ve.chon_phim')); ?>" class="quick-booking-step">
                        <span>02</span>
                        <strong>Chọn suất chiếu</strong>
                        <small>Lọc theo ngày, giờ và rạp gần bạn</small>
                        <i class="fa-solid fa-calendar-days"></i>
                    </a>

                    <a href="<?php echo e(route('user.cinemas.index')); ?>" class="quick-booking-step">
                        <span>03</span>
                        <strong>Nhận vé điện tử</strong>
                        <small>Thanh toán nhanh, vào rạp tiện lợi</small>
                        <i class="fa-solid fa-qrcode"></i>
                    </a>
                </section>

                <section class="booking-poster-wall reveal-on-scroll">
                    <div class="poster-wall-copy">
                        <span>
                            <i class="fa-solid fa-images"></i>
                            Bộ sưu tập nổi bật
                        </span>
                        <h2>Chọn phim bằng cảm xúc từ những khung hình đầu tiên</h2>
                        <p>
                            Trang chủ được phủ nhiều poster phim hơn để người dùng lướt nhanh, nhìn thấy phim nổi bật
                            và đi thẳng đến chi tiết hoặc đặt vé.
                        </p>
                        <a href="<?php echo e(route('user.phims.index')); ?>">
                            Khám phá phim
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>

                    <div class="poster-wall-grid" aria-label="Poster phim nổi bật">
                        <?php $__currentLoopData = $visualMovies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movie): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e(route('user.movies.show', $movie->slug)); ?>"
                                class="poster-wall-card <?php echo e($loop->first ? 'large' : ''); ?>">
                                <img src="<?php echo e(asset('storage/movies/' . $movie->poster)); ?>"
                                    alt="<?php echo e($movie->ten_phim); ?>">
                                <span><?php echo e($movie->ten_phim); ?></span>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <?php $__currentLoopData = $fallbackVisualMovies->take(max(0, 9 - $visualMovies->count())); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $poster): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e(route('user.phims.index')); ?>"
                                class="poster-wall-card <?php echo e($visualMovies->isEmpty() && $loop->first ? 'large' : ''); ?>">
                                <img src="<?php echo e($poster['image']); ?>" alt="<?php echo e($poster['title']); ?>">
                                <span><?php echo e($poster['title']); ?></span>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </section>

                <section class="cinema-experience-board reveal-on-scroll">
                    <div class="experience-board-head">
                        <span>
                            <i class="fa-solid fa-clapperboard"></i>
                            Không gian CineHome
                        </span>
                        <h2>Trải nghiệm rạp phim được đưa lên ngay trang chủ</h2>
                    </div>

                    <div class="experience-shot-grid">
                        <?php $__currentLoopData = $cinemaShots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <figure class="experience-shot <?php echo e($loop->first ? 'wide' : ''); ?>">
                                <img src="<?php echo e($shot['image']); ?>" alt="<?php echo e($shot['title']); ?>">
                                <figcaption><?php echo e($shot['title']); ?></figcaption>
                            </figure>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </section>

                <section class="booking-benefits reveal-on-scroll">
                    <div class="booking-benefit-card">
                        <span>
                            <i class="fa-solid fa-couch"></i>
                        </span>
                        <div>
                            <h3>Chọn ghế trực quan</h3>
                            <p>Xem sơ đồ ghế rõ ràng, chọn đúng vị trí yêu thích trước khi thanh toán.</p>
                        </div>
                    </div>

                    <div class="booking-benefit-card">
                        <span>
                            <i class="fa-solid fa-shield-halved"></i>
                        </span>
                        <div>
                            <h3>Giữ ghế tạm thời</h3>
                            <p>Ghế được khóa trong quá trình đặt vé để bạn thao tác yên tâm hơn.</p>
                        </div>
                    </div>

                    <div class="booking-benefit-card">
                        <span>
                            <i class="fa-solid fa-mobile-screen-button"></i>
                        </span>
                        <div>
                            <h3>Vé điện tử tiện lợi</h3>
                            <p>Nhận mã vé sau thanh toán và xuất trình nhanh khi đến rạp.</p>
                        </div>
                    </div>
                </section>

                <section class="booking-section reveal-on-scroll" data-rail-section>
                    <div class="booking-section-head">
                        <div>
                            <p>Đang chiếu</p>
                            <h2>Phim đang chiếu</h2>
                        </div>
                        <div class="booking-section-actions">
                            <a href="<?php echo e(route('dat_ve.chon_phim')); ?>">
                                Đặt vé ngay
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                            <div class="rail-controls">
                                <button type="button" data-rail-prev aria-label="Cuộn phim trước">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </button>
                                <button type="button" data-rail-next aria-label="Cuộn phim tiếp theo">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="booking-movie-rail">
                        <?php $__empty_1 = true; $__currentLoopData = $nowShowingRail; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movie): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $nextShowtime = $movie->showtimes
                                    ->filter(
                                        fn($showtime) => $showtime->thoi_gian_chieu &&
                                            \Carbon\Carbon::parse($showtime->thoi_gian_chieu)->gte(now()),
                                    )
                                    ->sortBy('thoi_gian_chieu')
                                    ->first();

                                $bookingUrl = $nextShowtime
                                    ? route('dat_ve.chon_ghe', $movie)
                                    : route('user.movies.show', $movie->slug);
                            ?>

                            <article class="booking-movie-card">
                                <a href="<?php echo e(route('user.movies.show', $movie->slug)); ?>" class="booking-movie-poster">
                                    <img src="<?php echo e(asset('storage/movies/' . $movie->poster)); ?>"
                                        alt="<?php echo e($movie->ten_phim); ?>">
                                    <span class="movie-age-badge"><?php echo e($movie->gioi_han_tuoi); ?></span>
                                    <span class="movie-play-overlay">
                                        <i class="fa-solid fa-play"></i>
                                    </span>
                                </a>

                                <div class="booking-movie-body">
                                    <h3><?php echo e($movie->ten_phim); ?></h3>
                                    <p>
                                        <i class="fa-solid fa-clock"></i>
                                        <?php echo e($movie->thoi_luong); ?> phút
                                    </p>
                                    <p>
                                        <i class="fa-solid fa-tags"></i>
                                        <?php echo e($movie->genres->pluck('ten_the_loai')->take(2)->join(', ') ?: 'Điện ảnh'); ?>

                                    </p>

                                    <div class="booking-card-actions">
                                        <a href="<?php echo e($bookingUrl); ?>" class="card-book-btn">
                                            <i class="fa-solid fa-ticket"></i>
                                            Đặt vé
                                        </a>
                                        <a href="<?php echo e(route('user.movies.show', $movie->slug)); ?>" class="card-detail-btn">
                                            Chi tiết
                                        </a>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="booking-empty-state">
                                <i class="fa-solid fa-film"></i>
                                Chưa có phim đang chiếu.
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="booking-section reveal-on-scroll" data-rail-section>
                    <div class="booking-section-head">
                        <div>
                            <p>Sắp chiếu</p>
                            <h2>Sắp chiếu tại CineHome</h2>
                        </div>
                        <div class="booking-section-actions">
                            <a href="<?php echo e(route('user.phims.index')); ?>">
                                Xem tất cả
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                            <div class="rail-controls">
                                <button type="button" data-rail-prev aria-label="Cuộn phim trước">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </button>
                                <button type="button" data-rail-next aria-label="Cuộn phim tiếp theo">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="booking-movie-rail compact">
                        <?php $__empty_1 = true; $__currentLoopData = $comingSoonRail; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movie): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <article class="booking-movie-card compact">
                                <a href="<?php echo e(route('user.movies.show', $movie->slug)); ?>" class="booking-movie-poster">
                                    <img src="<?php echo e(asset('storage/movies/' . $movie->poster)); ?>"
                                        alt="<?php echo e($movie->ten_phim); ?>">
                                    <span class="movie-age-badge"><?php echo e($movie->gioi_han_tuoi); ?></span>
                                    <span class="movie-play-overlay">
                                        <i class="fa-solid fa-play"></i>
                                    </span>
                                </a>

                                <div class="booking-movie-body">
                                    <h3><?php echo e($movie->ten_phim); ?></h3>
                                    <p>
                                        <i class="fa-solid fa-earth-asia"></i>
                                        <?php echo e($movie->country?->ten_quoc_gia ?? 'Đang cập nhật'); ?>

                                    </p>
                                    <div class="booking-card-actions">
                                        <a href="<?php echo e(route('user.movies.show', $movie->slug)); ?>" class="card-book-btn">
                                            Quan tâm
                                        </a>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="booking-empty-state">
                                <i class="fa-regular fa-calendar"></i>
                                Chưa có phim sắp chiếu.
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="booking-promo-strip reveal-on-scroll">
                    <div class="promo-copy">
                        <span>
                            <i class="fa-solid fa-crown"></i>
                            Thành viên CineHome
                        </span>
                        <h2>Đặt vé hôm nay, tích điểm cho lần xem tiếp theo</h2>
                        <p>Nhận ưu đãi voucher, quản lý vé điện tử và theo dõi lịch sử đặt vé trong tài khoản của bạn.</p>
                    </div>

                    <div class="promo-actions">
                        <a href="<?php echo e(route('user.thanh-vien.index')); ?>" class="promo-primary">
                            Xem quyền lợi
                        </a>
                        <a href="<?php echo e(route('user.voucher.index')); ?>" class="promo-secondary">
                            Đổi voucher
                        </a>
                    </div>
                </section>
            </div>
        </main>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\laragon\www\WD-11-Cinehome-cinema\resources\views/user/home.blade.php ENDPATH**/ ?>