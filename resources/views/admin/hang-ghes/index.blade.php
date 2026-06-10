@extends('layouts.admin')

@section('page-title', 'Quản lý Hàng Ghế')

@section('content')

    <div class="admin-panel">

        <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

            <div>

                <h5 class="text-2xl font-black text-white">
                    Danh sách hàng ghế
                </h5>

                <small class="text-gray-400">
                    Quản lý toàn bộ hàng ghế trong hệ thống
                </small>

            </div>

            <a href="{{ route('admin.hang-ghes.create') }}"
                class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 py-3 text-sm font-bold text-white shadow-lg transition hover:scale-[1.02]">

                <i class="fa-solid fa-plus"></i>

                Thêm hàng ghế

            </a>

        </div>

        <form method="GET" action="{{ route('admin.hang-ghes.index') }}" class="mt-6 flex flex-wrap items-center gap-3">

            <select name="phong_chieu_id"
                class="h-12 rounded-2xl border border-white/10 bg-[#151515] px-4 text-white outline-none focus:border-[#d99a32]"
                onchange="this.form.submit()">

                <option value="">-- Tất cả Phòng Chiếu --</option>

                @foreach ($phongChieus as $phong)
                    <option value="{{ $phong->id }}"
                        {{ request('phong_chieu_id') == $phong->id ? 'selected' : '' }}>
                        {{ $phong->ten_phong }} - {{ $phong->rapChieuPhim->ten_rap ?? '' }}
                    </option>
                @endforeach

            </select>

        </form>

        <div class="mt-6 overflow-hidden rounded-3xl border border-white/10">

            <div class="overflow-x-auto">

                <table class="w-full min-w-[800px] text-left">

                    <thead class="bg-white/5 text-xs uppercase tracking-wider text-gray-400">

                        <tr>

                            <th class="px-5 py-4">STT</th>

                            <th class="px-5 py-4">Tên Hàng</th>

                            <th class="px-5 py-4">Phòng Chiếu</th>

                            <th class="px-5 py-4">Rạp</th>

                            <th class="px-5 py-4">Số Ghế</th>

                            <th class="px-5 py-4 text-right">Hành động</th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-white/5">

                        @forelse ($hangGhes as $key => $hang)
                            <tr class="bg-[#0f0f0f] transition hover:bg-white/5">

                                <td class="px-5 py-5 text-gray-400">

                                    #{{ $hangGhes->firstItem() + $key }}

                                </td>

                                <td class="px-5 py-5 text-white font-bold">

                                    {{ $hang->ten_hang }}

                                </td>

                                <td class="px-5 py-5 text-gray-300">

                                    {{ $hang->phongChieu->ten_phong ?? 'N/A' }}

                                </td>

                                <td class="px-5 py-5 text-gray-300">

                                    {{ $hang->phongChieu->rapChieuPhim->ten_rap ?? 'N/A' }}

                                </td>

                                <td class="px-5 py-5 text-gray-300">

                                    {{ $hang->gheNgois->count() }} ghế

                                </td>

                                <td class="px-5 py-5 align-middle">
                                    <div class="flex items-center justify-center gap-3 whitespace-nowrap">

                                        <a href="{{ route('admin.hang-ghes.show', $hang) }}"
                                            class="flex aspect-square h-10 w-10 items-center justify-center rounded-xl bg-blue-500/15 text-blue-300 transition hover:bg-blue-500/25">

                                            <i class="fa-solid fa-eye text-base leading-none"></i>

                                        </a>

                                        <a href="{{ route('admin.hang-ghes.edit', $hang) }}"
                                            class="flex aspect-square h-10 w-10 items-center justify-center rounded-xl bg-yellow-500/15 text-yellow-300 transition hover:bg-yellow-500/25">

                                            <i class="fa-solid fa-pen text-base leading-none"></i>

                                        </a>

                                        <form action="{{ route('admin.hang-ghes.destroy', $hang) }}" method="POST">

                                            @csrf

                                            @method('DELETE')

                                            <button type="submit"
                                                onclick="return confirm('Bạn có chắc muốn xóa hàng ghế này?')"
                                                class="flex aspect-square h-10 w-10 items-center justify-center rounded-xl bg-red-500/15 text-red-300 transition hover:bg-red-500/25">

                                                <i class="fa-solid fa-trash text-base leading-none"></i>

                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="6" class="px-5 py-16 text-center text-gray-500">

                                    Chưa có hàng ghế nào trong hệ thống

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        <div class="mt-4 flex justify-center">
            {{ $hangGhes->links() }}
        </div>

    </div>

@endsection