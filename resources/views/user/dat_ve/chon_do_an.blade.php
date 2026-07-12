@extends('layouts.user')

@section('title', 'Chọn đồ ăn - ' . $suatChieu->phim->ten_phim)

@section('content')

    <div class="min-h-screen bg-black text-white pt-32 pb-24 px-10">
        <div id="bookingWrapper" class="relative">
            <div class="max-w-7xl mx-auto grid grid-cols-[1fr_320px] gap-10 items-start">
                {{-- LEFT --}}
                <section class="space-y-10">
                    {{-- HEADER --}}
                    <div>
                        <h1 class="text-4xl md:text-5xl font-black uppercase tracking-wide">
                            Chọn <span class="text-yellow-400">Đồ Ăn</span>
                        </h1>
                        <p class="text-gray-500 mt-2 text-sm">
                            {{ $suatChieu->phim->ten_phim }} · {{ $suatChieu->rapChieuPhim->ten_rap }}
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-white/10 bg-zinc-900 p-4 shadow-[0_0_0_1px_rgba(255,255,255,0.03)]">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <p class="text-[10px] uppercase tracking-[0.35em] text-gray-500">Tiến trình đặt vé</p>
                                <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-gray-400">
                                    <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-gray-300">1.
                                        Chọn
                                        ghế</span>
                                    <span
                                        class="rounded-full bg-yellow-400/15 px-3 py-1 text-xs font-semibold text-yellow-300">2.
                                        Chọn đồ ăn</span>
                                    <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-gray-400">3.
                                        Thanh toán</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/20 px-4 py-2">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-black/40 text-lg">
                                    ⏰</div>
                                <div>
                                    <p class="text-[10px] uppercase tracking-[0.3em] text-gray-500">Thời gian còn lại</p>
                                    <div id="countdown" class="text-2xl font-black tracking-[0.2em] text-red-400">07:00
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 h-2 overflow-hidden rounded-full bg-white/10">
                            <div id="countdownBar"
                                class="h-full rounded-full bg-gradient-to-r from-yellow-400 to-amber-500 transition-all duration-1000"
                                style="width: 100%"></div>
                        </div>
                    </div>

                    @foreach ($menu as $category)
                        <div class="space-y-5">

                            {{-- CATEGORY TITLE --}}
                            <div class="flex items-center gap-3">
                                <div class="text-2xl">
                                    {{ $category['category'] === 'Đồ ăn' ? '🍿' : ($category['category'] === 'Đồ uống' ? '🥤' : '🎁') }}
                                </div>
                                <h2 class="text-xl font-black text-yellow-400 uppercase tracking-[0.25em]">
                                    {{ $category['category'] }}
                                </h2>
                            </div>

                            {{-- GRID 5 CỘT --}}
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">

                                @foreach ($category['foods'] as $food)
                                    {{-- ================= COMBO ================= --}}
                                    @if (!empty($food['is_combo']))
                                        <div
                                            class="bg-zinc-900 border border-white/10 rounded-2xl p-3 hover:-translate-y-1 hover:border-yellow-400/40 transition">

                                            <div
                                                class="bg-black rounded-xl h-28 flex items-center justify-center p-2 mb-2 overflow-hidden">
                                                <img src="{{ asset('storage/' . (str_starts_with($food['image'], 'foods/') ? $food['image'] : 'foods/' . $food['image'])) }}"
                                                    class="h-full object-contain">
                                            </div>

                                            <h3
                                                class="mt-2 text-center font-semibold text-sm min-h-[40px] flex items-center justify-center leading-5">
                                                {{ $food['name'] }}
                                            </h3>

                                            <p class="mt-2 text-center text-yellow-400 font-black text-lg">
                                                {{ number_format($food['price']) }}đ
                                            </p>


                                            <div class="mt-3 space-y-2">
                                                <div
                                                    class="flex items-center justify-between gap-2 rounded-2xl border border-white/10
                                           bg-black/30 px-2 py-1 shadow-inner backdrop-blur-md">

                                                    <button
                                                        class="btn-qty-decrease flex h-9 w-9 items-center justify-center rounded-xl
                               bg-white/5 text-white text-lg font-bold
                               transition hover:bg-white/15 active:scale-95"
                                                        type="button">
                                                        −
                                                    </button>

                                                    {{-- CHUẨN NGHIỆP VỤ: Đổi min=1 và giá trị mặc định ban đầu là 1 --}}
                                                    <input type="number" min="1" value="1"
                                                        class="item-qty w-12 bg-transparent text-center text-sm font-black text-white
                               border-0 outline-none ring-0 shadow-none
                               focus:outline-none focus:ring-0
                               appearance-none
                               [&::-webkit-outer-spin-button]:appearance-none
                               [&::-webkit-inner-spin-button]:appearance-none
                               [-moz-appearance:textfield]" />

                                                    <button
                                                        class="btn-qty-increase flex h-9 w-9 items-center justify-center rounded-xl
                               bg-yellow-400 text-black text-lg font-black
                               transition hover:bg-yellow-300 active:scale-95 shadow-md"
                                                        type="button">
                                                        +
                                                    </button>

                                                </div>

                                                <button
                                                    class="btn-add-to-cart w-full rounded-xl bg-yellow-400 px-3 py-2 text-xs font-black uppercase tracking-[0.2em] text-black transition hover:bg-yellow-300"
                                                    data-item-key="combo-{{ $food['id'] }}"
                                                    data-item-name="{{ $food['name'] }}"
                                                    data-item-price="{{ $food['price'] ?? 0 }}"
                                                    data-item-image="{{ $food['image'] }}" data-item-type="combo"
                                                    data-item-stock="{{ $food['available'] ?? 999 }}" type="button">
                                                    Thêm
                                                </button>
                                            </div>

                                        </div>

                                        {{-- ================= FOOD + VARIANT ================= --}}
                                    @else
                                        @php
                                            $variants = $food['variants'] ?? [];

                                            if (count($variants) == 0) {
                                                $variants = [
                                                    [
                                                        'id' => $food['id'],
                                                        'value' => '',
                                                        'price' => $food['price'] ?? 0,
                                                        'stock' => $food['stock'] ?? 999,
                                                    ],
                                                ];
                                            }
                                        @endphp

                                        @foreach ($variants as $variant)
                                            <div
                                                class="bg-zinc-900 border border-white/10 rounded-2xl p-3 hover:-translate-y-1 hover:border-yellow-400/40 transition">

                                                <div
                                                    class="bg-black rounded-xl h-28 flex items-center justify-center p-2 mb-2 overflow-hidden">
                                                    <img src="{{ asset('storage/' . (str_starts_with($food['image'], 'foods/') ? $food['image'] : 'foods/' . $food['image'])) }}"
                                                    class="h-full object-contain">
                                                </div>

                                                <div class="text-center mt-2">
                                                    <h3
                                                        class="font-semibold text-sm min-h-[40px] flex items-center justify-center leading-5">
                                                        {{ $food['name'] }}
                                                        @if (!empty($variant['value']))
                                                            - {{ $variant['value'] }}
                                                        @endif
                                                    </h3>

                                                    <p class="mt-2 text-yellow-400 font-black text-lg">
                                                        {{ number_format($variant['price']) }}đ
                                                    </p>

                                                    <p class="mt-1 text-[10px] text-gray-500">
                                                        Còn: {{ $variant['stock'] }}
                                                    </p>
                                                </div>
                                                <div class="mt-3 space-y-2">
                                                    <div
                                                        class="flex items-center justify-between gap-2 rounded-2xl border border-white/10
                               bg-black/30 px-2 py-1 shadow-inner backdrop-blur-md">

                                                        <button
                                                            class="btn-qty-decrease flex h-9 w-9 items-center justify-center rounded-xl
                               bg-white/5 text-white text-lg font-bold
                               transition hover:bg-white/15 active:scale-95"
                                                            type="button">
                                                            −
                                                        </button>

                                                        {{-- CHUẨN NGHIỆP VỤ: Đổi min=1 và giá trị mặc định ban đầu là 1 --}}
                                                        <input type="number" min="1" value="1"
                                                            class="item-qty w-12 bg-transparent text-center text-sm font-black text-white
                               border-0 outline-none ring-0 shadow-none
                               focus:outline-none focus:ring-0
                               appearance-none
                               [&::-webkit-outer-spin-button]:appearance-none
                               [&::-webkit-inner-spin-button]:appearance-none
                               [-moz-appearance:textfield]" />

                                                        <button
                                                            class="btn-qty-increase flex h-9 w-9 items-center justify-center rounded-xl
                               bg-yellow-400 text-black text-lg font-black
                               transition hover:bg-yellow-300 active:scale-95 shadow-md"
                                                            type="button">
                                                            +
                                                        </button>

                                                    </div>

                                                    <button
                                                        class="btn-add-to-cart w-full rounded-xl bg-yellow-400 px-3 py-2 text-xs font-black uppercase tracking-[0.2em] text-black transition hover:bg-yellow-300"
                                                        data-item-key="variant-{{ $variant['id'] }}"
                                                        data-item-name="{{ $food['name'] }}@if (!empty($variant['value'])) - {{ $variant['value'] }} @endif"
                                                        data-item-price="{{ $variant['price'] }}"
                                                        data-item-image="{{ $food['image'] }}"
                                                        data-item-stock="{{ $variant['stock'] }}" data-item-type="variant"
                                                        type="button">
                                                        Thêm
                                                    </button>
                                                </div>

                                            </div>
                                        @endforeach
                                    @endif
                                @endforeach

                            </div>

                        </div>
                    @endforeach

                </section>

                {{-- RIGHT SIDEBAR (FIXED SCROLL) --}}
                <aside id="bookingSidebar" class="fixed top-48 right-10 w-[320px] space-y-4">

                    <div class="rounded-3xl bg-zinc-900 border border-white/10 overflow-hidden shadow-xl">

                        <div class="border-b border-white/10 px-5 py-4">
                            <p class="text-xs uppercase tracking-widest text-gray-500">
                                Đơn hàng
                            </p>
                        </div>

                        <div id="cartItems"
                            class="max-h-[350px] overflow-y-auto overflow-x-hidden px-5 py-4 space-y-3
                                scrollbar-thin scrollbar-thumb-yellow-400 scrollbar-track-transparent">

                            <p class="text-gray-500">
                                Chưa có món nào
                            </p>

                        </div>

                    </div>

                    <div class="bg-zinc-900 border border-white/10 rounded-3xl p-5 text-center">
                        <p class="text-xs text-gray-500">Tổng tiền</p>
                        <div id="subtotalPrice" class="text-3xl font-black text-yellow-400">
                            {{ number_format($seatTotalPrice) }}đ
                        </div>
                    </div>

                    <a id="btnCheckout" href="{{ route('dat_ve.checkout', ['suat_chieu_id' => $suatChieu->id]) }}"
                        class="block w-full rounded-2xl bg-yellow-400 py-3 text-center font-black uppercase tracking-[0.2em] text-black hover:bg-yellow-300">
                        Checkout
                    </a>

                    <a href="{{ route('dat_ve.chon_ghe', ['movie' => $suatChieu->id]) }}?ghe={{ request()->query('ghe') }}"
                        class="inline-flex w-full items-center justify-center rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-gray-300 hover:bg-white/10">
                        ← Quay lại chọn ghế
                    </a>

                </aside>

            </div>
        </div>

    </div>

