@extends('layouts.admin')

@section('page-title', 'Thêm món lẻ mới')
@section('page-subtitle', 'Tạo món lẻ mới, quản lý giá và tồn kho qua biến thể')

@section('content')

@include('admin.partials.flash')

<div class="max-w-4xl mx-auto space-y-6">
    <div class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-lg font-black text-white">Thêm món lẻ mới</h3>
            </div>
            <a href="{{ route('admin.foods.index') }}" class="btn-admin-outline">
                Quay lại danh sách
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.foods.store') }}" enctype="multipart/form-data"
        class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-6 space-y-6">
        @csrf

        <div class="grid gap-4 lg:grid-cols-2">
            <div>
                <label class="text-xs uppercase tracking-wider text-gray-400">Tên món</label>
                <input name="name" value="{{ old('name') }}" class="admin-input" placeholder="Tên món">
                @error('name')
                    <small class="text-red-500">{{ $message }}</small>
                @enderror
            </div>

            <div>
                <label class="text-xs uppercase tracking-wider text-gray-400">Danh mục</label>
                <select name="category_id" class="admin-input">
                    <option value="">-- Chọn danh mục --</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <small class="text-red-500">{{ $message }}</small>
                @enderror
            </div>

            <div>
                <label class="text-xs uppercase tracking-wider text-gray-400 mb-2 block">
                    Ảnh món
                </label>

                <label for="image"
                    class="group relative flex h-56 w-full cursor-pointer items-center justify-center rounded-2xl border-2 border-dashed border-white/20 bg-[#181818] hover:border-red-500 transition">

                    <div id="preview-container" class="hidden absolute inset-0">
                        <img id="preview-image" class="h-full w-full rounded-2xl object-cover">
                    </div>

                    <div id="upload-placeholder"
                        class="flex flex-col items-center text-gray-400 group-hover:text-red-500 transition">
                        <div class="text-6xl font-light leading-none">+</div>
                        <p class="mt-2 text-sm">Chọn ảnh món</p>
                    </div>

                    <input id="image" type="file" name="image" accept="image/*" class="hidden">
                </label>

                @error('image')
                    <small class="text-red-500">{{ $message }}</small>
                @enderror
            </div>
        </div>

        <div>
            <label class="text-xs uppercase tracking-wider text-gray-400">Mô tả</label>
            <textarea name="description" class="admin-input" rows="5" placeholder="Mô tả món">{{ old('description') }}</textarea>
            @error('description')
                <small class="text-red-500">{{ $message }}</small>
            @enderror
        </div>

        <div id="variant-section" class="rounded-3xl border border-white/10 bg-white/5 p-4 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-black text-white">Biến thể món</h4>
                    <p class="text-xs text-gray-400">Thêm biến thể để quản lý giá và tồn kho.</p>
                </div>
                <button type="button" id="add-variant-row" class="btn-admin-outline text-xs px-3 py-2">+ Thêm dòng</button>
            </div>

            <div id="variant-item-list" class="space-y-3">
                @php
                    $oldVariants = old('variants', []);
                    if (empty($oldVariants)) {
                        $oldVariants = [
                            ['value' => '', 'price' => '', 'stock_quantity' => 0],
                        ];
                    }
                @endphp

                @foreach ($oldVariants as $index => $variant)
                    <div class="grid grid-cols-12 gap-3 variant-row">
                        <div class="col-span-4">
                            <label class="text-xs uppercase tracking-wider text-gray-400">Tên biến thể</label>
                            <input name="variants[{{ $index }}][value]" value="{{ old("variants.$index.value", $variant['value'] ?? '') }}" class="admin-input" placeholder="VD: Lớn, Nhỏ...">
                            @error("variants.$index.value")
                                <small class="text-red-500">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-span-3">
                            <label class="text-xs uppercase tracking-wider text-gray-400">Giá</label>
                            <input type="number" min="0" name="variants[{{ $index }}][price]" value="{{ old("variants.$index.price", $variant['price'] ?? '') }}" class="admin-input" placeholder="0">
                            @error("variants.$index.price")
                                <small class="text-red-500">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-span-3">
                            <label class="text-xs uppercase tracking-wider text-gray-400">Tồn kho</label>
                            <input type="number" min="0" name="variants[{{ $index }}][stock_quantity]" value="{{ old("variants.$index.stock_quantity", $variant['stock_quantity'] ?? 0) }}" class="admin-input" placeholder="0">
                            @error("variants.$index.stock_quantity")
                                <small class="text-red-500">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-span-2 flex items-end">
                            <button type="button" class="remove-variant-row btn-admin-outline w-full {{ count($oldVariants) <= 1 ? 'hidden' : '' }}">✕</button>
                        </div>
                    </div>
                @endforeach
            </div>

            <template id="variant-item-template">
                <div class="grid grid-cols-12 gap-3 variant-row">
                    <div class="col-span-4">
                        <label class="text-xs uppercase tracking-wider text-gray-400">Tên biến thể</label>
                        <input name="variants[__index__][value]" class="admin-input" placeholder="VD: Lớn, Nhỏ...">
                    </div>
                    <div class="col-span-3">
                        <label class="text-xs uppercase tracking-wider text-gray-400">Giá</label>
                        <input type="number" min="0" name="variants[__index__][price]" class="admin-input" placeholder="0">
                    </div>
                    <div class="col-span-3">
                        <label class="text-xs uppercase tracking-wider text-gray-400">Tồn kho</label>
                        <input type="number" min="0" name="variants[__index__][stock_quantity]" value="0" class="admin-input" placeholder="0">
                    </div>
                    <div class="col-span-2 flex items-end">
                        <button type="button" class="remove-variant-row btn-admin-outline w-full">✕</button>
                    </div>
                </div>
            </template>
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-300">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
            Hiển thị món
        </label>

        <button class="btn-admin w-full">
            Lưu món lẻ
        </button>
    </form>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {

        const imageInput = document.getElementById('image');
        const previewImage = document.getElementById('preview-image');
        const previewContainer = document.getElementById('preview-container');
        const uploadPlaceholder = document.getElementById('upload-placeholder');

        if (imageInput) {
            imageInput.addEventListener('change', function() {
                const file = imageInput.files && imageInput.files[0];
                if (!file) {
                    return;
                }

                previewImage.src = URL.createObjectURL(file);
                previewContainer.classList.remove('hidden');
                uploadPlaceholder.classList.add('hidden');
            });
        }

        const addVariantButton = document.getElementById('add-variant-row');
        const variantList = document.getElementById('variant-item-list');
        const variantTemplate = document.getElementById('variant-item-template');

        if (addVariantButton && variantList && variantTemplate) {
            let variantIndex = variantList.querySelectorAll('.variant-row').length || 0;

            addVariantButton.addEventListener('click', function() {
                const html = variantTemplate.innerHTML.replaceAll('__index__', variantIndex);
                variantList.insertAdjacentHTML('beforeend', html);
                variantIndex += 1;
                updateVariantRemoveButtons();
            });

            variantList.addEventListener('click', function(event) {
                const button = event.target.closest('.remove-variant-row');
                if (!button) {
                    return;
                }
                button.closest('.variant-row').remove();
                updateVariantRemoveButtons();
            });

            function updateVariantRemoveButtons() {
                const removeButtons = variantList.querySelectorAll('.remove-variant-row');
                removeButtons.forEach(function(button) {
                    button.classList.toggle('hidden', removeButtons.length <= 1);
                });
            }

            updateVariantRemoveButtons();
        }
    });
</script>
@endpush