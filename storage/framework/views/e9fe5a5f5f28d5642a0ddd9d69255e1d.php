<?php $__env->startSection('page-title', 'Danh sách phim'); ?>
<?php $__env->startSection('page-subtitle', 'Quản lý kho phim, poster, thể loại và lịch chiếu'); ?>

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

    $currentMovies = $movies->getCollection();
    $activeFilterCount = collect([
        request('tim_kiem'),
        request('the_loai'),
        request('quoc_gia'),
    ])->filter(fn ($value) => filled($value))->count();
    $moviesWithShowtimes = $currentMovies->filter(fn ($movie) => $movie->showtimes->isNotEmpty())->count();
?>

<?php $__env->startSection('content'); ?>
<div class="movie-admin-page">
    <?php echo $__env->make('admin.partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <section class="movie-library-hero">
        <div class="movie-library-copy">
            <span class="movie-kicker">
                <i class="fa-solid fa-film"></i>
                Kho phim CineHome
            </span>
            <h2>Danh sách phim</h2>
            <p>
                Quản lý poster, thông tin phim, thể loại và trạng thái lịch chiếu trong một giao diện gọn gàng,
                dễ quét và thao tác nhanh hơn.
            </p>

            <div class="movie-hero-meta">
                <span>
                    <i class="fa-solid fa-layer-group"></i>
                    <?php echo e(number_format($movies->total())); ?> phim
                </span>
                <span>
                    <i class="fa-solid fa-filter"></i>
                    <?php echo e($activeFilterCount); ?> bộ lọc đang dùng
                </span>
            </div>
        </div>

        <div class="movie-hero-actions">
            <a href="<?php echo e(route('admin.phims.create')); ?>" class="movie-action-btn is-primary">
                <i class="fa-solid fa-plus"></i>
                Thêm phim mới
            </a>
            <a href="<?php echo e(route('admin.suat-chieus.index')); ?>" class="movie-action-btn is-soft">
                <i class="fa-solid fa-calendar-days"></i>
                Quản lý suất chiếu
            </a>
        </div>
    </section>

    <section class="movie-stat-grid" aria-label="Tổng quan kho phim">
        <article class="movie-stat-card">
            <span><i class="fa-solid fa-clapperboard"></i></span>
            <div>
                <small>Tổng phim</small>
                <strong><?php echo e(number_format($movies->total())); ?></strong>
            </div>
        </article>

        <article class="movie-stat-card">
            <span><i class="fa-solid fa-table-list"></i></span>
            <div>
                <small>Đang hiển thị</small>
                <strong><?php echo e(number_format($currentMovies->count())); ?></strong>
            </div>
        </article>

        <article class="movie-stat-card">
            <span><i class="fa-solid fa-clock"></i></span>
            <div>
                <small>Có suất chiếu ở trang này</small>
                <strong><?php echo e(number_format($moviesWithShowtimes)); ?></strong>
            </div>
        </article>

        <article class="movie-stat-card">
            <span><i class="fa-solid fa-tags"></i></span>
            <div>
                <small>Thể loại đang bật</small>
                <strong><?php echo e(number_format($genres->count())); ?></strong>
            </div>
        </article>
    </section>

    <form method="GET" action="<?php echo e(route('admin.phims.index')); ?>" class="movie-filter-panel">
        <label class="movie-filter-field is-search">
            <span>Tìm kiếm</span>
            <div>
                <i class="fa-solid fa-magnifying-glass"></i>
                <input
                    type="text"
                    name="tim_kiem"
                    value="<?php echo e(request('tim_kiem')); ?>"
                    placeholder="Nhập tên phim cần tìm..."
                >
            </div>
        </label>

        <label class="movie-filter-field">
            <span>Thể loại</span>
            <select name="the_loai">
                <option value="">Tất cả thể loại</option>
                <?php $__currentLoopData = $genres; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $genre): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($genre->ten_the_loai); ?>" <?php if(request('the_loai') == $genre->ten_the_loai): echo 'selected'; endif; ?>>
                        <?php echo e($genre->ten_the_loai); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </label>

        <label class="movie-filter-field">
            <span>Quốc gia</span>
            <select name="quoc_gia">
                <option value="">Tất cả quốc gia</option>
                <?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($country->ten_quoc_gia); ?>" <?php if(request('quoc_gia') == $country->ten_quoc_gia): echo 'selected'; endif; ?>>
                        <?php echo e($country->ten_quoc_gia); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </label>

        <div class="movie-filter-actions">
            <button type="submit" class="movie-action-btn is-primary">
                <i class="fa-solid fa-sliders"></i>
                Lọc phim
            </button>
            <a href="<?php echo e(route('admin.phims.index')); ?>" class="movie-action-btn is-ghost">
                <i class="fa-solid fa-rotate-left"></i>
                Đặt lại
            </a>
        </div>
    </form>

    <section class="movie-table-panel">
        <div class="movie-table-head">
            <div>
                <span class="movie-kicker">
                    <i class="fa-solid fa-list-check"></i>
                    Kết quả quản lý
                </span>
                <h3>Kho phim</h3>
            </div>
            <p>
                Hiển thị
                <strong><?php echo e($movies->firstItem() ?? 0); ?> - <?php echo e($movies->lastItem() ?? 0); ?></strong>
                trong
                <strong><?php echo e(number_format($movies->total())); ?></strong>
                phim
            </p>
        </div>

        <div class="movie-table-wrap">
            <table class="movie-admin-table">
                <thead>
                    <tr>
                        <th>Phim</th>
                        <th>Quốc gia</th>
                        <th>Thể loại</th>
                        <th>Ngôn ngữ</th>
                        <th>Thời lượng</th>
                        <th>Suất chiếu</th>
                        <th class="is-actions">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $movies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movie): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $showtimeCount = $movie->showtimes->count();
                            $visibleGenres = $movie->genres->take(3);
                            $hiddenGenreCount = max($movie->genres->count() - $visibleGenres->count(), 0);
                        ?>
                        <tr>
                            <td>
                                <div class="movie-title-cell">
                                    <a href="<?php echo e(route('admin.phims.show', $movie)); ?>" class="movie-poster-thumb">
                                        <img src="<?php echo e($posterUrl($movie->poster)); ?>" alt="<?php echo e($movie->ten_phim); ?>">
                                    </a>
                                    <div class="movie-title-copy">
                                        <a href="<?php echo e(route('admin.phims.show', $movie)); ?>"><?php echo e($movie->ten_phim); ?></a>
                                        <span>
                                            <i class="fa-solid fa-id-card-clip"></i>
                                            #<?php echo e($movie->id); ?>

                                            <?php if($movie->gioi_han_tuoi): ?>
                                                <em><?php echo e($movie->gioi_han_tuoi); ?></em>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="movie-country-chip">
                                    <i class="fa-solid fa-location-dot"></i>
                                    <?php echo e($movie->country?->ten_quoc_gia ?? 'Chưa cập nhật'); ?>

                                </span>
                            </td>

                            <td>
                                <div class="movie-genre-list">
                                    <?php $__empty_2 = true; $__currentLoopData = $visibleGenres; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $genre): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                        <span><?php echo e($genre->ten_the_loai); ?></span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                        <span class="is-muted">Chưa có thể loại</span>
                                    <?php endif; ?>

                                    <?php if($hiddenGenreCount > 0): ?>
                                        <span class="is-more">+<?php echo e($hiddenGenreCount); ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td>
                                <span class="movie-muted-text"><?php echo e($movie->ngon_ngu ?: 'Chưa cập nhật'); ?></span>
                            </td>

                            <td>
                                <span class="movie-duration-pill">
                                    <i class="fa-regular fa-clock"></i>
                                    <?php echo e((int) $movie->thoi_luong); ?> phút
                                </span>
                            </td>

                            <td>
                                <?php if($showtimeCount > 0): ?>
                                    <span class="movie-schedule-badge is-live">
                                        <i class="fa-solid fa-calendar-check"></i>
                                        <?php echo e($showtimeCount); ?> suất
                                    </span>
                                <?php else: ?>
                                    <span class="movie-schedule-badge is-empty">
                                        <i class="fa-solid fa-calendar-xmark"></i>
                                        Chưa có
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="movie-row-actions">
                                    <a href="<?php echo e(route('admin.phims.show', $movie)); ?>" class="movie-icon-btn is-view" title="Xem chi tiết">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="<?php echo e(route('admin.phims.edit', $movie)); ?>" class="movie-icon-btn is-edit" title="Sửa phim">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form method="POST" action="<?php echo e(route('admin.phims.destroy', $movie)); ?>" onsubmit="return confirm('Bạn chắc chắn muốn xóa phim này?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="movie-icon-btn is-delete" title="Xóa phim">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7">
                                <div class="movie-empty-state">
                                    <i class="fa-solid fa-film"></i>
                                    <strong>Chưa có phim phù hợp</strong>
                                    <span>Thử đổi bộ lọc hoặc thêm phim mới vào kho CineHome.</span>
                                    <a href="<?php echo e(route('admin.phims.create')); ?>" class="movie-action-btn is-primary">
                                        <i class="fa-solid fa-plus"></i>
                                        Thêm phim mới
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="movie-admin-pagination">
            <?php echo $__env->make('components.admin-pagination', ['paginator' => $movies], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </section>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/admin/phims/index.blade.php ENDPATH**/ ?>