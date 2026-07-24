<?php $__env->startSection('page-title', 'Chỉnh Sửa Suất Chiếu'); ?>

<?php $__env->startSection('content'); ?>

    <div class="admin-panel space-y-6">

        
        <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-[#110c06]/30 p-5 rounded-2xl border border-[#d99a32]/10 backdrop-blur-md">
            <div>
                <h5 class="text-2xl font-black text-white m-0 flex items-center gap-2">
                    <i class="fa-solid fa-calendar-check text-[#d99a32]"></i>
                    Cập nhật thông tin suất chiếu #<?php echo e($suatChieu->id); ?>

                </h5>
                <small class="text-gray-400 block mt-1">
                    Điều chỉnh tham số vận hành, biểu giá hoặc thiết lập trạng thái đóng/hủy suất chiếu khẩn cấp.
                </small>
            </div>

            <a href="<?php echo e(route('admin.suat-chieus.index')); ?>"
                class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-white/10 no-underline cursor-pointer">
                <i class="fa-solid fa-arrow-left"></i>
                Quay lại
            </a>
        </div>

        
        <?php
            $isLocked = in_array($suatChieu->trang_thai, ['dang_chieu', 'dung_nhan_ve', 'da_chieu']);
        ?>

        <?php if($isLocked): ?>
            <div class="rounded-2xl border border-yellow-500/30 bg-yellow-500/10 p-4 flex items-start gap-3">
                <i class="fa-solid fa-triangle-exclamation text-yellow-500 text-xl mt-0.5"></i>
                <div>
                    <h6 class="text-yellow-500 font-bold m-0 text-sm">Khóa dữ liệu cốt lõi do Suất chiếu đang vận hành!</h6>
                    <p class="text-xs text-gray-400 m-0 mt-1 leading-relaxed">
                        Suất chiếu này đã bước vào giai đoạn mở cổng đón khách hoặc đã kết thúc. Hệ thống đã tự động đóng băng các trường thông tin: <span class="text-white font-bold">Phim, Rạp, Phòng chiếu, Ngày & Giờ</span> để bảo toàn dữ liệu vé của khách hàng. Bạn chỉ có thể điều chỉnh Biểu giá hoặc chọn trạng thái Hủy suất nếu có sự cố phòng chiếu.
                    </p>
                </div>
            </div>
        <?php endif; ?>

        
        <?php if($errors->any()): ?>
            <div class="rounded-2xl border border-red-500/30 bg-red-500/10 p-4">
                <ul class="list-inside list-disc text-red-400 m-0 p-0 text-sm font-semibold space-y-1">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?php echo e(route('admin.suat-chieus.update', $suatChieu->id)); ?>" method="POST" class="space-y-6 m-0">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                <div class="space-y-2">
                    <label class="text-sm text-gray-400 font-bold block">Phim Trình Chiếu</label>
                    <select name="phim_id" id="phim_id" required <?php echo e($isLocked ? 'disabled' : ''); ?>

                        class="w-full h-11 rounded-xl border border-white/10 bg-[#151515] px-3 text-sm text-white outline-none focus:border-[#d99a32] transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <?php $__currentLoopData = $phims; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $phim): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($phim->id); ?>" data-thoi-luong="<?php echo e($phim->thoi_luong); ?>"
                                <?php echo e(old('phim_id', $suatChieu->phim_id) == $phim->id ? 'selected' : ''); ?>>
                                <?php echo e($phim->ten_phim); ?> (<?php echo e($phim->thoi_luong ?? 90); ?> phút)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php if($isLocked): ?> <input type="hidden" name="phim_id" value="<?php echo e($suatChieu->phim_id); ?>"> <?php endif; ?>
                </div>

                <div class="space-y-2">
                    <label class="text-sm text-gray-400 font-bold block">Cơ Sở Rạp Phim</label>
                    <select name="rap_chieu_phim_id" id="rap_chieu_phim_id" required <?php echo e($isLocked ? 'disabled' : ''); ?>

                        class="w-full h-11 rounded-xl border border-white/10 bg-[#151515] px-3 text-sm text-white outline-none focus:border-[#d99a32] transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <?php $__currentLoopData = $rapChieuPhims; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rap): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($rap->id); ?>" <?php echo e(old('rap_chieu_phim_id', $suatChieu->rap_chieu_phim_id) == $rap->id ? 'selected' : ''); ?>>
                                <?php echo e($rap->ten_rap); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php if($isLocked): ?> <input type="hidden" name="rap_chieu_phim_id" value="<?php echo e($suatChieu->rap_chieu_phim_id); ?>"> <?php endif; ?>
                </div>

                <div class="space-y-2">
                    <label class="text-sm text-gray-400 font-bold block">Phòng Chiếu Mục Tiêu</label>
                    <select name="phong_chieu_id" id="phong_chieu_id" required <?php echo e($isLocked ? 'disabled' : ''); ?>

                        class="w-full h-11 rounded-xl border border-white/10 bg-[#151515] px-3 text-sm text-white outline-none focus:border-[#d99a32] transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <?php $__currentLoopData = $phongChieus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $phong): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($phong->id); ?>" <?php echo e(old('phong_chieu_id', $suatChieu->phong_chieu_id) == $phong->id ? 'selected' : ''); ?>>
                                <?php echo e($phong->ten_phong); ?> (<?php echo e(strtoupper($phong->loai_phong)); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php if($isLocked): ?> <input type="hidden" name="phong_chieu_id" value="<?php echo e($suatChieu->phong_chieu_id); ?>"> <?php endif; ?>
                </div>
            </div>

            
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div class="space-y-2">
                    <label class="text-sm text-gray-400 font-bold block">Ngày Chiếu <span class="text-red-400">*</span></label>
                    <input type="date" name="ngay_chieu" id="ngay_chieu" required <?php echo e($isLocked ? 'disabled' : ''); ?>

                        value="<?php echo e(old('ngay_chieu', \Carbon\Carbon::parse($suatChieu->thoi_gian_chieu)->format('Y-m-d'))); ?>"
                        class="w-full h-11 rounded-xl border border-white/10 bg-[#151515] px-3 text-sm text-white outline-none focus:border-[#d99a32] transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed [&::-webkit-calendar-picker-indicator]:invert">
                    <?php if($isLocked): ?> <input type="hidden" name="ngay_chieu" value="<?php echo e(\Carbon\Carbon::parse($suatChieu->thoi_gian_chieu)->format('Y-m-d')); ?>"> <?php endif; ?>
                </div>

                <div class="space-y-2">
                    <label class="text-sm text-gray-400 font-bold block">Giờ Khởi Chiếu <span class="text-red-400">*</span></label>
                    <input type="time" name="gio_chieu" id="gio_chieu" required <?php echo e($isLocked ? 'disabled' : ''); ?>

                        value="<?php echo e(old('gio_chieu', \Carbon\Carbon::parse($suatChieu->thoi_gian_chieu)->format('H:i'))); ?>"
                        class="w-full h-11 rounded-xl border border-white/10 bg-[#151515] px-3 text-sm text-white outline-none focus:border-[#d99a32] transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed [&::-webkit-calendar-picker-indicator]:invert">
                    <?php if($isLocked): ?> <input type="hidden" name="gio_chieu" value="<?php echo e(\Carbon\Carbon::parse($suatChieu->thoi_gian_chieu)->format('H:i')); ?>"> <?php endif; ?>
                </div>
            </div>

            
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                
                <div class="p-5 rounded-2xl border border-white/5 bg-white/5 space-y-3">
                    <label class="text-sm text-[#f4c56a] font-black uppercase tracking-wider block m-0">
                        Ghi Đè Biểu Giá Suất Chiếu Tùy Chỉnh
                    </label>
                    <div class="relative flex items-center">
                        <input type="number" name="gia_ve_tuy_chinh" placeholder="Dùng biểu giá ma trận tự động..." 
                            value="<?php echo e(old('gia_ve_tuy_chinh', $suatChieu->gia_ve)); ?>"
                            class="h-11 w-full rounded-xl border border-white/10 bg-[#151515] pl-4 pr-12 text-sm text-[#f4c56a] font-black outline-none focus:border-[#d99a32] transition [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                        <span class="absolute right-4 text-xs text-gray-500 font-bold select-none">VND</span>
                    </div>
                    <small class="text-gray-500 block leading-relaxed text-[11px]">
                        Nếu bạn xóa trống ô này, hệ thống sẽ tự động tính toán lại giá vé dựa trên cài đặt tham số ngày thường/cuối tuần và loại phòng máy của rạp.
                    </small>
                </div>

                
                <div class="p-5 rounded-2xl border border-white/5 bg-white/5 space-y-3">
                    <label class="text-sm text-[#f4c56a] font-black uppercase tracking-wider block m-0">
                        Quản Trị Quy Trình Trạng Thái
                    </label>
                    <select name="trang_thai" required
                        class="w-full h-11 rounded-xl border border-white/10 bg-[#151515] px-3 text-sm text-white outline-none focus:border-[#d99a32] transition cursor-pointer">
                        <option value="sap_chieu" <?php echo e($suatChieu->trang_thai !== 'huy' ? 'selected' : ''); ?>>
                            🔄 Kích hoạt chế độ quét tự động Real-time (Khuyên dùng)
                        </option>
                        <option value="huy" <?php echo e($suatChieu->trang_thai === 'huy' ? 'selected' : ''); ?>>
                            🚫 Hủy suất chiếu khẩn cấp (Sự cố phòng máy/Thiết bị)
                        </option>
                    </select>
                    <small class="text-gray-500 block leading-relaxed text-[11px]">
                        Giao diện đã loại bỏ tính năng gán cứng trạng thái Đang chiếu/Đã chiếu bằng tay để nhường quyền xử lý cho Lõi thuật toán gối đầu của CineHome.
                    </small>
                </div>
            </div>

            
            <div class="p-5 rounded-2xl border border-white/5 bg-[#121212]/90 space-y-2">
                <label class="text-xs text-gray-400 font-black uppercase tracking-widest block m-0">
                    <i class="fa-solid fa-desktop text-[#d99a32] mr-1"></i> Bảng tính toán dòng thời gian chiếm dụng phòng máy mới
                </label>
                <input type="text" id="thoi_luong_preview" class="w-full rounded-xl border border-0 bg-white/5 px-4 py-2.5 text-[#f4c56a] font-black outline-none text-sm shadow-inner"
                       value="" readonly>
            </div>

            
            <div class="flex items-center justify-end gap-3 border-t border-white/10 pt-5">
                <a href="<?php echo e(route('admin.suat-chieus.index')); ?>"
                    class="rounded-xl border border-white/10 bg-white/5 px-5 py-2.5 text-sm font-medium text-gray-300 transition hover:bg-white/10 hover:text-white no-underline">
                    Hủy bỏ
                </a>

                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-6 py-2.5 text-sm font-black text-white shadow-lg border-0 cursor-pointer transition hover:opacity-95 duration-200">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    Lưu lại thay đổi
                </button>
            </div>
        </form>
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const phimSelect = document.getElementById('phim_id');
    const ngayChieuInput = document.getElementById('ngay_chieu');
    const gioChieuInput = document.getElementById('gio_chieu');
    const thoiLuongPreview = document.getElementById('thoi_luong_preview');

    // Đọc tham số động giãn cách dọn rạp từ controller truyền xuống
    const thoiGianDonPhong = <?php echo e($thoiGianDonPhong); ?>;

    function updateTimePreview() {
        const selectedOption = phimSelect.options[phimSelect.selectedIndex];
        if (!selectedOption || !selectedOption.value) {
            thoiLuongPreview.value = "Thông tin phim không hợp lệ.";
            return;
        }
        
        const thoiLuong = parseInt(selectedOption.dataset.thoiLuong) || 90;
        
        if (ngayChieuInput.value && gioChieuInput.value) {
            const start = new Date(`${ngayChieuInput.value}T${gioChieuInput.value}`);
            const end = new Date(start.getTime() + (thoiLuong + thoiGianDonPhong) * 60000);
            
            const formatTime = (d) => d.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
            thoiLuongPreview.value = `Dòng thời gian sau căn chỉnh: ${formatTime(start)} - ${formatTime(end)} (Phòng máy chiếm dụng tổng cộng ${thoiLuong + thoiGianDonPhong} phút tính cả vệ sinh phòng chiếu).`;
        } else {
            thoiLuongPreview.value = `Thời lượng phim: ${thoiLuong} phút (+ ${thoiGianDonPhong} phút giãn cách).`;
        }
    }

    phimSelect.addEventListener('change', updateTimePreview);
    ngayChieuInput.addEventListener('change', updateTimePreview);
    gioChieuInput.addEventListener('change', updateTimePreview);
    
    // Khởi chạy tính toán ngay khi tải trang
    updateTimePreview();
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/admin/suat-chieus/edit.blade.php ENDPATH**/ ?>