<header class="cine-navbar booking-navbar">
    <div class="container-fluid px-5">
        <div class="booking-navbar-inner">

            <a href="{{ route('home') }}" class="booking-brand text-decoration-none">
                <span class="booking-brand-mark cinehome-logo-sparkle">
                    <img src="{{ asset('assets/images/LOGO copy.png') }}" alt="CineHome Logo" class="cinehome-logo-img">
                </span>
                <span>Cine<span>Home</span></span>
            </a>

            <nav class="nav-menu booking-nav">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">
                    Trang chủ
                </a>

                <a href="{{ route('user.phims.index') }}" class="{{ request()->routeIs('user.phims.*') || request()->routeIs('user.movies.*') ? 'active' : '' }}">
                    Phim
                </a>

                <a href="{{ route('user.cinemas.index') }}" class="{{ request()->routeIs('user.cinemas.*') ? 'active' : '' }}">
                    Rạp chiếu
                </a>

                <a href="{{ route('dat_ve.chon_phim') }}" class="{{ request()->routeIs('dat_ve.*') ? 'active' : '' }}">
                    Đặt vé
                </a>

                <a href="{{ route('user.tin-tuc.index') }}" class="{{ request()->routeIs('user.tin-tuc.*') ? 'active' : '' }}">
                    Tin tức
                </a>

                <a href="{{ route('user.voucher.index') }}" class="{{ request()->routeIs('user.voucher.*') || request()->routeIs('user.khuyen-mai.*') ? 'active' : '' }}">
                    Khuyến mãi
                </a>
            </nav>

            <div class="nav-action booking-actions">
                <form action="{{ route('user.phims.index') }}" method="GET" class="booking-search">
                    <button type="submit" class="booking-search-btn" aria-label="Tìm kiếm phim">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>

                    <input type="text" name="tim_kiem" class="search-box" placeholder="Tìm phim..." value="{{ request('tim_kiem') }}" autocomplete="off">
                </form>

                @guest
                <button type="button" data-auth-open="login" class="booking-login-btn">
                    Đăng nhập
                </button>

                <button type="button" data-auth-open="register" class="booking-register-btn">
                    Đăng ký
                </button>
                @else
                <div class="booking-user-dropdown" id="userDropdownBox">
                    <button type="button" id="userDropdownBtn" class="booking-user-btn" aria-expanded="false" aria-haspopup="menu" aria-controls="userDropdownMenu">
                        <span class="booking-user-avatar">
                            <i class="fa-solid fa-user"></i>
                        </span>

                        <span class="booking-user-name">
                            {{ Auth::user()->ho_ten }}
                        </span>

                        <i class="fa-solid fa-chevron-down text-[10px]"></i>
                    </button>

                    <div id="userDropdownMenu" class="booking-user-menu hidden" role="menu" hidden>
                        <div class="booking-user-info">
                            <span class="booking-user-avatar lg">
                                <i class="fa-solid fa-user"></i>
                            </span>

                            <div class="min-w-0">
                                <strong>{{ Auth::user()->ho_ten }}</strong>
                                <small>{{ Auth::user()->email }}</small>
                            </div>
                        </div>

                        <div class="booking-menu-list">
                            <a href="{{ route('profile.edit') }}">
                                <i class="fa-solid fa-user-gear"></i>
                                Tài khoản
                            </a>

                            @if (Auth::user()->hasRole('Khách hàng') || Auth::user()->vai_tro === 'khach_hang')
                            <a href="{{ route('user.ve_xem_phim.index') }}">
                                <i class="fa-solid fa-ticket"></i>
                                Vé của tôi
                            </a>

                            <a href="{{ route('user.thanh-vien.index') }}">
                                <i class="fa-solid fa-id-card"></i>
                                Thẻ thành viên & Điểm
                            </a>

                            <a href="{{ route('user.voucher.index') }}">
                                <i class="fa-solid fa-gift"></i>
                                Đổi điểm lấy voucher
                            </a>

                            <a href="{{ route('user.voucher.my') }}">
                                <i class="fa-solid fa-ticket-percent"></i>
                                Voucher của tôi
                            </a>

                            <a href="{{ route('user.notifications.index') }}">
                                <i class="fa-solid fa-bell"></i>
                                Thông báo
                            </a>
                            @endif

                            <a href="{{ route('user.phims.index') }}">
                                <i class="fa-solid fa-film"></i>
                                Danh sách phim
                            </a>

                            @if (Auth::user()->hasRole('Quản trị viên') ||
                            Auth::user()->hasRole('Quản lý hệ thống') ||
                            Auth::user()->hasRole('Quản lý') ||
                            Auth::user()->vai_tro === 'admin')
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="fa-solid fa-user-shield"></i>
                                Trang quản lý
                            </a>
                            @endif

                            @if (Auth::user()->hasRole('Nhân viên') || Auth::user()->vai_tro === 'nhan_vien')
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="fa-solid fa-user-tie"></i>
                                Trang nhân viên
                            </a>
                            @endif
                        </div>

                        <form method="POST" action="{{ route('logout') }}" class="booking-logout-form">
                            @csrf
                            <button type="submit">
                                <i class="fa-solid fa-right-from-bracket"></i>
                                Đăng xuất
                            </button>
                        </form>
                    </div>
                </div>
                @endguest
            </div>

        </div>
    </div>
</header>
