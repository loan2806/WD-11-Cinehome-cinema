<?php $__env->startSection('page-title', 'Thêm Quốc Gia'); ?>

<?php $__env->startSection('content'); ?>

<div class="admin-panel">

    
    <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

        <div>
            <h5 class="text-2xl font-black text-white">Thêm quốc gia mới</h5>
            <small class="text-gray-400">Điền thông tin quốc gia</small>
        </div>

        <a href="<?php echo e(route('admin.quoc-gias.index')); ?>"
           class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-5 py-3 text-white">
            <i class="fa-solid fa-arrow-left"></i>
            Quay lại
        </a>

    </div>

    
    <form action="<?php echo e(route('admin.quoc-gias.store')); ?>" method="POST" class="mt-6 space-y-6">

        <?php echo csrf_field(); ?>

        
        <div>
            <label class="text-sm text-gray-300">Tên quốc gia</label>

            <input type="text"
                   name="ten_quoc_gia"
                   value="<?php echo e(old('ten_quoc_gia')); ?>"
                   class="w-full rounded-xl border px-4 py-3 text-white bg-[#151515]
                   <?php echo e($errors->has('ten_quoc_gia') ? 'border-red-500' : 'border-white/10'); ?>">

            <?php $__errorArgs = ['ten_quoc_gia'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="text-red-400 text-xs mt-1"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        
        <div>
            <label class="text-sm text-gray-300">Mã quốc gia</label>

            <input type="text"
                   name="ma_quoc_gia"
                   value="<?php echo e(old('ma_quoc_gia')); ?>"
                   class="w-full rounded-xl border px-4 py-3 text-white bg-[#151515]
                   <?php echo e($errors->has('ma_quoc_gia') ? 'border-red-500' : 'border-white/10'); ?>">

            
            <?php $__errorArgs = ['ma_quoc_gia'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="text-red-400 text-xs mt-1"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        
        <div class="flex justify-end gap-3">

            <button type="submit"
                class="rounded-xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-8 py-3 font-bold text-white">
                Lưu quốc gia
            </button>

        </div>

    </form>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/admin/quoc-gias/create.blade.php ENDPATH**/ ?>