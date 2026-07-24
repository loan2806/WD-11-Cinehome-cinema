<?php $__env->startSection('title', 'Ma Trận Phân Quyền - CineHome'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6 text-white">

    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-[#121212] p-5 rounded-2xl border border-white/10">
        <div>
            <h2 class="text-2xl font-black text-white m-0">Ma trận phân quyền hệ thống</h2>
            <p class="text-sm text-gray-400 m-0 mt-1">Thiết lập quyền truy cập chi tiết cho Quản Lý Rạp và Nhân Viên Quầy</p>
        </div>
        <button type="submit" form="formPhanQuyen" class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600 text-white font-bold px-5 py-3 rounded-xl shadow-lg shadow-red-900/30 transition border-0 cursor-pointer">
            <i class="fa-solid fa-floppy-disk"></i>
            <span>Lưu cấu hình phân quyền</span>
        </button>
    </div>

    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <?php $__currentLoopData = $danhSachVaiTro; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $khoaVaiTro => $tenVaiTro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-[#121212] border border-white/10 rounded-2xl p-4 flex items-center justify-between shadow-lg">
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-gray-400"><?php echo e($tenVaiTro); ?></div>
                    <div class="text-xl font-black text-[#d99a32] mt-1" id="dem-<?php echo e($khoaVaiTro); ?>">0/0 quyền</div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-white/5 flex items-center justify-center text-[#d99a32] text-xl border border-white/10">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <form id="formPhanQuyen" action="<?php echo e(route('admin.phan-quyen.cap-nhat')); ?>" method="POST" class="space-y-4">
        <?php echo csrf_field(); ?>

        <?php $__currentLoopData = config('phan_quyen.nhom_quyen'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $khoaNhom => $nhom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-[#121212] border border-white/10 rounded-2xl overflow-hidden shadow-md">
                
                
                <div class="flex items-center justify-between px-5 py-4 bg-white/5 border-b border-white/10 cursor-pointer select-none transition hover:bg-white/10" onclick="toggleNhom('<?php echo e($khoaNhom); ?>')">
                    <div class="flex items-center gap-3">
                        <span class="text-red-500 text-xs">●</span>
                        <h3 class="text-base font-bold text-white m-0 uppercase tracking-wide"><?php echo e($nhom['tieu_de']); ?></h3>
                        <span class="bg-white/10 text-gray-300 text-xs font-bold px-2.5 py-1 rounded-full border border-white/10">
                            <?php echo e(count($nhom['danh_sach_quyen'])); ?> quyền
                        </span>
                    </div>
                    <i id="icon-<?php echo e($khoaNhom); ?>" class="fa-solid fa-chevron-down text-gray-400 transition-transform duration-200"></i>
                </div>

                
                <div id="nhom-<?php echo e($khoaNhom); ?>" class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-white/10 bg-black/30 text-xs text-gray-400 uppercase tracking-wider">
                                <th class="py-3 px-5 font-semibold" style="width: 40%;">Chức năng / Mã quyền hệ thống</th>
                                <?php $__currentLoopData = $danhSachVaiTro; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $khoaVaiTro => $tenVaiTro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <th class="py-3 px-4 text-center font-semibold" style="width: 20%;">
                                        <div class="text-gray-200 font-bold"><?php echo e($tenVaiTro); ?></div>
                                        <?php if($khoaVaiTro !== 'super_admin'): ?>
                                            <div class="mt-1 text-[11px] normal-case">
                                                <button type="button" onclick="chonCotVaiTro('<?php echo e($khoaNhom); ?>', '<?php echo e($khoaVaiTro); ?>', true)" class="text-[#d99a32] hover:underline bg-transparent border-0 p-0 cursor-pointer">Tất cả</button>
                                                <span class="text-gray-600 mx-1">|</span>
                                                <button type="button" onclick="chonCotVaiTro('<?php echo e($khoaNhom); ?>', '<?php echo e($khoaVaiTro); ?>', false)" class="text-gray-400 hover:underline bg-transparent border-0 p-0 cursor-pointer">Bỏ chọn</button>
                                            </div>
                                        <?php else: ?>
                                            <div class="mt-1 text-emerald-400 text-[11px] font-bold normal-case flex items-center justify-center gap-1">
                                                <i class="fa-solid fa-lock text-[10px]"></i> Toàn quyền
                                            </div>
                                        <?php endif; ?>
                                    </th>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-sm">
                            <?php $__currentLoopData = $nhom['danh_sach_quyen']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maQuyen => $tenQuyen): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-white/5 transition duration-150">
                                    <td class="py-3 px-5">
                                        
                                        <div class="text-white font-bold text-sm"><?php echo e($tenQuyen); ?></div>
                                        <div class="font-mono text-[#d99a32]/80 text-xs mt-0.5 tracking-wide"><?php echo e($maQuyen); ?></div>
                                    </td>
                                    <?php $__currentLoopData = $danhSachVaiTro; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $khoaVaiTro => $tenVaiTro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $laSuperAdmin = ($khoaVaiTro === 'super_admin');
                                            $daDuocChon = $laSuperAdmin || (isset($maTranQuyen[$khoaVaiTro]) && in_array($maQuyen, $maTranQuyen[$khoaVaiTro]));
                                        ?>
                                        <td class="py-3 px-4 text-center">
                                            <input 
                                                type="checkbox" 
                                                class="w-5 h-5 accent-[#d99a32] rounded cursor-pointer cot-<?php echo e($khoaNhom); ?>-<?php echo e($khoaVaiTro); ?> dem-vai-tro-<?php echo e($khoaVaiTro); ?>" 
                                                name="danh_sach_quyen[<?php echo e($khoaVaiTro); ?>][]" 
                                                value="<?php echo e($maQuyen); ?>"
                                                <?php echo e($daDuocChon ? 'checked' : ''); ?>

                                                <?php echo e($laSuperAdmin ? 'disabled' : ''); ?>

                                            >
                                            <?php if($laSuperAdmin): ?>
                                                <input type="hidden" name="danh_sach_quyen[super_admin][]" value="<?php echo e($maQuyen); ?>">
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    </form>

</div>

<script>
    function toggleNhom(khoaNhom) {
        const nhomEl = document.getElementById(`nhom-${khoaNhom}`);
        const iconEl = document.getElementById(`icon-${khoaNhom}`);
        if (nhomEl) nhomEl.classList.toggle('hidden');
        if (iconEl) iconEl.classList.toggle('rotate-180');
    }

    function chonCotVaiTro(khoaNhom, khoaVaiTro, trangThai) {
        if (khoaVaiTro === 'super_admin') return;
        const danhSachOTich = document.querySelectorAll(`.cot-${khoaNhom}-${khoaVaiTro}`);
        danhSachOTich.forEach(cb => {
            if (!cb.disabled) cb.checked = trangThai;
        });
        capNhatSoLuong();
    }

    function capNhatSoLuong() {
        const danhSachVaiTro = <?php echo json_encode(array_keys(config('phan_quyen.vai_tro')), 15, 512) ?>;
        danhSachVaiTro.forEach(khoaVaiTro => {
            const tong = document.querySelectorAll(`.dem-vai-tro-${khoaVaiTro}`).length;
            const daTich = document.querySelectorAll(`.dem-vai-tro-${khoaVaiTro}:checked`).length;
            const theHienThi = document.getElementById(`dem-${khoaVaiTro}`);
            if (theHienThi) theHienThi.innerText = `${daTich}/${tong} quyền`;
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        capNhatSoLuong();
        document.querySelectorAll('.dem-vai-tro-quan_ly_rap, .dem-vai-tro-nhan_vien').forEach(cb => {
            cb.addEventListener('change', capNhatSoLuong);
        });
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/admin/phan_quyen/ma_tran.blade.php ENDPATH**/ ?>