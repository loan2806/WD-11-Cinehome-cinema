<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CineHome') }} - Hồ Sơ Cá Nhân</title>

    <link rel="preconnect" href="https://fonts.bundlejs.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#0f0f0f] text-gray-200 m-0 p-0 selection:bg-[#d99a32] selection:text-[#2b1208]">
    <div class="min-h-screen bg-[#0f0f0f]">
        
        @php
            // ĐỒNG BỘ LOGO: Tự động kết nối kho dữ liệu cấu hình tối cao của rạp từ Admin
            $heThongSettings = \App\Models\CaiDatHeThong::first();
            $urlLogo = ($heThongSettings && $heThongSettings->logo) 
                ? asset('storage/' . $heThongSettings->logo) 
                : asset('assets/images/logo.png');
            $user = Auth::user();
        @endphp

        {{-- THIẾT KẾ NAVBAR ĐỒNG BỘ PHONG CÁCH RẠP PHIM (CINEHOME ROOT CONTROLLER) --}}
        <nav x-data="{ openDropdown: false }" class="bg-[#121212] border-b border-white/10 sticky top-0 z-50 h-20 shadow-xl">
            <div class="max-w-7xl mx-auto h-full px-4 sm:px-6 lg:px-8 flex items-center justify-between">
                
                {{-- KHỐI TRÁI: LOGO HỆ THỐNG VÀ TIÊU ĐỀ PHÂN HỆ --}}
                <div class="flex items-center gap-6">
                    {{-- Logo cấu hình động --}}
                    <a href="{{ route('home') }}" class="flex items-center gap-3 text-decoration-none group">
                        <div class="h-11 w-11 rounded-xl bg-white/5 p-1.5 border border-white/10 flex items-center justify-center overflow-hidden transition group-hover:border-[#d99a32]/40">
                            <img src="{{ $urlLogo }}" alt="CineHome Logo" class="object-contain max-h-full max-w-full">
                        </div>
                        <div class="hidden sm:block">
                            <span class="block text-lg font-black text-white tracking-tight leading-none">Cine<span class="text-[#d99a32]">Home</span></span>
                            <span class="block text-[10px] text-gray-500 font-bold uppercase tracking-widest mt-1">Hồ sơ cá nhân</span>
                        </div>
                    </a>

                    {{-- Thanh ngăn đứng phân tách --}}
                    <div class="h-8 w-[1px] bg-white/10 hidden md:block"></div>

                    {{-- ĐOẠN ĐÃ SỬA: Thay thế chữ Dashboard thành "Hồ Sơ Cá Nhân" bản chữ lớn thanh lịch --}}
                    <div class="hidden md:block">
                        <h1 class="text-xl font-black text-white m-0 tracking-wide flex items-center gap-2">
                            <i class="fa-solid fa-address-card text-[#d99a32] text-base"></i> Hồ Sơ Cá Nhân
                        </h1>
                    </div>
                </div>

                {{-- KHỐI PHẢI: CỤM THÔNG BÁO VÀ DROPDOWN TÀI KHOẢN --}}
                <div class="flex items-center gap-4">
                    
                    {{-- Chuông thông báo đồng bộ giao diện Admin --}}
                    <a href="{{ route('user.notifications.index') }}" class="h-10 w-10 rounded-xl bg-white/5 border border-white/5 text-gray-400 hover:text-white hover:bg-white/10 transition flex items-center justify-center relative text-decoration-none">
                        <i class="fa-solid fa-bell text-sm"></i>
                        <span class="absolute top-2 right-2.5 h-2 w-2 rounded-full bg-[#d99a32] shadow-lg"></span>
                    </a>

                    {{-- CỤM DROPDOWN TÀI KHOẢN AN ALIAS ĐỒNG BỘ CAO CẤP --}}
                    <div class="relative">
                        <button @click="openDropdown = !openDropdown" @click.away="openDropdown = false" type="button" class="h-12 px-4 rounded-xl border border-[#8a4a21]/50 bg-gradient-to-b from-[#221c16] to-[#151515] hover:from-[#2a221b] hover:to-[#1a1a1a] flex items-center gap-3 cursor-pointer select-none transition-all shadow-md group">
                            <div class="h-7 w-7 rounded-lg bg-[#d99a32]/10 border border-[#d99a32]/20 flex items-center justify-center text-[#d99a32] font-black text-xs">
                                <i class="fa-solid fa-user-shield"></i>
                            </div>
                            <div class="text-left hidden sm:block">
                                <span class="block text-xs font-black text-white leading-none tracking-wide group-hover:text-[#d99a32] transition-colors">{{ $user->ho_ten }}</span>
                                <span class="block text-[9px] text-amber-500/70 font-bold uppercase tracking-wider mt-1">
                                    {{ $user->vai_tro === 'admin' ? 'Quản trị viên' : ($user->vai_tro === 'nhan_vien' ? 'Nhân viên rạp' : 'Thành viên Cine') }}
                                </span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-[10px] text-gray-500 group-hover:text-white transition duration-200" :class="openDropdown ? 'rotate-180' : ''"></i>
                        </button>

                        {{-- THỰC THI MENU CON (MENU DROPDOWN) SỬ DỤNG ALPINE.JS --}}
                        <div x-show="openDropdown" 
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-56 rounded-xl border border-white/10 bg-[#151515] p-2 shadow-2xl z-50 display-none" style="display: none;">
                            
                            {{-- LỰA CHỌN 1: Điều hướng ra Trang Chủ --}}
                            <a href="{{ route('home') }}" class="flex items-center gap-2.5 w-full px-3 py-2.5 text-sm font-semibold rounded-lg text-gray-300 hover:bg-white/5 hover:text-white transition text-decoration-none">
                                <i class="fa-solid fa-house text-xs text-gray-500 w-4"></i> Quay lại Trang chủ
                            </a>

                            {{-- LỰA CHỌN 2: BIỆN PHÁP ĐIỀU PHỐI QUYỀN LỰC QUAY VỀ HẠ TẦNG QUẢN TRỊ --}}
                            @if($user->hasRole('Quản trị viên') || $user->hasRole('Quản lý hệ thống') || $user->hasRole('Quản lý') || $user->hasRole('Quản lý phòng chiếu') || $user->hasRole('Nhân viên') || in_array($user->vai_tro, ['admin', 'quan_ly_he_thong', 'nhan_vien']))
                                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 w-full px-3 py-2.5 text-sm font-bold rounded-lg text-[#f4c56a] bg-[#d99a32]/10 hover:bg-[#d99a32]/20 hover:text-white transition text-decoration-none border border-[#d99a32]/10 my-1">
                                    <i class="fa-solid fa-gauge-high text-xs w-4"></i> Quay lại Quản trị
                                </a>
                            @endif

                            <div class="h-[1px] bg-white/5 my-1"></div>

                            {{-- LỰA CHỌN 3: ĐĂNG XUẤT TÀI KHOẢN --}}
                            <form method="POST" action="{{ route('logout') }}" class="m-0 p-0">
                                @csrf
                                <button type="submit" class="flex items-center gap-2.5 w-full px-3 py-2.5 text-sm font-semibold rounded-lg text-red-400 hover:bg-red-500/10 hover:text-red-300 transition border-0 bg-transparent text-left cursor-pointer">
                                    <i class="fa-solid fa-right-from-bracket text-xs w-4"></i> Đăng xuất tài khoản
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </nav>

        {{-- NỘI DUNG FORM CHÍNH CỦA TRANG PROFILE SẼ ĐƯỢC INJECT VÀO ĐÂY --}}
        <main class="py-6 animate-fade-in">
            {{ $slot }}
        </main>
    </div>
</body>
</html>
