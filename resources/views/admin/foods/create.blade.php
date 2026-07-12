@extends('layouts.admin')

@section('page-title', 'Thêm món mới')
@section('page-subtitle', 'Tạo sản phẩm mới cho menu')

@section('content')

    @include('admin.partials.flash')

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-5">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-lg font-black text-white">Thêm món mới</h3>
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
                    <label class="text-xs uppercase tracking-wider text-gray-400">Tên sản phẩm</label>
                    <input name="name" value="{{ old('name') }}" class="admin-input" placeholder="Tên sản phẩm">
                    @error('name')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label class="text-xs uppercase tracking-wider text-gray-400">Danh mục</label>
                    <select name="category_id" id="category" class="admin-input">
                        <option value="">-- Chọn danh mục --</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                    <p id="combo-note" class="mt-2 text-xs text-gray-400 hidden">
                        Combo không quản lý biến thể riêng và không có tồn kho riêng cho combo. Tồn kho được quản lý theo
                        từng món thành phần.
                    </p>
                </div>

                <div>
                    <label class="text-xs uppercase tracking-wider text-gray-400 mb-2 block">
                        Ảnh sản phẩm
                    </label>

                    <label for="image"
                        class="group relative flex h-56 w-full cursor-pointer items-center justify-center rounded-2xl border-2 border-dashed border-white/20 bg-[#181818] hover:border-red-500 transition">

                        <div id="preview-container" class="hidden absolute inset-0">
                            <img id="preview-image" class="h-full w-full rounded-2xl object-cover">
                        </div>

                        <div id="upload-placeholder"
                            class="flex flex-col items-center text-gray-400 group-hover:text-red-500 transition">
                            <div class="text-6xl font-light leading-none">+</div>
                            <p class="mt-2 text-sm">Chọn ảnh sản phẩm</p>
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
                <textarea name="description" class="admin-input" rows="5" placeholder="Mô tả sản phẩm">{{ old('description') }}</textarea>
                @error('description')
                    <small class="text-red-500">{{ $message }}</small>
                @enderror
            </div>

            <div id="variant-section" class="rounded-3xl border border-white/10 bg-white/5 p-4 space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h4 class="text-sm font-black text-white">Biến thể sản phẩm</h4>
                        <p class="text-xs text-gray-400">Chỉ hiện khi chọn Đồ ăn hoặc Đồ uống.</p>
                    </div>
                    <button type="button" id="add-variant-btn" class="btn-admin-outline text-xs px-3 py-2">
                        Thêm biến thể
                    </button>
                </div>
                @error('variants')
                    <small class="text-red-500">{{ $message }}</small>
                @enderror

                <div id="variant-list" class="space-y-3">
                    @php
                        $oldVariants = old('variants', []);
                        if (empty($oldVariants)) {
                            $oldVariants = [['value' => '', 'price' => '', 'stock_quantity' => '']];
                        }
                    @endphp

                    @foreach ($oldVariants as $index => $variant)
                        <div class="grid gap-3 sm:grid-cols-3 items-end variant-row">
                            <div>
                                <label class="text-xs uppercase tracking-wider text-gray-400">Tên biến thể</label>
                                <input type="text" name="variants[{{ $index }}][value]"
                                    value="{{ old('variants.' . $index . '.value') }}" class="admin-input"
                                    placeholder="Ví dụ: Size L, 500ml">
                                @error('variants.' . $index . '.value')
                                    <small class="text-red-500">{{ $message }}</small>
                                @enderror
                            </div>
                            <div>
                                <label class="text-xs uppercase tracking-wider text-gray-400">Giá</label>
                                <input type="number" min="0" name="variants[{{ $index }}][price]"
                                    value="{{ old('variants.' . $index . '.price') }}" class="admin-input"
                                    placeholder="Giá">
                                @error('variants.' . $index . '.price')
                                    <small class="text-red-500">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="flex items-end gap-2">
                                <div class="flex-1">
                                    <label class="text-xs uppercase tracking-wider text-gray-400">Số lượng</label>
                                    <input type="number" min="0"
                                        name="variants[{{ $index }}][stock_quantity]"
                                        value="{{ old('variants.' . $index . '.stock_quantity') }}" class="admin-input"
                                        placeholder="Số lượng">
                                    @error('variants.' . $index . '.stock_quantity')
                                        <small class="text-red-500">{{ $message }}</small>
                                    @enderror
                                </div>
                                @if ($index > 0)
                                    <button type="button" class="remove-variant btn-admin-outline text-xs px-3 py-2">
                                        Xóa
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <template id="variant-row-template">
                    <div class="grid gap-3 sm:grid-cols-3 items-end variant-row">
                        <div>
                            <label class="text-xs uppercase tracking-wider text-gray-400">Tên biến thể</label>
                            <input type="text" name="variants[__index__][value]" class="admin-input"
                                placeholder="Ví dụ: Size L, 500ml">
                        </div>
                        <div>
                            <label class="text-xs uppercase tracking-wider text-gray-400">Giá</label>
                            <input type="number" min="0" name="variants[__index__][price]" class="admin-input"
                                placeholder="Giá">
                        </div>
                        <div class="flex items-end gap-2">
                            <div class="flex-1">
                                <label class="text-xs uppercase tracking-wider text-gray-400">Số lượng</label>
                                <input type="number" min="0" name="variants[__index__][stock_quantity]"
                                    class="admin-input" placeholder="Số lượng">
                            </div>
                            <button type="button" class="remove-variant btn-admin-outline text-xs px-3 py-2">
                                Xóa
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <div id="combo-section" class="hidden rounded-3xl border border-white/10 bg-white/5 p-4 space-y-4">

                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-black text-white">
                            Thành phần combo
                        </h4>
                        <p class="text-xs text-gray-400">
                            Chọn các món có trong combo.
                        </p>
                    </div>

                    <button type="button" id="add-combo-item" class="btn-admin-outline text-xs px-3 py-2">
                        + Thêm dòng
                    </button>
                </div>



                <div id="combo-item-list" class="space-y-3">

                    @php
                        $oldComboItems = old('combo_items', []);

                        if (empty($oldComboItems)) {
                            $oldComboItems = [
                                [
                                    'variant_id' => '',
                                    'quantity' => 1,
                                ],
                                [
                                    'variant_id' => '',
                                    'quantity' => 1,
                                ],
                            ];
                        }
                    @endphp

                    @foreach ($oldComboItems as $index => $item)
                        <div class="grid grid-cols-12 gap-3 combo-row">

                            <div class="col-span-8">

                                <label class="text-xs uppercase tracking-wider text-gray-400">
                                    Chọn món
                                </label>

                                <select name="combo_items[{{ $index }}][variant_id]" class="admin-input">

                                    <option value="">-- Chọn biến thể --</option>

                                    @foreach ($variants as $variant)
                                        <option value="{{ $variant->id }}"
                                            {{ old("combo_items.$index.variant_id") == $variant->id ? 'selected' : '' }}>
                                            {{ $variant->doAn->name }} ({{ $variant->value }})
                                        </option>
                                    @endforeach

                                </select>

                                @error("combo_items.$index.variant_id")
                                    <small class="text-red-500">
                                        {{ $message }}
                                    </small>
                                @enderror

                            </div>

                            <div class="col-span-3">

                                <label class="text-xs uppercase tracking-wider text-gray-400">
                                    SL
                                </label>

                                <input type="number" min="1" name="combo_items[{{ $index }}][quantity]"
                                    value="{{ old("combo_items.$index.quantity", 1) }}" class="admin-input">

                                @error("combo_items.$index.quantity")
                                    <small class="text-red-500">
                                        {{ $message }}
                                    </small>
                                @enderror

                            </div>

                            <div class="col-span-1 flex items-end">

                                @if ($index > 0)
                                    <button type="button" class="remove-combo-row btn-admin-outline w-full">
                                        ✕
                                    </button>
                                @endif

                            </div>

                        </div>
                    @endforeach

                </div>
                <div class="mt-4">
                    <label class="text-xs uppercase tracking-wider text-gray-400">
                        Giá combo
                    </label>

                    <input type="number" name="price" min="0" value="{{ old('price') }}"
                        class="admin-input" placeholder="Nhập giá combo">
                </div>
                <template id="combo-item-template">
                    <div class="grid grid-cols-12 gap-3 combo-row">

                        <div class="col-span-8">
                            <label class="text-xs uppercase tracking-wider text-gray-400">
                                Chọn món
                            </label>

                            <select name="combo_items[__index__][variant_id]" class="admin-input">

                                <option value="">-- Chọn biến thể --</option>

                                @foreach ($variants as $variant)
                                    <option value="{{ $variant->id }}">
                                        {{ $variant->doAn->name }} ({{ $variant->value }})
                                    </option>
                                @endforeach

                            </select>

                        </div>

                        <div class="col-span-3">

                            <label class="text-xs uppercase tracking-wider text-gray-400">
                                SL
                            </label>

                            <input type="number" min="1" value="1" name="combo_items[__index__][quantity]"
                                class="admin-input">

                        </div>

                        <div class="col-span-1 flex items-end">

                            <button type="button" class="remove-combo-row btn-admin-outline w-full">
                                ✕
                            </button>

                        </div>

                    </div>
                </template>

            </div>

            <label class="flex items-center gap-2 text-sm text-gray-300">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                Hiển thị menu
            </label>

            <button class="btn-admin w-full">
                Lưu sản phẩm
            </button>
        </form>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ==========================
            // Preview ảnh
            // ==========================
            const imageInput = document.getElementById('image');
            const previewImage = document.getElementById('preview-image');
            const previewContainer = document.getElementById('preview-container');
            const uploadPlaceholder = document.getElementById('upload-placeholder');

            if (imageInput) {
                imageInput.addEventListener('change', function() {

                    const file = this.files[0];

                    if (!file) return;

                    const reader = new FileReader();

                    reader.onload = function(e) {

                        previewImage.src = e.target.result;

                        previewContainer.classList.remove('hidden');
                        uploadPlaceholder.classList.add('hidden');

                    }

                    reader.readAsDataURL(file);

                });
            }

            // ==========================
            // Biến thể
            // ==========================
            const variantSection = document.getElementById('variant-section');
            const variantList = document.getElementById('variant-list');
            const addVariantBtn = document.getElementById('add-variant-btn');
            const variantTemplate = document.getElementById('variant-row-template').innerHTML;

            function addVariantRow() {

                const index = variantList.querySelectorAll('.variant-row').length;

                variantList.insertAdjacentHTML(
                    'beforeend',
                    variantTemplate.replace(/__index__/g, index)
                );

            }

            addVariantBtn.addEventListener('click', function() {

                addVariantRow();

            });

            variantList.addEventListener('click', function(e) {

                if (e.target.closest('.remove-variant')) {

                    e.target.closest('.variant-row').remove();

                }

            });

            // ==========================
            // Combo
            // ==========================
            const comboSection = document.getElementById('combo-section');
            const comboList = document.getElementById('combo-item-list');
            const comboTemplate = document.getElementById('combo-item-template').innerHTML;
            const addComboBtn = document.getElementById('add-combo-item');

            function addComboRow() {

                const index = comboList.querySelectorAll('.combo-row').length;

                comboList.insertAdjacentHTML(
                    'beforeend',
                    comboTemplate.replace(/__index__/g, index)
                );

            }

            addComboBtn.addEventListener('click', function() {

                addComboRow();

            });

            comboList.addEventListener('click', function(e) {

                if (e.target.closest('.remove-combo-row')) {

                    const rows = comboList.querySelectorAll('.combo-row');

                    if (rows.length > 2) {
                        e.target.closest('.combo-row').remove();
                    }

                }

            });

            // ==========================
            // Hiện / Ẩn Combo
            // ==========================
            const category = document.getElementById('category');
            const comboNote = document.getElementById('combo-note');

            function toggleSection() {

                const selectedOption = category.options[category.selectedIndex];
                const categoryName = (selectedOption.text || '').toLowerCase();

                if (categoryName.includes('combo')) {

                    variantSection.classList.add('hidden');
                    comboSection.classList.remove('hidden');
                    comboNote.classList.remove('hidden');

                    if (comboList.querySelectorAll('.combo-row').length < 2) {
                        while (comboList.querySelectorAll('.combo-row').length < 2) {
                            addComboRow();
                        }
                    }

                } else {

                    variantSection.classList.remove('hidden');
                    comboSection.classList.add('hidden');
                    comboNote.classList.add('hidden');

                }

            }
            category.addEventListener('change', toggleSection);

            toggleSection();

        });
    </script>
@endpush
