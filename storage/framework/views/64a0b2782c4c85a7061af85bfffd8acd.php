<div class="row g-4">

    <?php $__empty_1 = true; $__currentLoopData = $movies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movie): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            // LẤY TRẠNG THÁI TỪ SUẤT CHIẾU
            if ($movie->showtimes->contains('trang_thai', \App\Models\SuatChieu::TRANG_THAI_DANG_CHIEU)) {
                $status = \App\Models\SuatChieu::TRANG_THAI_DANG_CHIEU;
            } elseif ($movie->showtimes->contains('trang_thai', \App\Models\SuatChieu::TRANG_THAI_SAP_CHIEU)) {
                $status = \App\Models\SuatChieu::TRANG_THAI_SAP_CHIEU;
            } elseif ($movie->showtimes->contains('trang_thai', \App\Models\SuatChieu::TRANG_THAI_SAP_RA_MAT)) {
                $status = \App\Models\SuatChieu::TRANG_THAI_SAP_RA_MAT;
            } else {
                $status = \App\Models\SuatChieu::TRANG_THAI_DA_CHIEU;
            }

            // DEFAULT
            $badgeClass = '';
            $buttonText = '';
            $buttonClass = '';
            $buttonUrl = '';
            $buttonIcon = '';
            $buttonStyle = '';
        ?>

        
        <?php if($status === \App\Models\SuatChieu::TRANG_THAI_DA_CHIEU): ?>
            <?php continue; ?>
        <?php endif; ?>

        <?php
            /*
        |--------------------------------------------------------------------------
        | SẮP RA MẮT
        |--------------------------------------------------------------------------
        */
            if ($status === \App\Models\SuatChieu::TRANG_THAI_SAP_RA_MAT) {
                $badgeClass = 'bg-pink-600 text-white';

                $buttonText = 'Quan tâm';

                $buttonClass = 'bg-pink-600 text-white hover:bg-pink-500';

                $buttonUrl = route('user.movies.show', $movie->slug);

                $buttonIcon = 'fa-regular fa-heart';
            } /*
        |--------------------------------------------------------------------------
        | SẮP CHIẾU
        |--------------------------------------------------------------------------
        */ elseif (
                $status === \App\Models\SuatChieu::TRANG_THAI_SAP_CHIEU
            ) {
                $badgeClass = 'bg-blue-500 text-white';

                $buttonText = 'Đặt vé';

                $buttonClass = 'bg-[#f5a623] text-black hover:bg-[#ffc04d]';

                $showtime = $movie->showtimes
                    ->where('thoi_gian_chieu', '>=', now())
                    ->sortBy('thoi_gian_chieu')
                    ->first();

                if ($showtime) {
                    $buttonUrl = route('dat_ve.chon_ghe', $movie);
                } else {
                    $buttonUrl = route('user.movies.show', $movie->slug);
                }
                $buttonIcon = 'fa-solid fa-ticket';
            } /*
        |--------------------------------------------------------------------------
        | ĐANG CHIẾU
        |--------------------------------------------------------------------------
        */ else {
                $badgeClass = 'bg-[#f5a623] text-black';

                $buttonText = 'Chi tiết';

                $buttonClass =
                    'flex-1 text-center bg-[#1d1d1d] text-white font-bold py-3 rounded-xl  hover:bg-[#2b2b2b] transition';

                $buttonUrl = route('user.movies.show', $movie->slug);

                $buttonStyle = '';
            }
        ?>

        <div class="col-6 col-md-4 col-lg-3 col-xl-2">

            <div class="movie-card">

                
                <div class="movie-poster relative overflow-hidden">

                    <img src="<?php echo e(asset('storage/movies/' . $movie->poster)); ?>" alt="<?php echo e($movie->ten_phim); ?>"
                        class="w-full h-full object-cover">

                    
                    <div
                        class="absolute top-3 right-3 z-10 px-3 py-2 rounded-full bg-black/70 text-white text-xs font-bold shadow-lg">
                        <?php echo e($movie->gioi_han_tuoi); ?>

                    </div>

                </div>

                
                <div class="movie-body">

                    
                    <h3 class="movie-title line-clamp-2 min-h-[56px]">
                        <?php echo e($movie->ten_phim); ?>

                    </h3>

                    
                    <div class="movie-info space-y-1 text-sm text-gray-300">

                        <p class="mb-0">
                            <i class="fa-solid fa-film mr-2"></i>
                            <?php echo e($movie->genres->pluck('ten_the_loai')->join(', ')); ?>

                        </p>

                        <p class="mb-0 text-xs text-gray-400">
                            <i class="fa-solid fa-flag mr-2"></i>
                            <?php echo e($movie->country?->ten_quoc_gia); ?>

                        </p>

                        <p class="mb-0 text-xs text-gray-400">
                            <i class="fa-solid fa-clock mr-2"></i>
                            <?php echo e($movie->thoi_luong); ?> phút
                        </p>

                        <p class="mb-0 text-xs text-gray-400">
                            <i class="fa-solid fa-user-shield mr-2"></i>
                            <?php echo e($movie->gioi_han_tuoi); ?>

                        </p>

                    </div>

                    
                    <div class="movie-actions">

                        <a href="<?php echo e($buttonUrl); ?>" class="btn-small-book <?php echo e($buttonClass); ?>"
                            style="<?php echo e($buttonStyle); ?>">

                            <i class="<?php echo e($buttonIcon); ?> mr-1"></i>
                            <?php echo e($buttonText); ?>


                        </a>

                        <?php if($buttonText !== 'Chi tiết'): ?>
                            <a href="<?php echo e(route('user.movies.show', $movie->slug)); ?>" class="btn-small-detail">
                                Chi tiết
                            </a>
                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>

    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

        <div class="col-12 text-center text-secondary py-5">
            Chưa có phim.
        </div>
    <?php endif; ?>

</div>
<?php /**PATH C:\laragon\www\WD-11-Cinehome-cinema\resources\views/partials/movie-section.blade.php ENDPATH**/ ?>