

<?php $__env->startSection('page-title', 'Quản lý biến thể'); ?>
<?php $__env->startSection('page-subtitle', 'Quản lý các kích cỡ, dung tích và giá bán'); ?>

<?php $__env->startSection('content'); ?>

<?php echo $__env->make('admin.partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="admin-panel">

    
    <div class="panel-header flex items-center justify-between">
        <div>
            <h5><?php echo e($food->name); ?></h5>
            <small>
                Tổng <?php echo e($variants->total()); ?> biến thể
            </small>
        </div>

       <a href="<?php echo e(route('admin.foods.index')); ?>"
   class="btn-admin-outline inline-flex items-center gap-2">
    <i class="fa-solid fa-arrow-left"></i>
    Quay lại
</a>
    </div>

    
    <div class="space-y-4 p-5">

        <?php $__empty_1 = true; $__currentLoopData = $variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

            <div class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-5">

                <div class="grid gap-4 lg:grid-cols-[1fr_auto]">

                    <div>

                        <div class="mb-3 flex flex-wrap items-center gap-2">

                            <span class="rounded-full bg-[#d99a32]/20 px-3 py-1 text-xs font-bold text-[#f4c56a]">
                                <?php echo e($variant->value); ?>

                            </span>

                            <?php if($variant->is_active): ?>
                                <span class="rounded-full border border-green-500/30 bg-green-500/10 px-3 py-1 text-xs font-bold text-green-300">
                                    Hiển thị
                                </span>
                            <?php else: ?>
                                <span class="rounded-full border border-red-500/30 bg-red-500/10 px-3 py-1 text-xs font-bold text-red-300">
                                    Tạm ẩn
                                </span>
                            <?php endif; ?>

                        </div>

                        <div class="grid gap-3 md:grid-cols-3">

                            <div>
                                <p class="text-xs text-gray-500">
                                    Giá bán
                                </p>

                                <p class="text-lg font-black text-[#f4c56a]">
                                    <?php echo e(number_format($variant->price)); ?>đ
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500">
                                    Tồn kho
                                </p>

                                <p class="text-lg font-black text-white">
                                    <?php echo e($variant->stock_quantity); ?>

                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500">
                                    ID
                                </p>

                                <p class="text-lg font-black text-white">
                                    #<?php echo e($variant->id); ?>

                                </p>
                            </div>

                        </div>

                    </div>

                    <div class="flex items-center gap-2">

                        <a href="<?php echo e(route('admin.foods.variants.edit', [$food, $variant])); ?>"
                          class="h-10 w-10 flex items-center justify-center rounded-xl bg-yellow-500/15 text-yellow-300">
                                        <i class="fa-solid fa-pen"></i>
                        </a>

                        <form
                            action="<?php echo e(route('admin.foods.variants.destroy',[$food,$variant])); ?>"
                            method="POST"
                            onsubmit="return confirm('Xóa biến thể này?')">

                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>

                            <button onclick="return confirm('Xóa biến thể này?')"
                                            class="h-10 w-10 rounded-xl bg-red-500/15 text-red-300">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>

                        </form>

                    </div>

                </div>

            </div>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

            <div class="rounded-3xl border border-dashed border-white/10 p-10 text-center text-gray-400">
                Chưa có biến thể nào.
            </div>

        <?php endif; ?>

    </div>

    <div class="border-t border-white/10 p-4">
        <?php echo e($variants->links()); ?>

    </div>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\WD-11-Cinehome-cinema\resources\views/admin/foods/variants/index.blade.php ENDPATH**/ ?>