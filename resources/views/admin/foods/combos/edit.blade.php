@extends('layouts.admin')

@section('title', 'Chỉnh sửa combo')
@section('page-title', 'Chỉnh sửa combo')
@section('page-subtitle', 'Cập nhật thông tin combo, giá bán và thành phần')

@section('content')
@include('admin.partials.flash')

<div class="max-w-4xl mx-auto space-y-6">
    <div class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-lg font-black text-white">Chỉnh sửa combo</h3>
                <p class="text-sm text-gray-400">{{ $food->name }}</p>
            </div>
            <a href="{{ route('admin.foods.combos.index') }}" class="btn-admin-outline">
                Quay lại danh sách combo
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.foods.combos.update', $food) }}" enctype="multipart/form-data" class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-6 space-y-6">
        @csrf
        @method('PATCH')

        <div class="grid gap-4 lg:grid-cols-2">
            <div>
                <label class="text-xs uppercase tracking-wider text-gray-400">Tên combo</label>
                <input name="name" value="{{ old('name', $food->name) }}" class="admin-input" placeholder="Tên combo">
                @error('name')
                    <small class="text-red-500">{{ $message }}</small>
                @enderror
            </div>

            <div>
                <label class="text-xs uppercase tracking-wider text-gray-400">Danh mục</label>
                <div class="rounded-xl border border-white/10 bg-[#111111] p-4 text-white">
                    {{ optional($food->category)->name ?? 'Combo' }}
                </div>
                <input type="hidden" name="category_id" value="{{ $food->category_id }}">
                @error('category_id')
                    <small class="text-red-500">{{ $message }}</small>
                @enderror
            </div>

            <div class="lg:col-span-2">
                <label class="text-xs uppercase tracking-wider text-gray-400">Mô tả</label>
                <textarea name="description" class="admin-input" rows="4" placeholder="Mô tả combo">{{ old('description', $food->description) }}</textarea>
                @error('description')
                    <small class="text-red-500">{{ $message }}</small>
                @enderror
            </div>

            <div class="lg:col-span-2">
                <label class="text-xs uppercase tracking-wider text-gray-400 mb-2 block">Ảnh combo</label>
                <label for="image" class="group relative flex h-56 w-full cursor-pointer items-center justify-center rounded-2xl border-2 border-dashed border-white/20 bg-[#181818] hover:border-red-500 transition">
                    <div id="preview-container" class="absolute inset-0 {{ $food->image ? '' : 'hidden' }}">
                        @if($food->image)
                            @php
                                $comboImage = str_starts_with($food->image, 'foods/') ? $food->image : 'foods/' . $food->image;
                            @endphp
                            <img id="preview-image" src="{{ asset('storage/' . $comboImage) }}" class="h-full w-full rounded-2xl object-cover">
                        @endif
                    </div>
                    <div id="upload-placeholder" class="flex flex-col items-center text-gray-400 group-hover:text-red-500 transition {{ $food->image ? 'hidden' : '' }}">
                        <div class="text-6xl font-light leading-none">+</div>
                        <p class="mt-2 text-sm">Chọn ảnh combo</p>
                    </div>
                    <input id="image" type="file" name="image" accept="image/*" class="hidden">
                </label>
                @error('image')
                    <small class="text-red-500">{{ $message }}</small>
                @enderror
            </div>
        </div>

        <div id="combo-section" class="rounded-3xl border border-white/10 bg-white/5 p-4 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-black text-white">Thành phần combo</h4>
                    <p class="text-xs text-gray-400">Chỉnh sửa danh sách biến thể kèm số lượng.</p>
                </div>
                <button type="button" id="add-combo-item" class="btn-admin-outline text-xs px-3 py-2">+ Thêm dòng</button>
            </div>

            <div id="combo-item-list" class="space-y-3">
                @php
                    $comboItems = old('combo_items', $food->comboItems->map(fn ($item) => [
                        'variant_id' => $item->food_variant_id,
                        'quantity' => $item->quantity,
                    ])->toArray());
                    if (empty($comboItems)) {
                        $comboItems = [
                            ['variant_id' => '', 'quantity' => 1],
                            ['variant_id' => '', 'quantity' => 1],
                        ];
                    }
                @endphp

                @foreach ($comboItems as $index => $item)
                    <div class="grid grid-cols-12 gap-3 combo-row">
                        <div class="col-span-8">
                            <label class="text-xs uppercase tracking-wider text-gray-400">Chọn biến thể</label>
                            <select name="combo_items[{{ $index }}][variant_id]" class="admin-input">
                                <option value="">-- Chọn biến thể --</option>
                                @foreach ($variants as $variant)
                                    <option value="{{ $variant->id }}" @selected(old("combo_items.$index.variant_id", $item['variant_id']) == $variant->id)>
                                        {{ $variant->doAn->name }} ({{ $variant->value ?: 'Mặc định' }})
                                    </option>
                                @endforeach
                            </select>
                            @error("combo_items.$index.variant_id")
                                <small class="text-red-500">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-span-3">
                            <label class="text-xs uppercase tracking-wider text-gray-400">SL</label>
                            <input type="number" min="1" name="combo_items[{{ $index }}][quantity]" value="{{ old("combo_items.$index.quantity", $item['quantity']) }}" class="admin-input">
                            @error("combo_items.$index.quantity")
                                <small class="text-red-500">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-span-1 flex items-end">
                            @if ($index > 0)
                                <button type="button" class="remove-combo-row btn-admin-outline w-full">✕</button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                <div>
                    <label class="text-xs uppercase tracking-wider text-gray-400">Giá combo</label>
                    <input type="number" name="price" min="0" value="{{ old('price', $food->price) }}" class="admin-input" placeholder="Nhập giá combo">
                    @error('price')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>
                <div class="flex items-end">
                    <label class="flex items-center gap-2 text-sm text-gray-300 w-full">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $food->is_active) ? 'checked' : '' }}>
                        Hiển thị combo
                    </label>
                </div>
            </div>

            <template id="combo-item-template">
                <div class="grid grid-cols-12 gap-3 combo-row">
                    <div class="col-span-8">
                        <label class="text-xs uppercase tracking-wider text-gray-400">Chọn biến thể</label>
                        <select name="combo_items[__index__][variant_id]" class="admin-input">
                            <option value="">-- Chọn biến thể --</option>
                            @foreach ($variants as $variant)
                                <option value="{{ $variant->id }}">{{ $variant->doAn->name }} ({{ $variant->value ?: 'Mặc định' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-3">
                        <label class="text-xs uppercase tracking-wider text-gray-400">SL</label>
                        <input type="number" min="1" value="1" name="combo_items[__index__][quantity]" class="admin-input">
                    </div>
                    <div class="col-span-1 flex items-end">
                        <button type="button" class="remove-combo-row btn-admin-outline w-full">✕</button>
                    </div>
                </div>
            </template>
        </div>

        <button class="btn-admin w-full">Cập nhật combo</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const imageInput = document.getElementById('image');
        const previewImage = document.getElementById('preview-image');
        const previewContainer = document.getElementById('preview-container');
        const uploadPlaceholder = document.getElementById('upload-placeholder');

        if (imageInput) {
            imageInput.addEventListener('change', function () {
                const file = imageInput.files && imageInput.files[0];
                if (!file) {
                    return;
                }
                previewImage.src = URL.createObjectURL(file);
                previewContainer.classList.remove('hidden');
                uploadPlaceholder.classList.add('hidden');
            });
        }

        const addButton = document.getElementById('add-combo-item');
        const list = document.getElementById('combo-item-list');
        const template = document.getElementById('combo-item-template');

        if (!addButton || !list || !template) {
            return;
        }

        let index = list.querySelectorAll('.combo-row').length || 0;

        addButton.addEventListener('click', function () {
            const html = template.innerHTML.replaceAll('__index__', index);
            list.insertAdjacentHTML('beforeend', html);
            index += 1;
            updateRemoveButtons();
        });

        list.addEventListener('click', function (event) {
            const button = event.target.closest('.remove-combo-row');
            if (!button) {
                return;
            }
            button.closest('.combo-row').remove();
            updateRemoveButtons();
        });

        function updateRemoveButtons() {
            const buttons = list.querySelectorAll('.remove-combo-row');
            buttons.forEach(function (button) {
                button.classList.toggle('hidden', list.querySelectorAll('.combo-row').length <= 2);
            });
        }

        updateRemoveButtons();
    });
</script>
@endpush
