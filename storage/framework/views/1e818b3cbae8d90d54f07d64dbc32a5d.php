<header class="cine-navbar">
    <div class="container-fluid px-5">
        <div class="d-flex justify-content-between align-items-center">

            <a href="<?php echo e(route('home')); ?>" class="d-flex align-items-center gap-2 text-decoration-none">
                <div class="brand-text">Cine<span>Home</span></div>
            </a>

            <nav class="nav-menu">
                <a href="<?php echo e(route('home')); ?>" class="<?php echo e(request()->routeIs('home') ? 'active' : ''); ?>">
                    Trang chủ
                </a>

                <a href="<?php echo e(route('user.phims.index')); ?>"
                    class="<?php echo e(request()->routeIs('user.phims.*') ? 'active' : ''); ?>">
                    Phim
                </a>

                <a href="<?php echo e(route('user.cinemas.index')); ?>"
                    class="<?php echo e(request()->routeIs('user.cinemas.*') ? 'active' : ''); ?>">
                    Giới thiệu rạp
                </a>

                <a href="<?php echo e(route('user.tin-tuc.index')); ?>"
                    class="<?php echo e(request()->routeIs('user.tin-tuc.*') || request()->routeIs('user.khuyen-mai.*') ? 'active' : ''); ?>">
                    Tin tức
                </a>

                <a href="<?php echo e(route('user.showtimes.index')); ?>"
                    class="<?php echo e(request()->routeIs('user.showtimes.*') ? 'active' : ''); ?>">
                    Lịch chiếu
                </a>
                <a href="<?php echo e(route('dat_ve.chon_phim')); ?>" class="<?php echo e(request()->routeIs('dat_ve.*') ? 'active' : ''); ?>">
                    Đặt vé
                </a>
                <a href="#" class="<?php echo e(request()->is('khuyen-mai*') ? 'active' : ''); ?>">
                    Khuyến mãi
                </a>
            </nav>

            <div class="nav-action">
                <input type="text" class="search-box" placeholder="Tìm phim...">

                <?php if(auth()->guard()->guest()): ?>
                <button type="button" data-auth-open="login"
                    class="font-bold text-white transition hover:text-[#d99a32]">
                    Đăng nhập
                </button>

                <button type="button" data-auth-open="register"
                    class="rounded-full bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 py-2.5 font-bold text-white shadow-lg transition hover:scale-[1.02]">
                    Đăng ký
                </button>
                <?php else: ?>
                <div class="relative" id="userDropdownBox">
                    <button type="button" id="userDropdownBtn"
                        class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-bold text-white shadow-md transition bg-gradient-to-r from-[#8a4a21] to-[#d99a32] hover:scale-[1.02] hover:shadow-lg">
                        <i class="fa-solid fa-user text-sm"></i>

                        <span class="max-w-[105px] truncate">
                            <?php echo e(Auth::user()->ho_ten); ?>

                        </span>

                        <i class="fa-solid fa-chevron-down text-[10px]"></i>
                    </button>

                    <div id="userDropdownMenu"
                        class="absolute right-0 top-[120%] z-[9999] hidden w-56 overflow-hidden rounded-xl border border-[#d99a32]/30 bg-[#151515]/95 shadow-2xl backdrop-blur-md">

                        
                        <div class="flex items-center gap-3 border-b border-white/10 px-3 py-3">
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white">
                                <i class="fa-solid fa-user text-sm"></i>
                            </div>

                            <div class="min-w-0">
                                <div class="truncate text-sm font-bold text-white">
                                    <?php echo e(Auth::user()->ho_ten); ?>

                                </div>

                                <div class="truncate text-[11px] text-gray-400">
                                    <?php echo e(Auth::user()->email); ?>

                                </div>
                            </div>
                        </div>

                        
                        <div class="p-1.5">
                            <a href="<?php echo e(route('profile.edit')); ?>"
                                class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-gray-100 transition hover:bg-[#d99a32] hover:text-[#2b1208]">
                                <i class="fa-solid fa-user-gear w-4 text-xs"></i>
                                Tài khoản
                            </a>

                            <?php if(Auth::user()->hasRole('Khách hàng') || Auth::user()->vai_tro === 'khach_hang'): ?>
                            <a href="<?php echo e(route('user.ve_xem_phim.index')); ?>"
                                class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-gray-100 transition hover:bg-[#d99a32] hover:text-[#2b1208]">
                                <i class="fa-solid fa-ticket w-4 text-xs"></i>
                                Vé của tôi
                            </a>

                            <a href="<?php echo e(route('user.thanh-vien.index')); ?>"
                                class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-gray-100 transition hover:bg-[#d99a32] hover:text-[#2b1208]">
                                <i class="fa-solid fa-id-card w-4 text-xs"></i>
                                Thẻ thành viên & Điểm
                            </a>

                            <a href="<?php echo e(route('user.voucher.index')); ?>"
                                class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-gray-100 transition hover:bg-[#d99a32] hover:text-[#2b1208]">
                                <i class="fa-solid fa-gift w-4 text-xs"></i>
                                Đổi điểm lấy voucher
                            </a>

                            <a href="<?php echo e(route('user.voucher.my')); ?>"
                                class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-gray-100 transition hover:bg-[#d99a32] hover:text-[#2b1208]">
                                <i class="fa-solid fa-ticket-percent w-4 text-xs"></i>
                                Voucher của tôi
                            </a>

                            <a href="<?php echo e(route('user.notifications.index')); ?>"
                                class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-gray-100 transition hover:bg-[#d99a32] hover:text-[#2b1208]">
                                <i class="fa-solid fa-bell w-4 text-xs"></i>
                                Thông báo
                            </a>
                            <?php endif; ?>

                            <a href="<?php echo e(route('user.phims.index')); ?>"
                                class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-gray-100 transition hover:bg-[#d99a32] hover:text-[#2b1208]">
                                <i class="fa-solid fa-film w-4 text-xs"></i>
                                Danh sách phim
                            </a>

                            
                            <?php if(Auth::user()->hasRole('Quản trị viên') || Auth::user()->hasRole('Quản lý hệ thống') ||
                            Auth::user()->hasRole('Quản lý') || Auth::user()->vai_tro === 'admin'): ?>
                            <a href="<?php echo e(route('admin.dashboard')); ?>"
                                class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-gray-100 transition hover:bg-[#d99a32] hover:text-[#2b1208]">
                                <i class="fa-solid fa-user-shield w-4 text-xs"></i>
                                Trang quản lý
                            </a>
                            <?php endif; ?>

                            
                            <?php if(Auth::user()->hasRole('Nhân viên') || Auth::user()->vai_tro === 'nhan_vien'): ?>
                            <a href="<?php echo e(route('admin.dashboard')); ?>"
                                class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-gray-100 transition hover:bg-[#d99a32] hover:text-[#2b1208]">
                                <i class="fa-solid fa-user-tie w-4 text-xs"></i>
                                Trang nhân viên
                            </a>
                            <?php endif; ?>
                        </div>

                        
                        <div class="border-t border-white/10 p-1.5">
                            <form method="POST" action="<?php echo e(route('logout')); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit"
                                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-bold text-red-400 transition hover:bg-red-500/15">
                                    <i class="fa-solid fa-right-from-bracket w-4 text-xs"></i>
                                    Đăng xuất
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</header>
<?php /**PATH C:\laragon\www\WD-11-Cinehome-cinema\resources\views/layouts/header.blade.php ENDPATH**/ ?>