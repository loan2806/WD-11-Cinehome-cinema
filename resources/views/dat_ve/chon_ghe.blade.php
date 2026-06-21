@extends('layouts.user')

@section('title', 'Chọn ghế - ' . $suatChieu->phim->ten_phim)

@push('styles')
<style>
    .dat-ve-page {
        --gold: #d99a32;
        --gold-light: #f4c56a;
        --panel: #121212;
        --soft: rgba(255, 255, 255, .055);
        color: #fff;
    }

    .seat-button {
        position: relative;
        width: 46px;
        height: 42px;
        border-radius: 9px 9px 5px 5px;
        border: 1px solid rgba(255, 255, 255, .16);
        background: var(--seat-color, #2a2a2a);
        color: #fff;
        font-size: 11px;
        font-weight: 900;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease, opacity .18s ease;
    }

    .seat-button:hover:not(:disabled) {
        transform: translateY(-2px);
        border-color: rgba(244, 197, 106, .7);
        box-shadow: 0 8px 20px rgba(217, 154, 50, .2);
    }

    .seat-button.selected {
        background: linear-gradient(135deg, var(--gold-light), var(--gold));
        border-color: var(--gold-light);
        color: #2b1208;
        box-shadow: 0 0 0 3px rgba(244, 197, 106, .2), 0 10px 24px rgba(217, 154, 50, .34);
        transform: translateY(-2px);
    }

    .seat-button.booked,
    .seat-button.maintenance {
        cursor: not-allowed;
        opacity: .55;
        background: #0e0e0e;
        color: #666;
    }

    .seat-button.booked::after,
    .seat-button.maintenance::after {
        content: "×";
        position: absolute;
        inset: 0;
        display: grid;
        place-items: center;
        color: #777;
        font-size: 20px;
    }

    .seat-button.booked span,
    .seat-button.maintenance span {
        opacity: 0;
    }

    .seat-tooltip {
        pointer-events: none;
        position: absolute;
        bottom: calc(100% + 10px);
        left: 50%;
        z-index: 30;
        min-width: 190px;
        transform: translateX(-50%) translateY(6px);
        border: 1px solid rgba(217, 154, 50, .55);
        border-radius: 12px;
        background: #171717;
        padding: 10px 12px;
        text-align: left;
        opacity: 0;
        box-shadow: 0 12px 32px rgba(0, 0, 0, .45);
        transition: opacity .18s ease, transform .18s ease;
    }

    .seat-wrapper:hover .seat-tooltip {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }

    .screen-line {
        height: 34px;
        border-radius: 50% 50% 0 0;
        background: linear-gradient(180deg, rgba(244, 197, 106, .92), rgba(217, 154, 50, .16));
        box-shadow: 0 18px 40px rgba(217, 154, 50, .25);
    }

    .payment-option input:checked + span {
        border-color: rgba(244, 197, 106, .8);
        background: rgba(217, 154, 50, .14);
        color: #f4c56a;
    }
</style>
@endpush

@section('content')
<div class="dat-ve-page min-h-screen bg-[#080808] px-6 pt-28 pb-12">
    <div class="mx-auto grid max-w-7xl gap-6 lg:grid-cols-[370px_1fr]">
        <aside class="rounded-2xl border border-white/10 bg-[#121212] p-5">
            <img
                src="{{ $suatChieu->phim->poster ?? 'https://via.placeholder.com/300x450?text=Poster' }}"
                class="h-[420px] w-full rounded-xl object-cover"
                alt="{{ $suatChieu->phim->ten_phim }}"
            >

            <h1 class="mt-5 text-2xl font-black text-[#d99a32]">{{ $suatChieu->phim->ten_phim }}</h1>
            <div class="mt-4 space-y-2 text-sm text-gray-300">
                <p><strong>Rạp:</strong> {{ $suatChieu->rapChieuPhim->ten_rap }}</p>
                <p><strong>Phòng:</strong> {{ $suatChieu->phongChieu->ten_phong ?? 'Phòng chiếu' }}</p>
                <p><strong>Suất chiếu:</strong> {{ $suatChieu->thoi_gian_chieu->format('H:i d/m/Y') }}</p>
                <p><strong>Giá cơ bản:</strong> {{ number_format((float) $suatChieu->gia_ve, 0, ',', '.') }}đ</p>
            </div>

            @if($errors->any())
                <div class="mt-4 rounded-xl bg-red-500/15 px-4 py-3 text-sm text-red-300">
                    {{ $errors->first() }}
                </div>
            @endif

            @if(! $coSoDoGheThat)
                <div class="mt-4 rounded-xl bg-yellow-500/10 px-4 py-3 text-sm text-yellow-200">
                    Phòng chiếu chưa có sơ đồ ghế trong admin, hệ thống đang dùng sơ đồ mặc định để test đặt vé.
                </div>
            @endif

            <form id="booking-form" method="POST" action="{{ route('user.bookings.store', $suatChieu) }}" class="mt-5">
                @csrf
                <input type="hidden" name="ghe_duoc_chon" id="ghe_duoc_chon" value="{{ old('ghe_duoc_chon') }}">

                <div class="rounded-xl bg-white/5 p-4">
                    <div class="flex justify-between text-sm">
                        <span>Ghế đã chọn</span>
                        <strong id="count-seats" class="text-[#f4c56a]">0</strong>
                    </div>
                    <div id="selected-list" class="mt-3 flex min-h-10 flex-wrap gap-2 text-sm text-gray-400">
                        Chưa chọn ghế nào
                    </div>
                    <div class="mt-3 flex justify-between border-t border-white/10 pt-3 text-sm">
                        <span>Tổng tiền</span>
                        <strong class="text-[#f4c56a]"><span id="total-price">0</span>đ</strong>
                    </div>
                </div>

                <div class="mt-4 rounded-xl bg-white/5 p-4">
                    <div class="mb-3 text-sm font-black text-[#f4c56a]">Phương thức thanh toán</div>
                    <div class="grid gap-2">
                        @forelse($phuongThucThanhToan as $value => $label)
                            <label class="payment-option cursor-pointer">
                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="{{ $value }}"
                                    class="sr-only"
                                    @checked(old('payment_method', array_key_first($phuongThucThanhToan)) === $value)
                                >
                                <span class="block rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-sm font-bold text-gray-200 transition">
                                    {{ $label }}
                                </span>
                            </label>
                        @empty
                            <div class="rounded-xl bg-red-500/10 px-4 py-3 text-sm text-red-200">
                                Chưa bật phương thức thanh toán nào trong cấu hình hệ thống.
                            </div>
                        @endforelse
                    </div>
                </div>

                @auth
                    <button
                        id="btn-dat-ve"
                        class="mt-5 w-full rounded-xl bg-[#d99a32] px-5 py-3 font-black text-[#2b1208] opacity-60 transition hover:bg-[#f4c56a]"
                        disabled
                    >
                        Xác nhận đặt vé
                    </button>
                @else
                    <a href="{{ route('login') }}" class="mt-5 flex w-full items-center justify-center rounded-xl bg-[#d99a32] px-5 py-3 font-black text-[#2b1208] transition hover:bg-[#f4c56a]">
                        Đăng nhập để đặt vé
                    </a>
                @endauth
            </form>
        </aside>

        <section class="rounded-2xl border border-white/10 bg-[#121212] p-6">
            <div class="mx-auto max-w-5xl text-center">
                <div class="mx-auto mb-10 w-4/5">
                    <div class="screen-line"></div>
                    <div class="mt-2 text-xs font-bold uppercase tracking-[0.3em] text-[#f4c56a]">Màn hình</div>
                </div>

                <div class="inline-block space-y-3">
                    @foreach($gheTheoHang as $hang => $cacGhe)
                        <div class="flex items-center justify-center gap-2">
                            <span class="w-8 text-center text-sm font-black text-[#d99a32]">{{ $hang }}</span>
                            @foreach($cacGhe as $ghe)
                                <div class="seat-wrapper relative" data-seat="{{ $ghe['ma_ghe'] }}">
                                    <button
                                        type="button"
                                        class="seat-button {{ $ghe['da_dat'] ? 'booked' : '' }} {{ $ghe['bao_tri'] ? 'maintenance' : '' }}"
                                        style="--seat-color: {{ $ghe['mau_sac'] }}"
                                        data-seat="{{ $ghe['ma_ghe'] }}"
                                        data-price="{{ $ghe['gia'] }}"
                                        data-type="{{ $ghe['loai_ghe'] }}"
                                        @disabled(! $ghe['chon_duoc'])
                                    >
                                        <span>{{ $ghe['ma_ghe'] }}</span>
                                    </button>
                                    <div class="seat-tooltip">
                                        <div class="font-black text-[#f4c56a]">Ghế {{ $ghe['ma_ghe'] }}</div>
                                        <div class="mt-1 text-xs text-gray-300">Loại: {{ $ghe['loai_ghe'] }}</div>
                                        <div class="text-xs text-gray-300">Giá: {{ number_format($ghe['gia'], 0, ',', '.') }}đ</div>
                                        <div class="text-xs {{ $ghe['chon_duoc'] ? 'text-green-300' : 'text-red-300' }}">
                                            {{ $ghe['da_dat'] ? 'Đã đặt' : ($ghe['bao_tri'] ? 'Bảo trì' : 'Còn trống') }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <span class="w-8 text-center text-sm font-black text-[#d99a32]">{{ $hang }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8 flex flex-wrap justify-center gap-5 border-t border-white/10 pt-5 text-sm text-gray-300">
                    <span class="flex items-center gap-2">
                        <span class="inline-block h-5 w-5 rounded bg-[#2a2a2a] border border-white/20"></span>
                        Ghế trống
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="inline-block h-5 w-5 rounded bg-gradient-to-br from-[#f4c56a] to-[#d99a32]"></span>
                        Đang chọn
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="inline-block h-5 w-5 rounded bg-[#0e0e0e] border border-white/10"></span>
                        Đã đặt / bảo trì
                    </span>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const seatButtons = document.querySelectorAll('.seat-button:not(:disabled)');
    const selectedInput = document.getElementById('ghe_duoc_chon');
    const countSeats = document.getElementById('count-seats');
    const totalPrice = document.getElementById('total-price');
    const submitButton = document.getElementById('btn-dat-ve');
    const selectedList = document.getElementById('selected-list');
    let selectedSeats = selectedInput.value
        ? selectedInput.value.split(',').map((seat) => seat.trim()).filter(Boolean)
        : [];

    function money(value) {
        return Number(value || 0).toLocaleString('vi-VN');
    }

    function selectedSeatData() {
        return selectedSeats.map((code) => {
            const button = document.querySelector(`.seat-button[data-seat="${code}"]`);
            return {
                code,
                price: Number(button?.dataset.price || 0),
                type: button?.dataset.type || 'Ghế',
            };
        });
    }

    function render() {
        document.querySelectorAll('.seat-button').forEach((button) => {
            button.classList.toggle('selected', selectedSeats.includes(button.dataset.seat));
        });

        const seats = selectedSeatData();
        const total = seats.reduce((sum, seat) => sum + seat.price, 0);

        selectedInput.value = selectedSeats.join(',');
        countSeats.textContent = selectedSeats.length;
        totalPrice.textContent = money(total);

        if (submitButton) {
            submitButton.disabled = selectedSeats.length === 0;
            submitButton.classList.toggle('opacity-60', selectedSeats.length === 0);
        }

        if (seats.length === 0) {
            selectedList.innerHTML = 'Chưa chọn ghế nào';
            return;
        }

        selectedList.innerHTML = seats.map((seat) => `
            <button type="button" data-remove-seat="${seat.code}" class="rounded-full bg-[#d99a32] px-3 py-1 text-xs font-black text-[#2b1208]">
                ${seat.code} · ${money(seat.price)}đ
            </button>
        `).join('');
    }

    seatButtons.forEach((button) => {
        button.addEventListener('click', function () {
            const seat = button.dataset.seat;

            selectedSeats = selectedSeats.includes(seat)
                ? selectedSeats.filter((item) => item !== seat)
                : [...selectedSeats, seat];

            render();
        });
    });

    selectedList.addEventListener('click', function (event) {
        const button = event.target.closest('[data-remove-seat]');

        if (!button) {
            return;
        }

        selectedSeats = selectedSeats.filter((seat) => seat !== button.dataset.removeSeat);
        render();
    });

    render();
});
</script>
@endsection
