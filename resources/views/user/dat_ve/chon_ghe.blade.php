@extends('layouts.user')

@section('title', 'Chọn ghế - ' . $suatChieu->phim->ten_phim)

@section('content')
    {{-- CSS Ghi đè màu ghế đang chọn & Tối ưu UX --}}
    <style>
        .seat-button.selected,
        button.seat-button.selected {
            background: #ffffff !important;
            background-color: #ffffff !important;
            color: #000000 !important;
            border-color: #ffffff !important;
            box-shadow: 0 0 18px rgba(255, 255, 255, 0.95) !important;
        }

        .seat-button.selected span,
        .seat-button.selected .seat-couple-label,
        .seat-button.selected * {
            color: #000000 !important;
            font-weight: 900 !important;
        }

        .seat-swatch.is-selected {
            background: #ffffff !important;
            background-color: #ffffff !important;
            border: 1px solid #ffffff !important;
            box-shadow: 0 0 8px rgba(255, 255, 255, 0.8) !important;
        }

        .animate-pulse {
            animation: pulse 1s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }

        /* UX: Khóa nút bấm khi chọn ghế không hợp lệ */
        .booking-seat-primary-cta:disabled,
        .booking-seat-primary-cta[disabled] {
            background: #334155 !important;
            color: #94a3b8 !important;
            cursor: not-allowed !important;
            opacity: 0.55 !important;
            box-shadow: none !important;
            transform: none !important;
            border: 1px solid #475569 !important;
        }

        /* UX: Hiệu ứng thông báo lỗi mượt mà */
        .booking-seat-alert {
            background: rgba(239, 68, 68, 0.15) !important;
            border: 1px solid #ef4444 !important;
            color: #f87171 !important;
            padding: 14px 18px !important;
            border-radius: 12px !important;
            margin: 15px 0 !important;
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            font-weight: 600 !important;
            font-size: 14px !important;
            position: relative !important;
            z-index: 99 !important;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15) !important;
            animation: fadeInAlert 0.25s ease-in-out;
        }

        @keyframes fadeInAlert {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    @php
        $poster = $suatChieu->phim->poster ?? null;
        $posterUrl = $poster
            ? (\Illuminate\Support\Str::startsWith($poster, ['http://', 'https://'])
                ? $poster
                : asset('storage/movies/' . $poster))
            : 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=1200&auto=format&fit=crop';

        $flatSeats = collect($gheTheoHang ?? [])->flatten(1)->map(function ($seat) {
            $seat['loai_ghe_norm'] = mb_strtolower($seat['loai_ghe'] ?? '');
            $seat['is_couple_seat'] = ($seat['la_couple'] ?? false) ||
                str_contains($seat['loai_ghe_norm'], 'couple') ||
                str_contains($seat['loai_ghe_norm'], 'đôi') ||
                str_contains($seat['loai_ghe_norm'], 'doi') ||
                str_contains($seat['loai_ghe_norm'], 'double');
            return $seat;
        });

        $normalSeat = $flatSeats->first(fn($seat) => str_contains($seat['loai_ghe_norm'] ?? '', 'thường') || ($seat['loai_ghe_norm'] ?? '') === 'normal');
        $vipSeat = $flatSeats->first(fn($seat) => str_contains($seat['loai_ghe_norm'] ?? '', 'vip'));
        $doubleSeat = $flatSeats->first(fn($seat) => $seat['is_couple_seat']);

        $hasNormal = !is_null($normalSeat);
        $hasVip = !is_null($vipSeat);
        $hasDouble = !is_null($doubleSeat);

        $normalPrice = (int) round($normalSeat['gia'] ?? ($suatChieu->gia_ve_cuoi_cung ?? 0));
        $vipPrice = (int) round($vipSeat['gia'] ?? $normalPrice);
        $doublePrice = (int) round($doubleSeat['gia'] ?? (($suatChieu->gia_ve_cuoi_cung ?? 0) * 2));

        $totalSeats = $flatSeats->count();
        $availableSeats = $flatSeats->where('chon_duoc', true)->count();
        $unavailableSeats = $flatSeats->filter(fn($seat) => ($seat['da_dat'] ?? false) || ($seat['bao_tri'] ?? false))->count();
        $weekdayLabels = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
        $showDate = $suatChieu->thoi_gian_chieu;
        $showDateLabel = ($weekdayLabels[$showDate->dayOfWeek] ?? $showDate->format('D')) . ', ' . $showDate->format('d/m/Y');
    @endphp

    <div class="dat-ve-page booking-seat-page" lang="vi" spellcheck="false">
        <section class="booking-seat-hero">
            <div class="booking-flow-hero-copy">
                <span class="booking-eyebrow">
                    <i class="fa-solid fa-couch"></i> Bước 2 trong 4
                </span>
                <h1>Chọn ghế đẹp cho suất chiếu của bạn.</h1>
                <p>
                    <strong>{{ $suatChieu->phim->ten_phim }}</strong> chiếu lúc
                    <strong>{{ $suatChieu->thoi_gian_chieu->format('H:i') }}</strong> tại
                    <strong>{{ $suatChieu->rapChieuPhim->ten_rap }}</strong>.
                    Ghế được giữ trong 7 phút sau khi chọn.
                </p>
                <div class="booking-seat-mini-stats" aria-label="Tổng quan ghế">
                    <div>
                        <strong>{{ $availableSeats }}</strong>
                        <span>Ghế trống</span>
                    </div>
                    <div>
                        <strong>{{ $unavailableSeats }}</strong>
                        <span>Đã đặt / giữ</span>
                    </div>
                    <div>
                        <strong>{{ number_format((int)round($suatChieu->gia_ve_cuoi_cung), 0, ',', '.') }}đ</strong>
                        <span>Giá từ</span>
                    </div>
                </div>
            </div>

            <div class="booking-stepper" aria-label="Tiến trình đặt vé">
                <div class="booking-step is-done">
                    <span><i class="fa-solid fa-check"></i></span>
                    <strong>Chọn phim</strong>
                </div>
                <div class="booking-step is-active">
                    <span>2</span>
                    <strong>Chọn ghế</strong>
                </div>
                <div class="booking-step">
                    <span>3</span>
                    <strong>Đồ ăn</strong>
                </div>
                <div class="booking-step">
                    <span>4</span>
                    <strong>Thanh toán</strong>
                </div>
            </div>
        </section>

        <div class="booking-seat-layout">
            <aside class="booking-seat-movie-card">
                <div class="booking-seat-poster-wrap">
                    <img src="{{ $posterUrl }}" alt="{{ $suatChieu->phim->ten_phim }}">
                    <div class="booking-seat-poster-overlay">
                        <span>CineHome</span>
                        <strong>{{ $suatChieu->phim->ten_phim }}</strong>
                    </div>
                </div>

                <div class="booking-seat-info-card">
                    <h2>{{ $suatChieu->phim->ten_phim }}</h2>
                    <dl>
                        <div>
                            <dt><i class="fa-solid fa-location-dot"></i> Rạp</dt>
                            <dd>{{ $suatChieu->rapChieuPhim->ten_rap }}</dd>
                        </div>
                        <div>
                            <dt><i class="fa-solid fa-door-open"></i> Phòng</dt>
                            <dd>{{ $suatChieu->phongChieu->ten_phong ?? 'Phòng chiếu' }}</dd>
                        </div>
                        <div>
                            <dt><i class="fa-solid fa-calendar-days"></i> Ngày chiếu</dt>
                            <dd>{{ $showDateLabel }}</dd>
                        </div>
                        <div>
                            <dt><i class="fa-solid fa-clock"></i> Giờ chiếu</dt>
                            <dd>{{ $suatChieu->thoi_gian_chieu->format('H:i') }}</dd>
                        </div>
                    </dl>
                </div>

                <a href="{{ route('dat_ve.chon_phim') }}" class="booking-seat-back-link">
                    <i class="fa-solid fa-arrow-left"></i>
                    Đổi phim hoặc suất chiếu
                </a>
            </aside>

            <section class="booking-seat-map-panel" aria-label="Sơ đồ chọn ghế">
                @if (isset($pendingTicket) && $pendingTicket && !$pendingTicket->isExpired())
                    <div class="booking-pending-alert" style="background: rgba(234, 179, 8, 0.12) !important; border: 1px solid #eab308 !important; color: #fef08a !important; padding: 16px 20px !important; border-radius: 16px !important; margin-bottom: 20px !important; display: flex !important; justify-content: space-between !important; align-items: center !important; gap: 15px !important; flex-wrap: wrap !important; z-index: 99 !important; box-shadow: 0 10px 25px -5px rgba(234, 179, 8, 0.2);">
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <i class="fa-solid fa-clock-rotate-left" style="color: #facc15 !important; font-size: 22px !important;"></i>
                            <div>
                                <strong style="display: block; color: #fff; font-size: 15px; font-weight: 700; margin-bottom: 2px;">Bạn đang có đơn hàng chờ thanh toán (Mã vé: {{ $pendingTicket->ma_ve }})</strong>
                                <span style="font-size: 13px; color: #cbd5e1;">Ghế đang giữ: <b style="color: #facc15;">{{ $pendingTicket->ma_ghe }}</b></span>
                            </div>
                        </div>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <a href="{{ route('dat_ve.chon_do_an', ['suat_chieu_id' => $suatChieu->id, 'pending_ticket_id' => $pendingTicket->id]) }}" class="js-proceed-link" style="background: #eab308; color: #000; padding: 9px 18px; border-radius: 10px; font-weight: 800; text-decoration: none; font-size: 13px; transition: 0.2s;">
                                Tiếp tục thanh toán <i class="fa-solid fa-arrow-right" style="margin-left: 4px;"></i>
                            </a>
                            <a href="{{ route('dat_ve.chon_ghe', ['movie' => $suatChieu->id, 'reset' => 1]) }}" style="background: rgba(239, 68, 68, 0.2); color: #f87171; padding: 9px 16px; border-radius: 10px; font-weight: 700; text-decoration: none; font-size: 13px; border: 1px solid rgba(239, 68, 68, 0.4);" onclick="return confirm('Bạn có chắc muốn hủy đơn chờ này để chọn lại ghế mới?')">
                                Hủy & Chọn lại
                            </a>
                        </div>
                    </div>
                @endif

                <div class="booking-seat-toolbar">
                    <div>
                        <span class="booking-eyebrow">
                            <i class="fa-solid fa-ticket"></i> Sơ đồ phòng chiếu
                        </span>
                        <h2>Chọn vị trí yêu thích</h2>
                        <p>{{ $availableSeats }} ghế còn trống / {{ $totalSeats }} ghế. Tối đa 8 ghế mỗi lần đặt.</p>
                    </div>

                    <div class="booking-seat-timer" aria-live="polite">
                        <span><i class="fa-regular fa-clock"></i> Thời gian giữ ghế</span>
                        <strong id="countdown">07:00</strong>
                    </div>
                </div>

                @if (session('error'))
                    <div class="booking-seat-alert">
                        <i class="fa-solid fa-circle-exclamation" style="color: #ef4444 !important; font-size: 18px !important;"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <div class="booking-theater">
                    <div class="booking-screen-wrap" aria-hidden="true">
                        <div class="screen-line"></div>
                        <span>Màn hình</span>
                    </div>

                    <div class="booking-seat-scroll">
                        <div class="booking-seat-grid">
                            @foreach ($gheTheoHang ?? [] as $hang => $cacGhe)
                                <div class="seat-row">
                                    <span class="row-label">{{ $hang }}</span>

                                    @php
                                        usort($cacGhe, function ($a, $b) {
                                            return strnatcmp($a['ma_ghe'], $b['ma_ghe']);
                                        });

                                        $merged = [];
                                        $skip = false;
                                        $totalRowSeats = count($cacGhe);

                                        for ($i = 0; $i < $totalRowSeats; $i++) {
                                            if ($skip) {
                                                $skip = false;
                                                continue;
                                            }

                                            $ghe = $cacGhe[$i];
                                            $type = mb_strtolower($ghe['loai_ghe'] ?? '');
                                            $isCouple = ($ghe['la_couple'] ?? false) ||
                                                        str_contains($type, 'couple') ||
                                                        str_contains($type, 'đôi') ||
                                                        str_contains($type, 'doi') ||
                                                        str_contains($type, 'double');

                                            if ($isCouple && isset($cacGhe[$i + 1])) {
                                                $ghe2 = $cacGhe[$i + 1];
                                                $type2 = mb_strtolower($ghe2['loai_ghe'] ?? '');
                                                $isCouple2 = ($ghe2['la_couple'] ?? false) ||
                                                             str_contains($type2, 'couple') ||
                                                             str_contains($type2, 'đôi') ||
                                                             str_contains($type2, 'doi') ||
                                                             str_contains($type2, 'double');

                                                if ($isCouple2) {
                                                    $merged[] = [
                                                        'ma_ghe' => $ghe['ma_ghe'] . ' | ' . $ghe2['ma_ghe'],
                                                        'seat_codes' => $ghe['ma_ghe'] . ',' . $ghe2['ma_ghe'],
                                                        'loai_ghe' => $ghe['loai_ghe'],
                                                        'gia' => (int) round($ghe['gia']),
                                                        'mau_sac' => $ghe['mau_sac'],
                                                        'da_dat' => ($ghe['da_dat'] ?? false) || ($ghe2['da_dat'] ?? false),
                                                        'bao_tri' => ($ghe['bao_tri'] ?? false) || ($ghe2['bao_tri'] ?? false),
                                                        'chon_duoc' => ($ghe['chon_duoc'] ?? true) && ($ghe2['chon_duoc'] ?? true),
                                                        'is_couple' => true,
                                                    ];
                                                    $skip = true;
                                                    continue;
                                                }
                                            }

                                            $merged[] = [
                                                'ma_ghe' => $ghe['ma_ghe'],
                                                'seat_codes' => $ghe['ma_ghe'],
                                                'loai_ghe' => $ghe['loai_ghe'],
                                                'gia' => (int) round($ghe['gia']),
                                                'mau_sac' => $ghe['mau_sac'],
                                                'da_dat' => $ghe['da_dat'] ?? false,
                                                'bao_tri' => $ghe['bao_tri'] ?? false,
                                                'chon_duoc' => $ghe['chon_duoc'] ?? true,
                                                'is_couple' => $isCouple,
                                            ];
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
                                            $typeText = $ghe['loai_ghe'] ?? 'Ghế';
                                            $giaVeGhe = (int) round($ghe['gia']);
                                        @endphp

                                        <div class="seat-wrapper {{ $isCouple ? 'seat-wrapper--couple' : '' }}">
                                            @foreach ($codes as $seat)
                                                <input type="checkbox" class="js-seat sr-only" value="{{ trim($seat) }}" {{ $disabled ? 'disabled' : '' }}>
                                            @endforeach

                                            <button
                                                type="button"
                                                class="seat-button {{ $isCouple ? 'seat-button--couple' : '' }} {{ $isBooked ? 'booked' : '' }} {{ $isMaintenance ? 'maintenance' : '' }}"
                                                style="--seat-color: {{ $ghe['mau_sac'] }}; @if($isCouple) width: 84px; max-width: 84px; @endif"
                                                data-seat="{{ $seatLabel }}"
                                                data-seat-codes="{{ $seatCodes }}"
                                                data-price="{{ $giaVeGhe }}"
                                                data-type="{{ $typeText }}"
                                                data-static-disabled="{{ $disabled ? '1' : '0' }}"
                                                aria-label="Ghế {{ $seatLabel }} - {{ $typeText }} - {{ number_format($giaVeGhe, 0, ',', '.') }}đ"
                                                aria-pressed="false"
                                                @disabled($disabled)
                                            >
                                                @if ($isCouple)
                                                    <span class="seat-couple-label">{{ trim($codes[0]) }} | {{ trim($codes[1] ?? '') }}</span>
                                                @else
                                                    <span>{{ $seatLabel }}</span>
                                                @endif
                                            </button>

                                            <div class="seat-tooltip">
                                                <strong>{{ $seatLabel }}</strong>
                                                <span>{{ $typeText }}</span>
                                                <small>{{ number_format($giaVeGhe, 0, ',', '.') }}đ</small>
                                            </div>
                                        </div>
                                    @endforeach

                                    <span class="row-label">{{ $hang }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="booking-seat-legend" aria-label="Chú thích ghế">
                        <span><i class="seat-swatch is-empty"></i> Ghế trống</span>
                        <span><i class="seat-swatch is-vip"></i> Ghế VIP</span>
                        <span><i class="seat-swatch is-selected"></i> Đang chọn</span>
                        <span><i class="seat-swatch is-locked"></i> Đã đặt / đang giữ</span>
                    </div>
                </div>
            </section>

            <aside class="booking-seat-order-card" aria-label="Tóm tắt vé">
                <div class="booking-order-card-head">
                    <span>Tóm tắt đặt vé</span>
                    <h2>Ghế của bạn</h2>
                </div>

                <div class="booking-order-section">
                    <h3>Loại ghế</h3>
                    <div class="booking-seat-price-list">
                        @if ($hasNormal)
                            <div>
                                <span><i class="seat-swatch is-empty"></i> Thường</span>
                                <strong>{{ number_format($normalPrice, 0, ',', '.') }}đ</strong>
                            </div>
                        @endif
                        @if ($hasVip)
                            <div>
                                <span><i class="seat-swatch is-vip"></i> VIP</span>
                                <strong>{{ number_format($vipPrice, 0, ',', '.') }}đ</strong>
                            </div>
                        @endif
                        @if ($hasDouble)
                            <div>
                                <span><i class="seat-swatch is-couple"></i> Đôi</span>
                                <strong>{{ number_format($doublePrice, 0, ',', '.') }}đ/cặp</strong>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="booking-order-section">
                    <h3>Vé đã chọn</h3>
                    <div class="booking-order-line">
                        <span>Số ghế</span>
                        <strong id="seatCount">0 ghế</strong>
                    </div>
                    <div class="booking-order-line">
                        <span>Vị trí</span>
                        <strong id="seatLabels">—</strong>
                    </div>
                    <div id="selected-list" class="booking-selected-list">
                        <span class="booking-seat-empty-selection">Chưa chọn ghế nào</span>
                    </div>
                </div>

                <div class="booking-seat-total-card">
                    <span>Tổng thanh toán</span>
                    <strong id="totalPrice">0đ</strong>
                    <small>Chưa bao gồm đồ ăn và voucher</small>
                </div>

                <button type="button" id="btnFood" disabled class="booking-seat-primary-cta">
                    Tiếp tục chọn đồ ăn
                    <i class="fa-solid fa-arrow-right"></i>
                </button>

                <button type="button" id="btnResetSeats" class="booking-seat-secondary-link" disabled>
                    <i class="fa-solid fa-rotate-left"></i>
                    Chọn lại ghế
                </button>

                <a href="{{ route('home') }}" class="booking-seat-secondary-link">
                    <i class="fa-solid fa-house"></i>
                    Về trang chủ
                </a>
            </aside>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const csrf = "{{ csrf_token() }}";
        const showtimeId = "{{ $suatChieu->id }}";
        const pendingTicketId = "{{ $pendingTicketId ?? '' }}";
        const pendingDeadline = "{{ $pendingDeadline ?? '' }}";
        const initialSelectedSeats = @json($selectedSeats->values()->all());

        document.addEventListener("DOMContentLoaded", function() {
            const maxSeatCount = 8;
            const seatButtons = Array.from(document.querySelectorAll(".seat-button"));
            const btnFood = document.getElementById("btnFood");
            const btnResetSeats = document.getElementById("btnResetSeats");
            const seatLabels = document.getElementById("seatLabels");
            const seatCountEl = document.getElementById("seatCount");
            const totalPriceEl = document.getElementById("totalPrice");
            const selectedList = document.getElementById("selected-list");
            const countdownEl = document.getElementById("countdown");

            const storageKey = "booking_deadline_" + showtimeId;
            let countdownTimerInterval = null;
            let selectedSeats = [];
            let lockedSeats = new Set();
            let isProceedingToNextStep = false;
            let isFetchingLocks = false;

            const params = new URLSearchParams(window.location.search);
            const seatParam = params.get("ghe");
            if (seatParam) {
                selectedSeats = seatParam.split(",").map(normalizeSeat).filter(Boolean);
            } else if (Array.isArray(initialSelectedSeats) && initialSelectedSeats.length > 0) {
                selectedSeats = initialSelectedSeats.map(normalizeSeat).filter(Boolean);
            }

            function normalizeSeat(seat) {
                return String(seat || "").trim().toUpperCase();
            }

            function money(value) {
                return Math.round(Number(value || 0)).toLocaleString("vi-VN") + "đ";
            }

            function getSeatsFromButton(btn) {
                return (btn.dataset.seatCodes || btn.dataset.seat)
                    .split(",")
                    .map(normalizeSeat)
                    .filter(Boolean);
            }

            function buttonIsSelected(btn) {
                return getSeatsFromButton(btn).some(code => selectedSeats.includes(code));
            }

            function buttonIsLocked(btn) {
                const codes = getSeatsFromButton(btn);
                const selected = codes.some(code => selectedSeats.includes(code));
                return !selected && codes.some(code => lockedSeats.has(code));
            }

            function syncButtonStates() {
                seatButtons.forEach(btn => {
                    const selected = buttonIsSelected(btn);
                    const locked = buttonIsLocked(btn);
                    const staticDisabled = btn.dataset.staticDisabled === "1";

                    btn.classList.toggle("selected", selected);
                    btn.classList.toggle("locked", locked);
                    btn.disabled = staticDisabled || locked;
                    btn.setAttribute("aria-pressed", selected ? "true" : "false");
                    btn.setAttribute("aria-disabled", btn.disabled ? "true" : "false");
                });
            }

            async function loadLockedSeats() {
                if (isFetchingLocks || isProceedingToNextStep) return;
                isFetchingLocks = true;
                try {
                    const res = await fetch(`/dat-ve/seat-locks/${showtimeId}`);
                    if (res.ok) {
                        const data = await res.json();
                        lockedSeats = new Set(Object.keys(data.locked || {}).map(normalizeSeat));
                        syncButtonStates();
                    }
                } catch (e) {
                    console.error("Lỗi quét ghế đang giữ:", e);
                } finally {
                    isFetchingLocks = false;
                }
            }

            async function releaseSeats(seats) {
                for (const seat of seats) {
                    try {
                        await fetch(`/dat-ve/seat-locks/${showtimeId}/${encodeURIComponent(seat)}`, {
                            method: "DELETE",
                            headers: { "X-CSRF-TOKEN": csrf }
                        });
                    } catch (err) {
                        console.error("Lỗi hủy giữ ghế:", err);
                    }
                }
            }

            async function releaseAllSeats() {
                await releaseSeats(selectedSeats);
                selectedSeats = [];
                localStorage.removeItem(storageKey);
                updateUI();
            }

            function releaseAllSeatsBeacon() {
                if (isProceedingToNextStep || selectedSeats.length === 0) return;

                const url = `/dat-ve/seat-locks/${showtimeId}/release-all`;
                const formData = new FormData();
                formData.append("_token", csrf);

                if (navigator.sendBeacon) {
                    navigator.sendBeacon(url, formData);
                } else {
                    fetch(url, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": csrf,
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({}),
                        keepalive: true
                    });
                }
                localStorage.removeItem(storageKey);
            }

            window.addEventListener("pagehide", releaseAllSeatsBeacon);

            document.addEventListener("click", function(e) {
                const link = e.target.closest("a");
                if (link && link.href) {
                    if (link.href.includes("chon-do-an") || link.href.includes("chon_do_an") || link.classList.contains("js-proceed-link")) {
                        isProceedingToNextStep = true;
                        if (countdownTimerInterval) clearInterval(countdownTimerInterval);
                        return;
                    }
                    releaseAllSeatsBeacon();
                }
            });

            function updateCountdown(deadline) {
                const remain = deadline - Date.now();

                if (remain <= 0) {
                    clearInterval(countdownTimerInterval);
                    countdownTimerInterval = null;
                    if (countdownEl) countdownEl.innerText = "00:00";
                    releaseAllSeats();
                    window.location.href = "{{ route('home') }}";
                    return;
                }

                const minute = Math.floor(remain / 60000);
                const second = Math.floor((remain % 60000) / 1000);

                if (countdownEl) {
                    countdownEl.innerText = String(minute).padStart(2, "0") + ":" + String(second).padStart(2, "0");
                    countdownEl.classList.toggle("animate-pulse", remain <= 60000);
                }
            }

            function checkTimerState() {
                if (!countdownEl) return;

                if (selectedSeats.length === 0) {
                    if (countdownTimerInterval) {
                        clearInterval(countdownTimerInterval);
                        countdownTimerInterval = null;
                    }
                    localStorage.removeItem(storageKey);
                    countdownEl.innerText = "07:00";
                    countdownEl.classList.remove("animate-pulse");
                    return;
                }

                let deadline = Number(localStorage.getItem(storageKey));
                const serverDeadline = Number(pendingDeadline) || null;
                const validStoredDeadline = deadline && deadline > Date.now() ? deadline : null;
                const validServerDeadline = serverDeadline && serverDeadline > Date.now() ? serverDeadline : null;

                if (validStoredDeadline && validServerDeadline) {
                    deadline = Math.min(validStoredDeadline, validServerDeadline);
                } else if (validServerDeadline) {
                    deadline = validServerDeadline;
                } else if (validStoredDeadline) {
                    deadline = validStoredDeadline;
                } else {
                    deadline = Date.now() + (7 * 60 * 1000);
                }

                localStorage.setItem(storageKey, deadline);

                if (!countdownTimerInterval) {
                    updateCountdown(deadline);
                    countdownTimerInterval = setInterval(() => updateCountdown(deadline), 1000);
                }
            }

            // -------------------------------------------------------------
            // LOGIC TỰ ĐỘNG PHÂN BIỆT ẢNH 1 VÀ ẢNH 2:
            // - Ảnh 1 (Hàng có ghế người khác đã đặt): Cho phép chọn bất kỳ ghế trống nào ở 2 bên.
            // - Ảnh 2 (Hàng hoàn toàn trống chưa ai mua): Chặn trường hợp bỏ lại đúng 1 ghế trống lẻ ở sát tường.
            // -------------------------------------------------------------
            function validateSeatsAdjacentJS(seats) {
                if (!seats || seats.length === 0) return true;

                const seatRegex = /^([A-Z]+)([0-9]+)$/;
                const parsedSeats = seats.map(s => String(s || "").toUpperCase().trim()).filter(Boolean);
                const selectedCodes = new Set(parsedSeats);

                if (selectedCodes.size === 0) return true;

                // Lấy các hàng mà người dùng đang có chọn ghế
                const selectedRows = new Set();
                parsedSeats.forEach(code => {
                    const m = code.match(seatRegex);
                    if (m) selectedRows.add(m[1]);
                });

                for (const row of selectedRows) {
                    const rowButtons = seatButtons.filter(btn => {
                        const btnCodes = getSeatsFromButton(btn);
                        return btnCodes.some(c => c.startsWith(row));
                    });

                    const physicalSeats = [];
                    let hasBookedOrLockedInRow = false;

                    rowButtons.forEach(btn => {
                        const codes = getSeatsFromButton(btn);
                        const isUnavailable = btn.classList.contains('booked') ||
                                              btn.classList.contains('locked') ||
                                              btn.classList.contains('maintenance') ||
                                              btn.dataset.staticDisabled === "1";

                        if (isUnavailable) {
                            hasBookedOrLockedInRow = true; // Phát hiện hàng đã có người khác đặt vé
                        }

                        codes.forEach(c => {
                            const m = c.match(seatRegex);
                            if (m) {
                                physicalSeats.push({
                                    code: c,
                                    num: parseInt(m[2], 10),
                                    isUnavailable: isUnavailable,
                                    isSelected: selectedCodes.has(c),
                                    isOccupied: isUnavailable || selectedCodes.has(c)
                                });
                            }
                        });
                    });

                    physicalSeats.sort((a, b) => a.num - b.num);

                    // TH 1 (Ảnh 1): HÀNG ĐÃ CÓ GHẾ NGƯỜI KHÁC ĐẶT
                    // Chỉ kiểm tra không được bỏ trống ghế ở giữa CÁC GHẾ NGƯỜI DÙNG ĐANG CHỌN.
                    if (hasBookedOrLockedInRow) {
                        const selectedIndices = [];
                        physicalSeats.forEach((seat, idx) => {
                            if (seat.isSelected) selectedIndices.push(idx);
                        });

                        if (selectedIndices.length > 1) {
                            const minIdx = Math.min(...selectedIndices);
                            const maxIdx = Math.max(...selectedIndices);
                            for (let i = minIdx + 1; i < maxIdx; i++) {
                                if (!physicalSeats[i].isOccupied) {
                                    return false; // Chọn ghế đứt đoạn ở giữa
                                }
                            }
                        }
                    } 
                    // TH 2 (Ảnh 2): HÀNG HOÀN TOÀN TRỐNG (CHƯA AI ĐẶT VÉ NÀO)
                    // Áp dụng quy tắc không được bỏ lại duy nhất 1 ghế trống đơn lẻ ở đầu hàng
                    else {
                        let emptyBlockLength = 0;
                        for (let i = 0; i < physicalSeats.length; i++) {
                            if (physicalSeats[i].isOccupied) {
                                if (emptyBlockLength === 1) {
                                    return false; // Bị để lại 1 ghế trống lẻ sát tường/mép
                                }
                                emptyBlockLength = 0;
                            } else {
                                emptyBlockLength++;
                            }
                        }
                        if (emptyBlockLength === 1) {
                            return false;
                        }
                    }
                }

                return true;
            }

            function showSeatErrorJS(message) {
                let alertEl = document.querySelector(".booking-seat-alert");
                if (!alertEl) {
                    alertEl = document.createElement("div");
                    alertEl.className = "booking-seat-alert";
                    const toolbar = document.querySelector(".booking-seat-toolbar");
                    if (toolbar) toolbar.parentNode.insertBefore(alertEl, toolbar.nextSibling);
                }
                alertEl.innerHTML = `<i class="fa-solid fa-circle-exclamation" style="color: #ef4444 !important; font-size: 18px !important;"></i><span>${message}</span>`;
            }

            function clearSeatErrorJS() {
                const alertEl = document.querySelector(".booking-seat-alert");
                if (alertEl) alertEl.remove();
            }

            function updateUI() {
                selectedSeats = Array.from(new Set(selectedSeats.map(normalizeSeat).filter(Boolean)));
                syncButtonStates();

                const hasSeat = selectedSeats.length > 0;
                const isValid = hasSeat && validateSeatsAdjacentJS(selectedSeats);

                // UX: Hiển thị/ẩn thông báo mượt mà & Khóa nút bấm
                if (!hasSeat) {
                    clearSeatErrorJS();
                } else if (!isValid) {
                    showSeatErrorJS("Vị trí chọn không hợp lệ: Hàng ghế chưa có người đặt không được để lại 1 ghế trống đơn lẻ ở đầu hàng!");
                } else {
                    clearSeatErrorJS();
                }

                if (seatCountEl) seatCountEl.innerText = selectedSeats.length + " ghế";
                if (seatLabels) seatLabels.innerText = selectedSeats.length ? selectedSeats.join(", ") : "—";

                if (selectedList) {
                    if (selectedSeats.length === 0) {
                        selectedList.innerHTML = '<span class="booking-seat-empty-selection">Chưa chọn ghế nào</span>';
                    } else {
                        selectedList.innerHTML = selectedSeats.map(seat => `
                            <button type="button" data-remove-seat="${seat}" class="booking-seat-chip" aria-label="Bỏ ghế ${seat}">
                                ${seat}
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        `).join("");
                    }
                }

                let total = 0;
                seatButtons.forEach(btn => {
                    if (buttonIsSelected(btn)) total += Math.round(Number(btn.dataset.price || 0));
                });

                if (totalPriceEl) totalPriceEl.innerText = money(total);

                // UX: Khóa nút Tiếp tục chọn đồ ăn khi không hợp lệ
                if (btnFood) {
                    btnFood.disabled = !isValid;
                    btnFood.classList.toggle("is-enabled", isValid);
                }

                if (btnResetSeats) {
                    btnResetSeats.disabled = !hasSeat;
                    btnResetSeats.classList.toggle("is-disabled", !hasSeat);
                }

                checkTimerState();
            }

            seatButtons.forEach(btn => {
                btn.addEventListener("click", async function() {
                    if (btn.disabled || btn.dataset.staticDisabled === "1") return;

                    const codes = getSeatsFromButton(btn);
                    const isSelected = codes.every(code => selectedSeats.includes(code));
                    const previousSelectedSeats = [...selectedSeats];

                    if (isSelected) {
                        selectedSeats = selectedSeats.filter(seat => !codes.includes(seat));
                        updateUI();
                        await releaseSeats(codes);
                    } else {
                        if (selectedSeats.length + codes.length > maxSeatCount) {
                            alert("Bạn chỉ có thể chọn tối đa " + maxSeatCount + " ghế trong một lần đặt.");
                            return;
                        }

                        const reservedNow = [];
                        selectedSeats = Array.from(new Set([...selectedSeats, ...codes]));
                        updateUI();

                        for (const seat of codes) {
                            try {
                                const res = await fetch(`/dat-ve/seat-locks/${showtimeId}/${encodeURIComponent(seat)}`, {
                                    method: "POST",
                                    headers: { "X-CSRF-TOKEN": csrf }
                                });

                                if (!res.ok) throw new Error("seat_locked");
                                reservedNow.push(seat);
                            } catch (e) {
                                alert("Ghế " + seat + " vừa được người khác chọn.");
                                await releaseSeats(reservedNow);
                                selectedSeats = previousSelectedSeats;
                                updateUI();
                                await loadLockedSeats();
                                return;
                            }
                        }
                    }

                    updateUI();
                    await loadLockedSeats();
                });
            });

            if (selectedList) {
                selectedList.addEventListener("click", async function(e) {
                    const removeBtn = e.target.closest("[data-remove-seat]");
                    if (!removeBtn) return;

                    const seat = normalizeSeat(removeBtn.dataset.removeSeat);
                    const ownerButton = seatButtons.find(btn => getSeatsFromButton(btn).includes(seat));
                    const seatsToRemove = ownerButton ? getSeatsFromButton(ownerButton) : [seat];

                    selectedSeats = selectedSeats.filter(value => !seatsToRemove.includes(value));
                    updateUI();
                    await releaseSeats(seatsToRemove);
                    await loadLockedSeats();
                });
            }

            if (btnFood) {
                btnFood.addEventListener("click", function(e) {
                    e.preventDefault();
                    if (selectedSeats.length === 0) return;

                    if (!validateSeatsAdjacentJS(selectedSeats)) {
                        showSeatErrorJS("Vị trí chọn không hợp lệ: Hàng ghế chưa có người đặt không được để lại 1 ghế trống đơn lẻ ở đầu hàng!");
                        return;
                    }

                    isProceedingToNextStep = true;
                    window.removeEventListener("pagehide", releaseAllSeatsBeacon);
                    if (countdownTimerInterval) clearInterval(countdownTimerInterval);

                    btnFood.disabled = true;
                    btnFood.style.opacity = "0.7";
                    btnFood.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang chuyển trang...';

                    const seats = encodeURIComponent(selectedSeats.join(","));
                    let url = `{{ route('dat_ve.chon_do_an', ['suat_chieu_id' => $suatChieu->id]) }}?ghe=${seats}`;
                    if (pendingTicketId) {
                        url += `&pending_ticket_id=${encodeURIComponent(pendingTicketId)}`;
                    }

                    window.location.href = url;
                });
            }

            if (btnResetSeats) {
                btnResetSeats.addEventListener("click", async function() {
                    if (!confirm('Bạn có chắc chắn muốn hủy chọn ghế ?')) return;

                    localStorage.removeItem(storageKey);
                    await releaseAllSeats();
                    clearSeatErrorJS();

                    if (countdownEl) countdownEl.innerText = "07:00";
                    window.location.href = "{{ route('dat_ve.chon_ghe', ['movie' => $suatChieu->id, 'reset' => 1]) }}";
                });
            }

            updateUI();
            loadLockedSeats();
            setInterval(loadLockedSeats, 5000);
        });
    </script>
@endsection