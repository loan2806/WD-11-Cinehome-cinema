

<?php $__env->startSection('page-title', 'Chi tiết món'); ?>
<?php $__env->startSection('page-subtitle', 'Xem thông tin món, danh mục và biến thể'); ?>

<?php $__env->startSection('content'); ?>

    <?php echo $__env->make('admin.partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="max-w-4xl mx-auto space-y-6">
        <div
            class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-5 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h3 class="text-lg font-black text-white"><?php echo e($food->name); ?></h3>
                <p class="text-xs text-gray-400">
                    SKU: <?php echo e($food->sku ?? 'Không có'); ?>

                    · Danh mục:
                    <?php echo e(optional($food->category)->name); ?>

                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="<?php echo e(route('admin.foods.index')); ?>" class="btn-admin-outline">Quay lại</a>
                <a href="<?php echo e(route('admin.foods.edit', $food)); ?>" class="btn-admin">Sửa món</a>
            </div>
        </div>

        <div class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-6 grid gap-6 lg:grid-cols-[260px_1fr]">
            <div class="rounded-3xl overflow-hidden bg-white/5">
                <?php if($food->image): ?>
                    <img src="<?php echo e(asset('storage/foods/' . $food->image)); ?>" class="h-full w-full object-cover">
                <?php else: ?>
                    <div class="flex h-full min-h-[260px] items-center justify-center text-gray-400">
                        Chưa có ảnh
                    </div>
                <?php endif; ?>
            </div>

            <div class="space-y-4">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl border border-white/10 bg-[#101010] p-4">
                        <p class="text-xs text-gray-400">Trạng thái</p>
                        <p class="text-lg font-black text-white"><?php echo e($food->is_active ? 'Đang bán' : 'Tạm ẩn'); ?></p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-[#101010] p-4">
                        <?php
                            $isCombo = str_contains(strtolower(optional($food->category)->name), 'combo');
                        ?>

                        <div class="space-y-4">
                                
                                    <p class="text-xs text-gray-400">
                                        <?php echo e($isCombo ? 'Tổng thành phần' : 'Tổng biến thể'); ?>

                                    </p>

                                    <p class="text-lg font-black text-white">
                                        <?php echo e($isCombo ? $food->comboItems->count() : $food->variants->count()); ?>

                                    </p>

                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-white/10 bg-[#101010] p-4">
                    <p class="text-xs text-gray-400">Mô tả</p>
                    <p class="mt-2 text-sm text-gray-200"><?php echo e($food->description ?? 'Không có mô tả'); ?></p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-[#101010] p-4">
                    <p class="text-xs text-gray-400">Thông tin bổ sung</p>
                    <ul class="mt-3 space-y-2 text-sm text-gray-200">
                        <li>
                            Danh mục:
                            <?php echo e(optional($food->category)->name); ?>

                        </li>

                        <li>
                            Loại:
                            <?php echo e(str_contains(strtolower(optional($food->category)->name), 'combo') ? 'Combo' : 'Sản phẩm thường'); ?>

                        </li>
                        <li>Thêm vào: <?php echo e($food->created_at->format('d/m/Y H:i')); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php if(!str_contains(strtolower(optional($food->category)->name), 'combo')): ?>
            <div class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="text-lg font-black text-white">Biến thể</h4>
                        <p class="text-xs text-gray-400">Danh sách giá và tồn kho.</p>
                    </div>

                    <a href="<?php echo e(route('admin.foods.variants.index', $food)); ?>" class="btn-admin-outline text-xs px-3 py-2">
                        Quản lý biến thể
                    </a>
                </div>

                <?php if($food->variants->isEmpty()): ?>

                    <div class="text-center text-gray-400 py-8">
                        Chưa có biến thể.
                    </div>
                <?php else: ?>
                    <div class="space-y-3">

                        <?php $__currentLoopData = $food->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="rounded-2xl border border-white/10 bg-[#101010] p-4 flex justify-between">

                                <div>
                                    <div class="font-semibold">
                                        <?php echo e($variant->value); ?>

                                    </div>

                                    <div class="text-gray-400 text-sm">
                                        Giá: <?php echo e(number_format($variant->price)); ?>đ
                                    </div>

                                    <div class="text-gray-400 text-sm">
                                        Tồn kho: <?php echo e($variant->stock_quantity); ?>

                                    </div>
                                </div>

                                <div class="text-sm">
                                    <?php echo e($variant->is_active ? 'Đang bán' : 'Tạm ẩn'); ?>

                                </div>

                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    </div>

                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if(str_contains(strtolower(optional($food->category)->name), 'combo')): ?>

            <div class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-6">

                <h4 class="text-lg font-black text-white mb-4">
                    Thành phần combo
                </h4>

                <?php $__empty_1 = true; $__currentLoopData = $food->comboItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="rounded-2xl border border-white/10 bg-[#101010] p-4 mb-3 flex justify-between">

                        <div>

                            <div>
                                <div class="font-semibold">
                                    <?php echo e($item->variant->food->name); ?>

                                    (<?php echo e($item->variant->value); ?>)
                                </div>

                                <div class="text-gray-400 text-sm">
                                    <?php echo e(optional($item->variant->food->category)->name); ?>

                                </div>
                            </div>

                        </div>

                        <div class="font-bold">
                            x<?php echo e($item->quantity); ?>

                        </div>

                    </div>

                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                    <div class="text-center text-gray-400">
                        Combo chưa có thành phần.
                    </div>
                <?php endif; ?>

            </div>

        <?php endif; ?>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\WD-11-Cinehome-cinema\resources\views/admin/foods/show.blade.php ENDPATH**/ ?>