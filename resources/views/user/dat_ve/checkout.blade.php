@extends('layouts.user')

@section('title', 'Checkout - ' . $suatChieu->phim->ten_phim)

@section('content')

    <div class="booking-checkout-page min-h-screen bg-[#080808] pt-24 pb-12 text-white" lang="vi" spellcheck="false">

        <div class="booking-checkout-container mx-auto max-w-7xl px-4 lg:px-6">

            {{-- HEADER --}}
            <div class="mb-8">
                <p class="text-xs uppercase tracking-[0.45em] text-yellow-400">Checkout</p>
                <h1 class="mt-2 text-4xl font-black">THANH TOÁN</h1>
                <p class="mt-2 text-gray-400">Kiểm tra thông tin trước khi hoàn tất đơn đặt vé.</p>
            </div>

            {{-- BOX --}}
            <div class="booking-checkout-shell overflow-hidden rounded-3xl border border-white/10 bg-[#141414] shadow-2xl">
                {{-- COUNTDOWN --}}
                <div class="mb-8 flex items-center justify-between rounded-2xl border border-white/10 bg-zinc-900 p-4">

                    <div>
                        <p class="text-[10px] uppercase tracking-[0.35em] text-gray-500">
                            Thời gian hoàn tất thanh toán
                        </p>

                        <p class="mt-1 text-sm text-gray-400">
                            Đơn đặt vé sẽ tự động hủy khi hết thời gian.
                        </p>
                    </div>

                    <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/20 px-4 py-2">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-black/40 text-lg">⏰</div>
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.3em] text-gray-500">Thời gian còn lại</p>
                            <div id="countdown" class="text-2xl font-black tracking-[0.2em] text-red-400">07:00</div>
                        </div>
                    </div>

                </div>

                <div class="booking-checkout-layout grid lg:grid-cols-[380px_1fr]">

                    {{-- ================= LEFT: THÔNG TIN KHÁCH HÀNG & PHIM ================= --}}
                    <div class="booking-checkout-info border-r border-white/10 p-8">

                        {{-- USER --}}
                        <div class="pb-6">

                            <div class="mb-4 flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-yellow-400 text-black text-xl">👤</div>
                                <div>
                                    <h2 class="text-xl font-black text-yellow-400">Thông tin người đặt</h2>
                                    <p class="text-sm text-gray-400">Tài khoản</p>
                                </div>
                            </div>

                            <input readonly value="{{ auth()->user()->ho_ten ?? (auth()->user()->ten ?? '') }}"
                                class="w-full mb-3 rounded-xl border border-white/10 bg-[#1d1d1d] px-4 py-3">

                            <input readonly value="{{ auth()->user()->email }}"
                                class="w-full rounded-xl border border-white/10 bg-[#1d1d1d] px-4 py-3">
                        </div>

                        {{-- FILM --}}
                        <div class="border-t border-white/10 pt-6 mt-6">

                            <h2 class="text-lg font-black text-yellow-400 mb-3">Thông tin Phim</h2>

                            <div class="rounded-xl overflow-hidden border border-white/10">
                                <img src="{{ asset('storage/movies/' . $suatChieu->phim->poster) }}"
                                    alt="{{ $suatChieu->phim->ten_phim }}" class="w-full rounded-lg object-cover">
                            </div>

                            <div class="mt-4 text-sm space-y-2 text-gray-300">
                                <div><strong>Rạp chiếu:</strong> {{ $suatChieu->rapChieuPhim->ten_rap }}</div>
                                <div><strong>Tên Phim:</strong> {{ $suatChieu->phim->ten_phim }}</div>
                                <div><strong>Suất chiếu:</strong> {{ $suatChieu->thoi_gian_chieu->format('d/m/Y H:i') }}</div>
                            </div>

                        </div>

                    </div>

                    {{-- ================= RIGHT: CHI TIẾT ĐƠN HÀNG & PHƯƠNG THỨC THANH TOÁN ================= --}}
                    <div class="booking-checkout-order p-8">

                        {{-- ITEMS --}}
                        <div class="border-b border-white/10 pb-6">

                            <h2 class="text-2xl font-black text-yellow-400 mb-4">Danh sách đặt</h2>

                            <div class="py-6 border-b border-white/10">

                                <div class="flex justify-between">
                                    <span>Số ghế</span>
                                    <span class="text-yellow-400 font-semibold">
                                        {{ $selectedSeats->implode(', ') }}
                                    </span>
                                </div>

                                @forelse($foodItems as $item)
                                    @php
                                        $foodImagePath = trim((string) ($item['image'] ?? ''));
                                        if ($foodImagePath !== '' && ! str_starts_with($foodImagePath, 'foods/')) {
                                            $foodImagePath = 'foods/' . $foodImagePath;
                                        }
                                    @endphp
                                    <div class="flex justify-between items-center mt-3">
                                        <div class="flex items-center gap-3">
                                            @if ($foodImagePath !== '')
                                                <img src="{{ asset('storage/' . $foodImagePath) }}"
                                                    class="w-16 h-16 object-contain rounded">
                                            @endif
                                            <span>{{ $item['name'] }}</span>
                                        </div>
                                        <span>x{{ $item['qty'] }}</span>
                                    </div>
                                @empty
                                    <div class="flex justify-between mt-3">
                                        <span>Đồ ăn</span>
                                        <span>Không có</span>
                                    </div>
                                @endforelse

                            </div>

                        </div>

                        {{-- TOTAL --}}
                        <div class="py-6 border-b border-white/10">

                            <div class="flex justify-between">
                                <span>Tiền ghế</span>
                                <span>{{ number_format($seatTotalPrice, 0, ',', '.') }}đ</span>
                            </div>

                            <div class="flex justify-between mt-2">
                                <span>Tiền đồ ăn</span>
                                <span>{{ number_format($foodTotal, 0, ',', '.') }}đ</span>
                            </div>

                        </div>

                        {{-- VOUCHER --}}
                        <div class="py-6 border-b border-white/10">

                            <h3 class="mb-4 text-lg font-black text-yellow-400">Mã giảm giá</h3>

                            <div class="flex gap-2">
                                <input type="text" id="voucherCode" placeholder="Nhập mã voucher..."
                                    class="flex-1 rounded-2xl border border-white/10 bg-[#1d1d1d] px-4 py-3 text-white outline-none focus:border-yellow-400">

                                <button type="button" onclick="applyVoucher()"
                                    class="rounded-2xl bg-yellow-400 px-5 font-black text-black hover:bg-yellow-300">
                                    Áp dụng
                                </button>
                            </div>

                            <div id="voucherResult"
                                class="mt-3 hidden rounded-xl border border-yellow-400/30 bg-yellow-400/10 p-3 text-sm text-yellow-300">
                                ✔ Voucher đã áp dụng
                            </div>

                            <div class="border-t border-white/10 mt-4 pt-4 flex justify-between text-yellow-400 text-2xl font-black">
                                <span>Tổng</span>
                                <span id="grandTotal">{{ number_format($grandTotal, 0, ',', '.') }}đ</span>
                            </div>

                        </div>

                        {{-- PAYMENT METHOD REFACTOR --}}
                        <div class="pt-6">

                            <h2 class="text-xl font-black text-yellow-400 mb-4">Chọn phương thức thanh toán</h2>

                            <form id="paymentForm" action="{{ route('dat_ve.xu_ly_thanh_toan', $suatChieu->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="ghe" value="{{ request('ghe') }}">
                                <input type="hidden" name="food_cart" value="{{ request('food_cart') }}">
                                <input type="hidden" id="submitVoucherCode" name="voucher_code" value="">

                                <div class="space-y-3">
                                    {{-- LỰA CHỌN 1: CỔNG VNPAY --}}
                                    <label class="payment-method-label flex items-center justify-between border border-yellow-400 bg-yellow-400/10 p-4 rounded-2xl cursor-pointer transition dynamics-duration-200">
                                        <div class="flex items-center gap-3">
                                            <input type="radio" checked name="payment_method" value="online" class="accent-yellow-400 w-5 h-5">
                                            <div class="ml-1">
                                                <span class="block font-bold text-gray-100">Cổng thanh toán VNPAY</span>
                                                <span class="block text-xs text-gray-400 mt-0.5">Thanh toán qua ATM, Thẻ quốc tế hoặc ứng dụng ngân hàng</span>
                                            </div>
                                        </div>
                                        <div class="bg-white px-2 py-1 rounded-lg flex items-center justify-center shadow-sm">
                                            <img src="{{ asset('assets/images/logo-vnpay.webp') }}" class="h-5 w-16 object-contain" alt="VNPAY Logo">
                                        </div>
                                    </label>

                                    {{-- LỰA CHỌN 2: CHUYỂN KHOẢN VIETQR --}}
                                    <label class="payment-method-label flex items-center justify-between border border-white/10 bg-zinc-900/30 p-4 rounded-2xl cursor-pointer transition dynamics-duration-200 hover:border-white/20">
                                        <div class="flex items-center gap-3">
                                            <input type="radio" name="payment_method" value="vietqr" class="accent-yellow-400 w-5 h-5">
                                            <div class="ml-1">
                                                <span class="block font-bold text-gray-200">Chuyển khoản nhanh VietQR</span>
                                                <span class="block text-xs text-gray-400 mt-0.5">Tạo mã QR bốc động số tiền, quét để chuyển khoản an toàn</span>
                                            </div>
                                        </div>
                                        <div class="bg-white px-2 py-1 rounded-lg flex items-center justify-center shadow-sm">
                                            <img src="{{ asset('assets/images/logo-vietqr.png') }}" class="h-5 w-16 object-contain" alt="VietQR Logo">
                                        </div>
                                    </label>
                                </div>

                                <button type="submit"
                                    class="mt-6 w-full rounded-2xl bg-yellow-400 py-4 font-black text-black hover:bg-yellow-300 transition duration-200 shadow-lg shadow-yellow-400/10">
                                    THANH TOÁN NGAY
                                </button>
                            </form>

                            <a href="{{ route('dat_ve.chon_do_an', ['suat_chieu_id' => $suatChieu->id]) }}?ghe={{ request('ghe') }}"
                                class="mt-3 flex w-full items-center justify-center rounded-2xl border border-white/20 bg-white/5 py-4 font-semibold text-gray-300 transition hover:border-white/40 hover:bg-white/10 hover:text-white">
                                ← Quay lại chọn đồ ăn
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection

