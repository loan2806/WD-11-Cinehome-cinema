@extends('layouts.admin')

@section('page-title', 'Cập nhật danh mục đồ ăn')
@section('page-subtitle', 'Chỉnh sửa thông tin danh mục')

@section('content')

@include('admin.partials.flash')

<div class="max-w-3xl mx-auto space-y-6">
    <div class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-lg font-black text-white">Cập nhật danh mục</h3>
                <p class="text-xs text-gray-500">Chỉnh sửa thông tin danh mục.</p>
            </div>

            <a href="{{ route('admin.foods.categories.index') }}" class="btn-admin-outline">
                Quay lại danh sách
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.foods.categories.update', $category) }}"
        class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-6 space-y-6">

        @csrf
        @method('PATCH')

        <div>
            <label class="text-xs uppercase tracking-wider text-gray-400">
                Tên danh mục
            </label>

            <input
                name="name"
                value="{{ old('name', $category->name) }}"
                class="admin-input"
                placeholder="Ví dụ: Đồ ăn nhẹ">

            @error('name')
                <small class="text-red-500">{{ $message }}</small>
            @enderror
        </div>

        <div class="flex items-center gap-3">
            <input
                type="checkbox"
                id="is_combo"
                name="is_combo"
                value="1"
                {{ old('is_combo', $category->is_combo) ? 'checked' : '' }}
                class="h-4 w-4">

            <label for="is_combo" class="text-sm text-white">
                Đây là danh mục Combo
            </label>
        </div>

        @error('is_combo')
            <small class="text-red-500">{{ $message }}</small>
        @enderror

        <button class="btn-admin w-full">
            Cập nhật danh mục
        </button>

    </form>
</div>

@endsection