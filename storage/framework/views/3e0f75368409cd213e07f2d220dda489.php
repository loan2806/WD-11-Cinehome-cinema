<?php $__env->startSection('title', 'Cấu hình Menu & Kho hàng'); ?>
<?php $__env->startSection('page-title', 'Cấu hình Menu & Kho hàng'); ?>
<?php $__env->startSection('page-subtitle', 'Quản lý món, giá bán, tồn kho và trạng thái hiển thị'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $activeFilters = collect([
        request('q'),
        request('category_id'),
        request('status'),
    ])->filter()->count();

    $statusOptions = [
        'active' => 'Đang bán',
        'inactive' => 'Tạm ẩn',
        'low' => 'Sắp hết hàng',
    ];
?>

<div class="food-menu-page">
    <?php echo $__env->make('admin.partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <section class="food-menu-hero">
        <div>
            <span class="food-menu-eyebrow">
                <i class="fa-solid fa-boxes-stacked"></i>
                Menu & kho hàng
            </span>
            <h2>Quản lý đồ ăn, nước uống và combo</h2>
            <p>Theo dõi giá bán, tồn kho, trạng thái hiển thị và thao tác nhanh với từng món tại quầy.</p>
        </div>

        <div class="food-menu-actions">
            <a href="<?php echo e(route('admin.foods.categories.index')); ?>" class="food-menu-btn is-soft">
                <i class="fa-solid fa-layer-group"></i>
                Danh mục
            </a>
            <a href="<?php echo e(route('admin.foods.create')); ?>" class="food-menu-btn">
                <i class="fa-solid fa-plus"></i>
                Thêm món
            </a>
        </div>
    </section>

    <section class="food-menu-stats">
        <div class="food-menu-stat">
            <span>Tổng món</span>
            <strong><?php echo e($summary['total']); ?></strong>
        </div>
        <div class="food-menu-stat is-good">
            <span>Đang bán</span>
            <strong><?php echo e($summary['active']); ?></strong>
        </div>
        <div class="food-menu-stat is-muted">
            <span>Tạm ẩn</span>
            <strong><?php echo e($summary['inactive']); ?></strong>
        </div>
        <div class="food-menu-stat is-warn">
            <span>Sắp hết hàng</span>
            <strong><?php echo e($summary['low_stock'] ?? 0); ?></strong>
        </div>
    </section>

    <section class="food-menu-panel">
        <div class="food-menu-panel-head">
            <div>
                <span class="food-menu-eyebrow">Danh sách</span>
                <h3>Món đang cấu hình</h3>
                <p><?php echo e($foods->total()); ?> kết quả theo bộ lọc hiện tại.</p>
            </div>
        </div>

        <form method="GET" action="<?php echo e(route('admin.foods.index')); ?>" class="food-menu-filter">
            <label class="food-menu-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="q" value="<?php echo e(request('q')); ?>" placeholder="Tìm món, SKU hoặc nhóm món...">
            </label>

            <select name="category_id" class="admin-input">
                <option value="">Tất cả nhóm món</option>
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($category->id); ?>" <?php if(request('category_id') == $category->id): echo 'selected'; endif; ?>>
                        <?php echo e($category->name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <select name="status" class="admin-input">
                <option value="">Tất cả trạng thái</option>
                <?php $__currentLoopData = $statusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($value); ?>" <?php if(request('status') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <button type="submit" class="food-menu-filter-btn">
                <i class="fa-solid fa-filter"></i>
                Lọc
                <?php if($activeFilters): ?>
                    <span><?php echo e($activeFilters); ?></span>
                <?php endif; ?>
            </button>

            <?php if($activeFilters): ?>
                <a href="<?php echo e(route('admin.foods.index')); ?>" class="food-menu-reset-btn" title="Xóa bộ lọc">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            <?php endif; ?>
        </form>

        <div class="food-menu-list">
            <?php $__empty_1 = true; $__currentLoopData = $foods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $food): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $isCombo = $food->isCombo();
                    $stock = $food->stock_quantity;
                    $isLowStock = $stock <= $food->min_stock_quantity;
                    $imagePath = $food->image
                        ? asset('storage/' . (str_starts_with($food->image, 'foods/') ? $food->image : 'foods/' . $food->image))
                        : null;
                ?>

                <article class="food-menu-card <?php echo e(! $food->is_active ? 'is-inactive' : ''); ?>">
                    <div class="food-menu-media">
                        <?php if($imagePath): ?>
                            <img src="<?php echo e($imagePath); ?>" alt="<?php echo e($food->name); ?>">
                        <?php else: ?>
                            <i class="fa-solid fa-burger"></i>
                        <?php endif; ?>
                    </div>

                    <div class="food-menu-info">
                        <div class="food-menu-tags">
                            <span class="food-menu-status <?php echo e($food->is_active ? 'is-active' : 'is-hidden'); ?>">
                                <?php echo e($food->is_active ? 'Đang bán' : 'Tạm ẩn'); ?>

                            </span>
                            <span><?php echo e($isCombo ? 'Combo' : 'Món lẻ'); ?></span>
                            <span><?php echo e(optional($food->category)->name ?? 'Chưa phân loại'); ?></span>
                        </div>

                        <h3><?php echo e($food->name); ?></h3>

                        <p>
                            SKU: <strong><?php echo e($food->sku ?: 'Chưa có'); ?></strong>
                            <?php if($food->description): ?>
                                <span><?php echo e(\Illuminate\Support\Str::limit($food->description, 90)); ?></span>
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="food-menu-metrics">
                        <div>
                            <span>Giá bán</span>
                            <strong><?php echo e(number_format((float) $food->price, 0, ',', '.')); ?>đ</strong>
                        </div>
                        <div class="<?php echo e($isLowStock ? 'is-low' : ''); ?>">
                            <span>Tồn kho</span>
                            <strong><?php echo e($stock); ?></strong>
                        </div>
                        <div>
                            <span><?php echo e($isCombo ? 'Thành phần' : 'Biến thể'); ?></span>
                            <strong><?php echo e($isCombo ? $food->comboItems->count() : $food->variants->count()); ?></strong>
                        </div>
                        <div>
                            <span>Đã bán</span>
                            <strong><?php echo e($food->invoice_items_count); ?></strong>
                        </div>
                    </div>

                    <div class="food-menu-card-actions">
                        <a href="<?php echo e(route('admin.foods.show', $food)); ?>" class="food-menu-icon-btn is-view">
                            <i class="fa-solid fa-eye"></i>
                            Xem
                        </a>

                        <a href="<?php echo e(route('admin.foods.edit', $food)); ?>" class="food-menu-icon-btn is-edit">
                            <i class="fa-solid fa-pen"></i>
                            Sửa
                        </a>

                        <form method="POST" action="<?php echo e(route('admin.foods.toggle-status', $food)); ?>">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PATCH'); ?>
                            <button type="submit" class="food-menu-icon-btn <?php echo e($food->is_active ? 'is-hide' : 'is-show'); ?>">
                                <i class="fa-solid <?php echo e($food->is_active ? 'fa-eye-slash' : 'fa-eye'); ?>"></i>
                                <?php echo e($food->is_active ? 'Ẩn' : 'Hiện'); ?>

                            </button>
                        </form>

                        <form method="POST" action="<?php echo e(route('admin.foods.destroy', $food)); ?>" onsubmit="return confirm('Xóa món <?php echo e($food->name); ?>?')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="food-menu-icon-btn is-delete">
                                <i class="fa-solid fa-trash"></i>
                                Xóa
                            </button>
                        </form>
                    </div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="food-menu-empty">
                    <i class="fa-solid fa-burger"></i>
                    <h3>Chưa có món phù hợp</h3>
                    <p>Thử đổi bộ lọc hoặc thêm món mới để bắt đầu cấu hình menu.</p>
                    <a href="<?php echo e(route('admin.foods.create')); ?>" class="food-menu-btn">
                        <i class="fa-solid fa-plus"></i>
                        Thêm món
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <div class="food-menu-pagination">
            <?php echo e($foods->links()); ?>

        </div>
    </section>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\DATN\WD-11-Cinehome-cinema\resources\views/admin/foods/index.blade.php ENDPATH**/ ?>