@extends('layouts.user')

@section('title', 'Chọn ghế - ' . $suatChieu->phim->ten_phim)

@push('styles')
    <style>
        .dat-ve-page {
            --gold: #d99a32;
            --gold-light: #f4c56a;
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

        .payment-option input:checked+span {
            border-color: rgba(244, 197, 106, .8);
            background: rgba(217, 154, 50, .14);
            color: #f4c56a;
        }
    </style>
@endpush

<!-- cspell:disable -->
@section('content')
    <div class="dat-ve-page min-h-screen bg-[#080808] px-6 pt-28 pb-12" lang="vi" spellcheck="false">
        <div class="mx-auto grid max-w-7xl gap-6 lg:grid-cols-[320px_1fr_370px]">

            {{-- LEFT: film info + poster (vertical) --}}
            <aside class="rounded-2xl border border-white/10 bg-[#121212] p-5">
                <div class="flex flex-col items-start gap-4">
                    <img src="{{ asset('storage/movies/' . $suatChieu->phim->poster) }}"
                        alt="{{ $suatChieu->phim->ten_phim }}" class="w-full rounded-lg object-cover">

                    <div class="w-full rounded-2xl border border-white/10 bg-zinc-900 p-4">
                        <h2 class="text-lg font-black text-yellow-500 mb-2">{{ $suatChieu->phim->ten_phim }}</h2>
                        <div class="text-sm text-gray-300">
                            <div><strong>Rạp:</strong> {{ $suatChieu->rapChieuPhim->ten_rap }}</div>
                            <div><strong>Phòng:</strong> {{ $suatChieu->phongChieu->ten_phong ?? 'Phòng chiếu' }}</div>
                            <div><strong>Suất chiếu:</strong> {{ $suatChieu->thoi_gian_chieu->format('H:i d/m/Y') }}</div>
                            <div><strong>Giá từ:</strong> {{ number_format($suatChieu->gia_ve, 0, ',', '.') }}đ</div>
                        </div>
                    </div>
                </div>
            </aside>

            @php
                // derive seat prices for legend (case-insensitive)
                $flatSeats = collect($gheTheoHang ?? [])
                    ->flatten(1)
                    ->map(function ($s) {
                        $s['loai_ghe'] = mb_strtolower($s['loai_ghe'] ?? '');
                        return $s;
                    });

                $normalSeat = $flatSeats->first(
                    fn($s) => str_contains($s['loai_ghe'] ?? '', 'thường') || $s['loai_ghe'] === 'normal',
                );
                $vipSeat = $flatSeats->first(fn($s) => str_contains($s['loai_ghe'] ?? '', 'vip'));
                $doubleSeat = $flatSeats->first(
                    fn($s) => str_contains($s['loai_ghe'] ?? '', 'couple') ||
                        str_contains($s['loai_ghe'] ?? '', 'doi') ||
                        str_contains($s['loai_ghe'] ?? '', 'đôi') ||
                        str_contains($s['loai_ghe'] ?? '', 'double'),
                );

                $hasNormal = !is_null($normalSeat);
                $hasVip = !is_null($vipSeat);
                $hasDouble = !is_null($doubleSeat);

                $normalPrice = $normalSeat['gia'] ?? ($suatChieu->gia_ve ?? 0);
                $vipPrice = $vipSeat['gia'] ?? $normalPrice;
                $doublePrice = ($doubleSeat['gia'] ?? $suatChieu->gia_ve * 2) + ($doubleSeat['phu_thu'] ?? 0);

                // colors from seat data (fallbacks)
                $normalColor = $normalSeat['mau_sac'] ?? '#2a2a2a';
                $vipColor = $vipSeat['mau_sac'] ?? '#b91c1c';
                $doubleColor = $doubleSeat['mau_sac'] ?? '#db2777';
            @endphp

            <section class="rounded-2xl border border-white/10 bg-[#121212] p-6">
                {{-- center: countdown + screen + seats --}}

                {{-- COUNTDOWN: thời gian giữ ghế (7 phút) --}}
                <div class="mb-6 flex items-center justify-between rounded-2xl border border-white/10 bg-zinc-900 p-4">

                    <div>
                        <p class="text-[10px] uppercase tracking-[0.3em] text-gray-500">Thời gian giữ ghế</p>
                        <p class="text-sm text-gray-400 mt-1">Ghế sẽ tự động huỷ khi hết thời gian</p>
                    </div>

                    <div class="flex items-center gap-3 rounded-xl border border-white/10 bg-black/20 px-4 py-2">
                        <div
                            class="w-11 h-11 rounded-full bg-black/40 border border-white/10 flex items-center justify-center text-lg">
                            ⏰</div>
                        <div id="countdown" class="text-3xl font-black tracking-[0.15em] text-red-400">07:00</div>
                    </div>

                </div>

                <div class="mx-auto max-w-5xl text-center">
                    <div class="mx-auto mb-10 w-4/5">
                        <div class="screen-line"></div>
                        <div class="mt-2 text-xs font-bold uppercase tracking-[0.3em] text-[#f4c56a]">
                            Màn hình
                        </div>
                    </div>

                    <div class="inline-block space-y-3">
                        @php
                            $loaiCouple = ['couple', 'đôi', 'doi'];
                        @endphp

                        @foreach ($gheTheoHang ?? [] as $hang => $cacGhe)
                            <div class="seat-row flex items-center justify-center flex-nowrap gap-2">
                                <span class="row-label w-8 text-center text-sm font-black text-[#d99a32]">
                                    {{ $hang }}
                                </span>
                                @php
                                    $merged = [];
                                    $skip = false;

                                    for ($i = 0; $i < count($cacGhe); $i++) {
                                        if ($skip) {
                                            $skip = false;
                                            continue;
                                        }

                                        $ghe = $cacGhe[$i];
                                        $type = strtolower($ghe['loai_ghe'] ?? '');

                                        $isCouple =
                                            str_contains($type, 'couple') ||
                                            str_contains($type, 'đôi') ||
                                            str_contains($type, 'doi');

                                        if (
                                            $isCouple &&
                                            isset($cacGhe[$i + 1]) &&
                                            strtolower($cacGhe[$i + 1]['loai_ghe']) == $type
                                        ) {
                                            $ghe2 = $cacGhe[$i + 1];

                                            $merged[] = [
                                                'ma_ghe' => $ghe['ma_ghe'] . ' | ' . $ghe2['ma_ghe'],
                                                'seat_codes' => $ghe['ma_ghe'] . ',' . $ghe2['ma_ghe'],
                                                'loai_ghe' => $ghe['loai_ghe'],
                                                'gia' => $ghe['gia'] + $ghe['phu_thu'],
                                                'mau_sac' => $ghe['mau_sac'],
                                                'da_dat' => $ghe['da_dat'] || $ghe2['da_dat'],
                                                'bao_tri' => $ghe['bao_tri'] || $ghe2['bao_tri'],
                                                'chon_duoc' => $ghe['chon_duoc'] && $ghe2['chon_duoc'],
                                                'is_couple' => true,
                                            ];

                                            $skip = true;
                                        } else {
                                            $merged[] = [
                                                'ma_ghe' => $ghe['ma_ghe'],
                                                'seat_codes' => $ghe['ma_ghe'],
                                                'loai_ghe' => $ghe['loai_ghe'],
                                                'gia' => $ghe['gia'],
                                                'mau_sac' => $ghe['mau_sac'],
                                                'da_dat' => $ghe['da_dat'],
                                                'bao_tri' => $ghe['bao_tri'],
                                                'chon_duoc' => $ghe['chon_duoc'],
                                                'is_couple' => false,
                                            ];
                                        }
                                    }
                                @endphp

                                @foreach ($merged as $ghe)
                                    @php
                                        $disabled = !($ghe['chon_duoc'] ?? false);
                                        $isBooked = $ghe['da_dat'];
                                        $isMaintenance = $ghe['bao_tri'];

                                        $seatCodes = $ghe['seat_codes'];
                                        $seatLabel = $ghe['ma_ghe'];

                                        $isCouple = $ghe['is_couple'];

                                        $codes = explode(',', $seatCodes);
                                    @endphp

                                    <div class="seat-wrapper relative">

                                        @foreach ($codes as $seat)
                                            <input type="checkbox" class="js-seat sr-only" value="{{ trim($seat) }}"
                                                {{ $disabled ? 'disabled' : '' }}>
                                        @endforeach

                                        <button type="button"
                                            class="seat-button
                {{ $isCouple ? 'seat-button--couple' : '' }}
                {{ $isBooked ? 'booked' : '' }}
                {{ $isMaintenance ? 'maintenance' : '' }}"
                                            style="--seat-color: {{ $ghe['mau_sac'] }}" data-seat="{{ $seatLabel }}"
                                            data-seat-codes="{{ $seatCodes }}" data-price="{{ $ghe['gia'] }}"
                                            data-type="{{ $ghe['loai_ghe'] }}" @disabled($disabled)>

                                            @if ($isCouple)
                                                <div class="seat-couple-label">
                                                    {{ trim($codes[0]) }} | {{ trim($codes[1]) }}
                                                </div>
                                            @else
                                                <span>{{ $seatLabel }}</span>
                                            @endif

                                        </button>

                                    </div>
                                @endforeach

                                <span class="row-label w-8 text-center text-sm font-black text-[#d99a32]">
                                    {{ $hang }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <div
                        class="mt-8 flex flex-wrap justify-center gap-5 border-t border-white/10 pt-5 text-sm text-gray-300">
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

            <aside class="rounded-2xl border border-white/10 bg-[#121212] p-5">
                {{-- poster removed from sidebar (moved to left) --}}

                {{-- Legend: seat types --}}
                <div class="bg-zinc-900 border border-white/40 rounded-2xl p-4 mb-4">
                    <p class="text-gray-500 text-[10px] uppercase tracking-widest font-bold mb-3">Loại ghế</p>
                    <div class="space-y-2 text-[13px] text-gray-300">

                        @if ($hasNormal)
                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-2">
                                    <span
                                        class="inline-block h-3.5 w-3.5 rounded border border-white/20 bg-[#2a2a2a]"></span>
                                    Thường
                                </span>
                                <b>{{ number_format($normalPrice) }}đ</b>
                            </div>
                        @endif

                        @if ($hasVip)
                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-2">
                                    <span
                                        class="inline-block h-3.5 w-3.5 rounded bg-gradient-to-br from-[#f4c56a] to-[#d99a32]"></span>
                                    VIP
                                </span>
                                <b>{{ number_format($vipPrice) }}đ</b>
                            </div>
                        @endif

                        @if ($hasDouble)
                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-2">
                                    <span
                                        class="inline-block h-3.5 w-3.5 rounded border border-white/10 bg-[#0e0e0e]"></span>
                                    Đôi
                                </span>
                                <b>{{ number_format($doublePrice) }}đ/cặp</b>
                            </div>
                        @endif

                    </div>
                </div>

                {{-- Selected seats info --}}
                <div class="bg-zinc-900 border border-white/40 rounded-2xl p-4 mb-4">
                    <p class="text-gray-500 text-[10px] uppercase tracking-widest font-bold mb-3">Vé đã chọn</p>

                    <div class="flex justify-between border-b border-white/10 pb-2 text-xs">
                        <span class="text-gray-500">Số ghế</span>
                        <strong id="seatCount">0 ghế</strong>
                    </div>

                    <div class="flex justify-between gap-2 pt-2 text-xs">
                        <span class="text-gray-500">Vị trí</span>
                        <strong id="seatLabels" class="text-right text-gray-400 break-words">—</strong>
                    </div>
                </div>

                {{-- Total price --}}
                <div class="rounded-2xl border border-yellow-500/40 bg-yellow-500/10 p-4 text-center mb-4">
                    <p class="text-gray-500 text-[10px] uppercase tracking-widest font-bold mb-1">Tổng thanh toán</p>
                    <div id="totalPrice" class="text-3xl font-black text-yellow-500">0đ</div>
                </div>

                {{-- Buttons --}}
                <button type="button" id="btnFood" disabled
                    class="w-full rounded-xl bg-yellow-500 py-2.5 text-sm text-black font-black opacity-30 cursor-not-allowed transition mb-2">
                    Tiếp tục chọn đồ ăn
                </button>

                <a href="{{ route('dat_ve.chon_phim') }}"
                    class="block text-center rounded-xl border border-white/30 bg-white/5 py-2.5 text-sm text-gray-400 hover:bg-white/10 transition">
                    ← Quay lại
                </a>

            </aside>

        </div>
    </div>
@endsection
<!-- cspell:enable -->

@section('scripts')
    <style>
        /* Ghế thường */
        .seat-button {
            width: 46px;
            height: 42px;
            border-radius: 9px 9px 5px 5px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 11px;
            font-weight: 700;
            flex: 0 0 46px;
        }

        /* Ghế đôi */
        .seat-button--couple {
            width: 100px;
            /* 46 + 46 + gap(8px) */
            height: 42px;
            flex: 0 0 100px;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .seat-button--couple .seat-couple-label {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            gap: 8px;
            font-size: 11px;
            font-weight: 700;
        }

        .seat-button--couple .seat-couple-sep {
            opacity: .6;
        }

        /* Hàng ghế */
        .seat-row {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: nowrap;
            gap: 8px;
            margin-bottom: 12px;
        }

        .row-label {
            width: 28px;
            text-align: center;
            font-weight: 700;
            color: #d99a32;
            flex-shrink: 0;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const seatButtons = document.querySelectorAll('.seat-button');
            const btnFood = document.getElementById('btnFood');
            const selectedList = document.getElementById('selected-list');
            const seatLabels = document.getElementById('seatLabels');
            const seatCountEl = document.getElementById('seatCount');
            const totalPriceEl = document.getElementById('totalPrice');

            let selectedSeats = [];
            // Lấy ghế từ URL (?ghe=A1,A2,B3)
            const params = new URLSearchParams(window.location.search);
            const seatParam = params.get('ghe');

            if (seatParam) {
                selectedSeats = seatParam
                    .split(',')
                    .map(s => s.trim())
                    .filter(Boolean);
            }

            const showtimeId = '{{ $suatChieu->id }}';

            function money(v) {
                return Number(v || 0).toLocaleString('vi-VN') + 'đ';
            }

            function getSeatsFromButton(btn) {
                return (btn.dataset.seatCodes || btn.dataset.seat)
                    .split(',')
                    .map(s => s.trim())
                    .filter(Boolean);
            }

            function updateUI() {

                // update selected state UI
                seatButtons.forEach(btn => {
                    const codes = getSeatsFromButton(btn);
                    const isSelected = codes.some(c => selectedSeats.includes(c));
                    btn.classList.toggle('selected', isSelected);
                });

                if (seatCountEl) {
                    seatCountEl.textContent = `${selectedSeats.length} ghế`;
                }

                // labels
                if (seatLabels) {
                    seatLabels.textContent = selectedSeats.join(', ') || '—';
                }

                // list
                if (selectedList) {
                    selectedList.innerHTML = selectedSeats.length ?
                        selectedSeats.map(s => `
                    <button type="button"
                        data-remove-seat="${s}"
                        class="rounded-full bg-[#d99a32] px-3 py-1 text-xs font-black text-[#2b1208]">
                        ${s}
                    </button>
                `).join('') :
                        'Chưa chọn ghế nào';
                }

                // total
                if (totalPriceEl) {
                    const total = Array.from(seatButtons).reduce((sum, btn) => {
                        const codes = getSeatsFromButton(btn);
                        const isSelected = codes.some(code => selectedSeats.includes(code));
                        return isSelected ? sum + Number(btn.dataset.price || 0) : sum;
                    }, 0);

                    totalPriceEl.textContent = money(total);
                }

                // button FOOD logic (QUAN TRỌNG)
                if (btnFood) {
                    const hasSeat = selectedSeats.length > 0;

                    btnFood.disabled = !hasSeat;
                    btnFood.classList.toggle('opacity-30', !hasSeat);
                    btnFood.classList.toggle('cursor-not-allowed', !hasSeat);
                }
            }

            // CLICK GHẾ
            seatButtons.forEach(btn => {
                btn.addEventListener('click', function() {

                    const codes = getSeatsFromButton(btn);

                    codes.forEach(code => {
                        if (selectedSeats.includes(code)) {
                            selectedSeats = selectedSeats.filter(x => x !== code);
                        } else {
                            selectedSeats.push(code);
                        }
                    });

                    updateUI();
                });
            });

            // CLICK FOOD → FIX CHẮC CHẮN CHUYỂN TRANG
            if (btnFood) {
                btnFood.addEventListener('click', function(e) {
                    e.preventDefault();

                    if (selectedSeats.length === 0) return;

                    const seatsQuery = encodeURIComponent(selectedSeats.join(','));

                    const url =
                        `{{ route('dat_ve.chon_do_an', ['suat_chieu_id' => $suatChieu->id]) }}?ghe=${seatsQuery}`;
                    window.location.href = url;
                });
            }

            // REMOVE SEAT
            if (selectedList) {
                selectedList.addEventListener('click', function(e) {
                    const btn = e.target.closest('[data-remove-seat]');
                    if (!btn) return;

                    const seat = btn.dataset.removeSeat;
                    selectedSeats = selectedSeats.filter(s => s !== seat);

                    updateUI();
                });
            }

            updateUI();
        });
    </script>
    <script>
        (function() {
            const countdownEl = document.getElementById('countdown');
            if (!countdownEl) return;

            const storageKey = 'booking_deadline_{{ $suatChieu->id }}';

            function getStoredDeadline() {
                try {
                    return Number(localStorage.getItem(storageKey)) || null;
                } catch (e) {
                    return null;
                }
            }

            function setStoredDeadline(deadline) {
                try {
                    localStorage.setItem(storageKey, String(deadline));
                } catch (e) {}
            }

            function clearStoredDeadline() {
                try {
                    localStorage.removeItem(storageKey);
                } catch (e) {}
            }

            let deadline = getStoredDeadline();
            if (!deadline || deadline <= Date.now()) {
                deadline = Date.now() + 7 * 60 * 1000;
            }
            setStoredDeadline(deadline);

            function updateCountdown() {
                const remaining = deadline - Date.now();

                if (remaining <= 0) {
                    clearStoredDeadline();
                    countdownEl.innerText = '00:00';
                    countdownEl.classList.add('animate-pulse');

                    const checked = Array.from(document.querySelectorAll('.js-seat:checked')).map(i => i.value);
                    const showtimeId =
                        "{{ $suatChieu->id ?? ($showtime->id ?? (request()->route('suat_chieu') ?? request()->route('suatChieu'))) }}";
                    checked.forEach(function(seatId) {
                        try {
                            navigator.sendBeacon("{{ url('/dat-ve/seat-locks') }}/" + showtimeId + '/' +
                                seatId + '?_method=DELETE&token={{ csrf_token() }}');
                        } catch (e) {}
                    });

                    window.location.href = '/';
                    return;
                }

                const minutes = Math.floor(remaining / 60000);
                const seconds = Math.floor((remaining % 60000) / 1000);
                countdownEl.innerText = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');

                if (remaining <= 60 * 1000) {
                    countdownEl.classList.add('animate-pulse');
                }
            }

            updateCountdown();
            setInterval(updateCountdown, 1000);
        })();
    </script>
@endsection
