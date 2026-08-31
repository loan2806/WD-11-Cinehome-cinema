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

        @if($tab === 'thong_bao')

        @php

        $typeMeta = [

        'info' => [
        'label' => 'Thông tin',
        'icon' => 'fa-circle-info',
        'class' => 'is-info',
        ],

        'warning' => [
        'label' => 'Cảnh báo',
        'icon' => 'fa-triangle-exclamation',
        'class' => 'is-warning',
        ],

        'promo' => [
        'label' => 'Khuyến mãi',
        'icon' => 'fa-gift',
        'class' => 'is-promo',
        ],

        'system' => [
        'label' => 'Hệ thống',
        'icon' => 'fa-gear',
        'class' => 'is-system',
        ],

        ];

        $audienceMeta = [

        'all' => [
        'label' => 'Tất cả người dùng',
        'icon' => 'fa-users',
        'class' => 'is-all',
        ],

        'hang_thanh_vien' => [
        'label' => 'Hạng thành viên',
        'icon' => 'fa-ranking-star',
        'class' => 'is-vip',
        ],

        'khach_hang' => [
        'label' => 'Khách hàng',
        'icon' => 'fa-user',
        'class' => 'is-user',
        ],

        'nguoi_dung_cu_the' => [
        'label' => 'Người dùng cụ thể',
        'icon' => 'fa-user-pen',
        'class' => 'is-specific',
        ],

        'nhan_vien' => [
        'label' => 'Nhân viên',
        'icon' => 'fa-user-tie',
        'class' => 'is-staff',
        ],

        'quan_ly' => [
        'label' => 'Quản lý',
        'icon' => 'fa-user-shield',
        'class' => 'is-admin',
        ],

        ];

        $activeFilterCount = collect([

        request('search'),

        request('loai'),

        request('trang_thai'),

        request('doi_tuong_nhan'),

        request('hang_thanh_vien'),

        request('nguoi_dung'),

        ])->filter(fn ($value) => filled($value))->count();

        @endphp


        {{-- FILTER --}}
        <form
            method="GET"
            action="{{ route('admin.thung-rac.index') }}"
            class="push-filter">

            <input type="hidden"
                name="tab"
                value="thong_bao">


            {{-- Tìm kiếm --}}
            <label class="push-field push-field--search">

                <span>Tìm kiếm</span>

                <div>

                    <i class="fa-solid fa-magnifying-glass"></i>

                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Nhập tiêu đề thông báo...">

                </div>

            </label>


            {{-- Loại --}}
            <label class="push-field">

                <span>Loại</span>

                <div class="push-custom-select"
                    data-select="loai">

                    <select name="loai"
                        class="custom-select-source">

                        <option value="">
                            Tất cả loại
                        </option>

                        @foreach ($typeMeta as $value => $meta)

                        <option value="{{ $value }}"
                            @selected(request('loai')===$value)>

                            {{ $meta['label'] }}

                        </option>

                        @endforeach

                    </select>

                    <button type="button"
                        class="push-custom-select__trigger"
                        aria-haspopup="listbox"
                        aria-expanded="false">

                        <span class="push-custom-select__value">
                            Tất cả loại
                        </span>

                        <i class="fa-solid fa-chevron-down push-custom-select__arrow"></i>

                    </button>

                    <div class="push-custom-select__menu"
                        role="listbox">

                        <div class="push-custom-select__option"
                            data-value=""
                            data-icon-color="all">

                            <i class="fa-regular fa-circle"></i>

                            <span>
                                Tất cả loại
                            </span>

                        </div>

                        @foreach ($typeMeta as $value => $meta)

                        <div class="push-custom-select__option"
                            data-value="{{ $value }}"
                            data-icon-color="{{ $value }}">

                            <i class="fa-solid {{ $meta['icon'] }}"></i>

                            <span>
                                {{ $meta['label'] }}
                            </span>

                        </div>

                        @endforeach

                    </div>

                </div>

            </label>


            {{-- Trạng thái --}}
            <label class="push-field">

                <span>Trạng thái</span>

                <div class="push-custom-select"
                    data-select="trang_thai">

                    <select name="trang_thai"
                        class="custom-select-source">

                        <option value="">
                            Tất cả trạng thái
                        </option>

                        <option value="chua_gui"
                            @selected(request('trang_thai')==='chua_gui' )>
                            Nháp
                        </option>

                        <option value="da_gui"
                            @selected(request('trang_thai')==='da_gui' )>
                            Đã gửi
                        </option>

                    </select>

                    <button type="button"
                        class="push-custom-select__trigger"
                        aria-haspopup="listbox"
                        aria-expanded="false">

                        <span class="push-custom-select__value">
                            Tất cả trạng thái
                        </span>

                        <i class="fa-solid fa-chevron-down push-custom-select__arrow"></i>

                    </button>

                    <div class="push-custom-select__menu"
                        role="listbox">

                        <div class="push-custom-select__option"
                            data-value=""
                            data-icon-color="all">

                            <i class="fa-regular fa-circle"></i>

                            <span>
                                Tất cả trạng thái
                            </span>

                        </div>

                        <div class="push-custom-select__option"
                            data-value="chua_gui"
                            data-icon-color="draft">

                            <i class="fa-regular fa-clock"></i>

                            <span>
                                Nháp
                            </span>

                        </div>

                        <div class="push-custom-select__option"
                            data-value="da_gui"
                            data-icon-color="sent">

                            <i class="fa-regular fa-circle-check"></i>

                            <span>
                                Đã gửi
                            </span>

                        </div>

                    </div>

                </div>

            </label>


            {{-- Người nhận --}}
            <label class="push-field">

                <span>
                    Người nhận
                </span>

                <div class="push-custom-select"
                    data-select="doi_tuong_nhan">

                    <select name="doi_tuong_nhan"
                        id="doi_tuong_nhan"
                        class="custom-select-source">

                        <option value="">
                            Tất cả nhóm
                        </option>

                        @foreach ($audienceMeta as $value => $meta)

                        <option value="{{ $value }}"
                            @selected(request('doi_tuong_nhan')===$value)>

                            {{ $meta['label'] }}

                        </option>

                        @endforeach

                    </select>

                    <button type="button"
                        class="push-custom-select__trigger"
                        aria-haspopup="listbox"
                        aria-expanded="false">

                        <span class="push-custom-select__value">
                            Tất cả nhóm
                        </span>

                        <i class="fa-solid fa-chevron-down push-custom-select__arrow"></i>

                    </button>

                    <div class="push-custom-select__menu"
                        role="listbox">

                        <div class="push-custom-select__option"
                            data-value=""
                            data-icon-color="all">

                            <i class="fa-solid fa-cube"></i>

                            <span>
                                Tất cả nhóm
                            </span>

                        </div>

                        @foreach ($audienceMeta as $value => $meta)

                        <div class="push-custom-select__option"
                            data-value="{{ $value }}"
                            data-icon-color="{{ $value }}">

                            <i class="fa-solid {{ $meta['icon'] }}"></i>

                            <span>
                                {{ $meta['label'] }}
                            </span>

                        </div>

                        @endforeach

                    </div>

                </div>

            </label>


            {{-- Người dùng cụ thể --}}
            <div
                class="push-field"
                id="nguoi-dung-filter"
                style="{{ request('doi_tuong_nhan') === 'nguoi_dung_cu_the' ? '' : 'display:none;' }}">

                <span>
                    Người dùng
                </span>

                <div>

                    <input
                        type="text"
                        name="nguoi_dung"
                        value="{{ request('nguoi_dung') }}"
                        placeholder="Nhập tên hoặc email...">

                </div>

            </div>


            {{-- Hạng thành viên --}}
            <label
                class="push-field"
                id="hang-thanh-vien-filter"
                style="{{ request('doi_tuong_nhan') === 'hang_thanh_vien' ? '' : 'display:none;' }}">

                <span>
                    Hạng thành viên
                </span>

                <div class="push-custom-select"
                    data-select="hang_thanh_vien">

                    <select name="hang_thanh_vien"
                        class="custom-select-source">

                        <option value="">
                            Tất cả hạng
                        </option>

                        <option value="member"
                            @selected(request('hang_thanh_vien')==='member' )>
                            Member
                        </option>

                        <option value="silver"
                            @selected(request('hang_thanh_vien')==='silver' )>
                            Silver
                        </option>

                        <option value="gold"
                            @selected(request('hang_thanh_vien')==='gold' )>
                            Gold
                        </option>

                        <option value="platinum"
                            @selected(request('hang_thanh_vien')==='platinum' )>
                            Platinum
                        </option>

                    </select>

                    <button type="button"
                        class="push-custom-select__trigger"
                        aria-haspopup="listbox"
                        aria-expanded="false">

                        <span class="push-custom-select__value">
                            Tất cả hạng
                        </span>

                        <i class="fa-solid fa-chevron-down push-custom-select__arrow"></i>

                    </button>

                    <div class="push-custom-select__menu"
                        role="listbox">

                        <div class="push-custom-select__option"
                            data-value=""
                            data-icon-color="all">

                            <i class="fa-solid fa-layer-group"></i>

                            <span>
                                Tất cả hạng
                            </span>

                        </div>

                        <div class="push-custom-select__option"
                            data-value="member"
                            data-icon-color="member">

                            <i class="fa-solid fa-user"></i>

                            <span>
                                Member
                            </span>

                        </div>

                        <div class="push-custom-select__option"
                            data-value="silver"
                            data-icon-color="silver">

                            <i class="fa-solid fa-medal"></i>

                            <span>
                                Silver
                            </span>

                        </div>

                        <div class="push-custom-select__option"
                            data-value="gold"
                            data-icon-color="gold">

                            <i class="fa-solid fa-medal"></i>

                            <span>
                                Gold
                            </span>

                        </div>

                        <div class="push-custom-select__option"
                            data-value="platinum"
                            data-icon-color="platinum">

                            <i class="fa-solid fa-crown"></i>

                            <span>
                                Platinum
                            </span>

                        </div>

                    </div>

                </div>

            </label>


            {{-- Nút lọc --}}
            <div class="push-filter-actions">

                <button type="submit"
                    class="push-filter-btn">

                    <i class="fa-solid fa-filter"></i>

                    Lọc

                </button>


                @if ($activeFilterCount > 0)

                <a
                    href="{{ route('admin.thung-rac.index', ['tab' => 'thong_bao']) }}"
                    class="push-reset-btn"
                    title="Xóa bộ lọc">

                    <i class="fa-solid fa-rotate-left"></i>

                </a>

                @endif

            </div>

        </form>

        @else

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

        @endif


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

                            <td colspan="7"
                                class="py-16 text-center">

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
                    THÔNG BÁO PUSH
                ================================================= --}}
                @elseif($tab === 'thong_bao')

                <table class="push-trash-table w-full text-left text-sm text-gray-300">

                    <thead>

                        <tr>

                            <th>STT</th>

                            @if(request('doi_tuong_nhan') === 'nguoi_dung_cu_the')
                            <th>Người dùng</th>
                            @endif

                            <th>Thông báo</th>
                            <th>Loại</th>
                            <th>Người nhận</th>
                            <th>Người tạo</th>
                            <th>Ngày xóa</th>

                            <th class="is-right">
                                Thao tác
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($items as $index => $thongBao)

                        @php

                        $typeMeta = [

                        'info' => [
                        'label' => 'Thông tin',
                        'icon' => 'fa-circle-info',
                        'class' => 'is-info',
                        ],

                        'warning' => [
                        'label' => 'Cảnh báo',
                        'icon' => 'fa-triangle-exclamation',
                        'class' => 'is-warning',
                        ],

                        'promo' => [
                        'label' => 'Khuyến mãi',
                        'icon' => 'fa-gift',
                        'class' => 'is-promo',
                        ],

                        'system' => [
                        'label' => 'Hệ thống',
                        'icon' => 'fa-gear',
                        'class' => 'is-system',
                        ],

                        ];

                        $audienceMeta = [

                        'all' => [
                        'label' => 'Tất cả người dùng',
                        'icon' => 'fa-users',
                        'class' => 'is-all',
                        ],

                        'hang_thanh_vien' => [
                        'label' => 'Hạng thành viên',
                        'icon' => 'fa-ranking-star',
                        'class' => 'is-vip',
                        ],

                        'khach_hang' => [
                        'label' => 'Khách hàng',
                        'icon' => 'fa-user',
                        'class' => 'is-user',
                        ],

                        'nguoi_dung_cu_the' => [
                        'label' => 'Người dùng cụ thể',
                        'icon' => 'fa-user-pen',
                        'class' => 'is-specific',
                        ],

                        'nhan_vien' => [
                        'label' => 'Nhân viên',
                        'icon' => 'fa-user-tie',
                        'class' => 'is-staff',
                        ],

                        'quan_ly' => [
                        'label' => 'Quản lý',
                        'icon' => 'fa-user-shield',
                        'class' => 'is-admin',
                        ],

                        ];

                        $type = $typeMeta[$thongBao->loai]
                        ?? [
                        'label' => ucfirst($thongBao->loai ?? ''),
                        'icon' => 'fa-bell',
                        'class' => 'is-system',
                        ];

                        $audience = $audienceMeta[$thongBao->doi_tuong_nhan]
                        ?? [
                        'label' => 'Không xác định',
                        'icon' => 'fa-users',
                        'class' => 'is-all',
                        ];

                        $authorName =
                        $thongBao->nguoiTao->ho_ten
                        ?? 'Hệ thống';

                        @endphp

                        <tr>

                            {{-- STT --}}
                            <td data-label="STT">

                                <span class="push-trash-index">
                                    {{ $items->firstItem() + $index }}
                                </span>

                            </td>


                            {{-- NGƯỜI DÙNG CỤ THỂ --}}
                            @if(request('doi_tuong_nhan') === 'nguoi_dung_cu_the')

                            <td data-label="Người dùng">

                                @php

                                $nguoiNhan =
                                \App\Models\ThongBaoPushNguoiDung::with('nguoiDung')
                                ->where(
                                'thong_bao_push_id',
                                $thongBao->id
                                )
                                ->get()
                                ->pluck('nguoiDung')
                                ->filter();

                                @endphp

                                @forelse($nguoiNhan as $user)

                                <div class="push-trash-user">

                                    <strong>
                                        {{ $user->ho_ten }}
                                    </strong>

                                    <small>
                                        {{ $user->email }}
                                    </small>

                                </div>

                                @empty

                                <span class="push-trash-muted">
                                    Không xác định
                                </span>

                                @endforelse

                            </td>

                            @endif


                            {{-- THÔNG BÁO --}}
                            <td data-label="Thông báo">

                                <div class="push-trash-message">

                                    <strong>
                                        {{ \Illuminate\Support\Str::limit($thongBao->tieu_de, 68) }}
                                    </strong>

                                    <small>
                                        {{ \Illuminate\Support\Str::limit($thongBao->noi_dung, 96) }}
                                    </small>

                                </div>

                            </td>


                            {{-- LOẠI --}}
                            <td data-label="Loại">

                                <span class="push-chip {{ $type['class'] }}">

                                    <i class="fa-solid {{ $type['icon'] }}"></i>

                                    {{ $type['label'] }}

                                </span>

                            </td>


                            {{-- NGƯỜI NHẬN --}}
                            <td data-label="Người nhận">

                                <span class="push-chip {{ $audience['class'] }}">

                                    <i class="fa-solid {{ $audience['icon'] }}"></i>

                                    {{ $audience['label'] }}

                                </span>

                            </td>


                            {{-- NGƯỜI TẠO --}}
                            <td data-label="Người tạo">

                                <div class="push-trash-author">

                                    <span>
                                        {{ strtoupper(mb_substr($authorName, 0, 1)) }}
                                    </span>

                                    <strong>
                                        {{ $authorName }}
                                    </strong>

                                </div>

                            </td>


                            {{-- NGÀY XÓA --}}
                            <td data-label="Ngày xóa">

                                @if($thongBao->deleted_at)

                                <span class="push-date">

                                    <i class="fa-regular fa-calendar-xmark"></i>

                                    {{ $thongBao->deleted_at->format('d/m/Y') }}

                                    <small>
                                        {{ $thongBao->deleted_at->format('H:i') }}
                                    </small>

                                </span>

                                @else

                                <span class="text-gray-500">
                                    —
                                </span>

                                @endif

                            </td>


                            {{-- THAO TÁC --}}
                            <td
                                data-label="Thao tác"
                                class="is-right">

                                <div class="push-action-buttons">

                                    {{-- KHÔI PHỤC --}}
                                    <form method="POST"
                                        class="restore-form"
                                        action="{{ route('admin.thung-rac.restore', [
        'type' => 'thong_bao',
        'id' => $thongBao->id
    ]) }}">

                                        @csrf
                                        @method('PATCH')

                                        <button type="submit"
                                            class="push-action-btn is-restore"
                                            title="Khôi phục">

                                            <i class="fa-solid fa-rotate-left"></i>

                                        </button>

                                    </form>


                                    {{-- XÓA VĨNH VIỄN --}}
                                    <form method="POST"
                                        class="force-delete-form"
                                        action="{{ route('admin.thung-rac.force-delete', [
        'type' => 'thong_bao',
        'id' => $thongBao->id
    ]) }}">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                            class="push-action-btn is-delete"
                                            title="Xóa vĩnh viễn">

                                            <i class="fa-solid fa-trash-can"></i>

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td
                                colspan="{{ request('doi_tuong_nhan') === 'nguoi_dung_cu_the' ? 8 : 7 }}"
                                class="push-trash-empty">

                                <i class="fa-solid fa-trash-can"></i>

                                <h3>
                                    Thùng rác đang trống
                                </h3>

                                <p>
                                    Chưa có thông báo đẩy nào được xóa.
                                </p>

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

                                default => ucfirst(
                                str_replace('_', ' ', $vaiTro)
                                ),

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


                                    {{-- =================================================
                                        KHÔI PHỤC
                                    ================================================== --}}
                                    <form action="{{ $restoreRoute }}"
                                        method="POST">

                                        @csrf

                                        {{-- PHIM + SUẤT CHIẾU dùng PATCH --}}
                                        @if(in_array($tab, ['phim', 'suat_chieu']))
                                        @method('PATCH')
                                        @endif

                                        <button type="submit"
                                            onclick="return confirm('Khôi phục bản ghi này về trạng thái hoạt động?')"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-blue-500/15 text-blue-400 hover:bg-blue-500 hover:text-white transition shadow-md"
                                            title="Khôi phục dữ liệu">

                                            <i class="fa-solid fa-rotate-left text-xs"></i>

                                        </button>

                                    </form>


                                    {{-- =================================================
                                        XÓA VĨNH VIỄN
                                    ================================================== --}}
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
    STYLE BỔ SUNG CHO TRASH
