<?php if($paginator->hasPages()): ?>
    <?php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $start = max($current - 2, 1);
        $end = min($current + 2, $last);
    ?>

    <nav class="admin-pagination" aria-label="Phân trang quản trị">
        <div class="admin-pagination__meta">
            <span>Trang <?php echo e($current); ?>/<?php echo e($last); ?></span>
            <strong><?php echo e(number_format($paginator->total())); ?></strong>
            <span>kết quả</span>
        </div>

        <div class="admin-pagination__controls">
            <?php if($paginator->onFirstPage()): ?>
                <span class="admin-pagination__btn is-disabled" aria-disabled="true">
                    <i class="fa-solid fa-arrow-left"></i>
                    Trước
                </span>
            <?php else: ?>
                <a href="<?php echo e($paginator->previousPageUrl()); ?>" class="admin-pagination__btn" rel="prev">
                    <i class="fa-solid fa-arrow-left"></i>
                    Trước
                </a>
            <?php endif; ?>

            <?php if($start > 1): ?>
                <a href="<?php echo e($paginator->url(1)); ?>" class="admin-pagination__page">1</a>

                <?php if($start > 2): ?>
                    <span class="admin-pagination__dots">...</span>
                <?php endif; ?>
            <?php endif; ?>

            <?php for($page = $start; $page <= $end; $page++): ?>
                <?php if($page === $current): ?>
                    <span class="admin-pagination__page is-active" aria-current="page"><?php echo e($page); ?></span>
                <?php else: ?>
                    <a href="<?php echo e($paginator->url($page)); ?>" class="admin-pagination__page"><?php echo e($page); ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if($end < $last): ?>
                <?php if($end < $last - 1): ?>
                    <span class="admin-pagination__dots">...</span>
                <?php endif; ?>

                <a href="<?php echo e($paginator->url($last)); ?>" class="admin-pagination__page"><?php echo e($last); ?></a>
            <?php endif; ?>

            <?php if($paginator->hasMorePages()): ?>
                <a href="<?php echo e($paginator->nextPageUrl()); ?>" class="admin-pagination__btn" rel="next">
                    Sau
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            <?php else: ?>
                <span class="admin-pagination__btn is-disabled" aria-disabled="true">
                    Sau
                    <i class="fa-solid fa-arrow-right"></i>
                </span>
            <?php endif; ?>
        </div>
    </nav>
<?php endif; ?>
<?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/components/admin-pagination.blade.php ENDPATH**/ ?>