@endsection

<script>
    const storageKey = 'booking_deadline_{{ $suatChieu->id }}';
    const baseSeatPrice = parseInt("{{ $seatTotalPrice }}") || 0;

    let cart = {};
    let countdownInterval = null;
    let countdownExpired = false;

    /* ================= FORMAT ================= */
    function formatCurrency(value) {
        return Number(value || 0).toLocaleString('vi-VN') + 'đ';
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    /* ================= CART ================= */
    function renderCart() {
        const cartItems = document.getElementById('cartItems');
        const subtotalPrice = document.getElementById('subtotalPrice');

        if (!cartItems || !subtotalPrice) return;

        const items = Object.values(cart).filter(i => i.qty > 0);

        let html = '';
        let foodTotal = 0;

        items.forEach(item => {
            foodTotal += item.qty * item.price;

            html += `
            <div class="border-b border-white/10 py-3">
                <p class="text-sm font-bold text-white">${escapeHtml(item.name)}</p>

                <div class="mt-2 flex items-center justify-between">
                    <span class="text-yellow-400 font-semibold">
                        ${formatCurrency(item.price)}
                    </span>

                    <div class="flex items-center gap-2">
                        <button class="btn-cart-decrease w-7 h-7 rounded-full bg-white/10"
                            data-item-key="${item.key}">-</button>

                        <span class="w-6 text-center">${item.qty}</span>

                        <button class="btn-cart-increase w-7 h-7 rounded-full bg-yellow-400 text-black"
                            data-item-key="${item.key}">+</button>
                    </div>
                </div>
            </div>`;
        });

        cartItems.innerHTML = html || '<p class="text-gray-500">Chưa có món nào</p>';

        let grandTotal = baseSeatPrice + foodTotal;
        subtotalPrice.innerText = formatCurrency(grandTotal);

        localStorage.setItem('food_cart', JSON.stringify(cart));

        updateCheckoutButton();
    }

    /* ================= ADD TO CART ================= */
    function addToCart(item) {
        if (countdownExpired) return;

        const stock = Number(item.stock);

        if (!cart[item.key]) {
            cart[item.key] = {
                key: item.key,
                name: item.name,
                price: Number(item.price),
                image: item.image,
                stock: stock,
                qty: 0
            };
        }

        const currentQty = cart[item.key].qty;
        const addQty = Number(item.qty);

        if (currentQty + addQty > stock) {
            alert('Số lượng vượt tồn kho.');
            return;
        }

        cart[item.key].qty += addQty;

        renderCart();
    }

    /* ================= CHECKOUT BUTTON ================= */
    function updateCheckoutButton() {
        const btn = document.getElementById('btnCheckout');
        if (!btn) return;

        if (countdownExpired) {
            btn.classList.add('opacity-50', 'pointer-events-none');
            btn.textContent = 'Hết thời gian';
            return;
        }

        btn.classList.remove('opacity-50', 'pointer-events-none');
        btn.textContent = 'Checkout';
    }

    /* ================= COUNTDOWN ================= */
    function startCountdown() {
        const countdownEl = document.getElementById('countdown');
        const countdownBar = document.getElementById('countdownBar');

        if (!countdownEl || !countdownBar) return;

        let deadline = Number(localStorage.getItem(storageKey));

        if (!deadline) {
            deadline = Date.now() + 7 * 60 * 1000;
            localStorage.setItem(storageKey, deadline);
        }

        const update = () => {
            const remaining = deadline - Date.now();

            if (remaining <= 0) {
                clearInterval(countdownInterval);

                countdownExpired = true;
                localStorage.removeItem(storageKey);

                countdownEl.innerText = '00:00';
                countdownBar.style.width = '0%';

                document.querySelectorAll('.btn-add-to-cart').forEach(btn => {
                    btn.disabled = true;
                    btn.classList.add('opacity-50', 'cursor-not-allowed');
                });

                updateCheckoutButton();
                return;
            }

            const minutes = String(Math.floor(remaining / 60000)).padStart(2, '0');
            const seconds = String(Math.floor((remaining % 60000) / 1000)).padStart(2, '0');

            const percent = (remaining / (7 * 60 * 1000)) * 100;

            countdownEl.innerText = `${minutes}:${seconds}`;
            countdownBar.style.width = `${percent}%`;
        };

        update();
        countdownInterval = setInterval(update, 1000);
    }

    /* ================= EVENTS ================= */
    document.addEventListener('input', function(event) {
        const input = event.target.closest('.item-qty');
        if (!input) return;

        const card = input.closest('.bg-zinc-900');
        const addBtn = card.querySelector('.btn-add-to-cart');

        const stock = Number(addBtn.dataset.itemStock);
        let value = Number(input.value);

        if (value > stock) input.value = stock;
        // CHUẨN NGHIỆP VỤ: Không để số lượng nhỏ hơn 1 khi nhập trực tiếp
        if (value < 1 || isNaN(value)) input.value = 1;
    });

    document.addEventListener('click', function(event) {

        /* ================= ADD TO CART ================= */
        const addButton = event.target.closest('.btn-add-to-cart');
        if (addButton) {
            event.preventDefault();

            const card = addButton.closest('.bg-zinc-900');
            const qtyEl = card.querySelector('.item-qty');

            const qty = qtyEl ? Number(qtyEl.value || 1) : 1;

            if (qty === 0) {
                return;
            }

            addToCart({
                key: addButton.dataset.itemKey,
                name: addButton.dataset.itemName,
                price: addButton.dataset.itemPrice,
                image: addButton.dataset.itemImage,
                stock: addButton.dataset.itemStock,
                qty: qty
            });

            // CHUẨN NGHIỆP VỤ: Sau khi thêm thành công, reset ô nhập liệu về số 1 thay vì số 0
            if (qtyEl) qtyEl.value = 1;
            return;
        }

        /* ================= PLUS / MINUS UI ================= */
        const minus = event.target.closest('.btn-qty-decrease');
        if (minus) {
            const card = minus.closest('.bg-zinc-900');
            const qtyEl = card.querySelector('.item-qty');

            let qty = Number(qtyEl.value);
            // CHUẨN NGHIỆP VỤ: Giới hạn tối thiểu là 1, không cho phép giảm xuống 0
            qtyEl.value = Math.max(1, qty - 1);
            return;
        }

        const plus = event.target.closest('.btn-qty-increase');
        if (plus) {
            const card = plus.closest('.bg-zinc-900');
            const qtyEl = card.querySelector('.item-qty');
            const addBtn = card.querySelector('.btn-add-to-cart');

            const stock = Number(addBtn.dataset.itemStock);
            let qty = Number(qtyEl.value);

            if (qty >= stock) {
                alert("Đã đạt số lượng tối đa.");
                return;
            }

            qtyEl.value = qty + 1;
            return;
        }

        /* ================= CART +/- ================= */
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
                    alert("Đã đạt số lượng tối đa.");
                    return;
                }

                cart[key].qty++;
                renderCart();
            }
            return;
        }
    });

    /* ================= CHECKOUT ================= */
    document.addEventListener('DOMContentLoaded', function() {
        const checkoutBtn = document.getElementById('btnCheckout');

        if (checkoutBtn) {
            checkoutBtn.addEventListener('click', function(e) {
                e.preventDefault();

                const items = Object.values(cart).filter(i => i.qty > 0);
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

                window.location.href = url;
            });
        }
    });

    /* ================= INIT ================= */
    document.addEventListener('DOMContentLoaded', function() {
        cart = JSON.parse(localStorage.getItem('food_cart') || '{}');

        renderCart();
        startCountdown();
    });

    document.addEventListener('DOMContentLoaded', function() {

        const sidebar = document.getElementById('bookingSidebar');
        const footer = document.querySelector('.cine-footer');

        function updateSidebar() {
            if (!sidebar) return;
            if (!footer) return;
            const footerTop = footer.getBoundingClientRect().top + window.scrollY;
            const sidebarHeight = sidebar.offsetHeight;

            const stopPoint = footerTop - sidebarHeight - 30;

            if (window.scrollY + 128 >= stopPoint) {

                sidebar.style.position = 'absolute';
                sidebar.style.top = stopPoint + 'px';
                sidebar.style.right = '40px';

            } else {

                sidebar.style.position = 'fixed';
                sidebar.style.top = '128px';
                sidebar.style.right = '40px';

            }
        }

        window.addEventListener('scroll', updateSidebar);
        window.addEventListener('resize', updateSidebar);

        updateSidebar();

    });
</script>