@section('scripts')
<script>
    let appliedVoucher = null;
    let baseTotal = {{ $grandTotal }};

    function applyVoucher() {
        const code = document.getElementById('voucherCode').value.trim();
        const result = document.getElementById('voucherResult');
        const totalEl = document.getElementById('grandTotal');

        if (!code) {
            alert('Nhập mã voucher');
            return;
        }

        appliedVoucher = {
            code: code,
            discount: 20000
        };

        const submitVoucherInput = document.getElementById('submitVoucherCode');
        if (submitVoucherInput) {
            submitVoucherInput.value = code;
        }

        result.classList.remove('hidden');
        result.innerHTML = `✔ Đã áp dụng: <b>${code}</b> (-${appliedVoucher.discount.toLocaleString('vi-VN')}đ)`;

        const final = Math.max(0, baseTotal - appliedVoucher.discount);
        totalEl.innerText = final.toLocaleString('vi-VN') + 'đ';
    }

    document.addEventListener('DOMContentLoaded', function () {

        // ==========================================
        // EFFECT LOGIC: Xử lý đổi màu viền động khi click chọn ví
        // ==========================================
        const paymentLabels = document.querySelectorAll('.payment-method-label');
        paymentLabels.forEach(label => {
            const radio = label.querySelector('input[type="radio"]');
            
            radio.addEventListener('change', function() {
                // Reset toàn bộ các label về trạng thái mặc định viền tối
                paymentLabels.forEach(l => {
                    l.classList.remove('border-yellow-400', 'bg-yellow-400/10');
                    l.classList.add('border-white/10', 'bg-zinc-900/30');
                    l.querySelector('span').classList.remove('text-gray-100');
                    l.querySelector('span').classList.add('text-gray-200');
                });

                // Kích hoạt màu vàng gold sáng rực rỡ cho tùy chọn vừa click
                if (this.checked) {
                    label.classList.add('border-yellow-400', 'bg-yellow-400/10');
                    label.classList.remove('border-white/10', 'bg-zinc-900/30');
                    label.querySelector('span').classList.add('text-gray-100');
                    label.querySelector('span').classList.remove('text-gray-200');
                }
            });
        });

        // COUNTDOWN TIMER LOGIC
        const countdownEl = document.getElementById('countdown');
        if (!countdownEl) return;

        const storageKey = 'booking_deadline_{{ $suatChieu->id }}';

        function getStoredDeadline() {
            try { return Number(localStorage.getItem(storageKey)) || null; } catch (e) { return null; }
        }

        function setStoredDeadline(deadline) {
            try { return localStorage.setItem(storageKey, String(deadline)); } catch (e) {}
        }

        function clearStoredDeadline() {
            try { return localStorage.removeItem(storageKey); } catch (e) {}
        }

        let deadline = getStoredDeadline();

        if (!deadline || deadline <= Date.now()) {
            deadline = Date.now() + 7 * 60 * 1000;
            setStoredDeadline(deadline);
        }

        function updateCountdown() {
            const remaining = deadline - Date.now();

            if (remaining <= 0) {
                clearStoredDeadline();
                countdownEl.innerText = '00:00';
                countdownEl.classList.add('animate-pulse');
                window.location.href = "{{ route('home') }}";
                return;
            }

            const minutes = Math.floor(remaining / 60000);
            const seconds = Math.floor((remaining % 60000) / 1000);

            countdownEl.innerText = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');

            if (remaining <= 60000) {
                countdownEl.classList.add('animate-pulse');
            }
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);
    });
</script>
@endsection
