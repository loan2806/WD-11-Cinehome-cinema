@extends('layouts.admin')

@section('title', 'Trung tâm Thùng Rác - CineHome')
@section('page-title', 'Quản lý thùng rác')
@section('page-subtitle', 'Theo dõi, phục hồi dữ liệu hoặc xóa vĩnh viễn các bản ghi rác trong hệ thống CineHome.')

@section('content')

<div class="space-y-6">

    {{-- =========================================================
        1. HERO HEADER
    ========================================================== --}}
    <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-[#121214] p-6 md:p-8 shadow-2xl">

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">

            <div class="space-y-3">

                <span class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-widest text-[#d99a32]">
                    <i class="fa-solid fa-shield-cat text-sm"></i>
                    QUẢN TRỊ HỆ THỐNG CINEHOME
                </span>

                <h1 class="text-3xl md:text-4xl font-black tracking-tight text-white">
                    Thùng rác hệ thống
                    <span class="text-red-500">CineHome</span>
                </h1>

                <p class="text-sm text-gray-300 max-w-2xl leading-relaxed">
                    Quản lý toàn bộ dữ liệu đã xóa mềm. Bạn có thể khôi phục từng phần,
                    khôi phục hàng loạt hoặc dọn dẹp dữ liệu vĩnh viễn.
                </p>

                <div class="flex flex-wrap items-center gap-3 pt-1">

                    <span class="inline-flex items-center gap-2 rounded-full bg-white/5 border border-white/10 px-3.5 py-1.5 text-xs font-bold text-gray-200">
                        <i class="fa-solid fa-database text-amber-400"></i>

                        {{ number_format($totalTrash ?? array_sum($stats ?? [])) }}

                        bản ghi trong rác
                    </span>

                    <span class="inline-flex items-center gap-2 rounded-full bg-white/5 border border-white/10 px-3.5 py-1.5 text-xs font-bold text-gray-200">
                        <i class="fa-solid fa-layer-group text-red-400"></i>
                        5 danh mục phân loại
                    </span>

                </div>

            </div>


            {{-- ACTION --}}
            @if(($stats[$tab] ?? 0) > 0)

            <div class="flex flex-wrap items-center gap-3 shrink-0">

                {{-- KHÔI PHỤC TẤT CẢ --}}
                <form action="{{ route('admin.thung-rac.restore-all', $tab) }}"
                    method="POST"
                    onsubmit="return confirm('Bạn có chắc chắn muốn KHÔI PHỤC TẤT CẢ {{ $stats[$tab] }} bản ghi trong danh mục này?')">

                    @csrf
                    @method('PATCH')

                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-[#222328] border border-white/15 px-5 py-3 text-xs font-bold text-white hover:bg-white/10 transition shadow-xl">

                        <i class="fa-solid fa-rotate-left text-emerald-400 text-sm"></i>

                        Khôi phục tất cả

                    </button>

                </form>


                {{-- DỌN SẠCH --}}
                <form action="{{ route('admin.thung-rac.empty', $tab) }}"
                    method="POST"
                    onsubmit="return confirm('CẢNH BÁO NGHIÊM TRỌNG: Thao tác này sẽ XÓA VĨNH VIỄN toàn bộ {{ $stats[$tab] }} bản ghi trong mục này!')">

                    @csrf
                    @method('DELETE')

                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-red-600 to-red-500 hover:from-red-500 hover:to-red-400 px-5 py-3 text-xs font-bold text-white transition shadow-xl shadow-red-600/30">

                        <i class="fa-solid fa-trash-can text-sm"></i>

                        Dọn sạch mục này

                    </button>

                </form>

            </div>

            @endif

        </div>

    </div>


    {{-- =========================================================
        2. STAT CARDS
    ========================================================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">

        {{-- PHIM --}}
        <a href="{{ route('admin.thung-rac.index', array_merge(request()->query(), ['tab' => 'phim'])) }}"
            class="group relative flex items-center gap-3.5 rounded-2xl border p-4 transition-all duration-200 no-underline block overflow-hidden
           {{ $tab === 'phim'
                ? 'border-red-500 bg-red-500/10 shadow-[0_0_20px_rgba(239,68,68,0.2)]'
                : 'border-white/10 bg-[#121214] hover:border-white/20 hover:bg-white/[0.03]' }}">

            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-500 text-white font-bold text-lg shadow-lg shadow-red-500/30">
                <i class="fa-solid fa-film"></i>
            </div>

            <div class="min-w-0 flex-1">

                <span class="text-[11px] font-black uppercase tracking-wider text-gray-400 block truncate">
                    PHIM ĐÃ XÓA
                </span>

                <span class="text-2xl font-black text-white block mt-0.5 leading-none">
                    {{ number_format($stats['phim'] ?? 0) }}
                </span>

            </div>

        </a>


        {{-- SUẤT CHIẾU --}}
        <a href="{{ route('admin.thung-rac.index', array_merge(request()->query(), ['tab' => 'suat_chieu'])) }}"
            class="group relative flex items-center gap-3.5 rounded-2xl border p-4 transition-all duration-200 no-underline block overflow-hidden
           {{ $tab === 'suat_chieu'
                ? 'border-emerald-500 bg-emerald-500/10 shadow-[0_0_20px_rgba(16,185,129,0.2)]'
                : 'border-white/10 bg-[#121214] hover:border-white/20 hover:bg-white/[0.03]' }}">

            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-500 text-white font-bold text-lg shadow-lg shadow-emerald-500/30">
                <i class="fa-solid fa-calendar-days"></i>
            </div>

            <div class="min-w-0 flex-1">

                <span class="text-[11px] font-black uppercase tracking-wider text-gray-400 block truncate">
                    SUẤT CHIẾU
                </span>

                <span class="text-2xl font-black text-white block mt-0.5 leading-none">
                    {{ number_format($stats['suat_chieu'] ?? 0) }}
                </span>

            </div>

        </a>


        {{-- KHÁCH HÀNG --}}
        <a href="{{ route('admin.thung-rac.index', array_merge(request()->query(), ['tab' => 'khach_hang'])) }}"
            class="group relative flex items-center gap-3.5 rounded-2xl border p-4 transition-all duration-200 no-underline block overflow-hidden
           {{ $tab === 'khach_hang'
                ? 'border-blue-500 bg-blue-500/10 shadow-[0_0_20px_rgba(59,130,246,0.2)]'
                : 'border-white/10 bg-[#121214] hover:border-white/20 hover:bg-white/[0.03]' }}">

            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-500 text-white font-bold text-lg shadow-lg shadow-blue-500/30">
                <i class="fa-solid fa-users"></i>
            </div>

            <div class="min-w-0 flex-1">

                <span class="text-[11px] font-black uppercase tracking-wider text-gray-400 block truncate">
                    KHÁCH HÀNG
                </span>

                <span class="text-2xl font-black text-white block mt-0.5 leading-none">
                    {{ number_format($stats['khach_hang'] ?? 0) }}
                </span>

            </div>

        </a>


        {{-- NHÂN VIÊN --}}
        <a href="{{ route('admin.thung-rac.index', array_merge(request()->query(), ['tab' => 'nhan_vien'])) }}"
            class="group relative flex items-center gap-3.5 rounded-2xl border p-4 transition-all duration-200 no-underline block overflow-hidden
           {{ $tab === 'nhan_vien'
                ? 'border-amber-500 bg-amber-500/10 shadow-[0_0_20px_rgba(245,158,11,0.2)]'
                : 'border-white/10 bg-[#121214] hover:border-white/20 hover:bg-white/[0.03]' }}">

            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white font-bold text-lg shadow-lg shadow-amber-500/30">
                <i class="fa-solid fa-user-tie"></i>
            </div>

            <div class="min-w-0 flex-1">

                <span class="text-[11px] font-black uppercase tracking-wider text-gray-400 block truncate">
                    NHÂN VIÊN
                </span>

                <span class="text-2xl font-black text-white block mt-0.5 leading-none">
                    {{ number_format($stats['nhan_vien'] ?? 0) }}
                </span>

            </div>

        </a>


        {{-- THÔNG BÁO --}}
        <a href="{{ route('admin.thung-rac.index', array_merge(request()->query(), ['tab' => 'thong_bao'])) }}"
            class="group relative flex items-center gap-3.5 rounded-2xl border p-4 transition-all duration-200 no-underline block overflow-hidden
           {{ $tab === 'thong_bao'
                ? 'border-purple-500 bg-purple-500/10 shadow-[0_0_20px_rgba(168,85,247,0.2)]'
                : 'border-white/10 bg-[#121214] hover:border-white/20 hover:bg-white/[0.03]' }}">

            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-purple-500 text-white font-bold text-lg shadow-lg shadow-purple-500/30">
                <i class="fa-solid fa-bell"></i>
            </div>

            <div class="min-w-0 flex-1">

                <span class="text-[11px] font-black uppercase tracking-wider text-gray-400 block truncate">
                    THÔNG BÁO PUSH
                </span>

                <span class="text-2xl font-black text-white block mt-0.5 leading-none">
                    {{ number_format($stats['thong_bao'] ?? 0) }}
                </span>

            </div>

        </a>

    </div>


    {{-- =========================================================
        3. MAIN CONTENT
    ========================================================== --}}
    <div class="rounded-2xl border border-white/10 bg-[#121214] p-6 space-y-6 shadow-2xl">

        {{-- TITLE --}}
        <div>

            <span class="text-xs font-black uppercase tracking-widest text-[#d99a32]">
                DANH SÁCH BẢN GHI RÁC
            </span>

            <h2 class="text-2xl font-black text-white mt-1">

                {{ match($tab) {

                    'phim' => 'Kho Phim Đã Xóa',

                    'suat_chieu' => 'Lịch Suất Chiếu Đã Xóa',

                    'khach_hang' => 'Tài Khoản Khách Hàng Đã Xóa',

                    'nhan_vien' => 'Tài Khoản Nhân Viên Đã Xóa',

                    'thong_bao' => 'Thông Báo Push Đã Xóa',

                    default => 'Thùng Rác Hệ Thống'

                } }}

            </h2>

            <p class="text-xs text-gray-400 mt-1 font-medium">

                Đang hiển thị
                {{ $items->count() }}

                @if($items instanceof \Illuminate\Pagination\LengthAwarePaginator)

                / {{ $items->total() }}

                @endif

                bản ghi phù hợp theo bộ lọc hiện tại.

            </p>

        </div>


        {{-- =====================================================
            FILTER BAR
        ====================================================== --}}
        <form action="{{ route('admin.thung-rac.index') }}"
            method="GET"
            class="flex flex-col md:flex-row gap-3 bg-[#18181c] p-3.5 border-t border-b border-white/10">

            <input type="hidden"
                name="tab"
                value="{{ $tab }}">


            {{-- TÌM KIẾM --}}
            <div class="relative flex-1 min-w-0">

                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-red-500"></i>

                <input type="text"
                    name="tim_kiem"
                    value="{{ request('tim_kiem') ?? request('search') }}"
                    placeholder="Tìm theo tên, email hoặc số điện thoại..."
                    class="h-12 w-full rounded-2xl border border-white/10 bg-[#202126] pl-11 pr-4 text-sm text-white placeholder-gray-500 focus:border-red-500 focus:outline-none transition font-medium">

            </div>


            {{-- TỪ NGÀY --}}
            <div class="w-full md:w-[240px] shrink-0">

                <input type="date"
                    id="filter_tu_ngay"
                    name="tu_ngay"
                    value="{{ request('tu_ngay') }}"
                    class="h-12 w-full rounded-2xl border border-white/10 bg-[#202126] px-4 text-sm text-white font-medium focus:border-red-500 focus:outline-none transition [color-scheme:dark]">

            </div>


            {{-- ĐẾN NGÀY --}}
            <div class="w-full md:w-[165px] shrink-0">

                <input type="date"
                    id="filter_den_ngay"
                    name="den_ngay"
                    value="{{ request('den_ngay') }}"
                    class="h-12 w-full rounded-2xl border border-white/10 bg-[#202126] px-4 text-sm text-white font-medium focus:border-red-500 focus:outline-none transition [color-scheme:dark]">

            </div>


            {{-- LỌC --}}
            <button type="submit"
                class="h-12 shrink-0 inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-red-600 to-red-500 hover:from-red-500 hover:to-red-400 px-6 text-sm font-black text-white transition shadow-lg shadow-red-600/30">

                <i class="fa-solid fa-filter text-sm"></i>

                Lọc

            </button>


            {{-- RESET --}}
            @if(request()->hasAny([
            'tim_kiem',
            'search',
            'tu_ngay',
            'den_ngay'
            ]))

            <a href="{{ route('admin.thung-rac.index', ['tab' => $tab]) }}"
                class="h-12 w-12 shrink-0 inline-flex items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-gray-300 hover:bg-white/10 hover:text-white transition"
                title="Đặt lại bộ lọc">

                <i class="fa-solid fa-rotate-left text-xs"></i>

            </a>

            @endif

        </form>


        {{-- =====================================================
            DATA TABLE
        ====================================================== --}}
        <div class="overflow-hidden rounded-2xl border border-white/10 bg-[#121214]">

            <div class="overflow-x-auto">

                {{-- =================================================
                    KHÁCH HÀNG
                ================================================= --}}
                @if($tab === 'khach_hang')

                <table class="w-full text-left text-sm text-gray-300">

                    <thead class="bg-[#18181c] text-xs font-black uppercase tracking-wider text-gray-400 border-b border-white/10">

                        <tr>

                            <th class="px-4 py-4">
                                Mã
                            </th>

                            <th class="px-4 py-4">
                                Khách hàng
                            </th>

                            <th class="px-4 py-4">
                                Email
                            </th>

                            <th class="px-4 py-4">
                                Số điện thoại
                            </th>

                            <th class="px-4 py-4">
                                Trạng thái
                            </th>

                            <th class="px-4 py-4">
                                Ngày xóa
                            </th>

                            <th class="px-4 py-4 text-right">
                                Thao tác
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-white/5">

                        @forelse($items as $khachHang)

                        <tr class="hover:bg-white/[0.03] transition duration-150">

                            {{-- MÃ --}}
                            <td class="px-4 py-5">

                                <span class="inline-flex items-center justify-center rounded-full bg-white/5 border border-white/10 px-3 py-1.5 text-xs font-bold text-gray-300">

                                    #{{ $khachHang->id }}

                                </span>

                            </td>


                            {{-- KHÁCH HÀNG --}}
                            <td class="px-4 py-5">

                                <div class="flex items-center gap-3">

                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-orange-500 to-red-500 text-white shadow-lg">

                                        <i class="fa-solid fa-user text-base"></i>

                                    </div>


                                    <div class="min-w-0">

                                        <strong class="block text-sm font-black text-white">

                                            {{ $khachHang->ho_ten ?? 'Chưa cập nhật' }}

                                        </strong>

                                        <small class="mt-1 flex items-center gap-1.5 text-xs font-bold text-amber-400">

                                            <i class="fa-solid fa-user text-[10px]"></i>

                                            Khách hàng

                                        </small>

                                    </div>

                                </div>

                            </td>


                            {{-- EMAIL --}}
                            <td class="px-4 py-5">

                                <span class="text-sm font-semibold text-gray-300">

                                    {{ $khachHang->email ?? 'Chưa có email' }}

                                </span>

                            </td>


                            {{-- SỐ ĐIỆN THOẠI --}}
                            <td class="px-4 py-5">

                                <span class="inline-flex items-center gap-2 text-sm font-semibold text-gray-300">

                                    <i class="fa-solid fa-phone text-gray-400"></i>

                                    {{ $khachHang->so_dien_thoai ?: 'Chưa có SĐT' }}

                                </span>

                            </td>


                            {{-- TRẠNG THÁI --}}
                            <td class="px-4 py-5">

                                <span class="inline-flex items-center gap-2 rounded-full bg-red-500/10 border border-red-500/30 px-3 py-1.5 text-xs font-bold text-red-400">

                                    <i class="fa-solid fa-trash-can text-[11px]"></i>

                                    Xóa mềm

                                </span>

                            </td>


                            {{-- NGÀY XÓA --}}
                            <td class="px-4 py-5">

                                <span class="inline-flex items-center gap-2 rounded-full bg-white/5 border border-white/10 px-3 py-1.5 text-xs font-bold text-gray-300">

                                    <i class="fa-regular fa-calendar-xmark text-[11px]"></i>

                                    {{ optional($khachHang->deleted_at)->format('d/m/Y H:i') }}

                                </span>

                            </td>


                            {{-- THAO TÁC --}}
                            <td class="px-4 py-5">

                                <div class="flex items-center justify-end gap-2">

                                    {{-- KHÔI PHỤC --}}
                                    <form method="POST"
                                        action="{{ route('admin.khach-hang.restore', $khachHang->id) }}"
                                        onsubmit="return confirm('Bạn có chắc muốn khôi phục khách hàng này?');">

                                        @csrf
                                        @method('PATCH')

                                        <button type="submit"
                                            class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-400 hover:bg-emerald-500 hover:text-white transition shadow-md"
                                            title="Khôi phục">

                                            <i class="fa-solid fa-rotate-left text-sm"></i>

                                        </button>

                                    </form>


                                    {{-- XÓA VĨNH VIỄN --}}
                                    <form method="POST"
                                        action="{{ route('admin.khach-hang.force-delete', $khachHang->id) }}"
                                        onsubmit="return confirm('Bạn có chắc muốn XÓA VĨNH VIỄN khách hàng này? Dữ liệu sẽ không thể khôi phục.');">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                            class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-red-500/15 text-red-400 hover:bg-red-600 hover:text-white transition shadow-md"
                                            title="Xóa vĩnh viễn">

                                            <i class="fa-solid fa-trash-can text-sm"></i>

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td colspan="7" class="py-16 text-center">

                                <div class="flex flex-col items-center justify-center">

                                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/5 border border-white/10 mb-4">

                                        <i class="fa-solid fa-trash-can text-2xl text-gray-500"></i>

                                    </div>

                                    <h3 class="text-lg font-black text-gray-300">
                                        Thùng rác đang trống
                                    </h3>

                                    <p class="text-sm text-gray-500 mt-1">
                                        Hiện chưa có khách hàng nào bị xóa.
                                    </p>

                                    <a href="{{ route('admin.khach-hang.index') }}"
                                        class="mt-5 inline-flex items-center gap-2 rounded-xl bg-[#222328] border border-white/10 px-5 py-3 text-xs font-bold text-white hover:bg-white/10 transition">

                                        <i class="fa-solid fa-arrow-left"></i>

                                        Quay lại danh sách

                                    </a>

                                </div>

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>


                {{-- =================================================
                    CÁC TAB KHÁC
                ================================================= --}}
                @else

                <table class="w-full text-left text-sm text-gray-300">

                    <thead class="bg-[#18181c] text-xs font-black uppercase tracking-wider text-gray-400 border-b border-white/10">

                        <tr>

                            <th class="px-6 py-4">
                                Mã
                            </th>

                            <th class="px-6 py-4">
                                Tên / Nhận diện
                            </th>

                            <th class="px-6 py-4">
                                Thông tin chi tiết
                            </th>

                            <th class="px-6 py-4">
                                Thời gian xóa
                            </th>

                            <th class="px-6 py-4 text-right">
                                Thao tác
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-white/5">

                        @forelse($items as $item)

                        @php

                        $rawPoster = null;

                        if ($tab === 'phim') {

                        $rawPoster =
                        $item->poster
                        ?? $item->anh_poster
                        ?? $item->duong_dan_anh
                        ?? $item->hinh_anh
                        ?? $item->anh_bia
                        ?? null;

                        }

                        elseif ($tab === 'suat_chieu' && isset($item->phim)) {

                        $rawPoster =
                        $item->phim->poster
                        ?? $item->phim->anh_poster
                        ?? $item->phim->duong_dan_anh
                        ?? $item->phim->hinh_anh
                        ?? $item->phim->anh_bia
                        ?? null;

                        }


                        $imageUrl = null;

                        if ($rawPoster) {

                        if (
                        \Illuminate\Support\Str::startsWith(
                        $rawPoster,
                        ['http://', 'https://']
                        )
                        ) {

                        $imageUrl = $rawPoster;

                        } else {

                        $cleanPath = ltrim(
                        preg_replace(
                        '#^storage/#',
                        '',
                        $rawPoster
                        ),
                        '/'
                        );

                        $imageUrl = asset(
                        'storage/' . $cleanPath
                        );

                        }

                        }

                        @endphp


                        <tr class="hover:bg-white/[0.03] transition duration-150">

                            {{-- ID --}}
                            <td class="px-6 py-4 font-mono text-xs text-gray-400">

                                <span class="inline-block rounded-lg bg-white/5 px-2.5 py-1 font-bold border border-white/10">

                                    #{{ $item->id }}

                                </span>

                            </td>


                            {{-- NAME --}}
                            <td class="px-6 py-4">

                                <div class="flex items-center gap-3.5">

                                    <div class="relative h-12 w-9 shrink-0 overflow-hidden rounded-lg border border-white/15 bg-[#1a1a1e] shadow-md flex items-center justify-center">

                                        @if($imageUrl)

                                        <img src="{{ $imageUrl }}"
                                            alt="Poster"
                                            class="h-full w-full object-cover"
                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">

                                        <div class="hidden h-full w-full items-center justify-center bg-gradient-to-br from-red-500/20 to-amber-500/20 text-red-500 font-bold text-sm">

                                            <i class="fa-solid fa-film"></i>

                                        </div>

                                        @else

                                        <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-red-500/20 to-amber-500/20 text-red-500 font-bold text-sm">

                                            <i class="fa-solid
                                                        {{ match($tab) {

                                                            'phim' => 'fa-film',

                                                            'suat_chieu' => 'fa-calendar-days',

                                                            'nhan_vien' => 'fa-user-tie',

                                                            'thong_bao' => 'fa-bell',

                                                            default => 'fa-trash'

                                                        } }}"></i>

                                        </div>

                                        @endif

                                    </div>


                                    <div>

                                        <strong class="block font-bold text-white text-base">

                                            @if($tab === 'suat_chieu')

                                            {{ $item->phim->ten_phim ?? 'Suất chiếu #' . $item->id }}

                                            @else

                                            {{ $item->ten_phim
                                                            ?? $item->ho_ten
                                                            ?? $item->name
                                                            ?? $item->tieu_de
                                                            ?? 'N/A' }}

                                            @endif

                                        </strong>


                                        <small class="text-xs text-gray-400 font-mono block mt-0.5">

                                            {{ $item->email
                                                        ?? $item->so_dien_thoai
                                                        ?? 'Bản ghi hệ thống' }}

                                        </small>

                                    </div>

                                </div>

                            </td>


                            {{-- DETAILS --}}
                            <td class="px-6 py-4">

                                @if($tab === 'phim')

                                <span class="text-[#d99a32] font-bold text-xs">

                                    <i class="fa-regular fa-clock mr-1"></i>

                                    {{ $item->thoi_luong ?? 0 }} phút

                                </span>


                                @elseif($tab === 'suat_chieu')

                                <span class="text-gray-300 text-xs">

                                    Phòng:

                                    <strong class="text-white font-bold">

                                        {{ $item->phongChieu->ten_phong ?? 'N/A' }}

                                    </strong>

                                </span>


                                @elseif($tab === 'nhan_vien')

                                @php
                                $vaiTro = $item->vai_tro ?? 'nhan_vien';

                                $vaiTroLabel = match ($vaiTro) {
                                'admin' => 'Quản trị viên',
                                'nhan_vien' => 'Nhân viên',
                                'khach_hang' => 'Khách hàng',
                                default => ucfirst(str_replace('_', ' ', $vaiTro)),
                                };
                                @endphp

                                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/10 px-3 py-1 text-xs font-bold text-amber-400 border border-amber-500/20">

                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>

                                    {{ $vaiTroLabel }}

                                </span>

                                @endif

                            </td>


                            {{-- DELETED AT --}}
                            <td class="px-6 py-4">

                                <span class="inline-flex items-center gap-1.5 text-xs text-red-400 font-semibold">

                                    <i class="fa-regular fa-clock text-[10px]"></i>

                                    {{ $item->deleted_at
                                                ? \Carbon\Carbon::parse($item->deleted_at)->format('H:i - d/m/Y')
                                                : '—' }}

                                </span>

                            </td>


                            {{-- ACTION --}}
                            <td class="px-6 py-4 text-right">

                                <div class="flex items-center justify-end gap-2">

                                    @php

                                    $restoreRoute = match($tab) {

                                    'phim' =>
                                    route(
                                    'admin.phims.restore',
                                    $item->id
                                    ),

                                    'suat_chieu' =>
                                    route(
                                    'admin.suat-chieus.restore',
                                    $item->id
                                    ),

                                    'nhan_vien' =>
                                    route(
                                    'admin.nhanviens.restore',
                                    $item->id
                                    ),

                                    'thong_bao' =>
                                    route(
                                    'admin.thong-bao-push.restore',
                                    $item->id
                                    ),

                                    default => '#'

                                    };


                                    $forceRoute = match($tab) {

                                    'phim' =>
                                    route(
                                    'admin.phims.force-delete',
                                    $item->id
                                    ),

                                    'suat_chieu' =>
                                    route(
                                    'admin.suat-chieus.force-delete',
                                    $item->id
                                    ),

                                    'nhan_vien' =>
                                    route(
                                    'admin.nhanviens.forceDelete',
                                    $item->id
                                    ),

                                    'thong_bao' =>
                                    route(
                                    'admin.thong-bao-push.force-delete',
                                    $item->id
                                    ),

                                    default => '#'

                                    };

                                    @endphp


                                    {{-- KHÔI PHỤC --}}
                                    <form action="{{ $restoreRoute }}"
                                        method="POST">

                                        @csrf

                                        <button type="submit"
                                            onclick="return confirm('Khôi phục bản ghi này về trạng thái hoạt động?')"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-blue-500/15 text-blue-400 hover:bg-blue-500 hover:text-white transition shadow-md"
                                            title="Khôi phục dữ liệu">

                                            <i class="fa-solid fa-rotate-left text-xs"></i>

                                        </button>

                                    </form>


                                    {{-- XÓA VĨNH VIỄN --}}
                                    <form action="{{ $forceRoute }}"
                                        method="POST">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                            onclick="return confirm('CẢNH BÁO: Thao tác này sẽ xóa vĩnh viễn khỏi Cơ sở dữ liệu!')"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-red-500/15 text-red-400 hover:bg-red-600 hover:text-white transition shadow-md"
                                            title="Xóa vĩnh viễn">

                                            <i class="fa-solid fa-trash-can text-xs"></i>

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td colspan="5"
                                class="py-16 text-center text-gray-500">

                                <i class="fa-solid fa-trash-can text-5xl mb-3 block opacity-20 text-gray-400"></i>

                                <span class="text-sm font-bold text-gray-400">

                                    Không tìm thấy dữ liệu rác nào trong danh mục này!

                                </span>

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

                @endif

            </div>


            {{-- =================================================
                PAGINATION
            ================================================== --}}
            @if(
            $items instanceof \Illuminate\Pagination\LengthAwarePaginator
            && $items->hasPages()
            )

            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-white/10 p-4 bg-[#18181c]/50">

                <div class="text-xs text-gray-400 font-medium">

                    Hiển thị

                    <strong class="text-white font-bold">
                        {{ $items->firstItem() ?? 0 }}
                    </strong>

                    đến

                    <strong class="text-white font-bold">
                        {{ $items->lastItem() ?? 0 }}
                    </strong>

                    trong tổng số

                    <strong class="text-white font-bold">
                        {{ number_format($items->total()) }}
                    </strong>

                    bản ghi

                </div>


                <div class="flex items-center gap-1.5 shrink-0">

                    {{-- TRƯỚC --}}
                    @if ($items->onFirstPage())

                    <span class="inline-flex h-9 px-3 items-center justify-center rounded-xl bg-white/5 text-gray-600 text-xs font-bold cursor-not-allowed border border-white/5">

                        <i class="fa-solid fa-chevron-left mr-1.5 text-[10px]"></i>

                        Trước

                    </span>

                    @else

                    <a href="{{ $items->previousPageUrl() }}"
                        class="inline-flex h-9 px-3 items-center justify-center rounded-xl bg-[#1e232d] border border-white/10 text-gray-300 hover:bg-red-600 hover:text-white hover:border-red-600 text-xs font-bold transition shadow-sm">

                        <i class="fa-solid fa-chevron-left mr-1.5 text-[10px]"></i>

                        Trước

                    </a>

                    @endif


                    @php

                    $startPage = max(
                    1,
                    $items->currentPage() - 2
                    );

                    $endPage = min(
                    $items->lastPage(),
                    $items->currentPage() + 2
                    );

                    @endphp


                    @if ($startPage > 1)

                    <a href="{{ $items->url(1) }}"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-[#1e232d] border border-white/10 text-gray-300 hover:bg-white/10 hover:text-white text-xs font-bold transition">

                        1

                    </a>

                    @if ($startPage > 2)

                    <span class="inline-flex h-9 w-6 items-center justify-center text-gray-500 text-xs font-bold">
                        ...
                    </span>

                    @endif

                    @endif


                    @foreach (
                    $items->getUrlRange(
                    $startPage,
                    $endPage
                    )
                    as $page => $url
                    )

                    @if ($page == $items->currentPage())

                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-r from-red-600 to-red-500 text-white text-xs font-black shadow-lg shadow-red-600/30 border border-red-500">

                        {{ $page }}

                    </span>

                    @else

                    <a href="{{ $url }}"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-[#1e232d] border border-white/10 text-gray-300 hover:bg-white/10 hover:text-white text-xs font-bold transition">

                        {{ $page }}

                    </a>

                    @endif

                    @endforeach


                    @if ($endPage < $items->lastPage())

                        @if ($endPage < $items->lastPage() - 1)

                            <span class="inline-flex h-9 w-6 items-center justify-center text-gray-500 text-xs font-bold">
                                ...
                            </span>

                            @endif


                            <a href="{{ $items->url($items->lastPage()) }}"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-[#1e232d] border border-white/10 text-gray-300 hover:bg-white/10 hover:text-white text-xs font-bold transition">

                                {{ $items->lastPage() }}

                            </a>

                            @endif


                            {{-- SAU --}}
                            @if ($items->hasMorePages())

                            <a href="{{ $items->nextPageUrl() }}"
                                class="inline-flex h-9 px-3 items-center justify-center rounded-xl bg-[#1e232d] border border-white/10 text-gray-300 hover:bg-red-600 hover:text-white hover:border-red-600 text-xs font-bold transition shadow-sm">

                                Sau

                                <i class="fa-solid fa-chevron-right ml-1.5 text-[10px]"></i>

                            </a>

                            @else

                            <span class="inline-flex h-9 px-3 items-center justify-center rounded-xl bg-white/5 text-gray-600 text-xs font-bold cursor-not-allowed border border-white/5">

                                Sau

                                <i class="fa-solid fa-chevron-right ml-1.5 text-[10px]"></i>

                            </span>

                            @endif

                </div>

            </div>

            @endif

        </div>

    </div>

</div>


{{-- =============================================================
    JS RÀNG BUỘC NGÀY
============================================================= --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {

        const tuNgayInput = document.getElementById('filter_tu_ngay');
        const denNgayInput = document.getElementById('filter_den_ngay');

        if (!tuNgayInput || !denNgayInput) {
            return;
        }

        function updateDateConstraints() {

            if (tuNgayInput.value) {

                denNgayInput.min = tuNgayInput.value;

                if (
                    denNgayInput.value &&
                    denNgayInput.value < tuNgayInput.value
                ) {

                    denNgayInput.value = tuNgayInput.value;

                }

            } else {

                denNgayInput.removeAttribute('min');

            }


            if (denNgayInput.value) {

                tuNgayInput.max = denNgayInput.value;

                if (
                    tuNgayInput.value &&
                    tuNgayInput.value > denNgayInput.value
                ) {

                    tuNgayInput.value = denNgayInput.value;

                }

            } else {

                tuNgayInput.removeAttribute('max');

            }

        }

        tuNgayInput.addEventListener(
            'change',
            updateDateConstraints
        );

        denNgayInput.addEventListener(
            'change',
            updateDateConstraints
        );

        updateDateConstraints();

    });
</script>

@endsection