@extends('layouts.admin')

@section('title','Chọn đồ ăn tại quầy - '.$suatChieu->phim->ten_phim)

@section('content')
@php
$seatSummary = $selectedSeats->implode(', ');
$seatCount = $selectedSeats->count();

// Giả định giá vé trung bình (ví dụ: 100.000đ/ghế) để tính tiền vé riêng biệt
// Tổng tiền ghế do backend tính theo đúng nghiệp vụ:
// giá vé suất chiếu + phụ thu loại ghế;
// ghế đôi = giá vé gốc x 2 + phụ thu.
$baseSeatPrice = (float) $seatTotal;

@endphp

<div class="min-h-screen bg-[#0a0a0a] text-white pt-28 pb-20 px-4 md:px-6">
    <div class="max-w-7xl mx-auto space-y-6">

        {{-- 1. THANH TIẾN ĐỘ CÁC BƯỚC (GIỐNG GIAO DIỆN MẪU) --}}
        <div class="bg-zinc-900 border border-white/10 rounded-3xl p-6 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <div class="space-y-2">
                <span class="text-xs font-bold text-yellow-400 uppercase tracking-widest bg-yellow-400/10 px-3 py-1 rounded-md">
                    Bước 3 trong 4
                </span>
                <h1 class="text-3xl font-black uppercase">Thêm combo ngon cho buổi xem phim</h1>
                <p class="text-gray-400 text-sm">
                    Ghế <strong class="text-white">{{ $seatSummary }}</strong> đã được giữ cho phim <strong class="text-white">{{ $suatChieu->phim->ten_phim }}</strong>.
                </p>
            </div>

            {{-- THANH TIẾN TRÌNH --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 w-full lg:w-auto text-xs font-bold">
                <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-xl flex items-center gap-2">
                    <span class="w-4 h-4 rounded-full bg-emerald-400 text-black flex items-center justify-center font-black">✓</span> Chọn phim
                </div>
                <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-xl flex items-center gap-2">
                    <span class="w-4 h-4 rounded-full bg-emerald-400 text-black flex items-center justify-center font-black">✓</span> Chọn ghế
                </div>
                <div class="bg-red-500/20 border border-red-500/30 text-red-400 px-4 py-3 rounded-xl flex items-center gap-2">
                    <span class="w-4 h-4 rounded-full bg-red-500 text-white flex items-center justify-center font-black">3</span> Đồ ăn
                </div>
                <div class="bg-zinc-800 border border-white/5 text-gray-500 px-4 py-3 rounded-xl flex items-center gap-2">
                    <span class="w-4 h-4 rounded-full bg-zinc-700 text-gray-500 flex items-center justify-center font-black">4</span> Thanh toán
                </div>
            </div>
        </div>

        {{-- 2. THANH THỜI GIAN GIỮ VÉ --}}
        <div class="bg-zinc-900 border border-white/10 rounded-2xl p-4 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-sm">
                <span class="text-yellow-400 font-bold uppercase tracking-wider block">Thời gian giữ ghế</span>
                <p class="text-xs text-gray-400">Hoàn tất đơn hàng trong thời gian quy định trước khi ghế tự động giải phóng.</p>
            </div>
            <div class="flex items-center gap-3 w-full md:w-auto flex-1 md:justify-end">
                <div class="h-2 bg-black rounded-full overflow-hidden flex-1 md:w-64 border border-white/5">
                    <div id="countdownBar" class="bg-gradient-to-r from-yellow-500 to-red-500 h-full w-full transition-all duration-1000"></div>
                </div>
                <span id="countdownText" class="text-xl font-black text-red-500 font-mono">07:00</span>
            </div>
        </div>

        {{-- 3. KHU VỰC CHI TIẾT ĐỒ ĂN & GIỎ HÀNG --}}
        <div class="grid lg:grid-cols-[1fr_350px] gap-6 items-start">

            {{-- MENU ĐỒ ĂN (BÊN TRÁI) --}}
            <section class="space-y-6">

                {{-- THANH TÌM KIẾM & DANH MỤC NHANH --}}
                <div class="bg-zinc-900 border border-white/10 p-4 rounded-2xl flex flex-col md:flex-row gap-3 sticky top-24 z-10 shadow-xl">
                    <input id="foodSearchInput" type="text" placeholder="Tìm bắp nhanh tại đây..." class="flex-1 bg-black border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-yellow-400 focus:outline-none transition">

                    <div class="flex gap-1 overflow-x-auto pb-1 md:pb-0 scrollbar-none">
                        <button type="button" data-filter="all" class="filter-btn bg-yellow-400 text-black px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap">Tất cả</button>
                        @foreach($menu as $category)
                        <button type="button" data-filter="{{ \Illuminate\Support\Str::slug($category['category']) }}" class="filter-btn bg-black hover:bg-zinc-800 border border-white/10 text-gray-400 hover:text-white px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition">
                            {{ $category['category'] }}
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- DANH SÁCH MÓN ĂN THEO PHÂN LOẠI --}}
                <div class="space-y-8">
                    @foreach($menu as $category)
                    @php $catSlug = \Illuminate\Support\Str::slug($category['category']); @endphp

                    <div class="category-section" data-cat="{{ $catSlug }}">
                        <h2 class="text-md font-black text-yellow-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <span class="w-2 h-4 bg-yellow-400 rounded-sm"></span>
                            {{ $category['category'] }}
                        </h2>

                        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                            @foreach($category['foods'] as $food)
                            <div class="food-card bg-zinc-900 border border-white/10 rounded-2xl p-4 flex flex-col justify-between hover:border-yellow-400 transition" data-search-term="{{ mb_strtolower($food['name']) }}">
                                <div class="h-28 bg-black rounded-xl flex items-center justify-center overflow-hidden mb-3 relative border border-white/5">
                                    @if (!empty($food['image']))
                                    <img src="{{ asset('storage/foods/' . $food['image']) }}" class="h-full object-contain hover:scale-110 transition duration-300" alt="{{ $food['name'] }}">
                                    @else
                                    <span class="text-xs text-gray-600">
                                        No Image
                                    </span>
                                    @endif

                                    @if (($food['available'] ?? 0) <= 0) <div class="absolute inset-0 bg-black/75 flex items-center justify-center">
                                        <span class="rounded-lg bg-red-500 px-3 py-1 text-xs font-black text-white">
                                            HẾT HÀNG
                                        </span>
                                </div>
                                @endif
                            </div>

                            <h3 class="font-bold text-xs text-center line-clamp-2 min-h-[36px] text-gray-200">
                                {{ $food['name'] }}
                            </h3>

                            @if (!empty($food['description']))
                            <p class="mt-1 line-clamp-2 text-center text-[10px] text-gray-500">
                                {{ $food['description'] }}
                            </p>
                            @endif

                            <p class="mt-2 text-center text-yellow-400 font-black text-sm">
                                {{ number_format((float) $food['price'], 0, ',', '.') }}đ
                            </p>

                            <p class="mb-3 mt-1 text-center text-[10px] text-gray-500">
                                Còn {{ (int) ($food['available'] ?? 0) }} sản phẩm
                            </p>

                            <div class="flex gap-2">
                                <input type="number" min="1" max="{{ max((int) ($food['available'] ?? 0), 1) }}" value="1" class="qty w-12 bg-black border border-white/10 rounded-lg text-center text-xs font-bold focus:border-yellow-400 focus:outline-none" @disabled(($food['available'] ?? 0) <=0)>

                                <button type="button" class="add-food flex-1 py-2 rounded-lg font-black text-xs transition
                {{ ($food['available'] ?? 0) > 0
                    ? 'bg-yellow-400 text-black hover:bg-yellow-300'
                    : 'bg-zinc-700 text-gray-500 cursor-not-allowed'
                }}" data-cart-key="{{ $food['cart_key'] }}" data-type="{{ $food['type'] }}" data-id="{{ $food['id'] }}" data-food-id="{{ $food['food_id'] }}" data-variant-id="{{ $food['variant_id'] ?? '' }}" data-name="{{ $food['name'] }}" data-price="{{ $food['price'] }}" data-available="{{ $food['available'] ?? 0 }}" @disabled(($food['available'] ?? 0) <=0)>
                                    {{ ($food['available'] ?? 0) > 0 ? 'THÊM' : 'HẾT HÀNG' }}
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
        </div>
        </section>

        {{-- GIỎ HÀNG CỐ ĐỊNH (BÊN PHẢI) --}}
        <aside class="lg:sticky lg:top-24">
            <div class="bg-zinc-900 border border-white/10 rounded-3xl p-5 space-y-5 shadow-2xl">

                <div class="flex justify-between items-center pb-3 border-b border-white/10">
                    <h2 class="text-lg font-black text-white flex items-center gap-2">🛒 Đơn hàng</h2>
                    <span id="cartCount" class="bg-red-500 text-white px-2.5 py-0.5 rounded-full font-black text-xs">0</span>
                </div>

                {{-- Thông tin ghế phụ trợ từ ảnh --}}
                <div class="bg-black/40 border border-white/5 rounded-xl p-3 text-xs flex justify-between items-center">
                    <span class="text-gray-400">Ghế đã chọn:</span>
                    <strong class="text-yellow-400 font-bold">{{ $seatSummary }}</strong>
                </div>

                {{-- Danh sách các món ăn đã chọn --}}
                <div id="cart" class="space-y-3 max-h-[280px] overflow-y-auto pr-1 scrollbar-thin scrollbar-thumb-zinc-800">
                    <p class="text-gray-500 text-xs text-center py-4 italic">Chưa chọn món nào</p>
                </div>

                {{-- BẢNG PHÂN BỔ CHI PHÍ TỪNG MỤC --}}
                <div class="border-t border-white/10 pt-4 space-y-2.5 text-xs">
                    <div class="flex justify-between text-gray-400">
                        <span>Tiền ghế</span>
                        <b class="text-white">{{ number_format($baseSeatPrice) }}đ</b>
                    </div>
                    <div class="flex justify-between text-gray-400">
                        <span>Đồ ăn đính kèm</span>
                        <b id="totalFood" class="text-white">0đ</b>
                    </div>
                    <div class="border-t border-white/5 pt-3 flex justify-between items-center text-sm">
                        <span class="font-bold text-white">Tổng cộng</span>
                        <b id="total" class="text-yellow-400 text-lg font-black">0đ</b>
                    </div>
                </div>

                {{-- FORM THANH TOÁN --}}
                <form id="checkoutForm" action="{{ route('staff.ban-ve.checkout', $suatChieu->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="seats" value="{{ $selectedSeats->implode(',') }}">
                    <input type="hidden" id="foodCartInput" name="food_cart">
                    <input type="hidden" name="clear_cart_key" value="staff_food_cart_v2_{{ auth()->id() }}_{{ $suatChieu->id }}">

                    <button class="w-full bg-red-500 hover:bg-red-600 text-white py-3.5 rounded-xl font-black text-xs uppercase tracking-wider transition">
                        Tiếp tục thanh toán →
                    </button>
                </form>
            </div>
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

    // 3. ĐẾM NGƯỢC THỜI GIAN GIỮ VÉ (7 PHÚT)
    const duration = 7 * 60;
    let timeRemaining = duration;
    const countText = document.getElementById('countdownText');
    const countBar = document.getElementById('countdownBar');

    const timer = setInterval(() => {
        timeRemaining--;
        if (timeRemaining <= 0) {
            clearInterval(timer);
            alert("Hết thời gian giữ vé!");
            window.location.reload();
            return;
        }
        const mins = String(Math.floor(timeRemaining / 60)).padStart(2, '0');
        const secs = String(timeRemaining % 60).padStart(2, '0');
        countText.innerText = `${mins}:${secs}`;
        countBar.style.width = `${(timeRemaining / duration) * 100}%`;
    }, 1000);

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

</script>
@endpush
