<?php if($paginator->hasPages()): ?>

<div class="mt-8 flex items-center justify-center">

    <div class="flex items-center gap-2 rounded-2xl bg-white/5 p-2 backdrop-blur">

        
        <?php if($paginator->onFirstPage()): ?>
            <span class="px-4 py-2 text-gray-500">← Trước</span>
        <?php else: ?>
            <a href="<?php echo e($paginator->previousPageUrl()); ?>"
               class="px-4 py-2 rounded-xl text-white hover:bg-white/10 transition">
                ← Trước
            </a>
        <?php endif; ?>

        <?php
            $current = $paginator->currentPage();
            $last = $paginator->lastPage();

            $start = max($current - 2, 1);
            $end = min($current + 2, $last);
        ?>

        
        <?php if($start > 1): ?>
            <a href="<?php echo e($paginator->url(1)); ?>"
               class="px-4 py-2 rounded-xl text-white hover:bg-white/10 transition">
                1
            </a>

            <?php if($start > 2): ?>
                <span class="px-2 text-gray-500">...</span>
            <?php endif; ?>
        <?php endif; ?>

        
        <?php for($page = $start; $page <= $end; $page++): ?>
            <?php if($page == $current): ?>
                <span class="px-4 py-2 rounded-xl bg-[#d99a32] text-black font-bold">
                    <?php echo e($page); ?>

                </span>
            <?php else: ?>
                <a href="<?php echo e($paginator->url($page)); ?>"
                   class="px-4 py-2 rounded-xl text-white hover:bg-white/10 transition">
                    <?php echo e($page); ?>

                </a>
            <?php endif; ?>
        <?php endfor; ?>

        
        <?php if($end < $last): ?>
            <?php if($end < $last - 1): ?>
                <span class="px-2 text-gray-500">...</span>
            <?php endif; ?>

            <a href="<?php echo e($paginator->url($last)); ?>"
               class="px-4 py-2 rounded-xl text-white hover:bg-white/10 transition">
                <?php echo e($last); ?>

            </a>
        <?php endif; ?>

        
        <?php if($paginator->hasMorePages()): ?>
            <a href="<?php echo e($paginator->nextPageUrl()); ?>"
               class="px-4 py-2 rounded-xl text-white hover:bg-white/10 transition">
                Sau →
            </a>
        <?php else: ?>
            <span class="px-4 py-2 text-gray-500">Sau →</span>
        <?php endif; ?>

    </div>

</div>

<?php endif; ?><?php /**PATH C:\laragon\www\WD-11-Cinehome-cinema\resources\views/components/admin-pagination.blade.php ENDPATH**/ ?>