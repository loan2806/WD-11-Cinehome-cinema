@extends('layouts.admin')

@section('page-title', 'Quản lý danh mục đồ ăn')

@section('content')

<div class="admin-panel">

    {{-- HEADER --}}
    <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

        <div>
            <h5 class="text-2xl font-black text-white">
                Danh sách danh mục
            </h5>

            <small class="text-gray-400">
                Quản lý danh mục đồ ăn, đồ uống và combo
            </small>
        </div>

        <a href="{{ route('admin.foods.categories.create') }}"
            class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 py-3 text-sm font-bold text-white shadow-lg transition hover:scale-[1.02]">

            <i class="fa-solid fa-plus"></i>
            Thêm danh mục
        </a>

    </div>

    {{-- SEARCH --}}
    <form action="{{ route('admin.foods.categories.index') }}"
        method="GET"
        class="mt-6 flex flex-col gap-3 lg:flex-row lg:items-end">

        <div class="flex-1">
            <label class="block text-xs font-semibold uppercase text-gray-400 mb-2">
                Tìm kiếm danh mục
            </label>

            <input type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="VD: Đồ ăn , Nước uống ..."
                class="w-full rounded-xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">
        </div>

        <button type="submit"
            class="rounded-xl bg-[#d99a32] px-6 py-3 font-semibold text-black transition hover:bg-[#e6a940]">
            <i class="fa-solid fa-search mr-2"></i>Tìm kiếm
        </button>

        <a href="{{ route('admin.foods.categories.index') }}"
            class="rounded-xl border border-white/10 bg-white/5 px-6 py-3 font-semibold text-white transition hover:bg-white/10">
            <i class="fa-solid fa-rotate-right mr-2"></i>Đặt lại
        </a>

    </form>

    {{-- TABLE --}}
    <div class="mt-6 overflow-hidden rounded-3xl border border-white/10">

        <div class="overflow-x-auto">

            <table class="w-full min-w-[900px] text-left">

                <thead class="bg-white/4 text-xs uppercase tracking-wider text-gray-400">

                    <tr>
                        <th class="px-5 py-4">STT</th>
                        <th class="px-5 py-4">Tên danh mục</th>
                        <th class="px-5 py-4 text-center">Trạng thái</th>
                        <th class="px-5 py-4 text-right">Hành động</th>
                    </tr>

                </thead>

                <tbody class="divide-y divide-white/5">

                    @forelse($categories as $key => $category)

                        <tr class="bg-[#0f0f0f] transition hover:bg-white/5">

                            {{-- STT --}}
                            <td class="px-5 py-5 text-gray-400">
                                #{{ ($categories->currentPage() - 1) * $categories->perPage() + $key + 1 }}
                            </td>

                            {{-- TÊN --}}
                            <td class="px-5 py-5">

                                <div class="font-bold text-white">
                                    {{ $category->name }}
                                </div>

                            </td>


                            {{-- TRẠNG THÁI --}}
                            <td class="px-5 py-5 text-center">

                                <span class="rounded-full bg-green-500/20 px-3 py-1 text-xs font-bold text-green-300">
                                    Hoạt động
                                </span>

                            </td>

                            {{-- ACTION --}}
                            <td class="px-5 py-5">

                                <div class="flex items-center justify-end gap-3 whitespace-nowrap">

                                    <a href="{{ route('admin.foods.categories.edit',$category) }}"
                                        class="flex aspect-square h-10 w-10 items-center justify-center rounded-xl bg-yellow-500/15 text-yellow-300 transition hover:bg-yellow-500/25"
                                        title="Chỉnh sửa">

                                        <i class="fa-solid fa-pen"></i>

                                    </a>

                                    <form action="{{ route('admin.foods.categories.destroy',$category) }}"
                                        method="POST">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                            onclick="return confirm('Bạn có chắc muốn xóa danh mục này?')"
                                            class="flex aspect-square h-10 w-10 items-center justify-center rounded-xl bg-red-500/15 text-red-300 transition hover:bg-red-500/25"
                                            title="Xóa">

                                            <i class="fa-solid fa-trash"></i>

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="5"
                                class="px-5 py-16 text-center text-gray-500">

                                <i class="fa-solid fa-inbox mr-2"></i>

                                Chưa có danh mục nào.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

    @include('components.admin-pagination',[
        'paginator'=>$categories
    ])

</div>

@endsection