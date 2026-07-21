<?php $__env->startSection('title', 'CineHome - Đặt vé xem phim'); ?>

<?php $__env->startSection('content'); ?>

    
    <section class="hero-slider">

        <?php $__empty_1 = true; $__currentLoopData = $bannerMovies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $movie): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                // LẤY TRẠNG THÁI TỪ SUẤT CHIẾU
                $status = optional($movie->showtimes->sortBy('thoi_gian_chieu')->first())?->trang_thai;
            ?>

            <div class="hero-slide <?php echo e($index === 0 ? 'active' : ''); ?>"
                style="background-image: url('<?php echo e(asset('storage/movies/' . $movie->poster)); ?>');">
                
                <div
                class="container-fluid px-5 hero-content">
                <div class="hero-info">

                    <div class="hero-badge">
                        <i class="fa-solid fa-fire"></i>
                        Phim hot trong tháng
                    </div>

                    <h1 class="hero-title">
                        <?php echo e($movie->ten_phim); ?>

                    </h1>

                    <p class="hero-desc">
                        <?php echo e($movie->mo_ta); ?>

                    </p>

                    <div class="hero-meta">
                        <span>
                            <i class="fa-solid fa-film"></i>
                            <?php echo e($movie->genres->pluck('ten_the_loai')->join(', ')); ?>

                        </span>

                        <span>
                            <i class="fa-solid fa-clock"></i>
                            <?php echo e($movie->thoi_luong); ?> phút
                        </span>

                        <span>
                            <i class="fa-solid fa-user-shield"></i>
                            <?php echo e($movie->gioi_han_tuoi); ?>

                        </span>
                    </div>

                    <div class="hero-buttons">

                        <?php if($status === \App\Models\SuatChieu::TRANG_THAI_SAP_CHIEU): ?>
                            <a href="<?php echo e(route('dat_ve.chon_ghe', ['movie' => $movie->slug])); ?>" class="btn-book">
                                <i class="fa-solid fa-ticket"></i> Đặt vé ngay
                            </a>
                        <?php else: ?>
                            <a href="<?php echo e(route('user.movies.show', $movie->slug)); ?>" class="btn-book">
                                <i class="fa-solid fa-film"></i> Xem thêm
                            </a>
                        <?php endif; ?>

                        <a href="<?php echo e($movie->trailer_url); ?>" target="_blank" class="btn-trailer">
                            <i class="fa-solid fa-play"></i> Xem trailer
                        </a>

                    </div>

                </div>
            </div>

            </div>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

            <div class="hero-slide active">
                <div class="container-fluid px-5 hero-content">
                    <div class="hero-info">
                        <h1 class="hero-title">Chưa có phim</h1>
                        <p class="hero-desc">Hãy seed dữ liệu phim vào database.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        
        <div class="hot-movies">

            <div class="section-label">
                <i class="fa-solid fa-ranking-star"></i>
                Top 5 phim
            </div>

            <div class="hot-list">

                <?php $__currentLoopData = $bannerMovies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $movie): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="hot-item <?php echo e($index === 0 ? 'active' : ''); ?>" data-slide="<?php echo e($index); ?>">

                        <div class="hot-rank"><?php echo e($index + 1); ?></div>


                        <img src="<?php echo e(asset('storage/movies/' . $movie->poster)); ?>" alt="<?php echo e($movie->ten_phim); ?>">
                        
                        <p><?php echo e($movie->ten_phim); ?></p>

                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            </div>

        </div>

    </section>

    
    <main class="main-section">

        <div class="container-fluid px-5">

            
            <div class="section-title-wrap">
                <h2 class="section-title">
                    Phim <span>đang chiếu</span>
                </h2>
            </div>

            <?php echo $__env->make('partials.movie-section', ['movies' => $nowShowingMovies], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            
            <div class="section-title-wrap mt-5">
                <h2 class="section-title">
                    Phim <span>sắp chiếu</span>
                </h2>
            </div>

            <?php echo $__env->make('partials.movie-section', ['movies' => $comingSoonMovies], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            
            <div class="section-title-wrap mt-5">
                <h2 class="section-title">
                    Phim <span>sắp ra mắt</span>
                </h2>
            </div>

            <?php echo $__env->make('partials.movie-section', ['movies' => $comingLaterMovies], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        </div>

    </main>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const slides = document.querySelectorAll('.hero-slide');
            const hotItems = document.querySelectorAll('.hot-item');

            hotItems.forEach(function(item) {
                item.addEventListener('click', function() {

                    const index = Number(this.dataset.slide);

                    slides.forEach(slide => slide.classList.remove('active'));
                    hotItems.forEach(hot => hot.classList.remove('active'));

                    if (slides[index]) {
                        slides[index].classList.add('active');
                    }

                    this.classList.add('active');
                });
            });

        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\WD-11-Cinehome-cinema\resources\views/user/home.blade.php ENDPATH**/ ?>