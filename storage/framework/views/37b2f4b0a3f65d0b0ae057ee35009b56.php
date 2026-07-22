<?php $__env->startSection('title', 'Thống kê lương nhân viên'); ?>
<?php $__env->startSection('page-title', 'Thống kê lương nhân viên'); ?>
<?php $__env->startSection('page-subtitle', 'Quản lý và thống kê bảng lương của nhân viên theo chi nhánh'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-white">Thống kê lương</h2>
            <p class="text-gray-400">Xem báo cáo và tính toán lương thực nhận dựa trên chấm công</p>
        </div>
        <a href="<?php echo e(route('admin.bang-luongs.calculate')); ?>"
           class="rounded-xl bg-[#d99a32] px-5 py-3 font-bold text-[#2b1208] transition hover:scale-105">
            <i class="fa-solid fa-calculator mr-2"></i> Tính & Chốt lương tháng
        </a>
    </div>

    <!-- Thống kê tổng quan -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="rounded-2xl border border-white/10 bg-[#151515] p-5 flex items-center justify-between transition hover:border-[#d99a32]/50">
            <div>
                <p class="text-sm text-gray-400">Đã chốt lương</p>
                <h3 class="text-2xl font-black text-white mt-1"><?php echo e($thongKe['so_nhan_vien_da_chot']); ?> <span class="text-sm font-normal text-gray-500">nhân viên</span></h3>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-500/20 text-blue-400 text-xl">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>
        <div class="rounded-2xl border border-white/10 bg-[#151515] p-5 flex items-center justify-between transition hover:border-[#d99a32]/50">
            <div>
                <p class="text-sm text-gray-400">Tổng quỹ lương tháng</p>
                <h3 class="text-2xl font-black text-[#d99a32] mt-1"><?php echo e(number_format($thongKe['tong_chi_tra'], 0, ',', '.')); ?>đ</h3>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[#d99a32]/20 text-[#d99a32] text-xl">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
        </div>
        <div class="rounded-2xl border border-white/10 bg-[#151515] p-5 flex items-center justify-between transition hover:border-[#d99a32]/50">
            <div>
                <p class="text-sm text-gray-400">Đã thanh toán</p>
                <h3 class="text-2xl font-black text-green-400 mt-1"><?php echo e(number_format($thongKe['da_thanh_toan'], 0, ',', '.')); ?>đ</h3>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-500/20 text-green-400 text-xl">
                <i class="fa-solid fa-check-double"></i>
            </div>
        </div>
    </div>

    <!-- Bộ lọc -->
    <div class="rounded-2xl border border-white/10 bg-[#151515] p-5">
        <form method="GET" action="<?php echo e(route('admin.bang-luongs.index')); ?>" class="grid grid-cols-1 gap-4 md:grid-cols-5">
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
                    <option value="thang" <?php echo e((!isset($loaiLoc) || $loaiLoc == 'thang') ? 'selected' : ''); ?>>Tháng</option>
                    <option value="quy" <?php echo e((isset($loaiLoc) && $loaiLoc == 'quy') ? 'selected' : ''); ?>>Quý</option>
                    <option value="nam" <?php echo e((isset($loaiLoc) && $loaiLoc == 'nam') ? 'selected' : ''); ?>>Năm</option>
                </select>
            </div>
            <div id="filter_thang_container">
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
            <div id="filter_quy_container" style="display: none;">
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
                    Lọc thống kê
                </button>
                <a href="<?php echo e(route('admin.bang-luongs.index')); ?>" class="flex items-center justify-center rounded-xl border border-white/10 bg-[#1f1f1f] px-4 py-3 text-white transition hover:bg-white/5">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </form>

        <script>
            function toggleFilterFields() {
                var loaiLoc = document.getElementById('loai_loc').value;
                document.getElementById('filter_thang_container').style.display = (loaiLoc === 'thang') ? 'block' : 'none';
                document.getElementById('filter_quy_container').style.display = (loaiLoc === 'quy') ? 'block' : 'none';
            }
            // Gọi 1 lần lúc load
            document.addEventListener('DOMContentLoaded', function() {
                toggleFilterFields();
            });
        </script>
    </div>

    <!-- Thông báo -->
    <?php if(session('success')): ?>
        <div class="rounded-xl border border-green-500/20 bg-green-500/10 p-4 text-green-400">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <!-- Bảng danh sách -->
    <div class="overflow-hidden rounded-2xl border border-white/10 bg-[#151515]">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-[#1f1f1f] text-gray-300">
                    <tr>
                        <th class="whitespace-nowrap px-4 py-4 text-left align-middle w-10"></th>
                        <th class="whitespace-nowrap px-4 py-4 text-left align-middle">Nhân viên</th>
                        <th class="whitespace-nowrap px-4 py-4 text-center align-middle">Tổng ngày công</th>
                        <th class="whitespace-nowrap px-4 py-4 text-center align-middle">Tổng tăng ca</th>
                        <th class="whitespace-nowrap px-4 py-4 text-right align-middle text-red-400">Tổng phạt</th>
                        <th class="whitespace-nowrap px-4 py-4 text-right align-middle text-[#d99a32] font-black">Tổng thực nhận</th>
                        <th class="whitespace-nowrap px-4 py-4 text-center align-middle">Đã chốt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 text-white">
                    <?php $__empty_1 = true; $__currentLoopData = $employeesPaginator; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <!-- Main Row -->
                        <tr class="hover:bg-white/5 cursor-pointer transition" onclick="toggleRow('details-<?php echo e($emp->id); ?>', 'icon-<?php echo e($emp->id); ?>')">
                            <td class="px-4 py-4 text-center text-gray-400">
                                <i id="icon-<?php echo e($emp->id); ?>" class="fa-solid fa-chevron-right transition-transform duration-200"></i>
                            </td>
                            <td class="px-4 py-4">
                                <div class="font-bold"><?php echo e($emp->ho_ten); ?></div>
                                <div class="text-xs text-gray-400"><?php echo e($emp->email); ?></div>
                            </td>
                            <td class="px-4 py-4 text-center font-bold text-green-400">
                                <?php echo e($emp->summary->tong_ngay_cong); ?>

                            </td>
                            <td class="px-4 py-4 text-center font-bold text-yellow-400">
                                <?php echo e($emp->summary->tong_gio_tang_ca); ?>h
                            </td>
                            <td class="px-4 py-4 text-right font-medium text-red-400">
                                -<?php echo e(number_format($emp->summary->tong_phat, 0, ',', '.')); ?>đ
                            </td>
                            <td class="px-4 py-4 text-right font-black text-[#d99a32] text-base">
                                <?php echo e(number_format($emp->summary->tong_thuc_nhan, 0, ',', '.')); ?>đ
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="rounded-full bg-blue-500/20 px-3 py-1 text-xs font-bold <?php echo e($emp->summary->so_thang_da_chot == $emp->summary->tong_thang && $emp->summary->tong_thang > 0 ? 'text-blue-400' : 'text-gray-400'); ?>">
                                    <?php echo e($emp->summary->so_thang_da_chot); ?> / <?php echo e($emp->summary->tong_thang); ?> tháng
                                </span>
                            </td>
                        </tr>

                        <!-- Expanded Row (Details) -->
                        <tr id="details-<?php echo e($emp->id); ?>" class="hidden bg-[#101010]/50 border-t-0">
                            <td colspan="7" class="p-0">
                                <div class="px-10 py-4">
                                    <?php if($emp->monthly_data->count() > 0): ?>
                                        <table class="w-full text-xs text-left mb-2">
                                            <thead class="text-gray-400 border-b border-white/5">
                                                <tr>
                                                    <th class="py-2 px-3">Tháng</th>
                                                    <th class="py-2 px-3 text-center">Ngày công</th>
                                                    <th class="py-2 px-3 text-center">Tăng ca</th>
                                                    <th class="py-2 px-3 text-right">Lương CB</th>
                                                    <th class="py-2 px-3 text-right">Thưởng/Phụ cấp</th>
                                                    <th class="py-2 px-3 text-right">Phạt</th>
                                                    <th class="py-2 px-3 text-right font-bold text-[#d99a32]">Thực nhận</th>
                                                    <th class="py-2 px-3 text-center">Trạng thái</th>
                                                    <th class="py-2 px-3 text-center">Thao tác</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-white/5">
                                                <?php $__currentLoopData = $emp->monthly_data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr class="hover:bg-white/5">
                                                        <td class="py-2 px-3 font-bold"><?php echo e(sprintf("%02d", $bl->thang)); ?>/<?php echo e($bl->nam); ?></td>
                                                        <td class="py-2 px-3 text-center text-green-400"><?php echo e($bl->tong_ngay_cong); ?></td>
                                                        <td class="py-2 px-3 text-center text-yellow-400"><?php echo e($bl->tong_gio_tang_ca); ?>h</td>
                                                        <td class="py-2 px-3 text-right"><?php echo e(number_format($bl->luong_co_ban, 0, ',', '.')); ?>đ</td>
                                                        <td class="py-2 px-3 text-right">
                                                            <div class="text-[10px]">PC: <?php echo e(number_format($bl->phu_cap, 0, ',', '.')); ?>đ</div>
                                                            <div class="text-[10px] text-green-400">Th: <?php echo e(number_format($bl->thuong, 0, ',', '.')); ?>đ</div>
                                                        </td>
                                                        <td class="py-2 px-3 text-right text-red-400">-<?php echo e(number_format($bl->phat, 0, ',', '.')); ?>đ</td>
                                                        <td class="py-2 px-3 text-right font-bold text-[#d99a32]"><?php echo e(number_format($bl->luong_thuc_nhan, 0, ',', '.')); ?>đ</td>
                                                        <td class="py-2 px-3 text-center">
                                                            <?php if(isset($bl->is_tam_tinh) && $bl->is_tam_tinh): ?>
                                                                <span class="rounded bg-gray-500/20 px-2 py-0.5 text-[10px] font-bold text-gray-400">Tạm tính</span>
                                                            <?php else: ?>
                                                                <form method="POST" action="<?php echo e(route('admin.bang-luongs.toggle-payment', $bl->id)); ?>" class="inline">
                                                                    <?php echo csrf_field(); ?>
                                                                    <?php echo method_field('PATCH'); ?>
                                                                    <button type="submit" class="rounded px-2 py-0.5 text-[10px] font-bold transition <?php echo e($bl->trang_thai === 'da_thanh_toan' ? 'bg-green-500/20 text-green-400 hover:bg-green-500/30' : 'bg-red-500/20 text-red-400 hover:bg-red-500/30'); ?>">
                                                                        <?php echo e($bl->trang_thai === 'da_thanh_toan' ? 'Đã chi trả' : 'Chưa chi trả'); ?>

                                                                    </button>
                                                                </form>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="py-2 px-3 text-center">
                                                            <div class="flex justify-center gap-1">
                                                                <a href="<?php echo e(route('admin.bang-luongs.calculate', ['nhan_vien_id' => $emp->id, 'thang' => $bl->thang, 'nam' => $bl->nam])); ?>"
                                                                   class="rounded bg-blue-500/20 px-2 py-1 text-blue-400 hover:bg-blue-500/40 transition" title="Xem chi tiết">
                                                                    <i class="fa-solid fa-eye"></i>
                                                                </a>
                                                                <?php if(!isset($bl->is_tam_tinh) || !$bl->is_tam_tinh): ?>
                                                                    <form method="POST" action="<?php echo e(route('admin.bang-luongs.destroy', $bl->id)); ?>" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bảng lương này?')">
                                                                        <?php echo csrf_field(); ?>
                                                                        <?php echo method_field('DELETE'); ?>
                                                                        <button type="submit" class="rounded bg-red-500/20 px-2 py-1 text-red-400 hover:bg-red-500/40 transition">
                                                                            <i class="fa-solid fa-trash"></i>
                                                                        </button>
                                                                    </form>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </tbody>
                                        </table>
                                    <?php else: ?>
                                        <div class="text-center text-gray-400 py-4 text-xs italic">Không có dữ liệu trong khoảng thời gian này</div>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="py-10 text-center text-gray-400">
                                Không có dữ liệu
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="mt-4">
        <?php echo e($employeesPaginator->appends(request()->query())->links()); ?>

    </div>

    <script>
        function toggleRow(rowId, iconId) {
            const row = document.getElementById(rowId);
            const icon = document.getElementById(iconId);
            
            if (row.classList.contains('hidden')) {
                row.classList.remove('hidden');
                icon.classList.add('rotate-90');
            } else {
                row.classList.add('hidden');
                icon.classList.remove('rotate-90');
            }
        }
    </script>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/admin/bang-luongs/index.blade.php ENDPATH**/ ?>