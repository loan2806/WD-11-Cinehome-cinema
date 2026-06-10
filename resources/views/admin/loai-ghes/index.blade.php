@extends('layouts.admin')

@section('page-title', 'Quản lý Loại Ghế')

@section('content')

    <div class="admin-panel">

        <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

            <div>

                <h5 class="text-2xl font-black text-white">
                    Danh sách loại ghế
                </h5>

                <small class="text-gray-400">
                    Quản lý toàn bộ loại ghế trong hệ thống
                </small>

            </div>

            <a href="{{ route('admin.loai-ghes.create') }}"
                class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 py-3 text-sm font-bold text-white shadow-lg transition hover:scale-[1.02]">

                <i class="fa-solid fa-plus"></i>

                Thêm loại ghế

            </a>

        </div>

        <div class="mt-6 overflow-hidden rounded-3xl border border-white/10">

            <div class="overflow-x-auto">

                <table class="w-full min-w-[800px] text-left">

                    <thead class="bg-white/5 text-xs uppercase tracking-wider text-gray-400">

                        <tr>

                            <th class="px-5 py-4">STT</th>

                            <th class="px-5 py-4">Màu</th>

                            <th class="px-5 py-4">Tên Loại</th>

                            <th class="px-5 py-4">Mô tả</th>

                            <th class="px-5 py-4">Phụ Thu</th>

                            <th class="px-5 py-4">Số Ghế Đang Dùng</th>

                            <th class="px-5 py-4 text-right">Hành động</th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-white/5">

                        @forelse ($loaiGhes as $key => $loai)
                            <tr class="bg-[#0f0f0f] transition hover:bg-white/5">

                                <td class="px-5 py-5 text-gray-400">

                                    #{{ $key + 1 }}

                                </td>

                                <td class="px-5 py-5">

                                    <span class="inline-block h-7 w-7 rounded-lg border border-white/15 shadow-sm" style="background-color: {{ $loai->mau_sac ?? '#666666' }};"></span>

                                </td>

                                <td class="px-5 py-5 text-white font-bold">

                                    {{ $loai->ten_loai }}

                                </td>

                                <td class="px-5 py-5 text-gray-300">

                                    {{ $loai->mo_ta ?? '-' }}

                                </td>

                                <td class="px-5 py-5 text-gray-300">

                                    {{ number_format($loai->phu_thu) }}đ

                                </td>

                                <td class="px-5 py-5">

                                    <span class="rounded-full bg-blue-500/15 px-3 py-1 text-xs font-medium text-blue-300">
                                        {{ $loai->ghe_ngois_count }}
                                    </span>

                                </td>

                                <td class="px-5 py-5 align-middle">
                                    <div class="flex items-center justify-center gap-3 whitespace-nowrap">

                                        <a href="{{ route('admin.loai-ghes.show', $loai) }}"
                                            class="flex aspect-square h-10 w-10 items-center justify-center rounded-xl bg-blue-500/15 text-blue-300 transition hover:bg-blue-500/25">

                                            <i class="fa-solid fa-eye text-base leading-none"></i>

                                        </a>

                                        <a href="{{ route('admin.loai-ghes.edit', $loai) }}"
                                            class="flex aspect-square h-10 w-10 items-center justify-center rounded-xl bg-yellow-500/15 text-yellow-300 transition hover:bg-yellow-500/25">

                                            <i class="fa-solid fa-pen text-base leading-none"></i>

                                        </a>

                                        <form action="{{ route('admin.loai-ghes.destroy', $loai) }}" method="POST">

                                            @csrf

                                            @method('DELETE')

                                            <button type="submit"
                                                onclick="return confirm('Bạn có chắc muốn xóa loại ghế này?')"
                                                class="flex aspect-square h-10 w-10 items-center justify-center rounded-xl bg-red-500/15 text-red-300 transition hover:bg-red-500/25"
                                                {{ $loai->ghe_ngois_count > 0 ? 'disabled' : '' }}>

                                                <i class="fa-solid fa-trash text-base leading-none"></i>

                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="6" class="px-5 py-16 text-center text-gray-500">

                                    Chưa có loại ghế nào trong hệ thống

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

@endsection
