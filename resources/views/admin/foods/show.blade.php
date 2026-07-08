@extends('layouts.admin')

@section('page-title', 'Chi tiết món')
@section('page-subtitle', 'Xem thông tin món, danh mục và biến thể')

@section('content')

    @include('admin.partials.flash')

    <div class="max-w-4xl mx-auto space-y-6">
        <div
            class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-5 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h3 class="text-lg font-black text-white">{{ $food->name }}</h3>
                <p class="text-xs text-gray-400">
                    SKU: {{ $food->sku ?? 'Không có' }}
                    · Danh mục:
                    {{ optional($food->category)->name }}
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.foods.index') }}" class="btn-admin-outline">Quay lại</a>
                <a href="{{ route('admin.foods.edit', $food) }}" class="btn-admin">Sửa món</a>
            </div>
        </div>

        <div class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-6 grid gap-6 lg:grid-cols-[260px_1fr]">
            <div class="rounded-3xl overflow-hidden bg-white/5">
                @if ($food->image)
                    <img src="{{ asset('storage/foods/' . $food->image) }}" class="h-full w-full object-cover">
                @else
                    <div class="flex h-full min-h-[260px] items-center justify-center text-gray-400">
                        Chưa có ảnh
                    </div>
                @endif
            </div>

            <div class="space-y-4">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl border border-white/10 bg-[#101010] p-4">
                        <p class="text-xs text-gray-400">Trạng thái</p>
                        <p class="text-lg font-black text-white">{{ $food->is_active ? 'Đang bán' : 'Tạm ẩn' }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-[#101010] p-4">
                        @php
                            $isCombo = str_contains(strtolower(optional($food->category)->name), 'combo');
                        @endphp

                        <div class="space-y-4">
                                {{-- Thành phần / Biến thể --}}
                                    <p class="text-xs text-gray-400">
                                        {{ $isCombo ? 'Tổng thành phần' : 'Tổng biến thể' }}
                                    </p>

                                    <p class="text-lg font-black text-white">
                                        {{ $isCombo ? $food->comboItems->count() : $food->variants->count() }}
                                    </p>

                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-white/10 bg-[#101010] p-4">
                    <p class="text-xs text-gray-400">Mô tả</p>
                    <p class="mt-2 text-sm text-gray-200">{{ $food->description ?? 'Không có mô tả' }}</p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-[#101010] p-4">
                    <p class="text-xs text-gray-400">Thông tin bổ sung</p>
                    <ul class="mt-3 space-y-2 text-sm text-gray-200">
                        <li>
                            Danh mục:
                            {{ optional($food->category)->name }}
                        </li>

                        <li>
                            Loại:
                            {{ str_contains(strtolower(optional($food->category)->name), 'combo') ? 'Combo' : 'Sản phẩm thường' }}
                        </li>
                        <li>Thêm vào: {{ $food->created_at->format('d/m/Y H:i') }}</li>
                    </ul>
                </div>
            </div>
        </div>
        @if (!str_contains(strtolower(optional($food->category)->name), 'combo'))
            <div class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="text-lg font-black text-white">Biến thể</h4>
                        <p class="text-xs text-gray-400">Danh sách giá và tồn kho.</p>
                    </div>

                    <a href="{{ route('admin.foods.variants.index', $food) }}" class="btn-admin-outline text-xs px-3 py-2">
                        Quản lý biến thể
                    </a>
                </div>

                @if ($food->variants->isEmpty())

                    <div class="text-center text-gray-400 py-8">
                        Chưa có biến thể.
                    </div>
                @else
                    <div class="space-y-3">

                        @foreach ($food->variants as $variant)
                            <div class="rounded-2xl border border-white/10 bg-[#101010] p-4 flex justify-between">

                                <div>
                                    <div class="font-semibold">
                                        {{ $variant->value }}
                                    </div>

                                    <div class="text-gray-400 text-sm">
                                        Giá: {{ number_format($variant->price) }}đ
                                    </div>

                                    <div class="text-gray-400 text-sm">
                                        Tồn kho: {{ $variant->stock_quantity }}
                                    </div>
                                </div>

                                <div class="text-sm">
                                    {{ $variant->is_active ? 'Đang bán' : 'Tạm ẩn' }}
                                </div>

                            </div>
                        @endforeach

                    </div>

                @endif
            </div>
        @endif

        @if (str_contains(strtolower(optional($food->category)->name), 'combo'))

            <div class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-6">

                <h4 class="text-lg font-black text-white mb-4">
                    Thành phần combo
                </h4>

                @forelse($food->comboItems as $item)
                    <div class="rounded-2xl border border-white/10 bg-[#101010] p-4 mb-3 flex justify-between">

                        <div>

                            <div>
                                <div class="font-semibold">
                                    {{ $item->variant->food->name }}
                                    ({{ $item->variant->value }})
                                </div>

                                <div class="text-gray-400 text-sm">
                                    {{ optional($item->variant->food->category)->name }}
                                </div>
                            </div>

                        </div>

                        <div class="font-bold">
                            x{{ $item->quantity }}
                        </div>

                    </div>

                @empty

                    <div class="text-center text-gray-400">
                        Combo chưa có thành phần.
                    </div>
                @endforelse

            </div>

        @endif
    </div>

@endsection
