@extends('layouts.user')

@section('title', 'Checkout - ' . $suatChieu->phim->ten_phim)

@section('content')

    <style>
        /* Custom Scrollbar Mảnh dành riêng cho danh sách Voucher */
        .custom-voucher-scroll::-webkit-scrollbar {
            width: 5px;
        }
        .custom-voucher-scroll::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.02);
            border-radius: 10px;
        }
        .custom-voucher-scroll::-webkit-scrollbar-thumb {
            background: #3f3f46;
            border-radius: 10px;
        }
        .custom-voucher-scroll::-webkit-scrollbar-thumb:hover {
            background: #eab308;
        }
        .custom-voucher-scroll {
            scrollbar-width: thin;
            scrollbar-color: #3f3f46 transparent;
        }
    </style>

    <div class="booking-checkout-page min-h-screen bg-[#080808] pt-24 pb-12 text-white" lang="vi" spellcheck="false">

        <div class="booking-checkout-container mx-auto max-w-7xl px-4 lg:px-6">

            {{-- HEADER --}}
            <div class="mb-8">
                <p class="text-xs uppercase tracking-[0.45em] text-yellow-400">Checkout</p>
                <h1 class="mt-2 text-4xl font-black">THANH TOÁN</h1>
                <p class="mt-2 text-gray-400">Kiểm tra thông tin trước khi hoàn tất đơn đặt vé.</p>
            </div>

            {{-- BOX --}}
            <div class="booking-checkout-shell overflow-hidden rounded-3xl border border-zinc-800 bg-[#141414] shadow-2xl">
                {{-- COUNTDOWN --}}
                <div class="mb-8 flex items-center justify-between rounded-2xl border border-zinc-800 bg-zinc-900/90 p-4">

                    <div>
                        <p class="text-[10px] uppercase tracking-[0.35em] text-gray-500">
                            Thời gian hoàn tất thanh toán
                        </p>

                        <p class="mt-1 text-sm text-gray-400">
                            Đơn đặt vé sẽ tự động hủy khi hết thời gian.
                        </p>
                    </div>

                    <div class="flex items-center gap-3 rounded-2xl border border-zinc-800 bg-black/40 px-4 py-2">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full border border-zinc-700 bg-black/60 text-lg">⏰</div>
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.3em] text-gray-500">Thời gian còn lại</p>
                            <div id="countdown" class="text-2xl font-black tracking-[0.2em] text-red-400">07:00</div>
                        </div>
                    </div>

                </div>

                <div class="booking-checkout-layout grid lg:grid-cols-[380px_1fr]">

                    {{-- ================= LEFT: THÔNG TIN KHÁCH HÀNG & PHIM ================= --}}
                    <div class="booking-checkout-info border-r border-zinc-800/80 p-8">

                        {{-- USER --}}
                        <div class="pb-6">

                            <div class="mb-4 flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-yellow-400 text-black text-xl font-bold">👤</div>
                                <div>
                                    <h2 class="text-xl font-black text-yellow-400">Thông tin người đặt</h2>
                                    <p class="text-sm text-gray-400">Tài khoản</p>
                                </div>
                            </div>

                            <input readonly value="{{ auth()->user()->ho_ten ?? (auth()->user()->ten ?? '') }}"
                                class="w-full mb-3 rounded-xl border border-zinc-800 bg-[#1a1a1a] px-4 py-3 text-gray-200">

                            <input readonly value="{{ auth()->user()->email }}"
                                class="w-full rounded-xl border border-zinc-800 bg-[#1a1a1a] px-4 py-3 text-gray-200">
                        </div>

                        {{-- FILM --}}
                        <div class="border-t border-zinc-800/80 pt-6 mt-6">

                            <h2 class="text-lg font-black text-yellow-400 mb-3">Thông tin Phim</h2>

                            <div class="rounded-xl overflow-hidden border border-zinc-800">
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
                        <div class="border-b border-zinc-800/80 pb-6">

                            <h2 class="text-2xl font-black text-yellow-400 mb-4">Danh sách đặt</h2>

                            <div class="py-6 border-b border-zinc-800/80">

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

                        {{-- TOTAL SUMMARY --}}
                        <div class="py-6 border-b border-zinc-800/80">

                            <div class="flex justify-between">
                                <span>Tiền ghế</span>
                                <span>{{ number_format($seatTotalPrice, 0, ',', '.') }}đ</span>
                            </div>

                            <div class="flex justify-between mt-2">
                                <span>Tiền đồ ăn</span>
                                <span>{{ number_format($foodTotal, 0, ',', '.') }}đ</span>
                            </div>

                            <div id="discountRow" class="hidden justify-between mt-2 text-green-400 font-semibold">
                                <span>Giảm giá (Voucher)</span>
                                <span id="discountAmount">-0đ</span>
                            </div>

                        </div>

                        {{-- VOUCHER SECTION (GIAO DIỆN MỚI CÓ BORDER TỐI + SCROLLBAR TÙY CHỈNH) --}}
                        <div class="py-6 border-b border-zinc-800/80">

                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-lg font-black text-yellow-400">Mã giảm giá</h3>
                                    <p class="text-xs text-gray-400">Chọn voucher từ ví hoặc nhập mã ưu đãi</p>
                                </div>
                                @if(isset($vouchersKhaDung) && $vouchersKhaDung->isNotEmpty())
                                    <span class="text-xs bg-yellow-400/10 text-yellow-400 font-bold px-3 py-1 rounded-full border border-yellow-400/30">
                                        {{ $vouchersKhaDung->count() }} voucher khả dụng
                                    </span>
                                @endif
                            </div>

                            {{-- DANH SÁCH THẺ VOUCHER --}}
                            @if(!isset($vouchersKhaDung) || $vouchersKhaDung->isEmpty())
                                <div class="flex items-center justify-between rounded-2xl border border-zinc-800/80 bg-[#1a1a1a] px-4 py-3.5 text-gray-400 mb-4">
                                    <span class="text-sm">Không có voucher khả dụng</span>
                                    <span class="text-xs text-gray-500 bg-white/5 px-2 py-1 rounded">0 voucher</span>
                                </div>
                            @else
                                <div class="custom-voucher-scroll space-y-3 max-h-60 overflow-y-auto pr-2 mb-4">
                                    @foreach($vouchersKhaDung as $userVoucher)
                                        @php
                                            $vc = $userVoucher->voucher;
                                            $giamGia = (float)($vc->gia_tri_giam ?? 0);
                                            $tenVoucher = $vc->ten_voucher ?? 'Voucher giảm giá';
                                            $code = $userVoucher->ma_voucher_ca_nhan;
                                        @endphp
                                        <div onclick="selectVoucherCard('{{ $code }}')"
                                            id="voucher-card-{{ $code }}"
                                            class="voucher-card group relative flex items-center justify-between p-3.5 rounded-2xl border border-zinc-800 bg-[#1a1a1a] hover:border-yellow-400/50 cursor-pointer transition-all duration-200">
                                            
                                            <div class="flex items-center gap-3">
                                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-yellow-400 to-amber-600 text-black font-black text-lg shadow-md shadow-yellow-500/10">
                                                    🎟️
                                                </div>
                                                <div>
                                                    <h4 class="font-bold text-sm text-gray-100 group-hover:text-yellow-400 transition-colors">{{ $tenVoucher }}</h4>
                                                    <div class="flex items-center gap-2 mt-1">
                                                        <span class="text-xs font-mono font-bold text-yellow-400 bg-yellow-400/10 px-2 py-0.5 rounded border border-yellow-400/20">
                                                            {{ $code }}
                                                        </span>
                                                        <span class="text-xs text-green-400 font-semibold">
                                                            Giảm {{ number_format($giamGia, 0, ',', '.') }}đ
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="ml-2">
                                                <button type="button"
                                                    id="btn-voucher-{{ $code }}"
                                                    class="btn-voucher-action text-xs font-bold px-3.5 py-1.5 rounded-xl border border-yellow-400/40 text-yellow-400 group-hover:bg-yellow-400 group-hover:text-black transition duration-200">
                                                    Sử dụng
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- NHẬP MÃ THỦ CÔNG --}}
                            <div class="flex gap-2">
                                <input type="text" id="voucherCode" placeholder="Hoặc nhập mã voucher khác..."
                                    class="flex-1 rounded-2xl border border-zinc-800 bg-[#1a1a1a] px-4 py-3 text-white outline-none focus:border-yellow-400 uppercase text-sm tracking-wider transition">

                                <button type="button" id="applyVoucherButton" onclick="applyVoucher()"
                                    class="rounded-2xl bg-yellow-400 px-5 font-black text-black hover:bg-yellow-300 transition duration-200 text-sm shrink-0">
                                    Áp dụng
                                </button>
                            </div>

                            {{-- THÔNG BÁO KẾT QUẢ --}}
                            <div id="voucherResult"
                                class="mt-3 hidden rounded-2xl border border-yellow-400/30 bg-yellow-400/10 p-3.5 text-sm text-yellow-300 flex items-center justify-between">
                                <span id="voucherResultText">✔ Đã áp dụng voucher</span>
                                <button type="button" onclick="resetVoucher()" class="text-xs text-red-400 font-bold hover:text-red-300 underline ml-2 transition">
                                    Hủy dùng
                                </button>
                            </div>

                            {{-- TỔNG TIỀN BAN ĐẦU / SAU GIẢM GIÁ --}}
                            <div class="border-t border-zinc-800/80 mt-5 pt-4 flex justify-between text-yellow-400 text-2xl font-black">
                                <span>Tổng</span>
                                <span id="grandTotal">{{ number_format($grandTotal, 0, ',', '.') }}đ</span>
                            </div>

                        </div>

                        {{-- PAYMENT METHOD --}}
                        <div class="pt-6">

                            <h2 class="text-xl font-black text-yellow-400 mb-4">Chọn phương thức thanh toán</h2>

                            <form id="paymentForm" action="{{ route('dat_ve.xu_ly_thanh_toan', $suatChieu->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="ghe" value="{{ $selectedSeats->implode(', ') }}">
                                <input type="hidden" name="food_cart" value="{{ json_encode($foodItems->toArray(), JSON_UNESCAPED_UNICODE) }}">
                                <input type="hidden" id="submitVoucherCode" name="voucher_code" value="">
                                @if(!empty($pendingTicketId))
                                    <input type="hidden" name="pending_ticket_id" value="{{ $pendingTicketId }}">
                                @endif

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
                                    <label class="payment-method-label flex items-center justify-between border border-zinc-800 bg-[#1a1a1a] p-4 rounded-2xl cursor-pointer transition dynamics-duration-200 hover:border-zinc-700">
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

                            @php
                                $backUrl = route('dat_ve.chon_do_an', ['suat_chieu_id' => $suatChieu->id]) . '?ghe=' . urlencode(request('ghe'));
                                if (request('food_cart')) {
                                    $backUrl .= '&food_cart=' . urlencode(request('food_cart'));
                                }
                                if (! empty($pendingTicketId)) {
                                    $backUrl .= '&pending_ticket_id=' . urlencode($pendingTicketId);
                                }
                            @endphp

                            <a href="{{ $backUrl }}"
                                class="mt-3 flex w-full items-center justify-center rounded-2xl border border-zinc-800 bg-white/5 py-4 font-semibold text-gray-300 transition hover:border-zinc-700 hover:bg-white/10 hover:text-white">
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
    const baseTotal = Number("{{ (float) $grandTotal }}");
    let appliedVoucher = null;
    let voucherRequestRunning = false;

    const formatVnd = (value) =>
        new Intl.NumberFormat('vi-VN').format(Math.max(0, Number(value) || 0)) + 'đ';

    function selectVoucherCard(code) {
        const codeInput = document.getElementById('voucherCode');
        if (codeInput) {
            codeInput.value = code;
        }

        if (appliedVoucher && appliedVoucher.code === code) {
            resetVoucher();
            return;
        }

        applyVoucher();
    }

    function updateVoucherCardsUI(activeCode = null) {
        document.querySelectorAll('.voucher-card').forEach(card => {
            card.classList.remove('border-yellow-400', 'bg-yellow-400/10', 'ring-1', 'ring-yellow-400');
            card.classList.add('border-zinc-800', 'bg-[#1a1a1a]');
        });

        document.querySelectorAll('.btn-voucher-action').forEach(btn => {
            btn.classList.remove('bg-yellow-400', 'text-black');
            btn.classList.add('border-yellow-400/40', 'text-yellow-400');
            btn.textContent = 'Sử dụng';
        });

        if (activeCode) {
            const activeCard = document.getElementById(`voucher-card-${activeCode}`);
            const activeBtn = document.getElementById(`btn-voucher-${activeCode}`);

            if (activeCard) {
                activeCard.classList.remove('border-zinc-800', 'bg-[#1a1a1a]');
                activeCard.classList.add('border-yellow-400', 'bg-yellow-400/10', 'ring-1', 'ring-yellow-400');
            }

            if (activeBtn) {
                activeBtn.classList.remove('border-yellow-400/40');
                activeBtn.classList.add('bg-yellow-400', 'text-black');
                activeBtn.textContent = 'Đã chọn';
            }
        }
    }

    function resetVoucher(message = '') {
        appliedVoucher = null;
        updateVoucherCardsUI(null);

        const result = document.getElementById('voucherResult');
        const textSpan = document.getElementById('voucherResultText');
        const totalEl = document.getElementById('grandTotal');
        const submitVoucherInput = document.getElementById('submitVoucherCode');
        const discountRow = document.getElementById('discountRow');
        const codeInput = document.getElementById('voucherCode');

        if (codeInput) codeInput.value = '';
        if (submitVoucherInput) submitVoucherInput.value = '';
        if (totalEl) totalEl.innerText = formatVnd(baseTotal);
        if (discountRow) discountRow.classList.add('hidden');

        if (result) {
            if (message) {
                result.classList.remove('hidden');
                result.classList.remove('border-yellow-400/30', 'bg-yellow-400/10', 'text-yellow-300');
                result.classList.add('border-red-400/30', 'bg-red-400/10', 'text-red-300');
                
                if (textSpan) textSpan.textContent = message;
            } else {
                result.classList.add('hidden');
            }
        }
    }

    async function applyVoucher() {
        if (voucherRequestRunning) return;

        const codeInput = document.getElementById('voucherCode');
        const result = document.getElementById('voucherResult');
        const textSpan = document.getElementById('voucherResultText');
        const totalEl = document.getElementById('grandTotal');
        const submitVoucherInput = document.getElementById('submitVoucherCode');
        const applyButton = document.getElementById('applyVoucherButton');
        const discountRow = document.getElementById('discountRow');
        const discountAmount = document.getElementById('discountAmount');

        const code = (codeInput?.value || '').trim().toUpperCase();

        if (!code) {
            resetVoucher('Vui lòng chọn hoặc nhập mã voucher.');
            return;
        }

        voucherRequestRunning = true;
        if (applyButton) {
            applyButton.disabled = true;
            applyButton.textContent = '...';
        }

        try {
            const response = await fetch("{{ route('dat_ve.ap_dung_voucher') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                },
                body: JSON.stringify({
                    voucher_code: code,
                    subtotal: baseTotal,
                }),
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                resetVoucher(data.message || 'Voucher không hợp lệ.');
                return;
            }

            appliedVoucher = {
                code: data.voucher_code,
                discount: Number(data.discount),
                finalTotal: Number(data.final_total),
            };

            if (codeInput) codeInput.value = appliedVoucher.code;
            if (submitVoucherInput) submitVoucherInput.value = appliedVoucher.code;

            updateVoucherCardsUI(appliedVoucher.code);

            if (discountRow && discountAmount) {
                discountAmount.innerText = '-' + formatVnd(appliedVoucher.discount);
                discountRow.classList.remove('hidden');
                discountRow.classList.add('flex');
            }

            if (result) {
                result.classList.remove('hidden');
                result.classList.remove('border-red-400/30', 'bg-red-400/10', 'text-red-300');
                result.classList.add('border-yellow-400/30', 'bg-yellow-400/10', 'text-yellow-300');
                
                if (textSpan) {
                    textSpan.innerHTML = `✔ Đã áp dụng <b>${appliedVoucher.code}</b> (-${formatVnd(appliedVoucher.discount)})`;
                }
            }

            if (totalEl) {
                totalEl.innerText = formatVnd(appliedVoucher.finalTotal);
            }
        } catch (error) {
            console.error(error);
            resetVoucher('Không thể kết nối máy chủ.');
        } finally {
            voucherRequestRunning = false;
            if (applyButton) {
                applyButton.disabled = false;
                applyButton.textContent = 'Áp dụng';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const codeInput = document.getElementById('voucherCode');
        const paymentForm = document.getElementById('paymentForm');

        codeInput?.addEventListener('input', function () {
            const val = this.value.trim().toUpperCase();
            if (appliedVoucher && val !== appliedVoucher.code) {
                resetVoucher();
            }
        });

        codeInput?.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                applyVoucher();
            }
        });

        paymentForm?.addEventListener('submit', function (event) {
            const typedCode = (codeInput?.value || '').trim().toUpperCase();

            if (typedCode && (!appliedVoucher || appliedVoucher.code !== typedCode)) {
                event.preventDefault();
                resetVoucher('Bạn phải bấm “Áp dụng” và xác thực voucher trước khi thanh toán.');
                codeInput?.focus();
            }
        });

        const paymentLabels = document.querySelectorAll('.payment-method-label');

        paymentLabels.forEach(label => {
            const radio = label.querySelector('input[type="radio"]');

            radio.addEventListener('change', function () {
                paymentLabels.forEach(item => {
                    item.classList.remove('border-yellow-400', 'bg-yellow-400/10');
                    item.classList.add('border-zinc-800', 'bg-[#1a1a1a]');

                    const text = item.querySelector('span');
                    text?.classList.remove('text-gray-100');
                    text?.classList.add('text-gray-200');
                });

                if (this.checked) {
                    label.classList.add('border-yellow-400', 'bg-yellow-400/10');
                    label.classList.remove('border-zinc-800', 'bg-[#1a1a1a]');

                    const text = label.querySelector('span');
                    text?.classList.add('text-gray-100');
                    text?.classList.remove('text-gray-200');
                }
            });
        });

        const countdownEl = document.getElementById('countdown');

        if (!countdownEl) {
            return;
        }

        const storageKey = "booking_deadline_{{ $suatChieu->id }}";
        const pendingDeadlineValue = "{{ $pendingDeadline ?? '' }}";
        const pendingDeadline = pendingDeadlineValue ? Number(pendingDeadlineValue) : null;

        function getStoredDeadline() {
            try {
                const value = Number(localStorage.getItem(storageKey));
                return Number.isFinite(value) ? value : null;
            } catch (error) {
                return null;
            }
        }

        function setStoredDeadline(deadline) {
            try {
                localStorage.setItem(storageKey, String(deadline));
            } catch (error) {
                console.error(error);
            }
        }

        function clearStoredDeadline() {
            try {
                localStorage.removeItem(storageKey);
            } catch (error) {
                console.error(error);
            }
        }

        const storedDeadline = getStoredDeadline();
        const validPendingDeadline = pendingDeadline && pendingDeadline > Date.now() ? pendingDeadline : null;
        const validStoredDeadline = storedDeadline && storedDeadline > Date.now() ? storedDeadline : null;
        let deadline = null;

        if (validPendingDeadline && validStoredDeadline) {
            deadline = Math.min(validPendingDeadline, validStoredDeadline);
        } else if (validPendingDeadline) {
            deadline = validPendingDeadline;
        } else if (validStoredDeadline) {
            deadline = validStoredDeadline;
        } else {
            deadline = Date.now() + 7 * 60 * 1000;
        }

        setStoredDeadline(deadline);

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

            countdownEl.innerText =
                String(minutes).padStart(2, '0') +
                ':' +
                String(seconds).padStart(2, '0');

            if (remaining <= 60000) {
                countdownEl.classList.add('animate-pulse');
            }
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);
    });
</script>
@endsection