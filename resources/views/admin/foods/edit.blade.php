@extends('layouts.admin')

@section('page-title', 'Chỉnh sửa món')
@section('page-subtitle', 'Cập nhật thông tin món và biến thể')

@section('content')

    @include('admin.partials.flash')

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-5">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-lg font-black text-white">Chỉnh sửa {{ $food->name }}</h3>
                </div>
                <a href="{{ route('admin.foods.index') }}" class="btn-admin-outline">Quay lại danh sách</a>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.foods.update', $food) }}" enctype="multipart/form-data"
            class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-6 space-y-6">
            @csrf
            @method('PATCH')

            <div class="grid gap-4 lg:grid-cols-2">
                <div>
                    <label class="text-xs uppercase tracking-wider text-gray-400">Tên sản phẩm</label>
                    <input name="name"
                        value="{{ old('name') !== null && old('name') !== '' ? old('name') : $food->name }}"
                        class="admin-input" placeholder="Tên sản phẩm">
                    @error('name')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label class="text-xs uppercase tracking-wider text-gray-400">Danh mục</label>
                    <select class="admin-input" disabled>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ $food->category_id == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-xs text-yellow-400">
                        Danh mục không thể thay đổi sau khi tạo món.
                    </p>

                    <input type="hidden" name="category_id" value="{{ $food->category_id }}">
                    @error('category_id')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label class="text-xs uppercase tracking-wider text-gray-400 mb-2 block">Ảnh sản phẩm</label>
                    <label for="image"
                        class="group relative flex h-56 w-full cursor-pointer items-center justify-center rounded-2xl border-2 border-dashed border-white/20 bg-[#181818] hover:border-red-500 transition overflow-hidden">
                        <div id="preview-container" class="absolute inset-0 hidden">
                            <img id="preview-image" class="h-full w-full object-cover">
                        </div>

                        @if ($food->image)
                            <img id="current-image" src="{{ asset('storage/' . $food->image) }}"
                                class="absolute inset-0 h-full w-full object-cover rounded-2xl">
                        @else
                            <div id="upload-placeholder"
                                class="relative flex flex-col items-center text-gray-400 group-hover:text-red-500 transition">
                                <div class="text-6xl font-light leading-none">+</div>
                                <p class="mt-2 text-sm">Chọn ảnh sản phẩm</p>
                            </div>
                        @endif

                        <input id="image" type="file" name="image" accept="image/*" class="hidden">
                    </label>
                    @error('image')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div>
                <label class="text-xs uppercase tracking-wider text-gray-400">Mô tả</label>
                <textarea name="description" class="admin-input" rows="5" placeholder="Mô tả sản phẩm">{{ old('description') !== null && old('description') !== '' ? old('description') : $food->description }}</textarea>
                @error('description')
                    <small class="text-red-500">{{ $message }}</small>
                @enderror
            </div>

            @if (!str_contains(strtolower(optional($food->category)->name), 'combo'))
                <div class="rounded-3xl border border-white/10 bg-white/5 p-4 space-y-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h4 class="text-sm font-black text-white">Biến thể</h4>
                            <p class="text-xs text-gray-400">Thêm hoặc sửa các biến thể đã tạo.</p>
                        </div>
                        <a href="{{ route('admin.foods.variants.create', $food) }}"
                            class="btn-admin-outline text-xs px-3 py-2">
                            Thêm biến thể
                        </a>
                    </div>

                    @if ($food->variants->isEmpty())
                        <div class="rounded-3xl border border-dashed border-white/10 p-4 text-gray-400">
                            Chưa có biến thể nào.
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach ($food->variants as $variant)
                                <div
                                    class="rounded-3xl border border-white/10 bg-[#181818] p-4 grid gap-3 sm:grid-cols-[1fr_auto] items-center">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2 mb-2">
                                            <span
                                                class="rounded-full bg-[#d99a32]/20 px-3 py-1 text-xs font-bold text-[#f4c56a]">{{ $variant->value }}</span>
                                            <span
                                                class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-gray-300">{{ number_format($variant->price) }}đ</span>
                                            <span
                                                class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-gray-300">Tồn
                                                kho: {{ $variant->stock_quantity }}</span>
                                        </div>
                                        <p class="text-xs text-gray-400">
                                            Trạng thái: {{ $variant->is_active ? 'Đang bán' : 'Tạm ẩn' }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.foods.variants.edit', [$food, $variant]) }}"
                                            class="btn-admin-outline text-xs px-3 py-2">Sửa</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            @if (str_contains(strtolower(optional($food->category)->name), 'combo'))

                <div class="rounded-3xl border border-white/10 bg-white/5 p-4 space-y-4">

                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-black text-white">
                                Thành phần combo
                            </h4>

                            <p class="text-xs text-gray-400">
                                Các biến thể có trong combo.
                            </p>
                        </div>


                    </div>

                    <div id="combo-item-list" class="space-y-3">
                        @php
                            $comboItems = collect(
                                old(
                                    'combo_items',
                                    $food->comboItems
                                        ->map(function ($item) {
                                            return [
                                                'food_variant_id' => $item->food_variant_id,
                                                'quantity' => $item->quantity,
                                            ];
                                        })
                                        ->toArray(),
                                ),
                            )->map(function ($item) {
                                return [
                                    'food_variant_id' => $item['food_variant_id'] ?? null,
                                    'quantity' => $item['quantity'] ?? 1,
                                ];
                            });
                        @endphp

                        @forelse ($comboItems as $index => $item)
                            <div class="grid grid-cols-12 gap-3 combo-row">

                                {{-- VARIANT --}}
                                <div class="col-span-8">
                                    <select name="combo_items[{{ $index }}][variant_id]"
                                        class="admin-input
                    @error("combo_items.$index.variant_id") border-red-500 ring-2 ring-red-500 @enderror">
                                        @foreach ($variants as $variant)
                                            <option value="{{ $variant->id }}"
                                                {{ $variant->id == ($item['food_variant_id'] ?? null) ? 'selected' : '' }}>
                                                {{ $variant->food->name }} ({{ $variant->value }})
                                            </option>
                                        @endforeach
                                    </select>

                                    @error("combo_items.$index.variant_id")
                                        <small class="text-red-500 block mt-1">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- QUANTITY --}}
                                <div class="col-span-3">
                                    <input type="number" min="1" name="combo_items[{{ $index }}][quantity]"
                                        value="{{ $item['quantity'] }}"
                                        class="admin-input
                    @error("combo_items.$index.quantity") border-red-500 ring-2 ring-red-500 @enderror">

                                    @error("combo_items.$index.quantity")
                                        <small class="text-red-500 block mt-1">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- REMOVE --}}
                                <div class="col-span-1 flex items-end">
                                    <button type="button" class="remove-combo-row btn-admin-outline w-full">
                                        ✕
                                    </button>
                                </div>

                            </div>
                        @empty
                            <div class="rounded-3xl border border-dashed border-white/10 p-4 text-gray-400">
                                Combo chưa có thành phần.
                            </div>
                        @endforelse

                    </div>

                    <button type="button" id="add-combo-item" class="btn-admin-outline">
                        + Thêm dòng
                    </button>

                    <template id="combo-item-template">

                        <div class="grid grid-cols-12 gap-3 combo-row">

                            <div class="col-span-8">

                                <select name="combo_items[__index__][variant_id]" class="admin-input">

                                    @foreach ($variants as $variant)
                                        <option value="{{ $variant->id }}">
                                            {{ $variant->food->name }}
                                            ({{ $variant->value }})
                                        </option>
                                    @endforeach

                                </select>

                            </div>

                            <div class="col-span-3">

                                <input type="number" min="1" value="1"
                                    name="combo_items[__index__][quantity]" class="admin-input">

                            </div>

                            <div class="col-span-1 flex items-end">

                                <button type="button" class="remove-combo-row btn-admin-outline w-full">

                                    ✕

                                </button>

                            </div>

                        </div>

                    </template>
                </div>

            @endif

            <input type="hidden" name="is_active" value="0">
            <label class="flex items-center gap-2 text-sm text-gray-300">
                <input type="checkbox" name="is_active" value="1"
                    {{ old('is_active', $food->is_active) ? 'checked' : '' }}>
                Hiển thị menu
            </label>

            <button class="btn-admin w-full">Lưu thay đổi</button>
        </form>
    </div>

@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {

    const addBtn = document.getElementById('add-combo-item');
    if (!addBtn) return;

    const list = document.getElementById('combo-item-list');
    const template = document.getElementById('combo-item-template').innerHTML;

    let index = Date.now();

    addBtn.addEventListener('click', function () {
        let html = template.replaceAll('__index__', index);
        list.insertAdjacentHTML('beforeend', html);
        index++;
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-combo-row')) {
            e.target.closest('.combo-row').remove();
        }
    });

});
</script>
