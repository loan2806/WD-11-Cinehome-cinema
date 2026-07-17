@extends('layouts.admin')

@section('title', 'Chỉnh sửa món')
@section('page-title', 'Chỉnh sửa món')
@section('page-subtitle', 'Cập nhật thông tin món, ảnh, giá bán, tồn kho và thành phần combo')

@section('content')
@php
    $isCombo = $food->isCombo();
    $imagePath = $food->image
        ? asset('storage/' . (str_starts_with($food->image, 'foods/') ? $food->image : 'foods/' . $food->image))
        : null;

    $comboItems = collect(
        old(
            'combo_items',
            $food->comboItems
                ->map(fn ($item) => [
                    'variant_id' => $item->food_variant_id,
                    'quantity' => $item->quantity,
                ])
                ->toArray(),
        ),
    )->map(fn ($item) => [
        'variant_id' => $item['variant_id'] ?? ($item['food_variant_id'] ?? null),
        'quantity' => $item['quantity'] ?? 1,
    ]);
@endphp

<div class="food-edit-page">
    @include('admin.partials.flash')

    <section class="food-edit-hero">
        <div>
            <span class="food-edit-eyebrow">
                <i class="fa-solid fa-pen-to-square"></i>
                Chỉnh sửa menu
            </span>
            <h2>{{ $food->name }}</h2>
            <p>Cập nhật thông tin bán hàng, ảnh hiển thị, trạng thái và cấu hình {{ $isCombo ? 'thành phần combo' : 'biến thể kho' }}.</p>
        </div>

        <div class="food-edit-actions">
            <a href="{{ route('admin.foods.index') }}" class="food-edit-action is-soft">
                <i class="fa-solid fa-arrow-left"></i>
                Danh sách
            </a>
            <a href="{{ route('admin.foods.show', $food) }}" class="food-edit-action is-soft">
                <i class="fa-solid fa-eye"></i>
                Chi tiết
            </a>
        </div>
    </section>

    <form method="POST" action="{{ route('admin.foods.update', $food) }}" enctype="multipart/form-data" class="food-edit-form">
        @csrf
        @method('PATCH')

        <div class="food-edit-grid">
            <section class="food-edit-panel">
                <div class="food-edit-panel-head">
                    <div>
                        <span class="food-edit-eyebrow">Thông tin chính</span>
                        <h3>Dữ liệu món</h3>
                    </div>
                </div>

                <div class="food-edit-fields">
                    <label class="food-edit-field is-wide">
                        <span>Tên món</span>
                        <input name="name"
                            value="{{ old('name') !== null && old('name') !== '' ? old('name') : $food->name }}"
                            class="admin-input @error('name') is-invalid @enderror"
                            placeholder="Nhập tên món">
                        @error('name')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    <label class="food-edit-field">
                        <span>SKU</span>
                        <input name="sku"
                            value="{{ old('sku') !== null && old('sku') !== '' ? old('sku') : $food->sku }}"
                            class="admin-input @error('sku') is-invalid @enderror"
                            placeholder="VD: POPCORN-L">
                        @error('sku')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    <label class="food-edit-field">
                        <span>Thứ tự hiển thị</span>
                        <input type="number" min="0" name="sort_order"
                            value="{{ old('sort_order', $food->sort_order ?? 0) }}"
                            class="admin-input @error('sort_order') is-invalid @enderror"
                            placeholder="0">
                        @error('sort_order')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    <label class="food-edit-field is-wide">
                        <span>Danh mục</span>
                        <select class="admin-input" disabled>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected($food->category_id == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="category_id" value="{{ $food->category_id }}">
                        <em>Danh mục được khóa để tránh đổi sai loại món/combo sau khi đã tạo.</em>
                        @error('category_id')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>

                    <label class="food-edit-field is-wide">
                        <span>Mô tả</span>
                        <textarea name="description" class="admin-input @error('description') is-invalid @enderror" rows="5" placeholder="Nhập mô tả món">{{ old('description') !== null && old('description') !== '' ? old('description') : $food->description }}</textarea>
                        @error('description')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>
                </div>
            </section>

            <aside class="food-edit-panel">
                <div class="food-edit-panel-head">
                    <div>
                        <span class="food-edit-eyebrow">Hiển thị</span>
                        <h3>Ảnh & trạng thái</h3>
                    </div>
                </div>

                <label for="image" class="food-edit-upload">
                    <div id="preview-container" class="food-edit-preview {{ $imagePath ? '' : 'is-hidden' }}">
                        <img id="preview-image" src="{{ $imagePath ?: '' }}" alt="{{ $food->name }}">
                    </div>
                    <div id="upload-placeholder" class="food-edit-upload-empty {{ $imagePath ? 'is-hidden' : '' }}">
                        <i class="fa-solid fa-image"></i>
                        <strong>Chọn ảnh món</strong>
                        <span>JPG, PNG, WEBP tối đa 2MB</span>
                    </div>
                    <input id="image" type="file" name="image" accept="image/*">
                </label>
                @error('image')
                    <small class="food-edit-error">{{ $message }}</small>
                @enderror

                <input type="hidden" name="is_active" value="0">
                <label class="food-edit-switch">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $food->is_active))>
                    <span></span>
                    <div>
                        <strong>Hiển thị trên menu</strong>
                        <small>{{ $food->is_active ? 'Món đang bán cho khách.' : 'Món đang tạm ẩn.' }}</small>
                    </div>
                </label>

                @if ($isCombo)
                    <label class="food-edit-field">
                        <span>Giá combo</span>
                        <input type="number" name="price" min="0" value="{{ old('price', $food->price) }}" class="admin-input @error('price') is-invalid @enderror" placeholder="Nhập giá combo">
                        @error('price')
                            <small>{{ $message }}</small>
                        @enderror
                    </label>
                @else
                    <div class="food-edit-hint">
                        <i class="fa-solid fa-circle-info"></i>
                        Giá bán món lẻ được lấy từ biến thể có giá thấp nhất.
                    </div>
                @endif
            </aside>
        </div>

        @if (! $isCombo)
            <section class="food-edit-panel">
                <div class="food-edit-panel-head">
                    <div>
                        <span class="food-edit-eyebrow">Kho & giá</span>
                        <h3>Biến thể hiện có</h3>
                        <p>Biến thể được chỉnh ở màn quản lý riêng để tránh sai lệch tồn kho.</p>
                    </div>
                    <a href="{{ route('admin.foods.variants.create', $food) }}" class="food-edit-small-btn">
                        <i class="fa-solid fa-plus"></i>
                        Thêm biến thể
                    </a>
                </div>

                <div class="food-edit-variant-list">
                    @forelse ($food->variants as $variant)
                        <article class="food-edit-variant-card {{ ! $variant->is_active ? 'is-inactive' : '' }}">
                            <div>
                                <span>Biến thể</span>
                                <strong>{{ $variant->value ?: 'Mặc định' }}</strong>
                            </div>
                            <div>
                                <span>Giá</span>
                                <strong>{{ number_format((float) $variant->price, 0, ',', '.') }}đ</strong>
                            </div>
                            <div class="{{ $variant->stock_quantity <= $food->min_stock_quantity ? 'is-low' : '' }}">
                                <span>Tồn kho</span>
                                <strong>{{ $variant->stock_quantity }}</strong>
                            </div>
                            <div>
                                <span>Trạng thái</span>
                                <strong>{{ $variant->is_active ? 'Đang bán' : 'Tạm ẩn' }}</strong>
                            </div>
                            <a href="{{ route('admin.foods.variants.edit', [$food, $variant]) }}" class="food-edit-mini-btn">
                                <i class="fa-solid fa-pen"></i>
                                Sửa
                            </a>
                        </article>
                    @empty
                        <div class="food-edit-empty">
                            <i class="fa-solid fa-box-open"></i>
                            <h3>Chưa có biến thể</h3>
                            <p>Thêm biến thể để quản lý giá bán và tồn kho.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        @else
            <section class="food-edit-panel">
                <div class="food-edit-panel-head">
                    <div>
                        <span class="food-edit-eyebrow">Combo</span>
                        <h3>Thành phần combo</h3>
                        <p>Combo cần tối thiểu 2 thành phần và không được chọn trùng biến thể.</p>
                    </div>
                    <button type="button" id="add-combo-item" class="food-edit-small-btn">
                        <i class="fa-solid fa-plus"></i>
                        Thêm dòng
                    </button>
                </div>

                <div id="combo-item-list" class="food-edit-combo-list">
                    @forelse ($comboItems as $index => $item)
                        <div class="food-edit-combo-row combo-row">
                            <label class="food-edit-field">
                                <span>Biến thể thành phần</span>
                                <select name="combo_items[{{ $index }}][variant_id]" class="admin-input @error("combo_items.$index.variant_id") is-invalid @enderror">
                                    <option value="">Chọn biến thể</option>
                                    @foreach ($variants as $variant)
                                        <option value="{{ $variant->id }}" @selected(old("combo_items.$index.variant_id", $item['variant_id']) == $variant->id)>
                                            {{ $variant->doAn->name }} - {{ $variant->value ?: 'Mặc định' }} (kho {{ $variant->stock_quantity }})
                                        </option>
                                    @endforeach
                                </select>
                                @error("combo_items.$index.variant_id")
                                    <small>{{ $message }}</small>
                                @enderror
                            </label>

                            <label class="food-edit-field">
                                <span>Số lượng</span>
                                <input type="number" min="1" name="combo_items[{{ $index }}][quantity]" value="{{ old("combo_items.$index.quantity", $item['quantity']) }}" class="admin-input @error("combo_items.$index.quantity") is-invalid @enderror">
                                @error("combo_items.$index.quantity")
                                    <small>{{ $message }}</small>
                                @enderror
                            </label>

                            <button type="button" class="food-edit-remove-btn remove-combo-row remove-btn" title="Xóa dòng">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    @empty
                        <div class="food-edit-empty">
                            <i class="fa-solid fa-box-open"></i>
                            <h3>Combo chưa có thành phần</h3>
                            <p>Thêm ít nhất 2 thành phần để lưu combo.</p>
                        </div>
                    @endforelse
                </div>

                <template id="combo-item-template">
                    <div class="food-edit-combo-row combo-row">
                        <label class="food-edit-field">
                            <span>Biến thể thành phần</span>
                            <select name="combo_items[__index__][variant_id]" class="admin-input">
                                <option value="">Chọn biến thể</option>
                                @foreach ($variants as $variant)
                                    <option value="{{ $variant->id }}">
                                        {{ $variant->doAn->name }} - {{ $variant->value ?: 'Mặc định' }} (kho {{ $variant->stock_quantity }})
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="food-edit-field">
                            <span>Số lượng</span>
                            <input type="number" min="1" value="1" name="combo_items[__index__][quantity]" class="admin-input">
                        </label>

                        <button type="button" class="food-edit-remove-btn remove-combo-row remove-btn" title="Xóa dòng">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </template>
            </section>
        @endif

        <div class="food-edit-submitbar">
            <a href="{{ route('admin.foods.index') }}" class="food-edit-action is-soft">
                Hủy
            </a>
            <button type="submit" class="food-edit-action">
                <i class="fa-solid fa-floppy-disk"></i>
                Lưu thay đổi
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const imageInput = document.getElementById('image');
    const previewContainer = document.getElementById('preview-container');
    const previewImage = document.getElementById('preview-image');
    const uploadPlaceholder = document.getElementById('upload-placeholder');

    if (imageInput && previewContainer && previewImage && uploadPlaceholder) {
        imageInput.addEventListener('change', function () {
            const file = imageInput.files && imageInput.files[0];

            if (!file) {
                return;
            }

            previewImage.src = URL.createObjectURL(file);
            previewContainer.classList.remove('is-hidden');
            uploadPlaceholder.classList.add('is-hidden');
        });
    }

    const addBtn = document.getElementById('add-combo-item');
    const list = document.getElementById('combo-item-list');
    const template = document.getElementById('combo-item-template');

    if (!addBtn || !list || !template) {
        return;
    }

    let index = list.querySelectorAll('.combo-row').length || 0;

    function updateRemoveButtons() {
        const buttons = list.querySelectorAll('.remove-btn');
        const rows = list.querySelectorAll('.combo-row');

        buttons.forEach(function (button) {
            button.classList.toggle('is-hidden', rows.length <= 2);
        });
    }

    addBtn.addEventListener('click', function () {
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

    updateRemoveButtons();
});
</script>
@endpush
