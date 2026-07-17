```blade
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Staff - CineHome')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body class="overflow-x-hidden bg-[#080808] text-white">

@include('components.preloader')

<div class="min-h-screen overflow-x-hidden bg-[#080808] text-white">

    {{-- SIDEBAR --}}
    <aside id="staffSidebar"
        class="fixed left-0 top-0 z-[60] h-screen w-[270px] overflow-y-auto border-r border-[#d99a32]/20 bg-gradient-to-b from-[#1a0b04] to-[#2b1208] transition-transform duration-300">

        {{-- LOGO --}}
        <div class="flex items-center gap-3 px-5 py-6">
            <span class="cinehome-logo-sparkle flex h-[76px] w-[76px] items-center justify-center overflow-hidden rounded-3xl bg-transparent p-0">
                <img src="{{ asset('assets/images/LOGO copy.png') }}"
                    class="cinehome-logo-img h-full w-full object-contain"
                    alt="Logo">
            </span>

            <div>
                <h3 class="text-2xl font-black text-white">
                    Cine<span class="text-[#d99a32]">Home</span>
                </h3>
                <p class="text-sm font-bold text-[#f4c56a]">
                    Staff Panel
                </p>
            </div>
        </div>


        <div class="mt-4 px-3 pb-8">

            <p class="mb-3 px-3 text-xs font-black uppercase tracking-widest text-[#d7a767]">
                Tổng quan tác nghiệp
            </p>

            <nav class="space-y-2">

                <a href="{{ route('staff.dashboard') }}"
                    class="flex items-center gap-3 rounded-2xl px-4 py-3 font-bold transition {{ request()->routeIs('staff.dashboard') ? 'bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white shadow-lg' : 'text-gray-300 hover:bg-white/10 no-underline' }}">

                    <i class="fa-solid fa-chart-line w-5"></i>
                    Dashboard
                </a>


                @can('soat_ve_vao_cua')

                <a href="{{ route('staff.soat-ve.index') }}"
                    class="flex items-center gap-3 rounded-2xl px-4 py-3 font-bold transition {{ request()->routeIs('staff.soat-ve.*') ? 'bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white shadow-lg' : 'text-gray-300 hover:bg-white/10 no-underline' }}">

                    <i class="fa-solid fa-qrcode w-5"></i>
                    Soát vé QR
                </a>

                @endcan



                @can('ban_ve_tai_quay')

                <a href="{{ route('staff.ban-ve.index') }}"
                    class="flex items-center gap-3 rounded-2xl px-4 py-3 font-bold transition {{ request()->routeIs('staff.ban-ve.*') ? 'bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white shadow-lg' : 'text-gray-300 hover:bg-white/10 no-underline' }}">

                    <i class="fa-solid fa-ticket w-5"></i>
                    Bán vé tại quầy
                </a>


                <a href="{{ route('staff.lich-su-ve.index') }}"
                    class="flex items-center gap-3 rounded-2xl px-4 py-3 font-bold transition {{ request()->routeIs('staff.lich-su-ve.*') ? 'bg-gradient-to-r from-[#8a4a21] to-[#d99a32] text-white shadow-lg' : 'text-gray-300 hover:bg-white/10 no-underline' }}">

                    <i class="fa-solid fa-clock-rotate-left w-5"></i>
                    Lịch sử vé
                </a>

                @endcan



                @can('quan_ly_phim_suat_chieu')

                <a href="{{ route('admin.phims.index') }}"
                    class="flex items-center gap-3 rounded-2xl px-4 py-3 font-bold text-yellow-400/90 transition hover:bg-white/10 no-underline">

                    <i class="fa-solid fa-film w-5"></i>
                    Quản lý phim
                </a>

                @endcan



                @can('quan_ly_phong_ghe')

                <a href="{{ route('admin.phong-chieus.index') }}"
                    class="flex items-center gap-3 rounded-2xl px-4 py-3 font-bold text-yellow-400/90 transition hover:bg-white/10 no-underline">

                    <i class="fa-solid fa-building w-5"></i>
                    Sơ đồ phòng chiếu
                </a>

                @endcan



                @can('thong_ke_doanh_thu')

                <a href="{{ route('admin.revenue-reports.index') }}"
                    class="flex items-center gap-3 rounded-2xl px-4 py-3 font-bold text-yellow-400/90 transition hover:bg-white/10 no-underline">

                    <i class="fa-solid fa-sack-dollar w-5"></i>
                    Xem doanh thu tổng
                </a>

                @endcan

            </nav>


            <p class="mb-3 mt-7 px-3 text-xs font-black uppercase tracking-widest text-[#d7a767]">
                Hệ thống
            </p>


            <nav class="space-y-2">

                <a href="{{ route('home') }}"
                    class="flex items-center gap-3 rounded-2xl px-4 py-3 font-bold text-gray-300 transition hover:bg-white/10 no-underline">

                    <i class="fa-solid fa-house w-5"></i>
                    Trang chủ công cộng
                </a>

            </nav>

        </div>

    </aside>
```blade
    {{-- MAIN CONTENT --}}
    <main id="staffMain" class="min-h-screen bg-[#080808] ml-[270px]">

        {{-- HEADER --}}
        <header class="sticky top-0 z-50 border-b border-white/10 bg-[#101010]/95 backdrop-blur-xl">

            <div class="flex h-[76px] items-center justify-between px-6">

                <div>
                    <h1 class="m-0 text-[24px] font-black text-white">
                        @yield('page-title', 'Staff Dashboard')
                    </h1>

                    <p class="m-0 mt-1 text-sm text-gray-400">
                        Hệ thống quản lý bán vé và tác nghiệp tại quầy rạp phim
                    </p>
                </div>


                <div class="flex items-center gap-3">


                    {{-- SEARCH --}}
                    <div class="hidden h-11 w-[300px] items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 lg:flex">

                        <i class="fa-solid fa-magnifying-glass text-[#d99a32]"></i>

                        <input type="text"
                            placeholder="Tìm nhanh hóa đơn, mã vé..."
                            class="h-full w-full bg-transparent text-sm text-white outline-none">

                    </div>



                    {{-- USER --}}
                    <div class="flex items-center gap-3 rounded-2xl bg-white/10 px-4 py-2">

                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-r from-[#8a4a21] to-[#d99a32]">

                            <i class="fa-solid fa-user-tie text-white"></i>

                        </div>


                        <div>

                            <div class="text-sm font-bold leading-tight text-white">
                                {{ Auth::user()->ho_ten }}
                            </div>

                            <div class="mt-1 text-xs text-gray-400">
                                Nhân viên quầy
                            </div>

                        </div>

                    </div>



                    {{-- LOGOUT --}}
                    <form action="{{ route('logout') }}" method="POST" class="m-0">

                        @csrf

                        <button
                            class="border-0 rounded-2xl bg-red-500/20 px-4 py-2 font-bold text-red-400 transition hover:bg-red-500/30">

                            Đăng xuất

                        </button>

                    </form>


                </div>

            </div>

        </header>



        {{-- PAGE CONTENT --}}
        <section class="px-6 py-6">

            @yield('content')

        </section>


    </main>


</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@yield('scripts')


</body>

</html>
