@extends('layouts.admin')

@section('page-title', 'Thêm biến thể')
@section('page-subtitle', $food->name)

@section('content')

    <div class="max-w-3xl mx-auto">

        @include('admin.partials.flash')

        <div class="admin-panel">

            <div class="panel-header">
                <h5>Thêm biến thể cho {{ $food->name }}</h5>
            </div>

            <form method="POST" action="{{ route('admin.foods.variants.store', $food) }}" class="panel-body space-y-4">

                @csrf

                <div>
                    <label>Tên biến thể</label>

                    <input type="text" name="value" class="admin-input" placeholder="Ví dụ: Size L, 500ml..."
                        value="{{ old('value') }}">

                    @error('value')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>
                <div>
                    <label>Giá bán</label>

                    <input type="number" name="price" class="admin-input" min="0" step="1000"
                        value="{{ old('price') }}" placeholder="Ví dụ: 45000">

                    @error('price')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label>Tồn kho</label>

                    <input type="number" name="stock_quantity" class="admin-input"  value="{{ old('stock_quantity') }}"  placeholder="Ví dụ: 1000">

                    @error('stock_quantity')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <label class="flex items-center gap-2">

                    <input type="checkbox" name="is_active" value="1" checked>

                    Đang bán

                </label>

                <div class="flex gap-3">

                    <button class="btn-admin" type="submit">

                        Lưu

                    </button>

                    <a href="{{ route('admin.foods.edit', $food) }}" class="btn-admin-outline">

                        Quay lại

                    </a>

                </div>

            </form>

        </div>

    </div>

@endsection
