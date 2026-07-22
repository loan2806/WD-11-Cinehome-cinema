<?php $__env->startSection('title', 'Lịch sử chấm công'); ?>
<?php $__env->startSection('page-title', 'Lịch sử chấm công'); ?>
<?php $__env->startSection('page-subtitle', 'Quản lý lịch sử chấm công nhân viên'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-white">Lịch sử chấm công</h2>
            <p class="text-gray-400">Xem và quản lý thông tin chấm công hàng ngày của nhân viên</p>
        </div>
        <a href="<?php echo e(route('admin.cham-congs.create')); ?>"
           class="rounded-xl bg-[#d99a32] px-5 py-3 font-bold text-[#2b1208] transition hover:scale-105">
            <i class="fa-solid fa-plus mr-2"></i> Thực hiện chấm công
        </a>
    </div>

    <!-- Bộ lọc -->
    <div class="rounded-2xl border border-white/10 bg-[#151515] p-5">
        <form method="GET" action="<?php echo e(route('admin.cham-congs.index')); ?>" class="grid grid-cols-1 gap-4 md:grid-cols-5">
            <div>
                <label class="mb-2 block text-sm font-bold text-gray-300">Nhân viên</label>
                <select name="nhan_vien_id"
                        class="w-full rounded-xl border border-white/10 bg-[#101010] px-4 py-3 text-white focus:border-[#d99a32] focus:outline-none">
                    <option value="">-- Tất cả nhân viên --</option>
                    <?php $__currentLoopData = $nhanViens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($nv->id); ?>" <?php echo e(request('nhan_vien_id') == $nv->id ? 'selected' : ''); ?>>
                            <?php echo e($nv->ho_ten); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold text-gray-300">Lọc theo</label>
                <select name="loai_loc" id="loai_loc" onchange="toggleFilterFields()"
                        class="w-full rounded-xl border border-white/10 bg-[#101010] px-4 py-3 text-white focus:border-[#d99a32] focus:outline-none">
                    <option value="ngay" <?php echo e((!isset($loaiLoc) || $loaiLoc == 'ngay') ? 'selected' : ''); ?>>Ngày</option>
                    <option value="thang" <?php echo e((isset($loaiLoc) && $loaiLoc == 'thang') ? 'selected' : ''); ?>>Tháng</option>
                    <option value="quy" <?php echo e((isset($loaiLoc) && $loaiLoc == 'quy') ? 'selected' : ''); ?>>Quý</option>
                    <option value="nam" <?php echo e((isset($loaiLoc) && $loaiLoc == 'nam') ? 'selected' : ''); ?>>Năm</option>
                </select>
            </div>
            
            <div id="filter_ngay_container" style="<?php echo e((!isset($loaiLoc) || $loaiLoc == 'ngay') ? 'block' : 'none'); ?>">
                <label class="mb-2 block text-sm font-bold text-gray-300">Theo ngày</label>
                <input type="<?php echo e(request('ngay') ? 'date' : 'text'); ?>" name="ngay" value="<?php echo e(request('ngay')); ?>" placeholder="Chọn ngày..."
                       onfocus="(this.type='date'); this.showPicker()" onblur="if(!this.value) this.type='text'"
                       class="w-full rounded-xl border border-white/10 bg-[#101010] px-4 py-3 text-white focus:border-[#d99a32] focus:outline-none cursor-pointer" style="color-scheme: dark;" onclick="if(this.type==='date') this.showPicker()">
            </div>
            
            <div id="filter_thang_container" style="display: <?php echo e((isset($loaiLoc) && $loaiLoc == 'thang') ? 'block' : 'none'); ?>;">
                <label class="mb-2 block text-sm font-bold text-gray-300">Tháng</label>
                <select name="thang" id="thang"
                        class="w-full rounded-xl border border-white/10 bg-[#101010] px-4 py-3 text-white focus:border-[#d99a32] focus:outline-none">
                    <?php for($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo e($m); ?>" <?php echo e(request('thang', date('m')) == $m ? 'selected' : ''); ?>>
                            Tháng <?php echo e($m); ?>

                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div id="filter_quy_container" style="display: <?php echo e((isset($loaiLoc) && $loaiLoc == 'quy') ? 'block' : 'none'); ?>;">
                <label class="mb-2 block text-sm font-bold text-gray-300">Quý</label>
                <select name="quy" id="quy"
                        class="w-full rounded-xl border border-white/10 bg-[#101010] px-4 py-3 text-white focus:border-[#d99a32] focus:outline-none">
                    <?php for($q = 1; $q <= 4; $q++): ?>
                        <option value="<?php echo e($q); ?>" <?php echo e(request('quy', ceil(date('m')/3)) == $q ? 'selected' : ''); ?>>
                            Quý <?php echo e($q); ?>

                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold text-gray-300">Năm</label>
                <select name="nam"
                        class="w-full rounded-xl border border-white/10 bg-[#101010] px-4 py-3 text-white focus:border-[#d99a32] focus:outline-none">
                    <?php for($y = date('Y'); $y >= 2024; $y--): ?>
                        <option value="<?php echo e($y); ?>" <?php echo e(request('nam', date('Y')) == $y ? 'selected' : ''); ?>>
                            Năm <?php echo e($y); ?>

                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full rounded-xl bg-[#d99a32] py-3 font-bold text-[#2b1208] transition hover:bg-[#d99a32]/85">
                    Lọc dữ liệu
                </button>
                <a href="<?php echo e(route('admin.cham-congs.index')); ?>" class="flex items-center justify-center rounded-xl border border-white/10 bg-[#1f1f1f] px-4 py-3 text-white transition hover:bg-white/5">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </form>

        <script>
            function toggleFilterFields() {
                var loaiLoc = document.getElementById('loai_loc').value;
                document.getElementById('filter_ngay_container').style.display = (loaiLoc === 'ngay') ? 'block' : 'none';
                document.getElementById('filter_thang_container').style.display = (loaiLoc === 'thang') ? 'block' : 'none';
                document.getElementById('filter_quy_container').style.display = (loaiLoc === 'quy') ? 'block' : 'none';
            }
            // Gọi 1 lần lúc load
            document.addEventListener('DOMContentLoaded', function() {
                toggleFilterFields();
            });
        </script>
    </div>

    <!-- Thông báo nếu có -->
    <?php if(session('success')): ?>
        <div class="rounded-xl border border-green-500/20 bg-green-500/10 p-4 text-green-400">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="rounded-xl border border-red-500/20 bg-red-500/10 p-4 text-red-400">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <!-- Bảng danh sách -->
    <div class="overflow-hidden rounded-2xl border border-white/10 bg-[#151515]">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-[#1f1f1f]">
                    <tr>
                        <th class="px-6 py-4 text-left">Ngày</th>
                        <th class="px-6 py-4 text-left">Nhân viên</th>
                        <th class="px-6 py-4 text-left">Giờ vào - ra</th>
                        <th class="px-6 py-4 text-center">Giờ làm việc</th>
                        <th class="px-6 py-4 text-center">Tăng ca</th>
                        <th class="px-6 py-4 text-center">Trạng thái</th>
                        <th class="px-6 py-4 text-left">Ghi chú</th>
                        <th class="px-6 py-4 text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    <?php $__empty_1 = true; $__currentLoopData = $chamCongs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-white/5">
                            <td class="px-6 py-4 font-bold text-white">
                                <?php echo e($cc->ngay->format('d/m/Y')); ?>

                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white">
                                        <i class="fa-solid fa-user-tie"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-white"><?php echo e($cc->nguoiDung->ho_ten); ?></div>
                                        <div class="text-xs text-gray-400"><?php echo e($cc->nguoiDung->email); ?></div>
                                        <?php if($cc->nguoiDung->rapChieuPhim): ?>
                                            <div class="text-xs text-[#d99a32] mt-0.5"><i class="fa-solid fa-film mr-1"></i><?php echo e($cc->nguoiDung->rapChieuPhim->ten_rap); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?php if($cc->gio_vao && $cc->gio_ra): ?>
                                    <div class="text-sm font-medium text-white">
                                        <?php echo e(\Carbon\Carbon::parse($cc->gio_vao)->format('H:i')); ?> - <?php echo e(\Carbon\Carbon::parse($cc->gio_ra)->format('H:i')); ?>

                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-500">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-green-400">
                                <?php echo e($cc->so_gio_lam); ?>h
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-yellow-400">
                                <?php echo e($cc->so_gio_tang_ca > 0 ? $cc->so_gio_tang_ca . 'h' : '0'); ?>

                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="space-y-1">
                                    <?php if($cc->nghi_phep): ?>
                                        <span class="inline-block rounded-full bg-blue-500/20 px-3 py-1 text-xs font-bold text-blue-400">
                                            Nghỉ phép
                                        </span>
                                    <?php elseif($cc->nghi_khong_phep): ?>
                                        <span class="inline-block rounded-full bg-red-500/20 px-3 py-1 text-xs font-bold text-red-400">
                                            Nghỉ không phép
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-block rounded-full bg-green-500/20 px-3 py-1 text-xs font-bold text-green-400">
                                            Đi làm
                                        </span>
                                        <?php if($cc->di_muon): ?>
                                            <br>
                                            <span class="inline-block rounded-full bg-yellow-500/20 px-3 py-1 text-xs font-bold text-yellow-400">
                                                Đi muộn
                                            </span>
                                        <?php endif; ?>
                                        <?php if($cc->ve_som): ?>
                                            <br>
                                            <span class="inline-block rounded-full bg-orange-500/20 px-3 py-1 text-xs font-bold text-orange-400">
                                                Về sớm
                                            </span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-300">
                                <?php echo e($cc->ghi_chu ?? '-'); ?>

                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-center gap-2">
                                    <a href="<?php echo e(route('admin.cham-congs.edit', $cc)); ?>"
                                       class="rounded-lg bg-blue-500 px-3 py-2 text-white hover:bg-blue-600 transition">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form method="POST" action="<?php echo e(route('admin.cham-congs.destroy', $cc)); ?>"
                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa bản ghi chấm công này?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="rounded-lg bg-red-500 px-3 py-2 text-white hover:bg-red-600 transition">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="py-10 text-center text-gray-400">
                                Không tìm thấy dữ liệu chấm công nào
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="mt-4">
        <?php echo e($chamCongs->appends(request()->query())->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/admin/cham-congs/index.blade.php ENDPATH**/ ?>