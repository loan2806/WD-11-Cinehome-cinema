

<?php $__env->startSection('page-title', 'Thêm món mới'); ?>
<?php $__env->startSection('page-subtitle', 'Tạo sản phẩm mới cho menu'); ?>

<?php $__env->startSection('content'); ?>

    <?php echo $__env->make('admin.partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-5">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-lg font-black text-white">Thêm món mới</h3>
                </div>
                <a href="<?php echo e(route('admin.foods.index')); ?>" class="btn-admin-outline">
                    Quay lại danh sách
                </a>
            </div>
        </div>

        <form method="POST" action="<?php echo e(route('admin.foods.store')); ?>" enctype="multipart/form-data"
            class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-6 space-y-6">
            <?php echo csrf_field(); ?>

            <div class="grid gap-4 lg:grid-cols-2">
                <div>
                    <label class="text-xs uppercase tracking-wider text-gray-400">Tên sản phẩm</label>
                    <input name="name" value="<?php echo e(old('name')); ?>" class="admin-input" placeholder="Tên sản phẩm">
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
                    <select name="category_id" id="category" class="admin-input">
                        <option value="">-- Chọn danh mục --</option>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($category->id); ?>" <?php echo e(old('category_id') == $category->id ? 'selected' : ''); ?>>
                                <?php echo e($category->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
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
                    <p id="combo-note" class="mt-2 text-xs text-gray-400 hidden">
                        Combo không quản lý biến thể riêng và không có tồn kho riêng cho combo. Tồn kho được quản lý theo
                        từng món thành phần.
                    </p>
                </div>

                <div>
                    <label class="text-xs uppercase tracking-wider text-gray-400 mb-2 block">
                        Ảnh sản phẩm
                    </label>

                    <label for="image"
                        class="group relative flex h-56 w-full cursor-pointer items-center justify-center rounded-2xl border-2 border-dashed border-white/20 bg-[#181818] hover:border-red-500 transition">

                        <div id="preview-container" class="hidden absolute inset-0">
                            <img id="preview-image" class="h-full w-full rounded-2xl object-cover">
                        </div>

                        <div id="upload-placeholder"
                            class="flex flex-col items-center text-gray-400 group-hover:text-red-500 transition">
                            <div class="text-6xl font-light leading-none">+</div>
                            <p class="mt-2 text-sm">Chọn ảnh sản phẩm</p>
                        </div>

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
                <textarea name="description" class="admin-input" rows="5" placeholder="Mô tả sản phẩm"><?php echo e(old('description')); ?></textarea>
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

            <div id="variant-section" class="rounded-3xl border border-white/10 bg-white/5 p-4 space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h4 class="text-sm font-black text-white">Biến thể sản phẩm</h4>
                        <p class="text-xs text-gray-400">Chỉ hiện khi chọn Đồ ăn hoặc Đồ uống.</p>
                    </div>
                    <button type="button" id="add-variant-btn" class="btn-admin-outline text-xs px-3 py-2">
                        Thêm biến thể
                    </button>
                </div>
                <?php $__errorArgs = ['variants'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <small class="text-red-500"><?php echo e($message); ?></small>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                <div id="variant-list" class="space-y-3">
                    <?php
                        $oldVariants = old('variants', []);
                        if (empty($oldVariants)) {
                            $oldVariants = [['value' => '', 'price' => '', 'stock_quantity' => '']];
                        }
                    ?>

                    <?php $__currentLoopData = $oldVariants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="grid gap-3 sm:grid-cols-3 items-end variant-row">
                            <div>
                                <label class="text-xs uppercase tracking-wider text-gray-400">Tên biến thể</label>
                                <input type="text" name="variants[<?php echo e($index); ?>][value]"
                                    value="<?php echo e(old('variants.' . $index . '.value')); ?>" class="admin-input"
                                    placeholder="Ví dụ: Size L, 500ml">
                                <?php $__errorArgs = ['variants.' . $index . '.value'];
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
                                <label class="text-xs uppercase tracking-wider text-gray-400">Giá</label>
                                <input type="number" min="0" name="variants[<?php echo e($index); ?>][price]"
                                    value="<?php echo e(old('variants.' . $index . '.price')); ?>" class="admin-input"
                                    placeholder="Giá">
                                <?php $__errorArgs = ['variants.' . $index . '.price'];
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
                            <div class="flex items-end gap-2">
                                <div class="flex-1">
                                    <label class="text-xs uppercase tracking-wider text-gray-400">Số lượng</label>
                                    <input type="number" min="0"
                                        name="variants[<?php echo e($index); ?>][stock_quantity]"
                                        value="<?php echo e(old('variants.' . $index . '.stock_quantity')); ?>" class="admin-input"
                                        placeholder="Số lượng">
                                    <?php $__errorArgs = ['variants.' . $index . '.stock_quantity'];
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
                                <?php if($index > 0): ?>
                                    <button type="button" class="remove-variant btn-admin-outline text-xs px-3 py-2">
                                        Xóa
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <template id="variant-row-template">
                    <div class="grid gap-3 sm:grid-cols-3 items-end variant-row">
                        <div>
                            <label class="text-xs uppercase tracking-wider text-gray-400">Tên biến thể</label>
                            <input type="text" name="variants[__index__][value]" class="admin-input"
                                placeholder="Ví dụ: Size L, 500ml">
                        </div>
                        <div>
                            <label class="text-xs uppercase tracking-wider text-gray-400">Giá</label>
                            <input type="number" min="0" name="variants[__index__][price]" class="admin-input"
                                placeholder="Giá">
                        </div>
                        <div class="flex items-end gap-2">
                            <div class="flex-1">
                                <label class="text-xs uppercase tracking-wider text-gray-400">Số lượng</label>
                                <input type="number" min="0" name="variants[__index__][stock_quantity]"
                                    class="admin-input" placeholder="Số lượng">
                            </div>
                            <button type="button" class="remove-variant btn-admin-outline text-xs px-3 py-2">
                                Xóa
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <div id="combo-section" class="hidden rounded-3xl border border-white/10 bg-white/5 p-4 space-y-4">

                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-black text-white">
                            Thành phần combo
                        </h4>
                        <p class="text-xs text-gray-400">
                            Chọn các món có trong combo.
                        </p>
                    </div>

                    <button type="button" id="add-combo-item" class="btn-admin-outline text-xs px-3 py-2">
                        + Thêm dòng
                    </button>
                </div>



                <div id="combo-item-list" class="space-y-3">

                    <?php
                        $oldComboItems = old('combo_items', []);

                        if (empty($oldComboItems)) {
                            $oldComboItems = [
                                [
                                    'variant_id' => '',
                                    'quantity' => 1,
                                ],
                                [
                                    'variant_id' => '',
                                    'quantity' => 1,
                                ],
                            ];
                        }
                    ?>

                    <?php $__currentLoopData = $oldComboItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="grid grid-cols-12 gap-3 combo-row">

                            <div class="col-span-8">

                                <label class="text-xs uppercase tracking-wider text-gray-400">
                                    Chọn món
                                </label>

                                <select name="combo_items[<?php echo e($index); ?>][variant_id]" class="admin-input">

                                    <option value="">-- Chọn biến thể --</option>

                                    <?php $__currentLoopData = $variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($variant->id); ?>"
                                            <?php echo e(old("combo_items.$index.variant_id") == $variant->id ? 'selected' : ''); ?>>
                                            <?php echo e($variant->food->name); ?> (<?php echo e($variant->value); ?>)
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                </select>

                                <?php $__errorArgs = ["combo_items.$index.variant_id"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <small class="text-red-500">
                                        <?php echo e($message); ?>

                                    </small>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                            </div>

                            <div class="col-span-3">

                                <label class="text-xs uppercase tracking-wider text-gray-400">
                                    SL
                                </label>

                                <input type="number" min="1" name="combo_items[<?php echo e($index); ?>][quantity]"
                                    value="<?php echo e(old("combo_items.$index.quantity", 1)); ?>" class="admin-input">

                                <?php $__errorArgs = ["combo_items.$index.quantity"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <small class="text-red-500">
                                        <?php echo e($message); ?>

                                    </small>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                            </div>

                            <div class="col-span-1 flex items-end">

                                <?php if($index > 0): ?>
                                    <button type="button" class="remove-combo-row btn-admin-outline w-full">
                                        ✕
                                    </button>
                                <?php endif; ?>

                            </div>

                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                </div>
                <div class="mt-4">
                    <label class="text-xs uppercase tracking-wider text-gray-400">
                        Giá combo
                    </label>

                    <input type="number" name="price" min="0" value="<?php echo e(old('price')); ?>"
                        class="admin-input" placeholder="Nhập giá combo">
                </div>
                <template id="combo-item-template">
                    <div class="grid grid-cols-12 gap-3 combo-row">

                        <div class="col-span-8">
                            <label class="text-xs uppercase tracking-wider text-gray-400">
                                Chọn món
                            </label>

                            <select name="combo_items[__index__][variant_id]" class="admin-input">

                                <option value="">-- Chọn biến thể --</option>

                                <?php $__currentLoopData = $variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($variant->id); ?>">
                                        <?php echo e($variant->food->name); ?> (<?php echo e($variant->value); ?>)
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            </select>

                        </div>

                        <div class="col-span-3">

                            <label class="text-xs uppercase tracking-wider text-gray-400">
                                SL
                            </label>

                            <input type="number" min="1" value="1" name="combo_items[__index__][quantity]"
                                class="admin-input">

                        </div>

                        <div class="col-span-1 flex items-end">

                            <button type="button" class="remove-combo-row btn-admin-outline w-full">
                                ✕
                            </button>

                        </div>

                    </div>
                </template>

            </div>

            <label class="flex items-center gap-2 text-sm text-gray-300">
                <input type="checkbox" name="is_active" value="1" <?php echo e(old('is_active', true) ? 'checked' : ''); ?>>
                Hiển thị menu
            </label>

            <button class="btn-admin w-full">
                Lưu sản phẩm
            </button>
        </form>
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ==========================
            // Preview ảnh
            // ==========================
            const imageInput = document.getElementById('image');
            const previewImage = document.getElementById('preview-image');
            const previewContainer = document.getElementById('preview-container');
            const uploadPlaceholder = document.getElementById('upload-placeholder');

            if (imageInput) {
                imageInput.addEventListener('change', function() {

                    const file = this.files[0];

                    if (!file) return;

                    const reader = new FileReader();

                    reader.onload = function(e) {

                        previewImage.src = e.target.result;

                        previewContainer.classList.remove('hidden');
                        uploadPlaceholder.classList.add('hidden');

                    }

                    reader.readAsDataURL(file);

                });
            }

            // ==========================
            // Biến thể
            // ==========================
            const variantSection = document.getElementById('variant-section');
            const variantList = document.getElementById('variant-list');
            const addVariantBtn = document.getElementById('add-variant-btn');
            const variantTemplate = document.getElementById('variant-row-template').innerHTML;

            function addVariantRow() {

                const index = variantList.querySelectorAll('.variant-row').length;

                variantList.insertAdjacentHTML(
                    'beforeend',
                    variantTemplate.replace(/__index__/g, index)
                );

            }

            addVariantBtn.addEventListener('click', function() {

                addVariantRow();

            });

            variantList.addEventListener('click', function(e) {

                if (e.target.closest('.remove-variant')) {

                    e.target.closest('.variant-row').remove();

                }

            });

            // ==========================
            // Combo
            // ==========================
            const comboSection = document.getElementById('combo-section');
            const comboList = document.getElementById('combo-item-list');
            const comboTemplate = document.getElementById('combo-item-template').innerHTML;
            const addComboBtn = document.getElementById('add-combo-item');

            function addComboRow() {

                const index = comboList.querySelectorAll('.combo-row').length;

                comboList.insertAdjacentHTML(
                    'beforeend',
                    comboTemplate.replace(/__index__/g, index)
                );

            }

            addComboBtn.addEventListener('click', function() {

                addComboRow();

            });

            comboList.addEventListener('click', function(e) {

                if (e.target.closest('.remove-combo-row')) {

                    const rows = comboList.querySelectorAll('.combo-row');

                    if (rows.length > 2) {
                        e.target.closest('.combo-row').remove();
                    }

                }

            });

            // ==========================
            // Hiện / Ẩn Combo
            // ==========================
            const category = document.getElementById('category');
            const comboNote = document.getElementById('combo-note');

            function toggleSection() {

                const selectedOption = category.options[category.selectedIndex];
                const categoryName = (selectedOption.text || '').toLowerCase();

                if (categoryName.includes('combo')) {

                    variantSection.classList.add('hidden');
                    comboSection.classList.remove('hidden');
                    comboNote.classList.remove('hidden');

                    if (comboList.querySelectorAll('.combo-row').length < 2) {
                        while (comboList.querySelectorAll('.combo-row').length < 2) {
                            addComboRow();
                        }
                    }

                } else {

                    variantSection.classList.remove('hidden');
                    comboSection.classList.add('hidden');
                    comboNote.classList.add('hidden');

                }

            }
            category.addEventListener('change', toggleSection);

            toggleSection();

        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\WD-11-Cinehome-cinema\resources\views/admin/foods/create.blade.php ENDPATH**/ ?>