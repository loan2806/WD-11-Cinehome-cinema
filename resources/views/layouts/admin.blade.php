<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Admin - CineHome')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Tailwind / Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link class="router-css" rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">

    {{-- ChartJS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="overflow-x-hidden bg-[#080808] text-white">
    @include('components.preloader')

    {{-- GLOBAL TOAST NOTIFICATIONS --}}
    @if (session('success'))
        <x-toast type="success" :message="session('success')" />
    @endif
    @if (session('error'))
        <x-toast type="error" :message="session('error')" />
    @endif
    @if (session('warning'))
        <x-toast type="warning" :message="session('warning')" />
    @endif

    {{-- GLOBAL CONFIRM MODAL --}}
    <x-modal-confirm />

    <div id="adminLayout" data-sidebar="open" class="min-h-screen overflow-x-hidden bg-[#080808] text-white">

        {{-- SIDEBAR --}}
        <aside id="adminSidebar"
            class="admin-scrollbar fixed left-0 top-0 z-[60] h-screen w-[280px] overflow-y-auto overflow-x-hidden border-r border-[#d99a32]/20 bg-gradient-to-b from-[#1a0b04] to-[#2b1208] transition-transform duration-300">
            {{-- LOGO --}}
            <div class="flex items-center gap-3 px-5 py-6">
                <img src="{{ asset('assets/images/logo.png') }}" alt="CineHome Logo"
                    class="h-16 w-16 rounded-2xl bg-white object-contain p-1">

                <div>
                    <h3 class="text-2xl font-black text-white">
                        Cine<span class="text-[#d99a32]">Home</span>
                    </h3>

                    <p class="text-sm font-bold text-[#f4c56a]">
                        Admin Panel
                    </p>
                </div>
            </div>

            {{-- MENU --}}
            <div class="mt-4 px-3 pb-8">

                <p class="mb-3 px-3 text-xs font-black uppercase tracking-widest text-[#d7a767]">
                    Tổng quan
                </p>

                <nav class="space-y-2">
                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="{{ request()->routeIs('admin.dashboard') ? 'admin-nav-link active' : 'admin-nav-link' }}"
                    >
                        <i class="fa-solid fa-chart-line w-5"></i>
                        Dashboard
                    </a>
                </nav>

                <p class="mb-3 mt-7 px-3 text-xs font-black uppercase tracking-widest text-[#d7a767]">
                    Quản lý nội dung
                </p>

                <nav class="space-y-2">
                    <a href="{{ route('admin.phims.index') }}" class="{{ request()->routeIs('admin.phims.*') ? 'admin-nav-link active' : 'admin-nav-link' }}">
                        <i class="fa-solid fa-film w-5"></i>
                        <span>Quản lý phim</span>
                    </a>

                    <a href="{{ route('admin.phong-chieus.index') }}" class="{{ request()->routeIs('admin.phong-chieus.*') ? 'admin-nav-link active' : 'admin-nav-link' }}">
                        <i class="fa-solid fa-door-open w-5"></i>
                        <span>Phòng chiếu</span>
                    </a>

                    {{-- Ẩn menu Hàng ghế - đã gộp vào Phòng chiếu --}}
                    {{--
                    <a href="{{ route('admin.hang-ghes.index') }}" class="{{ request()->routeIs('admin.hang-ghes.*') ? 'admin-nav-link active' : 'admin-nav-link' }}">
                        <i class="fa-solid fa-bars w-5"></i>
                        <span>Hàng ghế</span>
                    </a>
                    --}}

                    <a href="{{ route('admin.loai-ghes.index') }}" class="{{ request()->routeIs('admin.loai-ghes.*') ? 'admin-nav-link active' : 'admin-nav-link' }}">
                        <i class="fa-solid fa-chair w-5"></i>
                        <span>Loại ghế</span>
                    </a>

                    {{-- Ẩn menu Ghế ngồi - đã gộp vào Phòng chiếu --}}
                    {{--
                    <a href="{{ route('admin.ghe-ngois.index') }}" class="{{ request()->routeIs('admin.ghe-ngois.*') ? 'admin-nav-link active' : 'admin-nav-link' }}">
                        <i class="fa-solid fa-couch w-5"></i>
                        <span>Ghế ngồi</span>
                    </a>
                    --}}

                    <a href="{{ route('admin.suat-chieus.index') }}" class="{{ request()->routeIs('admin.suat-chieus.*') ? 'admin-nav-link active' : 'admin-nav-link' }}">
                        <i class="fa-solid fa-calendar-days w-5"></i>
                        <span>Suất chiếu</span>
                    </a>

                    <a href="{{ route('admin.the-loais.index') }}" class="{{ request()->routeIs('admin.the-loais.*') ? 'admin-nav-link active' : 'admin-nav-link' }}">
                        <i class="fa-solid fa-tags w-5"></i>
                        <span>Thể loại phim</span>
                    </a>
                </nav>

                <p class="mb-3 mt-7 px-3 text-xs font-black uppercase tracking-widest text-[#d7a767]">
                    Vé & Giao dịch
                </p>

                <nav class="space-y-2">
                    <a href="#" class="admin-nav-link">
                        <i class="fa-solid fa-ticket w-5"></i>
                        <span>Quản lý vé</span>
                    </a>

                    <a href="#" class="admin-nav-link">
                        <i class="fa-solid fa-qrcode w-5"></i>
                        <span>Thanh toán QR</span>
                    </a>

                    <a href="{{ route('admin.food-invoices.index') }}" class="{{ request()->routeIs('admin.food-invoices.*') ? 'admin-nav-link active' : 'admin-nav-link' }}">
                        <i class="fa-solid fa-burger w-5"></i>
                        <span>Đồ ăn</span>
                    </a>
                </nav>

                <p class="mb-3 mt-7 px-3 text-xs font-black uppercase tracking-widest text-[#d7a767]">
                    Tài khoản
                </p>

                <nav class="space-y-2">
                    <a href="#" class="admin-nav-link">
                        <i class="fa-solid fa-users w-5"></i>
                        <span>Người dùng</span>
                    </a>

                    <a href="{{ route('admin.nhanviens.index') }}"
                        class="{{ request()->routeIs('admin.nhanviens.*') ? 'admin-nav-link active' : 'admin-nav-link' }}">
                        <i class="fa-solid fa-users"></i>
                        <span>Quản lý nhân viên</span>
                    </a>

                    <a href="#" class="admin-nav-link">
                        <i class="fa-solid fa-shield-halved w-5"></i>
                        <span>Phân quyền</span>
                    </a>
                </nav>

                <p class="mb-3 mt-7 px-3 text-xs font-black uppercase tracking-widest text-[#d7a767]">
                    Báo cáo
                </p>

                <nav class="space-y-2">
                    <a href="{{ route('admin.revenue-reports.index') }}" class="{{ request()->routeIs('admin.revenue-reports.*') ? 'admin-nav-link active' : 'admin-nav-link' }}">
                        <i class="fa-solid fa-chart-pie w-5"></i>
                        <span>Báo cáo doanh thu</span>
                    </a>

                    <a href="{{ route('admin.activity-logs.index') }}" class="{{ request()->routeIs('admin.activity-logs.*') ? 'admin-nav-link active' : 'admin-nav-link' }}">
                        <i class="fa-solid fa-clock-rotate-left w-5"></i>
                        <span>Nhật ký hoạt động hệ thống</span>
                    </a>
                </nav>

                <p class="mb-3 mt-7 px-3 text-xs font-black uppercase tracking-widest text-[#d7a767]">
                    Hệ thống
                </p>

                <nav class="space-y-2">
                    <a href="{{ route('admin.notifications.index') }}" class="{{ request()->routeIs('admin.notifications.*') ? 'admin-nav-link active' : 'admin-nav-link' }}">
                        <i class="fa-solid fa-bell w-5"></i>
                        <span>Thông báo</span>
                    </a>

                    <a href="{{ route('admin.movie-reviews.index') }}" class="{{ request()->routeIs('admin.movie-reviews.*') ? 'admin-nav-link active' : 'admin-nav-link' }}">
                        <i class="fa-solid fa-star w-5"></i>
                        <span>Đánh giá phim</span>
                    </a>

                    <a href="{{ route('admin.system-settings.index') }}" class="{{ request()->routeIs('admin.system-settings.*') ? 'admin-nav-link active' : 'admin-nav-link' }}">
                        <i class="fa-solid fa-gear w-5"></i>
                        <span>Cấu hình hệ thống</span>
                    </a>
                </nav>
            </div>
        </aside>

        {{-- MAIN CONTENT AREA --}}
        <main id="adminMain" class="min-h-screen overflow-x-hidden bg-[#080808] ml-[280px]">

            {{-- TOPBAR NAVBAR --}}
            <header class="sticky top-0 z-50 border-b border-white/10 bg-[#101010]/95 backdrop-blur-xl">
                <div class="flex h-[76px] items-center justify-between gap-4 px-5">

                    {{-- LEFT: TIÊU ĐỀ VÀ PHỤ ĐỀ HỆ THỐNG --}}
                    <div class="flex min-w-0 items-center gap-4">
                        <button
                            id="sidebarToggle"
                            type="button"
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-white transition hover:bg-[#d99a32] hover:text-[#2b1208]"
                        >
                            <i class="fa-solid fa-bars text-lg"></i>
                        </button>

                        <div class="min-w-0">
                            <h1 class="truncate text-[22px] font-black leading-tight text-white">
                                @yield('page-title', 'Dashboard quản lý')
                            </h1>

                            <p class="mt-1 max-w-[430px] truncate text-sm text-gray-400 xl:max-w-[560px]">
                                @yield('page-subtitle', 'Theo dõi doanh thu, vé bán, lịch chiếu và hoạt động hệ thống')
                            </p>
                        </div>
                    </div>

                    {{-- CENTER: THANH TÌM KIẾM CHỨC NĂNG --}}
                    <div class="hidden md:flex h-11 w-full max-w-[280px] lg:max-w-[360px] items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4">
                        <i class="fa-solid fa-magnifying-glass text-[#d99a32] text-sm"></i>
                        <input
                            type="text"
                            placeholder="Tìm nhanh chức năng hệ thống..."
                            class="h-full w-full bg-transparent text-sm text-white outline-none placeholder-gray-500"
                        >
                    </div>

                    {{-- RIGHT: THÔNG TIN TÀI KHOẢN --}}
                    <div class="flex items-center gap-3">
                        @auth
                            <div class="relative" id="adminDropdownBox">

                                <button type="button" id="adminDropdownBtn" class="inline-flex items-center gap-3 rounded-2xl bg-white/10 px-4 py-2 transition hover:bg-white/15">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white shadow-md">
                                        <i class="fa-solid fa-user-shield"></i>
                                    </div>

                                    <div class="hidden sm:block text-left">
                                        <div class="text-sm font-bold text-white max-w-[120px] truncate">
                                            {{ Auth::user()->ho_ten }}
                                        </div>
                                        <div class="text-xs text-[#d99a32] font-semibold">
                                            Quản trị viên
                                        </div>
                                    </div>

                                    <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 ml-1"></i>
                                </button>

                                <div id="adminDropdownMenu" class="absolute right-0 top-[125%] z-[9999] hidden w-60 overflow-hidden rounded-xl border border-[#d99a32]/30 bg-[#151515]/95 shadow-2xl backdrop-blur-md">

                                    <div class="flex items-center gap-3 border-b border-white/10 px-4 py-3 bg-white/5">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white">
                                            <i class="fa-solid fa-user text-sm"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="truncate text-sm font-bold text-white">
                                                {{ Auth::user()->ho_ten }}
                                            </div>
                                            <div class="truncate text-[11px] text-gray-400">
                                                {{ Auth::user()->email }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-1.5 border-b border-white/10">
                                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-gray-300 transition hover:bg-[#d99a32] hover:text-[#2b1208]">
                                            <i class="fa-solid fa-user-gear w-4 text-xs text-center"></i>
                                            Hồ sơ cá nhân
                                        </a>
                                        <a href="{{ route('home') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-gray-300 transition hover:bg-[#d99a32] hover:text-[#2b1208]">
                                            <i class="fa-solid fa-house w-4 text-xs text-center"></i>
                                            Xem trang chủ
                                        </a>
                                    </div>

                                    <div class="p-1.5 bg-[#1a1a1a]/30">
                                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                                            @csrf
                                            <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-bold text-red-400 transition hover:bg-red-500/15">
                                                <i class="fa-solid fa-right-from-bracket w-4 text-xs text-center"></i>
                                                Đăng xuất Admin
                                            </button>
                                        </form>
                                    </div>

                                </div>
                            </div>
                        @endauth
                    </div>

                </div>
            </header>

            {{-- VIEWS DỰ ÁN --}}
            <section class="w-full overflow-x-hidden px-6 py-6">
                @yield('content')
            </section>
        </main>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('assets/js/admin.js') }}"></script>

    @stack('scripts')
</body>

</html>
