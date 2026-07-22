<?php $__env->startSection('title', 'Chọn phim và suất chiếu'); ?>

<?php $__env->startSection('content'); ?>
    <div class="booking-flow-page booking-movie-select-page" lang="vi" spellcheck="false">
        <section class="booking-flow-hero">
            <div class="booking-flow-hero-copy">
                <span class="booking-eyebrow">
                    <i class="fa-solid fa-ticket"></i>
                    Đặt vé CineHome
                </span>
                <h1>Chọn phim, chọn suất, giữ ghế thật nhanh.</h1>
                <p>
                    Lịch chiếu tại <strong><?php echo e($rap->ten_rap); ?></strong>. Chọn ngày bên dưới để xem các suất đang mở bán
                    và tiếp tục đặt vé đúng giờ bạn muốn.
                </p>
            </div>

            <div class="booking-stepper" aria-label="Tiến trình đặt vé">
                <div class="booking-step is-active">
                    <span>1</span>
                    <strong>Chọn phim</strong>
                </div>
                <div class="booking-step">
                    <span>2</span>
                    <strong>Chọn ghế</strong>
                </div>
                <div class="booking-step">
                    <span>3</span>
                    <strong>Đồ ăn</strong>
                </div>
                <div class="booking-step">
                    <span>4</span>
                    <strong>Thanh toán</strong>
                </div>
            </div>
        </section>

        <?php
            $activeDateOption = collect($dateOptions)->firstWhere('active');
            $activeDateLabel = $activeDateOption['label'] ?? $selectedDate->format('d/m/Y');
            $weekdayLabels = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
        ?>

        <section class="booking-date-panel" aria-label="Chọn ngày chiếu">
            <div class="booking-date-heading">
                <div>
                    <span>Lịch chiếu</span>
                    <h2><?php echo e($activeDateLabel); ?> • <?php echo e($selectedDate->format('d/m/Y')); ?></h2>
                </div>
                <p><?php echo e($rap->dia_chi); ?></p>
            </div>

            <form id="dateForm" action="<?php echo e(request()->url()); ?>" method="GET" class="booking-date-form">
                <input type="hidden" name="ngay_chieu" id="selectedDateInput" value="<?php echo e($selectedDate->toDateString()); ?>">

                <button type="button" id="prevDate" class="booking-date-nav" aria-label="Ngày trước">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>

                <div id="dateList" class="booking-date-track">
                    <?php $__currentLoopData = $dateOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dateOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $dateCarbon = \Carbon\Carbon::parse($dateOption['date']);
                        ?>
                        <button type="button" data-date="<?php echo e($dateOption['date']); ?>"
                            class="booking-date-chip <?php echo e($dateOption['active'] ? 'is-active' : ''); ?>"
                            aria-pressed="<?php echo e($dateOption['active'] ? 'true' : 'false'); ?>">
                            <span><?php echo e($dateOption['label']); ?></span>
                            <strong><?php echo e($dateCarbon->format('d')); ?></strong>
                            <small><?php echo e($weekdayLabels[$dateCarbon->dayOfWeek] ?? $dateCarbon->format('D')); ?></small>
                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <button type="button" id="nextDate" class="booking-date-nav" aria-label="Ngày sau">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </form>
        </section>

        <section class="booking-showtime-section">
            <div class="booking-section-head">
                <div>
                    <span>Suất chiếu còn vé</span>
                    <h2>Chọn giờ bắt đầu</h2>
                </div>
                <a href="<?php echo e(route('user.showtimes.index', ['ngay_chieu' => $selectedDate->toDateString()])); ?>">
                    Xem lịch chiếu đầy đủ
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <div class="booking-showtime-list">
                <?php $__empty_1 = true; $__currentLoopData = $suatChieuTheoPhim; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $suatChieus): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $phim = $suatChieus->first()->phim;
                        $showtimes = $suatChieus;
                        $posterUrl = $phim->poster
                            ? asset('storage/movies/' . $phim->poster)
                            : 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=800&auto=format&fit=crop';
                    ?>

                    <article class="booking-showtime-card">
                        <a href="<?php echo e(route('user.movies.show', $phim->slug)); ?>" class="booking-showtime-poster"
                            aria-label="Xem chi tiết <?php echo e($phim->ten_phim); ?>">
                            <img src="<?php echo e($posterUrl); ?>" alt="<?php echo e($phim->ten_phim); ?>">
                        </a>

                        <div class="booking-showtime-body">
                            <div class="booking-showtime-top">
                                <div>
                                    <div class="booking-movie-tags">
                                        <?php if(!empty($phim->gioi_han_tuoi)): ?>
                                            <span class="age"><?php echo e($phim->gioi_han_tuoi); ?></span>
                                        <?php endif; ?>
                                        <span>2D</span>
                                        <?php if(!empty($phim->thoi_luong)): ?>
                                            <span><?php echo e($phim->thoi_luong); ?> phút</span>
                                        <?php endif; ?>
                                    </div>
                                    <h3><?php echo e($phim->ten_phim); ?></h3>
                                </div>
                                <a href="<?php echo e(route('user.movies.show', $phim->slug)); ?>" class="booking-detail-link">
                                    Chi tiết
                                </a>
                            </div>

                            <?php if($phim->genres->isNotEmpty()): ?>
                                <p class="booking-movie-genres">
                                    <?php echo e($phim->genres->pluck('ten_the_loai')->join(' • ')); ?>

                                </p>
                            <?php endif; ?>

                            <?php if(!empty($phim->mo_ta)): ?>
                                <p class="booking-movie-desc">
                                    <?php echo e(\Illuminate\Support\Str::limit(strip_tags($phim->mo_ta), 150)); ?>

                                </p>
                            <?php endif; ?>

                            <div class="booking-time-grid" aria-label="Danh sách suất chiếu của <?php echo e($phim->ten_phim); ?>">
                                <?php $__currentLoopData = $showtimes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $suat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($suat->ghe_trong > 0): ?>
                                        <a href="<?php echo e(route('dat_ve.chon_ghe', ['movie' => $suat->id])); ?>"
                                            class="booking-time-chip">
                                            <strong><?php echo e($suat->thoi_gian_chieu->format('H:i')); ?></strong>
                                            <span><?php echo e($suat->ghe_trong); ?>/<?php echo e($suat->tong_ghe); ?> ghế</span>
                                        </a>
                                    <?php else: ?>
                                        <span class="booking-time-chip is-disabled">
                                            <strong><?php echo e($suat->thoi_gian_chieu->format('H:i')); ?></strong>
                                            <span>Hết vé</span>
                                        </span>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="booking-flow-empty">
                        <i class="fa-solid fa-film"></i>
                        <h2>Chưa có suất chiếu</h2>
                        <p>Hiện chưa có suất chiếu nào cho ngày này. Hãy chọn một ngày khác để tiếp tục đặt vé.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dateForm = document.getElementById('dateForm');
            const dateInput = document.getElementById('selectedDateInput');
            const dateButtons = Array.from(document.querySelectorAll('[data-date]'));
            const prevDate = document.getElementById('prevDate');
            const nextDate = document.getElementById('nextDate');
            let isSubmitting = false;

            if (!dateForm || !dateInput || dateButtons.length === 0) return;

            let activeIndex = dateButtons.findIndex((button) => button.dataset.date === dateInput.value);
            if (activeIndex < 0) activeIndex = 0;

            function submitOnce() {
                if (isSubmitting) return;
                isSubmitting = true;
                window.setTimeout(() => dateForm.submit(), 70);
            }

            function setActiveIndex(index, shouldSubmit) {
                if (index < 0 || index >= dateButtons.length) return;

                activeIndex = index;
                dateInput.value = dateButtons[activeIndex].dataset.date;

                dateButtons.forEach((button, currentIndex) => {
                    const active = currentIndex === activeIndex;
                    button.classList.toggle('is-active', active);
                    button.setAttribute('aria-pressed', active ? 'true' : 'false');
                });

                dateButtons[activeIndex].scrollIntoView({
                    inline: 'center',
                    behavior: 'smooth',
                    block: 'nearest'
                });

                if (shouldSubmit) submitOnce();
            }

            dateButtons.forEach((button, index) => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    setActiveIndex(index, true);
                });
            });

            prevDate?.addEventListener('click', function(event) {
                event.preventDefault();
                setActiveIndex(Math.max(0, activeIndex - 1), false);
            });

            nextDate?.addEventListener('click', function(event) {
                event.preventDefault();
                setActiveIndex(Math.min(dateButtons.length - 1, activeIndex + 1), false);
            });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\laragon\www\Cinema\WD-11-Cinehome-cinema\resources\views/user/dat_ve/chon_phim.blade.php ENDPATH**/ ?>