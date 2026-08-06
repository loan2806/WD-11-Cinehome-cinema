@extends('layouts.admin')

@section('page-title', 'Thêm danh mục món lẻ')
@section('page-subtitle', 'Tạo danh mục chỉ dùng cho đồ ăn lẻ')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-lg font-black text-white">Thêm danh mục món lẻ</h3>
            </div>
            <a href="{{ route('admin.foods.categories.index') }}" class="btn-admin-outline">Quay lại danh sách</a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.foods.categories.store') }}" class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-6 space-y-6">
        @csrf

        <div>
            <label class="text-xs uppercase tracking-wider text-gray-400">Tên danh mục</label>
            <input name="name" value="{{ old('name') }}" class="admin-input" placeholder="Tên danh mục">
            @error('name')
                <small class="text-red-500">{{ $message }}</small>
            @enderror
        </div>

        <button class="btn-admin w-full">Lưu danh mục</button>
    </form>
</div>
@endsection
