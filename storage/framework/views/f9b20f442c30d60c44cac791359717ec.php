<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name', 'CineHome')); ?> - Hồ sơ cá nhân</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/user-home.css')); ?>?v=<?php echo e(filemtime(public_path('assets/css/user-home.css'))); ?>">

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="profile-body">
    <?php
        $urlLogo = asset('assets/images/LOGO copy.png');
        $user = Auth::user();
        $roleLabel = $user?->vai_tro === 'admin'
            ? 'Quản trị viên'
            : ($user?->vai_tro === 'nhan_vien' ? 'Nhân viên rạp' : 'Thành viên CineHome');
    ?>

    <div class="profile-layout">
        <nav class="profile-navbar" aria-label="Thanh điều hướng hồ sơ">
            <a href="<?php echo e(route('home')); ?>" class="profile-brand">
                <span class="profile-brand-mark cinehome-logo-sparkle">
                    <img src="<?php echo e($urlLogo); ?>" alt="CineHome" class="cinehome-logo-img">
                </span>
                <span>
                    Cine<span>Home</span>
                    <small>Hồ sơ cá nhân</small>
                </span>
            </a>

            <div class="profile-nav-actions">
                <a href="<?php echo e(route('user.notifications.index')); ?>" class="profile-icon-btn" aria-label="Thông báo">
                    <i class="fa-solid fa-bell"></i>
                    <span></span>
                </a>

                <details class="profile-user-menu">
                    <summary>
                        <span class="profile-avatar-sm">
                            <?php echo e(mb_substr($user->ho_ten ?? 'U', 0, 1)); ?>

                        </span>
                        <span>
                            <?php echo e($user->ho_ten ?? 'Tài khoản'); ?>

                            <small><?php echo e($roleLabel); ?></small>
                        </span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </summary>

                    <div class="profile-user-dropdown">
                        <a href="<?php echo e(route('home')); ?>">
                            <i class="fa-solid fa-house"></i>
                            Trang chủ
                        </a>

                        <?php if($user && ($user->hasRole('Quản trị viên') || $user->hasRole('Quản lý hệ thống') || $user->hasRole('Quản lý') || $user->hasRole('Quản lý phòng chiếu') || $user->hasRole('Nhân viên') || in_array($user->vai_tro, ['admin', 'quan_ly_he_thong', 'nhan_vien']))): ?>
                            <a href="<?php echo e(route('dashboard')); ?>" class="is-highlight">
                                <i class="fa-solid fa-gauge-high"></i>
                                Khu quản trị
                            </a>
                        <?php endif; ?>

                        <form method="POST" action="<?php echo e(route('logout')); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit">
                                <i class="fa-solid fa-right-from-bracket"></i>
                                Đăng xuất
                            </button>
                        </form>
                    </div>
                </details>
            </div>
        </nav>

        <main class="profile-main">
            <?php echo e($slot); ?>

        </main>
    </div>

    <script src="<?php echo e(asset('assets/js/user-home.js')); ?>?v=<?php echo e(filemtime(public_path('assets/js/user-home.js'))); ?>"></script>
</body>
</html>
<?php /**PATH C:\Users\ADMIN\Desktop\DuAnTotNghiep_CineHome\WD-11-Cinehome-cinema\resources\views/components/profile-layout.blade.php ENDPATH**/ ?>