@extends('layouts.user')

@section('title', 'Chọn ghế - ' . $suatChieu->phim->ten_phim)

@push('styles')
<style>
    /* ============== SƠ ĐỒ GHẾ ============== */
    .seat-wrapper {
        position: relative;
        display: inline-block;
    }

    .seat-button {
        position: relative;
        width: 44px;
        height: 44px;
        border-radius: 8px 8px 4px 4px;
        border: 1px solid rgba(255, 255, 255, .18);
        background: #2a2a2a;
        color: #e8e8e8;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .02em;
        transition: transform .15s ease, background .15s ease, border-color .15s ease, color .15s ease, box-shadow .15s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .seat-button:hover:not(.booked) {
        transform: translateY(-2px);
        background: #3a3a3a;
        border-color: rgba(217, 154, 50, .55);
    }

    .seat-button.selected {
        background: linear-gradient(135deg, #f4c56a, #d99a32);
        border-color: #f4c56a;
        color: #2b1208;
        box-shadow: 0 0 0 3px rgba(244, 197, 106, .25), 0 8px 20px rgba(217, 154, 50, .35);
        transform: translateY(-2px);
    }

    .seat-button.selected::after {
        content: "✓";
        position: absolute;
        top: -6px;
        right: -6px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #1a8f3a;
        color: #fff;
        font-size: 11px;
        font-weight: 900;
        line-height: 18px;
        text-align: center;
        border: 2px solid #121212;
    }

    .seat-button.booked {
        background: #0e0e0e;
        color: #4a4a4a;
        cursor: not-allowed;
        opacity: .7;
        border-color: rgba(255, 255, 255, .05);
    }

    .seat-button.booked::before {
        content: "✕";
        position: absolute;
        font-size: 16px;
        color: #5a5a5a;
    }

    .seat-button.booked .seat-label {
        opacity: 0;
    }

    /* ============== TOOLTIP ============== */
    .seat-tooltip {
        position: absolute;
        bottom: calc(100% + 12px);
        left: 50%;
        transform: translateX(-50%) translateY(4px);
        background: #1a1a1a;
        border: 1px solid rgba(217, 154, 50, .55);
        color: #fff;
        padding: 10px 14px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.5;
        white-space: nowrap;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .55), 0 0 0 4px rgba(217, 154, 50, .08);
        pointer-events: none;
        opacity: 0;
        transition: opacity .15s ease, transform .15s ease;
        z-index: 50;
        min-width: 170px;
    }

    .seat-tooltip::after {
        content: "";
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 6px solid transparent;
        border-top-color: rgba(217, 154, 50, .55);
    }

    .seat-tooltip .tt-title {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #f4c56a;
        font-weight: 900;
        font-size: 13px;
        margin-bottom: 4px;
    }

    .seat-tooltip .tt-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        color: #d0d0d0;
    }

    .seat-tooltip .tt-row .tt-label {
        color: #888;
    }

    .seat-tooltip .tt-row .tt-value {
        color: #fff;
        font-weight: 700;
    }

    .seat-wrapper:hover .seat-tooltip,
    .seat-button.selected+.seat-tooltip,
    .seat-wrapper.show-tooltip .seat-tooltip {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }

    .seat-wrapper.selected .seat-tooltip {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
        border-color: rgba(244, 197, 106, .85);
    }

    /* Tooltip cho hàng dưới cùng (đẩy lên trên đã là mặc định) — không cần đổi chiều */
    /* Tooltip cho hàng đầu tiên sẽ hơi sát màn hình — đẩy xuống dưới */
    .seat-row:first-of-type .seat-tooltip {
        bottom: auto;
        top: calc(100% + 12px);
    }

    .seat-row:first-of-type .seat-tooltip::after {
        top: auto;
        bottom: 100%;
        border-top-color: transparent;
        border-bottom-color: rgba(217, 154, 50, .55);
    }

    /* ============== SCREEN ============== */
    .screen-line {
        height: 34px;
        border-radius: 50% 50% 0 0;
        background: linear-gradient(180deg, rgba(244, 197, 106, .9), rgba(217, 154, 50, .15));
        box-shadow: 0 18px 40px rgba(217, 154, 50, .25);
    }

    .row-label {
        width: 28px;
        text-align: center;
        font-size: 14px;
        font-weight: 900;
        color: #d99a32;
    }

    /* ============== BẢNG CHỌN GHẾ BÊN PHẢI ============== */
    .selected-list:empty::before {
        content: "Chưa chọn ghế nào";
        display: block;
        text-align: center;
        color: #6b6b6b;
        font-style: italic;
        padding: 18px 0;
        font-size: 12px;
    }

    .selected-list .pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: linear-gradient(135deg, #f4c56a, #d99a32);
        color: #2b1208;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        margin: 2px;
    }

    .selected-list .pill button {
        background: transparent;
        border: 0;
        color: #2b1208;
        font-size: 14px;
        line-height: 1;
        cursor: pointer;
        font-weight: 900;
    }