============================================================= --}}

<style>
    /* =========================================================
   THÔNG BÁO PUSH - BẢNG RIÊNG TRONG THÙNG RÁC CHUNG
   Chỉ áp dụng cho tab thong_bao
========================================================= */

    .push-trash-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 980px;
    }

    .push-trash-table thead {
        background: #18181c;
        border-bottom: 1px solid rgba(255, 255, 255, .10);
    }

    .push-trash-table thead th {
        padding: 15px 16px;
        color: #9ca3af;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .06em;
        white-space: nowrap;
    }

    .push-trash-table thead th.is-right,
    .push-trash-table td.is-right {
        text-align: right;
    }

    .push-trash-table tbody tr {
        border-bottom: 1px solid rgba(255, 255, 255, .06);
        transition: background .15s ease;
    }

    .push-trash-table tbody tr:hover {
        background: rgba(255, 255, 255, .025);
    }

    .push-trash-table tbody td {
        padding: 17px 16px;
        vertical-align: middle;
    }

    /* =========================================================
   FIX 2 THANH CUỘN - TRANG THÙNG RÁC
========================================================= */

    html,
    body {
        overflow-x: hidden !important;
    }

    body {
        overflow-y: auto !important;
    }

    /* Không cho khu vực bảng tạo thêm thanh cuộn dọc */
    .push-trash-table {
        overflow: visible !important;
    }

    .push-trash-table+* {
        overflow-y: visible !important;
    }

    /* Chỉ bảng được phép cuộn ngang */
    .push-trash-table-wrap {
        overflow-x: auto !important;
        overflow-y: visible !important;
    }

    .push-trash-index {
        display: inline-flex;
        min-width: 30px;
        height: 30px;
        padding: 0 8px;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        background: rgba(255, 255, 255, .05);
        border: 1px solid rgba(255, 255, 255, .10);
        color: #d1d5db;
        font-size: 12px;
        font-weight: 800;
    }

    .push-trash-user {
        display: flex;
        flex-direction: column;
        gap: 3px;
        min-width: 150px;
    }

    .push-trash-user strong {
        color: #f8fafc;
        font-size: 13px;
        font-weight: 800;
    }

    .push-trash-user small {
        color: #94a3b8;
        font-size: 11px;
    }

    .push-trash-message {
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 210px;
    }

    .push-trash-message strong {
        color: #f8fafc;
        font-size: 14px;
        font-weight: 800;
    }

    .push-trash-message small {
        color: #94a3b8;
        font-size: 11px;
        line-height: 1.4;
    }

    .push-trash-author {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        white-space: nowrap;
    }

    .push-trash-author>span {
        display: inline-flex;
        width: 38px;
        height: 38px;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        border-radius: 11px;
        background: linear-gradient(135deg, #7f2d2d, #92400e);
        color: #fff;
        font-size: 13px;
        font-weight: 900;
    }

    .push-trash-author strong {
        color: #f8fafc;
        font-size: 13px;
        font-weight: 800;
    }

    .push-trash-muted {
        color: #64748b;
        font-size: 12px;
    }

    .push-trash-empty {
        padding: 60px 20px !important;
        text-align: center;
        color: #6b7280;
    }

    .push-trash-empty>i {
        display: block;
        margin-bottom: 14px;
        color: #4b5563;
        font-size: 42px;
    }

    .push-trash-empty h3 {
        margin: 0;
        color: #d1d5db;
        font-size: 16px;
        font-weight: 900;
    }

    .push-trash-empty p {
        margin-top: 5px;
        color: #6b7280;
        font-size: 12px;
    }

    /* Nút Lọc - Thông báo Push */
    .push-filter-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;

        height: 46px;
        min-width: 78px;
        padding: 0 18px;

        border: 1px solid #ff3b4f;
        border-radius: 12px;

        background: #ff3b4f;
        color: #fff;

        font-size: 14px;
        font-weight: 800;

        cursor: pointer;
        transition: all .2s ease;

        box-shadow: 0 6px 16px rgba(255, 59, 79, .20);
    }

    .push-filter-btn:hover {
        background: #ff253b;
        border-color: #ff253b;
        color: #fff;

        transform: translateY(-1px);

        box-shadow: 0 8px 20px rgba(255, 59, 79, .30);
    }

    .push-filter-btn:active {
        transform: translateY(0);
    }

    .push-filter-btn i {
        color: #fff;
        font-size: 13px;
    }

    .push-trash-table .push-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 11px;
        border-radius: 999px;
        white-space: nowrap;
        font-size: 11px;
        font-weight: 800;
    }

    .push-trash-table .push-chip i {
        margin: 0;
        font-size: 10px;
    }

    .push-trash-table .push-date {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #fbbf24;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .push-trash-table .push-date i {
        font-size: 10px;
    }

    .push-trash-table .push-date small {
        color: #fbbf24;
        font-size: 11px;
        font-weight: 800;
    }

    @media (max-width: 768px) {

        .push-trash-table {
            min-width: 900px;
        }

    }
