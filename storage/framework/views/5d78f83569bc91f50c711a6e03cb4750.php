<?php $__env->startSection('title', 'Danh sách phim'); ?>

<?php $__env->startSection('content'); ?>

    <section class="min-h-screen bg-[#0b0705] text-white pt-32 pb-10">
        <div class="max-w-[1800px] mx-auto px-8">

            
            <form action="<?php echo e(route('user.phims.index')); ?>" method="GET"
                class="bg-[#151515] border border-white/10 rounded-2xl p-5 mb-8 grid grid-cols-1 md:grid-cols-5 gap-4">

                <input type="text" name="tim_kiem" value="<?php echo e(request('tim_kiem')); ?>" placeholder="Tìm tên phim..."
                    class="bg-[#0b0705] border border-white/10 text-white rounded-xl px-4 py-3">

                <select name="the_loai" class="bg-[#0b0705] border border-white/10 text-white rounded-xl px-4 py-3">
                    <option value="">Thể loại</option>
                    <?php $__currentLoopData = $genres; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $genre): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($genre->ten_the_loai); ?>"
                            <?php echo e(request('the_loai') == $genre->ten_the_loai ? 'selected' : ''); ?>>
                            <?php echo e($genre->ten_the_loai); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>

                <select name="quoc_gia" class="bg-[#0b0705] border border-white/10 text-white rounded-xl px-4 py-3">
                    <option value="">Quốc gia</option>
                    <?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($country->ten_quoc_gia); ?>"
                            <?php echo e(request('quoc_gia') == $country->ten_quoc_gia ? 'selected' : ''); ?>>
                            <?php echo e($country->ten_quoc_gia); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>

                <select name="status" class="bg-[#0b0705] border border-white/10 text-white rounded-xl px-4 py-3">
                    <option value="">Trạng thái</option>
                    <option value="dang_chieu" <?php echo e(request('status') == 'dang_chieu' ? 'selected' : ''); ?>>Đang chiếu</option>
                    <option value="sap_chieu" <?php echo e(request('status') == 'sap_chieu' ? 'selected' : ''); ?>>Sắp chiếu</option>
                    <option value="sap_ra_mat" <?php echo e(request('status') == 'sap_ra_mat' ? 'selected' : ''); ?>>Sắp ra mắt
                    </option>
                </select>

                <div class="flex gap-3">
                    <button class="flex-1 bg-[#f5a623] text-black font-bold rounded-xl">
                        Tìm
                    </button>

                    <a href="<?php echo e(route('user.phims.index')); ?>"
                        class="w-[52px] flex items-center justify-center bg-white/10 rounded-xl">
                        ⟳
                    </a>
                </div>

            </form>

            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

                <?php $__empty_1 = true; $__currentLoopData = $movies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movie): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="bg-[#151515] border border-white/10 rounded-2xl overflow-hidden">

                        
                        <a href="<?php echo e(route('user.phims.show', $movie->slug)); ?>">
                            <img src="<?php echo e(asset('storage/movies/' . $movie->poster)); ?>"
                                class="w-full h-[250px] object-cover">
                        </a>

                        <div class="p-4">

                            
                            <h2 class="font-bold text-lg mb-2">
                                <?php echo e($movie->ten_phim); ?>

                            </h2>

                            
                            <p class="text-sm text-gray-400">
                                🎬 <?php echo e($movie->genres->pluck('ten_the_loai')->join(', ')); ?>

                            </p>

                            
                            <p class="text-sm text-gray-400">
                                🌍 <?php echo e($movie->country->ten_quoc_gia); ?>

                            </p>

                            
                            <p class="text-sm text-gray-400">
                                ⏱ <?php echo e($movie->thoi_luong); ?> phút
                            </p>

                            <?php
                                $firstShowtime = $movie->showtimes->sortBy('thoi_gian_chieu')->first();

                                $canBook = $firstShowtime
                                    ? $firstShowtime->trang_thai === \App\Models\SuatChieu::TRANG_THAI_SAP_CHIEU
                                    : false;
                            ?>

                            <div class="flex items-center gap-3 mt-4">

                                <?php if($canBook): ?>
                                    <a href="<?php echo e(route('dat_ve.chon_ghe', $movie->slug)); ?>"
                                        class="booking-link flex-1 text-center bg-[#f5a623] font-bold py-3 rounded-xl shadow-lg hover:bg-[#ffca40] transition">
                                        <i class="fa-solid fa-ticket mr-2"></i>
                                        Đặt vé
                                    </a>
                                <?php endif; ?>

                                <a href="<?php echo e(route('user.movies.show', $movie->slug)); ?>"
                                    class="flex-1 text-center bg-[#1d1d1d] text-white font-bold py-3 rounded-xl border border-white/10 hover:bg-[#2b2b2b] transition">
                                    Chi tiết
                                </a>

                            </div>

                        </div>
                    </div>

                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                    <div class="col-span-full text-center text-gray-400 py-20">
                        Không tìm thấy phim
                    </div>
                <?php endif; ?>

            </div>

        </div>
    </section>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\WD-11-Cinehome-cinema\resources\views/user/phims/index.blade.php ENDPATH**/ ?>