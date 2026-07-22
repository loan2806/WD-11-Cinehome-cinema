<?php $__env->startSection('title', 'Chi tiết khách hàng'); ?>
<?php $__env->startSection('page-title', 'Chi tiết khách hàng'); ?>
<?php $__env->startSection('page-subtitle', 'Hồ sơ, trạng thái, vé và voucher của khách hàng'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    <a href="<?php echo e(route('admin.khach-hang.index')); ?>"
        class="inline-flex items-center gap-2 text-sm font-bold text-[#d99a32] no-underline transition hover:translate-x-1">
        <i class="fa-solid fa-arrow-left"></i>
        Quay lại danh sách
    </a>
    <a href="<?php echo e(route('admin.khach-hang.edit', $khachHang)); ?>"
        class="inline-flex items-center gap-2 rounded-2xl bg-[#d99a32] px-5 py-3 text-sm font-black text-black no-underline transition hover:bg-[#f4c56a]">
        <i class="fa-solid fa-pen-to-square"></i>
        Sửa hồ sơ
    </a>

    
    <div
        class="rounded-3xl border border-[#d99a32]/30 bg-gradient-to-br from-[#1b0d05] via-[#121212] to-[#070707] p-6 shadow-2xl">
        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm text-gray-400">Khách hàng</p>
                <h2 class="mt-2 text-3xl font-black text-white">
                    <?php echo e($khachHang->ho_ten); ?>

                </h2>
                <p class="mt-1 text-gray-400"><?php echo e($khachHang->email); ?></p>
            </div>

            <div>
                <?php if($khachHang->trang_thai_hoat_dong): ?>
                <span class="rounded-full bg-green-500/15 px-4 py-2 text-sm font-black text-green-400">
                    Đang hoạt động
                </span>
                <?php else: ?>
                <span class="rounded-full bg-red-500/15 px-4 py-2 text-sm font-black text-red-400">
                    Tài khoản bị khóa
                </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 gap-5 md:grid-cols-4">
        <div class="rounded-3xl border border-white/10 bg-[#121212] p-5">
            <p class="text-sm text-gray-400">Tổng vé</p>
            <h3 class="mt-2 text-3xl font-black text-white"><?php echo e(number_format($tongVe)); ?></h3>
        </div>

        <div class="rounded-3xl border border-white/10 bg-[#121212] p-5">
            <p class="text-sm text-gray-400">Tổng chi tiêu</p>
            <h3 class="mt-2 text-3xl font-black text-white">
                <?php echo e(number_format($tongChiTieu, 0, ',', '.')); ?>đ
            </h3>
        </div>

        <div class="rounded-3xl border border-white/10 bg-[#121212] p-5">
            <p class="text-sm text-gray-400">Hạng thành viên</p>
            <h3 class="mt-2 text-3xl font-black text-[#f4c56a]">
                <?php echo e(strtoupper($khachHang->thanhVien->ten_hang ?? '---')); ?>

            </h3>
        </div>

        <div class="rounded-3xl border border-white/10 bg-[#121212] p-5">
            <p class="text-sm text-gray-400">Điểm hiện tại</p>
            <h3 class="mt-2 text-3xl font-black text-white">
                <?php echo e(number_format($khachHang->thanhVien->diem_hien_tai ?? 0)); ?>

            </h3>
        </div>

        <?php if($khachHang->thanhVien): ?>
        <a href="<?php echo e(route('admin.thanh-vien.show', $khachHang->thanhVien)); ?>"
            class="inline-flex items-center gap-2 rounded-2xl bg-[#d99a32] px-5 py-3 text-sm font-black text-black no-underline transition hover:bg-[#f4c56a]">
            <i class="fa-solid fa-crown"></i>
            Xem thẻ thành viên & điểm
        </a>
        <?php endif; ?>
    </div>



    
    <div class="rounded-3xl border border-white/10 bg-[#121212] p-6">
        <h2 class="mb-5 text-xl font-black">Thông tin cá nhân</h2>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <p class="text-sm text-gray-400">Ngày sinh</p>
                <p class="mt-1 font-bold text-white"><?php echo e($khachHang->ngay_sinh?->format('d/m/Y') ?? '---'); ?></p>
            </div>

            <div>
                <p class="text-sm text-gray-400">Ngày tạo tài khoản</p>
                <p class="mt-1 font-bold text-white"><?php echo e($khachHang->created_at?->format('d/m/Y') ?? '---'); ?></p>
            </div>

            <div>
                <p class="text-sm text-gray-400">Trạng thái email</p>
                <p class="mt-1 font-bold text-white">
                    <?php echo e($khachHang->email_verified_at ? 'Đã xác minh' : 'Chưa xác minh'); ?>

                </p>
            </div>

            <div>
                <p class="text-sm text-gray-400">Số điện thoại</p>
                <p class="mt-1 font-bold text-white">
                    <?php echo e($khachHang->so_dien_thoai ?? '---'); ?>

                </p>
            </div>
        </div>
    </div>

    
    <div class="overflow-hidden rounded-3xl border border-white/10 bg-[#121212]">
        <div class="border-b border-white/10 px-6 py-5">
            <h2 class="text-xl font-black">Vé gần đây</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[800px] text-left">
                <thead class="bg-white/[0.04] text-xs uppercase text-gray-400">
                    <tr>
                        <th class="px-6 py-4">Mã vé</th>
                        <th class="px-6 py-4">Phim</th>
                        <th class="px-6 py-4">Ghế</th>
                        <th class="px-6 py-4">Tiền</th>
                        <th class="px-6 py-4">Trạng thái</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-white/10">
                    <?php $__empty_1 = true; $__currentLoopData = $veGanDay; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ve): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-white/[0.04]">
                        <td class="px-6 py-4 font-black text-[#d99a32]"><?php echo e($ve->ma_ve); ?></td>
                        <td class="px-6 py-4 text-white"><?php echo e($ve->ten_phim); ?></td>
                        <td class="px-6 py-4 text-gray-400"><?php echo e($ve->ma_ghe); ?></td>
                        <td class="px-6 py-4 text-white"><?php echo e(number_format($ve->tong_tien, 0, ',', '.')); ?>đ</td>
                        <td class="px-6 py-4 text-gray-400"><?php echo e($ve->trang_thai); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                            Khách hàng chưa mua vé nào.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <div class="rounded-3xl border border-white/10 bg-[#121212] p-6">
        <h2 class="mb-5 text-xl font-black">Voucher khách đang có</h2>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <?php $__empty_1 = true; $__currentLoopData = $khachHang->vouchersCaNhan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $voucherCaNhan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                <p class="text-sm text-gray-400">Mã voucher</p>
                <h3 class="mt-2 break-all text-xl font-black text-[#f4c56a]">
                    <?php echo e($voucherCaNhan->ma_voucher_ca_nhan); ?>

                </h3>

                <p class="mt-4 text-sm text-gray-400">Giá trị</p>
                <p class="text-2xl font-black text-white">
                    <?php echo e(number_format($voucherCaNhan->voucher->gia_tri_giam ?? 0, 0, ',', '.')); ?>đ
                </p>

                <div class="mt-4">
                    <?php if($voucherCaNhan->da_su_dung): ?>
                    <span class="rounded-full bg-gray-500/15 px-3 py-1 text-xs font-bold text-gray-400">Đã dùng</span>
                    <?php else: ?>
                    <span class="rounded-full bg-green-500/15 px-3 py-1 text-xs font-bold text-green-400">Chưa
                        dùng</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-gray-500">Khách chưa có voucher nào.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/admin/khach_hang/show.blade.php ENDPATH**/ ?>