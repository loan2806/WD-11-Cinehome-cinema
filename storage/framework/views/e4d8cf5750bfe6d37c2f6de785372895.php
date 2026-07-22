<?php $__env->startSection('page-title', 'Sửa Phòng Chiếu'); ?>

<?php $__env->startSection('content'); ?>

<div class="admin-panel">

    <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

        <div>

            <h5 class="text-2xl font-black text-white">
                Sửa phòng chiếu
            </h5>

            <small class="text-gray-400">
                Cập nhật thông tin phòng chiếu
            </small>

        </div>

        <a href="<?php echo e(route('admin.phong-chieus.index')); ?>"
            class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/10">

            <i class="fa-solid fa-arrow-left"></i>

            Quay lại

        </a>

    </div>

    <?php if($errors->any()): ?>
        <div class="mt-6 rounded-xl border border-red-500/30 bg-red-500/10 p-4">
            <ul class="list-inside list-disc text-sm text-red-400">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?php echo e(route('admin.phong-chieus.update', $phongChieu)); ?>" method="POST" class="mt-6">

        <?php echo csrf_field(); ?>

        <?php echo method_field('PUT'); ?>

        <div class="grid gap-5 lg:grid-cols-2">

            
            <div>

                <label for="rap_chieu_phim_id" class="mb-2 block text-sm font-medium text-gray-300">
                    Rạp Chiếu Phim <span class="text-red-400">*</span>
                </label>

                <select name="rap_chieu_phim_id" id="rap_chieu_phim_id"
                    class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none transition focus:border-[#d99a32]"
                    required>

                    <?php $__currentLoopData = $rapChieuPhims; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rap): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($rap->id); ?>" <?php echo e($phongChieu->rap_chieu_phim_id == $rap->id ? 'selected' : ''); ?>>
                            <?php echo e($rap->ten_rap); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                </select>

            </div>

            
            <div>

                <label for="ten_phong" class="mb-2 block text-sm font-medium text-gray-300">
                    Tên Phòng Chiếu <span class="text-red-400">*</span>
                </label>

                <input type="text" name="ten_phong" id="ten_phong"
                    value="<?php echo e(old('ten_phong', $phongChieu->ten_phong)); ?>"
                    class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none transition focus:border-[#d99a32]"
                    required>

            </div>

            
            <div>

                <label for="loai_phong" class="mb-2 block text-sm font-medium text-gray-300">
                    Loại Phòng Chiếu <span class="text-red-400">*</span>
                </label>

                <select name="loai_phong" id="loai_phong"
                    class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none transition focus:border-[#d99a32]"
                    required>

                    <option value="2d" <?php echo e($phongChieu->loai_phong == '2d' ? 'selected' : ''); ?>>2D</option>

                    <option value="3d" <?php echo e($phongChieu->loai_phong == '3d' ? 'selected' : ''); ?>>3D</option>

                    <option value="imax" <?php echo e($phongChieu->loai_phong == 'imax' ? 'selected' : ''); ?>>IMAX</option>

                    <option value="4dx" <?php echo e($phongChieu->loai_phong == '4dx' ? 'selected' : ''); ?>>4DX</option>

                </select>

            </div>

            
            <div>

                <label for="suc_chua" class="mb-2 block text-sm font-medium text-gray-300">
                    Sức Chứa (ghế) <span class="text-red-400">*</span>
                </label>

                <input type="number" name="suc_chua" id="suc_chua"
                    value="<?php echo e(old('suc_chua', $phongChieu->suc_chua)); ?>"
                    min="1" max="500"
                    class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none transition focus:border-[#d99a32]"
                    required>

            </div>

            
            <div class="lg:col-span-2">

                <label for="trang_thai" class="mb-2 block text-sm font-medium text-gray-300">
                    Trạng Thái <span class="text-red-400">*</span>
                </label>

                <select name="trang_thai" id="trang_thai"
                    class="w-full rounded-2xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none transition focus:border-[#d99a32]"
                    required>

                    <option value="hoat_dong" <?php echo e($phongChieu->trang_thai == 'hoat_dong' ? 'selected' : ''); ?>>
                        Hoạt động
                    </option>

                    <option value="bao_tri" <?php echo e($phongChieu->trang_thai == 'bao_tri' ? 'selected' : ''); ?>>
                        Bảo trì
                    </option>

                    <option value="ngung_hoat_dong" <?php echo e($phongChieu->trang_thai == 'ngung_hoat_dong' ? 'selected' : ''); ?>>
                        Ngừng hoạt động
                    </option>

                </select>

            </div>

        </div>

        <div class="mt-6 flex justify-end gap-3">

            <a href="<?php echo e(route('admin.phong-chieus.index')); ?>"
                class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-6 py-3 text-sm font-bold text-white transition hover:bg-white/10">

                <i class="fa-solid fa-times"></i>

                Hủy

            </a>

            <button type="submit"
                class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-6 py-3 text-sm font-bold text-white shadow-lg transition hover:scale-[1.02]">

                <i class="fa-solid fa-save"></i>

                Cập nhật

            </button>

        </div>

    </form>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/admin/phong-chieus/edit.blade.php ENDPATH**/ ?>