@extends('layouts.user')

@section('title', 'Chọn đồ ăn - ' . $suatChieu->phim->ten_phim)

{{-- CSS CHỈ TÁC ĐỘNG GIỎ HÀNG DÍNH THEO MÀN HÌNH - KHÔNG LÀM BẠO/BIẾN DẠNG GIAO DIỆN GỐC --}}
<style>
    /* 1. Sửa lỗi thẻ cha làm hỏng thuộc tính sticky */
    html, body, main, .booking-food-page, #bookingWrapper {
        overflow: visible !important;
    }

    /* 2. Cấu hình lại khung Flexbox chuẩn tỉ lệ gốc */
    .booking-food-layout {
        display: flex !important;
        align-items: flex-start !important;
        gap: 24px !important;
    }

    /* Giúp cột danh sách món ăn bên trái tự động chiếm trọn phần không gian còn lại */
    .booking-food-menu {
        flex: 1 1 0% !important;
        min-width: 0 !important;
    }

    /* Cố định kích thước cột giỏ hàng bên phải & giữ dính khi cuộn */
    .booking-food-sidebar {
        flex: 0 0 340px !important;
        width: 340px !important;
        position: -webkit-sticky !important;
        position: sticky !important;
        top: 90px !important; /* Khoảng cách dừng cách Header khi cuộn */
        z-index: 20 !important;
    }

    /* 3. Tùy chỉnh thanh cuộn giỏ hàng mỏng mịn, đồng bộ Dark Theme (Ẩn thanh cuộn trắng xấu) */
    .booking-cart-items {
        max-height: 280px !important;
        overflow-y: auto !important;
        padding-right: 4px !important;
        /* Hỗ trợ Firefox */
        scrollbar-width: thin !important;
        scrollbar-color: #ef4444 rgba(255, 255, 255, 0.05) !important;
    }

    /* Chrome, Safari, Edge */
    .booking-cart-items::-webkit-scrollbar {
        width: 4px !important;
    }
    .booking-cart-items::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.03) !important;
        border-radius: 4px !important;
    }
    .booking-cart-items::-webkit-scrollbar-thumb {
        background: #ef4444 !important;
        border-radius: 4px !important;
    }
    .booking-cart-items::-webkit-scrollbar-thumb:hover {
        background: #dc2626 !important;
    }
</style>

