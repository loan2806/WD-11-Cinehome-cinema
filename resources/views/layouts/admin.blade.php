<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'CineHome - Nền tảng quản trị tối cao')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .sidebar-dropdown-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s ease;
        }

        .sidebar-dropdown-box.open .sidebar-dropdown-content {
            max-height: 1000px;
        }

        .sidebar-dropdown-box .fa-chevron-down {
            transition: transform 0.25s ease;
        }

        .sidebar-dropdown-box.open .fa-chevron-down {
            transform: rotate(180deg);
        }
    </style>
</head>

<body class="overflow-x-hidden bg-[#080808] text-white">

@include('components.preloader')

@if (session('success'))
    <x-toast type="success" :message="session('success')" />
@endif
@if (session('error'))
    <x-toast type="error" :message="session('error')" />
@endif
@if (session('warning'))
    <x-toast type="warning" :message="session('warning')" />
@endif

<x-modal-confirm />

<div id="adminLayout" class="min-h-screen">

    {{-- SIDEBAR --}}
    <aside id="adminSidebar"
        class="fixed left-0 top-0 h-screen w-[285px] overflow-y-auto bg-gradient-to-b from-[#110702] to-[#200d05] border-r border-[#d99a32]/20">

        {{-- LOGO --}}
        <div class="flex items-center gap-3 px-5 py-6 border-b border-white/5">
            <img src="{{ asset('assets/images/logo.png') }}" class="h-14 w-14 rounded-xl bg-white p-1">
            <div>
                <h3 class="font-black text-xl">Cine<span class="text-[#d99a32]">Home</span></h3>
                <p class="text-xs text-[#f4c56a]">Root Controller</p>
            </div>
        </div>

        <div class="mt-5 px-3 space-y-3">

            {{-- DASHBOARD --}}
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold
               {{ request()->routeIs('admin.dashboard') ? 'bg-[#d99a32] text-black' : 'text-gray-300 hover:bg-white/5' }}">
                <i class="fa fa-chart-line"></i> Dashboard
            </a>

            {{-- QUẢN TRỊ NỀN TẢNG --}}
            @if (auth()->user()->hasRole('Quản lý hệ thống') || auth()->user()->vai_tro === 'admin')
            <div class="sidebar-dropdown-box">
                <button class="sidebar-dropdown-btn w-full flex justify-between px-4 py-3 text-[#f4c56a]">
                    <span><i class="fa fa-server"></i> Quản trị nền tảng</span>
                    <i class="fa fa-chevron-down"></i>
                </button>
                <div class="sidebar-dropdown-content pl-6 space-y-2">
                    <a href="#" class="text-gray-400 block">Cổng thanh toán</a>
                    <a href="#" class="text-gray-400 block">Backup dữ liệu</a>
                    <a href="#" class="text-gray-400 block">Logs hệ thống</a>
                </div>
            </div>
            @endif

            {{-- PHIM --}}
            @if (auth()->user()->can('quan_ly_phim_suat_chieu'))
            <div class="sidebar-dropdown-box">
                <button class="sidebar-dropdown-btn w-full flex justify-between px-4 py-3">
                    <span><i class="fa fa-film"></i> Quản lý phim</span>
                    <i class="fa fa-chevron-down"></i>
                </button>
                <div class="sidebar-dropdown-content pl-6">
                    <a href="{{ route('admin.phims.index') }}">Danh sách phim</a>
                    <a href="{{ route('admin.suat-chieus.index') }}">Suất chiếu</a>
                    <a href="{{ route('admin.the-loais.index') }}">Thể loại</a>
                </div>
            </div>
            @endif

            {{-- PHÒNG GHẾ --}}
            @if (auth()->user()->can('quan_ly_phong_ghe'))
            <div class="sidebar-dropdown-box">
                <button class="sidebar-dropdown-btn w-full flex justify-between px-4 py-3">
                    <span><i class="fa fa-door-open"></i> Phòng chiếu</span>
                    <i class="fa fa-chevron-down"></i>
                </button>
                <div class="sidebar-dropdown-content pl-6">
                    <a href="{{ route('admin.phong-chieus.index') }}">Phòng chiếu</a>
                    <a href="{{ route('admin.loai-ghes.index') }}">Loại ghế</a>
                </div>
            </div>
            @endif

            {{-- VÉ + QR + FOOD --}}
            @if(auth()->user()->can('ban_ve_tai_quay')
                || auth()->user()->can('quan_ly_do_an_combo')
                || auth()->user()->can('soat_ve_vao_cua'))

            @php
                $active = request()->routeIs('admin.food-invoices.*')
                        || request()->routeIs('admin.ve-xem-phims.*')
                        || request()->routeIs('admin.soat-ve.*');
            @endphp

            <div class="sidebar-dropdown-box {{ $active ? 'open' : '' }}">
                <button class="sidebar-dropdown-btn w-full flex justify-between px-4 py-3">
                    <span><i class="fa fa-ticket"></i> Quầy vé</span>
                    <i class="fa fa-chevron-down"></i>
                </button>

                <div class="sidebar-dropdown-content pl-6 space-y-2">

                    @can('ban_ve_tai_quay')
                        <a href="{{ route('admin.ve-xem-phims.index') }}">Quản lý vé</a>
                        <a href="#">Bán vé</a>
                    @endcan

                    @can('soat_ve_vao_cua')
                        <a href="{{ route('admin.soat-ve.index') }}">Soát vé QR</a>
                    @endcan

                    @can('quan_ly_do_an_combo')
                        <a href="{{ route('admin.food-invoices.index') }}">Đồ ăn & Combo</a>
                    @endcan

                </div>
            </div>
            @endif

            {{-- KHÁC --}}
            <div class="pt-3 border-t border-white/10">
                <a href="{{ route('home') }}" class="flex gap-3 px-4 py-3 text-gray-300">
                    <i class="fa fa-home"></i> Trang chủ
                </a>
            </div>

        </div>
    </aside>

    {{-- MAIN --}}
    <main class="ml-[285px] min-h-screen">

        {{-- TOPBAR --}}
        <header class="sticky top-0 bg-[#101010] border-b border-white/10 h-[76px] flex items-center justify-between px-5">

            <div>
                <h1 class="font-black text-xl">@yield('page-title','Dashboard')</h1>
                <p class="text-gray-400 text-sm">@yield('page-subtitle')</p>
            </div>

            <div class="flex items-center gap-4">

                {{-- USER --}}
                <div class="flex items-center gap-3 bg-white/10 px-4 py-2 rounded-xl">
                    <i class="fa fa-user"></i>
                    <span>{{ Auth::user()->ho_ten }}</span>
                </div>

            </div>
        </header>

        {{-- CONTENT --}}
        <section class="p-6">
            @yield('content')
        </section>

    </main>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.sidebar-dropdown-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            btn.parentElement.classList.toggle('open');
        });
    });
});
</script>

@stack('scripts')
</body>
</html>