</style>

<style>
    /* =========================================================
   CUSTOM DROPDOWN - TRASH PAGE
   Đồng bộ với trang Quản lý thông báo đẩy
========================================================= */

    .push-panel,
    .push-panel-head,
    .push-filter,
    .push-filter-actions,
    .push-field,
    .push-custom-select {
        overflow: visible !important;
    }

    .push-panel {
        position: relative;
        z-index: 10;
    }

    .push-filter {
        position: relative;
        z-index: 1000 !important;
    }

    .push-field {
        position: relative;
        z-index: 10;
    }

    .push-custom-select {
        position: relative;
        width: 100%;
        z-index: 20;
    }

    .push-custom-select.is-open {
        z-index: 200 !important;
    }

    .custom-select-source {
        display: none !important;
    }

    .push-custom-select__trigger {
        width: 100%;
        min-height: 46px;
        padding: 0 14px 0 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;

        border: 1px solid #303642;
        border-radius: 14px;
        background: #171b23 !important;
        color: #f5f5f5 !important;

        font: inherit;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.2;

        cursor: pointer;
        outline: none;
        box-sizing: border-box;

        transition: border-color .18s ease, background .18s ease;
    }

    .push-custom-select__trigger:hover,
    .push-custom-select.is-open .push-custom-select__trigger {
        border-color: #ff3347 !important;
        background: #1b1f28 !important;
    }

    .push-custom-select__value {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* ================================
   NÚT THAO TÁC THÙNG RÁC
================================ */

    .push-action-buttons {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
    }

    /* Nút chung */
    .push-action-btn {
        width: 42px;
        height: 42px;
        border: none;
        border-radius: 12px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        cursor: pointer;
        transition: all 0.2s ease;

        font-size: 15px;
    }

    /* ================================
   KHÔI PHỤC
   Giống ảnh: nền xanh đậm
================================ */

    .push-action-btn.is-restore {
        background: #063526 !important;
        color: #59e6a7 !important;
    }

    .push-action-btn.is-restore i {
        color: #59e6a7 !important;
    }

    .push-action-btn.is-restore:hover {
        background: #084b38 !important;
        color: #72f0b8 !important;
        transform: translateY(-1px);
    }


    /* ================================
   XÓA VĨNH VIỄN / THÙNG RÁC
   Giống ảnh: nền đỏ rượu
================================ */

    .push-action-btn.is-delete {
        background: #35131b !important;
        color: #ff7186 !important;
    }

    .push-action-btn.is-delete i {
        color: #ff7186 !important;
    }

    .push-action-btn.is-delete:hover {
        background: #4a1722 !important;
        color: #ff8799 !important;
        transform: translateY(-1px);
    }


    /* Form không làm lệch nút */
    .push-action-buttons form {
        margin: 0;
        padding: 0;
        display: inline-flex;
    }

    .push-custom-select__arrow {
        flex: 0 0 auto;
        color: #9ca3af !important;
        font-size: 12px;
        transition: transform .18s ease;
    }

    .push-custom-select.is-open .push-custom-select__arrow {
        transform: rotate(180deg);
    }

    .push-custom-select__menu {
        position: absolute !important;
        left: 0;
        right: 0;
        top: calc(100% + 6px);

        padding: 0;
        margin: 0;

        background: #171b23 !important;
        border: 1px solid #303642 !important;
        border-radius: 14px !important;

        box-shadow: 0 14px 32px rgba(0, 0, 0, .48);

        opacity: 0 !important;
        visibility: hidden;
        pointer-events: none;

        transform: translateY(-5px);

        z-index: 1000 !important;

        overflow: hidden !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;

        transition:
            opacity .15s ease,
            transform .15s ease,
            visibility .15s ease;
    }

    .push-custom-select.is-open .push-custom-select__menu {
        opacity: 1 !important;
        visibility: visible;
        pointer-events: auto !important;
        transform: translateY(0);
    }

    /* Nếu JS phát hiện không đủ chỗ phía dưới */
    .push-custom-select.open-up .push-custom-select__menu {
        top: auto !important;
        bottom: calc(100% + 6px) !important;
        transform: translateY(5px);
    }

    .push-custom-select.open-up.is-open .push-custom-select__menu {
        transform: translateY(0);
    }

    .push-custom-select__option {
        min-height: 46px;
        width: 100%;
        padding: 0 14px;

        display: flex;
        align-items: center;
        gap: 10px;

        background: #171b23 !important;
        color: #e5e7eb !important;

        border: 0;
        border-bottom: 1px solid #303642;

        font-size: 14px;
        font-weight: 600;
        line-height: 1.2;

        cursor: pointer;
        user-select: none;
        pointer-events: auto !important;
        box-sizing: border-box;
    }

    .push-custom-select__option:first-child {
        border-radius: 13px 13px 0 0;
    }

    .push-custom-select__option:last-child {
        border-bottom: none;
        border-radius: 0 0 13px 13px;
    }

    .push-custom-select__option:hover {
        background: #252b36 !important;
        color: #fff !important;
    }

    .push-custom-select__option.is-selected {
        background: #2c202c !important;
        color: #fff !important;
    }

    /* =========================================================
   ICON - TẤT CẢ ĐỀU CÓ MÀU
========================================================= */

    .push-custom-select__option i {
        width: 18px;
        min-width: 18px;
        text-align: center;
        font-size: 14px;
        margin: 0;
    }

    /* Chung */
    .push-custom-select__option[data-icon-color="all"] i {
        color: #ff5262 !important;
    }

    /* Loại */
    .push-custom-select__option[data-icon-color="info"] i {
        color: #60a5fa !important;
    }

    .push-custom-select__option[data-icon-color="warning"] i {
        color: #fbbf24 !important;
    }

    .push-custom-select__option[data-icon-color="promo"] i {
        color: #c084fc !important;
    }

    .push-custom-select__option[data-icon-color="system"] i {
        color: #38bdf8 !important;
    }

    /* Trạng thái */
    .push-custom-select__option[data-icon-color="draft"] i {
        color: #ff5262 !important;
    }

    .push-custom-select__option[data-icon-color="sent"] i {
        color: #34d399 !important;
    }

    /* Người nhận */
    .push-custom-select__option[data-icon-color="all"] i {
        color: #ff5262 !important;
    }

    .push-custom-select__option[data-icon-color="hang_thanh_vien"] i {
        color: #fbbf24 !important;
    }

    .push-custom-select__option[data-icon-color="khach_hang"] i {
        color: #60a5fa !important;
    }

    .push-custom-select__option[data-icon-color="nguoi_dung_cu_the"] i {
        color: #c084fc !important;
    }

    .push-custom-select__option[data-icon-color="nhan_vien"] i {
        color: #34d399 !important;
    }

    .push-custom-select__option[data-icon-color="quan_ly"] i {
        color: #a78bfa !important;
    }

    /* Hạng */
    .push-custom-select__option[data-icon-color="member"] i {
        color: #60a5fa !important;
    }

    .push-custom-select__option[data-icon-color="silver"] i {
        color: #cbd5e1 !important;
    }

    .push-custom-select__option[data-icon-color="gold"] i {
        color: #fbbf24 !important;
    }

    .push-custom-select__option[data-icon-color="platinum"] i {
        color: #c084fc !important;
    }

    /* Giữ màu icon khi hover */
    .push-custom-select__option:hover i,
    .push-custom-select__option.is-selected i {
        filter: brightness(1.08);
    }

    /* Không cho table đè menu */
    .push-table-wrap {
        position: relative;
        z-index: 1;
    }

    @media (max-width: 768px) {
        .push-custom-select__menu {
            max-width: 100%;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        /* =====================================================
           CUSTOM DROPDOWN
        ===================================================== */

        const dropdowns = document.querySelectorAll('.push-custom-select');

        function closeAll(except = null) {
            dropdowns.forEach(function(dropdown) {
                if (dropdown !== except) {
                    dropdown.classList.remove('is-open', 'open-up');

                    const trigger = dropdown.querySelector(
                        '.push-custom-select__trigger'
                    );

                    if (trigger) {
                        trigger.setAttribute('aria-expanded', 'false');
                    }
                }
            });
        }

        dropdowns.forEach(function(dropdown) {

            const trigger = dropdown.querySelector(
                '.push-custom-select__trigger'
            );

            const menu = dropdown.querySelector(
                '.push-custom-select__menu'
            );

            const select = dropdown.querySelector(
                '.custom-select-source'
            );

            const valueDisplay = dropdown.querySelector(
                '.push-custom-select__value'
            );

            const options = dropdown.querySelectorAll(
                '.push-custom-select__option'
            );

            if (!trigger || !menu || !select || !valueDisplay) {
                return;
            }

            function sync() {
                const selected = select.options[select.selectedIndex];

                if (selected) {
                    valueDisplay.textContent =
                        selected.textContent.trim();
                }

                options.forEach(function(option) {
                    option.classList.toggle(
                        'is-selected',
                        option.dataset.value === select.value
                    );
                });
            }

            function calculateDirection() {
                dropdown.classList.remove('open-up');

                const rect = trigger.getBoundingClientRect();

                const spaceBelow =
                    window.innerHeight - rect.bottom;

                const spaceAbove =
                    rect.top;

                const menuHeight =
                    menu.scrollHeight;

                if (
                    spaceBelow < menuHeight &&
                    spaceAbove > spaceBelow
                ) {
                    dropdown.classList.add('open-up');
                }
            }

            sync();

            trigger.addEventListener('click', function(event) {

                event.preventDefault();
                event.stopPropagation();

                const wasOpen =
                    dropdown.classList.contains('is-open');

                closeAll();

                if (wasOpen) {
                    return;
                }

                dropdown.classList.add('is-open');

                trigger.setAttribute(
                    'aria-expanded',
                    'true'
                );

                requestAnimationFrame(function() {
                    calculateDirection();
                });
            });

            options.forEach(function(option) {

                option.addEventListener('click', function(event) {

                    event.preventDefault();
                    event.stopPropagation();

                    select.value = this.dataset.value;

                    sync();

                    dropdown.classList.remove(
                        'is-open',
                        'open-up'
                    );

                    trigger.setAttribute(
                        'aria-expanded',
                        'false'
                    );

                    select.dispatchEvent(
                        new Event('change', {
                            bubbles: true
                        })
                    );
                });

            });

            select.addEventListener('change', sync);
        });

        /* Click ngoài dropdown */
        document.addEventListener('click', function(event) {

            if (!event.target.closest('.push-custom-select')) {
                closeAll();
            }

        });

        /* Nếu resize/scroll khi đang mở thì tính lại hướng */
        window.addEventListener('resize', function() {

            document
                .querySelectorAll('.push-custom-select.is-open')
                .forEach(function(dropdown) {

                    const trigger =
                        dropdown.querySelector(
                            '.push-custom-select__trigger'
                        );

                    const menu =
                        dropdown.querySelector(
                            '.push-custom-select__menu'
                        );

                    if (!trigger || !menu) return;

                    dropdown.classList.remove('open-up');

                    const rect =
                        trigger.getBoundingClientRect();

                    const spaceBelow =
                        window.innerHeight - rect.bottom;

                    const spaceAbove =
                        rect.top;

                    if (
                        spaceBelow < menu.scrollHeight &&
                        spaceAbove > spaceBelow
                    ) {
                        dropdown.classList.add('open-up');
                    }
                });
        });

        /* =====================================================
           HIỆN / ẨN BỘ LỌC THEO NGƯỜI NHẬN
        ===================================================== */

        const audienceSelect =
            document.getElementById('doi_tuong_nhan');

        const memberRankFilter =
            document.getElementById('hang-thanh-vien-filter');

        const userFilter =
            document.getElementById('nguoi-dung-filter');

        function toggleTrashFilters() {

            if (!audienceSelect) {
                return;
            }

            const value = audienceSelect.value;

            if (memberRankFilter) {
                memberRankFilter.style.display =
                    value === 'hang_thanh_vien' ?
                    '' :
                    'none';
            }

            if (userFilter) {
                userFilter.style.display =
                    value === 'nguoi_dung_cu_the' ?
                    '' :
                    'none';
            }
        }

        if (audienceSelect) {
            audienceSelect.addEventListener(
                'change',
                toggleTrashFilters
            );
        }

        toggleTrashFilters();

    });
</script>


<style>
    /*
     * Dùng chung màu với index
     */

    .member-rank {
        display: block;
        margin-top: 5px;
        font-size: 11px;
        color: #94a3b8;
    }

    /* =========================================================
   NGƯỜI DÙNG CỤ THỂ
========================================================= */

    .user-cell {
        display: flex;
        flex-direction: column;
        gap: 3px;
        min-width: 150px;
    }

    .user-name {
        font-size: 13px;
        font-weight: 700;
        color: #f8fafc;
        line-height: 1.3;
    }

    .user-email {
        font-size: 11px;
        color: #94a3b8;
        line-height: 1.3;
    }




    /* =========================================================
   MÀU LOẠI THÔNG BÁO
   ========================================================= */

    /* Thông tin - xanh dương */
    .push-chip.is-info {
        background: rgba(59, 130, 246, 0.12);
        border: 1px solid rgba(59, 130, 246, 0.38);
        color: #7db7ff;
    }

    /* Cảnh báo - vàng/cam */
    .push-chip.is-warning {
        background: rgba(245, 158, 11, 0.12);
        border: 1px solid rgba(245, 158, 11, 0.38);
        color: #ffc85c;
    }

    /* Khuyến mãi - tím */
    .push-chip.is-promo {
        background: rgba(139, 92, 246, 0.14);
        border: 1px solid rgba(139, 92, 246, 0.38);
        color: #c4a7ff;
    }

    /* Hệ thống - xám */
    .push-chip.is-system {
        background: rgba(148, 163, 184, 0.10);
        border: 1px solid rgba(148, 163, 184, 0.28);
        color: #cbd5e1;
    }


    /* =========================================================
   MÀU ĐỐI TƯỢNG NHẬN
   ========================================================= */

    /* Tất cả người dùng - vàng */
    .push-chip.is-all {
        background: rgba(180, 120, 20, 0.15);
        border: 1px solid rgba(245, 180, 50, 0.38);
        color: #f5c76b;
    }

    /* Hạng thành viên - vàng */
    .push-chip.is-vip {
        background: rgba(180, 120, 20, 0.15);
        border: 1px solid rgba(245, 180, 50, 0.38);
        color: #f5c76b;
    }

    /* Khách hàng - xanh dương */
    .push-chip.is-user {
        background: rgba(37, 99, 235, 0.13);
        border: 1px solid rgba(59, 130, 246, 0.38);
        color: #80b8ff;
    }

    /* Người dùng cụ thể - tím */
    .push-chip.is-specific {
        background: rgba(124, 58, 237, 0.14);
        border: 1px solid rgba(139, 92, 246, 0.38);
        color: #c5a8ff;
    }

    /* Nhân viên - xanh ngọc */
    .push-chip.is-staff {
        background: rgba(16, 185, 129, 0.13);
        border: 1px solid rgba(16, 185, 129, 0.36);
        color: #72e3bb;
    }

    /* Quản lý - tím */
    .push-chip.is-admin {
        background: rgba(139, 92, 246, 0.15);
        border: 1px solid rgba(168, 120, 255, 0.38);
        color: #c8a9ff;
    }


    /* =========================================================
   ICON TRONG BADGE
   ========================================================= */

    .push-chip i {
        margin-right: 4px;
        font-size: 10px;
    }
</style>


<script>
    document.addEventListener('DOMContentLoaded', function() {

        /* =========================================================
           1. LẤY CÁC ELEMENT
        ========================================================= */

        const audienceSelect =
            document.getElementById('doi_tuong_nhan');

        const memberRankFilter =
            document.getElementById('hang-thanh-vien-filter');

        const userFilter =
            document.getElementById('nguoi-dung-filter');


        /* =========================================================
           2. HIỆN / ẨN BỘ LỌC THEO NGƯỜI NHẬN
        ========================================================= */

        function toggleFilters() {

            if (!audienceSelect) {
                return;
            }

            const value = audienceSelect.value;


            /* -----------------------------------------------------
               HẠNG THÀNH VIÊN
            ----------------------------------------------------- */

            if (memberRankFilter) {

                if (value === 'hang_thanh_vien') {
                    memberRankFilter.style.display = '';
                } else {
                    memberRankFilter.style.display = 'none';
                }

            }


            /* -----------------------------------------------------
               NGƯỜI DÙNG CỤ THỂ
            ----------------------------------------------------- */

            if (userFilter) {

                if (value === 'nguoi_dung_cu_the') {
                    userFilter.style.display = '';
                } else {
                    userFilter.style.display = 'none';
                }

            }

        }


        /* =========================================================
           3. KHI THAY ĐỔI NGƯỜI NHẬN
        ========================================================= */

        if (audienceSelect) {

            audienceSelect.addEventListener(
                'change',
                function() {

                    toggleFilters();

                }
            );

        }


        /* =========================================================
           4. CHẠY NGAY KHI LOAD TRANG
           
           Ví dụ:
           ?doi_tuong_nhan=nguoi_dung_cu_the

           => tự hiện ô "Người dùng"

           ?doi_tuong_nhan=hang_thanh_vien

           => tự hiện ô "Hạng thành viên"
        ========================================================= */

        toggleFilters();


        /* =========================================================
           5. XÁC NHẬN KHÔI PHỤC
        ========================================================= */

        document
            .querySelectorAll('.restore-form')
            .forEach(function(form) {

                form.addEventListener(
                    'submit',
                    function(event) {

                        const confirmed = confirm(
                            'Bạn có chắc chắn muốn khôi phục thông báo này?'
                        );

                        if (!confirmed) {
                            event.preventDefault();
                        }

                    }
                );

            });


        /* =========================================================
           6. XÁC NHẬN XÓA VĨNH VIỄN
        ========================================================= */

        document
            .querySelectorAll('.force-delete-form')
            .forEach(function(form) {

                form.addEventListener(
                    'submit',
                    function(event) {

                        const confirmed = confirm(
                            'Thông báo sẽ bị xóa vĩnh viễn và không thể khôi phục. Bạn có chắc chắn muốn tiếp tục?'
                        );

                        if (!confirmed) {
                            event.preventDefault();
                        }

                    }
                );

            });


    });
</script>

@endsection