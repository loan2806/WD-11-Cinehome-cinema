@extends('layouts.admin')

@section('page-title', 'Quản lý danh mục món lẻ')
@section('page-subtitle', 'Quản lý nhóm danh mục chỉ dùng cho đồ ăn lẻ')

@section('content')
<div class="admin-panel">
    <div class="panel-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h5 class="text-2xl font-black text-white">Quản lý danh mục món lẻ</h5>
            <small class="text-gray-400">Danh sách danh mục dùng cho thức ăn lẻ.</small>
        </div>
        <a href="{{ route('admin.foods.categories.create') }}" class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#8a4a21] to-[#d99a32] px-5 py-3 text-sm font-bold text-white shadow-lg transition hover:scale-[1.02]">
            <i class="fa-solid fa-plus"></i>
            Thêm danh mục
        </a>
    </div>

    <form action="{{ route('admin.foods.categories.index') }}" method="GET" class="mt-6 flex flex-col gap-3 lg:flex-row lg:items-end">
        <div class="flex-1">
            <label class="block text-xs font-semibold uppercase text-gray-400 mb-2">Tìm kiếm danh mục</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm tên danh mục..." class="w-full rounded-xl border border-white/10 bg-[#151515] px-4 py-3 text-white outline-none focus:border-[#d99a32]">
        </div>
        <button type="submit" class="rounded-xl bg-[#d99a32] px-6 py-3 font-semibold text-black transition hover:bg-[#e6a940]"><i class="fa-solid fa-search mr-2"></i>Tìm kiếm</button>
        <a href="{{ route('admin.foods.categories.index') }}" class="rounded-xl border border-white/10 bg-white/5 px-6 py-3 font-semibold text-white transition hover:bg-white/10"><i class="fa-solid fa-rotate-right mr-2"></i>Đặt lại</a>
    </form>

    <div class="mt-6 overflow-hidden rounded-3xl border border-white/10">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] text-left">
                <thead class="bg-white/4 text-xs uppercase tracking-wider text-gray-400">
                    <tr>
                        <th class="px-5 py-4">STT</th>
                        <th class="px-5 py-4">Tên danh mục</th>
                        <th class="px-5 py-4">Loại</th>
                        <th class="px-5 py-4 text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($categories as $category)
                        <tr class="bg-[#0f0f0f] transition hover:bg-white/5">
                            <td class="px-5 py-5 text-sm font-medium text-gray-200">{{ $loop->iteration + ($categories->currentPage() - 1) * $categories->perPage() }}</td>
                            <td class="px-5 py-5 text-white">{{ $category->name }}</td>
                            <td class="px-5 py-5">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $category->is_combo ? 'bg-purple-500/20 text-purple-300' : 'bg-green-500/20 text-green-300' }}">
                                    {{ $category->is_combo ? 'Combo' : 'Đồ ăn lẻ' }}
                                </span>
                            </td>
                            <td class="px-5 py-5 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.foods.categories.edit', $category) }}" class="rounded-xl border border-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/5">Sửa</a>
                                    <form action="{{ route('admin.foods.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Xóa danh mục này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-xl bg-red-500/15 px-4 py-2 text-sm font-semibold text-red-300 transition hover:bg-red-500/25">Xóa</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-16 text-center text-gray-500">
                                <i class="fa-solid fa-inbox mr-2"></i>
                                Chưa có danh mục nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('components.admin-pagination', ['paginator' => $categories])
</div>
@endsection
