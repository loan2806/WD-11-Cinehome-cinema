@extends('layouts.admin')

@section('title', 'Chi tiết món')
@section('page-title', 'Chi tiết món')
@section('page-subtitle', 'Xem thông tin món, danh mục, giá bán, tồn kho và cấu hình combo')

@section('content')
@php
    $isCombo = $food->isCombo();
    $tongTonKho = $food->tong_ton_kho;
    $stock = $food->stock_quantity;
    $isLowStock = $stock <= $food->min_stock_quantity;
    $imagePath = $food->image
        ? asset('storage/' . (str_starts_with($food->image, 'foods/') ? $food->image : 'foods/' . $food->image))
        : null;
@endphp

<div class="food-show-page">
    @include('admin.partials.flash')

    <section class="food-show-hero">
        <div class="food-show-media">
            @if ($imagePath)
                <img src="{{ $imagePath }}" alt="{{ $food->name }}">
            @else
                <i class="fa-solid fa-burger"></i>
                <span>Chưa có ảnh</span>
            @endif
        </div>

        <div class="food-show-main">
            <div class="food-show-tags">
                <span class="food-show-status {{ $food->is_active ? 'is-active' : 'is-hidden' }}">
                    {{ $food->is_active ? 'Đang bán' : 'Tạm ẩn' }}
                </span>
                <span>{{ $isCombo ? 'Combo' : 'Món lẻ' }}</span>
                <span>{{ optional($food->category)->name ?? 'Chưa phân loại' }}</span>
            </div>

            <h2>{{ $food->name }}</h2>
            <p>{{ $food->description ?: 'Món này chưa có mô tả chi tiết.' }}</p>

            <div class="food-show-code">
                <span>SKU</span>
                <strong>{{ $food->sku ?: 'Chưa có' }}</strong>
            </div>

            <div class="food-show-actions">
                <a href="{{ route('admin.foods.index') }}" class="food-show-action is-soft">
                    <i class="fa-solid fa-arrow-left"></i>
                    Quay lại
                </a>
                <a href="{{ route('admin.foods.edit', $food) }}" class="food-show-action">
                    <i class="fa-solid fa-pen"></i>
                    Sửa món
                </a>
                <form method="POST" action="{{ route('admin.foods.toggle-status', $food) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="food-show-action is-soft">
                        <i class="fa-solid {{ $food->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                        {{ $food->is_active ? 'Ẩn món' : 'Hiện món' }}
                    </button>
                </form>
            </div>
        </div>
    </section>

    <section class="food-show-stats">
        <div class="food-show-stat">
            <span>Giá bán</span>
            <strong>{{ number_format((float) $food->price, 0, ',', '.') }}đ</strong>
        </div>
        <div class="food-show-stat">
            <span>Tồn kho</span>
            <strong>{{ $tongTonKho }}</strong>
        </div>
        <div class="food-show-stat {{ $isLowStock ? 'is-warn' : 'is-good' }}">
            <span>Tồn kho khả dụng</span>
            <strong>{{ $stock }}</strong>
        </div>
        <div class="food-show-stat">
            <span>{{ $isCombo ? 'Thành phần' : 'Biến thể' }}</span>
            <strong>{{ $isCombo ? $food->comboItems->count() : $food->variants->count() }}</strong>
        </div>
        <div class="food-show-stat">
            <span>Đã bán</span>
            <strong>{{ $food->invoice_items_count }}</strong>
        </div>
    </section>

    <div class="food-show-grid">
        <section class="food-show-panel">
            <div class="food-show-panel-head">
                <div>
                    <span class="food-show-eyebrow">Thông tin</span>
                    <h3>Thông tin món</h3>
                </div>
            </div>

            <div class="food-show-info-list">
                <div>
                    <span>Danh mục</span>
                    <strong>{{ optional($food->category)->name ?? 'Chưa phân loại' }}</strong>
                </div>
                <div>
                    <span>Loại món</span>
                    <strong>{{ $isCombo ? 'Combo' : 'Sản phẩm thường' }}</strong>
                </div>
                <div>
                    <span>Trạng thái</span>
                    <strong>{{ $food->is_active ? 'Đang bán' : 'Tạm ẩn' }}</strong>
                </div>
                <div>
                    <span>Thứ tự sắp xếp</span>
                    <strong>{{ $food->sort_order ?? 0 }}</strong>
                </div>
                <div>
                    <span>Ngày tạo</span>
                    <strong>{{ $food->created_at?->format('d/m/Y H:i') }}</strong>
                </div>
                <div>
                    <span>Cập nhật gần nhất</span>
                    <strong>{{ $food->updated_at?->format('d/m/Y H:i') }}</strong>
                </div>
            </div>
        </section>

        @if (! $isCombo)
            <section class="food-show-panel">
                <div class="food-show-panel-head">
                    <div>
                        <span class="food-show-eyebrow">Kho & giá</span>
                        <h3>Biến thể món</h3>
                    </div>
                    <a href="{{ route('admin.foods.variants.index', $food) }}" class="food-show-small-btn">
                        <i class="fa-solid fa-sliders"></i>
                        Quản lý
                    </a>
                </div>

                <div class="food-show-variant-list">
                    @forelse ($food->variants as $variant)
                        <article class="food-show-variant-card {{ ! $variant->is_active ? 'is-inactive' : '' }}">
                            <div>
                                <span>Biến thể</span>
                                <strong>{{ $variant->value ?: 'Mặc định' }}</strong>
                            </div>
                            <div>
                                <span>Giá</span>
                                <strong>{{ number_format((float) $variant->price, 0, ',', '.') }}đ</strong>
                            </div>
                            <div class="{{ $variant->stock_quantity <= $food->min_stock_quantity ? 'is-low' : '' }}">
                                <span>Tồn kho</span>
                                <strong>{{ $variant->stock_quantity }}</strong>
                            </div>
                            <div>
                                <span>Trạng thái</span>
                                <strong>{{ $variant->is_active ? 'Đang bán' : 'Tạm ẩn' }}</strong>
                            </div>
                            <a href="{{ route('admin.foods.variants.edit', [$food, $variant]) }}" class="food-show-mini-action">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                        </article>
                    @empty
                        <div class="food-show-empty">
                            <i class="fa-solid fa-box-open"></i>
                            <h3>Chưa có biến thể</h3>
                            <p>Thêm biến thể để quản lý giá bán và tồn kho cho món này.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        @else
            <section class="food-show-panel">
                <div class="food-show-panel-head">
                    <div>
                        <span class="food-show-eyebrow">Combo</span>
                        <h3>Thành phần combo</h3>
                    </div>
                    <a href="{{ route('admin.foods.edit', $food) }}" class="food-show-small-btn">
                        <i class="fa-solid fa-pen"></i>
                        Chỉnh sửa
                    </a>
                </div>

                <div class="food-show-combo-list">
                    @forelse ($food->comboItems as $item)
                        @php
                            $variant = $item->variant;
                            $baseFood = $variant?->doAn;
                        @endphp
                        <article class="food-show-combo-card">
                            <div>
                                <span>Món thành phần</span>
                                <strong>{{ $baseFood?->name ?? 'Không rõ món' }}</strong>
                                <small>{{ $variant?->value ?: 'Mặc định' }} · {{ optional($baseFood?->category)->name ?? 'Chưa phân loại' }}</small>
                            </div>
                            <div>
                                <span>Số lượng trong combo</span>
                                <strong>x{{ $item->quantity }}</strong>
                            </div>
                            <div class="{{ (int) ($variant?->stock_quantity ?? 0) <= $food->min_stock_quantity ? 'is-low' : '' }}">
                                <span>Tồn kho biến thể</span>
                                <strong>{{ (int) ($variant?->stock_quantity ?? 0) }}</strong>
                            </div>
                        </article>
                    @empty
                        <div class="food-show-empty">
                            <i class="fa-solid fa-box-open"></i>
                            <h3>Combo chưa có thành phần</h3>
                            <p>Thêm thành phần để combo có thể bán và tính tồn kho chính xác.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        @endif
    </div>
</div>
@endsection
