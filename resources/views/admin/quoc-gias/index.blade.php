@extends('layouts.admin')

@section('page-title', 'Quản lý Quốc Gia Phim')

@section('content')

<div class="admin-panel">

    {{-- HEADER --}}
    <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

        <div>
            <h5 class="text-2xl font-black text-white">
                Danh sách quốc gia
            </h5>

            <small class="text-gray-400">
                Quản lý toàn bộ quốc gia phim trong hệ thống
            </small>
        </div>

        <a href="{{ route('admin.quoc-gias.create') }}"
           class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 py-3 text-sm font-bold text-white shadow-lg transition hover:scale-[1.02]">

            <i class="fa-solid fa-plus"></i>
            Thêm quốc gia
        </a>

    </div>

    {{-- SEARCH --}}
    <form action="{{ route('admin.quoc-gias.index') }}"
          method="GET"
          class="mt-6 flex flex-col gap-3 lg:flex-row lg:items-end">

        <div class="flex-1">
            <label class="block text-xs font-semibold uppercase text-gray-400 mb-2">
                Tìm kiếm quốc gia
            </label>

            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="VD: Việt Nam, USA, Japan..."
                   class="w-full rounded-xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">
        </div>

        <button type="submit"
                class="rounded-xl bg-[#d99a32] px-6 py-3 font-semibold text-black transition hover:bg-[#e6a940]">
            <i class="fa-solid fa-search mr-2"></i>Tìm kiếm
        </button>

        <a href="{{ route('admin.quoc-gias.index') }}"
           class="rounded-xl border border-white/10 bg-white/5 px-6 py-3 font-semibold text-white transition hover:bg-white/10">
            <i class="fa-solid fa-redo mr-2"></i>Đặt lại
        </a>

    </form>

    {{-- TABLE --}}
    <div class="mt-6 overflow-hidden rounded-3xl border border-white/10">

        <div class="overflow-x-auto">

            <table class="w-full min-w-[800px] text-left">

                <thead class="bg-white/5 text-xs uppercase tracking-wider text-gray-400">

                    <tr>
                        <th class="px-5 py-4">STT</th>
                        <th class="px-5 py-4">Tên quốc gia</th>
                        <th class="px-5 py-4">Mã quốc gia</th>
                        <th class="px-5 py-4 text-right">Hành động</th>
                    </tr>

                </thead>

                <tbody class="divide-y divide-white/5">

                    @forelse ($countries as $key => $country)

                        <tr class="bg-[#0f0f0f] transition hover:bg-white/5">

                            {{-- STT --}}
                            <td class="px-5 py-5 text-gray-400">
                                #{{ ($countries->currentPage() - 1) * $countries->perPage() + $key + 1 }}
                            </td>

                            {{-- TÊN --}}
                            <td class="px-5 py-5 text-white font-bold">
                                {{ $country->ten_quoc_gia }}
                            </td>

                            {{-- MÃ --}}
                            <td class="px-5 py-5 text-gray-300">
                                {{ $country->ma_quoc_gia ?? '-' }}
                            </td>

                            {{-- ACTION --}}
                            <td class="px-5 py-5">

                                <div class="flex items-center justify-end gap-3 whitespace-nowrap">

                                    {{-- EDIT --}}
                                    <a href="{{ route('admin.quoc-gias.edit', $country) }}"
                                       class="flex aspect-square h-10 w-10 items-center justify-center rounded-xl bg-yellow-500/15 text-yellow-300 transition hover:bg-yellow-500/25"
                                       title="Chỉnh sửa">

                                        <i class="fa-solid fa-pen text-base"></i>

                                    </a>

                                    {{-- DELETE --}}
                                    <form action="{{ route('admin.quoc-gias.destroy', $country) }}"
                                          method="POST"
                                          class="inline">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                onclick="return confirm('Bạn có chắc muốn xóa quốc gia này?')"
                                                class="flex aspect-square h-10 w-10 items-center justify-center rounded-xl bg-red-500/15 text-red-300 transition hover:bg-red-500/25"
                                                title="Xóa">

                                            <i class="fa-solid fa-trash text-base"></i>

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="4" class="px-5 py-16 text-center text-gray-500">
                                <i class="fa-solid fa-inbox mr-2"></i>
                                Không có quốc gia nào
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

@include('components.admin-pagination', ['paginator' => $countries])

</div>

@endsection