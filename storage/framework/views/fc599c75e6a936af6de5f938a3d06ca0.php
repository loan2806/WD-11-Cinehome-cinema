<?php $__env->startSection('title', 'Tin tức & Khuyến mãi'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $featuredHero = $tinNoiBat->first();
        $featuredSide = $tinNoiBat->skip(1);
        $fallbackHero = 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=1400&auto=format&fit=crop';
        $fallbackCard = 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?q=80&w=900&auto=format&fit=crop';

        $imageUrl = function ($tin, $fallback) {
            return $tin->hinh_anh && file_exists(public_path('storage/' . $tin->hinh_anh))
                ? asset('storage/' . $tin->hinh_anh)
                : $fallback;
        };
    ?>

    <div class="news-page" lang="vi" spellcheck="false">
        <section class="news-hero">
            <div class="news-hero-copy">
                <span class="news-eyebrow">
                    <i class="fa-solid fa-newspaper"></i>
                    CineHome Newsroom
                </span>
                <h1>Tin điện ảnh, ưu đãi và câu chuyện sau màn ảnh.</h1>
                <p>
                    Cập nhật phim mới, sự kiện rạp chiếu và voucher đang mở. Mọi thứ được sắp xếp để bạn đọc nhanh,
                    chọn phim nhanh và đặt vé cũng nhanh.
                </p>
            </div>

            <form method="GET" action="<?php echo e(route('user.tin-tuc.index')); ?>" class="news-hero-search">
                <?php if(request('danh_muc')): ?>
                    <input type="hidden" name="danh_muc" value="<?php echo e(request('danh_muc')); ?>">
                <?php endif; ?>
                <?php if(request('tag')): ?>
                    <input type="hidden" name="tag" value="<?php echo e(request('tag')); ?>">
                <?php endif; ?>

                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Tìm tin phim, sự kiện, voucher...">
                <button type="submit">Tìm</button>
            </form>
        </section>

        <nav class="news-category-rail" aria-label="Danh mục tin tức">
            <a href="<?php echo e(route('user.tin-tuc.index')); ?>" class="<?php echo e(!request('danh_muc') && !request('tag') ? 'is-active' : ''); ?>">
                <i class="fa-solid fa-border-all"></i>
                Tất cả
            </a>
            <?php $__currentLoopData = $danhMucs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $danhMuc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('user.tin-tuc.index', ['danh_muc' => $danhMuc->slug])); ?>"
                    class="<?php echo e(request('danh_muc') === $danhMuc->slug ? 'is-active' : ''); ?>">
                    <i class="<?php echo e($danhMuc->icon ?? 'fa-solid fa-tag'); ?>"></i>
                    <?php echo e($danhMuc->ten_danh_muc); ?>

                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('user.khuyen-mai.index')); ?>" class="voucher-link">
                <i class="fa-solid fa-ticket"></i>
                Voucher
            </a>
        </nav>

        <?php if($tinNoiBat->count() > 0): ?>
            <section class="news-featured-section">
                <div class="news-section-head">
                    <div>
                        <span>Tin nổi bật</span>
                        <h2>Đang được quan tâm</h2>
                    </div>
                    <span class="news-hot-badge">
                        <i class="fa-solid fa-fire"></i>
                        HOT
                    </span>
                </div>

                <div class="news-featured-grid <?php echo e($featuredSide->count() === 0 ? 'is-single' : ''); ?>">
                    <?php if($featuredHero): ?>
                        <a href="<?php echo e(route('user.tin-tuc.show', $featuredHero->slug)); ?>" class="news-featured-main">
                            <img src="<?php echo e($imageUrl($featuredHero, $fallbackHero)); ?>" alt="<?php echo e($featuredHero->tieu_de); ?>">
                            <div class="news-featured-overlay">
                                <?php if($featuredHero->danhMucTin): ?>
                                    <span class="news-category-badge">
                                        <i class="<?php echo e($featuredHero->danhMucTin->icon ?? 'fa-solid fa-tag'); ?>"></i>
                                        <?php echo e($featuredHero->danhMucTin->ten_danh_muc); ?>

                                    </span>
                                <?php endif; ?>
                                <h3><?php echo e($featuredHero->tieu_de); ?></h3>
                                <p><?php echo e($featuredHero->mo_ta_ngan); ?></p>
                                <div class="news-meta">
                                    <span><i class="fa-solid fa-calendar"></i><?php echo e($featuredHero->ngay_dang ? $featuredHero->ngay_dang->format('d/m/Y') : now()->format('d/m/Y')); ?></span>
                                    <span><i class="fa-solid fa-eye"></i><?php echo e(number_format($featuredHero->luot_xem)); ?></span>
                                </div>
                            </div>
                        </a>
                    <?php endif; ?>

                    <?php if($featuredSide->count() > 0): ?>
                        <div class="news-featured-side">
                            <?php $__currentLoopData = $featuredSide; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tin): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="<?php echo e(route('user.tin-tuc.show', $tin->slug)); ?>" class="news-side-card">
                                    <img src="<?php echo e($imageUrl($tin, $fallbackCard)); ?>" alt="<?php echo e($tin->tieu_de); ?>">
                                    <div>
                                        <?php if($tin->danhMucTin): ?>
                                            <span><?php echo e($tin->danhMucTin->ten_danh_muc); ?></span>
                                        <?php endif; ?>
                                        <h3><?php echo e($tin->tieu_de); ?></h3>
                                        <small><?php echo e($tin->ngay_dang ? $tin->ngay_dang->format('d/m/Y') : now()->format('d/m/Y')); ?></small>
                                    </div>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if($vouchers->count() > 0): ?>
            <section class="news-voucher-section">
                <div class="news-section-head">
                    <div>
                        <span>Ưu đãi hôm nay</span>
                        <h2>Voucher đang mở</h2>
                    </div>
                    <a href="<?php echo e(route('user.khuyen-mai.index')); ?>">
                        Xem tất cả
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <div class="news-voucher-grid">
                    <?php $__currentLoopData = $vouchers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $voucher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $daysLeft = $voucher->ngay_het_han ? now()->diffInDays($voucher->ngay_het_han, false) : null;
                        ?>
                        <article class="news-voucher-card">
                            <div class="news-voucher-top">
                                <span><?php echo e($voucher->ma_voucher); ?></span>
                                <?php if(!is_null($daysLeft) && $daysLeft > 0 && $daysLeft <= 7): ?>
                                    <small>Còn <?php echo e(floor($daysLeft)); ?> ngày</small>
                                <?php endif; ?>
                            </div>
                            <h3><?php echo e($voucher->ten_voucher); ?></h3>
                            <div class="news-voucher-value">
                                <span>Giảm</span>
                                <strong><?php echo e(number_format($voucher->gia_tri_giam, 0, ',', '.')); ?>đ</strong>
                            </div>
                            <?php if($voucher->ngay_het_han): ?>
                                <p>Hạn dùng: <?php echo e(\Carbon\Carbon::parse($voucher->ngay_het_han)->format('d/m/Y')); ?></p>
                            <?php endif; ?>
                            <button type="button" data-voucher-id="<?php echo e($voucher->id); ?>">
                                <i class="fa-solid fa-bolt"></i>
                                Sử dụng ngay
                            </button>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
        <?php endif; ?>

        <section class="news-latest-section">
            <div class="news-section-head">
                <div>
                    <span>Bài mới</span>
                    <h2>Tin mới nhất</h2>
                </div>
                <?php if(request('search') || request('danh_muc') || request('tag')): ?>
                    <a href="<?php echo e(route('user.tin-tuc.index')); ?>">
                        Xóa bộ lọc
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                <?php endif; ?>
            </div>

            <?php if($tinTucs->count() > 0): ?>
                <div class="news-card-grid">
                    <?php $__currentLoopData = $tinTucs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tin): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('user.tin-tuc.show', $tin->slug)); ?>" class="news-card">
                            <figure>
                                <img src="<?php echo e($imageUrl($tin, $fallbackCard)); ?>" alt="<?php echo e($tin->tieu_de); ?>">
                                <?php if($tin->danhMucTin): ?>
                                    <span class="news-category-badge"><?php echo e($tin->danhMucTin->ten_danh_muc); ?></span>
                                <?php endif; ?>
                            </figure>
                            <div class="news-card-body">
                                <h3><?php echo e($tin->tieu_de); ?></h3>
                                <p><?php echo e($tin->mo_ta_ngan); ?></p>
                                <div class="news-meta">
                                    <span><i class="fa-solid fa-calendar"></i><?php echo e($tin->ngay_dang ? $tin->ngay_dang->format('d/m/Y') : now()->format('d/m/Y')); ?></span>
                                    <span><i class="fa-solid fa-eye"></i><?php echo e(number_format($tin->luot_xem)); ?></span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <?php if($tinTucs->hasPages()): ?>
                    <div class="news-pagination">
                        <?php if($tinTucs->onFirstPage()): ?>
                            <span><i class="fa-solid fa-chevron-left"></i></span>
                        <?php else: ?>
                            <a href="<?php echo e($tinTucs->previousPageUrl()); ?>"><i class="fa-solid fa-chevron-left"></i></a>
                        <?php endif; ?>

                        <?php $__currentLoopData = $tinTucs->getUrlRange(max(1, $tinTucs->currentPage() - 1), min($tinTucs->lastPage(), $tinTucs->currentPage() + 1)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($page === $tinTucs->currentPage()): ?>
                                <span class="is-current"><?php echo e($page); ?></span>
                            <?php else: ?>
                                <a href="<?php echo e($url); ?>"><?php echo e($page); ?></a>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <?php if($tinTucs->hasMorePages()): ?>
                            <a href="<?php echo e($tinTucs->nextPageUrl()); ?>"><i class="fa-solid fa-chevron-right"></i></a>
                        <?php else: ?>
                            <span><i class="fa-solid fa-chevron-right"></i></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="news-empty-state">
                    <i class="fa-solid fa-newspaper"></i>
                    <h3>Chưa có tin tức phù hợp</h3>
                    <p>Thử đổi danh mục hoặc tìm kiếm từ khóa khác để xem thêm bài viết.</p>
                </div>
            <?php endif; ?>
        </section>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[data-voucher-id]').forEach(function(button) {
                button.addEventListener('click', function() {
                    suDungVoucher(this.dataset.voucherId);
                });
            });
        });

        function suDungVoucher(voucherId) {
            <?php if(auth()->guard()->guest()): ?>
                const loginButton = document.querySelector('[data-auth-open="login"]');
                if (loginButton) {
                    loginButton.click();
                    return;
                }
                window.location.href = '<?php echo e(route('login')); ?>';
                return;
            <?php endif; ?>

            fetch('<?php echo e(route('user.voucher.save-tam')); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    voucher_id: voucherId
                })
            })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.success) {
                        window.location.href = '<?php echo e(route('dat_ve.chon_phim')); ?>';
                        return;
                    }

                    alert(data.message || 'Có lỗi xảy ra khi lưu voucher.');
                })
                .catch(function() {
                    window.location.href = '<?php echo e(route('dat_ve.chon_phim')); ?>';
                });
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\WD-11-Cinehome-cinema - Copy\WD-11-Cinehome-cinema - Copy\resources\views/user/tin-tuc/index.blade.php ENDPATH**/ ?>