<?php $__env->startSection('page-title', 'Danh sách thể loại phim'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $summary = $summary ?? [
            'total' => 0,
            'active' => 0,
            'inactive' => 0,
            'with_movies' => 0,
        ];
    ?>

    <div class="genre-admin-page">
        <?php echo $__env->make('admin.partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <section class="genre-hero-panel">
            <div class="genre-hero-content">
                <span class="genre-kicker">
                    <i class="fa-solid fa-layer-group"></i>
                    Kho thể loại CineHome
                </span>
                <h1>Danh sách thể loại phim</h1>
                <p>
                    Quản lý nhóm nội dung, trạng thái hiển thị và số lượng phim đang gắn với từng thể loại.
                    Giao diện được tối ưu để lọc nhanh, kiểm tra nhanh và thao tác an toàn.
                </p>
            </div>

            <div class="genre-hero-actions">
                <a href="<?php echo e(route('admin.phims.index')); ?>" class="movie-action-btn is-ghost">
                    <i class="fa-solid fa-film"></i>
                    Danh sách phim
                </a>
                <a href="<?php echo e(route('admin.the-loais.create')); ?>" class="movie-action-btn is-primary">
                    <i class="fa-solid fa-plus"></i>
                    Thêm thể loại
                </a>
            </div>
        </section>

        <section class="genre-stat-grid" aria-label="Thống kê thể loại">
            <article class="genre-stat-card">
                <span class="genre-stat-icon is-total"><i class="fa-solid fa-clapperboard"></i></span>
                <div>
                    <span>Tổng thể loại</span>
                    <strong><?php echo e(number_format($summary['total'] ?? 0)); ?></strong>
                </div>
            </article>

            <article class="genre-stat-card">
                <span class="genre-stat-icon is-active"><i class="fa-solid fa-circle-check"></i></span>
                <div>
                    <span>Đang kích hoạt</span>
                    <strong><?php echo e(number_format($summary['active'] ?? 0)); ?></strong>
                </div>
            </article>

            <article class="genre-stat-card">
                <span class="genre-stat-icon is-muted"><i class="fa-solid fa-circle-pause"></i></span>
                <div>
                    <span>Tạm ẩn</span>
                    <strong><?php echo e(number_format($summary['inactive'] ?? 0)); ?></strong>
                </div>
            </article>

            <article class="genre-stat-card">
                <span class="genre-stat-icon is-linked"><i class="fa-solid fa-link"></i></span>
                <div>
                    <span>Có phim liên kết</span>
                    <strong><?php echo e(number_format($summary['with_movies'] ?? 0)); ?></strong>
                </div>
            </article>
        </section>

        <form action="<?php echo e(route('admin.the-loais.index')); ?>" method="GET" class="genre-filter-panel">
            <label class="genre-filter-field">
                <span>Tìm kiếm</span>
                <div class="genre-filter-control">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input
                        type="text"
                        name="search"
                        value="<?php echo e(request('search')); ?>"
                        placeholder="Nhập tên thể loại, ví dụ: Hành động..."
                    >
                </div>
            </label>

            <label class="genre-filter-field is-status">
                <span>Trạng thái</span>
                <div class="genre-filter-control">
                    <i class="fa-solid fa-sliders"></i>
                    <select name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="1" <?php if(request('status') === '1'): echo 'selected'; endif; ?>>Đang kích hoạt</option>
                        <option value="0" <?php if(request('status') === '0'): echo 'selected'; endif; ?>>Tạm ẩn</option>
                    </select>
                </div>
            </label>

            <div class="genre-filter-actions">
                <button type="submit" class="movie-action-btn is-primary">
                    <i class="fa-solid fa-filter"></i>
                    Lọc dữ liệu
                </button>
                <a href="<?php echo e(route('admin.the-loais.index')); ?>" class="movie-action-btn is-soft">
                    <i class="fa-solid fa-rotate-left"></i>
                    Đặt lại
                </a>
            </div>
        </form>

        <section class="genre-table-panel">
            <div class="genre-table-head">
                <div>
                    <span class="genre-kicker">
                        <i class="fa-solid fa-list-check"></i>
                        Bảng quản lý
                    </span>
                    <h2>Thể loại phim</h2>
                </div>
                <div class="genre-result-count">
                    <?php echo e(number_format($theLoais->total())); ?> kết quả
                </div>
            </div>

            <div class="genre-table-wrap">
                <table class="genre-admin-table">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Thể loại</th>
                            <th>Mô tả</th>
                            <th>Slug</th>
                            <th>Số phim</th>
                            <th>Trạng thái</th>
                            <th class="is-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $theLoais; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $theLoai): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="genre-index-cell">
                                    #<?php echo e(($theLoais->currentPage() - 1) * $theLoais->perPage() + $key + 1); ?>

                                </td>
                                <td>
                                    <div class="genre-title-cell">
                                        <span class="genre-icon">
                                            <i class="fa-solid fa-ticket"></i>
                                        </span>
                                        <div class="genre-title-copy">
                                            <strong><?php echo e($theLoai->ten_the_loai); ?></strong>
                                            <small>ID <?php echo e($theLoai->id); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <p class="genre-desc">
                                        <?php echo e(\Illuminate\Support\Str::limit($theLoai->mo_ta ?: 'Chưa có mô tả cho thể loại này.', 86)); ?>

                                    </p>
                                </td>
                                <td>
                                    <code class="genre-slug"><?php echo e($theLoai->slug ?: 'chua-co-slug'); ?></code>
                                </td>
                                <td>
                                    <span class="genre-movie-count <?php echo e($theLoai->phims_count > 0 ? 'has-movies' : ''); ?>">
                                        <i class="fa-solid fa-film"></i>
                                        <?php echo e(number_format($theLoai->phims_count)); ?>

                                    </span>
                                </td>
                                <td>
                                    <?php if($theLoai->trang_thai): ?>
                                        <span class="genre-status is-active">
                                            <i class="fa-solid fa-circle-check"></i>
                                            Đang bật
                                        </span>
                                    <?php else: ?>
                                        <span class="genre-status is-inactive">
                                            <i class="fa-solid fa-circle-pause"></i>
                                            Tạm ẩn
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="genre-row-actions">
                                        <a
                                            href="<?php echo e(route('admin.the-loais.edit', $theLoai)); ?>"
                                            class="movie-icon-btn is-edit"
                                            title="Chỉnh sửa thể loại"
                                            aria-label="Chỉnh sửa <?php echo e($theLoai->ten_the_loai); ?>"
                                        >
                                            <i class="fa-solid fa-pen"></i>
                                        </a>

                                        <?php if($theLoai->phims_count > 0): ?>
                                            <button
                                                type="button"
                                                class="movie-icon-btn is-delete genre-delete-disabled"
                                                title="Không thể xóa vì đang có phim liên kết"
                                                aria-label="Không thể xóa <?php echo e($theLoai->ten_the_loai); ?>"
                                                disabled
                                            >
                                                <i class="fa-solid fa-lock"></i>
                                            </button>
                                        <?php else: ?>
                                            <form action="<?php echo e(route('admin.the-loais.destroy', $theLoai)); ?>" method="POST">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button
                                                    type="submit"
                                                    onclick="return confirm('Bạn có chắc muốn xóa thể loại này?')"
                                                    class="movie-icon-btn is-delete"
                                                    title="Xóa thể loại"
                                                    aria-label="Xóa <?php echo e($theLoai->ten_the_loai); ?>"
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
                                    <div class="genre-empty-state">
                                        <span><i class="fa-solid fa-folder-open"></i></span>
                                        <h3>Chưa tìm thấy thể loại phù hợp</h3>
                                        <p>Thử đổi từ khóa, bỏ bộ lọc trạng thái hoặc tạo thể loại mới cho kho phim.</p>
                                        <a href="<?php echo e(route('admin.the-loais.create')); ?>" class="movie-action-btn is-primary">
                                            <i class="fa-solid fa-plus"></i>
                                            Thêm thể loại
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="genre-pagination">
                <?php echo $__env->make('components.admin-pagination', ['paginator' => $theLoais], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </section>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/admin/the-loais/index.blade.php ENDPATH**/ ?>