

<?php $__env->startSection('page-title', 'Chỉnh sửa biến thể'); ?>
<?php $__env->startSection('page-subtitle', $food->name); ?>

<?php $__env->startSection('content'); ?>

<div class="max-w-3xl mx-auto">

    <?php echo $__env->make('admin.partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="admin-panel">

        <div class="panel-header flex items-center justify-between">
            <div>
                <h5>Chỉnh sửa biến thể cho <?php echo e($food->name); ?></h5>
                <p class="text-xs text-gray-400"><?php echo e($variant->value); ?></p>
            </div>
        </div>

        <form method="POST"
              action="<?php echo e(route('admin.foods.variants.update', [$food, $variant])); ?>"
              class="panel-body space-y-4">

            <?php echo csrf_field(); ?>
            <?php echo method_field('PATCH'); ?>

            <div>
                <label>Tên biến thể</label>
                <input
                    type="text"
                    name="value"
                    class="admin-input"
                    placeholder="Ví dụ: Size L, 500ml..."
                    value="<?php echo e(old('value') !== null && old('value') !== '' ? old('value') : $variant->value); ?>">

                <?php $__errorArgs = ['value'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <small class="text-red-500"><?php echo e($message); ?></small>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div>
                <label>Giá</label>
                <input
                    type="number"
                    min="0"
                    name="price"
                    class="admin-input"
                    value="<?php echo e(old('price') !== null && old('price') !== '' ? old('price') : $variant->price); ?>">

                <?php $__errorArgs = ['price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <small class="text-red-500"><?php echo e($message); ?></small>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div>
                <label>Tồn kho</label>
                <input
                    type="number"
                    min="0"
                    name="stock_quantity"
                    class="admin-input"
                    value="<?php echo e(old('stock_quantity') !== null && old('stock_quantity') !== '' ? old('stock_quantity') : $variant->stock_quantity); ?>">

                <?php $__errorArgs = ['stock_quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <small class="text-red-500"><?php echo e($message); ?></small>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <input type="hidden" name="is_active" value="0">
            <label class="flex items-center gap-2">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    <?php echo e(old('is_active', $variant->is_active) ? 'checked' : ''); ?>>
                Đang bán
            </label>

            <div class="flex gap-3">
                <button class="btn-admin" type="submit">Lưu</button>
                <a href="<?php echo e(route('admin.foods.edit', $food)); ?>" class="btn-admin-outline">Quay lại</a>
            </div>

        </form>

    </div>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\WD-11-Cinehome-cinema\resources\views/admin/foods/variants/edit.blade.php ENDPATH**/ ?>