<?php $__env->startSection('title', 'Phân quyền hệ thống động - CineHome'); ?>
<?php $__env->startSection('page-title', 'Quản lý phân quyền động'); ?>
<?php $__env->startSection('page-subtitle', 'Cấu hình ma trận phân bổ chức năng trực quan cho các bộ phận vận hành rạp phim CineHome'); ?>

<?php $__env->startSection('content'); ?>
<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    
    
    <div class="lg:col-span-1">
        <div class="rounded-2xl border border-white/10 bg-[#101010] p-5 shadow-xl">
            <h3 class="mb-3 text-lg font-black text-white flex items-center gap-2">
                <i class="fa-solid fa-users-gear text-[#d99a32]"></i> Vai trò vận hành
            </h3>
            <p class="text-xs text-gray-400 mb-5">Chọn vai trò dưới đây để tiến hành tích chọn phân bổ danh mục chức năng được phép sử dụng.</p>
            
            <div class="space-y-3">
                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($role->name !== 'Quản trị viên'): ?>
                        <a href="<?php echo e(route('admin.phan-quyen.index', ['role_id' => $role->id])); ?>"
                            class="flex items-center justify-between px-4 py-4 rounded-xl border transition-all duration-200 <?php echo e($selectedRole->id == $role->id ? 'bg-[#d99a32]/10 border-[#d99a32] text-[#d99a32] shadow-lg shadow-[#d99a32]/5' : 'bg-white/5 border-white/5 text-gray-300 hover:bg-white/10'); ?>">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid <?php if($role->name === 'Khách hàng'): ?> fa-user <?php elseif($role->name === 'Quản lý hệ thống'): ?> fa-gears <?php else: ?> fa-user-shield <?php endif; ?> text-base opacity-80"></i>
                                <span class="text-sm font-bold"><?php echo e($role->name); ?></span>
                            </div>
                            <span class="rounded-md bg-white/10 px-2.5 py-0.5 text-xs font-semibold text-gray-400">
                                <?php echo e($role->permissions->count()); ?> quyền
                            </span>
                        </a>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    
    <div class="lg:col-span-2">
        <div class="rounded-2xl border border-white/10 bg-[#101010] p-6 shadow-xl h-full">
            
            <?php if($selectedRole && $selectedRole->name !== 'Quản trị viên'): ?>
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-white/10 pb-5 mb-6">
                    <div>
                        <h2 class="text-xl font-black text-white">
                            Thiết lập quyền: <span class="text-[#d99a32]"><?php echo e($selectedRole->name); ?></span>
                        </h2>
                        <p class="text-sm text-gray-400 mt-1">Đánh dấu vào ô vuông để cấp chức năng hoặc bỏ chọn để tước quyền nhân viên ngay lặp tức.</p>
                    </div>
                </div>

                <form action="<?php echo e(route('admin.phan-quyen.updateMatrix', $selectedRole->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>

                    
                    <div class="mb-8">
                        <h4 class="text-xs font-black uppercase tracking-wider text-orange-400 mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-sliders"></i> Phân khu 1: Kỹ Thuật & Cấu Hình Hệ Thống (Admin Backend)
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($permission->name === 'phan_quyen_he_thong' || $permission->name === 'quan_ly_cau_hinh_he_thong'): ?>
                                    <?php $hasPermission = $selectedRole->hasPermissionTo($permission->name); ?>
                                    <label class="flex items-start gap-4 p-4 rounded-xl bg-white/5 border border-white/5 hover:border-white/10 cursor-pointer transition duration-150 select-none group">
                                        <div class="flex h-5 items-center mt-0.5">
                                            <input type="checkbox" name="permissions[]" value="<?php echo e($permission->name); ?>" <?php echo e($hasPermission ? 'checked' : ''); ?> class="h-5 w-5 rounded border-white/20 bg-white/10 text-orange-400 focus:ring-0 focus:ring-offset-0 accent-orange-400">
                                        </div>
                                        <div class="text-sm">
                                            <span class="block font-bold text-white group-hover:text-orange-400 transition duration-150"><?php echo e($permission->description); ?></span>
                                            <span class="block text-xs text-gray-500 font-mono mt-1">Mã: <?php echo e($permission->name); ?></span>
                                        </div>
                                    </label>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    
                    <div class="mb-8 pt-4 border-t border-white/5">
                        <h4 class="text-xs font-black uppercase tracking-wider text-[#d99a32] mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-door-open"></i> Phân khu 2: Vận Hành & Nghiệp Vụ Kinh Doanh (Quầy Bán Vé)
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if(!str_contains($permission->name, 'khach_hang_') && $permission->name !== 'phan_quyen_he_thong' && $permission->name !== 'quan_ly_cau_hinh_he_thong'): ?>
                                    <?php $hasPermission = $selectedRole->hasPermissionTo($permission->name); ?>
                                    <label class="flex items-start gap-4 p-4 rounded-xl bg-white/5 border border-white/5 hover:border-white/10 cursor-pointer transition duration-150 select-none group">
                                        <div class="flex h-5 items-center mt-0.5">
                                            <input type="checkbox" name="permissions[]" value="<?php echo e($permission->name); ?>" <?php echo e($hasPermission ? 'checked' : ''); ?> class="h-5 w-5 rounded border-white/20 bg-white/10 text-[#d99a32] focus:ring-0 focus:ring-offset-0 accent-[#d99a32]">
                                        </div>
                                        <div class="text-sm">
                                            <span class="block font-bold text-white group-hover:text-[#d99a32] transition duration-150"><?php echo e($permission->description); ?></span>
                                            <span class="block text-xs text-gray-500 font-mono mt-1">Mã: <?php echo e($permission->name); ?></span>
                                        </div>
                                    </label>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    
                    <div class="mb-8 pt-4 border-t border-white/5">
                        <h4 class="text-xs font-black uppercase tracking-wider text-[#4ade80] mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-users"></i> Phân khu 3: Dịch Vụ Khách Hàng (Giao diện Website công cộng)
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if(str_contains($permission->name, 'khach_hang_')): ?>
                                    <?php $hasPermission = $selectedRole->hasPermissionTo($permission->name); ?>
                                    <label class="flex items-start gap-4 p-4 rounded-xl bg-white/5 border border-white/5 hover:border-white/10 cursor-pointer transition duration-150 select-none group">
                                        <div class="flex h-5 items-center mt-0.5">
                                            <input type="checkbox" name="permissions[]" value="<?php echo e($permission->name); ?>" <?php echo e($hasPermission ? 'checked' : ''); ?> class="h-5 w-5 rounded border-white/20 bg-white/10 text-[#4ade80] focus:ring-0 focus:ring-offset-0 accent-[#4ade80]">
                                        </div>
                                        <div class="text-sm">
                                            <span class="block font-bold text-white group-hover:text-[#4ade80] transition duration-150"><?php echo e($permission->description); ?></span>
                                            <span class="block text-xs text-gray-500 font-mono mt-1">Mã: <?php echo e($permission->name); ?></span>
                                        </div>
                                    </label>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <div class="flex items-center justify-end border-t border-white/10 pt-5">
                        <button type="submit" class="flex h-12 items-center justify-center gap-2 rounded-xl bg-[#d99a32] px-6 text-sm font-black text-[#2b1208] shadow-lg hover:opacity-90 transition duration-150">
                            <i class="fa-solid fa-square-check"></i> Lưu lại cấu hình ma trận
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <div class="flex h-full flex-col items-center justify-center text-center p-6">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-white/5 text-[#d99a32] mb-4 text-2xl shadow-xl">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                    <h3 class="text-lg font-black text-white">Cấu hình vai trò tối cao</h3>
                    <p class="text-sm text-gray-400 max-w-sm mt-2">Vui lòng lựa chọn vai trò ở danh sách bên cạnh để tiến hành cấu hình.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/admin/phan-quyen/index.blade.php ENDPATH**/ ?>