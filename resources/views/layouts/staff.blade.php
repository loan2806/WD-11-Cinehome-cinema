<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Staff - CineHome')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

<<<<<<< HEAD
    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
=======
    {{-- Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

>>>>>>> 807f5a1576cca599974fda78f3d7355628e310d9
    {{-- Font Awesome --}}
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<<<<<<< HEAD
<body>
    @include('components.preloader')

<div class="dashboard-wrapper">

    <aside class="sidebar">
        <div class="sidebar-logo">
            <img src="{{ asset('assets/images/logo.png') }}" alt="CineHome Logo">
            <div>
                <h5>CineHome</h5>
                <small>Staff Panel</small>
            </div>
        </div>

        <div class="sidebar-menu">
            <a href="#" class="active">
                <i class="fa-solid fa-gauge"></i> Tổng quan
            </a>

            <a href="#">
                <i class="fa-solid fa-qrcode"></i> Soát vé QR
            </a>

            <a href="#">
                <i class="fa-solid fa-ticket"></i> Bán vé tại quầy
            </a>

            <a href="#">
                <i class="fa-solid fa-chair"></i> Chọn ghế
            </a>

            <a href="#">
                <i class="fa-solid fa-money-bill-wave"></i> Thanh toán
            </a>

            <a href="#">
                <i class="fa-solid fa-layer-group"></i> Phân loại vé
            </a>

            <a href="#">
                <i class="fa-solid fa-clock-rotate-left"></i> Lịch sử bán vé
            </a>

            <a href="{{ route('home') }}">
                <i class="fa-solid fa-house"></i> Về trang User
            </a>
        </div>
    </aside>

    <section class="dashboard-main">
        <header class="dashboard-topbar">
            <div>
                <h5 class="mb-0 fw-bold">@yield('page-title', 'Trang nhân viên')</h5>
                <small class="text-muted">Soát vé, bán vé và quản lý vé offline</small>
=======

<body class="overflow-x-hidden bg-[#080808] text-white">

@include('components.preloader')

<div class="min-h-screen overflow-x-hidden bg-[#080808] text-white">

    {{-- SIDEBAR --}}
    <aside
        id="staffSidebar"
        class="fixed left-0 top-0 z-[60] h-screen w-[270px]
        overflow-y-auto border-r border-[#d99a32]/20
        bg-gradient-to-b from-[#1a0b04] to-[#2b1208]
        transition-transform duration-300"
    >

        {{-- LOGO --}}
        <div class="flex items-center gap-3 px-5 py-6">

            <img
                src="{{ asset('assets/images/logo.png') }}"
                class="h-16 w-16 rounded-2xl bg-white object-contain p-1"
            >

            <div>
                <h3 class="text-2xl font-black text-white">
                    Cine<span class="text-[#d99a32]">Home</span>
                </h3>

                <p class="text-sm font-bold text-[#f4c56a]">
                    Staff Panel
                </p>
>>>>>>> 807f5a1576cca599974fda78f3d7355628e310d9
            </div>
        </div>

<<<<<<< HEAD
            <div class="dropdown">
                <button class="btn btn-cine dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fa-solid fa-user-tie"></i> Nhân viên
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a href="#" class="dropdown-item">Tài khoản</a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="dropdown-item text-danger">Đăng xuất</button>
                        </form>
                    </li>
                </ul>
            </div>
        </header>

        <main class="dashboard-content">
            @yield('content')
        </main>
    </section>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@yield('scripts')
=======
        {{-- MENU --}}
        <div class="mt-4 px-3 pb-8">

            <p class="mb-3 px-3 text-xs font-black uppercase tracking-widest text-[#d7a767]">
                Tổng quan
            </p>

            <nav class="space-y-2">

                <a
                    href="{{ route('staff.dashboard') }}"
                    class="flex items-center gap-3 rounded-2xl px-4 py-3 font-bold transition
                    {{ request()->routeIs('staff.dashboard')
                        ? 'bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white shadow-lg'
                        : 'text-gray-300 hover:bg-white/10'
                    }}"
                >
                    <i class="fa-solid fa-chart-line w-5"></i>
                    Dashboard
                </a>

                <a
                    href="{{ route('staff.tickets.scan') }}"
                    class="flex items-center gap-3 rounded-2xl px-4 py-3 font-bold transition
                    {{ request()->routeIs('staff.tickets.scan')
                        ? 'bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white shadow-lg'
                        : 'text-gray-300 hover:bg-white/10'
                    }}"
                >
                    <i class="fa-solid fa-qrcode w-5"></i>
                    Soát vé QR
                </a>

                <a href="#"
                    class="flex items-center gap-3 rounded-2xl px-4 py-3 font-bold text-gray-300 transition hover:bg-white/10">
                    <i class="fa-solid fa-ticket w-5"></i>
                    Bán vé
                </a>

                <a href="#"
                    class="flex items-center gap-3 rounded-2xl px-4 py-3 font-bold text-gray-300 transition hover:bg-white/10">
                    <i class="fa-solid fa-chair w-5"></i>
                    Chọn ghế
                </a>

                <a href="#"
                    class="flex items-center gap-3 rounded-2xl px-4 py-3 font-bold text-gray-300 transition hover:bg-white/10">
                    <i class="fa-solid fa-clock-rotate-left w-5"></i>
                    Lịch sử vé
                </a>

            </nav>

            <p class="mb-3 mt-7 px-3 text-xs font-black uppercase tracking-widest text-[#d7a767]">
                Hệ thống
            </p>

            <nav class="space-y-2">

                <a href="{{ route('home') }}"
                    class="flex items-center gap-3 rounded-2xl px-4 py-3 font-bold text-gray-300 transition hover:bg-white/10">

                    <i class="fa-solid fa-house w-5"></i>
                    Trang chủ
                </a>

            </nav>

        </div>
    </aside>

    {{-- MAIN --}}
    <main
        id="staffMain"
        class="min-h-screen bg-[#080808] ml-[270px]"
    >

        {{-- TOPBAR --}}
        <header class="sticky top-0 z-50 border-b border-white/10 bg-[#101010]/95 backdrop-blur-xl">

            <div class="flex h-[76px] items-center justify-between px-6">

                {{-- LEFT --}}
                <div>

                    <h1 class="text-[24px] font-black text-white">
                        @yield('page-title', 'Staff Dashboard')
                    </h1>

                    <p class="text-sm text-gray-400">
                        Quản lý vé và hỗ trợ khách hàng
                    </p>
                </div>

                {{-- RIGHT --}}
                <div class="flex items-center gap-3">

                    <div class="hidden lg:flex h-11 w-[300px]
                        items-center gap-3 rounded-2xl border border-white/10
                        bg-white/5 px-4">

                        <i class="fa-solid fa-magnifying-glass text-[#d99a32]"></i>

                        <input
                            type="text"
                            placeholder="Tìm kiếm..."
                            class="h-full w-full bg-transparent text-sm text-white outline-none"
                        >
                    </div>

                    {{-- USER --}}
                    <div class="flex items-center gap-3 rounded-2xl bg-white/10 px-4 py-2">

                        <div class="flex h-10 w-10 items-center justify-center rounded-full
                            bg-gradient-to-r from-[#8a4a21] to-[#d99a32]">

                            <i class="fa-solid fa-user-tie"></i>
                        </div>

                        <div>
                            <div class="text-sm font-bold">
                                {{ Auth::user()->name }}
                            </div>

                            <div class="text-xs text-gray-400">
                                Nhân viên
                            </div>
                        </div>

                    </div>

                    {{-- LOGOUT --}}
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf

                        <button
                            class="rounded-2xl bg-red-500/20 px-4 py-2 font-bold text-red-400 transition hover:bg-red-500/30"
                        >
                            Đăng xuất
                        </button>
                    </form>

                </div>

            </div>

        </header>

        {{-- CONTENT --}}
        <section class="px-6 py-6">
            @yield('content')
        </section>

    </main>

</div>

>>>>>>> 807f5a1576cca599974fda78f3d7355628e310d9
</body>
</html>