<?php $__env->startSection('page-title', 'Chi tiết phim'); ?>
<?php $__env->startSection('page-subtitle', 'Xem thông tin phim, poster, trailer và lịch chiếu liên quan'); ?>

<?php
    $posterUrl = function (?string $poster): string {
        if (empty($poster)) {
            return asset('assets/images/LOGO copy.png');
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

    $getTrailerEmbed = function (?string $url): ?string {
        if (blank($url)) {
            return null;
        }

        $parts = parse_url($url);
        $host = isset($parts['host']) ? str_replace('www.', '', $parts['host']) : '';
        $path = $parts['path'] ?? '';

        if ($host === 'youtube.com') {
            parse_str($parts['query'] ?? '', $query);

            if (!empty($query['v'])) {
                return 'https://www.youtube.com/embed/' . $query['v'];
            }

            if (str_contains($path, '/shorts/')) {
                $id = explode('/', trim(str_replace('/shorts/', '', $path), '/'))[0] ?? null;
                return $id ? 'https://www.youtube.com/embed/' . $id : null;
            }
        }

        if ($host === 'youtu.be') {
            $id = trim($path, '/');
            return $id ? 'https://www.youtube.com/embed/' . $id : null;
        }

        return null;
    };

    $poster = $posterUrl($phim->poster);
    $trailerEmbed = $getTrailerEmbed($phim->trailer);
    $showtimes = $phim->showtimes->sortByDesc('thoi_gian_chieu');
    $upcomingShowtimes = $phim->showtimes
        ->filter(fn ($showtime) => $showtime->thoi_gian_chieu && $showtime->thoi_gian_chieu->isFuture())
        ->sortBy('thoi_gian_chieu');
    $latestShowtimes = $showtimes->take(6);
    $statusLabels = [
        'sap_ra_mat' => 'Sắp ra mắt',
        'sap_chieu' => 'Sắp chiếu',
        'dang_chieu' => 'Đang chiếu',
        'da_chieu' => 'Đã chiếu',
        'huy' => 'Hủy',
        'dung_nhan_ve' => 'Dừng nhận vé',
    ];
?>

<?php $__env->startSection('content'); ?>
<div class="movie-detail-page">
    <?php echo $__env->make('admin.partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <section class="movie-detail-hero">
        <div class="movie-detail-backdrop" style="background-image: url('<?php echo e($poster); ?>');"></div>
        <div class="movie-detail-hero-content">
            <div class="movie-detail-poster">
                <img src="<?php echo e($poster); ?>" alt="<?php echo e($phim->ten_phim); ?>">
            </div>

            <div class="movie-detail-copy">
                <span class="movie-kicker">
                    <i class="fa-solid fa-film"></i>
                    Hồ sơ phim #<?php echo e($phim->id); ?>

                </span>
                <h2><?php echo e($phim->ten_phim); ?></h2>

                <div class="movie-detail-tags">
                    <?php if($phim->gioi_han_tuoi): ?>
                        <span class="is-age"><?php echo e($phim->gioi_han_tuoi); ?></span>
                    <?php endif; ?>
                    <span><i class="fa-regular fa-clock"></i><?php echo e((int) $phim->thoi_luong); ?> phút</span>
                    <span><i class="fa-solid fa-language"></i><?php echo e($phim->ngon_ngu ?: 'Chưa cập nhật'); ?></span>
                    <span><i class="fa-solid fa-location-dot"></i><?php echo e($phim->country?->ten_quoc_gia ?? 'Chưa cập nhật'); ?></span>
                </div>

                <p>
                    <?php echo e(\Illuminate\Support\Str::limit($phim->mo_ta ?: 'Chưa có mô tả cho phim này.', 230)); ?>

                </p>

                <div class="movie-detail-actions">
                    <a href="<?php echo e(route('admin.phims.edit', $phim)); ?>" class="movie-action-btn is-primary">
                        <i class="fa-solid fa-pen"></i>
                        Sửa phim
                    </a>
                    <a href="<?php echo e(route('admin.suat-chieus.index', ['phim_id' => $phim->id])); ?>" class="movie-action-btn is-soft">
                        <i class="fa-solid fa-calendar-days"></i>
                        Xem suất chiếu
                    </a>
                    <a href="<?php echo e(route('admin.phims.index')); ?>" class="movie-action-btn is-ghost">
                        <i class="fa-solid fa-arrow-left"></i>
                        Quay lại
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="movie-detail-stat-grid">
        <article>
            <span><i class="fa-solid fa-tags"></i></span>
            <div>
                <small>Thể loại</small>
                <strong><?php echo e($phim->genres->count()); ?></strong>
            </div>
        </article>

        <article>
            <span><i class="fa-solid fa-calendar-check"></i></span>
            <div>
                <small>Tổng suất chiếu</small>
                <strong><?php echo e($phim->showtimes->count()); ?></strong>
            </div>
        </article>

        <article>
            <span><i class="fa-solid fa-hourglass-half"></i></span>
            <div>
                <small>Suất sắp tới</small>
                <strong><?php echo e($upcomingShowtimes->count()); ?></strong>
            </div>
        </article>

        <article>
            <span><i class="fa-solid fa-video"></i></span>
            <div>
                <small>Trailer</small>
                <strong><?php echo e($trailerEmbed ? 'Có' : 'Chưa'); ?></strong>
            </div>
        </article>
    </section>

    <div class="movie-detail-layout">
        <main class="movie-detail-main">
            <section class="movie-detail-panel">
                <div class="movie-detail-panel-head">
                    <span><i class="fa-solid fa-circle-info"></i></span>
                    <div>
                        <h3>Thông tin phim</h3>
                        <p>Các dữ liệu chính đang được dùng để hiển thị và bán vé.</p>
                    </div>
                </div>

                <div class="movie-info-grid">
                    <div>
                        <small>Tên phim</small>
                        <strong><?php echo e($phim->ten_phim); ?></strong>
                    </div>
                    <div>
                        <small>Quốc gia</small>
                        <strong><?php echo e($phim->country?->ten_quoc_gia ?? 'Chưa cập nhật'); ?></strong>
                    </div>
                    <div>
                        <small>Đạo diễn</small>
                        <strong><?php echo e($phim->dao_dien ?: 'Chưa cập nhật'); ?></strong>
                    </div>
                    <div>
                        <small>Diễn viên</small>
                        <strong><?php echo e($phim->dien_vien ?: 'Chưa cập nhật'); ?></strong>
                    </div>
                    <div>
                        <small>Ngôn ngữ</small>
                        <strong><?php echo e($phim->ngon_ngu ?: 'Chưa cập nhật'); ?></strong>
                    </div>
                    <div>
                        <small>Giới hạn tuổi</small>
                        <strong><?php echo e($phim->gioi_han_tuoi ?: 'Chưa cập nhật'); ?></strong>
                    </div>
                    <div>
                        <small>Thời lượng</small>
                        <strong><?php echo e((int) $phim->thoi_luong); ?> phút</strong>
                    </div>
                    <div>
                        <small>Slug</small>
                        <strong><?php echo e($phim->slug); ?></strong>
                    </div>
                </div>
            </section>

            <section class="movie-detail-panel">
                <div class="movie-detail-panel-head">
                    <span><i class="fa-solid fa-align-left"></i></span>
                    <div>
                        <h3>Mô tả phim</h3>
                        <p>Nội dung giới thiệu đang hiển thị trên trang chi tiết người dùng.</p>
                    </div>
                </div>

                <div class="movie-description-box">
                    <?php echo e($phim->mo_ta ?: 'Chưa có mô tả cho phim này.'); ?>

                </div>
            </section>

            <section class="movie-detail-panel">
                <div class="movie-detail-panel-head">
                    <span><i class="fa-solid fa-calendar-days"></i></span>
                    <div>
                        <h3>Suất chiếu gần đây</h3>
                        <p>Theo dõi nhanh lịch chiếu đã gắn với phim này.</p>
                    </div>
                    <a href="<?php echo e(route('admin.suat-chieus.index', ['phim_id' => $phim->id])); ?>">
                        Xem tất cả <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <?php if($latestShowtimes->isNotEmpty()): ?>
                    <div class="movie-showtime-list">
                        <?php $__currentLoopData = $latestShowtimes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $showtime): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $status = $showtime->trang_thai;
                                $statusClass = match ($status) {
                                    'dang_chieu' => 'is-live',
                                    'sap_chieu', 'sap_ra_mat' => 'is-upcoming',
                                    'huy' => 'is-cancelled',
                                    default => 'is-muted',
                                };
                            ?>
                            <article class="movie-showtime-item">
                                <div class="movie-showtime-date">
                                    <strong><?php echo e($showtime->thoi_gian_chieu?->format('d') ?? '--'); ?></strong>
                                    <span><?php echo e($showtime->thoi_gian_chieu?->format('m/Y') ?? '--/----'); ?></span>
                                </div>
                                <div class="movie-showtime-info">
                                    <strong>
                                        <?php echo e($showtime->thoi_gian_chieu?->format('H:i') ?? '--:--'); ?>

                                        <?php if($showtime->thoi_gian_ket_thuc): ?>
                                            - <?php echo e($showtime->thoi_gian_ket_thuc->format('H:i')); ?>

                                        <?php endif; ?>
                                    </strong>
                                    <span>
                                        <?php echo e($showtime->rapChieuPhim?->ten_rap ?? 'Chưa cập nhật rạp'); ?>

                                        •
                                        <?php echo e($showtime->phongChieu?->ten_phong ?? 'Chưa cập nhật phòng'); ?>

                                    </span>
                                </div>
                                <div class="movie-showtime-meta">
                                    <b><?php echo e(number_format((float) $showtime->gia_ve)); ?>đ</b>
                                    <em class="<?php echo e($statusClass); ?>"><?php echo e($statusLabels[$status] ?? $status); ?></em>
                                </div>
                            </article>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <div class="movie-detail-empty">
                        <i class="fa-regular fa-calendar-xmark"></i>
                        <strong>Chưa có suất chiếu</strong>
                        <span>Hãy tạo lịch chiếu để phim có thể bán vé trên hệ thống.</span>
                        <a href="<?php echo e(route('admin.suat-chieus.create', ['phim_id' => $phim->id])); ?>" class="movie-action-btn is-primary">
                            <i class="fa-solid fa-calendar-plus"></i>
                            Tạo suất chiếu
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </section>
        </main>

        <aside class="movie-detail-side">
            <section class="movie-detail-panel movie-detail-media">
                <div class="movie-detail-panel-head">
                    <span><i class="fa-solid fa-image"></i></span>
                    <div>
                        <h3>Poster</h3>
                        <p>Ảnh dọc dùng trong danh sách phim và đặt vé.</p>
                    </div>
                </div>

                <img src="<?php echo e($poster); ?>" alt="<?php echo e($phim->ten_phim); ?>">
            </section>

            <section class="movie-detail-panel movie-detail-media">
                <div class="movie-detail-panel-head">
                    <span><i class="fa-brands fa-youtube"></i></span>
                    <div>
                        <h3>Trailer</h3>
                        <p>Xem trước video giới thiệu phim.</p>
                    </div>
                </div>

                <?php if($trailerEmbed): ?>
                    <div class="movie-detail-trailer">
                        <iframe src="<?php echo e($trailerEmbed); ?>" title="Trailer <?php echo e($phim->ten_phim); ?>" allowfullscreen></iframe>
                    </div>
                <?php else: ?>
                    <div class="movie-detail-empty is-compact">
                        <i class="fa-brands fa-youtube"></i>
                        <strong>Chưa có trailer hợp lệ</strong>
                        <span>Cập nhật link YouTube để xem preview tại đây.</span>
                    </div>
                <?php endif; ?>
            </section>

            <section class="movie-detail-panel">
                <div class="movie-detail-panel-head">
                    <span><i class="fa-solid fa-tags"></i></span>
                    <div>
                        <h3>Thể loại</h3>
                        <p>Các nhóm phim đang gắn.</p>
                    </div>
                </div>

                <div class="movie-detail-genres">
                    <?php $__empty_1 = true; $__currentLoopData = $phim->genres; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $genre): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <span><?php echo e($genre->ten_the_loai); ?></span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <span class="is-muted">Chưa có thể loại</span>
                    <?php endif; ?>
                </div>
            </section>
        </aside>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\DATN\WD-11-Cinehome-cinema\resources\views/admin/phims/show.blade.php ENDPATH**/ ?>