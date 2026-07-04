@extends('layouts.user')

@section('title', 'Checkout - ' . $suatChieu->phim->ten_phim)

@section('content')

    <div class="min-h-screen bg-[#080808] pt-24 pb-12 text-white">

        <div class="mx-auto max-w-7xl px-4 lg:px-6">

            {{-- HEADER --}}
            <div class="mb-8">
                <p class="text-xs uppercase tracking-[0.45em] text-yellow-400">Checkout</p>
                <h1 class="mt-2 text-4xl font-black">THANH TOÁN</h1>
                <p class="mt-2 text-gray-400">Kiểm tra thông tin trước khi hoàn tất đơn đặt vé.</p>
            </div>

            {{-- BOX --}}
            <div class="overflow-hidden rounded-3xl border border-white/10 bg-[#141414] shadow-2xl">

                <div class="grid lg:grid-cols-[380px_1fr]">

                    {{-- ================= LEFT ================= --}}
                    <div class="border-r border-white/10 p-8">

                        {{-- USER --}}
                        <div class="pb-6">

                            <div class="mb-4 flex items-center gap-4">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-yellow-400 text-black text-xl">
                                    👤</div>
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

                            <h2 class="text-lg font-black text-yellow-400 mb-3"> Thông tin Phim</h2>

                            <div class="rounded-xl overflow-hidden border border-white/10">
                                <img src="{{ asset('storage/movies/' . $suatChieu->phim->poster) }}"
                                    alt="{{ $suatChieu->phim->ten_phim }}" class="w-full rounded-lg object-cover">
                            </div>

                            <div class="mt-4 text-sm space-y-2 text-gray-300">
                                <div>Tên rạp : {{ $suatChieu->rapChieuPhim->ten_rap }}</div>
                                <div> Tên Phim : {{ $suatChieu->phim->ten_phim }}</div>
                                <div>Suất chiếu: {{ $suatChieu->thoi_gian_chieu->format('d/m/Y H:i') }}</div>
                            </div>

                        </div>


                    </div>

                    {{-- ================= RIGHT ================= --}}
                    <div class="p-8">

                        {{-- ITEMS --}}
                        <div class="border-b border-white/10 pb-6">

                            <h2 class="text-2xl font-black text-yellow-400 mb-4">Danh sách đặt</h2>

                            {{-- GHẾ GRID 3 CỘT --}}
                            <div class="py-6 border-b border-white/10">

                                <div class="flex justify-between">
                                    <span>Số ghế</span>
                                    <span class="text-yellow-400 font-semibold">
                                        {{ $selectedSeats->implode(', ') }}
                                    </span>
                                </div>

                                @forelse($foodItems as $item)
                                    <div class="flex justify-between items-center mt-3">
                                        <div class="flex items-center gap-3">

                                            @if (!empty($item['image']))
                                                <img src="{{ asset('storage/foods/' . $item['image']) }}"
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


                            {{-- TOTAL --}}
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

                            {{-- ================= VOUCHER ================= --}}
                            <div class="py-6 border-b border-white/10">

                                <h3 class="mb-4 text-lg font-black text-yellow-400">
                                    Mã giảm giá
                                </h3>

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



                            <div
                                class="border-t border-white/10 mt-4 pt-4 flex justify-between text-yellow-400 text-2xl font-black">
                                <span>Tổng</span>
                                <span id="grandTotal">{{ number_format($grandTotal, 0, ',', '.') }}đ</span>
                            </div>

                        </div>


                        {{-- PAYMENT --}}
                        <div class="pt-6">

                            <h2 class="text-xl font-black text-yellow-400 mb-4">Thanh toán</h2>

                            <label
                                class="flex items-center gap-3 border border-yellow-400 bg-yellow-400/10 p-4 rounded-2xl">
                                <input type="radio" checked name="payment_method" class="accent-yellow-400">
                                <span>Online (VNPay / MoMo)</span>
                            </label>

                            <button
                                class="mt-6 w-full rounded-2xl bg-yellow-400 py-4 font-black text-black hover:bg-yellow-300">
                                THANH TOÁN NGAY
                            </button>

                        </div>

                    </div>

                </div>
            </div>

        </div>

    @endsection

    {{-- ================= SCRIPT VOUCHER ================= --}}
    <script>
        let appliedVoucher = null;
        let baseTotal = {{ $grandTotal }};

        function applyVoucher() {
            const code = document.getElementById('voucherCode').value.trim();
            const result = document.getElementById('voucherResult');
            const totalEl = document.getElementById('grandTotal');

            if (!code) return alert('Nhập mã voucher');

            // DEMO LOGIC (sau này thay backend)
            appliedVoucher = {
                code,
                discount: 20000
            };

            result.classList.remove('hidden');
            result.innerHTML = `✔ Đã áp dụng: <b>${code}</b> (-${appliedVoucher.discount.toLocaleString('vi-VN')}đ)`;

            const final = Math.max(0, baseTotal - appliedVoucher.discount);

            totalEl.innerText = final.toLocaleString('vi-VN') + 'đ';
        }
    </script>