</style>
@endpush

@section('content')
<div class="min-h-screen bg-[#080808] px-6 pt-28 pb-12 text-white">
    <div class="mx-auto grid max-w-7xl gap-6 lg:grid-cols-[360px_1fr]">
        <aside class="rounded-2xl border border-white/10 bg-[#121212] p-5">
            <img src="{{ $suatChieu->phim->poster ?? 'https://via.placeholder.com/300x450?text=Poster' }}" class="h-[420px] w-full rounded-xl object-cover" alt="{{ $suatChieu->phim->ten_phim }}">

            <h1 class="mt-5 text-2xl font-black text-[#d99a32]">{{ $suatChieu->phim->ten_phim }}</h1>
            <div class="mt-4 space-y-2 text-sm text-gray-300">
                <p><strong>Rạp:</strong> {{ $suatChieu->rapChieuPhim->ten_rap }}</p>
                <p><strong>Phòng:</strong> Phòng 1</p>
                <p><strong>Suất chiếu:</strong> {{ $suatChieu->thoi_gian_chieu->format('H:i d/m/Y') }}</p>
                <p><strong>Giá vé:</strong> {{ number_format($suatChieu->gia_ve, 0, ',', '.') }} VND</p>
            </div>

            @if($errors->any())
            <div class="mt-4 rounded-xl bg-red-500/15 px-4 py-3 text-sm text-red-300">
                {{ $errors->first() }}
            </div>
            @endif

            <form id="booking-form" method="POST" action="{{ route('user.bookings.store', $suatChieu) }}" class="mt-5">
                @csrf
                <input type="hidden" name="ghe_duoc_chon" id="ghe_duoc_chon" value="{{ old('ghe_duoc_chon') }}">

                <div class="mb-4 rounded-xl bg-white/5 p-4">
                    <div class="flex justify-between text-sm">
                        <span>Ghế đã chọn</span>
                        <strong id="count-seats" class="text-[#f4c56a]">0</strong>
                    </div>

                    {{-- Danh sách ghế đang chọn (pill) --}}
                    <div id="selected-list" class="selected-list mt-2 mb-3"></div>

                    <div class="mt-4 border-t border-white/10 pt-4">

                        <label class="mb-2 block text-sm font-bold">
                            Voucher
                        </label>

                        <select id="voucher_select" name="voucher_id" class="w-full rounded-xl border border-white/10 bg-[#1a1a1a] p-3 text-white">
                            <option value="">
                                Không sử dụng voucher
                            </option>

                            @foreach($vouchers as $voucher)
                            <option value="{{ $voucher->id }}" data-discount="{{ $voucher->voucher->gia_tri_giam }}">
                                {{ $voucher->ma_voucher_ca_nhan }}
                                -
                                Giảm
                                {{ number_format($voucher->voucher->gia_tri_giam) }}đ
                            </option>
                            @endforeach
                        </select>

                    </div>

                    <div class="mt-4 space-y-2 text-sm">

                        <div class="flex justify-between">
                            <span>Tạm tính</span>
                            <span id="subtotal-price">0đ</span>
                        </div>

                        <div class="flex justify-between text-green-400">
                            <span>Giảm giá</span>
                            <span id="discount-price">0đ</span>
                        </div>

                        <div class="flex justify-between border-t border-white/10 pt-2 text-base font-black">
                            <span>Thanh toán</span>
                            <span class="text-[#f4c56a]" id="final-price">
                                0đ
                            </span>
                        </div>

                    </div>
                </div>

                <button id="btn-dat-ve" class="w-full rounded-xl bg-[#d99a32] px-5 py-3 font-black text-[#2b1208] opacity-60 transition hover:bg-[#f4c56a]" disabled>
                    Đặt vé
                </button>
            </form>
        </aside>

        <section class="rounded-2xl border border-white/10 bg-[#121212] p-6">
            <div class="mx-auto max-w-4xl text-center">
                <div class="mx-auto mb-10 w-4/5">
                    <div class="screen-line"></div>
                    <div class="mt-2 text-xs font-bold uppercase tracking-[0.3em] text-[#f4c56a]">Màn hình</div>
                </div>

                <div class="inline-block space-y-2">
                    @foreach($hangGhe as $hang)
                    <div class="seat-row flex items-center gap-2 justify-center">
                        <span class="row-label">{{ $hang }}</span>
                        @for($i = 1; $i <= $soCot; $i++) @php $maGhe=$hang . $i; $daDat=in_array($maGhe, $gheDaDat, true); @endphp <div class="seat-wrapper {{ $daDat ? 'booked' : '' }}" data-seat="{{ $maGhe }}">
                            <button type="button" class="seat-button {{ $daDat ? 'booked' : '' }}" data-seat="{{ $maGhe }}" @disabled($daDat)>
                                <span class="seat-label">{{ $maGhe }}</span>
                            </button>
                            <div class="seat-tooltip" role="tooltip">
                                <div class="tt-title">
                                    <i class="fa-solid fa-couch"></i>
                                    <span>Ghế {{ $maGhe }}</span>
                                </div>
                                <div class="tt-row">
                                    <span class="tt-label">Hàng</span>
                                    <span class="tt-value">{{ $hang }}</span>
                                </div>
                                <div class="tt-row">
                                    <span class="tt-label">Cột</span>
                                    <span class="tt-value">{{ $i }}</span>
                                </div>
                                <div class="tt-row">
                                    <span class="tt-label">Giá</span>
                                    <span class="tt-value">{{ number_format($suatChieu->gia_ve, 0, ',', '.') }}đ</span>
                                </div>
                                @if($daDat)
                                <div class="tt-row" style="margin-top:4px;color:#ff6b6b;">
                                    <span class="tt-label">Trạng thái</span>
                                    <span class="tt-value">Đã bán</span>
                                </div>
                                @else
                                <div class="tt-row" style="margin-top:4px;color:#7ed957;">
                                    <span class="tt-label">Trạng thái</span>
                                    <span class="tt-value">Còn trống</span>
                                </div>
                                @endif
                            </div>
                    </div>
                    @endfor
                    <span class="row-label">{{ $hang }}</span>
                </div>
                @endforeach
            </div>

            <div class="mt-8 flex flex-wrap justify-center gap-5 border-t border-white/10 pt-5 text-sm text-gray-300">
                <span class="flex items-center gap-2">
                    <span class="inline-block h-5 w-5 rounded bg-[#2a2a2a] border border-white/20 align-middle"></span>
                    Ghế trống
                </span>
                <span class="flex items-center gap-2">
                    <span class="inline-block h-5 w-5 rounded bg-gradient-to-br from-[#f4c56a] to-[#d99a32] align-middle"></span>
                    Đang chọn
                </span>
                <span class="flex items-center gap-2">
                    <span class="inline-block h-5 w-5 rounded bg-[#0e0e0e] border border-white/10 align-middle"></span>
                    Đã đặt
                </span>
            </div>
    </div>
    </section>
