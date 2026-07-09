<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Quản lý rạp - CineHome')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link class="router-css" rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">

    @stack('styles')

    <style>
        .sidebar-dropdown-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-dropdown-box.open>.sidebar-dropdown-content {
            max-height: 1000px;
            transition: max-height 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-dropdown-box .fa-chevron-down {
            transition: transform 0.25s ease;
        }

        .sidebar-dropdown-box.open>button .fa-chevron-down {
            transform: rotate(180deg);
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>

<body class="overflow-x-hidden bg-[#080808] text-white">
    @include('components.preloader')

    @if (session('success') || session('error') || session('warning'))
        <div style="position: fixed; top: 24px; right: 24px; z-index: 100000; display: flex; width: min(390px, calc(100vw - 32px)); flex-direction: column; gap: 12px; pointer-events: none;">
            @if (session('success'))
                <x-toast type="success" :message="session('success')" />
            @endif
            @if (session('error'))
                <x-toast type="error" :message="session('error')" />
            @endif
            @if (session('warning'))
                <x-toast type="warning" :message="session('warning')" />
            @endif
        </div>
    @endif

    <x-modal-confirm />

    <div id="adminLayout" data-sidebar="open" class="min-h-screen overflow-x-hidden bg-[#080808] text-white">

        <aside id="adminSidebar"
            class="admin-scrollbar fixed left-0 top-0 z-[60] h-screen w-[285px] overflow-y-auto overflow-x-hidden border-r border-[#d99a32]/20 bg-gradient-to-b from-[#110702] to-[#200d05] transition-transform duration-300">

            <div class="flex items-center gap-3.5 border-b border-white/5 px-5 py-6">
                <img src="{{ asset('assets/images/logo.png') }}" alt="CineHome Logo"
                    class="h-16 w-16 rounded-2xl bg-white object-contain p-1 shadow-lg shadow-[#d99a32]/10">

                <div>
                    <h3 class="m-0 text-2xl font-black tracking-wide text-white">
                        Cine<span class="text-[#d99a32]">Home</span>
                    </h3>
                    <p class="mt-0.5 text-xs font-black uppercase tracking-widest text-[#f4c56a]">
                        Quản lý rạp
                    </p>
                </div>
            </div>

            <div class="mt-5 space-y-4 px-3 pb-8">
                <div>
                    <p class="mb-2 px-3 text-[11px] font-black uppercase tracking-widest text-[#d7a767]/50">Tổng quan</p>
                    <a href="{{ route('manager.dashboard') }}"
                        class="{{ request()->routeIs('manager.dashboard') ? 'bg-[#d99a32] text-[#2b1208]' : 'text-gray-300 hover:bg-white/5' }} flex items-center gap-3.5 rounded-xl px-4 py-3 text-[16px] font-bold no-underline transition duration-200">
                        <i
                            class="fa-solid fa-chart-line w-5 text-center text-xl {{ request()->routeIs('manager.dashboard') ? 'text-[#2b1208]' : 'text-[#d99a32]' }}"></i>
                        Dashboard
                    </a>
                </div>

                @if(auth()->user()->can('quan_ly_phim_suat_chieu'))
                    @php
                        $isPhimActive = request()->routeIs('manager.phims.*') || request()->routeIs('manager.suat-chieus.*');
                    @endphp
                    <div class="sidebar-dropdown-box {{ $isPhimActive ? 'open' : '' }}">
                        <button type="button"
                            class="sidebar-dropdown-btn flex w-full items-center justify-between rounded-xl border border-[#d99a32]/30 bg-white/5 px-4 py-3 text-left text-[16px] font-black leading-none text-[#f4c56a] outline-none transition duration-200 hover:bg-white/10">
                            <span class="flex items-center gap-3.5">
                                <i class="fa-solid fa-film w-5 text-center text-xl text-[#d99a32]"></i>
                                <span>Nội dung phim</span>
                            </span>
                            <i class="fa-solid fa-chevron-down mr-1 text-[11px] text-[#f4c56a]"></i>
                        </button>
                        <div class="sidebar-dropdown-content pl-5 mt-1 border-l-2 border-[#d99a32] ml-6 space-y-1">
                            <a href="{{ route('manager.phims.index') }}"
                                class="block py-2.5 pl-3 text-[15px] font-bold transition duration-200 hover:translate-x-1.5 {{ request()->routeIs('manager.phims.*') ? 'text-[#d99a32]' : 'text-gray-400 hover:text-white' }} no-underline">Danh
                                sách phim</a>
                            <a href="{{ route('manager.suat-chieus.index') }}"
                                class="block py-2.5 pl-3 text-[15px] font-bold transition duration-200 hover:translate-x-1.5 {{ request()->routeIs('manager.suat-chieus.*') ? 'text-[#d99a32]' : 'text-gray-400 hover:text-white' }} no-underline">Lịch
                                suất chiếu</a>
                        </div>
                    </div>
                @endif

                @if(auth()->user()->can('quan_ly_phong_ghe'))
                    @php
                        $isPhongActive = request()->routeIs('manager.phong-chieus.*') || request()->routeIs('manager.loai-ghes.*');
                    @endphp
                    <div class="sidebar-dropdown-box {{ $isPhongActive ? 'open' : '' }}">
                        <button type="button"
                            class="sidebar-dropdown-btn flex w-full items-center justify-between rounded-xl border border-[#d99a32]/30 bg-white/5 px-4 py-3 text-left text-[16px] font-black leading-none text-[#f4c56a] outline-none transition duration-200 hover:bg-white/10">
                            <span class="flex items-center gap-3.5">
                                <i class="fa-solid fa-door-open w-5 text-center text-xl text-[#d99a32]"></i>
                                <span>Phòng chiếu & Ghế</span>
                            </span>
                            <i class="fa-solid fa-chevron-down mr-1 text-[11px] text-[#f4c56a]"></i>
                        </button>
                        <div class="sidebar-dropdown-content pl-5 mt-1 border-l-2 border-[#d99a32] ml-6 space-y-1">
                            <a href="{{ route('manager.phong-chieus.index') }}"
                                class="block py-2.5 pl-3 text-[15px] font-bold transition duration-200 hover:translate-x-1.5 {{ request()->routeIs('manager.phong-chieus.*') ? 'text-[#d99a32]' : 'text-gray-400 hover:text-white' }} no-underline">Quản
                                lý phòng chiếu</a>
                            <a href="{{ route('manager.loai-ghes.index') }}"
                                class="block py-2.5 pl-3 text-[15px] font-bold transition duration-200 hover:translate-x-1.5 {{ request()->routeIs('manager.loai-ghes.*') ? 'text-[#d99a32]' : 'text-gray-400 hover:text-white' }} no-underline">Loại
                                ghế</a>
                        </div>
                    </div>
                @endif

                @if(auth()->user()->can('ban_ve_tai_quay') || auth()->user()->can('soat_ve_vao_cua') || auth()->user()->can('quan_ly_do_an_combo'))
                    @php
                        $isQuayActive = request()->routeIs('manager.foods.*') || request()->routeIs('manager.soat-ve.*') || request()->routeIs('manager.ve-xem-phims.*');
                    @endphp
                    <div class="sidebar-dropdown-box {{ $isQuayActive ? 'open' : '' }}">
                        <button type="button"
                            class="sidebar-dropdown-btn flex w-full items-center justify-between rounded-xl border border-[#d99a32]/30 bg-white/5 px-4 py-3 text-left text-[16px] font-black leading-none text-[#f4c56a] outline-none transition duration-200 hover:bg-white/10">
                            <span class="flex items-center gap-3.5">
                                <i class="fa-solid fa-ticket w-5 text-center text-xl text-[#d99a32]"></i>
                                <span>Nghiệp vụ quầy</span>
                            </span>
                            <i class="fa-solid fa-chevron-down mr-1 text-[11px] text-[#f4c56a]"></i>
                        </button>
                        <div class="sidebar-dropdown-content pl-5 mt-1 border-l-2 border-[#d99a32] ml-6 space-y-1">
                            @can('ban_ve_tai_quay')
                                <a href="{{ route('manager.ve-xem-phims.index') }}"
                                    class="block py-2.5 pl-3 text-[15px] font-bold transition duration-200 hover:translate-x-1.5 {{ request()->routeIs('manager.ve-xem-phims.*') ? 'text-[#d99a32]' : 'text-gray-400 hover:text-white' }} no-undefined">Quản
                                    lý vé</a>
                            @endcan
                            @can('soat_ve_vao_cua')
                                <a href="{{ route('manager.soat-ve.index') }}"
                                    class="block py-2.5 pl-3 text-[15px] font-bold transition duration-200 hover:translate-x-1.5 {{ request()->routeIs('manager.soat-ve.*') ? 'text-[#d99a32]' : 'text-gray-400 hover:text-white' }} no-undefined">Soát vé</a>
                            @endcan
                            @can('quan_ly_do_an_combo')
                                <a href="{{ route('manager.foods.index') }}"
                                    class="block py-2.5 pl-3 text-[15px] font-bold transition duration-200 hover:translate-x-1.5 {{ request()->routeIs('manager.foods.*') ? 'text-[#d99a32]' : 'text-gray-400 hover:text-white' }} no-undefined">Đồ ăn & Combo</a>
                            @endcan
                        </div>
                    </div>
                @endif

                @if(auth()->user()->can('quan_ly_nhan_vien') || auth()->user()->can('quan_ly_khach_hang'))
                    @php
                        $isTaiKhoanActive = request()->routeIs('manager.nhanviens.*') || request()->routeIs('manager.khach-hang.*');
                    @endphp
                    <div class="sidebar-dropdown-box {{ $isTaiKhoanActive ? 'open' : '' }}">
                        <button type="button"
                            class="sidebar-dropdown-btn flex w-full items-center justify-between rounded-xl border border-[#d99a32]/30 bg-white/5 px-4 py-3 text-left text-[16px] font-black leading-none text-[#f4c56a] outline-none transition duration-200 hover:bg-white/10">
                            <span class="flex items-center gap-3.5">
                                <i class="fa-solid fa-user-gear w-5 text-center text-xl text-[#d99a32]"></i>
                                <span>Tài khoản & Nhân lực</span>
                            </span>
                            <i class="fa-solid fa-chevron-down mr-1 text-[11px] text-[#f4c56a]"></i>
                        </button>
                        <div class="sidebar-dropdown-content pl-5 mt-1 border-l-2 border-[#d99a32] ml-6 space-y-1">
                            @can('quan_ly_nhan_vien')
                                <a href="{{ route('manager.nhanviens.index') }}"
                                    class="block py-2.5 pl-3 text-[15px] font-bold transition duration-200 hover:translate-x-1.5 {{ request()->routeIs('manager.nhanviens.*') ? 'text-[#d99a32]' : 'text-gray-400 hover:text-white' }} no-underline">Nhân
                                    viên</a>
                            @endcan
                            @can('quan_ly_khach_hang')
                                <a href="{{ route('manager.khach-hang.index') }}"
                                    class="block py-2.5 pl-3 text-[15px] font-bold transition duration-200 hover:translate-x-1.5 {{ request()->routeIs('manager.khach-hang.*') ? 'text-[#d99a32]' : 'text-gray-400 hover:text-white' }} no-undefined">Khách
                                    hàng</a>
                            @endcan
                        </div>
                    </div>
                @endif

                @if(auth()->user()->can('thong_ke_doanh_thu'))
                    <div>
                        <a href="{{ route('manager.revenue-reports.index') }}"
                            class="flex items-center gap-3.5 rounded-xl px-4 py-3 text-[16px] font-bold transition duration-200 {{ request()->routeIs('manager.revenue-reports.*') ? 'bg-[#d99a32] text-[#2b1208]' : 'text-gray-300 hover:bg-white/5' }} no-underline">
                            <i class="fa-solid fa-chart-pie w-5 text-center text-xl {{ request()->routeIs('manager.revenue-reports.*') ? 'text-[#2b1208]' : 'text-[#d99a32]' }}"></i>
                            Báo cáo doanh thu
                        </a>
                    </div>
                @endif

                <div class="border-t border-white/10 pt-3">
                    <a href="{{ route('home') }}"
                        class="flex items-center gap-3.5 rounded-xl px-4 py-3 text-[16px] font-bold text-gray-300 no-underline transition duration-200 hover:bg-white/5 hover:text-white">
                        <i class="fa-solid fa-house w-5 text-center text-xl text-[#d99a32]"></i>
                        <span>Xem trang chủ ngoài</span>
                    </a>
                </div>
            </div>
        </aside>

        <main id="adminMain" class="ml-[285px] min-h-screen overflow-x-hidden bg-[#080808]">

            <header class="sticky top-0 z-50 border-b border-white/10 bg-[#101010]/95 backdrop-blur-xl">
                <div class="flex h-[76px] items-center justify-between gap-4 px-5">

                    <div class="flex min-w-0 items-center gap-4">
                        <button id="sidebarToggle" type="button"
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border-0 bg-white/10 text-white transition hover:bg-[#d99a32] hover:text-[#2b1208]">
                            <i class="fa-solid fa-bars text-lg"></i>
                        </button>

                        <div class="min-w-0">
                            <h1 class="m-0 truncate text-[22px] font-black leading-tight text-white">
                                @yield('page-title', 'Quản lý rạp')
                            </h1>
                            <p class="m-0 mt-1 max-w-[430px] truncate text-sm text-gray-400 xl:max-w-[560px]">
                                @yield('page-subtitle', 'Điều hành nội dung, phòng chiếu và báo cáo doanh thu')
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        @auth
                            <div class="relative" id="adminDropdownBox">
                                <button type="button" id="adminDropdownBtn"
                                    class="inline-flex items-center gap-3 rounded-2xl border-0 bg-white/10 px-4 py-2 transition hover:bg-white/15">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white shadow-md">
                                        <i class="fa-solid fa-user-shield"></i>
                                    </div>

                                    <div class="hidden text-left sm:block">
                                        <div class="max-w-[120px] truncate text-sm font-bold text-white">
                                            {{ Auth::user()->ho_ten }}
                                        </div>
                                        <div class="mt-0.5 text-xs font-semibold text-[#d99a32]">
                                            Quản lý rạp
                                        </div>
                                    </div>

                                    <i class="fa-solid fa-chevron-down ml-1 text-[10px] text-gray-400"></i>
                                </button>

                                <div id="adminDropdownMenu"
                                    class="absolute right-0 top-[125%] z-[9999] hidden w-60 overflow-hidden rounded-xl border border-[#d99a32]/30 bg-[#151515]/95 shadow-2xl backdrop-blur-md">
                                    <div class="flex items-center gap-3 border-b border-white/10 bg-white/5 px-4 py-3">
                                        <div
                                            class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white">
                                            <i class="fa-solid fa-user text-sm"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="truncate text-sm font-bold text-white">{{ Auth::user()->ho_ten }}
                                            </div>
                                            <div class="truncate text-[11px] text-gray-400">{{ Auth::user()->email }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-1.5 border-b border-white/10">
                                        <a href="{{ route('profile.edit') }}"
                                            class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-gray-300 transition hover:bg-[#d99a32] hover:text-[#2b1208] no-underline">
                                            <i class="fa-solid fa-user-gear w-4 text-xs text-center"></i> Hồ sơ cá nhân
                                        </a>
                                    </div>
                                    <div class="p-1.5 bg-[#1a1a1a]/30">
                                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                                            @csrf
                                            <button type="submit"
                                                class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-bold text-red-400 transition hover:bg-red-500/15 border-0 bg-transparent text-left">
                                                <i class="fa-solid fa-right-from-bracket w-4 text-xs text-center"></i> Đăng
                                                xuất
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endauth
                    </div>
                </div>
            </header>

            <section class="w-full overflow-x-hidden px-6 py-6">
                @yield('content')
            </section>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownBoxes = document.querySelectorAll('.sidebar-dropdown-box');

            dropdownBoxes.forEach(box => {
                const btn = box.querySelector('.sidebar-dropdown-btn');

                if (btn) {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        box.classList.toggle('open');
                    });
                }
            });

            const dropdownBox = document.getElementById('adminDropdownBox');
            const dropdownBtn = document.getElementById('adminDropdownBtn');
            const dropdownMenu = document.getElementById('adminDropdownMenu');

            if (dropdownBtn && dropdownMenu && dropdownBox) {
                dropdownBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropdownMenu.classList.toggle('hidden');
                });

                document.addEventListener('click', function(e) {
                    if (!dropdownBox.contains(e.target)) {
                        dropdownMenu.classList.add('hidden');
                    }
                });
            }
        });
    </script>
    @stack('scripts')
</body>

</html>
