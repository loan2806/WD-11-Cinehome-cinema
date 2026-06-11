@extends('layouts.admin')

@section('page-title', 'Quản lý Thể Loại Phim')

@section('content')

    <div class="admin-panel">

        <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

            <div>

                <h5 class="text-2xl font-black text-white">
                    Danh sách thể loại phim
                </h5>

                <small class="text-gray-400">
                    Quản lý toàn bộ thể loại phim trong hệ thống
                </small>

            </div>

            <a href="{{ route('admin.the-loais.create') }}"
                class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 py-3 text-sm font-bold text-white shadow-lg transition hover:scale-[1.02]">

                <i class="fa-solid fa-plus"></i>

                Thêm thể loại

            </a>

        </div>

        {{-- SEARCH & FILTER --}}
        <form action="{{ route('admin.the-loais.index') }}" method="GET"
            class="mt-6 flex flex-col gap-3 lg:flex-row lg:items-end">

            <div class="flex-1">
                <label class="block text-xs font-semibold uppercase text-gray-400 mb-2">Tìm kiếm tên thể loại</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="VD: Hành động, Kinh dị..."
                    class="w-full rounded-xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">
            </div>

            <div class="w-full lg:w-48">
                <label class="block text-xs font-semibold uppercase text-gray-400 mb-2">Trạng thái</label>
                <select name="status"
                    class="w-full rounded-xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">
                    <option value="">-- Tất cả --</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Kích hoạt</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Vô hiệu hóa</option>
                </select>
            </div>

            <button type="submit"
                class="rounded-xl bg-[#d99a32] px-6 py-3 font-semibold text-black transition hover:bg-[#e6a940]">
                <i class="fa-solid fa-search mr-2"></i>Tìm kiếm
            </button>

            <a href="{{ route('admin.the-loais.index') }}"
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

                            <th class="px-5 py-4">Tên Thể Loại</th>

                            <th class="px-5 py-4">Mô Tả</th>

                            <th class="px-5 py-4">Số Phim</th>

                            <th class="px-5 py-4">Trạng Thái</th>

                            <th class="px-5 py-4 text-right">Hành Động</th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-white/5">

                        @forelse ($theLoais as $key => $theLoai)
                            <tr class="bg-[#0f0f0f] transition hover:bg-white/5">

                                <td class="px-5 py-5 text-gray-400">

                                    #{{ ($theLoais->currentPage() - 1) * $theLoais->perPage() + $key + 1 }}

                                </td>

                                <td class="px-5 py-5 text-white font-bold">

                                    {{ $theLoai->ten_the_loai }}

                                </td>

                                <td class="px-5 py-5 text-gray-300">

                                    {{ Str::limit($theLoai->mo_ta ?? '-', 50) }}

                                </td>

                                <td class="px-5 py-5">

                                    <span
                                        class="rounded-full {{ $theLoai->phims_count > 0 ? 'bg-blue-500/15 text-blue-300' : 'bg-gray-500/15 text-gray-300' }} px-3 py-1 text-xs font-medium">
                                        {{ $theLoai->phims_count }}
                                    </span>

                                </td>

                                <td class="px-5 py-5">

                                    @if ($theLoai->trang_thai)
                                        <span
                                            class="rounded-full bg-green-500/15 px-3 py-1 text-xs font-medium text-green-300">
                                            <i class="fa-solid fa-check-circle mr-1"></i>Kích hoạt
                                        </span>
                                    @else
                                        <span
                                            class="rounded-full bg-gray-500/15 px-3 py-1 text-xs font-medium text-gray-300">
                                            <i class="fa-solid fa-ban mr-1"></i>Vô hiệu
                                        </span>
                                    @endif

                                </td>

                                <td class="px-5 py-5 align-middle">

                                    <div class="flex items-center justify-end gap-3 whitespace-nowrap">

                                        <a href="{{ route('admin.the-loais.edit', $theLoai) }}"
                                            class="flex aspect-square h-10 w-10 items-center justify-center rounded-xl bg-yellow-500/15 text-yellow-300 transition hover:bg-yellow-500/25"
                                            title="Chỉnh sửa">

                                            <i class="fa-solid fa-pen text-base leading-none"></i>

                                        </a>

                                        <form action="{{ route('admin.the-loais.destroy', $theLoai) }}" method="POST"
                                            class="inline">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                onclick="return confirm('Bạn có chắc muốn xóa thể loại này?')"
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

                                <td colspan="6" class="px-5 py-16 text-center text-gray-500">

                                    <i class="fa-solid fa-inbox mr-2"></i>Không tìm thấy thể loại phim nào

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

       @include('components.admin-pagination', ['paginator' => $theLoais])

    </div>

@endsection
