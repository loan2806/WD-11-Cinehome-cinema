@extends('layouts.admin')

@section('page-title', 'Quản lý biến thể')
@section('page-subtitle', 'Quản lý các kích cỡ, dung tích và giá bán')

@section('content')

@include('admin.partials.flash')

<div class="admin-panel">

    {{-- Header --}}
    <div class="panel-header flex items-center justify-between">
        <div>
            <h5>{{ $food->name }}</h5>
            <small>
                Tổng {{ $variants->total() }} biến thể
            </small>
        </div>

       <a href="{{ route('admin.foods.index') }}"
   class="btn-admin-outline inline-flex items-center gap-2">
    <i class="fa-solid fa-arrow-left"></i>
    Quay lại
</a>
    </div>

    {{-- Danh sách --}}
   <div class="space-y-3 p-5">

@forelse($variants as $variant)

<div class="rounded-2xl border border-white/10 bg-[#111111] px-6 py-5">

    <div class="grid grid-cols-5 items-center gap-4">

        {{-- Biến thể --}}
        <div>
            <p class="text-xs text-gray-500 mb-1">
                Biến thể
            </p>

            <p class="font-semibold text-white">
                {{ $variant->value }}
            </p>
        </div>

        {{-- Giá --}}
        <div>
            <p class="text-xs text-gray-500 mb-1">
                Giá
            </p>

            <p class="font-bold text-[#f4c56a]">
                {{ number_format($variant->price) }}đ
            </p>
        </div>

        {{-- Tồn kho --}}
        <div>
            <p class="text-xs text-gray-500 mb-1">
                Tồn kho
            </p>

            <p class="font-bold text-white">
                {{ $variant->stock_quantity }}
            </p>
        </div>

        {{-- Trạng thái --}}
        <div>
            <p class="text-xs text-gray-500 mb-1">
                Trạng thái
            </p>

            @if($variant->is_active)
                <span class="text-green-400 font-semibold">
                    Đang bán
                </span>
            @else
                <span class="text-red-400 font-semibold">
                    Ngừng bán
                </span>
            @endif
        </div>

        {{-- Action --}}
        <div class="flex justify-end">

            <a href="{{ route('admin.foods.variants.edit',[$food,$variant]) }}"
               class="w-10 h-10 rounded-xl bg-yellow-500/15 hover:bg-yellow-500/25 transition flex items-center justify-center text-yellow-400">
                <i class="fa-solid fa-pen"></i>
            </a>

        </div>

    </div>

</div>

@empty

<div class="rounded-2xl border border-dashed border-white/10 p-10 text-center text-gray-400">
    Chưa có biến thể nào.
</div>

@endforelse

</div>

    <div class="border-t border-white/10 p-4">
        {{ $variants->links() }}
    </div>

</div>

@endsection