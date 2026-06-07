@extends('layouts.admin')

@section('page-title', 'Quản lý Suất Chiếu')

@section('content')

    <div class="admin-panel">

        {{-- HEADER --}}
        <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

            <div>

                <h5 class="text-2xl font-black text-white">
                    Danh sách suất chiếu
                </h5>

                <small class="text-gray-400">
                    Quản lý toàn bộ suất chiếu trong hệ thống
                </small>

            </div>

            <a href="{{ route('admin.suat-chieus.create') }}"
                class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 py-3 text-sm font-bold text-white shadow-lg transition hover:scale-[1.02]">

                <i class="fa-solid fa-plus"></i>

                Thêm suất chiếu

            </a>

        </div>

        {{-- FILTER --}}
        <form method="GET" action="{{ route('admin.suat-chieus.index') }}" class="mt-6 flex flex-wrap items-center gap-3">

            <select name="phim_id"
                class="h-12 rounded-2xl border border-white/10 bg-[#151515] px-4 text-white outline-none focus:border-[#d99a32]">

                <option value="">-- Tất cả Phim --</option>

                @foreach ($phims as $phim)
                    <option value="{{ $phim->id }}"
                        {{ request('phim_id') == $phim->id ? 'selected' : '' }}>

                        {{ $phim->ten_phim }}

                    </option>
                @endforeach

            </select>

            <select name="trang_thai"
                class="h-12 rounded-2xl border border-white/10 bg-[#151515] px-4 text-white outline-none focus:border-[#d99a32]">

                <option value="">-- Tất cả trạng thái --</option>

                @foreach (\App\Models\SuatChieu::TRANG_THAI_LIST as $value => $label)
                    <option value="{{ $value }}"
                        {{ request('trang_thai') == $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach

            </select>

            <select name="phong_chieu_id"
                class="h-12 rounded-2xl border border-white/10 bg-[#151515] px-4 text-white outline-none focus:border-[#d99a32]">

                <option value="">-- Tất cả Phòng --</option>

                @foreach ($phongChieus as $phong)
                    <option value="{{ $phong->id }}"
                        {{ request('phong_chieu_id') == $phong->id ? 'selected' : '' }}>

                        {{ $phong->ten_phong }}

                    </option>
                @endforeach

            </select>

            <input type="date" name="ngay_chieu" value="{{ request('ngay_chieu') }}"
                class="h-12 rounded-2xl border border-white/10 bg-white/5 px-4 text-white outline-none transition focus:border-[#d99a32]">

            <button type="submit"
                class="h-12 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 text-sm font-bold text-white shadow-lg transition hover:opacity-90">

                <i class="fa-solid fa-filter mr-1"></i>

                Lọc

            </button>

            <a href="{{ route('admin.suat-chieus.index') }}"
                class="flex h-12 items-center rounded-2xl border border-white/10 bg-white/5 px-5 text-sm font-bold text-white transition hover:bg-white/10">

                Reset

            </a>

        </form>

        {{-- TABLE --}}
        <div class="mt-6 overflow-hidden rounded-3xl border border-white/10">

            <div class="overflow-x-auto">

                <table class="w-full min-w-[1000px] text-left">

                    <thead class="bg-white/5 text-xs uppercase tracking-wider text-gray-400">

                        <tr>

                            <th class="px-5 py-4">STT</th>

                            <th class="px-5 py-4">Phim</th>

                            <th class="px-5 py-4">Rạp</th>

                            <th class="px-5 py-4">Phòng</th>

                            <th class="px-5 py-4">Ngày Chiếu</th>

                            <th class="px-5 py-4">Giờ Chiếu</th>

                            <th class="px-5 py-4">Giờ Kết Thúc</th>

                            <th class="px-5 py-4">Giá Vé</th>

                            <th class="px-5 py-4">Trạng Thái</th>

                            <th class="px-5 py-4 text-right">Hành động</th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-white/5">

                        @forelse ($suatChieus as $key => $suat)
                            <tr class="bg-[#0f0f0f] transition hover:bg-white/5">

                                <td class="px-5 py-5 text-gray-400">

                                    #{{ $suatChieus->firstItem() + $key }}

                                </td>

                                <td class="px-5 py-5 text-gray-300">

                                    {{ $suat->phim->ten_phim ?? 'N/A' }}

                                </td>

                                <td class="px-5 py-5 text-gray-300">

                                    {{ $suat->rapChieuPhim->ten_rap ?? 'N/A' }}

                                </td>

                                <td class="px-5 py-5 text-gray-300">

                                    {{ $suat->phongChieu->ten_phong ?? 'N/A' }}

                                </td>

                                <td class="px-5 py-5 text-gray-300">

                                    {{ $suat->thoi_gian_chieu->format('d/m/Y') }}

                                </td>

                                <td class="px-5 py-5 text-gray-300">

                                    {{ $suat->thoi_gian_chieu->format('H:i') }}

                                </td>

                                <td class="px-5 py-5 text-gray-300">

                                    {{ $suat->thoi_gian_ket_thuc ? $suat->thoi_gian_ket_thuc->format('H:i') : '-' }}

                                </td>

                                <td class="px-5 py-5 text-gray-300">

                                    {{ number_format($suat->gia_ve) }}đ

                                </td>

                                <td class="px-5 py-5 text-gray-300">

                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold
                                        {{ $suat->trang_thai === 'dang_chieu' ? 'bg-green-500/15 text-green-300' : '' }}
                                        {{ $suat->trang_thai === 'sap_chieu' ? 'bg-blue-500/15 text-blue-300' : '' }}
                                        {{ $suat->trang_thai === 'da_chieu' ? 'bg-gray-500/15 text-gray-300' : '' }}
                                        {{ $suat->trang_thai === 'huy' ? 'bg-red-500/15 text-red-300' : '' }}">

                                        {{ \App\Models\SuatChieu::TRANG_THAI_LIST[$suat->trang_thai] ?? $suat->trang_thai }}

                                    </span>

                                </td>

                                <td class="px-5 py-5 align-middle">
                                    <div class="flex items-center justify-center gap-3 whitespace-nowrap">

                                        <a href="{{ route('admin.suat-chieus.show', $suat) }}"
                                            class="flex aspect-square h-10 w-10 items-center justify-center rounded-xl bg-blue-500/15 text-blue-300 transition hover:bg-blue-500/25">

                                            <i class="fa-solid fa-eye text-base leading-none"></i>

                                        </a>

                                        <a href="{{ route('admin.suat-chieus.edit', $suat) }}"
                                            class="flex aspect-square h-10 w-10 items-center justify-center rounded-xl bg-yellow-500/15 text-yellow-300 transition hover:bg-yellow-500/25">

                                            <i class="fa-solid fa-pen text-base leading-none"></i>

                                        </a>

                                        <form action="{{ route('admin.suat-chieus.destroy', $suat) }}" method="POST">

                                            @csrf

                                            @method('DELETE')

                                            <button type="submit"
                                                onclick="return confirm('Bạn có chắc muốn xóa suất chiếu này?')"
                                                class="flex aspect-square h-10 w-10 items-center justify-center rounded-xl bg-red-500/15 text-red-300 transition hover:bg-red-500/25">

                                                <i class="fa-solid fa-trash text-base leading-none"></i>

                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="10" class="px-5 py-16 text-center text-gray-500">

                                    Chưa có suất chiếu nào trong hệ thống

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        <div class="mt-4 flex justify-center">
            {{ $suatChieus->links() }}
        </div>

    </div>

@endsection