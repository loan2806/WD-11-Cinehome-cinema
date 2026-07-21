<?php $__env->startSection('page-title', 'Quản lý Phòng Chiếu'); ?>

<?php $__env->startSection('content'); ?>

    <div class="admin-panel">

        
        <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

            <div>

                <h5 class="text-2xl font-black text-white">
                    Danh sách phòng chiếu
                </h5>

                <small class="text-gray-400">
                    Quản lý toàn bộ phòng chiếu trong hệ thống
                </small>

            </div>

            <a href="<?php echo e(route('admin.phong-chieus.create')); ?>"
                class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 py-3 text-sm font-bold text-white shadow-lg transition hover:scale-[1.02]">

                <i class="fa-solid fa-plus"></i>

                Thêm phòng chiếu

            </a>

        </div>

        
        <form method="GET" action="<?php echo e(route('admin.phong-chieus.index')); ?>" class="mt-6 flex flex-wrap items-center gap-3">

            <select name="rap_chieu_phim_id"
                class="h-12 rounded-2xl border border-white/10 bg-[#151515] px-4 text-white outline-none focus:border-[#d99a32]">

                <option value="">-- Tất cả Rạp --</option>

                <?php $__currentLoopData = $rapChieuPhims; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rap): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($rap->id); ?>"
                        <?php echo e(request('rap_chieu_phim_id') == $rap->id ? 'selected' : ''); ?>>
                        <?php echo e($rap->ten_rap); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            </select>

            <select name="trang_thai"
                class="h-12 rounded-2xl border border-white/10 bg-[#151515] px-4 text-white outline-none focus:border-[#d99a32]">

                <option value="">-- Trạng thái --</option>

                <option value="hoat_dong"
                    <?php echo e(request('trang_thai') == 'hoat_dong' ? 'selected' : ''); ?>>
                    Hoạt động
                </option>

                <option value="bao_tri"
                    <?php echo e(request('trang_thai') == 'bao_tri' ? 'selected' : ''); ?>>
                    Bảo trì
                </option>

                <option value="ngung_hoat_dong"
                    <?php echo e(request('trang_thai') == 'ngung_hoat_dong' ? 'selected' : ''); ?>>
                    Ngừng hoạt động
                </option>

            </select>

            <button type="submit"
                class="h-12 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 text-sm font-bold text-white shadow-lg transition hover:opacity-90">

                <i class="fa-solid fa-filter mr-1"></i>

                Lọc

            </button>

            <a href="<?php echo e(route('admin.phong-chieus.index')); ?>"
                class="flex h-12 items-center rounded-2xl border border-white/10 bg-white/5 px-5 text-sm font-bold text-white transition hover:bg-white/10">

                Reset

            </a>

        </form>

        
        <div class="mt-6 overflow-hidden rounded-3xl border border-white/10">

            <div class="overflow-x-auto">

                <table class="w-full min-w-[900px] text-left">

                    
                    <thead class="bg-white/5 text-xs uppercase tracking-wider text-gray-400">

                        <tr>

                            <th class="px-5 py-4">
                                STT
                            </th>

                            <th class="px-5 py-4">
                                Tên Phòng
                            </th>

                            <th class="px-5 py-4">
                                Rạp Chiếu
                            </th>

                            <th class="px-5 py-4">
                                Loại Phòng
                            </th>

                            <th class="px-5 py-4">
                                Sức Chứa
                            </th>

                            <th class="px-5 py-4">
                                Số Hàng
                            </th>

                            <th class="px-5 py-4">
                                Trạng Thái
                            </th>

                            <th class="px-5 py-4 text-right">
                                Hành động
                            </th>

                        </tr>

                    </thead>

                    
                    <tbody class="divide-y divide-white/5">

                        <?php $__empty_1 = true; $__currentLoopData = $phongChieus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $phongChieu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="bg-[#0f0f0f] transition hover:bg-white/5">

                                
                                <td class="px-5 py-5 text-gray-400">
                                    #<?php echo e($phongChieus->firstItem() + $key); ?>

                                </td>

                                
                                <td class="px-5 py-5 text-white font-bold">
                                    <?php echo e($phongChieu->ten_phong); ?>

                                </td>

                                
                                <td class="px-5 py-5 text-gray-300">
                                    <?php echo e($phongChieu->rapChieuPhim->ten_rap ?? 'N/A'); ?>

                                </td>

                                
                                <td class="px-5 py-5 text-gray-300">
                                    <?php
                                        $loaiLabels = ['2d' => '2D', '3d' => '3D', 'imax' => 'IMAX', '4dx' => '4DX'];
                                    ?>
                                    <span class="inline-block rounded-full bg-white/10 px-3 py-1 text-xs">
                                        <?php echo e($loaiLabels[$phongChieu->loai_phong] ?? $phongChieu->loai_phong); ?>

                                    </span>
                                </td>

                                
                                <td class="px-5 py-5 text-gray-300">
                                    <?php echo e($phongChieu->suc_chua); ?> ghế
                                </td>

                                
                                <td class="px-5 py-5 text-gray-300">
                                    <?php echo e($phongChieu->hangGhes->count()); ?> hàng
                                </td>

                                
                                <td class="px-5 py-5">
                                    <?php
                                        $trangThaiClass = [
                                            'hoat_dong' => 'bg-green-500/20 text-green-400',
                                            'bao_tri' => 'bg-yellow-500/20 text-yellow-400',
                                            'ngung_hoat_dong' => 'bg-gray-500/20 text-gray-400'
                                        ];
                                        $trangThaiLabels = [
                                            'hoat_dong' => 'Hoạt động',
                                            'bao_tri' => 'Bảo trì',
                                            'ngung_hoat_dong' => 'Ngừng hoạt động'
                                        ];
                                    ?>
                                    <span class="rounded-full px-3 py-1 text-xs font-medium <?php echo e($trangThaiClass[$phongChieu->trang_thai] ?? 'bg-gray-500/20 text-gray-400'); ?>">
                                        <?php echo e($trangThaiLabels[$phongChieu->trang_thai] ?? $phongChieu->trang_thai); ?>

                                    </span>
                                </td>

                                
                                <td class="px-5 py-5 align-middle">
                                    <div class="flex items-center justify-center gap-3 whitespace-nowrap">

                                        
                                        <a href="<?php echo e(route('admin.phong-chieus.show', $phongChieu)); ?>"
                                            class="flex items-center gap-1.5 rounded-xl bg-[#d99a32]/15 px-3 py-2 text-sm text-[#d99a32] transition hover:bg-[#d99a32]/25"
                                            title="Quản lý ghế">
                                            <i class="fa-solid fa-couch"></i>
                                            <span class="hidden sm:inline">Ghế</span>
                                        </a>

                                        <a href="<?php echo e(route('admin.phong-chieus.edit', $phongChieu)); ?>"
                                            class="flex aspect-square h-10 w-10 items-center justify-center rounded-xl bg-yellow-500/15 text-yellow-300 transition hover:bg-yellow-500/25"
                                            title="Sửa phòng chiếu">

                                            <i class="fa-solid fa-pen text-base leading-none"></i>

                                        </a>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                            <tr>

                                <td colspan="8" class="px-5 py-16 text-center text-gray-500">
                                    Chưa có phòng chiếu nào trong hệ thống
                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

        <div class="mt-4 flex justify-center">
            <?php echo e($phongChieus->links()); ?>

        </div>

    </div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\WD-11-Cinehome-cinema\resources\views/admin/phong-chieus/index.blade.php ENDPATH**/ ?>