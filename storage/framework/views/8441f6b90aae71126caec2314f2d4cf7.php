<?php $__env->startSection('title', 'Chi tiết vé - CineHome'); ?>
<?php $__env->startSection('page-title', 'Chi tiết vé'); ?>
<?php $__env->startSection('page-subtitle', 'Thông tin chi tiết vé xem phim'); ?>

<?php $__env->startSection('content'); ?>
<?php
    // 🌟 ĐỒNG BỘ ĐA TẦNG: Lấy dữ liệu đồ ăn từ Cache giống User, tự động fallback sang Quan hệ DB nếu có
    $foodItems = \Illuminate\Support\Facades\Cache::get("ve_foods:{$veXemPhim->id}", []);
    
    if (empty($foodItems)) {
        if ($veXemPhim->relationLoaded('foods') && $veXemPhim->foods->isNotEmpty()) {
            $foodItems = $veXemPhim->foods->map(function($f) {
                return [
                    'name' => $f->ten_do_an ?? $f->ten_mon ?? $f->name ?? 'Đồ ăn',
                    'qty' => $f->pivot->so_luong ?? 1
                ];
            })->toArray();
        } elseif (isset($veXemPhim->foods) && !empty($veXemPhim->foods)) {
            $foodItems = $veXemPhim->foods;
        }
    }
?>

