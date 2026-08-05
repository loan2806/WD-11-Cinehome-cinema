@extends('layouts.admin')

@section('title', 'Thêm combo mới')
@section('page-title', 'Thêm combo mới')
@section('page-subtitle', 'Tạo combo đồ ăn, nước uống với thành phần và giá riêng')

@section('content')
@include('admin.partials.flash')

<div class="max-w-4xl mx-auto space-y-6">
    <div class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-lg font-black text-white">Thêm combo mới</h3>
            </div>
            <a href="{{ route('admin.foods.combos.index') }}" class="btn-admin-outline">
                Quay lại danh sách combo
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.foods.combos.store') }}" enctype="multipart/form-data" class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-6 space-y-6">
        @csrf

        <div class="grid gap-4 lg:grid-cols-2">
            <div>
                <label class="text-xs uppercase tracking-wider text-gray-400">Tên combo</label>
                <input name="name" value="{{ old('name') }}" class="admin-input" placeholder="Tên combo">
                @error('name')
                <small class="text-red-500">{{ $message }}</small>
                @enderror
            </div>

            <input type="hidden" name="category_id" value="{{ $comboCategory?->id }}">

            <div class="lg:col-span-2">
                <label class="text-xs uppercase tracking-wider text-gray-400">Mô tả</label>
                <textarea name="description" class="admin-input" rows="4" placeholder="Mô tả combo">{{ old('description') }}</textarea>
                @error('description')
                <small class="text-red-500">{{ $message }}</small>
                @enderror
            </div>

            <div class="lg:col-span-2">
                <label class="text-xs uppercase tracking-wider text-gray-400 mb-2 block">Ảnh combo</label>
                <label for="image" class="group relative flex h-56 w-full cursor-pointer items-center justify-center rounded-2xl border-2 border-dashed border-white/20 bg-[#181818] hover:border-red-500 transition">
                    <div id="preview-container" class="hidden absolute inset-0">
                        <img id="preview-image" class="h-full w-full rounded-2xl object-cover">
                    </div>
                    <div id="upload-placeholder" class="flex flex-col items-center text-gray-400 group-hover:text-red-500 transition">
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
                    <p class="text-xs text-gray-400">Chọn tối thiểu 2 biến thể để tạo combo.</p>
                </div>
                <button type="button" id="add-combo-item" class="btn-admin-outline text-xs px-3 py-2">+ Thêm dòng</button>
            </div>

            <div id="combo-item-list" class="space-y-3">
                @php
                    $oldComboItems = old('combo_items', []);
                    if (empty($oldComboItems)) {
                        $oldComboItems = [
                            ['variant_id' => '', 'quantity' => 1],
                            ['variant_id' => '', 'quantity' => 1],
                        ];
                    }
                @endphp

                @foreach ($oldComboItems as $index => $item)
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
                    <input type="number" name="price" min="0" value="{{ old('price') }}" class="admin-input" placeholder="Nhập giá combo">
                    @error('price')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>
                <div class="flex items-end">
                    <label class="flex items-center gap-2 text-sm text-gray-300 w-full">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
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

        <button class="btn-admin w-full">Lưu combo</button>
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
            const rows = list.querySelectorAll('.combo-row');
            buttons.forEach(function (button) {
                button.classList.toggle('hidden', rows.length <= 2);
            });
        }

        updateRemoveButtons();
    });
</script>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form[action="{{ route('admin.foods.combos.store') }}"]');
    if (!form) return;

    function convertKeyToName(key) {
        // combo_items.0.variant_id -> combo_items[0][variant_id]
        const parts = key.split('.');
        return parts.map((p, i) => i === 0 ? p : '[' + p + ']').join('');
    }

    function clearValidationErrors() {
        form.querySelectorAll('[data-validation-error]').forEach(el => el.remove());
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        clearValidationErrors();

        const submitBtn = form.querySelector('button[type="submit"]') || form.querySelector('button');
        const originalHtml = submitBtn ? submitBtn.innerHTML : null;
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1.5"></i>Đang xử lý...';
        }

        const fd = new FormData(form);
        try {
            const res = await fetch(form.action, {
                method: form.method || 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: fd
            });

            if (res.status === 422) {
                const data = await res.json();
                const errors = data.errors || {};
                Object.keys(errors).forEach(key => {
                    const message = errors[key][0] || errors[key];
                    const name = convertKeyToName(key);
                    const field = form.querySelector('[name="' + name + '"]');
                    const small = document.createElement('small');
                    small.className = 'text-red-500';
                    small.dataset.validationError = '1';
                    small.textContent = message;
                    if (field && field.parentNode) field.parentNode.appendChild(small);
                    else form.insertAdjacentElement('afterbegin', small);
                });
                // scroll to form
                window.scrollTo({ top: form.getBoundingClientRect().top + window.scrollY - 40, behavior: 'smooth' });
            } else if (res.ok) {
                // success — redirect to index (server may redirect; navigate to index to be safe)
                window.location.href = '{{ route("admin.foods.combos.index") }}';
            } else {
                const txt = await res.text();
                console.error('Unexpected response', res.status, txt);
                alert('Lỗi server: ' + res.status);
            }
        } catch (err) {
            console.error(err);
            alert('Lỗi mạng hoặc không thể kết nối tới server.');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHtml;
            }
        }
    });
});
</script>
@endpush
