<?php $__env->startSection('title', 'Danh sách phim'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $statusLabels = [
            \App\Models\SuatChieu::TRANG_THAI_DANG_CHIEU => 'Đang chiếu',
            \App\Models\SuatChieu::TRANG_THAI_SAP_CHIEU => 'Sắp chiếu',
            \App\Models\SuatChieu::TRANG_THAI_SAP_RA_MAT => 'Sắp ra mắt',
        ];

        $posterUrl = function ($movie) {
            if (!empty($movie->poster) && file_exists(public_path('storage/movies/' . $movie->poster))) {
                return asset('storage/movies/' . $movie->poster);
            }

            return 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=700&auto=format&fit=crop';
        };
    ?>

    <div class="movie-list-page" lang="vi" spellcheck="false">
        <section class="movie-list-hero">
            <div>
                <span class="movie-list-eyebrow">
                    <i class="fa-solid fa-film"></i>
                    CineHome Movies
                </span>
                <h1>Chọn phim bạn muốn xem tại CineHome.</h1>
                <p>
                    Lọc nhanh theo tên phim, thể loại, quốc gia và trạng thái chiếu. Giao diện được tối ưu để xem poster,
                    so sánh phim và đặt vé nhanh hơn.
                </p>
            </div>

            <div class="movie-list-summary">
                <strong><?php echo e($movies->count()); ?></strong>
                <span>phim phù hợp</span>
            </div>
        </section>

        <form action="<?php echo e(route('user.phims.index')); ?>" method="GET" class="movie-filter movie-filter-form">
            <label>
                <span>Tên phim</span>
                <input type="text" name="tim_kiem" value="<?php echo e(request('tim_kiem')); ?>" placeholder="Tìm tên phim..."
                    class="filter-input">
            </label>

            <label>
                <span>Thể loại</span>
                <select name="the_loai" class="filter-input">
                    <option value="">Tất cả thể loại</option>
                    <?php $__currentLoopData = $genres; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $genre): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($genre->ten_the_loai); ?>" <?php echo e(request('the_loai') == $genre->ten_the_loai ? 'selected' : ''); ?>>
                            <?php echo e($genre->ten_the_loai); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>

            <label>
                <span>Quốc gia</span>
                <select name="quoc_gia" class="filter-input">
                    <option value="">Tất cả quốc gia</option>
                    <?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($country->ten_quoc_gia); ?>" <?php echo e(request('quoc_gia') == $country->ten_quoc_gia ? 'selected' : ''); ?>>
                            <?php echo e($country->ten_quoc_gia); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>

            <label>
                <span>Trạng thái</span>
                <select name="status" class="filter-input">
                    <option value="">Tất cả trạng thái</option>
                    <?php $__currentLoopData = $statusLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $statusValue => $statusLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($statusValue); ?>" <?php echo e(request('status') == $statusValue ? 'selected' : ''); ?>>
                            <?php echo e($statusLabel); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>

            <div class="movie-filter-actions">
                <button type="submit" class="btn-filter">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    Tìm
                </button>
                <a href="<?php echo e(route('user.phims.index')); ?>" class="btn-reset" aria-label="Xóa bộ lọc">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </form>

        <?php if($movies->count() > 0): ?>
            <section class="movie-list-grid" aria-label="Danh sách phim">
                <?php $__currentLoopData = $movies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movie): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $now = now('Asia/Ho_Chi_Minh');
                        $futureShowtime = $movie->showtimes
                            ->filter(fn($showtime) => $showtime->thoi_gian_chieu && \Carbon\Carbon::parse($showtime->thoi_gian_chieu)->gte($now))
                            ->sortBy('thoi_gian_chieu')
                            ->first();

                        $movieStatus = $futureShowtime?->trang_thai ?? \App\Models\SuatChieu::TRANG_THAI_SAP_RA_MAT;
                        $canBook = $futureShowtime && $futureShowtime->trang_thai === \App\Models\SuatChieu::TRANG_THAI_SAP_CHIEU;
                    ?>

                    <article class="movie-card movie-list-card">
                        <a href="<?php echo e(route('user.movies.show', $movie->slug)); ?>" class="movie-poster" aria-label="Xem chi tiết <?php echo e($movie->ten_phim); ?>">
                            <img src="<?php echo e($posterUrl($movie)); ?>" alt="<?php echo e($movie->ten_phim); ?>">
                            <span class="movie-status"><?php echo e($statusLabels[$movieStatus] ?? 'Sắp chiếu'); ?></span>
                            <?php if(!empty($movie->gioi_han_tuoi)): ?>
                                <span class="movie-age"><?php echo e($movie->gioi_han_tuoi); ?></span>
                            <?php endif; ?>
                        </a>

                        <div class="movie-body">
                            <h2 class="movie-title"><?php echo e($movie->ten_phim); ?></h2>

                            <div class="movie-info">
                                <p>
                                    <i class="fa-solid fa-clapperboard"></i>
                                    <?php echo e($movie->genres->pluck('ten_the_loai')->filter()->take(2)->join(', ') ?: 'Đang cập nhật'); ?>

                                </p>
                                <p>
                                    <i class="fa-solid fa-globe"></i>
                                    <?php echo e($movie->country->ten_quoc_gia ?? 'Đang cập nhật'); ?>

                                </p>
                                <p>
                                    <i class="fa-solid fa-clock"></i>
                                    <?php echo e($movie->thoi_luong ?? '--'); ?> phút
                                </p>
                            </div>

                            <div class="movie-actions">
                                <?php if($canBook): ?>
                                    <a href="<?php echo e(route('dat_ve.chon_ghe', $movie->slug)); ?>" class="btn-small-book booking-link">
                                        <i class="fa-solid fa-ticket"></i>
                                        Đặt vé
                                    </a>
                                <?php else: ?>
                                    <span class="btn-small-book is-disabled">
                                        <i class="fa-solid fa-calendar-days"></i>
                                        Chờ lịch
                                    </span>
                                <?php endif; ?>

                                <a href="<?php echo e(route('user.movies.show', $movie->slug)); ?>" class="btn-small-detail">
                                    Chi tiết
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </section>
        <?php else: ?>
            <section class="empty-movies">
                <i class="fa-solid fa-film"></i>
                <h2>Không tìm thấy phim</h2>
                <p>Thử đổi từ khóa, thể loại hoặc trạng thái chiếu để xem thêm phim phù hợp.</p>
            </section>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\WD-11-Cinehome-cinema - Copy\WD-11-Cinehome-cinema - Copy\resources\views/user/phims/index.blade.php ENDPATH**/ ?>