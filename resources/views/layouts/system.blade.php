<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'CineHome - Hệ Thống Điều Phối Tối Cao')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Tailwind / Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link class="router-css" rel="stylesheet" href="{{ asset('assets/css/admin.css') }}?v={{ filemtime(public_path('assets/css/admin.css')) }}">

    <style>
        /* Hiệu ứng accordion đóng mở mượt mà độc lập */
        .sidebar-dropdown-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sidebar-dropdown-box.open .sidebar-dropdown-content {
            max-height: 500px;
            transition: max-height 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sidebar-dropdown-box .fa-chevron-down {
            transition: transform 0.25s ease;
        }
        .sidebar-dropdown-box.open .fa-chevron-down {
            transform: rotate(180deg);
        }
    </style>
</head>

<body class="overflow-x-hidden bg-[#060606] text-white">
    @include('components.preloader')

    <div id="adminLayout" class="min-h-screen overflow-x-hidden bg-[#060606] text-white">

        {{-- SIDEBAR ĐỘC QUYỀN CHO QUẢN LÝ HỆ THỐNG --}}
        <aside id="adminSidebar"
            class="admin-scrollbar fixed left-0 top-0 z-[60] h-screen w-[285px] overflow-y-auto overflow-x-hidden border-r border-[#d99a32]/30 bg-gradient-to-b from-[#0a0502] to-[#160b04] transition-transform duration-300">
            
            {{-- LOGO BRANDING ROOT SYSTEM --}}
            <div class="flex items-center gap-3.5 px-5 py-6 border-b border-white/5">
                <span class="cinehome-logo-sparkle flex h-[76px] w-[76px] items-center justify-center overflow-hidden rounded-3xl bg-transparent p-0 shadow-lg shadow-[#ff2f45]/20">
                    <img src="{{ asset('assets/images/LOGO copy.png') }}" alt="CineHome Logo"
                        class="cinehome-logo-img h-full w-full object-contain">
                </span>

                <div>
                    <h3 class="text-2xl font-black text-white tracking-wide m-0">
                        Cine<span class="text-[#d99a32]">Home</span>
                    </h3>
                    <p class="text-[11px] font-black tracking-widest text-[#f4c56a] uppercase mt-0.5">
                        System Platform
                    </p>
                </div>
            </div>

            {{-- MENU TIÊU CHUẨN COMPACT --}}
            <div class="mt-6 px-3 pb-8 space-y-4">

                {{-- MỤC TĨNH: DASHBOARD ĐẦU NÃO --}}
                <div>
                    <p class="mb-2 px-3 text-[11px] font-black uppercase tracking-widest text-gray-500">Giám sát</p>
                    <a href="{{ route('system.dashboard') }}"
                        class="{{ request()->routeIs('system.dashboard') ? 'bg-[#d99a32] text-[#2b1208]' : 'text-gray-300 hover:bg-white/5' }} no-underline flex items-center gap-3.5 px-4 py-3 rounded-xl text-[16px] font-bold transition duration-200">
                        <i class="fa-solid fa-gauge-high w-5 text-center text-xl {{ request()->routeIs('system.dashboard') ? 'text-[#2b1208]' : 'text-[#d99a32]' }}"></i>
                        Dashboard Đầu Não
                    </a>
                </div>

                {{-- THƯ MỤC DROPDOWN: QUẢN TRỊ NỀN TẢNG THƯƠNG MẠI --}}
                @php $isCoreActive = request()->routeIs('system.payments') || request()->routeIs('system.backups'); @endphp
                <div class="sidebar-dropdown-box {{ $isCoreActive ? 'open' : '' }}">
                    <button type="button" class="sidebar-dropdown-btn w-full flex items-center justify-between px-4 py-3 rounded-xl text-[16px] font-bold text-gray-200 hover:bg-white/5 transition duration-200 border-0 bg-transparent text-left">
                        <span class="flex items-center gap-3.5">
                            <i class="fa-solid fa-gears w-5 text-center text-xl text-[#d99a32]"></i>
                            <span>Cấu hình hạt nhân</span>
                        </span>
                        <i class="fa-solid fa-chevron-down text-[11px] text-gray-500 mr-1"></i>
                    </button>
                    <div class="sidebar-dropdown-content pl-5 mt-1 border-l-2 border-[#d99a32] ml-6 space-y-1">
                        <a href="{{ route('system.payments') }}" class="block py-2.5 pl-3 text-[15px] font-semibold transition duration-200 hover:translate-x-1.5 {{ request()->routeIs('system.payments') ? 'text-[#d99a32]' : 'text-gray-400 hover:text-white' }} no-underline">Cài đặt cổng thanh toán</a>
                        <a href="{{ route('system.backups') }}" class="block py-2.5 pl-3 text-[15px] font-semibold transition duration-200 hover:translate-x-1.5 {{ request()->routeIs('system.backups') ? 'text-[#d99a32]' : 'text-gray-400 hover:text-white' }} no-underline">Sao lưu dữ liệu gốc</a>
                    </div>
                </div>

                {{-- THƯ MỤC DROPDOWN: GIÁM SÁT KỸ THUẬT --}}
                <div class="sidebar-dropdown-box {{ request()->routeIs('system.logs') ? 'open' : '' }}">
                    <button type="button" class="sidebar-dropdown-btn w-full flex items-center justify-between px-4 py-3 rounded-xl text-[16px] font-bold text-gray-200 hover:bg-white/5 transition duration-200 border-0 bg-transparent text-left">
                        <span class="flex items-center gap-3.5">
                            <i class="fa-solid fa-shield-halved w-5 text-center text-xl text-[#d99a32]"></i>
                            <span>Bảo mật & Kiểm toán</span>
                        </span>
                        <i class="fa-solid fa-chevron-down text-[11px] text-gray-500 mr-1"></i>
                    </button>
                    <div class="sidebar-dropdown-content pl-5 mt-1 border-l-2 border-[#d99a32] ml-6 space-y-1">
                        <a href="{{ route('system.logs') }}" class="block py-2.5 pl-3 text-[15px] font-semibold transition duration-200 hover:translate-x-1.5 {{ request()->routeIs('system.logs') ? 'text-[#d99a32]' : 'text-gray-400 hover:text-white' }} no-underline">Log hàng đợi & Monitor lỗi</a>
                    </div>
                </div>

                {{-- LỐI THOÁT RA WEBSITE CHỦ --}}
                <div class="pt-3 border-t border-white/10">
                    <a href="{{ route('home') }}"
                        class="flex items-center gap-3.5 rounded-xl px-4 py-3 text-[16px] font-bold text-gray-300 hover:bg-white/5 hover:text-white transition duration-200 no-underline">
                        <i class="fa-solid fa-house w-5 text-center text-xl text-[#d99a32]"></i>
                        <span>Xem trang chủ ngoài</span>
                    </a>
                </div>

            </div>
        </aside>

        {{-- KHU VỰC CHỨA NỘI DUNG VIEW CHÍNH --}}
        <main id="adminMain" class="min-h-screen overflow-x-hidden bg-[#060606] ml-[285px]">

            {{-- TOPBAR KHÔNG GỘP CHUNG --}}
            <header class="sticky top-0 z-50 border-b border-white/10 bg-[#0d0d0d]/95 backdrop-blur-xl">
                <div class="flex h-[76px] items-center justify-between gap-4 px-5">
                    
                    <div class="flex min-w-0 items-center gap-4">
                        <div class="min-w-0">
                            <h1 class="truncate text-[22px] font-black leading-tight text-white m-0">
                                @yield('page-title', 'Bảng cấu hình gốc Platform')
                            </h1>
                        </div>
                    </div>

                    {{-- RIGHT: CHỈ HIỂN THỊ THÔNG TIN USER TỐI CAO --}}
                    <div class="flex items-center gap-3">
                        @auth
                        <div class="relative" id="adminDropdownBox">
                            <button type="button" class="inline-flex items-center gap-3 rounded-2xl bg-white/10 px-4 py-2 border-0">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-r from-[#d99a32] to-[#8a4a21] text-white">
                                    <i class="fa-solid fa-user-gear"></i>
                                </div>
                                <div class="hidden sm:block text-left">
                                    <div class="text-sm font-bold text-white max-w-[120px] truncate">{{ Auth::user()->ho_ten }}</div>
                                    <div class="text-xs text-[#d99a32] font-semibold mt-0.5">Quản lý hệ thống</div>
                                </div>
                            </button>
                        </div>
                        @endauth
                    </div>

                </div>
            </header>

            {{-- KHU VỰC INJECT VIEW CON --}}
            <section class="w-full overflow-x-hidden px-6 py-6">
                @yield('content')
            </section>
        </main>
    </div>

    {{-- SCRIPT ĐỘC LẬP MULTI-OPEN CHO TRANG CHỦ HỆ THỐNG --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dropdownBoxes = document.querySelectorAll('.sidebar-dropdown-box');
            dropdownBoxes.forEach(box => {
                const btn = box.querySelector('.sidebar-dropdown-btn');
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    box.classList.toggle('open'); // Đóng/mở độc lập, không ảnh hưởng đến các thư mục khác
                });
            });
        });
    </script>
</body>
</html>
