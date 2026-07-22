<?php $__env->startSection('page-title', 'Khuyến mãi & Voucher'); ?>
<?php $__env->startSection('page-subtitle', 'Thiết lập voucher mẫu, điểm đổi, hạn dùng và cấp ưu đãi trực tiếp cho khách hàng'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $typeClasses = [
        'giam_gia_ve' => 'bg-blue-500/15 text-blue-300',
        'giam_gia_do_an' => 'bg-orange-500/15 text-orange-300',
        'giam_gia_ghe_vip' => 'bg-purple-500/15 text-purple-300',
        'sinh_nhat' => 'bg-pink-500/15 text-pink-300',
        'khach_hang_than_thiet' => 'bg-green-500/15 text-green-300',
    ];
?>

<?php echo $__env->make('admin.partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="mb-5 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
    <div class="stat-card">
        <div class="stat-label">Voucher mẫu</div>
        <div class="stat-value"><?php echo e($summary['total']); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Đang hiệu lực</div>
        <div class="stat-value"><?php echo e($summary['active']); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Hết hạn</div>
        <div class="stat-value"><?php echo e($summary['expired']); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Đã cấp</div>
        <div class="stat-value"><?php echo e($summary['issued']); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Đã dùng</div>
        <div class="stat-value"><?php echo e($summary['used']); ?></div>
    </div>
</div>

<div class="grid gap-6 xl:grid-cols-[430px_1fr]">
    <div class="space-y-6">
        <form method="POST" action="<?php echo e(route('admin.vouchers.store')); ?>" class="admin-panel">
            <?php echo csrf_field(); ?>
            <div class="panel-header">
                <div>
                    <h5>Tạo voucher mẫu</h5>
                    <small>Voucher mẫu dùng để khách đổi điểm hoặc admin cấp thủ công</small>
                </div>
            </div>

            <div class="panel-body space-y-4">
                <input name="ma_voucher" value="<?php echo e(old('ma_voucher')); ?>" class="admin-input" placeholder="Mã voucher, ví dụ FOOD20K" required>
                <input name="ten_voucher" value="<?php echo e(old('ten_voucher')); ?>" class="admin-input" placeholder="Tên chương trình" required>

                <select name="loai_voucher" class="admin-input" required>
                    <?php $__currentLoopData = $voucherTypeLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($value); ?>" <?php if(old('loai_voucher', 'giam_gia_ve') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>

                <div class="grid gap-3 md:grid-cols-2">
                    <input name="gia_tri_giam" type="number" min="0" value="<?php echo e(old('gia_tri_giam', 0)); ?>" class="admin-input" placeholder="Giá trị giảm" required>
                    <input name="diem_can_doi" type="number" min="0" value="<?php echo e(old('diem_can_doi', 0)); ?>" class="admin-input" placeholder="Điểm cần đổi" required>
                </div>

                <input name="ngay_het_han" type="date" value="<?php echo e(old('ngay_het_han', now()->addMonth()->format('Y-m-d'))); ?>" class="admin-input" required>

                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3 text-sm font-bold text-gray-200">
                    <input type="checkbox" name="trang_thai" value="1" class="h-4 w-4 accent-[#d99a32]" checked>
                    Bật voucher ngay
                </label>

                <button type="submit" class="btn-admin w-full">
                    <i class="fa-solid fa-ticket-simple"></i>
                    Lưu voucher
                </button>
            </div>
        </form>

        <form method="POST" action="<?php echo e(route('admin.vouchers.issue')); ?>" class="admin-panel">
            <?php echo csrf_field(); ?>
            <div class="panel-header">
                <div>
                    <h5>Cấp voucher cho khách</h5>
                    <small>Tặng voucher cá nhân, không trừ điểm khách hàng</small>
                </div>
            </div>

            <div class="panel-body space-y-4">
                <select name="voucher_id" class="admin-input" required>
                    <option value="">Chọn voucher đang hiệu lực</option>
                    <?php $__currentLoopData = $activeVouchers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $voucher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($voucher->id); ?>" <?php if(old('voucher_id') == $voucher->id): echo 'selected'; endif; ?>>
                            <?php echo e($voucher->ma_voucher); ?> - <?php echo e($voucher->ten_voucher); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>

                <select name="nguoi_dung_id" class="admin-input" required>
                    <option value="">Chọn khách hàng</option>
                    <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($customer->id); ?>" <?php if(old('nguoi_dung_id') == $customer->id): echo 'selected'; endif; ?>>
                            <?php echo e($customer->ho_ten); ?> - <?php echo e($customer->email); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>

                <div class="grid gap-3 md:grid-cols-2">
                    <input name="quantity" type="number" min="1" max="20" value="<?php echo e(old('quantity', 1)); ?>" class="admin-input" placeholder="Số lượng" required>
                    <input name="ngay_het_han" type="date" value="<?php echo e(old('ngay_het_han')); ?>" class="admin-input" placeholder="Hạn riêng">
                </div>

                <select name="loai_cap_phat" class="admin-input" required>
                    <option value="admin_tang" <?php if(old('loai_cap_phat', 'admin_tang') === 'admin_tang'): echo 'selected'; endif; ?>>Admin tặng</option>
                    <option value="khach_hang_than_thiet" <?php if(old('loai_cap_phat') === 'khach_hang_than_thiet'): echo 'selected'; endif; ?>>Khách hàng thân thiết</option>
                </select>

                <button type="submit" class="btn-admin w-full">
                    <i class="fa-solid fa-gift"></i>
                    Cấp voucher
                </button>
            </div>
        </form>
    </div>

    <div class="space-y-6">
        <div class="admin-panel">
            <div class="panel-header flex-col items-start gap-4 lg:flex-row lg:items-center">
                <div>
                    <h5>Danh sách voucher mẫu</h5>
                    <small>Lọc, bật/tắt và chỉnh chính sách đổi điểm</small>
                </div>
            </div>

            <form method="GET" action="<?php echo e(route('admin.vouchers.index')); ?>" class="panel-body grid gap-3 border-b border-white/10 lg:grid-cols-[1fr_180px_170px_auto_auto]">
                <input name="q" value="<?php echo e(request('q')); ?>" class="admin-input" placeholder="Tìm mã hoặc tên voucher...">
                <select name="loai_voucher" class="admin-input">
                    <option value="">Tất cả loại</option>
                    <?php $__currentLoopData = $voucherTypeLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($value); ?>" <?php if(request('loai_voucher') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <select name="trang_thai" class="admin-input">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active" <?php if(request('trang_thai') === 'active'): echo 'selected'; endif; ?>>Đang hiệu lực</option>
                    <option value="inactive" <?php if(request('trang_thai') === 'inactive'): echo 'selected'; endif; ?>>Đang tắt</option>
                    <option value="expired" <?php if(request('trang_thai') === 'expired'): echo 'selected'; endif; ?>>Hết hạn</option>
                </select>
                <button class="btn-admin" type="submit">Lọc</button>
                <a href="<?php echo e(route('admin.vouchers.index')); ?>" class="btn-admin-outline text-center no-underline">Reset</a>
            </form>

            <div class="space-y-4 p-4">
                <?php $__empty_1 = true; $__currentLoopData = $vouchers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $voucher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $expired = $voucher->ngay_het_han->lt(today());
                    ?>
                    <div class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-4">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0">
                                <div class="mb-2 flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-[#d99a32]/15 px-3 py-1 text-xs font-black text-[#f4c56a]"><?php echo e($voucher->ma_voucher); ?></span>
                                    <span class="rounded-full px-3 py-1 text-xs font-black <?php echo e($typeClasses[$voucher->loai_voucher] ?? 'bg-white/10 text-gray-300'); ?>">
                                        <?php echo e($voucherTypeLabels[$voucher->loai_voucher] ?? $voucher->loai_voucher); ?>

                                    </span>
                                    <?php if($voucher->trang_thai && ! $expired): ?>
                                        <span class="rounded-full bg-green-500/15 px-3 py-1 text-xs font-black text-green-300">Đang hiệu lực</span>
                                    <?php elseif($expired): ?>
                                        <span class="rounded-full bg-red-500/15 px-3 py-1 text-xs font-black text-red-300">Hết hạn</span>
                                    <?php else: ?>
                                        <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-black text-gray-300">Đang tắt</span>
                                    <?php endif; ?>
                                </div>

                                <h3 class="m-0 text-lg font-black text-white"><?php echo e($voucher->ten_voucher); ?></h3>
                                <div class="mt-2 grid gap-2 text-sm text-gray-300 md:grid-cols-4">
                                    <div>Giảm: <strong class="text-white"><?php echo e(number_format((float) $voucher->gia_tri_giam, 0, ',', '.')); ?>đ</strong></div>
                                    <div>Điểm đổi: <strong class="text-white"><?php echo e(number_format($voucher->diem_can_doi)); ?></strong></div>
                                    <div>Đã cấp: <strong class="text-white"><?php echo e($voucher->nguoi_dung_vouchers_count); ?></strong></div>
                                    <div>Đã dùng: <strong class="text-white"><?php echo e($voucher->used_count); ?></strong></div>
                                </div>
                                <div class="mt-2 text-sm text-gray-500">Hết hạn: <?php echo e($voucher->ngay_het_han->format('d/m/Y')); ?></div>
                            </div>

                            <div class="flex flex-wrap gap-2 lg:justify-end">
                                <form method="POST" action="<?php echo e(route('admin.vouchers.toggle-status', $voucher)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PATCH'); ?>
                                    <button class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm font-black text-gray-200 transition hover:bg-white/10" type="submit">
                                        <?php echo e($voucher->trang_thai ? 'Tắt' : 'Bật'); ?>

                                    </button>
                                </form>

                                <form method="POST" action="<?php echo e(route('admin.vouchers.destroy', $voucher)); ?>" onsubmit="return confirm('Xóa voucher <?php echo e($voucher->ma_voucher); ?>?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button class="action-btn action-delete" type="submit" <?php if($voucher->nguoi_dung_vouchers_count > 0): echo 'disabled'; endif; ?>>
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <details class="mt-4 rounded-2xl border border-white/10 bg-white/[0.03] p-4">
                            <summary class="cursor-pointer text-sm font-black text-[#f4c56a]">Sửa voucher</summary>
                            <form method="POST" action="<?php echo e(route('admin.vouchers.update', $voucher)); ?>" class="mt-4 grid gap-3 md:grid-cols-2">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PATCH'); ?>
                                <input name="ma_voucher" value="<?php echo e(old('ma_voucher', $voucher->ma_voucher)); ?>" class="admin-input" placeholder="Mã voucher" required>
                                <input name="ten_voucher" value="<?php echo e(old('ten_voucher', $voucher->ten_voucher)); ?>" class="admin-input" placeholder="Tên voucher" required>
                                <select name="loai_voucher" class="admin-input">
                                    <?php $__currentLoopData = $voucherTypeLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($value); ?>" <?php if(old('loai_voucher', $voucher->loai_voucher) === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <input name="ngay_het_han" type="date" value="<?php echo e(old('ngay_het_han', $voucher->ngay_het_han->format('Y-m-d'))); ?>" class="admin-input" required>
                                <input name="gia_tri_giam" type="number" min="0" value="<?php echo e(old('gia_tri_giam', (float) $voucher->gia_tri_giam)); ?>" class="admin-input" required>
                                <input name="diem_can_doi" type="number" min="0" value="<?php echo e(old('diem_can_doi', $voucher->diem_can_doi)); ?>" class="admin-input" required>
                                <label class="flex items-center gap-3 rounded-xl border border-white/10 bg-white/[0.03] px-4 py-3 text-sm font-bold text-gray-200 md:col-span-2">
                                    <input type="checkbox" name="trang_thai" value="1" class="h-4 w-4 accent-[#d99a32]" <?php if($voucher->trang_thai): echo 'checked'; endif; ?>>
                                    Bật voucher
                                </label>
                                <button class="btn-admin md:col-span-2" type="submit">Cập nhật voucher</button>
                            </form>
                        </details>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="rounded-3xl border border-dashed border-white/15 p-10 text-center text-gray-400">
                        Chưa có voucher phù hợp bộ lọc.
                    </div>
                <?php endif; ?>
            </div>

            <div class="border-t border-white/10 p-4">
                <?php echo e($vouchers->links()); ?>

            </div>
        </div>

        <div class="admin-panel">
            <div class="panel-header flex-col items-start gap-4 lg:flex-row lg:items-center">
                <div>
                    <h5>Voucher đã cấp cho khách</h5>
                    <small>Theo dõi mã cá nhân, trạng thái dùng và thu hồi khi chưa sử dụng</small>
                </div>
            </div>

            <form method="GET" action="<?php echo e(route('admin.vouchers.index')); ?>" class="panel-body grid gap-3 border-b border-white/10 lg:grid-cols-[1fr_180px_auto]">
                <input name="issued_q" value="<?php echo e(request('issued_q')); ?>" class="admin-input" placeholder="Tìm mã cá nhân, khách hàng...">
                <select name="issued_status" class="admin-input">
                    <option value="">Tất cả</option>
                    <option value="unused" <?php if(request('issued_status') === 'unused'): echo 'selected'; endif; ?>>Chưa dùng</option>
                    <option value="used" <?php if(request('issued_status') === 'used'): echo 'selected'; endif; ?>>Đã dùng</option>
                    <option value="expired" <?php if(request('issued_status') === 'expired'): echo 'selected'; endif; ?>>Hết hạn</option>
                </select>
                <button class="btn-admin" type="submit">Lọc cấp phát</button>
            </form>

            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Mã cá nhân</th>
                            <th>Khách hàng</th>
                            <th>Voucher mẫu</th>
                            <th>Hạn dùng</th>
                            <th>Trạng thái</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $issuedVouchers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $issuedExpired = $item->ngay_het_han && $item->ngay_het_han->lt(now());
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo e($item->ma_voucher_ca_nhan); ?></strong>
                                    <div class="text-xs text-gray-500"><?php echo e($item->ngay_nhan?->format('d/m/Y H:i')); ?></div>
                                </td>
                                <td>
                                    <strong><?php echo e($item->nguoiDung?->ho_ten ?? 'Không rõ'); ?></strong>
                                    <div class="text-xs text-gray-500"><?php echo e($item->nguoiDung?->email); ?></div>
                                </td>
                                <td>
                                    <?php echo e($item->voucher?->ma_voucher); ?>

                                    <div class="text-xs text-gray-500"><?php echo e($item->voucher?->ten_voucher); ?></div>
                                </td>
                                <td><?php echo e($item->ngay_het_han?->format('d/m/Y') ?? 'Theo voucher mẫu'); ?></td>
                                <td>
                                    <?php if($item->da_su_dung): ?>
                                        <span class="rounded-full bg-green-500/15 px-3 py-1 text-xs font-black text-green-300">Đã dùng</span>
                                    <?php elseif($issuedExpired): ?>
                                        <span class="rounded-full bg-red-500/15 px-3 py-1 text-xs font-black text-red-300">Hết hạn</span>
                                    <?php else: ?>
                                        <span class="rounded-full bg-yellow-500/15 px-3 py-1 text-xs font-black text-yellow-200">Chưa dùng</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if(! $item->da_su_dung): ?>
                                        <form method="POST" action="<?php echo e(route('admin.vouchers.issued.destroy', $item)); ?>" onsubmit="return confirm('Thu hồi voucher <?php echo e($item->ma_voucher_ca_nhan); ?>?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button class="action-btn action-delete" type="submit" title="Thu hồi">
                                                <i class="fa-solid fa-rotate-left"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center text-gray-400">Chưa có voucher cá nhân phù hợp.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="border-t border-white/10 p-4">
                <?php echo e($issuedVouchers->links()); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/admin/vouchers/index.blade.php ENDPATH**/ ?>