@section('content')
    @php
        $seatSummary = $selectedSeats->implode(', ');
        $seatCount = $selectedSeats->count();
        $categoryCount = collect($menu)->count();
        $productCount = collect($menu)->sum(function ($category) {
            return collect($category['foods'] ?? [])->sum(function ($food) {
                if (!empty($food['is_combo'])) {
                    return 1;
                }

                return max(1, count($food['variants'] ?? []));
            });
        });
    @endphp

    <div class="booking-food-page" lang="vi" spellcheck="false">
        @if(session('error'))
            <div class="booking-seat-alert" style="background: rgba(239, 68, 68, 0.15) !important; border: 1px solid #ef4444 !important; color: #f87171 !important; padding: 16px !important; border-radius: 12px !important; margin: 15px 0 !important; display: flex !important; align-items: center !important; gap: 12px !important; font-weight: 600 !important; font-size: 14px !important; position: relative !important; z-index: 99 !important;">
                <i class="fa-solid fa-circle-exclamation" style="color: #ef4444 !important; font-size: 18px !important;"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        <section class="booking-food-hero">
            <div class="booking-flow-hero-copy">
                <span class="booking-eyebrow">
                    <i class="fa-solid fa-burger"></i>
                    Bước 3 trong 4
                </span>
                <h1>Thêm combo ngon cho buổi xem phim.</h1>
                <p>
                    Ghế <strong>{{ $seatSummary }}</strong> đã được giữ cho
                    <strong>{{ $suatChieu->phim->ten_phim }}</strong>.
                    Chọn thêm đồ ăn hoặc tiếp tục thanh toán nếu bạn muốn bỏ qua bước này.
                </p>

                <div class="booking-seat-mini-stats" aria-label="Tóm tắt chọn đồ ăn">
                    <div>
                        <strong>{{ $seatCount }}</strong>
                        <span>Ghế đã chọn</span>
                    </div>
                    <div>
                        <strong>{{ $categoryCount }}</strong>
                        <span>Danh mục</span>
                    </div>
                    <div>
                        <strong>{{ number_format($seatTotalPrice, 0, ',', '.') }}đ</strong>
                        <span>Tiền ghế</span>
                    </div>
                </div>
            </div>

            <div class="booking-stepper" aria-label="Tiến trình đặt vé">
                <div class="booking-step is-done">
                    <span><i class="fa-solid fa-check"></i></span>
                    <strong>Chọn phim</strong>
                </div>
                <div class="booking-step is-done">
                    <span><i class="fa-solid fa-check"></i></span>
                    <strong>Chọn ghế</strong>
                </div>
                <div class="booking-step is-active">
                    <span>3</span>
                    <strong>Đồ ăn</strong>
                </div>
                <div class="booking-step">
                    <span>4</span>
                    <strong>Thanh toán</strong>
                </div>
            </div>
        </section>

        <div id="bookingWrapper">
            <div class="booking-food-layout">
                <section class="booking-food-menu" aria-label="Danh sách đồ ăn">
                    <div class="booking-food-progress">
                        <div>
                            <div>
                                <span class="booking-eyebrow">
                                    <i class="fa-regular fa-clock"></i>
                                    Thời gian giữ vé
                                </span>
                                <h2>Hoàn tất đơn trong thời gian còn lại</h2>
                                <p>Hết thời gian, ghế đang giữ sẽ được nhả lại để người khác đặt.</p>
                            </div>

                            <div class="booking-food-timer" aria-live="polite">
                                <span>Còn lại</span>
                                <strong id="countdown">07:00</strong>
                            </div>
                        </div>

                        <div class="booking-food-countdown-track">
                            <div id="countdownBar" style="width: 100%"></div>
                        </div>
                    </div>

                    <div class="booking-food-tools">
                        <label class="booking-food-search" for="foodSearchInput">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input id="foodSearchInput" type="search" placeholder="Tìm bắp, nước, combo...">
                        </label>

                        <div class="booking-food-category-rail" aria-label="Danh mục nhanh">
                            @foreach ($menu as $category)
                                @php
                                    $categoryId = \Illuminate\Support\Str::slug($category['category']) ?: 'category-' . $loop->index;
                                @endphp
                                <a href="#food-cat-{{ $categoryId }}">{{ $category['category'] }}</a>
                            @endforeach
                        </div>
                    </div>

                    @foreach ($menu as $category)
                        @php
                            $categoryId = \Illuminate\Support\Str::slug($category['category']) ?: 'category-' . $loop->index;
                            $categoryNameLower = mb_strtolower($category['category']);
                            $categoryIcon = str_contains($categoryNameLower, 'uống')
                                ? 'fa-mug-saucer'
                                : (str_contains($categoryNameLower, 'combo') || str_contains($categoryNameLower, 'quà')
                                    ? 'fa-gift'
                                    : 'fa-burger');

                            $foodCards = collect($category['foods'] ?? [])->flatMap(function ($food) {
                                if (!empty($food['is_combo'])) {
                                    return [[
                                        'key' => 'combo-' . $food['id'],
                                        'name' => $food['name'],
                                        'price' => $food['price'] ?? 0,
                                        'stock' => $food['available'] ?? 999,
                                        'image' => $food['image'] ?? '',
                                        'type' => 'combo',
                                        'badge' => 'Combo',
                                    ]];
                                }

                                $variants = $food['variants'] ?? [];

                                if (count($variants) === 0) {
                                    $variants = [[
                                        'id' => $food['id'],
                                        'value' => '',
                                        'price' => $food['price'] ?? 0,
                                        'stock' => $food['stock'] ?? 999,
                                        'image' => $food['image'] ?? '',
                                    ]];
                                }

                                return collect($variants)->map(function ($variant) use ($food) {
                                    $variantName = !empty($variant['value']) ? ' - ' . $variant['value'] : '';

                                    return [
                                        'key' => 'variant-' . $variant['id'],
                                        'name' => $food['name'] . $variantName,
                                        'price' => $variant['price'] ?? 0,
                                        'stock' => $variant['stock'] ?? 999,
                                        'image' => $variant['image'] ?? $food['image'] ?? '',
                                        'type' => 'variant',
                                        'badge' => !empty($variant['value']) ? $variant['value'] : 'Món lẻ',
                                    ];
                                })->all();
                            })->values();
                        @endphp

                        <section id="food-cat-{{ $categoryId }}" class="booking-food-category" data-food-category>
                            <div class="booking-food-category-head">
                                <div>
                                    <i class="fa-solid {{ $categoryIcon }}"></i>
                                </div>
                                <div>
                                    <span>{{ $foodCards->count() }} lựa chọn</span>
                                    <h2>{{ $category['category'] }}</h2>
                                </div>
                            </div>

                            <div class="booking-food-grid">
                                @foreach ($foodCards as $item)
                                    @php
                                        $rawImage = trim((string) ($item['image'] ?? ''));
                                        $fallbackImage = asset('assets/images/LOGO copy.png');

                                        if (empty($rawImage)) {
                                            $imageUrl = $fallbackImage;
                                        } elseif (str_starts_with($rawImage, 'http://') || str_starts_with($rawImage, 'https://')) {
                                            $imageUrl = $rawImage;
                                        } elseif (str_starts_with($rawImage, 'assets/')) {
                                            $imageUrl = asset($rawImage);
                                        } elseif (str_starts_with($rawImage, 'storage/')) {
                                            $imageUrl = asset($rawImage);
                                        } elseif (str_starts_with($rawImage, '/storage/')) {
                                            $imageUrl = asset(ltrim($rawImage, '/'));
                                        } else {
                                            $cleanPath = ltrim($rawImage, '/');
                                            if (! str_starts_with($cleanPath, 'foods/')) {
                                                $cleanPath = 'foods/' . $cleanPath;
                                            }
                                            $imageUrl = asset('storage/' . $cleanPath);
                                        }

                                        $stock = (int) ($item['stock'] ?? 0);
                                        $isSoldOut = $stock <= 0;
                                        $searchText = mb_strtolower($item['name'] . ' ' . $category['category'] . ' ' . $item['badge']);
                                    @endphp

                                    <article class="booking-food-card {{ $isSoldOut ? 'is-sold-out' : '' }}"
                                        data-food-card
                                        data-search="{{ $searchText }}">
                                        <div class="booking-food-image">
                                            <img src="{{ $imageUrl }}" alt="{{ $item['name'] }}" onerror="this.onerror=null;this.src='{{ $fallbackImage }}';">
                                            <span>{{ $item['badge'] }}</span>
                                        </div>

                                        <div class="booking-food-card-body">
                                            <h3>{{ $item['name'] }}</h3>

                                            <div class="booking-food-meta">
                                                <strong>{{ number_format($item['price'], 0, ',', '.') }}đ</strong>
                                                <span>{{ $isSoldOut ? 'Hết món' : 'Còn ' . $stock }}</span>
                                            </div>

                                            <div class="booking-food-actions">
                                                <div class="booking-food-qty">
                                                    <button class="btn-qty-decrease" type="button" aria-label="Giảm số lượng" @disabled($isSoldOut)>
                                                        <i class="fa-solid fa-minus"></i>
                                                    </button>

                                                    <input type="number" min="1" max="{{ $stock }}" value="1" class="item-qty" @disabled($isSoldOut)>

                                                    <button class="btn-qty-increase" type="button" aria-label="Tăng số lượng" @disabled($isSoldOut)>
                                                        <i class="fa-solid fa-plus"></i>
                                                    </button>
                                                </div>

                                                <button class="btn-add-to-cart"
                                                    data-item-key="{{ $item['key'] }}"
                                                    data-item-name="{{ $item['name'] }}"
                                                    data-item-price="{{ $item['price'] ?? 0 }}"
                                                    data-item-image="{{ $item['image'] }}"
                                                    data-item-image-url="{{ $imageUrl }}"
                                                    data-item-type="{{ $item['type'] }}"
                                                    data-item-stock="{{ $stock }}"
                                                    type="button"
                                                    @disabled($isSoldOut)>
                                                    {{ $isSoldOut ? 'Hết món' : 'Thêm vào giỏ' }}
                                                </button>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </section>

                <aside id="bookingSidebar" class="booking-food-sidebar" aria-label="Giỏ hàng đồ ăn">
                    <div class="booking-cart-panel">
                        <div class="booking-cart-head">
                            <div>
                                <span>Đơn hàng</span>
                                <h2>Giỏ đồ ăn</h2>
                            </div>
                            <strong id="cartCount">0</strong>
                        </div>

                        <div class="booking-cart-seat">
                            <span>Ghế đã chọn</span>
                            <strong>{{ $seatSummary }}</strong>
                        </div>

                        <div id="cartItems" class="booking-cart-items">
                            <p>Chưa có món nào</p>
                        </div>

                        <button id="btnClearCart" type="button" class="booking-cart-clear">
                            <i class="fa-solid fa-trash-can"></i>
                            Xóa giỏ đồ ăn
                        </button>
                    </div>

                    <div class="booking-cart-total">
                        <div>
                            <span>Tiền ghế</span>
                            <strong>{{ number_format($seatTotalPrice, 0, ',', '.') }}đ</strong>
                        </div>
                        <div>
                            <span>Đồ ăn</span>
                            <strong id="foodSubtotal">0đ</strong>
                        </div>
                        <div class="is-grand">
                            <span>Tổng tiền</span>
                            <strong id="subtotalPrice">{{ number_format($seatTotalPrice, 0, ',', '.') }}đ</strong>
                        </div>
                    </div>

                    <a id="btnCheckout" href="{{ route('dat_ve.checkout', ['suat_chieu_id' => $suatChieu->id]) }}"
                        class="booking-cart-checkout">
                        Tiếp tục thanh toán
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                    <a href="{{ route('dat_ve.chon_ghe', ['movie' => $suatChieu->id]) }}?ghe={{ urlencode(request()->query('ghe') ?: $selectedSeats->implode(',')) }}@if(! empty($pendingTicketId))&pending_ticket_id={{ urlencode($pendingTicketId) }}@endif"
                        class="booking-cart-back">
                        <i class="fa-solid fa-arrow-left"></i>
                        Quay lại chọn ghế
                    </a>
                </aside>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    const storageKey = 'booking_deadline_{{ $suatChieu->id }}';
    const pendingTicketId = "{{ $pendingTicketId ?? '' }}";
    const pendingDeadline = "{{ $pendingDeadline ?? '' }}";
    const initialSelectedSeats = JSON.parse('@json($selectedSeats->values()->all())');
    const initialFoodCart = @json($initialFoodCart ?? []);
    const baseSeatPrice = parseInt("{{ $seatTotalPrice }}") || 0;
    const fallbackFoodImage = "{{ asset('assets/images/LOGO copy.png') }}";

    // KEY BẢO VỆ GIỎ HÀNG: Tạo định danh riêng cho từng đơn hàng (Chống dính đồ ăn đơn cũ)
    const sortedSeatsKey = [...initialSelectedSeats].sort().join('_');
    const cartStorageKey = pendingTicketId 
        ? `food_cart_ticket_${pendingTicketId}` 
        : `food_cart_suat_{{ $suatChieu->id }}_${sortedSeatsKey}`;

    let cart = {};
    let countdownInterval = null;
    let countdownExpired = false;

    function formatCurrency(value) {
        return Number(value || 0).toLocaleString('vi-VN') + 'đ';
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function sanitizeCart() {
        Object.keys(cart).forEach(key => {
            const item = cart[key];
            item.qty = Math.max(0, Number(item.qty || 0));
            item.price = Number(item.price || 0);
            item.stock = Number(item.stock || 999);
            if (!item.imageUrl || item.imageUrl === '' || item.imageUrl.includes('undefined')) {
                item.imageUrl = fallbackFoodImage;
            }

            if (item.qty <= 0) {
                delete cart[key];
            }
        });
    }

    function getCartItems() {
        sanitizeCart();
        return Object.values(cart).filter(item => item.qty > 0);
    }

    function renderCart() {
        const cartItems = document.getElementById('cartItems');
        const subtotalPrice = document.getElementById('subtotalPrice');
        const foodSubtotal = document.getElementById('foodSubtotal');
        const cartCount = document.getElementById('cartCount');
        const clearCartBtn = document.getElementById('btnClearCart');

        if (!cartItems || !subtotalPrice) return;

        const items = getCartItems();
        let foodTotal = 0;
        let totalQty = 0;

        const html = items.map(item => {
            foodTotal += item.qty * item.price;
            totalQty += item.qty;

            return `
                <div class="booking-cart-item">
                    <img src="${escapeHtml(item.imageUrl || fallbackFoodImage)}" alt="${escapeHtml(item.name)}" onerror="this.onerror=null;this.src='${escapeHtml(fallbackFoodImage)}';">
                    <div>
                        <h3>${escapeHtml(item.name)}</h3>
                        <span>${formatCurrency(item.price)}</span>
                    </div>
                    <div class="booking-cart-qty">
                        <button class="btn-cart-decrease" data-item-key="${escapeHtml(item.key)}" type="button" aria-label="Giảm ${escapeHtml(item.name)}">
                            <i class="fa-solid fa-minus"></i>
                        </button>
                        <strong>${item.qty}</strong>
                        <button class="btn-cart-increase" data-item-key="${escapeHtml(item.key)}" type="button" aria-label="Tăng ${escapeHtml(item.name)}">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                    <button class="booking-cart-remove" data-remove-cart-item="${escapeHtml(item.key)}" type="button" aria-label="Xóa ${escapeHtml(item.name)}">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            `;
        }).join('');

        cartItems.innerHTML = html || '<p>Chưa có món nào</p>';

        if (foodSubtotal) foodSubtotal.innerText = formatCurrency(foodTotal);
        if (cartCount) cartCount.innerText = totalQty;
        if (clearCartBtn) clearCartBtn.disabled = items.length === 0;

        subtotalPrice.innerText = formatCurrency(baseSeatPrice + foodTotal);
        
        // Lưu dữ liệu giỏ hàng riêng cho đơn này
        localStorage.setItem(cartStorageKey, JSON.stringify(cart));

        updateCheckoutButton();
    }

    function addToCart(item) {
        if (countdownExpired) return;

        const stock = Number(item.stock || 0);
        const addQty = Number(item.qty || 1);

        if (stock <= 0) {
            alert('Món này hiện đã hết.');
            return;
        }

        if (!cart[item.key]) {
            cart[item.key] = {
                key: item.key,
                name: item.name,
                price: Number(item.price),
                image: item.image,
                imageUrl: item.imageUrl || fallbackFoodImage,
                stock: stock,
                qty: 0
            };
        }

        if (cart[item.key].qty + addQty > stock) {
            alert('Số lượng vượt tồn kho.');
            return;
        }

        cart[item.key].qty += addQty;
        renderCart();
    }

    function updateCheckoutButton() {
        const btn = document.getElementById('btnCheckout');
        if (!btn) return;

        if (countdownExpired) {
            btn.classList.add('opacity-50', 'pointer-events-none');
            btn.innerHTML = 'Hết thời gian <i class="fa-solid fa-clock"></i>';
            return;
        }

        btn.classList.remove('opacity-50', 'pointer-events-none');
        btn.innerHTML = 'Tiếp tục thanh toán <i class="fa-solid fa-arrow-right"></i>';
    }

    function startCountdown() {
        const countdownEl = document.getElementById('countdown');
        const countdownBar = document.getElementById('countdownBar');

        if (!countdownEl || !countdownBar) return;

        let deadline = Number(localStorage.getItem(storageKey));
        const serverDeadline = Number(pendingDeadline) || null;
        const validStoredDeadline = deadline && deadline > Date.now() ? deadline : null;
        const validServerDeadline = serverDeadline && serverDeadline > Date.now() ? serverDeadline : null;

        if (validStoredDeadline && validServerDeadline) {
            deadline = Math.min(validStoredDeadline, validServerDeadline);
        } else if (validServerDeadline) {
            deadline = validServerDeadline;
        } else if (validStoredDeadline) {
            deadline = validStoredDeadline;
        } else {
            deadline = Date.now() + 7 * 60 * 1000;
        }

        localStorage.setItem(storageKey, deadline);

        const update = () => {
            const remaining = deadline - Date.now();

            if (remaining <= 0) {
                clearInterval(countdownInterval);
                countdownExpired = true;
                localStorage.removeItem(storageKey);
                countdownEl.innerText = '00:00';
                countdownBar.style.width = '0%';

                document.querySelectorAll('.btn-add-to-cart, .btn-qty-decrease, .btn-qty-increase').forEach(btn => {
                    btn.disabled = true;
                    btn.classList.add('opacity-50', 'cursor-not-allowed');
                });

                updateCheckoutButton();
                return;
            }

            const minutes = String(Math.floor(remaining / 60000)).padStart(2, '0');
            const seconds = String(Math.floor((remaining % 60000) / 1000)).padStart(2, '0');
            const percent = Math.max(0, Math.min(100, (remaining / (7 * 60 * 1000)) * 100));

            countdownEl.innerText = `${minutes}:${seconds}`;
            countdownBar.style.width = `${percent}%`;
            countdownEl.classList.toggle('animate-pulse', remaining <= 60000);
        };

        update();
        countdownInterval = setInterval(update, 1000);
    }

    function getFoodCard(element) {
        return element.closest('[data-food-card]');
    }

    function clampQty(input) {
        const max = Number(input.getAttribute('max') || 999);
        let value = Number(input.value || 1);

        if (Number.isNaN(value) || value < 1) value = 1;
        if (value > max) value = max;

        input.value = value;
        return value;
    }

    function filterFoodCards(query) {
        const normalized = String(query || '').trim().toLowerCase();

        document.querySelectorAll('[data-food-card]').forEach(card => {
            const haystack = (card.dataset.search || '').toLowerCase();
            card.classList.toggle('hidden', normalized.length > 0 && !haystack.includes(normalized));
        });

        document.querySelectorAll('[data-food-category]').forEach(category => {
            const visibleCards = category.querySelectorAll('[data-food-card]:not(.hidden)').length;
            category.classList.toggle('hidden', visibleCards === 0);
        });
    }

    document.addEventListener('input', function(event) {
        if (event.target.id === 'foodSearchInput') {
            filterFoodCards(event.target.value);
            return;
        }

        const input = event.target.closest('.item-qty');
        if (!input) return;

        clampQty(input);
    });

    document.addEventListener('click', function(event) {
        const addButton = event.target.closest('.btn-add-to-cart');
        if (addButton) {
            event.preventDefault();

            const card = getFoodCard(addButton);
            const qtyEl = card ? card.querySelector('.item-qty') : null;
            const qty = qtyEl ? clampQty(qtyEl) : 1;

            addToCart({
                key: addButton.dataset.itemKey,
                name: addButton.dataset.itemName,
                price: addButton.dataset.itemPrice,
                image: addButton.dataset.itemImage,
                imageUrl: addButton.dataset.itemImageUrl,
                stock: addButton.dataset.itemStock,
                qty: qty
            });

            if (qtyEl) qtyEl.value = 1;
            return;
        }

        const minus = event.target.closest('.btn-qty-decrease');
        if (minus) {
            const card = getFoodCard(minus);
            const qtyEl = card ? card.querySelector('.item-qty') : null;
            if (qtyEl) qtyEl.value = Math.max(1, Number(qtyEl.value || 1) - 1);
            return;
        }

        const plus = event.target.closest('.btn-qty-increase');
        if (plus) {
            const card = getFoodCard(plus);
            const qtyEl = card ? card.querySelector('.item-qty') : null;
            if (!qtyEl) return;

            const max = Number(qtyEl.getAttribute('max') || 999);
            const current = Number(qtyEl.value || 1);

            if (current >= max) {
                alert('Đã đạt số lượng tối đa.');
                return;
            }

            qtyEl.value = current + 1;
            return;
        }

        const dec = event.target.closest('.btn-cart-decrease');
        if (dec) {
            const key = dec.dataset.itemKey;
            if (cart[key]) {
                cart[key].qty--;
                if (cart[key].qty <= 0) delete cart[key];
                renderCart();
            }
            return;
        }

        const inc = event.target.closest('.btn-cart-increase');
        if (inc) {
            const key = inc.dataset.itemKey;

            if (cart[key]) {
                if (cart[key].qty >= cart[key].stock) {
                    alert('Đã đạt số lượng tối đa.');
                    return;
                }

                cart[key].qty++;
                renderCart();
            }
            return;
        }

        const remove = event.target.closest('[data-remove-cart-item]');
        if (remove) {
            delete cart[remove.dataset.removeCartItem];
            renderCart();
            return;
        }

        const clearCart = event.target.closest('#btnClearCart');
        if (clearCart) {
            cart = {};
            renderCart();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const checkoutBtn = document.getElementById('btnCheckout');

        // Lấy dữ liệu đồ ăn riêng biệt của đơn này
        if (Array.isArray(initialFoodCart) && initialFoodCart.length > 0) {
            cart = Object.fromEntries(initialFoodCart.map(item => [item.key, item]));
        } else {
            cart = JSON.parse(localStorage.getItem(cartStorageKey) || '{}');
        }

        renderCart();
        startCountdown();

        if (checkoutBtn) {
            checkoutBtn.addEventListener('click', function(e) {
                e.preventDefault();

                const items = getCartItems();
                const cartData = encodeURIComponent(JSON.stringify(items));
                const baseUrl = this.getAttribute('href');

                if (!baseUrl || baseUrl === '#') {
                    alert('Tuyến đường checkout chưa được thiết lập cấu hình.');
                    return;
                }

                const currentParams = new URLSearchParams(window.location.search);
                const seatParam = currentParams.get('ghe') || '';

                let url = baseUrl + (baseUrl.includes('?') ? '&' : '?') + 'food_cart=' + cartData;
                if (seatParam) {
                    url += '&ghe=' + encodeURIComponent(seatParam);
                }
                if (pendingTicketId) {
                    url += '&pending_ticket_id=' + encodeURIComponent(pendingTicketId);
                }

                window.location.href = url;
            });
        }
    });
</script>
@endsection