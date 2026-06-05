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
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">

    {{-- ChartJS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="overflow-x-hidden bg-[#080808] text-white">
    @include('components.preloader')

    <div class="min-h-screen overflow-x-hidden bg-[#080808] text-white">
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

                <a href="#" class="admin-nav-link">
                    <i class="fa-solid fa-clapperboard w-5"></i>
                    <span>Trailer / Poster</span>
                </a>

                <a href="#" class="admin-nav-link">
                    <i class="fa-solid fa-building w-5"></i>
                    <span>Quản lý rạp</span>
                </a>

                <a href="#" class="admin-nav-link">
                    <i class="fa-solid fa-chair w-5"></i>
                    <span>Sơ đồ ghế</span>
                </a>

                <a href="{{ route('admin.suat-chieu.index') }}" class="{{ request()->routeIs('admin.suat-chieu.*') ? 'admin-nav-link active' : 'admin-nav-link' }}">
                    <i class="fa-solid fa-calendar-days w-5"></i>
                    <span>Lịch chiếu</span>
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

                <a href="#" class="admin-nav-link">
                    <i class="fa-solid fa-user-tie w-5"></i>
                    <span>Nhân viên</span>
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

    {{-- MAIN --}}
     <main
        id="adminMain"
        class="min-h-screen overflow-x-hidden bg-[#080808]"
    >
        {{-- TOPBAR --}}
        <header class="sticky top-0 z-50 border-b border-white/10 bg-[#101010]/95 backdrop-blur-xl">
            <div class="flex h-[76px] items-center justify-between gap-4 px-5">

                {{-- LEFT --}}
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
                            @yield('page-subtitle', 'Theo dõi doanh thu, vé bán và hoạt động hệ thống')
                        </p>
                    </div>
                </div>
            </div>

 
        </aside>

         
            {{-- CONTENT --}}
            <section class="w-full overflow-x-hidden px-6 py-6">
                @yield('content')
            </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('assets/js/admin.js') }}"></script>

    @yield('scripts')
</body>

</html>
