@extends('layouts.admin')

@section('page-title', 'Chỉnh sửa biến thể')
@section('page-subtitle', $food->name)

@section('content')

<div class="max-w-3xl mx-auto">

    @include('admin.partials.flash')

    <div class="admin-panel">

        <div class="panel-header flex items-center justify-between">
            <div>
                <h5>Chỉnh sửa biến thể cho {{ $food->name }}</h5>
                <p class="text-xs text-gray-400">{{ $variant->value }}</p>
            </div>
        </div>

        <form method="POST"
              action="{{ route('admin.foods.variants.update', [$food, $variant]) }}"
              class="panel-body space-y-4">

            @csrf
            @method('PATCH')

            <div>
                <label>Tên biến thể</label>
                <input
                    type="text"
                    name="value"
                    class="admin-input"
                    placeholder="Ví dụ: Size L, 500ml..."
                    value="{{ old('value') !== null && old('value') !== '' ? old('value') : $variant->value }}">

                @error('value')
                    <small class="text-red-500">{{ $message }}</small>
                @enderror
            </div>

            <div>
                <label>Giá</label>
                <input
                    type="number"
                    min="0"
                    name="price"
                    class="admin-input"
                    value="{{ old('price') !== null && old('price') !== '' ? old('price') : $variant->price }}">

                @error('price')
                    <small class="text-red-500">{{ $message }}</small>
                @enderror
            </div>

            <div>
                <label>Tồn kho</label>
                <input
                    type="number"
                    min="0"
                    name="stock_quantity"
                    class="admin-input"
                    value="{{ old('stock_quantity') !== null && old('stock_quantity') !== '' ? old('stock_quantity') : $variant->stock_quantity }}">

                @error('stock_quantity')
                    <small class="text-red-500">{{ $message }}</small>
                @enderror
            </div>

            <input type="hidden" name="is_active" value="0">
            <label class="flex items-center gap-2">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    {{ old('is_active', $variant->is_active) ? 'checked' : '' }}>
                Đang bán
            </label>

            <div class="flex gap-3">
                <button class="btn-admin" type="submit">Lưu</button>
                <a href="{{ route('admin.foods.edit', $food) }}" class="btn-admin-outline">Quay lại</a>
            </div>

        </form>

    </div>

</div>

@endsection