</div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const wrappers = document.querySelectorAll('.seat-wrapper');
        const selectedInput = document.getElementById('ghe_duoc_chon');
        const countSeats = document.getElementById('count-seats');
        const submitButton = document.getElementById('btn-dat-ve');
        const selectedList = document.getElementById('selected-list');

        const subtotalPrice = document.getElementById('subtotal-price');
        const discountPrice = document.getElementById('discount-price');
        const finalPrice = document.getElementById('final-price');
        const voucherSelect = document.getElementById('voucher_select');

        const price = @json((float) $suatChieu->gia_ve);

        let selectedSeats = selectedInput.value ?
            selectedInput.value.split(',').map(s => s.trim()).filter(Boolean) :
            [];

        function render() {
            wrappers.forEach(wrapper => {
                const code = wrapper.dataset.seat;
                const isSelected = selectedSeats.includes(code);

                wrapper.classList.toggle('selected', isSelected);

                const btn = wrapper.querySelector('.seat-button');
                if (btn) {
                    btn.classList.toggle('selected', isSelected);
                }
            });

            selectedInput.value = selectedSeats.join(',');
            countSeats.textContent = selectedSeats.length;

            const subtotal = selectedSeats.length * price;

            let discount = 0;

            if (voucherSelect && voucherSelect.value) {
                const selectedOption = voucherSelect.options[voucherSelect.selectedIndex];
                discount = Number(selectedOption.dataset.discount || 0);
            }

            if (discount > subtotal) {
                discount = subtotal;
            }

            const finalTotal = Math.max(subtotal - discount, 0);

            if (subtotalPrice) {
                subtotalPrice.textContent = subtotal.toLocaleString('vi-VN') + 'đ';
            }

            if (discountPrice) {
                discountPrice.textContent = '-' + discount.toLocaleString('vi-VN') + 'đ';
            }

            if (finalPrice) {
                finalPrice.textContent = finalTotal.toLocaleString('vi-VN') + 'đ';
            }

            submitButton.disabled = selectedSeats.length === 0;
            submitButton.classList.toggle('opacity-60', selectedSeats.length === 0);

            selectedList.innerHTML = selectedSeats.map(code =>
                `<span class="pill" data-code="${code}">${code}<button type="button" data-remove="${code}" aria-label="Bỏ chọn ${code}">×</button></span>`
            ).join('');

            selectedList.querySelectorAll('button[data-remove]').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();

                    const code = btn.dataset.remove;
                    selectedSeats = selectedSeats.filter(s => s !== code);

                    render();
                });
            });
        }

        wrappers.forEach(wrapper => {
            const btn = wrapper.querySelector('.seat-button');

            if (!btn || btn.classList.contains('booked')) {
                return;
            }

            wrapper.addEventListener('click', (e) => {
                if (e.target.closest('.seat-tooltip')) {
                    return;
                }

                const code = wrapper.dataset.seat;

                selectedSeats = selectedSeats.includes(code) ?
                    selectedSeats.filter(s => s !== code) :
                    [...selectedSeats, code];

                render();
            });
        });

        if (voucherSelect) {
            voucherSelect.addEventListener('change', render);
        }

        document.addEventListener('keydown', (e) => {
            if (e.target.matches('input, textarea, select')) {
                return;
            }

            if (/^[1-9]$/.test(e.key)) {
                const targetCode = 'A' + e.key;
                const target = document.querySelector(`.seat-wrapper[data-seat="${targetCode}"]`);

                if (target && !target.classList.contains('booked')) {
                    target.click();
                }
            }
        });

        render();
    });

</script>
@endsection