<div class="rounded-3xl border border-white/10 bg-[#101010] shadow-xl">

    <div class="flex flex-col gap-4 border-b border-white/10 p-6 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h5 class="text-2xl font-black text-white">Chi tiết vé</h5>
            <p class="mt-1 text-sm text-gray-400">
                Mã vé:
                <span class="font-bold text-[#d99a32]"><?php echo e($veXemPhim->ma_ve); ?></span>
            </p>
        </div>

        <a href="<?php echo e(route('admin.ve-xem-phims.index')); ?>"
            class="inline-flex items-center justify-center rounded-2xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/10">
            <i class="fa-solid fa-arrow-left mr-2"></i>
            Quay lại
        </a>
    </div>

    <div class="grid grid-cols-1 gap-5 p-6 xl:grid-cols-2">

        <div class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 transition duration-300 hover:-translate-y-1 hover:border-[#d99a32]/40">
            <h6 class="mb-5 flex items-center gap-2 text-lg font-black text-white">
                <i class="fa-solid fa-ticket text-[#d99a32]"></i>
                Thông tin vé
            </h6>

            <div class="space-y-4 text-sm">
                <div class="flex justify-between gap-4 border-b border-white/5 pb-3">
                    <span class="text-gray-400">Mã vé</span>
                    <span class="font-bold text-[#d99a32]"><?php echo e($veXemPhim->ma_ve); ?></span>
                </div>

                <div class="flex justify-between gap-4 border-b border-white/5 pb-3">
                    <span class="text-gray-400">Loại vé</span>
                    <span class="font-bold text-white">
                        <?php echo e($veXemPhim->loai_ve === 'tai_quay' ? 'Tại quầy' : 'Trực tuyến'); ?>

                    </span>
                </div>

                <div class="flex justify-between gap-4 border-b border-white/5 pb-3">
                    <span class="text-gray-400">Trạng thái</span>

                    <?php if($veXemPhim->trang_thai === 'da_thanh_toan'): ?>
                        <span class="rounded-full bg-green-500/15 px-3 py-1 text-xs font-bold text-green-300">Đã thanh toán</span>
                    <?php elseif($veXemPhim->trang_thai === 'da_su_dung'): ?>
                        <span class="rounded-full bg-gray-500/15 px-3 py-1 text-xs font-bold text-gray-300">Đã sử dụng</span>
                    <?php else: ?>
                        <span class="rounded-full bg-red-500/15 px-3 py-1 text-xs font-bold text-red-300">Đã hủy</span>
                    <?php endif; ?>
                </div>

                <div class="flex justify-between gap-4 border-b border-white/5 pb-3">
                    <span class="text-gray-400">Tổng tiền</span>
                    <span class="font-bold text-white"><?php echo e(number_format($veXemPhim->tong_tien)); ?>đ</span>
                </div>

                <div class="flex justify-between gap-4">
                    <span class="text-gray-400">Tiền hoàn</span>
                    <span class="font-bold text-white"><?php echo e(number_format($veXemPhim->tien_hoan)); ?>đ</span>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 transition duration-300 hover:-translate-y-1 hover:border-[#d99a32]/40">
            <h6 class="mb-5 flex items-center gap-2 text-lg font-black text-white">
                <i class="fa-solid fa-user text-[#d99a32]"></i>
                Người mua
            </h6>

            <div class="space-y-4 text-sm">
                <div class="flex justify-between gap-4 border-b border-white/5 pb-3">
                    <span class="text-gray-400">Khách hàng</span>
                    <span class="font-bold text-white">
                        <?php echo e($veXemPhim->nguoiDung->ho_ten ?? 'Khách mua tại quầy'); ?>

                    </span>
                </div>

                <div class="flex justify-between gap-4 border-b border-white/5 pb-3">
                    <span class="text-gray-400">Email</span>
                    <span class="text-white"><?php echo e($veXemPhim->nguoiDung->email ?? '-'); ?></span>
                </div>

                <div class="flex justify-between gap-4">
                    <span class="text-gray-400">Nhân viên bán</span>
                    <span class="font-bold text-white"><?php echo e($veXemPhim->nhanVien->ho_ten ?? '-'); ?></span>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 transition duration-300 hover:-translate-y-1 hover:border-[#d99a32]/40">
            <h6 class="mb-5 flex items-center gap-2 text-lg font-black text-white">
                <i class="fa-solid fa-film text-[#d99a32]"></i>
                Phim & ghế
            </h6>

            <div class="space-y-4 text-sm">
                <div class="flex justify-between gap-4 border-b border-white/5 pb-3">
                    <span class="text-gray-400">Tên phim</span>
                    <span class="font-bold text-white"><?php echo e($veXemPhim->ten_phim); ?></span>
                </div>

                <div class="flex justify-between gap-4 border-b border-white/5 pb-3">
                    <span class="text-gray-400">Rạp</span>
                    <span class="text-white"><?php echo e($veXemPhim->ten_rap ?? '-'); ?></span>
                </div>

                <div class="flex justify-between gap-4 border-b border-white/5 pb-3">
                    <span class="text-gray-400">Phòng</span>
                    <span class="text-white"><?php echo e($veXemPhim->ten_phong ?? '-'); ?></span>
                </div>

                <div class="flex justify-between gap-4">
                    <span class="text-gray-400">Ghế</span>
                    <span class="font-bold text-[#d99a32]"><?php echo e($veXemPhim->ma_ghe ?? '-'); ?></span>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 transition duration-300 hover:-translate-y-1 hover:border-[#d99a32]/40">
            <h6 class="mb-5 flex items-center gap-2 text-lg font-black text-white">
                <i class="fa-solid fa-clock text-[#d99a32]"></i>
                Thời gian
            </h6>

            <div class="space-y-4 text-sm">
                <div class="flex justify-between gap-4 border-b border-white/5 pb-3">
                    <span class="text-gray-400">Thời gian chiếu</span>
                    <span class="font-bold text-white">
                        <?php echo e($veXemPhim->thoi_gian_chieu?->format('d/m/Y H:i') ?? '-'); ?>

                    </span>
                </div>

                <div class="flex justify-between gap-4 border-b border-white/5 pb-3">
                    <span class="text-gray-400">Ngày tạo vé</span>
                    <span class="text-white"><?php echo e($veXemPhim->created_at?->format('d/m/Y H:i')); ?></span>
                </div>

                <div class="flex justify-between gap-4">
                    <span class="text-gray-400">Cập nhật cuối</span>
                    <span class="text-white"><?php echo e($veXemPhim->updated_at?->format('d/m/Y H:i')); ?></span>
                </div>
            </div>
        </div>

        
        <div class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 transition duration-300 hover:-translate-y-1 hover:border-[#d99a32]/40 xl:col-span-2">
            <h6 class="mb-5 flex items-center gap-2 text-lg font-black text-white">
                <i class="fa-solid fa-cookie-bite text-[#d99a32]"></i>
                Đồ ăn & Combo kèm theo
            </h6>

            <?php if(!empty($foodItems) && count($foodItems) > 0): ?>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <?php $__currentLoopData = $foodItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $tenMon = $item['name'] ?? $item['ten_mon'] ?? 'Đồ ăn';
                            $soLuong = $item['qty'] ?? $item['quantity'] ?? $item['so_luong'] ?? 1;
                        ?>
                        <div class="flex items-center justify-between rounded-2xl border border-white/5 bg-white/[0.02] p-4 transition hover:bg-white/[0.05]">
                            <span class="font-bold text-gray-200"><?php echo e($tenMon); ?></span>
                            <span class="rounded-xl bg-[#d99a32]/15 px-3 py-1.5 text-sm font-black text-[#d99a32]">
                                x<?php echo e($soLuong); ?>

                            </span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <div class="flex items-center gap-3 rounded-2xl border border-dashed border-white/10 bg-white/[0.01] p-5 text-sm text-gray-400">
                    <i class="fa-solid fa-circle-info text-[#d99a32] text-base"></i>
                    <span>Không có đồ ăn hoặc combo nào được đặt kèm theo vé này.</span>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <?php if($veXemPhim->trang_thai === 'da_thanh_toan'): ?>
        <div class="flex flex-wrap gap-3 border-t border-white/10 p-6">
            <form method="POST" action="<?php echo e(route('admin.ve-xem-phims.su-dung', $veXemPhim)); ?>">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PATCH'); ?>

                <button type="submit"
                    onclick="return confirm('Xác nhận vé này đã sử dụng?')"
                    class="rounded-2xl bg-green-500/15 px-5 py-3 font-bold text-green-300 transition hover:-translate-y-0.5 hover:bg-green-500/25">
                    <i class="fa-solid fa-check mr-2"></i>
                    Đánh dấu đã sử dụng
                </button>
            </form>

            <form method="POST" action="<?php echo e(route('admin.ve-xem-phims.huy', $veXemPhim)); ?>">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PATCH'); ?>

                <button type="submit"
                    onclick="return confirm('Bạn có chắc muốn hủy vé này?')"
                    class="rounded-2xl bg-red-500/15 px-5 py-3 font-bold text-red-300 transition hover:-translate-y-0.5 hover:bg-red-500/25">
                    <i class="fa-solid fa-ban mr-2"></i>
                    Hủy vé
                </button>
            </form>
        </div>
    <?php endif; ?>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/admin/ve-xem-phims/show.blade.php ENDPATH**/ ?>