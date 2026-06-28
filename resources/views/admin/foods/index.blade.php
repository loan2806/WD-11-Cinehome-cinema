@extends('layouts.admin')

@section('page-title', 'Cấu hình Menu & Kho hàng')
@section('page-subtitle', 'Quản lý món bán tại quầy, giá menu, trạng thái hiển thị và tồn kho đồ ăn')

@section('content')
@php
    $statusClasses = [
        'available' => 'border-green-500/30 bg-green-500/10 text-green-300',
        'low' => 'border-yellow-500/30 bg-yellow-500/10 text-yellow-200',
        'out' => 'border-red-500/30 bg-red-500/10 text-red-300',
        'inactive' => 'border-gray-500/30 bg-white/5 text-gray-300',
    ];
@endphp

@include('admin.partials.flash')

<div class="mb-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
    <div class="stat-card">
        <div class="stat-label">Tổng món</div>
        <div class="stat-value">{{ $summary['total'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Đang bán</div>
        <div class="stat-value">{{ $summary['active'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Sắp hết</div>
        <div class="stat-value">{{ $summary['low'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Hết hàng</div>
        <div class="stat-value">{{ $summary['out'] }}</div>
    </div>
</div>

<div class="grid gap-6 xl:grid-cols-[420px_1fr]">
    <form method="POST" action="{{ route('admin.foods.store') }}" class="admin-panel">
        @csrf
        <div class="panel-header">
            <div>
                <h5>Thêm món vào menu</h5>
                <small>Giá menu và số lượng tồn kho ban đầu</small>
            </div>
        </div>

        <div class="panel-body space-y-4">
            <div class="grid gap-3 md:grid-cols-2">
                <input name="sku" value="{{ old('sku') }}" class="admin-input" placeholder="SKU, ví dụ POPCORN-L">
                <input name="category" value="{{ old('category') }}" class="admin-input" placeholder="Nhóm món">
            </div>

            <input name="name" value="{{ old('name') }}" class="admin-input" placeholder="Tên món" required>
            <input name="image" value="{{ old('image') }}" class="admin-input" placeholder="URL hình ảnh (nếu có)">

            <div class="grid gap-3 md:grid-cols-2">
                <input name="price" type="number" min="0" value="{{ old('price', 0) }}" class="admin-input" placeholder="Giá bán" required>
                <input name="sort_order" type="number" min="0" value="{{ old('sort_order', 0) }}" class="admin-input" placeholder="Thứ tự">
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <input name="stock_quantity" type="number" min="0" value="{{ old('stock_quantity', 0) }}" class="admin-input" placeholder="Tồn kho" required>
                <input name="min_stock_quantity" type="number" min="0" value="{{ old('min_stock_quantity', 5) }}" class="admin-input" placeholder="Ngưỡng cảnh báo" required>
            </div>

            <textarea name="description" class="admin-input min-h-[92px]" placeholder="Ghi chú món, kích cỡ, combo...">{{ old('description') }}</textarea>

            <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3 text-sm font-bold text-gray-200">
                <input type="checkbox" name="is_active" value="1" class="h-4 w-4 accent-[#d99a32]" checked>
                Hiển thị món trên menu bán hàng
            </label>

            <button type="submit" class="btn-admin w-full">
                <i class="fa-solid fa-plus"></i>
                Lưu món mới
            </button>
        </div>
    </form>

    <div class="admin-panel">
        <div class="panel-header flex-col items-start gap-4 lg:flex-row lg:items-center">
            <div>
                <h5>Danh sách menu & tồn kho</h5>
                <small>Lọc món, sửa giá và nhập/xuất kho theo từng món</small>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.foods.index') }}" class="panel-body grid gap-3 border-b border-white/10 lg:grid-cols-[1fr_180px_180px_auto_auto]">
            <input name="q" value="{{ request('q') }}" class="admin-input" placeholder="Tìm tên, SKU, nhóm món...">
            <select name="category" class="admin-input">
                <option value="">Tất cả nhóm</option>
                @foreach ($categories as $category)
                    <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                @endforeach
            </select>
            <select name="status" class="admin-input">
                <option value="">Tất cả trạng thái</option>
                <option value="active" @selected(request('status') === 'active')>Đang bán</option>
                <option value="low" @selected(request('status') === 'low')>Sắp hết</option>
                <option value="out" @selected(request('status') === 'out')>Hết hàng</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Tạm ẩn</option>
            </select>
            <button class="btn-admin" type="submit">
                <i class="fa-solid fa-filter"></i>
                Lọc
            </button>
            <a href="{{ route('admin.foods.index') }}" class="btn-admin-outline text-center no-underline">Reset</a>
        </form>

        <div class="space-y-4 p-4">
            @forelse ($foods as $food)
                <div class="rounded-3xl border border-white/10 bg-[#0f0f0f] p-4">
                    <div class="grid gap-4 lg:grid-cols-[86px_1fr_auto] lg:items-center">
                        <div class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                            @if ($food->image)
                                <img src="{{ $food->image }}" alt="{{ $food->name }}" class="h-full w-full object-cover">
                            @else
                                <i class="fa-solid fa-burger text-2xl text-[#d99a32]"></i>
                            @endif
                        </div>

                        <div class="min-w-0">
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <span class="rounded-full border px-3 py-1 text-xs font-black {{ $statusClasses[$food->stock_status] ?? $statusClasses['inactive'] }}">
                                    {{ $food->stock_status_label }}
                                </span>
                                @if ($food->sku)
                                    <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-gray-300">{{ $food->sku }}</span>
                                @endif
                                @if ($food->category)
                                    <span class="rounded-full bg-[#d99a32]/15 px-3 py-1 text-xs font-bold text-[#f4c56a]">{{ $food->category }}</span>
                                @endif
                            </div>
                            <h3 class="m-0 truncate text-lg font-black text-white">{{ $food->name }}</h3>
                            <div class="mt-2 grid gap-2 text-sm text-gray-300 md:grid-cols-3">
                                <div>Giá: <strong class="text-white">{{ number_format((float) $food->price, 0, ',', '.') }}đ</strong></div>
                                <div>Tồn: <strong class="text-white">{{ $food->stock_quantity }}</strong></div>
                                <div>Cảnh báo: <strong class="text-white">{{ $food->min_stock_quantity }}</strong></div>
                            </div>
                            @if ($food->description)
                                <p class="m-0 mt-2 text-sm text-gray-500">{{ $food->description }}</p>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                            <form method="POST" action="{{ route('admin.foods.toggle-status', $food) }}">
                                @csrf
                                @method('PATCH')
                                <button class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm font-black text-gray-200 transition hover:bg-white/10" type="submit">
                                    {{ $food->is_active ? 'Tạm ẩn' : 'Bật bán' }}
                                </button>
                            </form>

                            <form method="POST" action="{{ route('admin.foods.destroy', $food) }}" onsubmit="return confirm('Xóa món {{ $food->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button class="action-btn action-delete" type="submit" title="Xóa món" @disabled($food->invoice_items_count > 0)>
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-4 xl:grid-cols-[1fr_360px]">
                        <details class="rounded-2xl border border-white/10 bg-white/[0.03] p-4">
                            <summary class="cursor-pointer text-sm font-black text-[#f4c56a]">Sửa thông tin menu</summary>
                            <form method="POST" action="{{ route('admin.foods.update', $food) }}" class="mt-4 grid gap-3 md:grid-cols-2">
                                @csrf
                                @method('PATCH')
                                <input name="sku" value="{{ old('sku', $food->sku) }}" class="admin-input" placeholder="SKU">
                                <input name="category" value="{{ old('category', $food->category) }}" class="admin-input" placeholder="Nhóm món">
                                <input name="name" value="{{ old('name', $food->name) }}" class="admin-input md:col-span-2" placeholder="Tên món" required>
                                <input name="image" value="{{ old('image', $food->image) }}" class="admin-input md:col-span-2" placeholder="URL hình ảnh">
                                <input name="price" type="number" min="0" value="{{ old('price', (float) $food->price) }}" class="admin-input" placeholder="Giá bán" required>
                                <input name="sort_order" type="number" min="0" value="{{ old('sort_order', $food->sort_order) }}" class="admin-input" placeholder="Thứ tự">
                                <input name="stock_quantity" type="number" min="0" value="{{ old('stock_quantity', $food->stock_quantity) }}" class="admin-input" placeholder="Tồn kho" required>
                                <input name="min_stock_quantity" type="number" min="0" value="{{ old('min_stock_quantity', $food->min_stock_quantity) }}" class="admin-input" placeholder="Ngưỡng cảnh báo" required>
                                <textarea name="description" class="admin-input min-h-[78px] md:col-span-2" placeholder="Ghi chú">{{ old('description', $food->description) }}</textarea>
                                <label class="flex items-center gap-3 rounded-xl border border-white/10 bg-white/[0.03] px-4 py-3 text-sm font-bold text-gray-200 md:col-span-2">
                                    <input type="checkbox" name="is_active" value="1" class="h-4 w-4 accent-[#d99a32]" @checked($food->is_active)>
                                    Hiển thị trên menu
                                </label>
                                <button class="btn-admin md:col-span-2" type="submit">Cập nhật món</button>
                            </form>
                        </details>

                        <form method="POST" action="{{ route('admin.foods.update-stock', $food) }}" class="rounded-2xl border border-white/10 bg-white/[0.03] p-4">
                            @csrf
                            @method('PATCH')
                            <label class="mb-2 block text-sm font-black text-[#f4c56a]">Nhập/xuất kho nhanh</label>
                            <div class="grid gap-3 md:grid-cols-[130px_1fr] xl:grid-cols-1">
                                <input name="adjustment" type="number" class="admin-input" placeholder="+20 hoặc -5" required>
                                <input name="note" class="admin-input" placeholder="Lý do điều chỉnh">
                            </div>
                            <button class="btn-admin mt-3 w-full" type="submit">
                                Cập nhật tồn kho
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="rounded-3xl border border-dashed border-white/15 p-10 text-center text-gray-400">
                    Chưa có món nào trong menu. Hãy thêm món đầu tiên ở khung bên trái.
                </div>
            @endforelse
        </div>

        <div class="border-t border-white/10 p-4">
            {{ $foods->links() }}
        </div>
    </div>
</div>
@endsection
