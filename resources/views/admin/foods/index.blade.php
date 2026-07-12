@extends('layouts.admin')

@section('page-title', 'Cấu hình Menu & Kho hàng')
@section('page-subtitle', 'Quản lý món, giá bán, tồn kho và trạng thái hiển thị')

@section('content')

    @include('admin.partials.flash')

    {{-- TOP STATS --}}
    <div class="mb-6 grid gap-4 md:grid-cols-3">
        <div class="rounded-2xl border border-white/10 bg-[#0f0f0f] p-4">
            <div class="text-sm text-gray-400">Tổng món</div>
            <div class="text-2xl font-black text-white">{{ $summary['total'] }}</div>
        </div>

        <div class="rounded-2xl border border-white/10 bg-[#0f0f0f] p-4">
            <div class="text-sm text-gray-400">Đang bán</div>
            <div class="text-2xl font-black text-green-400">{{ $summary['active'] }}</div>
        </div>

        <div class="rounded-2xl border border-white/10 bg-[#0f0f0f] p-4">
            <div class="text-sm text-gray-400">Tạm ẩn</div>
            <div class="text-2xl font-black text-red-400">{{ $summary['inactive'] }}</div>
        </div>
    </div>

    {{-- MAIN GRID --}}
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-lg font-black text-white">Danh sách món</h3>
                <p class="text-xs text-gray-500">Quản lý món ăn, nước uống và combo.</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.foods.categories.index') }}" class="btn-admin-outline">
                    Danh mục loại món
                </a>
                <a href="{{ route('admin.foods.create') }}" class="btn-admin">
                    + Thêm món mới
                </a>
            </div>
        </div>

        <div class="space-y-4">

            {{-- FILTER --}}
            <form method="GET" action="{{ route('admin.foods.index') }}"
                class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-4 grid gap-3 lg:grid-cols-4">

                {{-- Tìm kiếm --}}
                <input type="text" name="q" value="{{ request('q') }}" class="admin-input"
                    placeholder="Tìm món hoặc SKU...">

                {{-- Nhóm món --}}
                <select name="category_id" class="admin-input">
                    <option value="">Nhóm món</option>

                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>

                {{-- Trạng thái --}}
                <select name="status" class="admin-input">
                    <option value="">Trạng thái</option>

                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                        Đang bán
                    </option>

                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>
                        Tạm ẩn
                    </option>
                </select>

                {{-- Nút --}}
                <div class="flex gap-3">
                    <button type="submit"
                        class="flex-1 h-12 rounded-2xl bg-[#d99a32] font-bold text-white hover:bg-[#c98b25] transition">
                        Lọc
                    </button>

                    <a href="{{ route('admin.foods.index') }}"
                        class="flex-1 flex items-center justify-center h-12 rounded-2xl bg-white/10 text-white hover:bg-white/20 transition">
                        ⟳
                    </a>
                </div>

            </form>

            {{-- ITEMS --}}
            @forelse ($foods as $food)
                <div class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-5">

                    <div class="flex gap-4">

                        {{-- IMAGE --}}
                        <div class="h-20 w-20 rounded-2xl overflow-hidden bg-white/5 flex items-center justify-center">
                            @if ($food->image)
                                <img src="{{ asset('storage/' . (str_starts_with($food->image, 'foods/') ? $food->image : 'foods/' . $food->image)) }}"
                                    class="object-cover w-full h-full">
                            @else
                                <i class="fa-solid fa-burger text-[#d99a32] text-xl"></i>
                            @endif
                        </div>

                        {{-- INFO --}}
                        <div class="flex-1">
                            <div class="flex flex-wrap gap-2 items-center">

                                <span
                                    class="text-xs px-2 py-1 rounded-full
                                {{ $food->is_active ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400' }}">
                                    {{ $food->is_active ? 'Đang bán' : 'Tạm ẩn' }}
                                </span>

                                <span class="text-xs text-gray-400">
                                    SKU: {{ $food->sku }}
                                </span>

                                <span class="text-xs text-[#f4c56a]">
                                    {{ optional($food->category)->name }}
                                </span>

                            </div>

                            <h3 class="text-lg font-black text-white mt-1">
                                {{ $food->name }}
                            </h3>

                            @if ($food->isCombo())
                                <p class="text-sm text-gray-400 mt-1">
                                    Thành phần:
                                    <b class="text-white">
                                        {{ $food->comboItems->count() }}
                                    </b>
                                </p>
                            @else
                                <p class="text-sm text-gray-400 mt-1">
                                    Biến thể:
                                    <b class="text-white">
                                        {{ $food->variants->count() }}
                                    </b>
                                </p>
                            @endif
                        </div>

                        {{-- ACTION --}}
                        {{-- ACTION --}}
                        <div class="flex items-center gap-2">

                            <a href="{{ route('admin.foods.show', $food) }}"
                                class="h-10 w-10 flex items-center justify-center rounded-xl bg-blue-500/15 text-blue-300 hover:bg-blue-500/25 transition">
                                <i class="fa-solid fa-eye"></i>
                            </a>

                            <a href="{{ route('admin.foods.edit', $food) }}"
                                class="h-10 w-10 flex items-center justify-center rounded-xl bg-yellow-500/15 text-yellow-300 hover:bg-yellow-500/25 transition">
                                <i class="fa-solid fa-pen"></i>
                            </a>

                            <form method="POST" action="{{ route('admin.foods.destroy', $food) }}"
                                onsubmit="return confirm('Xóa món này?')">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                    class="h-10 w-10 flex items-center justify-center rounded-xl bg-red-500/15 text-red-300 hover:bg-red-500/25 transition">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>

                            <form method="POST" action="{{ route('admin.foods.toggle-status', $food) }}">
                                @csrf
                                @method('PATCH')

                                <button
                                    class="h-10 w-10 flex items-center justify-center rounded-xl transition
        {{ $food->is_active
            ? 'bg-red-500/15 text-red-300 hover:bg-red-500/25'
            : 'bg-green-500/15 text-green-300 hover:bg-green-500/25' }}"
                                    title="{{ $food->is_active ? 'Ẩn món' : 'Hiện món' }}">

                                    @if ($food->is_active)
                                        <i class="fa-solid fa-eye-slash"></i>
                                    @else
                                        <i class="fa-solid fa-eye"></i>
                                    @endif

                                </button>
                            </form>
                        </div>

                    </div>

                </div>
            @empty
                <div class="text-center text-gray-400 p-10">
                    Chưa có món nào
                </div>
            @endforelse

            <div>
                {{ $foods->links() }}
            </div>

        </div>
    </div>

@endsection
