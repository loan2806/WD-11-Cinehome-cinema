<header class="cine-navbar booking-navbar">
    <div class="container-fluid px-5">
        <div class="booking-navbar-inner">

            <a href="<?php echo e(route('home')); ?>" class="booking-brand text-decoration-none">
                <span class="booking-brand-mark cinehome-logo-sparkle">
                    <img src="<?php echo e(asset('assets/images/LOGO copy.png')); ?>" alt="CineHome Logo" class="cinehome-logo-img">
                </span>
                <span>Cine<span>Home</span></span>
            </a>

            <nav class="nav-menu booking-nav">
                <a href="<?php echo e(route('home')); ?>" class="<?php echo e(request()->routeIs('home') ? 'active' : ''); ?>">
                    Trang chủ
                </a>

                <a href="<?php echo e(route('user.phims.index')); ?>"
                    class="<?php echo e(request()->routeIs('user.phims.*') || request()->routeIs('user.movies.*') ? 'active' : ''); ?>">
                    Phim
                </a>

                <a href="<?php echo e(route('user.cinemas.index')); ?>"
                    class="<?php echo e(request()->routeIs('user.cinemas.*') ? 'active' : ''); ?>">
                    Rạp chiếu
                </a>

                <a href="<?php echo e(route('dat_ve.chon_phim')); ?>" class="<?php echo e(request()->routeIs('dat_ve.*') ? 'active' : ''); ?>">
                    Đặt vé
                </a>

                <a href="<?php echo e(route('user.tin-tuc.index')); ?>"
                    class="<?php echo e(request()->routeIs('user.tin-tuc.*') ? 'active' : ''); ?>">
                    Tin tức
                </a>

                <a href="<?php echo e(route('user.voucher.index')); ?>"
                    class="<?php echo e(request()->routeIs('user.voucher.*') || request()->routeIs('user.khuyen-mai.*') ? 'active' : ''); ?>">
                    Khuyến mãi
                </a>
            </nav>

            <div class="nav-action booking-actions">
                <form action="<?php echo e(route('user.phims.index')); ?>" method="GET" class="booking-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="tim_kiem" class="search-box" placeholder="Tìm phim..."
                        value="<?php echo e(request('tim_kiem')); ?>">
                </form>

                <?php if(auth()->guard()->guest()): ?>
                    <button type="button" data-auth-open="login" class="booking-login-btn">
                        Đăng nhập
                    </button>

                    <button type="button" data-auth-open="register" class="booking-register-btn">
                        Đăng ký
                    </button>
                <?php else: ?>
                    <div class="booking-user-dropdown" id="userDropdownBox">
                        <button
                            type="button"
                            id="userDropdownBtn"
                            class="booking-user-btn"
                            aria-expanded="false"
                            aria-haspopup="menu"
                            aria-controls="userDropdownMenu"
                        >
                            <span class="booking-user-avatar">
                                <i class="fa-solid fa-user"></i>
                            </span>

                            <span class="booking-user-name">
                                <?php echo e(Auth::user()->ho_ten); ?>

                            </span>

                            <i class="fa-solid fa-chevron-down text-[10px]"></i>
                        </button>

                        <div id="userDropdownMenu" class="booking-user-menu hidden" role="menu" hidden>
                            <div class="booking-user-info">
                                <span class="booking-user-avatar lg">
                                    <i class="fa-solid fa-user"></i>
                                </span>

                                <div class="min-w-0">
                                    <strong><?php echo e(Auth::user()->ho_ten); ?></strong>
                                    <small><?php echo e(Auth::user()->email); ?></small>
                                </div>
                            </div>

                            <div class="booking-menu-list">
                                <a href="<?php echo e(route('profile.edit')); ?>">
                                    <i class="fa-solid fa-user-gear"></i>
                                    Tài khoản
                                </a>

                                <?php if(Auth::user()->hasRole('Khách hàng') || Auth::user()->vai_tro === 'khach_hang'): ?>
                                    <a href="<?php echo e(route('user.ve_xem_phim.index')); ?>">
                                        <i class="fa-solid fa-ticket"></i>
                                        Vé của tôi
                                    </a>

                                    <a href="<?php echo e(route('user.thanh-vien.index')); ?>">
                                        <i class="fa-solid fa-id-card"></i>
                                        Thẻ thành viên & Điểm
                                    </a>

                                    <a href="<?php echo e(route('user.voucher.index')); ?>">
                                        <i class="fa-solid fa-gift"></i>
                                        Đổi điểm lấy voucher
                                    </a>

                                    <a href="<?php echo e(route('user.voucher.my')); ?>">
                                        <i class="fa-solid fa-ticket-percent"></i>
                                        Voucher của tôi
                                    </a>

                                    <a href="<?php echo e(route('user.notifications.index')); ?>">
                                        <i class="fa-solid fa-bell"></i>
                                        Thông báo
                                    </a>
                                <?php endif; ?>

                                <a href="<?php echo e(route('user.phims.index')); ?>">
                                    <i class="fa-solid fa-film"></i>
                                    Danh sách phim
                                </a>

                                <?php if(Auth::user()->hasRole('Quản trị viên') ||
                                        Auth::user()->hasRole('Quản lý hệ thống') ||
                                        Auth::user()->hasRole('Quản lý') ||
                                        Auth::user()->vai_tro === 'admin'): ?>
                                    <a href="<?php echo e(route('admin.dashboard')); ?>">
                                        <i class="fa-solid fa-user-shield"></i>
                                        Trang quản lý
                                    </a>
                                <?php endif; ?>

                                <?php if(Auth::user()->hasRole('Nhân viên') || Auth::user()->vai_tro === 'nhan_vien'): ?>
                                    <a href="<?php echo e(route('admin.dashboard')); ?>">
                                        <i class="fa-solid fa-user-tie"></i>
                                        Trang nhân viên
                                    </a>
                                <?php endif; ?>
                            </div>

                            <form method="POST" action="<?php echo e(route('logout')); ?>" class="booking-logout-form">
                                <?php echo csrf_field(); ?>
                                <button type="submit">
                                    <i class="fa-solid fa-right-from-bracket"></i>
                                    Đăng xuất
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</header>
<?php /**PATH E:\laragon\www\WD-11-Cinehome-cinema\resources\views/layouts/header.blade.php ENDPATH**/ ?>