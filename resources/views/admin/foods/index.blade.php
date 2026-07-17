@extends('layouts.admin')

@section('title', 'Cấu hình Menu & Kho hàng')
@section('page-title', 'Cấu hình Menu & Kho hàng')
@section('page-subtitle', 'Quản lý món, giá bán, tồn kho và trạng thái hiển thị')

@section('content')
@php
    $activeFilters = collect([
        request('q'),
        request('category_id'),
        request('status'),
    ])->filter()->count();

    $statusOptions = [
        'active' => 'Đang bán',
        'inactive' => 'Tạm ẩn',
        'low' => 'Sắp hết hàng',
    ];
@endphp

<div class="food-menu-page">
    @include('admin.partials.flash')

    <section class="food-menu-hero">
        <div>
            <span class="food-menu-eyebrow">
                <i class="fa-solid fa-boxes-stacked"></i>
                Menu & kho hàng
            </span>
            <h2>Quản lý đồ ăn, nước uống và combo</h2>
            <p>Theo dõi giá bán, tồn kho, trạng thái hiển thị và thao tác nhanh với từng món tại quầy.</p>
        </div>

        <div class="food-menu-actions">
            <a href="{{ route('admin.foods.categories.index') }}" class="food-menu-btn is-soft">
                <i class="fa-solid fa-layer-group"></i>
                Danh mục
            </a>
            <a href="{{ route('admin.foods.create') }}" class="food-menu-btn">
                <i class="fa-solid fa-plus"></i>
                Thêm món
            </a>
        </div>
    </section>

    <section class="food-menu-stats">
        <div class="food-menu-stat">
            <span>Tổng món</span>
            <strong>{{ $summary['total'] }}</strong>
        </div>
        <div class="food-menu-stat is-good">
            <span>Đang bán</span>
            <strong>{{ $summary['active'] }}</strong>
        </div>
        <div class="food-menu-stat is-muted">
            <span>Tạm ẩn</span>
            <strong>{{ $summary['inactive'] }}</strong>
        </div>
        <div class="food-menu-stat is-warn">
            <span>Sắp hết hàng</span>
            <strong>{{ $summary['low_stock'] ?? 0 }}</strong>
        </div>
    </section>

    <section class="food-menu-panel">
        <div class="food-menu-panel-head">
            <div>
                <span class="food-menu-eyebrow">Danh sách</span>
                <h3>Món đang cấu hình</h3>
                <p>{{ $foods->total() }} kết quả theo bộ lọc hiện tại.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.foods.index') }}" class="food-menu-filter">
            <label class="food-menu-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Tìm món, SKU hoặc nhóm món...">
            </label>

            <select name="category_id" class="admin-input">
                <option value="">Tất cả nhóm món</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            <select name="status" class="admin-input">
                <option value="">Tất cả trạng thái</option>
                @foreach ($statusOptions as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <button type="submit" class="food-menu-filter-btn">
                <i class="fa-solid fa-filter"></i>
                Lọc
                @if ($activeFilters)
                    <span>{{ $activeFilters }}</span>
                @endif
            </button>

            @if ($activeFilters)
                <a href="{{ route('admin.foods.index') }}" class="food-menu-reset-btn" title="Xóa bộ lọc">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            @endif
        </form>

        <div class="food-menu-list">
            @forelse ($foods as $food)
                @php
                    $isCombo = $food->isCombo();
                    $stock = $food->stock_quantity;
                    $isLowStock = $stock <= $food->min_stock_quantity;
                    $imagePath = $food->image
                        ? asset('storage/' . (str_starts_with($food->image, 'foods/') ? $food->image : 'foods/' . $food->image))
                        : null;
                @endphp

                <article class="food-menu-card {{ ! $food->is_active ? 'is-inactive' : '' }}">
                    <div class="food-menu-media">
                        @if ($imagePath)
                            <img src="{{ $imagePath }}" alt="{{ $food->name }}">
                        @else
                            <i class="fa-solid fa-burger"></i>
                        @endif
                    </div>

                    <div class="food-menu-info">
                        <div class="food-menu-tags">
                            <span class="food-menu-status {{ $food->is_active ? 'is-active' : 'is-hidden' }}">
                                {{ $food->is_active ? 'Đang bán' : 'Tạm ẩn' }}
                            </span>
                            <span>{{ $isCombo ? 'Combo' : 'Món lẻ' }}</span>
                            <span>{{ optional($food->category)->name ?? 'Chưa phân loại' }}</span>
                        </div>

                        <h3>{{ $food->name }}</h3>

                        <p>
                            SKU: <strong>{{ $food->sku ?: 'Chưa có' }}</strong>
                            @if ($food->description)
                                <span>{{ \Illuminate\Support\Str::limit($food->description, 90) }}</span>
                            @endif
                        </p>
                    </div>

                    <div class="food-menu-metrics">
                        <div>
                            <span>Giá bán</span>
                            <strong>{{ number_format((float) $food->price, 0, ',', '.') }}đ</strong>
                        </div>
                        <div class="{{ $isLowStock ? 'is-low' : '' }}">
                            <span>Tồn kho</span>
                            <strong>{{ $stock }}</strong>
                        </div>
                        <div>
                            <span>{{ $isCombo ? 'Thành phần' : 'Biến thể' }}</span>
                            <strong>{{ $isCombo ? $food->comboItems->count() : $food->variants->count() }}</strong>
                        </div>
                        <div>
                            <span>Đã bán</span>
                            <strong>{{ $food->invoice_items_count }}</strong>
                        </div>
                    </div>

                    <div class="food-menu-card-actions">
                        <a href="{{ route('admin.foods.show', $food) }}" class="food-menu-icon-btn is-view">
                            <i class="fa-solid fa-eye"></i>
                            Xem
                        </a>

                        <a href="{{ route('admin.foods.edit', $food) }}" class="food-menu-icon-btn is-edit">
                            <i class="fa-solid fa-pen"></i>
                            Sửa
                        </a>

                        <form method="POST" action="{{ route('admin.foods.toggle-status', $food) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="food-menu-icon-btn {{ $food->is_active ? 'is-hide' : 'is-show' }}">
                                <i class="fa-solid {{ $food->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                {{ $food->is_active ? 'Ẩn' : 'Hiện' }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.foods.destroy', $food) }}" onsubmit="return confirm('Xóa món {{ $food->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="food-menu-icon-btn is-delete">
                                <i class="fa-solid fa-trash"></i>
                                Xóa
                            </button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="food-menu-empty">
                    <i class="fa-solid fa-burger"></i>
                    <h3>Chưa có món phù hợp</h3>
                    <p>Thử đổi bộ lọc hoặc thêm món mới để bắt đầu cấu hình menu.</p>
                    <a href="{{ route('admin.foods.create') }}" class="food-menu-btn">
                        <i class="fa-solid fa-plus"></i>
                        Thêm món
                    </a>
                </div>
            @endforelse
        </div>

        <div class="food-menu-pagination">
            {{ $foods->links() }}
        </div>
    </section>
</div>
@endsection
