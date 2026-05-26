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

    {{-- ChartJS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="overflow-x-hidden bg-[#080808] text-white">
    @include('components.preloader')

<div id="adminLayout" data-sidebar="open" class="min-h-screen overflow-x-hidden bg-[#080808] text-white">

    {{-- SIDEBAR --}}
    <aside
    id="adminSidebar"
    class="admin-scrollbar fixed left-0 top-0 z-[60] h-screen w-[280px] overflow-y-auto overflow-x-hidden border-r border-[#d99a32]/20 bg-gradient-to-b from-[#1a0b04] to-[#2b1208] transition-transform duration-300"
>
        {{-- LOGO --}}
        <div class="flex items-center gap-3 px-5 py-6">
            <img
                src="{{ asset('assets/images/logo.png') }}"
                alt="CineHome Logo"
                class="h-16 w-16 rounded-2xl bg-white object-contain p-1"
            >

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
                <a href="{{ route('admin.movies.index') }}" class="{{ request()->routeIs('admin.movies.*') ? 'admin-nav-link active' : 'admin-nav-link' }}">
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

                <a href="#" class="admin-nav-link">
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

                <a href="{{ route('admin.food-orders.index') }}" class="{{ request()->routeIs('admin.food-orders.index') ? 'admin-nav-link active' : 'admin-nav-link' }}">
                    <i class="fa-solid fa-burger w-5"></i>
                    <span>Đồ ăn</span>
                </a>

                <a href="{{ route('admin.food-orders.index') }}" class="{{ request()->routeIs('admin.food-orders.show') ? 'admin-nav-link active' : 'admin-nav-link' }}">
                    <i class="fa-solid fa-receipt w-5"></i>
                    <span>Chi tiết hóa đơn đồ ăn</span>
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
                <a href="{{ route('admin.reports.revenue') }}" class="{{ request()->routeIs('admin.reports.*') ? 'admin-nav-link active' : 'admin-nav-link' }}">
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

                <a href="{{ route('admin.reviews.index') }}" class="{{ request()->routeIs('admin.reviews.*') ? 'admin-nav-link active' : 'admin-nav-link' }}">
                    <i class="fa-solid fa-star w-5"></i>
                    <span>Đánh giá phim</span>
                </a>

                <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'admin-nav-link active' : 'admin-nav-link' }}">
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

                {{-- RIGHT --}}
                <div class="flex shrink-0 items-center gap-3">

                    {{-- SEARCH --}}
                    <div class="hidden h-11 w-[310px] items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 transition focus-within:border-[#d99a32]/70 focus-within:bg-white/10 xl:flex">
                        <i class="fa-solid fa-magnifying-glass text-sm text-[#d99a32]"></i>

                        <input
                            type="text"
                            placeholder="Tìm phim, vé, người dùng..."
                            class="h-full w-full bg-transparent text-sm text-white outline-none placeholder:text-gray-500"
                        >
                    </div>

                    {{-- NOTIFICATION --}}
                    <button
                        type="button"
                        class="relative flex h-11 w-11 items-center justify-center rounded-2xl bg-white/10 text-white transition hover:bg-white/15"
                    >
                        <i class="fa-solid fa-bell text-base"></i>
                        <span class="absolute right-3 top-3 h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-[#101010]"></span>
                    </button>

                    {{-- ADMIN DROPDOWN --}}
                    <div class="relative" id="adminDropdownBox">
                        <button
                            id="adminDropdownBtn"
                            type="button"
                            class="flex h-11 items-center gap-2 rounded-2xl bg-white/10 px-3 text-white transition hover:bg-white/15"
                        >
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-r from-[#8a4a21] to-[#d99a32]">
                                <i class="fa-solid fa-user-shield text-sm text-white"></i>
                            </div>

                            <div class="hidden min-w-0 text-left lg:block">
                                <div class="max-w-[90px] truncate text-sm font-black leading-tight">
                                    {{ Auth::user()->name ?? 'Admin' }}
                                </div>

                                <div class="text-[11px] leading-tight text-gray-400">
                                    Quản lý
                                </div>
                            </div>

                            <i class="fa-solid fa-chevron-down text-[11px] text-gray-300"></i>
                        </button>

                        <div
                            id="adminDropdownMenu"
                            class="absolute right-0 top-[115%] hidden w-56 overflow-hidden rounded-2xl border border-white/10 bg-[#181818] shadow-2xl"
                        >
                            <div class="flex items-center gap-3 border-b border-white/10 p-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-r from-[#8a4a21] to-[#d99a32]">
                                    <i class="fa-solid fa-user-shield text-sm text-white"></i>
                                </div>

                                <div class="min-w-0">
                                    <div class="truncate text-sm font-bold text-white">
                                        {{ Auth::user()->name ?? 'Admin' }}
                                    </div>

                                    <div class="truncate text-[11px] text-gray-400">
                                        {{ Auth::user()->email ?? 'admin@cinehome.vn' }}
                                    </div>
                                </div>
                            </div>

                            <div class="p-1.5">
                                <a href="{{ route('profile.edit') }}" class="admin-dropdown-item">
                                    <i class="fa-solid fa-user w-4 text-xs"></i>
                                    Tài khoản
                                </a>

                                <a href="{{ route('admin.settings.index') }}" class="admin-dropdown-item">
                                    <i class="fa-solid fa-gear w-4 text-xs"></i>
                                    Cài đặt
                                </a>

                                <a href="{{ route('home') }}" class="admin-dropdown-item">
                                    <i class="fa-solid fa-house w-4 text-xs"></i>
                                    Về trang chủ
                                </a>
                            </div>

                            <div class="border-t border-white/10 p-1.5">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf

                                    <button
                                        type="submit"
                                        class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-sm font-bold text-red-400 transition hover:bg-red-500/15"
                                    >
                                        <i class="fa-solid fa-right-from-bracket w-4 text-xs"></i>
                                        Đăng xuất
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </header>

        {{-- CONTENT --}}
        <section class="w-full overflow-x-hidden px-6 py-6">
            @yield('content')
        </section>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('assets/js/admin.js') }}"></script>

@yield('scripts')
</body>
</html>
