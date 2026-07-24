<?php $__env->startSection('page-title', 'Danh sách quốc gia phim'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $summary = $summary ?? [
            'total' => 0,
            'active' => 0,
            'inactive' => 0,
            'with_movies' => 0,
        ];
    ?>

    <div class="country-admin-page">
        <?php echo $__env->make('admin.partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <section class="country-hero-panel">
            <div class="country-hero-content">
                <span class="country-kicker">
                    <i class="fa-solid fa-earth-asia"></i>
                    Kho quốc gia CineHome
                </span>
                <h1>Danh sách quốc gia</h1>
                <p>
                    Quản lý nguồn gốc phim, mã quốc gia và trạng thái sử dụng trong hệ thống.
                    Bảng được thiết kế để tìm kiếm nhanh, kiểm tra số phim liên kết và thao tác an toàn hơn.
                </p>
            </div>

            <div class="country-hero-actions">
                <a href="<?php echo e(route('admin.phims.index')); ?>" class="movie-action-btn is-ghost">
                    <i class="fa-solid fa-film"></i>
                    Danh sách phim
                </a>
                <a href="<?php echo e(route('admin.quoc-gias.create')); ?>" class="movie-action-btn is-primary">
                    <i class="fa-solid fa-plus"></i>
                    Thêm quốc gia
                </a>
            </div>
        </section>

        <section class="country-stat-grid" aria-label="Thống kê quốc gia">
            <article class="country-stat-card">
                <span class="country-stat-icon is-total"><i class="fa-solid fa-globe"></i></span>
                <div>
                    <span>Tổng quốc gia</span>
                    <strong><?php echo e(number_format($summary['total'] ?? 0)); ?></strong>
                </div>
            </article>

            <article class="country-stat-card">
                <span class="country-stat-icon is-active"><i class="fa-solid fa-circle-check"></i></span>
                <div>
                    <span>Đang sử dụng</span>
                    <strong><?php echo e(number_format($summary['active'] ?? 0)); ?></strong>
                </div>
            </article>

            <article class="country-stat-card">
                <span class="country-stat-icon is-muted"><i class="fa-solid fa-circle-pause"></i></span>
                <div>
                    <span>Tạm ẩn</span>
                    <strong><?php echo e(number_format($summary['inactive'] ?? 0)); ?></strong>
                </div>
            </article>

            <article class="country-stat-card">
                <span class="country-stat-icon is-linked"><i class="fa-solid fa-link"></i></span>
                <div>
                    <span>Có phim liên kết</span>
                    <strong><?php echo e(number_format($summary['with_movies'] ?? 0)); ?></strong>
                </div>
            </article>
        </section>

        <form action="<?php echo e(route('admin.quoc-gias.index')); ?>" method="GET" class="country-filter-panel">
            <label class="country-filter-field">
                <span>Tìm kiếm</span>
                <div class="country-filter-control">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input
                        type="text"
                        name="search"
                        value="<?php echo e(request('search')); ?>"
                        placeholder="Nhập tên hoặc mã quốc gia, ví dụ: Việt Nam, US..."
                    >
                </div>
            </label>

            <label class="country-filter-field is-status">
                <span>Trạng thái</span>
                <div class="country-filter-control">
                    <i class="fa-solid fa-sliders"></i>
                    <select name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="1" <?php if(request('status') === '1'): echo 'selected'; endif; ?>>Đang sử dụng</option>
                        <option value="0" <?php if(request('status') === '0'): echo 'selected'; endif; ?>>Tạm ẩn</option>
                    </select>
                </div>
            </label>

            <div class="country-filter-actions">
                <button type="submit" class="movie-action-btn is-primary">
                    <i class="fa-solid fa-filter"></i>
                    Lọc dữ liệu
                </button>
                <a href="<?php echo e(route('admin.quoc-gias.index')); ?>" class="movie-action-btn is-soft">
                    <i class="fa-solid fa-rotate-left"></i>
                    Đặt lại
                </a>
            </div>
        </form>

        <section class="country-table-panel">
            <div class="country-table-head">
                <div>
                    <span class="country-kicker">
                        <i class="fa-solid fa-list-check"></i>
                        Bảng quản lý
                    </span>
                    <h2>Quốc gia phim</h2>
                </div>
                <div class="country-result-count">
                    <?php echo e(number_format($countries->total())); ?> kết quả
                </div>
            </div>

            <div class="country-table-wrap">
                <table class="country-admin-table">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Quốc gia</th>
                            <th>Mã quốc gia</th>
                            <th>Số phim</th>
                            <th>Trạng thái</th>
                            <th>Cập nhật</th>
                            <th class="is-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="country-index-cell">
                                    #<?php echo e(($countries->currentPage() - 1) * $countries->perPage() + $key + 1); ?>

                                </td>
                                <td>
                                    <div class="country-title-cell">
                                        <span class="country-flag-avatar">
                                            <?php echo e(\Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($country->ma_quoc_gia ?: $country->ten_quoc_gia, 0, 2))); ?>

                                        </span>
                                        <div class="country-title-copy">
                                            <strong><?php echo e($country->ten_quoc_gia); ?></strong>
                                            <small>ID <?php echo e($country->id); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <code class="country-code">
                                        <?php echo e(\Illuminate\Support\Str::upper($country->ma_quoc_gia ?: '--')); ?>

                                    </code>
                                </td>
                                <td>
                                    <span class="country-movie-count <?php echo e($country->phims_count > 0 ? 'has-movies' : ''); ?>">
                                        <i class="fa-solid fa-film"></i>
                                        <?php echo e(number_format($country->phims_count)); ?>

                                    </span>
                                </td>
                                <td>
                                    <?php if($country->trang_thai): ?>
                                        <span class="country-status is-active">
                                            <i class="fa-solid fa-circle-check"></i>
                                            Đang dùng
                                        </span>
                                    <?php else: ?>
                                        <span class="country-status is-inactive">
                                            <i class="fa-solid fa-circle-pause"></i>
                                            Tạm ẩn
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="country-date">
                                        <i class="fa-regular fa-clock"></i>
                                        <?php echo e(optional($country->updated_at)->format('d/m/Y') ?? 'Chưa cập nhật'); ?>

                                    </span>
                                </td>
                                <td>
                                    <div class="country-row-actions">
                                        <a
                                            href="<?php echo e(route('admin.quoc-gias.edit', $country)); ?>"
                                            class="movie-icon-btn is-edit"
                                            title="Chỉnh sửa quốc gia"
                                            aria-label="Chỉnh sửa <?php echo e($country->ten_quoc_gia); ?>"
                                        >
                                            <i class="fa-solid fa-pen"></i>
                                        </a>

                                        <?php if($country->phims_count > 0): ?>
                                            <button
                                                type="button"
                                                class="movie-icon-btn is-delete country-delete-disabled"
                                                title="Không thể xóa vì đang có phim liên kết"
                                                aria-label="Không thể xóa <?php echo e($country->ten_quoc_gia); ?>"
                                                disabled
                                            >
                                                <i class="fa-solid fa-lock"></i>
                                            </button>
                                        <?php else: ?>
                                            <form action="<?php echo e(route('admin.quoc-gias.destroy', $country)); ?>" method="POST">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button
                                                    type="submit"
                                                    onclick="return confirm('Bạn có chắc muốn xóa quốc gia này?')"
                                                    class="movie-icon-btn is-delete"
                                                    title="Xóa quốc gia"
                                                    aria-label="Xóa <?php echo e($country->ten_quoc_gia); ?>"
                                                >
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7">
                                    <div class="country-empty-state">
                                        <span><i class="fa-solid fa-earth-asia"></i></span>
                                        <h3>Chưa tìm thấy quốc gia phù hợp</h3>
                                        <p>Thử đổi từ khóa, bỏ bộ lọc trạng thái hoặc thêm quốc gia mới cho kho phim.</p>
                                        <a href="<?php echo e(route('admin.quoc-gias.create')); ?>" class="movie-action-btn is-primary">
                                            <i class="fa-solid fa-plus"></i>
                                            Thêm quốc gia
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="country-pagination">
                <?php echo $__env->make('components.admin-pagination', ['paginator' => $countries], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </section>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\DATN\WD-11-Cinehome-cinema\resources\views/admin/quoc-gias/index.blade.php ENDPATH**/ ?>