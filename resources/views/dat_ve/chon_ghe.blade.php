@extends('layouts.user')

@section('title', 'Chon ghe')

@push('styles')
<style>
    .seat-button {
        width: 40px;
        height: 40px;
        border-radius: 8px 8px 3px 3px;
        border: 1px solid rgba(255,255,255,.18);
        background: #2a2a2a;
        color: #cfcfcf;
        font-size: 12px;
        font-weight: 800;
        transition: .15s ease;
    }
    .seat-button:not(.booked):hover,
    .seat-button.selected {
        background: #d99a32;
        border-color: #f4c56a;
        color: #2b1208;
    }
    .seat-button.booked {
        background: #111;
        color: #555;
        cursor: not-allowed;
        opacity: .65;
    }
    .screen-line {
        height: 34px;
        border-radius: 50% 50% 0 0;
        background: linear-gradient(180deg, rgba(244,197,106,.9), rgba(217,154,50,.15));
        box-shadow: 0 18px 40px rgba(217,154,50,.25);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-[#080808] px-6 pt-28 pb-12 text-white">
    <div class="mx-auto grid max-w-7xl gap-6 lg:grid-cols-[360px_1fr]">
        <aside class="rounded-2xl border border-white/10 bg-[#121212] p-5">
            <img
                src="{{ $suatChieu->phim->poster ?? 'https://via.placeholder.com/300x450?text=Poster' }}"
                class="h-[420px] w-full rounded-xl object-cover"
                alt="{{ $suatChieu->phim->ten_phim }}"
            >

            <h1 class="mt-5 text-2xl font-black text-[#d99a32]">{{ $suatChieu->phim->ten_phim }}</h1>
            <div class="mt-4 space-y-2 text-sm text-gray-300">
                <p><strong>Rap:</strong> {{ $suatChieu->rapChieuPhim->ten_rap }}</p>
                <p><strong>Phong:</strong> Phong 1</p>
                <p><strong>Suat chieu:</strong> {{ $suatChieu->thoi_gian_chieu->format('H:i d/m/Y') }}</p>
                <p><strong>Gia ve:</strong> {{ number_format($suatChieu->gia_ve, 0, ',', '.') }} VND</p>
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
                        <span>So ghe</span>
                        <strong id="count-seats">0</strong>
                    </div>
                    <div class="mt-2 flex justify-between text-sm">
                        <span>Tong tien</span>
                        <strong class="text-[#f4c56a]"><span id="total-price">0</span> VND</strong>
                    </div>
                </div>

                <button id="btn-dat-ve" class="w-full rounded-xl bg-[#d99a32] px-5 py-3 font-black text-[#2b1208] opacity-60 transition hover:bg-[#f4c56a]" disabled>
                    Dat ve
                </button>
            </form>
        </aside>

        <section class="rounded-2xl border border-white/10 bg-[#121212] p-6">
            <div class="mx-auto max-w-4xl text-center">
                <div class="mx-auto mb-10 w-4/5">
                    <div class="screen-line"></div>
                    <div class="mt-2 text-xs font-bold uppercase tracking-[0.3em] text-[#f4c56a]">Man hinh</div>
                </div>

                <div class="inline-block space-y-2">
                    @foreach($hangGhe as $hang)
                        <div class="flex items-center gap-2">
                            <span class="w-7 text-sm font-black text-gray-400">{{ $hang }}</span>
                            @for($i = 1; $i <= $soCot; $i++)
                                @php
                                    $maGhe = $hang . $i;
                                    $daDat = in_array($maGhe, $gheDaDat, true);
                                @endphp
                                <button type="button" class="seat-button {{ $daDat ? 'booked' : '' }}" data-seat="{{ $maGhe }}" @disabled($daDat)>
                                    {{ $i }}
                                </button>
                            @endfor
                            <span class="w-7 text-sm font-black text-gray-400">{{ $hang }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8 flex flex-wrap justify-center gap-5 border-t border-white/10 pt-5 text-sm text-gray-300">
                    <span><span class="inline-block h-4 w-4 rounded bg-[#2a2a2a] align-middle"></span> Ghe trong</span>
                    <span><span class="inline-block h-4 w-4 rounded bg-[#d99a32] align-middle"></span> Dang chon</span>
                    <span><span class="inline-block h-4 w-4 rounded bg-[#111] align-middle"></span> Da dat</span>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const seats = document.querySelectorAll('.seat-button:not(.booked)');
    const selectedInput = document.getElementById('ghe_duoc_chon');
    const countSeats = document.getElementById('count-seats');
    const totalPrice = document.getElementById('total-price');
    const submitButton = document.getElementById('btn-dat-ve');
    const price = Number({{ (float) $suatChieu->gia_ve }});
    let selectedSeats = selectedInput.value ? selectedInput.value.split(',').map(seat => seat.trim()).filter(Boolean) : [];

    function render() {
        seats.forEach(seat => {
            seat.classList.toggle('selected', selectedSeats.includes(seat.dataset.seat));
        });

        selectedInput.value = selectedSeats.join(',');
        countSeats.textContent = selectedSeats.length;
        totalPrice.textContent = (selectedSeats.length * price).toLocaleString('vi-VN');
        submitButton.disabled = selectedSeats.length === 0;
        submitButton.classList.toggle('opacity-60', selectedSeats.length === 0);
    }

    seats.forEach(seat => {
        seat.addEventListener('click', function () {
            const code = this.dataset.seat;
            selectedSeats = selectedSeats.includes(code)
                ? selectedSeats.filter(item => item !== code)
                : [...selectedSeats, code];
            render();
        });
    });

    render();
});
</script>
@endsection
