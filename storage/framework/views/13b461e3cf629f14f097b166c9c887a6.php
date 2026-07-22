<?php $__env->startSection('page-title', 'Quản lý suất chiếu'); ?>
<?php $__env->startSection('page-subtitle', 'Điều phối lịch chiếu theo từng phim, phòng và trạng thái vận hành'); ?>

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

    $visibleMovies = $phimsPhanTrang->getCollection();
    $visibleShowtimes = $visibleMovies->flatMap(fn ($movie) => $movie->showtimes);
    $activeFilterCount = collect([
        request('phim_id'),
        request('phong_chieu_id'),
        request('trang_thai'),
        request('ngay_chieu'),
    ])->filter(fn ($value) => filled($value))->count();
    $moviesWithShowtimes = $visibleMovies->filter(fn ($movie) => $movie->showtimes->isNotEmpty())->count();
    $todayShowtimes = $visibleShowtimes->filter(fn ($showtime) => $showtime->thoi_gian_chieu?->isToday())->count();
    $upcomingShowtimes = $visibleShowtimes->filter(fn ($showtime) => $showtime->thoi_gian_chieu?->isFuture())->count();
    $statusLabels = \App\Models\SuatChieu::TRANG_THAI_LIST + [
        'dung_nhan_ve' => 'Dừng nhận vé',
    ];
?>

