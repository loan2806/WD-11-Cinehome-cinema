<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title><?php echo $__env->yieldContent('title', 'CineHome - Đặt vé xem phim'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/user-home.css')); ?>?v=<?php echo e(filemtime(public_path('assets/css/user-home.css'))); ?>">

    <?php echo $__env->yieldPushContent('styles'); ?>

    
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>

<body>
    <?php echo $__env->make('components.preloader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->make('layouts.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <main class="page-content">
        <?php echo $__env->yieldContent('content'); ?>
    </main>
    <?php if(auth()->guard()->guest()): ?>
        <?php echo $__env->make('components.auth-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>

    <?php echo $__env->make('layouts.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    
    <script src="<?php echo e(asset('assets/js/user-home.js')); ?>?v=<?php echo e(filemtime(public_path('assets/js/user-home.js'))); ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /* ================= STORAGE ================= */
            function clearBookingData() {
                localStorage.removeItem('food_cart');

                Object.keys(localStorage).forEach(key => {
                    if (key.startsWith('booking_deadline_')) {
                        localStorage.removeItem(key);
                    }
                });
            }

            function isBookingPage() {
                return window.location.pathname.includes('/dat-ve');
            }

            function isHomePage() {
                return window.location.pathname === '/';
            }

            /* ================= RESET RULES ================= */

            // 1. Click về HOME => xoá toàn bộ booking
            document.querySelectorAll('a[href="/"], a[href="<?php echo e(url('/')); ?>"]').forEach(link => {
                link.addEventListener('click', function() {
                    clearBookingData();
                });
            });

            // 2. Click menu ngoài booking => xoá
            document.querySelectorAll('header a[href]').forEach(link => {
                link.addEventListener('click', function() {
                    const href = this.getAttribute('href');

                    if (href === '/' || href === '/home') {
                        clearBookingData();
                    }
                });
            });

            // 3. Click nút đặt vé mới => reset session cũ
            document.querySelectorAll('.booking-link').forEach(link => {
                link.addEventListener('click', clearBookingData);
            });

            // 4. Nếu đang ở HOME thì auto clear luôn
            if (isHomePage()) {
                clearBookingData();
            }

            // NOTE: Bỏ xử lý beforeunload để không xóa bộ đếm thanh toán khi người dùng
            // rời khỏi checkout sang trang khác và quay lại thanh toán tiếp.

        });
    </script>
    <?php echo $__env->yieldContent('scripts'); ?>
</body>

</html>
<?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/layouts/user.blade.php ENDPATH**/ ?>