<?php $__env->startSection('title', 'Lich chieu - CineHome'); ?>

<?php $__env->startSection('content'); ?>
<section class="min-h-screen bg-[#0b0705] px-6 pt-32 pb-12 text-white">
    <div class="mx-auto max-w-7xl">
        <h1 class="text-4xl font-black">Lich <span class="text-[#f5a623]">chieu phim</span></h1>
        <p class="mt-2 text-gray-400">Tim lich chieu theo phim, rap va ngay chieu.</p>

        <form method="GET" action="<?php echo e(route('user.showtimes.index')); ?>" class="mt-8 grid gap-4 rounded-2xl border border-white/10 bg-[#151515] p-5 md:grid-cols-5">
            <select name="phim_id" class="rounded-xl border border-white/10 bg-[#0b0705] px-4 py-3 text-white">
                <option value="">Tat ca phim</option>
                <?php $__currentLoopData = $movies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movie): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($movie->id); ?>" <?php if(request('phim_id') == $movie->id): echo 'selected'; endif; ?>><?php echo e($movie->ten_phim); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <select name="rap_chieu_phim_id" class="rounded-xl border border-white/10 bg-[#0b0705] px-4 py-3 text-white">
                <option value="">Tat ca rap</option>
                <?php $__currentLoopData = $rapChieuPhims; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rap): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($rap->id); ?>" <?php if(request('rap_chieu_phim_id') == $rap->id): echo 'selected'; endif; ?>><?php echo e($rap->ten_rap); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <input type="date" name="ngay_chieu" value="<?php echo e(request('ngay_chieu')); ?>" class="rounded-xl border border-white/10 bg-[#0b0705] px-4 py-3 text-white">

            <select name="trang_thai" class="rounded-xl border border-white/10 bg-[#0b0705] px-4 py-3 text-white">
                <option value="">Trang thai</option>
                <option value="dang_chieu" <?php if(request('trang_thai') === 'dang_chieu'): echo 'selected'; endif; ?>>Dang chieu</option>
                <option value="sap_chieu" <?php if(request('trang_thai') === 'sap_chieu'): echo 'selected'; endif; ?>>Sap chieu</option>
            </select>

            <div class="flex gap-3">
                <button class="flex-1 rounded-xl bg-[#f5a623] font-black text-black hover:bg-[#ffc04d]">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
                <a href="<?php echo e(route('user.showtimes.index')); ?>" class="flex w-14 items-center justify-center rounded-xl bg-white/10 text-white hover:bg-white/20">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>

        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <?php $__empty_1 = true; $__currentLoopData = $suatChieus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $suatChieu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <article class="flex min-h-[430px] flex-col overflow-hidden rounded-2xl border border-white/10 bg-[#151515]">
                    <img
                        src="<?php echo e(asset('storage/movies/' .  $suatChieu->phim->poster)); ?>"
                        alt="<?php echo e($suatChieu->phim->ten_phim); ?>"
                        class="h-52 w-full object-cover"
                    >

                    <div class="flex flex-1 flex-col p-4">
                        <h2 class="min-h-[52px] text-lg font-black text-[#f5a623]"><?php echo e($suatChieu->phim->ten_phim); ?></h2>
                        <p class="mt-3 text-sm text-gray-300"><i class="fa-solid fa-building text-[#f5a623]"></i> <?php echo e($suatChieu->rapChieuPhim->ten_rap); ?></p>
                        <p class="mt-2 text-sm text-gray-400"><i class="fa-solid fa-calendar-days text-[#f5a623]"></i> <?php echo e($suatChieu->thoi_gian_chieu->format('d/m/Y')); ?></p>
                        <p class="mt-2 text-sm text-gray-400"><i class="fa-solid fa-clock text-[#f5a623]"></i> <?php echo e($suatChieu->thoi_gian_chieu->format('H:i')); ?></p>
                        <p class="mt-2 text-sm text-gray-400"><i class="fa-solid fa-ticket text-[#f5a623]"></i> <?php echo e(number_format($suatChieu->gia_ve, 0, ',', '.')); ?> VND</p>

                        <div class="mt-auto flex gap-3 pt-5">
                            <a href="<?php echo e(route('dat_ve.chon_ghe', ['movie' => $movie->slug])); ?>" class="flex-1 rounded-xl bg-[#f5a623] py-2 text-center text-sm font-black text-black hover:bg-[#ffc04d]">
                                Dat ve
                            </a>
                            <a href="<?php echo e(route('user.showtimes.show', $suatChieu)); ?>" class="flex-1 rounded-xl bg-white/10 py-2 text-center text-sm font-bold text-white hover:bg-white/20">
                                Chi tiet
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="rounded-2xl border border-white/10 bg-[#151515] p-10 text-center text-gray-400 lg:col-span-4">
                    Khong co lich chieu phu hop.
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\WD-11-Cinehome-cinema\resources\views/user/showtimes/index.blade.php ENDPATH**/ ?>