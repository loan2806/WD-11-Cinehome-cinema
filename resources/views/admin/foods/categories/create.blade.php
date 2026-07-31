@extends('layouts.admin')

@section('page-title', 'Thêm danh mục đồ ăn')
@section('page-subtitle', 'Tạo danh mục mới cho đồ ăn hoặc đồ uống')

@section('content')

@include('admin.partials.flash')

<div class="max-w-3xl mx-auto space-y-6">
    <div class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-lg font-black text-white">Thêm danh mục</h3>
                <p class="text-xs text-gray-500">Tạo nhóm danh mục để chọn khi thêm món.</p>
            </div>

            <a href="{{ route('admin.foods.index') }}" class="btn-admin-outline">
                Quay lại danh sách
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.foods.categories.store') }}" class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-6 space-y-6">
        @csrf

        <div>
            <label class="text-xs uppercase tracking-wider text-gray-400">Tên danh mục</label>
            <input name="name" value="{{ old('name') }}" class="admin-input" placeholder="Ví dụ: Đồ ăn nhẹ">
            @error('name')<small class="text-red-500">{{ $message }}</small>@enderror
        </div>
        <div class="flex items-center gap-3">
            <input
                type="checkbox"
                name="is_combo"
                id="is_combo"
                value="1"
                {{ old('is_combo') ? 'checked' : '' }}
                class="h-4 w-4 rounded border-gray-600 bg-gray-800">

            <label for="is_combo" class="text-white">
                Đây là danh mục Combo
            </label>
        </div>

        <button class="btn-admin w-full">Lưu danh mục</button>
    </form>
</div>

@endsection