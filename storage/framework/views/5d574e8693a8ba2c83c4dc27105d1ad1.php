<?php $__env->startSection('title', 'Chi tiết món'); ?>
<?php $__env->startSection('page-title', 'Chi tiết món'); ?>
<?php $__env->startSection('page-subtitle', 'Xem thông tin món, danh mục, giá bán, tồn kho và cấu hình combo'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $isCombo = $food->isCombo();
    $stock = $food->stock_quantity;
    $isLowStock = $stock <= $food->min_stock_quantity;
    $imagePath = $food->image
        ? asset('storage/' . (str_starts_with($food->image, 'foods/') ? $food->image : 'foods/' . $food->image))
        : null;
?>

<div class="food-show-page">
    <?php echo $__env->make('admin.partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <section class="food-show-hero">
        <div class="food-show-media">
            <?php if($imagePath): ?>
                <img src="<?php echo e($imagePath); ?>" alt="<?php echo e($food->name); ?>">
            <?php else: ?>
                <i class="fa-solid fa-burger"></i>
                <span>Chưa có ảnh</span>
            <?php endif; ?>
        </div>

        <div class="food-show-main">
            <div class="food-show-tags">
                <span class="food-show-status <?php echo e($food->is_active ? 'is-active' : 'is-hidden'); ?>">
                    <?php echo e($food->is_active ? 'Đang bán' : 'Tạm ẩn'); ?>

                </span>
                <span><?php echo e($isCombo ? 'Combo' : 'Món lẻ'); ?></span>
                <span><?php echo e(optional($food->category)->name ?? 'Chưa phân loại'); ?></span>
            </div>

            <h2><?php echo e($food->name); ?></h2>
            <p><?php echo e($food->description ?: 'Món này chưa có mô tả chi tiết.'); ?></p>

            <div class="food-show-code">
                <span>SKU</span>
                <strong><?php echo e($food->sku ?: 'Chưa có'); ?></strong>
            </div>

            <div class="food-show-actions">
                <a href="<?php echo e(route('admin.foods.index')); ?>" class="food-show-action is-soft">
                    <i class="fa-solid fa-arrow-left"></i>
                    Quay lại
                </a>
                <a href="<?php echo e(route('admin.foods.edit', $food)); ?>" class="food-show-action">
                    <i class="fa-solid fa-pen"></i>
                    Sửa món
                </a>
                <form method="POST" action="<?php echo e(route('admin.foods.toggle-status', $food)); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PATCH'); ?>
                    <button type="submit" class="food-show-action is-soft">
                        <i class="fa-solid <?php echo e($food->is_active ? 'fa-eye-slash' : 'fa-eye'); ?>"></i>
                        <?php echo e($food->is_active ? 'Ẩn món' : 'Hiện món'); ?>

                    </button>
                </form>
            </div>
        </div>
    </section>

    <section class="food-show-stats">
        <div class="food-show-stat">
            <span>Giá bán</span>
            <strong><?php echo e(number_format((float) $food->price, 0, ',', '.')); ?>đ</strong>
        </div>
        <div class="food-show-stat <?php echo e($isLowStock ? 'is-warn' : 'is-good'); ?>">
            <span>Tồn kho khả dụng</span>
            <strong><?php echo e($stock); ?></strong>
        </div>
        <div class="food-show-stat">
            <span><?php echo e($isCombo ? 'Thành phần' : 'Biến thể'); ?></span>
            <strong><?php echo e($isCombo ? $food->comboItems->count() : $food->variants->count()); ?></strong>
        </div>
        <div class="food-show-stat">
            <span>Đã bán</span>
            <strong><?php echo e($food->invoice_items_count); ?></strong>
        </div>
    </section>

    <div class="food-show-grid">
        <section class="food-show-panel">
            <div class="food-show-panel-head">
                <div>
                    <span class="food-show-eyebrow">Thông tin</span>
                    <h3>Thông tin món</h3>
                </div>
            </div>

            <div class="food-show-info-list">
                <div>
                    <span>Danh mục</span>
                    <strong><?php echo e(optional($food->category)->name ?? 'Chưa phân loại'); ?></strong>
                </div>
                <div>
                    <span>Loại món</span>
                    <strong><?php echo e($isCombo ? 'Combo' : 'Sản phẩm thường'); ?></strong>
                </div>
                <div>
                    <span>Trạng thái</span>
                    <strong><?php echo e($food->is_active ? 'Đang bán' : 'Tạm ẩn'); ?></strong>
                </div>
                <div>
                    <span>Thứ tự sắp xếp</span>
                    <strong><?php echo e($food->sort_order ?? 0); ?></strong>
                </div>
                <div>
                    <span>Ngày tạo</span>
                    <strong><?php echo e($food->created_at?->format('d/m/Y H:i')); ?></strong>
                </div>
                <div>
                    <span>Cập nhật gần nhất</span>
                    <strong><?php echo e($food->updated_at?->format('d/m/Y H:i')); ?></strong>
                </div>
            </div>
        </section>

        <?php if(! $isCombo): ?>
            <section class="food-show-panel">
                <div class="food-show-panel-head">
                    <div>
                        <span class="food-show-eyebrow">Kho & giá</span>
                        <h3>Biến thể món</h3>
                    </div>
                    <a href="<?php echo e(route('admin.foods.variants.index', $food)); ?>" class="food-show-small-btn">
                        <i class="fa-solid fa-sliders"></i>
                        Quản lý
                    </a>
                </div>

                <div class="food-show-variant-list">
                    <?php $__empty_1 = true; $__currentLoopData = $food->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <article class="food-show-variant-card <?php echo e(! $variant->is_active ? 'is-inactive' : ''); ?>">
                            <div>
                                <span>Biến thể</span>
                                <strong><?php echo e($variant->value ?: 'Mặc định'); ?></strong>
                            </div>
                            <div>
                                <span>Giá</span>
                                <strong><?php echo e(number_format((float) $variant->price, 0, ',', '.')); ?>đ</strong>
                            </div>
                            <div class="<?php echo e($variant->stock_quantity <= $food->min_stock_quantity ? 'is-low' : ''); ?>">
                                <span>Tồn kho</span>
                                <strong><?php echo e($variant->stock_quantity); ?></strong>
                            </div>
                            <div>
                                <span>Trạng thái</span>
                                <strong><?php echo e($variant->is_active ? 'Đang bán' : 'Tạm ẩn'); ?></strong>
                            </div>
                            <a href="<?php echo e(route('admin.foods.variants.edit', [$food, $variant])); ?>" class="food-show-mini-action">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="food-show-empty">
                            <i class="fa-solid fa-box-open"></i>
                            <h3>Chưa có biến thể</h3>
                            <p>Thêm biến thể để quản lý giá bán và tồn kho cho món này.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php else: ?>
            <section class="food-show-panel">
                <div class="food-show-panel-head">
                    <div>
                        <span class="food-show-eyebrow">Combo</span>
                        <h3>Thành phần combo</h3>
                    </div>
                    <a href="<?php echo e(route('admin.foods.edit', $food)); ?>" class="food-show-small-btn">
                        <i class="fa-solid fa-pen"></i>
                        Chỉnh sửa
                    </a>
                </div>

                <div class="food-show-combo-list">
                    <?php $__empty_1 = true; $__currentLoopData = $food->comboItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $variant = $item->variant;
                            $baseFood = $variant?->doAn;
                        ?>
                        <article class="food-show-combo-card">
                            <div>
                                <span>Món thành phần</span>
                                <strong><?php echo e($baseFood?->name ?? 'Không rõ món'); ?></strong>
                                <small><?php echo e($variant?->value ?: 'Mặc định'); ?> · <?php echo e(optional($baseFood?->category)->name ?? 'Chưa phân loại'); ?></small>
                            </div>
                            <div>
                                <span>Số lượng trong combo</span>
                                <strong>x<?php echo e($item->quantity); ?></strong>
                            </div>
                            <div class="<?php echo e((int) ($variant?->stock_quantity ?? 0) <= $food->min_stock_quantity ? 'is-low' : ''); ?>">
                                <span>Tồn kho biến thể</span>
                                <strong><?php echo e((int) ($variant?->stock_quantity ?? 0)); ?></strong>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="food-show-empty">
                            <i class="fa-solid fa-box-open"></i>
                            <h3>Combo chưa có thành phần</h3>
                            <p>Thêm thành phần để combo có thể bán và tính tồn kho chính xác.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\DATN\WD-11-Cinehome-cinema\resources\views/admin/foods/show.blade.php ENDPATH**/ ?>