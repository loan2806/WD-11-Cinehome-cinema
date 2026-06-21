<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'CineHome - Nền tảng quản trị tối cao')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Tailwind / Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    {{-- CSS riêng Admin --}}
    <link class="router-css" rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">

    {{-- ChartJS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* Hiệu ứng trượt đóng mở mượt mà độc lập */
        .sidebar-dropdown-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* 💡 ĐÃ SỬA: Dùng dấu > để bảo vệ, chỉ mở đúng menu con trực tiếp, không làm ảnh hưởng menu khác */
        .sidebar-dropdown-box.open > .sidebar-dropdown-content {
            max-height: 1000px;
            transition: max-height 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-dropdown-box .fa-chevron-down {
            transition: transform 0.25s ease;
        }

        /* 💡 ĐÃ SỬA: Chỉ xoay mũi tên của chính danh mục được nhấn chọn */
        .sidebar-dropdown-box.open > button .fa-chevron-down {
            transform: rotate(180deg);
        }
    </style>
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
            class="admin-scrollbar fixed left-0 top-0 z-[60] h-screen w-[285px] overflow-y-auto overflow-x-hidden border-r border-[#d99a32]/20 bg-gradient-to-b from-[#110702] to-[#200d05] transition-transform duration-300">

            {{-- LOGO --}}
            <div class="flex items-center gap-3.5 border-b border-white/5 px-5 py-6">
                <img src="{{ asset('assets/images/logo.png') }}" alt="CineHome Logo"
                    class="h-16 w-16 rounded-2xl bg-white object-contain p-1 shadow-lg shadow-[#d99a32]/10">

                <div>
                    <h3 class="m-0 text-2xl font-black tracking-wide text-white">
                        Cine<span class="text-[#d99a32]">Home</span>
                    </h3>
                    <p class="mt-0.5 text-xs font-black uppercase tracking-widest text-[#f4c56a]">
                        Root Controller
                    </p>
                </div>
            </div>

            {{-- MENU --}}
            <div class="mt-5 space-y-4 px-3 pb-8">

                {{-- DASHBOARD --}}
                <div>
                    <p class="mb-2 px-3 text-[11px] font-black uppercase tracking-widest text-[#d7a767]/50">Tổng quan hệ thống</p>
                    <a href="{{ route('admin.dashboard') }}"
                        class="{{ request()->routeIs('admin.dashboard') ? 'bg-[#d99a32] text-[#2b1208]' : 'text-gray-300 hover:bg-white/5' }} flex items-center gap-3.5 rounded-xl px-4 py-3 text-[16px] font-bold no-underline transition duration-200">
                        <i class="fa-solid fa-chart-line w-5 text-center text-xl {{ request()->routeIs('admin.dashboard') ? 'text-[#2b1208]' : 'text-[#d99a32]' }}"></i>
                        Dashboard Tổng
                    </a>
                </div>

                {{-- QUẢN TRỊ NỀN TẢNG ĐẶC QUYỀN --}}
                @if (auth()->user()->hasRole('Quản lý hệ thống') || auth()->user()->vai_tro === 'admin')
                    @php
                        $isNenTangActive = request()->routeIs('admin.cai-dat-thanh-toan.*');
                    @endphp
                    <div class="sidebar-dropdown-box {{ $isNenTangActive ? 'open' : '' }}">
                        <button type="button"
                            class="sidebar-dropdown-btn flex w-full items-center justify-between rounded-xl border border-[#d99a32]/30 bg-white/5 px-4 py-3 text-left text-[16px] font-black leading-none text-[#f4c56a] outline-none transition duration-200 hover:bg-white/10">
                            <span class="flex items-center gap-3.5">
                                <i class="fa-solid fa-server w-5 text-center text-xl text-[#d99a32]"></i>
                                <span>Quản trị nền tảng</span>
                            </span>
                            <i class="fa-solid fa-chevron-down mr-1 text-[11px] text-[#f4c56a]"></i>
                        </button>
                        <div class="sidebar-dropdown-content pl-5 mt-1 border-l-2 border-[#d99a32] ml-6 space-y-1">
                            {{-- Đã lược bỏ Cụm rạp chi nhánh theo mô hình 1 rạp duy nhất --}}
                            <a href="{{ route('admin.cai-dat-thanh-toan.edit') }}"
                                class="block py-2.5 pl-3 text-[15px] font-bold transition duration-200 hover:translate-x-1.5 {{ request()->routeIs('admin.cai-dat-thanh-toan.*') ? 'text-[#d99a32]' : 'text-gray-400 hover:text-white' }} no-underline">Cài
                                đặt cổng thanh toán</a>
                            <a href="#"
                                class="block py-2.5 pl-3 text-[15px] font-bold text-gray-400 hover:text-white transition duration-200 hover:translate-x-1.5 no-underline">Sao
                                lưu dữ liệu hạt nhân</a>
                            <a href="#"
                                class="block py-2.5 pl-3 text-[15px] font-bold text-gray-400 hover:text-white transition duration-200 hover:translate-x-1.5 no-underline">Log
                                hàng đợi & Monitor lỗi</a>
                        </div>
                    </div>
                @endif

                {{-- NHÓM DROPDOWN 1: QUẢN LÝ NỘI DUNG --}}
                @if (auth()->user()->can('quan_ly_phim_suat_chieu'))
                    @php
                        $isNoidungActive =
                            request()->routeIs('admin.phims.*') ||
                            request()->routeIs('admin.suat-chieus.*') ||
                            request()->routeIs('admin.the-loais.*') ||
                            request()->routeIs('admin.quoc-gias.*');
                    @endphp
                    <div class="sidebar-dropdown-box {{ $isNoidungActive ? 'open' : '' }}">
                        <button type="button"
                            class="sidebar-dropdown-btn w-full flex items-center justify-between px-4 py-3 rounded-xl text-[16px] font-bold text-gray-200 hover:bg-white/5 transition duration-200 border-0 bg-transparent text-left whitespace-nowrap leading-none outline-none">
                            <span class="flex items-center gap-3.5">
                                <i class="fa-solid fa-film w-5 text-center text-xl text-[#d99a32]"></i>
                                <span>Quản lý nội dung phim</span>
                            </span>
                            <i class="fa-solid fa-chevron-down mr-1 text-[11px] text-gray-500"></i>
                        </button>
                        <div class="sidebar-dropdown-content pl-5 mt-1 border-l-2 border-[#d99a32]/20 ml-6 space-y-1">
                            <a href="{{ route('admin.phims.index') }}" class="block py-2.5 pl-3 text-[15px] font-semibold transition duration-200 hover:translate-x-1.5 {{ request()->routeIs('admin.phims.*') ? 'text-[#d99a32]' : 'text-gray-400 hover:text-white' }} no-underline">Danh sách phim</a>
                            <a href="{{ route('admin.suat-chieus.index') }}" class="block py-2.5 pl-3 text-[15px] font-semibold transition duration-200 hover:translate-x-1.5 {{ request()->routeIs('admin.suat-chieus.*') ? 'text-[#d99a32]' : 'text-gray-400 hover:text-white' }} no-underline">Lịch suất chiếu</a>
                            <a href="{{ route('admin.the-loais.index') }}" class="block py-2.5 pl-3 text-[15px] font-semibold transition duration-200 hover:translate-x-1.5 {{ request()->routeIs('admin.the-loais.*') ? 'text-[#d99a32]' : 'text-gray-400 hover:text-white' }} no-underline">Thể loại phim</a>
                            <a href="{{ route('admin.quoc-gias.index') }}" class="block py-2.5 pl-3 text-[15px] font-semibold transition duration-200 hover:translate-x-1.5 {{ request()->routeIs('admin.quoc-gias.*') ? 'text-[#d99a32]' : 'text-gray-400 hover:text-white' }} no-underline">Quốc gia sản xuất</a>
                        </div>
                    </div>
                @endif

                {{-- NHÓM DROPDOWN 2: PHÒNG CHIẾU VÀ SƠ ĐỒ GHẾ --}}
                @if (auth()->user()->can('quan_ly_phong_ghe'))
                    @php
                        $isPhongGheActive = request()->routeIs('admin.phong-chieus.*')
                            || request()->routeIs('admin.loai-ghes.*');
                    @endphp
                    <div class="sidebar-dropdown-box {{ $isPhongGheActive ? 'open' : '' }}">
                        <button type="button"
                            class="sidebar-dropdown-btn w-full flex items-center justify-between px-4 py-3 rounded-xl text-[16px] font-bold text-gray-200 hover:bg-white/5 transition duration-200 border-0 bg-transparent text-left whitespace-nowrap leading-none outline-none">
                            <span class="flex items-center gap-3.5">
                                <i class="fa-solid fa-door-open w-5 text-center text-xl text-[#d99a32]"></i>
                                <span>Cơ sở vật chất phòng</span>
                            </span>
                            <i class="fa-solid fa-chevron-down mr-1 text-[11px] text-gray-500"></i>
                        </button>
                        <div class="sidebar-dropdown-content pl-5 mt-1 border-l-2 border-[#d99a32]/20 ml-6 space-y-1">
                            <a href="{{ route('admin.phong-chieus.index') }}" class="block py-2.5 pl-3 text-[15px] font-semibold transition duration-200 hover:translate-x-1.5 {{ request()->routeIs('admin.phong-chieus.*') ? 'text-[#d99a32]' : 'text-gray-400 hover:text-white' }} no-underline">Quản lý phòng chiếu</a>
                            <a href="{{ route('admin.loai-ghes.index') }}" class="block py-2.5 pl-3 text-[15px] font-semibold transition duration-200 hover:translate-x-1.5 {{ request()->routeIs('admin.loai-ghes.*') ? 'text-[#d99a32]' : 'text-gray-400 hover:text-white' }} no-underline">Danh mục loại ghế</a>
                        </div>
                    </div>
                @endif

                {{-- NHÓM DROPDOWN 3: VÉ, HÓA ĐƠN & DỊCH VỤ QUẦY --}}
                @if(auth()->user()->can('ban_ve_tai_quay') || auth()->user()->can('quan_ly_do_an_combo') || auth()->user()->can('soat_ve_vao_cua'))
                    @php
                        $isGiaoDichActive = request()->routeIs('admin.food-invoices.*') || request()->routeIs('admin.ve-xem-phims.*') || request()->routeIs('admin.soat-ve.*');
                    @endphp
                    <div class="sidebar-dropdown-box {{ $isGiaoDichActive ? 'open' : '' }}">
                        <button type="button"
                            class="sidebar-dropdown-btn flex w-full items-center justify-between rounded-xl border-0 bg-transparent px-4 py-3 text-left text-[16px] font-bold leading-none text-gray-200 outline-none transition duration-200 hover:bg-white/5">
                            <span class="flex items-center gap-3.5">
                                <i class="fa-solid fa-ticket w-5 text-center text-xl text-[#d99a32]"></i>
                                <span>Nghiệp vụ quầy vé</span>
                            </span>
                            <i class="fa-solid fa-chevron-down mr-1 text-[11px] text-gray-500"></i>
                        </button>
                        <div class="sidebar-dropdown-content pl-5 mt-1 border-l-2 border-[#d99a32]/20 ml-6 space-y-1">
                            @can('ban_ve_tai_quay')
                                <a href="{{ route('admin.ve-xem-phims.index') }}"
                                    class="block py-2.5 pl-3 text-[15px] font-semibold no-underline transition duration-200 hover:translate-x-1.5 {{ request()->routeIs('admin.ve-xem-phims.*') ? 'text-[#d99a32]' : 'text-gray-400 hover:text-white' }}">
                                    Quản lý kho dữ liệu vé
                                </a>

                                <a href="#" class="block py-2.5 pl-3 text-[15px] font-semibold text-gray-400 no-underline transition duration-200 hover:translate-x-1.5 hover:text-white">
                                    Bán vé trực tiếp rạp
                                </a>
                            @endcan
                            @can('soat_ve_vao_cua')
                                <a href="{{ route('admin.soat-ve.index') }}" class="block py-2.5 pl-3 text-[15px] font-semibold transition duration-200 hover:translate-x-1.5 {{ request()->routeIs('admin.soat-ve.*') ? 'text-[#d99a32]' : 'text-gray-400 hover:text-white' }} no-underline">Soát vé QR</a>
                            @endcan
                            @can('quan_ly_do_an_combo')
                                <a href="{{ route('admin.food-invoices.index') }}"
                                    class="block py-2.5 pl-3 text-[15px] font-semibold no-underline transition duration-200 hover:translate-x-1.5 {{ request()->routeIs('admin.food-invoices.*') ? 'text-[#d99a32]' : 'text-gray-400 hover:text-white' }}">
                                    Hóa đơn đồ ăn & Combo
                                </a>
                            @endcan
                            <a href="#" class="block py-2.5 pl-3 text-[14px] font-medium text-gray-500 hover:text-[#d99a32] transition duration-200 hover:translate-x-1.5 no-underline">+ Cấu hình Menu & Kho hàng</a>
                            <a href="#" class="block py-2.5 pl-3 text-[14px] font-medium text-gray-500 hover:text-[#d99a32] transition duration-200 hover:translate-x-1.5 no-underline">+ Khuyến mãi & Voucher</a>
                        </div>
                    </div>
                @endif

                {{-- NHÓM DROPDOWN 4: QUẢN LÝ TÀI KHOẢN & NHÂN LỰC --}}
                @if (auth()->user()->can('quan_ly_khach_hang') || auth()->user()->can('quan_ly_nhan_vien') || auth()->user()->can('phan_quyen_he_thong'))
                    @php
                        $isTaiKhoanActive = request()->routeIs('admin.nhanviens.*')
                            || request()->routeIs('admin.phan-quyen.*')
                            || request()->routeIs('admin.khach-hang.*')
                            || request()->routeIs('admin.thanh-vien.*');
                    @endphp
                    <div class="sidebar-dropdown-box {{ $isTaiKhoanActive ? 'open' : '' }}">
                        <button type="button"
                            class="sidebar-dropdown-btn w-full flex items-center justify-between px-4 py-3 rounded-xl text-[16px] font-bold text-gray-200 hover:bg-white/5 transition duration-200 border-0 bg-transparent text-left whitespace-nowrap leading-none outline-none">
                            <span class="flex items-center gap-3.5">
                                <i class="fa-solid fa-user-gear w-5 text-center text-xl text-[#d99a32]"></i>
                                <span>Tài khoản & Nhân lực</span>
                            </span>
                            <i class="fa-solid fa-chevron-down mr-1 text-[11px] text-gray-500"></i>
                        </button>
                        <div class="sidebar-dropdown-content pl-5 mt-1 border-l-2 border-[#d99a32]/20 ml-6 space-y-1">
                            @can('quan_ly_nhan_vien')
                                <a href="{{ route('admin.nhanviens.index') }}"
                                    class="block py-2.5 pl-3 text-[15px] font-semibold no-underline transition duration-200 hover:translate-x-1.5 {{ request()->routeIs('admin.nhanviens.*') ? 'text-[#d99a32]' : 'text-gray-400 hover:text-white' }}">
                                    Danh sách nhân viên
                                </a>
                            @endcan
                            @can('phan_quyen_he_thong')
                                <a href="{{ route('admin.phan-quyen.index') }}"
                                    class="block py-2.5 pl-3 text-[15px] font-semibold no-underline transition duration-200 hover:translate-x-1.5 {{ request()->routeIs('admin.phan-quyen.*') ? 'text-[#d99a32]' : 'text-gray-400 hover:text-white' }}">
                                    Ma trận phân quyền
                                </a>
                            @endcan
                            @can('quan_ly_khach_hang')
                                <a href="{{ route('admin.khach-hang.index') }}"
                                    class="block py-2.5 pl-3 text-[15px] font-semibold no-underline transition duration-200 hover:translate-x-1.5 {{ request()->routeIs('admin.khach-hang.*') ? 'text-[#d99a32]' : 'text-gray-400 hover:text-white' }}">
                                    Tài khoản khách hàng
                                </a>
                            @endcan

                            <a href="{{ route('admin.thanh-vien.index') }}"
                                class="block py-2.5 pl-3 text-[14px] font-medium no-underline transition duration-200 hover:translate-x-1.5 {{ request()->routeIs('admin.thanh-vien.*') ? 'text-[#d99a32]' : 'text-gray-500 hover:text-[#d99a32]' }}">
                                <i class="fa-solid fa-crown mr-2"></i>
                                Thẻ thành viên & Điểm
                            </a>
                        </div>
                    </div>
                @endif

                {{-- NHÓM DROPDOWN 5: BÁO CÁO & THỐNG KÊ --}}
                @if (auth()->user()->can('thong_ke_doanh_thu'))
                    @php
                        $isBaoCaoActive = request()->routeIs('admin.revenue-reports.*')
                            || request()->routeIs('admin.activity-logs.*');
                    @endphp
                    <div class="sidebar-dropdown-box {{ $isBaoCaoActive ? 'open' : '' }}">
                        <button type="button"
                            class="sidebar-dropdown-btn w-full flex items-center justify-between px-4 py-3 rounded-xl text-[16px] font-bold text-gray-200 hover:bg-white/5 transition duration-200 border-0 bg-transparent text-left whitespace-nowrap leading-none outline-none">
                            <span class="flex items-center gap-3.5">
                                <i class="fa-solid fa-chart-pie w-5 text-center text-xl text-[#d99a32]"></i>
                                <span>Báo cáo vận hành</span>
                            </span>
                            <i class="fa-solid fa-chevron-down mr-1 text-[11px] text-gray-500"></i>
                        </button>
                        <div class="sidebar-dropdown-content pl-5 mt-1 border-l-2 border-[#d99a32]/20 ml-6 space-y-1">
                            <a href="{{ route('admin.revenue-reports.index') }}" class="block py-2.5 pl-3 text-[15px] font-semibold transition duration-200 hover:translate-x-1.5 {{ request()->routeIs('admin.revenue-reports.*') ? 'text-[#d99a32]' : 'text-gray-400 hover:text-white' }} no-underline">Thống kê doanh thu</a>
                            <a href="{{ route('admin.activity-logs.index') }}" class="block py-2.5 pl-3 text-[15px] font-semibold transition duration-200 hover:translate-x-1.5 {{ request()->routeIs('admin.activity-logs.*') ? 'text-[#d99a32]' : 'text-gray-400 hover:text-white' }} no-underline">Nhật ký vết hệ thống</a>
                        </div>
                    </div>
                @endif

                {{-- NHÓM DROPDOWN 6: THIẾT LẬP HỆ THỐNG --}}
                @if (auth()->user()->can('quan_ly_cau_hinh_he_thong'))
                    @php
                        $isSystemActive = request()->routeIs('admin.notifications.*')
                            || request()->routeIs('admin.movie-reviews.*')
                            || request()->routeIs('admin.system-settings.*');
                    @endphp
                    <div class="sidebar-dropdown-box {{ $isSystemActive ? 'open' : '' }}">
                        <button type="button"
                            class="sidebar-dropdown-btn w-full flex items-center justify-between px-4 py-3 rounded-xl text-[16px] font-bold text-gray-200 hover:bg-white/5 transition duration-200 border-0 bg-transparent text-left whitespace-nowrap leading-none outline-none">
                            <span class="flex items-center gap-3.5">
                                <i class="fa-solid fa-gear w-5 text-center text-xl text-[#d99a32]"></i>
                                <span>Cài đặt tham số gốc</span>
                            </span>
                            <i class="fa-solid fa-chevron-down mr-1 text-[11px] text-gray-500"></i>
                        </button>
                        <div class="sidebar-dropdown-content pl-5 mt-1 border-l-2 border-[#d99a32]/20 ml-6 space-y-1">
                            <a href="{{ route('admin.notifications.index') }}" class="block py-2.5 pl-3 text-[15px] font-semibold transition duration-200 hover:translate-x-1.5 {{ request()->routeIs('admin.notifications.*') ? 'text-[#d99a32]' : 'text-gray-400 hover:text-white' }} no-underline">Thông báo đẩy</a>
                            <a href="{{ route('admin.movie-reviews.index') }}" class="block py-2.5 pl-3 text-[15px] font-semibold transition duration-200 hover:translate-x-1.5 {{ request()->routeIs('admin.movie-reviews.*') ? 'text-[#d99a32]' : 'text-gray-400 hover:text-white' }} no-underline">Kiểm duyệt đánh giá</a>
                            <a href="{{ route('admin.system-settings.index') }}" class="block py-2.5 pl-3 text-[15px] font-semibold transition duration-200 hover:translate-x-1.5 {{ request()->routeIs('admin.system-settings.*') ? 'text-[#d99a32]' : 'text-gray-400 hover:text-white' }} no-underline">Cấu hình tham số gốc</a>
                        </div>
                    </div>
                @endif

                {{-- VỀ TRANG CHỦ --}}
                <div class="border-t border-white/10 pt-3">
                    <a href="{{ route('home') }}"
                        class="flex items-center gap-3.5 rounded-xl px-4 py-3 text-[16px] font-bold text-gray-300 no-underline transition duration-200 hover:bg-white/5 hover:text-white">
                        <i class="fa-solid fa-house w-5 text-center text-xl text-[#d99a32]"></i>
                        <span>Xem trang chủ ngoài</span>
                    </a>
                </div>

            </div>
        </aside>

        {{-- MAIN CONTENT --}}
        <main id="adminMain" class="ml-[285px] min-h-screen overflow-x-hidden bg-[#080808]">

            {{-- TOPBAR --}}
            <header class="sticky top-0 z-50 border-b border-white/10 bg-[#101010]/95 backdrop-blur-xl">
                <div class="flex h-[76px] items-center justify-between gap-4 px-5">

                    {{-- LEFT --}}
                    <div class="flex min-w-0 items-center gap-4">
                        <button id="sidebarToggle" type="button"
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border-0 bg-white/10 text-white transition hover:bg-[#d99a32] hover:text-[#2b1208]">
                            <i class="fa-solid fa-bars text-lg"></i>
                        </button>

                        <div class="min-w-0">
                            <h1 class="m-0 truncate text-[22px] font-black leading-tight text-white">
                                @yield('page-title', 'Hệ thống đầu não')
                            </h1>
                            <p class="m-0 mt-1 max-w-[430px] truncate text-sm text-gray-400 xl:max-w-[560px]">
                                @yield('page-subtitle', 'Tổng bảng điều phối và giám sát toàn bộ tài nguyên nền tảng')
                            </p>
                        </div>
                    </div>

                    {{-- SEARCH --}}
                    <div class="hidden h-11 w-full max-w-[280px] items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 md:flex lg:max-w-[360px]">
                        <i class="fa-solid fa-magnifying-glass text-sm text-[#d99a32]"></i>
                        <input type="text" placeholder="Tìm nhanh chức năng hệ thống..."
                            class="h-full w-full border-0 bg-transparent text-sm text-white outline-none placeholder-gray-500">
                    </div>

                    {{-- ADMIN USER --}}
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <button type="button" id="bellBtn"
                                class="relative flex h-11 w-11 items-center justify-center rounded-2xl bg-white/10 hover:bg-white/15 transition">
                                <i class="fa-solid fa-bell text-white"></i>
                                @php
                                    $notificationCount = \App\Models\AdminNotification::where('da_doc', false)->count();
                                @endphp

                                @if ($notificationCount > 0)
                                    <span id="notifyBadge"
                                        class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] w-5 h-5 flex items-center justify-center rounded-full font-bold">
                                        {{ $notificationCount > 99 ? '99+' : $notificationCount }}
                                    </span>
                                @endif
                            </button>

                            {{-- NOTIFICATION DROPDOWN --}}
                            <div id="notifyBox"
                                class="hidden absolute right-0 mt-2 w-96 bg-[#151515] border border-white/10 rounded-xl shadow-xl z-50">
                                <div class="p-3 border-b border-white/10">
                                    <h3 class="text-white font-bold">Thông báo hệ thống</h3>
                                </div>

                                <div class="max-h-96 overflow-y-auto scrollbar scrollbar-w-2 scrollbar-track-zinc-950 scrollbar-thumb-zinc-800 hover:scrollbar-thumb-zinc-700">
                                    @forelse($adminNotifications as $item)
                                        <div class="p-3 border-b border-white/5 hover:bg-white/5">
                                            <div class="text-[#d99a32] font-semibold">{{ $item->tieu_de }}</div>
                                            <div class="text-sm text-gray-300 mt-1">{{ $item->noi_dung }}</div>
                                            <div class="text-xs text-gray-500 mt-2">{{ $item->created_at->diffForHumans() }}</div>
                                        </div>
                                    @empty
                                        <div class="p-4 text-center text-gray-500">Không có thông báo</div>
                                    @endforelse
                                </div>

                                <div class="p-2 border-t border-white/10 text-center">
                                    <a href="{{ route('admin.notifications.index') }}" class="text-[#d99a32] text-sm font-semibold no-underline">Xem tất cả</a>
                                </div>
                            </div>
                        </div>

                        @auth
                            <div class="relative" id="adminDropdownBox">
                                <button type="button" id="adminDropdownBtn"
                                    class="inline-flex items-center gap-3 rounded-2xl border-0 bg-white/10 px-4 py-2 transition hover:bg-white/15">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white shadow-md">
                                        <i class="fa-solid fa-user-shield"></i>
                                    </div>

                                    <div class="hidden text-left sm:block">
                                        <div class="max-w-[120px] truncate text-sm font-bold text-white">
                                            {{ Auth::user()->ho_ten }}
                                        </div>
                                        <div class="mt-0.5 text-xs font-semibold text-[#d99a32]">
                                            {{ Auth::user()->roles->pluck('name')->first() ?? 'Quản trị viên' }}
                                        </div>
                                    </div>

                                    <i class="fa-solid fa-chevron-down ml-1 text-[10px] text-gray-400"></i>
                                </button>

                                <div id="adminDropdownMenu"
                                    class="absolute right-0 top-[125%] z-[9999] hidden w-60 overflow-hidden rounded-xl border border-[#d99a32]/30 bg-[#151515]/95 shadow-2xl backdrop-blur-md">
                                    <div class="flex items-center gap-3 border-b border-white/10 bg-white/5 px-4 py-3">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white">
                                            <i class="fa-solid fa-user text-sm"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="truncate text-sm font-bold text-white">{{ Auth::user()->ho_ten }}</div>
                                            <div class="truncate text-[11px] text-gray-400">{{ Auth::user()->email }}</div>
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
                                            <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-bold text-red-400 transition hover:bg-red-500/15 border-0 bg-transparent text-left">
                                                <i class="fa-solid fa-right-from-bracket w-4 text-xs text-center"></i> Đăng xuất Hệ thống
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endauth
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- INTERACTION SCRIPT: CHỐNG NỔI BỌT VÀ ĐÓNG MỞ ĐỘC LẬP TUYỆT ĐỐI --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownBoxes = document.querySelectorAll('.sidebar-dropdown-box');

            dropdownBoxes.forEach(box => {
                const btn = box.querySelector('.sidebar-dropdown-btn');

                if(btn) {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation(); // 💡 CHỐNG NỔI BỌT: Không cho lan sự kiện sang các thẻ cha/con xung quanh
                        
                        box.classList.toggle('open');
                    });
                }
            });
        });
    </script>
    @stack('scripts')
</body>

</html>