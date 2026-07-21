

<?php $__env->startSection('page-title', 'Thêm danh mục đồ ăn'); ?>
<?php $__env->startSection('page-subtitle', 'Tạo danh mục mới cho đồ ăn hoặc đồ uống'); ?>

<?php $__env->startSection('content'); ?>

<?php echo $__env->make('admin.partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="max-w-3xl mx-auto space-y-6">
    <div class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-lg font-black text-white">Thêm danh mục</h3>
                <p class="text-xs text-gray-500">Tạo nhóm danh mục để chọn khi thêm món.</p>
            </div>

            <a href="<?php echo e(route('admin.foods.index')); ?>" class="btn-admin-outline">
                Quay lại danh sách
            </a>
        </div>
    </div>

    <form method="POST" action="<?php echo e(route('admin.foods.categories.store')); ?>" class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-6 space-y-6">
        <?php echo csrf_field(); ?>

        <div>
            <label class="text-xs uppercase tracking-wider text-gray-400">Tên danh mục</label>
            <input name="name" value="<?php echo e(old('name')); ?>" class="admin-input" placeholder="Ví dụ: Đồ ăn nhẹ" >
            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small class="text-red-500"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <button class="btn-admin w-full">Lưu danh mục</button>
    </form>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\WD-11-Cinehome-cinema\resources\views/admin/foods/categories/create.blade.php ENDPATH**/ ?>