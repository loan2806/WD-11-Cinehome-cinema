<?php $__env->startSection('title', $movie->ten_phim . ' - CineHome'); ?>

<?php $__env->startSection('content'); ?>

    <section x-data="{ tab: 'description' }" class="relative min-h-screen bg-black text-white overflow-hidden">

        
        <div class="absolute inset-0">

            <img src="<?php echo e($movie->poster); ?>" class="w-full h-full object-cover opacity-20 blur-md">

            <div class="absolute inset-0 bg-gradient-to-r from-black via-black/90 to-black">
            </div>

        </div>

        <div class="relative max-w-7xl mx-auto px-6 lg:px-12 pt-32 pb-20">

            <div class="grid lg:grid-cols-12 gap-12">

                
                <div class="lg:col-span-3">

                    <img src="<?php echo e(asset('storage/movies/' . $movie->poster)); ?>" alt="<?php echo e($movie->ten_phim); ?>"
                        class="w-full aspect-[2/3.4] object-cover rounded-2xl shadow-2xl">

                    <div class="mt-5 space-y-3">

                        <?php if($movie->trailer): ?>
                            <a href="<?php echo e($movie->trailer); ?>" target="_blank"
                                class="w-full flex items-center justify-center gap-2 bg-pink-600 hover:bg-pink-500 px-5 py-3 rounded-xl font-bold transition">

                                <i class="fa-solid fa-play"></i>

                                Xem Trailer
                            </a>
                        <?php endif; ?>
                        <?php if($status === \App\Models\SuatChieu::TRANG_THAI_DANG_CHIEU || $status === \App\Models\SuatChieu::TRANG_THAI_SAP_CHIEU): ?>
                            <a href="<?php echo e(route('dat_ve.chon_ghe', $movie->slug)); ?>"
                                class="booking-link w-full flex items-center justify-center gap-2 bg-yellow-500 text-black px-5 py-3 rounded-xl font-bold hover:bg-yellow-400 transition">
                                <i class="fa-solid fa-ticket"></i>
                                Đặt vé
                            </a>
                        <?php else: ?>
                            <button
                                class="w-full flex items-center justify-center gap-2 bg-gray-600 text-white px-5 py-3 rounded-xl cursor-not-allowed"
                                disabled>
                                Không thể đặt vé
                            </button>
                        <?php endif; ?>

                        <?php if($status === \App\Models\SuatChieu::TRANG_THAI_SAP_CHIEU): ?>
                            <button
                                class="w-full flex items-center justify-center gap-2 bg-blue-500 text-white px-5 py-3 rounded-xl cursor-not-allowed"
                                disabled>
                                Sắp chiếu
                            </button>
                        <?php elseif($status === \App\Models\SuatChieu::TRANG_THAI_SAP_RA_MAT): ?>
                            <button
                                class="w-full flex items-center justify-center gap-2 bg-purple-500 text-white px-5 py-3 rounded-xl cursor-not-allowed"
                                disabled>
                                Sắp ra mắt
                            </button>
                        <?php endif; ?>

                    </div>

                </div>

                
                <div class="lg:col-span-9">
                    <div class="mb-6">
                        <a href="<?php echo e(url()->previous()); ?>"
                            class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/5 px-5 py-3 text-sm font-semibold text-white hover:bg-white/10 transition">
                            <i class="fa-solid fa-arrow-left"></i>
                            Quay lại
                        </a>
                    </div>

                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-white mb-3">

                        <?php echo e($movie->ten_phim); ?>


                    </h1>



                    
                    

                    
                    <div class="flex gap-8 border-b border-white/10 mb-8 overflow-x-auto">

                        <button @click="tab='description'"
                            :class="tab == 'description' ?
                                'border-yellow-400 text-yellow-400' :
                                'border-transparent text-gray-400'"
                            class="pb-4 border-b-2 font-bold whitespace-nowrap">

                            MÔ TẢ

                        </button>

                        <button @click="tab='review'"
                            :class="tab == 'review' ?
                                'border-yellow-400 text-yellow-400' :
                                'border-transparent text-gray-400'"
                            class="pb-4 border-b-2 font-bold whitespace-nowrap">

                            ĐÁNH GIÁ

                        </button>

                        <button @click="tab='cast'"
                            :class="tab == 'cast' ?
                                'border-yellow-400 text-yellow-400' :
                                'border-transparent text-gray-400'"
                            class="pb-4 border-b-2 font-bold whitespace-nowrap">

                            CAST & CREW

                        </button>

                    </div>

                    
                    <div x-show="tab=='description'" x-transition>

                        <div class="grid lg:grid-cols-3 gap-10">

                            
                            <div class="lg:col-span-2">

                                <h3 class="text-sm uppercase tracking-widest text-yellow-400 mb-4">
                                    Mô tả
                                </h3>

                                <p class="text-gray-300 leading-8 mb-8">
                                    <?php echo e($movie->mo_ta); ?>

                                </p>
                            </div>

                            
                            <div>

                                <div class="space-y-6">

                                    <div>
                                        <p class="text-gray-500 text-sm mb-1">
                                            Director
                                        </p>

                                        <p class="font-semibold text-white">
                                            <?php echo e($movie->dao_dien); ?>

                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-gray-500 text-sm mb-1">
                                            Stars
                                        </p>

                                        <p class="font-semibold text-white">
                                            <?php echo e($movie->dien_vien); ?>

                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-gray-500 text-sm mb-1">
                                            Genre
                                        </p>

                                        <p class="font-semibold text-white">
                                            <?php echo e($movie->genres->pluck('ten_the_loai')->join(', ')); ?>

                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-gray-500 text-sm mb-1">
                                            Country
                                        </p>

                                        <p class="font-semibold text-white">
                                            <?php echo e(optional($movie->country)->ten_quoc_gia); ?>

                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-gray-500 text-sm mb-1">
                                            Duration
                                        </p>

                                        <p class="font-semibold text-white">
                                            <?php echo e($movie->thoi_luong); ?> phút
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-gray-500 text-sm mb-1">
                                            Age Rating
                                        </p>

                                        <p class="font-semibold text-white">
                                            <?php echo e($movie->gioi_han_tuoi); ?>

                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-gray-500 text-sm mb-1">
                                            Release Date
                                        </p>

                                        <p class="font-semibold text-white">
                                            <?php echo e(\Carbon\Carbon::parse($movie->ngay_khoi_chieu)->format('d/m/Y')); ?>

                                        </p>
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    
                    <div x-show="tab=='review'" x-transition class="space-y-4">

                        <div class="bg-white/5 border border-white/10 rounded-xl p-5">

                            <div class="flex items-center justify-between mb-2">

                                <h3 class="font-bold">
                                    Nguyễn Văn A
                                </h3>

                                <span class="text-yellow-400">
                                    ★★★★★
                                </span>

                            </div>

                            <p class="text-gray-400">
                                Phim rất hay, kỹ xảo đẹp và đáng xem.
                            </p>

                        </div>

                        <div class="bg-white/5 border border-white/10 rounded-xl p-5">

                            <div class="flex items-center justify-between mb-2">

                                <h3 class="font-bold">
                                    Trần Văn B
                                </h3>

                                <span class="text-yellow-400">
                                    ★★★★☆
                                </span>

                            </div>

                            <p class="text-gray-400">
                                Nội dung cuốn hút, diễn xuất tốt.
                            </p>

                        </div>

                    </div>

                    <div x-show="tab=='cast'" x-transition>

                        
                        <div class="mb-10">

                            <h3 class="text-yellow-400 font-bold uppercase tracking-wider mb-6">
                                Director
                            </h3>

                            <div class="flex items-center gap-4">

                                <div
                                    class="w-16 h-16 rounded-full bg-yellow-400 text-black flex items-center justify-center font-bold text-xl">

                                    <?php echo e(strtoupper(substr($movie->dao_dien, 0, 1))); ?>


                                </div>

                                <div>

                                    <h4 class="text-xl font-semibold">
                                        <?php echo e($movie->dao_dien); ?>

                                    </h4>

                                    <p class="text-gray-500">
                                        Director
                                    </p>

                                </div>

                            </div>

                        </div>

                        
                        <div>

                            <h3 class="text-yellow-400 font-bold uppercase tracking-wider mb-6">
                                Cast
                            </h3>

                            <div class="grid md:grid-cols-2 gap-4">

                                <?php $__currentLoopData = explode(',', $movie->dien_vien); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $actor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="flex items-center gap-4 border-b border-white/10 pb-4">

                                        <div
                                            class="w-14 h-14 rounded-full bg-white/10 flex items-center justify-center font-bold">

                                            <?php echo e(strtoupper(substr(trim($actor), 0, 1))); ?>

                                        </div>

                                        <div>

                                            <h4 class="font-semibold">
                                                <?php echo e(trim($actor)); ?>

                                            </h4>

                                            <p class="text-gray-500 text-sm">
                                                Actor
                                            </p>

                                        </div>

                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            </div>

                        </div>

                    </div>

                    
                    <div x-show="tab=='media'" x-transition>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

                            <img src="<?php echo e($movie->poster); ?>" class="rounded-xl">

                            <img src="<?php echo e($movie->poster); ?>" class="rounded-xl">

                            <img src="<?php echo e($movie->poster); ?>" class="rounded-xl">

                            <img src="<?php echo e($movie->poster); ?>" class="rounded-xl">

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>

    
    <section class="bg-black py-20 text-white">

        <div class="max-w-7xl mx-auto px-6">

            <h2 class="text-3xl font-bold mb-8">

                Phim liên quan

            </h2>

            <?php if(isset($relatedMovies) && $relatedMovies->count()): ?>
                <?php echo $__env->make('partials.movie-section', [
                    'movies' => $relatedMovies,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endif; ?>

        </div>

    </section>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\WD-11-Cinehome-cinema\resources\views/user/phims/show.blade.php ENDPATH**/ ?>