@extends('layouts.admin')

@section('title','Chọn đồ ăn tại quầy - '.$suatChieu->phim->ten_phim)

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
<link rel="stylesheet" href="{{ asset('assets/css/user-home.css') }}?v={{ filemtime(public_path('assets/css/user-home.css')) }}">

@section('content')
    @php
        $seatSummary = $selectedSeats->implode(', ');
        $seatCount = $selectedSeats->count();
        $categoryCount = collect($menu)->count();
        $productCount = collect($menu)->sum(function ($category) {
            return count($category['foods'] ?? []);
        });
        $baseSeatPrice = (float) $seatTotal;
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
                <span class="booking-eyebrow"><i class="fa-solid fa-burger"></i> Bước 3 trong 4</span>
                <h1>Thêm combo ngon cho buổi xem phim.</h1>
                <p>Ghế <strong>{{ $seatSummary }}</strong> đã được giữ cho <strong>{{ $suatChieu->phim->ten_phim }}</strong>. Chọn thêm đồ ăn hoặc tiếp tục thanh toán nếu bạn muốn bỏ qua bước này.</p>
                <div class="booking-seat-mini-stats" aria-label="Tóm tắt chọn đồ ăn">
                    <div><strong>{{ $seatCount }}</strong><span>Ghế đã chọn</span></div>
                    <div><strong>{{ $categoryCount }}</strong><span>Danh mục</span></div>
                    <div><strong>{{ number_format($baseSeatPrice, 0, ',', '.') }}đ</strong><span>Tiền ghế</span></div>
                </div>
            </div>
            <div class="booking-stepper" aria-label="Tiến trình đặt vé">
                <div class="booking-step is-done"><span><i class="fa-solid fa-check"></i></span><strong>Chọn phim</strong></div>
                <div class="booking-step is-done"><span><i class="fa-solid fa-check"></i></span><strong>Chọn ghế</strong></div>
                <div class="booking-step is-active"><span>3</span><strong>Đồ ăn</strong></div>
                <div class="booking-step"><span>4</span><strong>Thanh toán</strong></div>
            </div>
        </section>

        <div id="bookingWrapper">
            <div class="booking-food-layout">
                <section class="booking-food-menu" aria-label="Danh sách đồ ăn">
                    <div class="booking-food-progress">
                        <div>
                            <div>
                                <span class="booking-eyebrow"><i class="fa-regular fa-clock"></i> Giữ ghế khi thanh toán VietQR</span>
                                <h2>Hoàn tất đơn tại quầy</h2>
                                <p>Ghế chỉ bắt đầu được khóa 7 phút sau khi nhân viên tạo giao dịch VietQR ở bước thanh toán.</p>
                            </div>
                        </div>
                        <div class="booking-food-timer" aria-hidden="true"><span>Trạng thái</span><strong>ĐANG GIỮ</strong></div>
                    </div>

                    <div class="booking-food-tools">
                        <label class="booking-food-search" for="foodSearchInput">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input id="foodSearchInput" type="search" placeholder="Tìm bắp, nước, combo...">
                        </label>
                        <div class="booking-food-category-rail" aria-label="Danh mục nhanh">
                            @foreach ($menu as $category)
                                @php $categoryId = \Illuminate\Support\Str::slug($category['category']) ?: 'category-' . $loop->index; @endphp
                                <a href="#food-cat-{{ $categoryId }}">{{ $category['category'] }}</a>
                            @endforeach
                        </div>
                    </div>

                    @foreach ($menu as $category)
                        @php
                            $categoryId = \Illuminate\Support\Str::slug($category['category']) ?: 'category-' . $loop->index;
                            $categoryNameLower = mb_strtolower($category['category']);
                            $categoryIcon = str_contains($categoryNameLower, 'uống') ? 'fa-mug-saucer' : (str_contains($categoryNameLower, 'combo') || str_contains($categoryNameLower, 'quà') ? 'fa-gift' : 'fa-burger');
                            $foodCards = collect($category['foods'] ?? []);
                        @endphp
                        <section id="food-cat-{{ $categoryId }}" class="booking-food-category category-section" data-food-category data-cat="{{ $categoryId }}">
                            <div class="booking-food-category-head">
                                <div><i class="fa-solid {{ $categoryIcon }}"></i></div>
                                <div><span>{{ $foodCards->count() }} lựa chọn</span><h2>{{ $category['category'] }}</h2></div>
                            </div>
                            <div class="booking-food-grid">
                                @foreach ($foodCards as $food)
                                    @php
                                        $imagePath = trim((string) ($food['image'] ?? ''));
                                        if ($imagePath !== '' && !str_starts_with($imagePath, 'foods/')) $imagePath = 'foods/' . $imagePath;
                                        $imageUrl = $imagePath ? asset('storage/' . $imagePath) : asset('assets/images/LOGO copy.png');
                                        $stock = (int) ($food['available'] ?? 0);
                                        $isSoldOut = $stock <= 0;
                                        $searchText = mb_strtolower(($food['name'] ?? '') . ' ' . $category['category'] . ' ' . ($food['type'] ?? ''));
                                        // Chỉ dùng cho HIỂN THỊ: lấy đúng biến thể từ tên món (S, M, Small, Large).
                                        // Không thay đổi data-type/data cart của Staff để giữ nguyên luồng nghiệp vụ.
                                        $foodName = trim((string) ($food['name'] ?? ''));
                                        $variantLabel = $food['type'] ?? 'Món';
                                        if (str_contains($foodName, ' - ')) {
                                            $variantLabel = trim(substr($foodName, strrpos($foodName, ' - ') + 3));
                                        }
                                    @endphp
                                    <article class="booking-food-card food-card {{ $isSoldOut ? 'is-sold-out' : '' }}" data-food-card data-search="{{ $searchText }}" data-search-term="{{ $searchText }}">
                                        <div class="booking-food-image">
                                            <img src="{{ $imageUrl }}" alt="{{ $food['name'] }}" onerror="this.src='{{ asset('assets/images/LOGO copy.png') }}';">
                                            <span>{{ $variantLabel }}</span>
                                        </div>
                                        <div class="booking-food-card-body">
                                            <h3>{{ $food['name'] }}</h3>
                                            <div class="booking-food-meta"><strong>{{ number_format((float) ($food['price'] ?? 0), 0, ',', '.') }}đ</strong><span>{{ $isSoldOut ? 'Hết món' : 'Còn ' . $stock }}</span></div>
                                            @if(!empty($food['description']))<p class="booking-food-desc">{{ $food['description'] }}</p>@endif
                                            <div class="booking-food-actions">
                                                <div class="booking-food-qty">
                                                    <button class="btn-qty-decrease" type="button" aria-label="Giảm số lượng" @disabled($isSoldOut)><i class="fa-solid fa-minus"></i></button>
                                                    <input type="number" min="1" max="{{ max($stock,1) }}" value="1" class="item-qty qty" @disabled($isSoldOut)>
                                                    <button class="btn-qty-increase" type="button" aria-label="Tăng số lượng" @disabled($isSoldOut)><i class="fa-solid fa-plus"></i></button>
                                                </div>
                                                <button type="button" class="btn-add-to-cart add-food" data-cart-key="{{ $food['cart_key'] }}" data-type="{{ $food['type'] }}" data-id="{{ $food['id'] }}" data-food-id="{{ $food['food_id'] ?? $food['id'] }}" data-variant-id="{{ $food['variant_id'] ?? '' }}" data-name="{{ $food['name'] }}" data-price="{{ $food['price'] }}" data-available="{{ $stock }}" @disabled($isSoldOut)>{{ $isSoldOut ? 'Hết món' : 'Thêm vào giỏ' }}</button>
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
                        <div class="booking-cart-head"><div><span>Đơn hàng</span><h2>Giỏ đồ ăn</h2></div><strong id="cartCount">0</strong></div>
                        <div class="booking-cart-seat"><span>Ghế đã chọn</span><strong>{{ $seatSummary }}</strong></div>
                        <div id="cart" class="booking-cart-items"><p>Chưa có món nào</p></div>
                        <button id="btnClearCart" type="button" class="booking-cart-clear"><i class="fa-solid fa-trash-can"></i> Xóa giỏ đồ ăn</button>
                    </div>
                    <div class="booking-cart-total">
                        <div><span>Tiền ghế</span><strong>{{ number_format($baseSeatPrice, 0, ',', '.') }}đ</strong></div>
                        <div><span>Đồ ăn</span><strong id="totalFood">0đ</strong></div>
                        <div class="is-grand"><span>Tổng tiền</span><strong id="total">{{ number_format($baseSeatPrice, 0, ',', '.') }}đ</strong></div>
                    </div>
                    <form id="checkoutForm" action="{{ route('staff.ban-ve.checkout', $suatChieu->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="seats" value="{{ $selectedSeats->implode(',') }}">
                        <input type="hidden" id="foodCartInput" name="food_cart">
                        <input type="hidden" name="clear_cart_key" value="staff_food_cart_v2_{{ auth()->id() }}_{{ $suatChieu->id }}">
                        <button id="btnCheckout" type="submit" class="booking-cart-checkout">Tiếp tục thanh toán <i class="fa-solid fa-arrow-right"></i></button>
                    </form>
                    <a href="{{ url()->previous() }}" class="booking-cart-back"><i class="fa-solid fa-arrow-left"></i> Quay lại chọn ghế</a>
                </aside>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const cartKey = 'staff_food_cart_v2_{{ auth()->id() }}_{{ $suatChieu->id }}';
    let cart = JSON.parse(localStorage.getItem(cartKey) || '{}');
    const baseSeatPrice = @json((int) $baseSeatPrice);

    // 1. RENDER ĐỒ ĂN TRONG GIỎ & CỘNG DỒN TỔNG TIỀN RIÊNG BIỆT
    function renderCart() {
        let html = '';
        let foodTotal = 0;
        let totalQty = 0;

        Object.values(cart).forEach(item => {
            let price = Number(item.price) || 0;
            let qty = Number(item.qty) || 0;

            if (qty <= 0) return;

            foodTotal += price * qty;
            totalQty += qty;

            html += `
                <div class="bg-black border border-white/5 rounded-xl p-3 flex flex-col gap-2 relative group">
                    <button type="button" class="remove-food absolute top-2 right-2 text-gray-500 hover:text-red-500 transition text-[10px] font-bold" data-id="${item.cart_key}">
                        Xóa
                    </button>
                    
                    <div class="flex-1 min-w-0 pr-8">
                        <h4 class="font-bold text-xs text-gray-200 truncate">${item.name}</h4>
                        <span class="text-[10px] text-gray-500">${price.toLocaleString('vi-VN')}đ/món</span>
                    </div>
                    
                    <div class="flex items-center justify-between mt-1">
                        <div class="flex items-center gap-2">
                            <button type="button" class="minus-food bg-zinc-800 hover:bg-zinc-700 w-6 h-6 rounded text-white font-black text-xs flex items-center justify-center" data-id="${item.cart_key}">-</button>
                            <span class="font-bold text-xs w-4 text-center text-white">${qty}</span>
                            <button type="button" class="plus-food bg-zinc-800 hover:bg-zinc-700 w-6 h-6 rounded text-white font-black text-xs flex items-center justify-center" data-id="${item.cart_key}">+</button>
                        </div>
                        <span class="text-yellow-400 font-bold text-xs">${(price * qty).toLocaleString('vi-VN')}đ</span>
                    </div>
                </div>
            `;
        });

        document.getElementById('cart').innerHTML = html || '<p class="text-gray-500 text-xs text-center py-4 italic">Chưa chọn món nào</p>';
        document.getElementById('totalFood').innerText = foodTotal.toLocaleString('vi-VN') + 'đ';
        document.getElementById('total').innerText = (baseSeatPrice + foodTotal).toLocaleString('vi-VN') + 'đ';
        document.getElementById('cartCount').innerText = totalQty;

        localStorage.setItem(cartKey, JSON.stringify(cart));
    }

    // 2. SEARCH & FILTER THEO DANH MỤC NHANH
    const searchInput = document.getElementById('foodSearchInput');
    const categorySections = document.querySelectorAll('.category-section');
    const filterBtns = document.querySelectorAll('.filter-btn');

    function filterProducts() {
        const query = searchInput.value.trim().toLowerCase();
        const activeFilter = document.querySelector('.filter-btn.bg-yellow-400').dataset.filter;

        categorySections.forEach(section => {
            const sectionCat = section.dataset.cat;
            let sectionHasMatch = false;

            section.querySelectorAll('.food-card').forEach(card => {
                const term = card.dataset.searchTerm || '';
                const matchesSearch = term.includes(query);
                const matchesCat = (activeFilter === 'all' || sectionCat === activeFilter);

                if (matchesSearch && matchesCat) {
                    card.classList.remove('hidden');
                    sectionHasMatch = true;
                } else {
                    card.classList.add('hidden');
                }
            });

            section.classList.toggle('hidden', !sectionHasMatch);
        });
    }

    searchInput.addEventListener('input', filterProducts);
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('bg-yellow-400', 'text-black'));
            this.classList.add('bg-yellow-400', 'text-black');
            filterProducts();
        });
    });

    // 4. BẮT CÁC SỰ KIỆN CLICK (THÊM, BỚT, TĂNG, GIẢM, XÓA MÓN)
    document.addEventListener('click', function(e) {
        // Thêm từ ngoài Menu card
        let addBtn = e.target.closest('.add-food');

        if (addBtn) {
            if (addBtn.disabled) {
                return;
            }

            const card = addBtn.closest('.food-card');
            const cartItemKey = addBtn.dataset.cartKey;

            const available =
                Number(addBtn.dataset.available) || 0;

            let quantity =
                Number(card.querySelector('.qty').value) || 1;

            quantity = Math.max(quantity, 1);

            const currentQuantity =
                Number(cart[cartItemKey]?.qty) || 0;

            if (currentQuantity + quantity > available) {
                alert(
                    `Sản phẩm chỉ còn ${available} phần.`
                );

                return;
            }

            if (!cart[cartItemKey]) {
                cart[cartItemKey] = {
                    cart_key: cartItemKey
                    , type: addBtn.dataset.type
                    , id: Number(addBtn.dataset.id)
                    , food_id: Number(addBtn.dataset.foodId),

                    variant_id: addBtn.dataset.variantId ?
                        Number(addBtn.dataset.variantId) : null,

                    name: addBtn.dataset.name
                    , price: Number(addBtn.dataset.price) || 0
                    , available: available
                    , qty: 0
                };
            }

            cart[cartItemKey].qty += quantity;

            renderCart();

            card.querySelector('.qty').value = 1;

            return;
        }

        // Tăng số lượng trong Giỏ
        let plusBtn = e.target.closest('.plus-food');

        if (plusBtn) {
            const id = plusBtn.dataset.id;

            if (!cart[id]) {
                return;
            }

            const available =
                Number(cart[id].available) || 0;

            if (cart[id].qty >= available) {
                alert(
                    `Sản phẩm chỉ còn ${available} phần.`
                );

                return;
            }

            cart[id].qty++;

            renderCart();

            return;
        }

        // Giảm số lượng trong Giỏ
        let minusBtn = e.target.closest('.minus-food');
        if (minusBtn) {
            let id = minusBtn.dataset.id;
            if (cart[id]) {
                cart[id].qty--;
                if (cart[id].qty <= 0) delete cart[id];
                renderCart();
            }
            return;
        }

        // Nút xóa nhanh món ăn khỏi Giỏ hàng
        let removeBtn = e.target.closest('.remove-food');
        if (removeBtn) {
            let id = removeBtn.dataset.id;
            if (cart[id]) {
                delete cart[id];
                renderCart();
            }
        }
    });

    document.getElementById('checkoutForm').addEventListener('submit', function() {
        document.getElementById('foodCartInput').value = JSON.stringify(Object.values(cart));
    });

    renderCart();

    // Các nút +/- trên card và nút xóa giỏ chỉ là điều khiển giao diện,
    // không thay đổi cấu trúc cart hay luồng thanh toán Staff.
    document.addEventListener('click', function(e) {
        const decrease = e.target.closest('.btn-qty-decrease');
        const increase = e.target.closest('.btn-qty-increase');
        const clearBtn = e.target.closest('#btnClearCart');

        if (decrease || increase) {
            const card = e.target.closest('.food-card');
            if (!card) return;
            const input = card.querySelector('.qty');
            if (!input || input.disabled) return;
            const min = Number(input.min) || 1;
            const max = Number(input.max) || Number.MAX_SAFE_INTEGER;
            let value = Number(input.value) || min;
            value += increase ? 1 : -1;
            input.value = Math.min(max, Math.max(min, value));
            return;
        }

        if (clearBtn) {
            cart = {};
            renderCart();
        }
    });

</script>
@endpush