<?php $__env->startSection('content'); ?>
<div class="showtime-index-page">
    <?php echo $__env->make('admin.partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <section class="showtime-index-hero">
        <div>
            <span class="showtime-kicker">
                <i class="fa-solid fa-layer-group"></i>
                Điều phối lịch chiếu
            </span>
            <h2>Quản lý suất chiếu theo phim</h2>
            <p>
                Theo dõi từng phim, kiểm tra rạp, phòng, khung giờ, giá vé và trạng thái vận hành trong một giao diện
                gọn, dễ mở rộng và dễ thao tác.
            </p>
        </div>

        <div class="showtime-index-actions">
            <a href="<?php echo e(route('admin.suat-chieus.create')); ?>" class="movie-action-btn is-primary">
                <i class="fa-solid fa-calendar-plus"></i>
                Thêm suất chiếu
            </a>
            <a href="<?php echo e(route('admin.phims.index')); ?>" class="movie-action-btn is-ghost">
                <i class="fa-solid fa-film"></i>
                Kho phim
            </a>
        </div>
    </section>

    <section class="showtime-index-stats" aria-label="Tổng quan suất chiếu">
        <article>
            <span><i class="fa-solid fa-clapperboard"></i></span>
            <div>
                <small>Phim trong trang</small>
                <strong><?php echo e(number_format($visibleMovies->count())); ?></strong>
            </div>
        </article>

        <article>
            <span><i class="fa-solid fa-calendar-check"></i></span>
            <div>
                <small>Suất đang hiển thị</small>
                <strong><?php echo e(number_format($visibleShowtimes->count())); ?></strong>
            </div>
        </article>

        <article>
            <span><i class="fa-solid fa-clock"></i></span>
            <div>
                <small>Suất hôm nay</small>
                <strong><?php echo e(number_format($todayShowtimes)); ?></strong>
            </div>
        </article>

        <article>
            <span><i class="fa-solid fa-filter"></i></span>
            <div>
                <small>Bộ lọc đang dùng</small>
                <strong><?php echo e(number_format($activeFilterCount)); ?></strong>
            </div>
        </article>
    </section>

    <form method="GET" action="<?php echo e(route('admin.suat-chieus.index')); ?>" class="showtime-index-filter">
        <label class="showtime-filter-field">
            <span>Phim</span>
            <select name="phim_id">
                <option value="">Tất cả phim</option>
                <?php $__currentLoopData = $phims; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itemPhim): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($itemPhim->id); ?>" <?php if(request('phim_id') == $itemPhim->id): echo 'selected'; endif; ?>>
                        <?php echo e($itemPhim->ten_phim); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </label>

        <label class="showtime-filter-field">
            <span>Phòng chiếu</span>
            <select name="phong_chieu_id">
                <option value="">Tất cả phòng</option>
                <?php $__currentLoopData = $phongChieus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $phong): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($phong->id); ?>" <?php if(request('phong_chieu_id') == $phong->id): echo 'selected'; endif; ?>>
                        <?php echo e($phong->ten_phong); ?> (<?php echo e(strtoupper($phong->loai_phong)); ?>)
                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </label>

        <label class="showtime-filter-field">
            <span>Trạng thái</span>
            <select name="trang_thai">
                <option value="">Tất cả trạng thái</option>
                <?php $__currentLoopData = $statusLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($value); ?>" <?php if(request('trang_thai') == $value): echo 'selected'; endif; ?>>
                        <?php echo e($label); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </label>

        <label class="showtime-filter-field">
            <span>Ngày chiếu</span>
            <input type="date" name="ngay_chieu" value="<?php echo e(request('ngay_chieu')); ?>">
        </label>

        <div class="showtime-filter-actions">
            <button type="submit" class="movie-action-btn is-primary">
                <i class="fa-solid fa-magnifying-glass"></i>
                Lọc lịch
            </button>
            <a href="<?php echo e(route('admin.suat-chieus.index')); ?>" class="movie-action-btn is-ghost">
                <i class="fa-solid fa-rotate-left"></i>
                Đặt lại
            </a>
        </div>
    </form>

    <section class="showtime-index-list">
        <div class="showtime-index-list-head">
            <div>
                <span class="showtime-kicker">
                    <i class="fa-solid fa-list-check"></i>
                    Kết quả điều phối
                </span>
                <h3><?php echo e(number_format($moviesWithShowtimes)); ?> phim có suất chiếu trong trang này</h3>
            </div>
            <p>
                Hiển thị
                <strong><?php echo e($phimsPhanTrang->firstItem() ?? 0); ?> - <?php echo e($phimsPhanTrang->lastItem() ?? 0); ?></strong>
                trong
                <strong><?php echo e(number_format($phimsPhanTrang->total())); ?></strong>
                phim
            </p>
        </div>

        <div class="showtime-movie-stack">
            <?php $__empty_1 = true; $__currentLoopData = $phimsPhanTrang; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $phim): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $movieShowtimes = $phim->showtimes;
                    $movieTodayCount = $movieShowtimes->filter(fn ($showtime) => $showtime->thoi_gian_chieu?->isToday())->count();
                    $movieUpcomingCount = $movieShowtimes->filter(fn ($showtime) => $showtime->thoi_gian_chieu?->isFuture())->count();
                    $firstShowtime = $movieShowtimes->first();
                ?>

                <details class="showtime-movie-card" <?php echo e($loop->first ? 'open' : ''); ?>>
                    <summary class="showtime-movie-summary">
                        <div class="showtime-movie-title">
                            <img src="<?php echo e($posterUrl($phim->poster)); ?>" alt="<?php echo e($phim->ten_phim); ?>">
                            <div>
                                <strong><?php echo e($phim->ten_phim); ?></strong>
                                <span>
                                    <i class="fa-regular fa-clock"></i>
                                    <?php echo e($phim->thoi_luong ?? 90); ?> phút
                                    <b></b>
                                    <?php echo e($phim->country?->ten_quoc_gia ?? 'Chưa cập nhật quốc gia'); ?>

                                </span>
                            </div>
                        </div>

                        <div class="showtime-movie-metrics">
                            <span>
                                <small>Tổng suất</small>
                                <strong><?php echo e($movieShowtimes->count()); ?></strong>
                            </span>
                            <span>
                                <small>Hôm nay</small>
                                <strong><?php echo e($movieTodayCount); ?></strong>
                            </span>
                            <span>
                                <small>Sắp tới</small>
                                <strong><?php echo e($movieUpcomingCount); ?></strong>
                            </span>
                        </div>

                        <div class="showtime-movie-chevron">
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                    </summary>

                    <div class="showtime-movie-body">
                        <?php if($movieShowtimes->isNotEmpty()): ?>
                            <div class="showtime-mobile-list">
                                <?php $__currentLoopData = $movieShowtimes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $suat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $status = $suat->trang_thai;
                                        $statusClass = match ($status) {
                                            'dang_chieu' => 'is-live',
                                            'sap_chieu', 'sap_ra_mat' => 'is-upcoming',
                                            'huy' => 'is-cancelled',
                                            default => 'is-muted',
                                        };
                                    ?>
                                    <article class="showtime-mobile-item">
                                        <div>
                                            <strong><?php echo e($suat->thoi_gian_chieu?->format('H:i d/m/Y') ?? 'N/A'); ?></strong>
                                            <span>
                                                <?php echo e($suat->rapChieuPhim?->ten_rap ?? 'N/A'); ?>

                                                •
                                                <?php echo e($suat->phongChieu?->ten_phong ?? 'N/A'); ?>

                                            </span>
                                        </div>
                                        <em class="<?php echo e($statusClass); ?>"><?php echo e($statusLabels[$status] ?? $status); ?></em>
                                    </article>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>

                            <div class="showtime-table-wrap">
                                <table class="showtime-index-table">
                                    <thead>
                                        <tr>
                                            <th>Mã suất</th>
                                            <th>Rạp</th>
                                            <th>Phòng</th>
                                            <th>Ngày chiếu</th>
                                            <th>Khung giờ</th>
                                            <th>Giá vé</th>
                                            <th>Trạng thái</th>
                                            <th class="is-actions">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $movieShowtimes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $suat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $status = $suat->trang_thai;
                                                $statusClass = match ($status) {
                                                    'dang_chieu' => 'is-live',
                                                    'sap_chieu', 'sap_ra_mat' => 'is-upcoming',
                                                    'huy' => 'is-cancelled',
                                                    default => 'is-muted',
                                                };
                                            ?>
                                            <tr>
                                                <td>
                                                    <span class="showtime-code">#<?php echo e(sprintf('%04d', $suat->id)); ?></span>
                                                </td>
                                                <td><?php echo e($suat->rapChieuPhim?->ten_rap ?? 'N/A'); ?></td>
                                                <td>
                                                    <span class="showtime-room-chip">
                                                        <?php echo e($suat->phongChieu?->ten_phong ?? 'N/A'); ?>

                                                        <?php if($suat->phongChieu?->loai_phong): ?>
                                                            <b><?php echo e(strtoupper($suat->phongChieu->loai_phong)); ?></b>
                                                        <?php endif; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo e($suat->thoi_gian_chieu?->format('d/m/Y') ?? 'N/A'); ?></td>
                                                <td>
                                                    <span class="showtime-time-range">
                                                        <?php echo e($suat->thoi_gian_chieu?->format('H:i') ?? '--:--'); ?>

                                                        <i class="fa-solid fa-arrow-right"></i>
                                                        <?php echo e($suat->thoi_gian_ket_thuc?->format('H:i') ?? '--:--'); ?>

                                                    </span>
                                                </td>
                                                <td>
                                                    <strong class="showtime-price"><?php echo e(number_format((float) $suat->gia_ve)); ?>đ</strong>
                                                </td>
                                                <td>
                                                    <span class="showtime-status <?php echo e($statusClass); ?>">
                                                        <?php echo e($statusLabels[$status] ?? $status); ?>

                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="showtime-row-actions">
                                                        <a href="<?php echo e(route('admin.suat-chieus.show', $suat)); ?>" class="movie-icon-btn is-view" title="Xem chi tiết">
                                                            <i class="fa-solid fa-eye"></i>
                                                        </a>
                                                        <a href="<?php echo e(route('admin.suat-chieus.edit', $suat)); ?>" class="movie-icon-btn is-edit" title="Sửa suất chiếu">
                                                            <i class="fa-solid fa-pen"></i>
                                                        </a>
                                                        <form action="<?php echo e(route('admin.suat-chieus.destroy', $suat)); ?>" method="POST" onsubmit="return confirm('Bạn chắc chắn muốn xóa suất chiếu này?')">
                                                            <?php echo csrf_field(); ?>
                                                            <?php echo method_field('DELETE'); ?>
                                                            <button type="submit" class="movie-icon-btn is-delete" title="Xóa suất chiếu">
                                                                <i class="fa-solid fa-trash-can"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="showtime-empty-card">
                                <i class="fa-regular fa-calendar-xmark"></i>
                                <strong>Phim này chưa có suất chiếu phù hợp</strong>
                                <span>Thêm suất chiếu mới hoặc thay đổi bộ lọc để xem các lịch khác.</span>
                                <a href="<?php echo e(route('admin.suat-chieus.create', ['phim_id' => $phim->id])); ?>" class="movie-action-btn is-primary">
                                    <i class="fa-solid fa-calendar-plus"></i>
                                    Tạo suất chiếu
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </details>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="showtime-empty-card is-page-empty">
                    <i class="fa-solid fa-box-open"></i>
                    <strong>Không tìm thấy phim phù hợp</strong>
                    <span>Thử thay đổi bộ lọc hoặc thêm phim mới vào kho trước khi lên lịch.</span>
                </div>
            <?php endif; ?>
        </div>

        <div class="showtime-index-pagination">
            <?php echo $__env->make('components.admin-pagination', ['paginator' => $phimsPhanTrang], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </section>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/admin/suat-chieus/index.blade.php ENDPATH**/ ?>