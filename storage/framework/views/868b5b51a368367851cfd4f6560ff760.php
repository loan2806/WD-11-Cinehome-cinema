

<?php $__env->startSection('page-title', 'Chỉnh sửa món'); ?>
<?php $__env->startSection('page-subtitle', 'Cập nhật thông tin món và biến thể'); ?>

<?php $__env->startSection('content'); ?>

    <?php echo $__env->make('admin.partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-5">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-lg font-black text-white">Chỉnh sửa <?php echo e($food->name); ?></h3>
                </div>
                <a href="<?php echo e(route('admin.foods.index')); ?>" class="btn-admin-outline">Quay lại danh sách</a>
            </div>
        </div>

        <form method="POST" action="<?php echo e(route('admin.foods.update', $food)); ?>" enctype="multipart/form-data"
            class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-6 space-y-6">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PATCH'); ?>

            <div class="grid gap-4 lg:grid-cols-2">
                <div>
                    <label class="text-xs uppercase tracking-wider text-gray-400">Tên sản phẩm</label>
                    <input name="name"
                        value="<?php echo e(old('name') !== null && old('name') !== '' ? old('name') : $food->name); ?>"
                        class="admin-input" placeholder="Tên sản phẩm">
                    <?php $__errorArgs = ['name'];
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
                    <label class="text-xs uppercase tracking-wider text-gray-400">Danh mục</label>
                    <select class="admin-input" disabled>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($category->id); ?>" <?php echo e($food->category_id == $category->id ? 'selected' : ''); ?>>
                                <?php echo e($category->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <p class="mt-2 text-xs text-yellow-400">
                        Danh mục không thể thay đổi sau khi tạo món.
                    </p>

                    <input type="hidden" name="category_id" value="<?php echo e($food->category_id); ?>">
                    <?php $__errorArgs = ['category_id'];
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
                    <label class="text-xs uppercase tracking-wider text-gray-400 mb-2 block">Ảnh sản phẩm</label>
                    <label for="image"
                        class="group relative flex h-56 w-full cursor-pointer items-center justify-center rounded-2xl border-2 border-dashed border-white/20 bg-[#181818] hover:border-red-500 transition overflow-hidden">
                        <div id="preview-container" class="absolute inset-0 hidden">
                            <img id="preview-image" class="h-full w-full object-cover">
                        </div>

                        <?php if($food->image): ?>
                            <img id="current-image" src="<?php echo e(asset('storage/foods/' . $food->image)); ?>"
                                class="absolute inset-0 h-full w-full object-cover rounded-2xl">
                        <?php else: ?>
                            <div id="upload-placeholder"
                                class="relative flex flex-col items-center text-gray-400 group-hover:text-red-500 transition">
                                <div class="text-6xl font-light leading-none">+</div>
                                <p class="mt-2 text-sm">Chọn ảnh sản phẩm</p>
                            </div>
                        <?php endif; ?>

                        <input id="image" type="file" name="image" accept="image/*" class="hidden">
                    </label>
                    <?php $__errorArgs = ['image'];
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
            </div>

            <div>
                <label class="text-xs uppercase tracking-wider text-gray-400">Mô tả</label>
                <textarea name="description" class="admin-input" rows="5" placeholder="Mô tả sản phẩm"><?php echo e(old('description') !== null && old('description') !== '' ? old('description') : $food->description); ?></textarea>
                <?php $__errorArgs = ['description'];
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

            <?php if(!str_contains(strtolower(optional($food->category)->name), 'combo')): ?>
                <div class="rounded-3xl border border-white/10 bg-white/5 p-4 space-y-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h4 class="text-sm font-black text-white">Biến thể</h4>
                            <p class="text-xs text-gray-400">Thêm hoặc sửa các biến thể đã tạo.</p>
                        </div>
                        <a href="<?php echo e(route('admin.foods.variants.create', $food)); ?>"
                            class="btn-admin-outline text-xs px-3 py-2">
                            Thêm biến thể
                        </a>
                    </div>

                    <?php if($food->variants->isEmpty()): ?>
                        <div class="rounded-3xl border border-dashed border-white/10 p-4 text-gray-400">
                            Chưa có biến thể nào.
                        </div>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php $__currentLoopData = $food->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div
                                    class="rounded-3xl border border-white/10 bg-[#181818] p-4 grid gap-3 sm:grid-cols-[1fr_auto] items-center">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2 mb-2">
                                            <span
                                                class="rounded-full bg-[#d99a32]/20 px-3 py-1 text-xs font-bold text-[#f4c56a]"><?php echo e($variant->value); ?></span>
                                            <span
                                                class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-gray-300"><?php echo e(number_format($variant->price)); ?>đ</span>
                                            <span
                                                class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-gray-300">Tồn
                                                kho: <?php echo e($variant->stock_quantity); ?></span>
                                        </div>
                                        <p class="text-xs text-gray-400">
                                            Trạng thái: <?php echo e($variant->is_active ? 'Đang bán' : 'Tạm ẩn'); ?>

                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <a href="<?php echo e(route('admin.foods.variants.edit', [$food, $variant])); ?>"
                                            class="btn-admin-outline text-xs px-3 py-2">Sửa</a>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if(str_contains(strtolower(optional($food->category)->name), 'combo')): ?>

                <div class="rounded-3xl border border-white/10 bg-white/5 p-4 space-y-4">

                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-black text-white">
                                Thành phần combo
                            </h4>

                            <p class="text-xs text-gray-400">
                                Các biến thể có trong combo.
                            </p>
                        </div>


                    </div>

                    <div id="combo-item-list" class="space-y-3">
                        <?php
                            $comboItems = collect(
                                old(
                                    'combo_items',
                                    $food->comboItems
                                        ->map(function ($item) {
                                            return [
                                                'food_variant_id' => $item->food_variant_id,
                                                'quantity' => $item->quantity,
                                            ];
                                        })
                                        ->toArray(),
                                ),
                            )->map(function ($item) {
                                return [
                                    'food_variant_id' => $item['food_variant_id'] ?? null,
                                    'quantity' => $item['quantity'] ?? 1,
                                ];
                            });
                        ?>

                        <?php $__empty_1 = true; $__currentLoopData = $comboItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="grid grid-cols-12 gap-3 combo-row">

                                
                                <div class="col-span-8">
                                    <select name="combo_items[<?php echo e($index); ?>][variant_id]"
                                        class="admin-input
                    <?php $__errorArgs = ["combo_items.$index.variant_id"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                        <?php $__currentLoopData = $variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($variant->id); ?>"
                                                <?php echo e($variant->id == ($item['food_variant_id'] ?? null) ? 'selected' : ''); ?>>
                                                <?php echo e($variant->food->name); ?> (<?php echo e($variant->value); ?>)
                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>

                                    <?php $__errorArgs = ["combo_items.$index.variant_id"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <small class="text-red-500 block mt-1"><?php echo e($message); ?></small>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                
                                <div class="col-span-3">
                                    <input type="number" min="1" name="combo_items[<?php echo e($index); ?>][quantity]"
                                        value="<?php echo e($item['quantity']); ?>"
                                        class="admin-input
                    <?php $__errorArgs = ["combo_items.$index.quantity"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">

                                    <?php $__errorArgs = ["combo_items.$index.quantity"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <small class="text-red-500 block mt-1"><?php echo e($message); ?></small>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                
                                <div class="col-span-1 flex items-end">
                                    <button type="button" class="remove-combo-row btn-admin-outline w-full">
                                        ✕
                                    </button>
                                </div>

                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="rounded-3xl border border-dashed border-white/10 p-4 text-gray-400">
                                Combo chưa có thành phần.
                            </div>
                        <?php endif; ?>

                    </div>

                    <button type="button" id="add-combo-item" class="btn-admin-outline">
                        + Thêm dòng
                    </button>

                    <template id="combo-item-template">

                        <div class="grid grid-cols-12 gap-3 combo-row">

                            <div class="col-span-8">

                                <select name="combo_items[__index__][variant_id]" class="admin-input">

                                    <?php $__currentLoopData = $variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($variant->id); ?>">
                                            <?php echo e($variant->food->name); ?>

                                            (<?php echo e($variant->value); ?>)
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                </select>

                            </div>

                            <div class="col-span-3">

                                <input type="number" min="1" value="1"
                                    name="combo_items[__index__][quantity]" class="admin-input">

                            </div>

                            <div class="col-span-1 flex items-end">

                                <button type="button" class="remove-combo-row btn-admin-outline w-full">

                                    ✕

                                </button>

                            </div>

                        </div>

                    </template>
                </div>

            <?php endif; ?>

            <input type="hidden" name="is_active" value="0">
            <label class="flex items-center gap-2 text-sm text-gray-300">
                <input type="checkbox" name="is_active" value="1"
                    <?php echo e(old('is_active', $food->is_active) ? 'checked' : ''); ?>>
                Hiển thị menu
            </label>

            <button class="btn-admin w-full">Lưu thay đổi</button>
        </form>
    </div>

<?php $__env->stopSection(); ?>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const addBtn = document.getElementById('add-combo-item');
    if (!addBtn) return;

    const list = document.getElementById('combo-item-list');
    const template = document.getElementById('combo-item-template').innerHTML;

    let index = Date.now();

    addBtn.addEventListener('click', function () {
        let html = template.replaceAll('__index__', index);
        list.insertAdjacentHTML('beforeend', html);
        index++;
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-combo-row')) {
            e.target.closest('.combo-row').remove();
        }
    });

});
</script>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\WD-11-Cinehome-cinema\resources\views/admin/foods/edit.blade.php ENDPATH**/ ?>