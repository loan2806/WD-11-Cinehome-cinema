@extends('layouts.admin')

@section('title', 'Bán vé tại quầy')
@section('page-title', 'Bán vé tại quầy')

@push('styles')
<style>
    .staff-booking-user-ui {
        --cinema-bg: #080a0f;
        --cinema-surface: #10141d;
        --cinema-red: #e50914;
        --cinema-red-soft: #ff3b46;
        --cinema-gold: #f7b84b;
        --cinema-text: #fff8ef;
        --cinema-muted: #aeb7c8;
        --cinema-shadow: 0 28px 70px rgba(0, 0, 0, 0.42);

        width: 100%;
        color: var(--cinema-text);
    }

    .staff-booking-user-ui *,
    .staff-booking-user-ui *::before,
    .staff-booking-user-ui *::after {
        box-sizing: border-box;
    }

    .staff-booking-user-ui a {
        text-decoration: none;
    }

    .staff-booking-user-ui .booking-flow-page {
        width: min(100%, 1240px);
        margin: 0 auto;
        padding: 8px 0 42px;
    }

    .staff-booking-user-ui .booking-flow-hero {
        position: relative;
        display: grid;
        grid-template-columns: minmax(0, 1fr) 400px;
        gap: 32px;
        align-items: end;
        min-height: 365px;
        overflow: hidden;
        padding: 34px;
        border: 1px solid rgba(255,255,255,.12);
        border-radius: 28px;
        background:
            linear-gradient(90deg, rgba(8,10,15,.96), rgba(8,10,15,.7) 48%, rgba(8,10,15,.3)),
            linear-gradient(0deg, rgba(8,10,15,.92), rgba(8,10,15,.12)),
            url("https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=1600&auto=format&fit=crop") center/cover;
        box-shadow: var(--cinema-shadow);
    }

    .staff-booking-user-ui .booking-flow-hero::after {
        content: "";
        position: absolute;
        inset: auto 0 0;
        height: 46%;
        background: linear-gradient(180deg, transparent, rgba(8,10,15,.96));
        pointer-events: none;
    }

    .staff-booking-user-ui .booking-flow-hero > * {
        position: relative;
        z-index: 1;
    }

    .staff-booking-user-ui .booking-eyebrow,
    .staff-booking-user-ui .booking-section-head > div > span,
    .staff-booking-user-ui .booking-date-heading > div > span {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--cinema-gold);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .22em;
        text-transform: uppercase;
    }

    .staff-booking-user-ui .booking-flow-hero-copy h1 {
        max-width: 760px;
        margin: 18px 0 16px;
        color: #fff;
        font-size: clamp(38px, 5vw, 66px);
        font-weight: 950;
        line-height: .96;
    }

    .staff-booking-user-ui .booking-flow-hero-copy p {
        max-width: 640px;
        margin: 0;
        color: #d6deeb;
        font-size: 17px;
        line-height: 1.7;
    }

    .staff-booking-user-ui .booking-flow-hero-copy strong {
        color: #fff;
    }

    .staff-booking-user-ui .booking-stepper {
        display: grid;
        gap: 12px;
    }

    .staff-booking-user-ui .booking-step {
        display: grid;
        grid-template-columns: 42px minmax(0,1fr);
        gap: 12px;
        align-items: center;
        padding: 12px;
        border: 1px solid rgba(255,255,255,.13);
        border-radius: 18px;
        background: rgba(255,255,255,.08);
        backdrop-filter: blur(14px);
    }

    .staff-booking-user-ui .booking-step span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: rgba(255,255,255,.1);
        color: #fff;
        font-size: 14px;
        font-weight: 950;
    }

    .staff-booking-user-ui .booking-step strong {
        color: #fff;
        font-size: 14px;
    }

    .staff-booking-user-ui .booking-step.is-active {
        border-color: rgba(229,9,20,.52);
        background: rgba(229,9,20,.2);
        box-shadow: 0 16px 34px rgba(229,9,20,.18);
    }

    .staff-booking-user-ui .booking-step.is-active span {
        background: var(--cinema-red);
    }

    .staff-booking-user-ui .booking-date-panel {
        position: sticky;
        top: 82px;
        z-index: 35;
        display: grid;
        gap: 18px;
        margin-top: 22px;
        padding: 18px;
        border: 1px solid rgba(255,255,255,.12);
        border-radius: 24px;
        background: rgba(12,15,22,.94);
        box-shadow: 0 24px 55px rgba(0,0,0,.32);
        backdrop-filter: blur(18px);
    }

    .staff-booking-user-ui .booking-date-heading {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 18px;
    }

    .staff-booking-user-ui .booking-date-heading h2 {
        margin: 6px 0 0;
        color: #fff;
        font-size: 26px;
        font-weight: 950;
    }

    .staff-booking-user-ui .booking-date-heading p {
        max-width: 430px;
        margin: 0;
        color: var(--cinema-muted);
        text-align: right;
        line-height: 1.6;
    }

    .staff-booking-user-ui .booking-date-form {
        display: grid;
        grid-template-columns: 48px minmax(0,1fr) 48px;
        gap: 10px;
        align-items: center;
    }

    .staff-booking-user-ui .booking-date-nav {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 68px;
        border: 1px solid rgba(255,255,255,.12);
        border-radius: 18px;
        background: rgba(255,255,255,.06);
        color: #fff;
        cursor: pointer;
        transition: .25s ease;
    }

    .staff-booking-user-ui .booking-date-nav:hover {
        border-color: rgba(247,184,75,.55);
        background: rgba(247,184,75,.13);
        color: var(--cinema-gold);
    }

    .staff-booking-user-ui .booking-date-track {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        padding: 2px;
        scroll-snap-type: x mandatory;
        scrollbar-width: none;
    }

    .staff-booking-user-ui .booking-date-track::-webkit-scrollbar {
        display: none;
    }

    .staff-booking-user-ui .booking-date-chip {
        flex: 0 0 92px;
        min-height: 68px;
        border: 1px solid rgba(255,255,255,.12);
        border-radius: 18px;
        background: rgba(255,255,255,.06);
        color: #fff;
        text-align: center;
        scroll-snap-align: center;
        cursor: pointer;
        transition: .25s ease;
    }

    .staff-booking-user-ui .booking-date-chip:hover {
        transform: translateY(-2px);
        border-color: rgba(247,184,75,.45);
    }

    .staff-booking-user-ui .booking-date-chip span,
    .staff-booking-user-ui .booking-date-chip small {
        display: block;
        color: var(--cinema-muted);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .staff-booking-user-ui .booking-date-chip strong {
        display: block;
        margin: 3px 0;
        color: #fff;
        font-size: 24px;
        font-weight: 950;
        line-height: 1;
    }

    .staff-booking-user-ui .booking-date-chip.is-active {
        border-color: rgba(229,9,20,.85);
        background: linear-gradient(135deg, #e50914, #ff5a31);
        box-shadow: 0 16px 34px rgba(229,9,20,.24);
        transform: translateY(-2px);
    }

    .staff-booking-user-ui .booking-date-chip.is-active span,
    .staff-booking-user-ui .booking-date-chip.is-active small,
    .staff-booking-user-ui .booking-date-chip.is-active strong {
        color: #fff;
    }

    .staff-booking-user-ui .booking-showtime-section {
        margin-top: 30px;
    }

    .staff-booking-user-ui .booking-section-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
    }

    .staff-booking-user-ui .booking-section-head h2 {
        margin: 6px 0 0;
        color: #fff;
        font-size: 34px;
        font-weight: 950;
    }

    .staff-booking-user-ui .booking-section-meta {
        color: var(--cinema-muted);
        font-size: 14px;
        font-weight: 800;
    }

    .staff-booking-user-ui .booking-showtime-list {
        display: grid;
        gap: 18px;
    }

    .staff-booking-user-ui .booking-showtime-card {
        display: grid;
        grid-template-columns: 160px minmax(0,1fr);
        gap: 22px;
        overflow: hidden;
        padding: 16px;
        border: 1px solid rgba(255,255,255,.12);
        border-radius: 24px;
        background:
            linear-gradient(135deg, rgba(255,255,255,.08), rgba(255,255,255,.03)),
            rgba(16,20,29,.92);
        box-shadow: 0 20px 45px rgba(0,0,0,.28);
        transition: .25s ease;
    }

    .staff-booking-user-ui .booking-showtime-card:hover {
        transform: translateY(-4px);
        border-color: rgba(247,184,75,.32);
        box-shadow: 0 28px 62px rgba(0,0,0,.36);
    }

    .staff-booking-user-ui .booking-showtime-poster {
        display: block;
        overflow: hidden;
        min-height: 230px;
        border-radius: 18px;
        background: #0c0f16;
    }

    .staff-booking-user-ui .booking-showtime-poster img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .25s ease;
    }

    .staff-booking-user-ui .booking-showtime-card:hover .booking-showtime-poster img {
        transform: scale(1.045);
    }

    .staff-booking-user-ui .booking-showtime-body {
        display: flex;
        min-width: 0;
        flex-direction: column;
        justify-content: center;
        padding: 4px 6px 4px 0;
    }

    .staff-booking-user-ui .booking-showtime-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
    }

    .staff-booking-user-ui .booking-movie-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 10px;
    }

    .staff-booking-user-ui .booking-movie-tags span {
        display: inline-flex;
        align-items: center;
        min-height: 26px;
        padding: 0 10px;
        border: 1px solid rgba(247,184,75,.28);
        border-radius: 999px;
        background: rgba(247,184,75,.1);
        color: #ffd891;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .staff-booking-user-ui .booking-movie-tags .age {
        border-color: rgba(229,9,20,.58);
        background: rgba(229,9,20,.82);
        color: #fff;
    }

    .staff-booking-user-ui .booking-showtime-body h3 {
        margin: 0;
        color: #fff;
        font-size: 30px;
        font-weight: 950;
        line-height: 1.12;
    }

    .staff-booking-user-ui .booking-detail-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        padding: 0 14px;
        border: 1px solid rgba(255,255,255,.14);
        border-radius: 999px;
        color: #fff;
        font-size: 13px;
        font-weight: 850;
        white-space: nowrap;
    }

    .staff-booking-user-ui .booking-detail-link:hover {
        border-color: rgba(255,255,255,.28);
        background: rgba(255,255,255,.1);
        color: #fff;
    }

    .staff-booking-user-ui .booking-movie-genres,
    .staff-booking-user-ui .booking-movie-desc {
        margin: 10px 0 0;
        color: var(--cinema-muted);
        line-height: 1.65;
    }

    .staff-booking-user-ui .booking-movie-desc {
        max-width: 760px;
        color: #cbd4e4;
    }

    .staff-booking-user-ui .booking-time-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 22px;
    }

    .staff-booking-user-ui .booking-time-chip {
        display: inline-flex;
        min-width: 112px;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        min-height: 70px;
        padding: 10px 14px;
        border: 1px solid rgba(229,9,20,.55);
        border-radius: 18px;
        background: linear-gradient(135deg, #e50914, #ff5838);
        color: #fff;
        box-shadow: 0 14px 28px rgba(229,9,20,.22);
        transition: .25s ease;
    }

    .staff-booking-user-ui .booking-time-chip strong {
        font-size: 22px;
        font-weight: 950;
        line-height: 1;
    }

    .staff-booking-user-ui .booking-time-chip span {
        color: rgba(255,255,255,.82);
        font-size: 12px;
        font-weight: 800;
    }

    .staff-booking-user-ui .booking-time-chip:hover {
        color: #fff;
        transform: translateY(-3px);
        box-shadow: 0 20px 34px rgba(229,9,20,.32);
    }

    .staff-booking-user-ui .booking-flow-empty {
        padding: 70px 20px;
        border: 1px dashed rgba(255,255,255,.18);
        border-radius: 24px;
        background: rgba(16,20,29,.92);
        text-align: center;
    }

    .staff-booking-user-ui .booking-flow-empty i {
        color: var(--cinema-gold);
        font-size: 50px;
    }

    .staff-booking-user-ui .booking-flow-empty h2 {
        margin: 16px 0 8px;
        color: #fff;
        font-size: 28px;
        font-weight: 950;
    }

    .staff-booking-user-ui .booking-flow-empty p {
        max-width: 480px;
        margin: 0 auto;
        color: var(--cinema-muted);
        line-height: 1.7;
    }

    @media (max-width: 992px) {
        .staff-booking-user-ui .booking-flow-hero {
            grid-template-columns: 1fr;
            min-height: auto;
        }

        .staff-booking-user-ui .booking-stepper {
            grid-template-columns: repeat(4, minmax(0,1fr));
        }

        .staff-booking-user-ui .booking-step {
            grid-template-columns: 1fr;
            justify-items: center;
            text-align: center;
        }

        .staff-booking-user-ui .booking-date-panel {
            position: relative;
            top: auto;
        }

        .staff-booking-user-ui .booking-date-heading,
        .staff-booking-user-ui .booking-section-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .staff-booking-user-ui .booking-date-heading p {
            text-align: left;
        }

        .staff-booking-user-ui .booking-showtime-card {
            grid-template-columns: 130px minmax(0,1fr);
        }
    }

    @media (max-width: 768px) {
        .staff-booking-user-ui .booking-flow-hero {
            padding: 24px;
            border-radius: 20px;
        }

        .staff-booking-user-ui .booking-flow-hero-copy h1 {
            font-size: 40px;
        }

        .staff-booking-user-ui .booking-stepper {
            grid-template-columns: repeat(2, minmax(0,1fr));
        }

        .staff-booking-user-ui .booking-date-form {
            grid-template-columns: 42px minmax(0,1fr) 42px;
        }

        .staff-booking-user-ui .booking-date-nav {
            width: 42px;
        }

        .staff-booking-user-ui .booking-showtime-card {
            grid-template-columns: 106px minmax(0,1fr);
            gap: 14px;
            padding: 12px;
            border-radius: 20px;
        }

        .staff-booking-user-ui .booking-showtime-poster {
            min-height: 164px;
        }

        .staff-booking-user-ui .booking-showtime-top {
            flex-direction: column;
        }

        .staff-booking-user-ui .booking-showtime-body h3 {
            font-size: 22px;
        }

        .staff-booking-user-ui .booking-time-chip {
            min-width: 96px;
        }
    }

    @media (max-width: 576px) {
        .staff-booking-user-ui .booking-flow-hero-copy h1 {
            font-size: 34px;
        }

        .staff-booking-user-ui .booking-stepper {
            grid-template-columns: 1fr;
        }

        .staff-booking-user-ui .booking-showtime-card {
            grid-template-columns: 1fr;
        }

        .staff-booking-user-ui .booking-showtime-poster {
            min-height: 280px;
        }

        .staff-booking-user-ui .booking-time-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0,1fr));
        }

        .staff-booking-user-ui .booking-time-chip {
            min-width: 0;
        }
    }

    /* ===== V3: mở rộng giao diện Staff và làm Hero sát giao diện User ===== */
    .staff-booking-user-ui .booking-flow-page {
        width: calc(100% - 24px) !important;
        max-width: none !important;
        margin: 0 12px !important;
        padding: 8px 0 52px !important;
    }

    .staff-booking-user-ui .booking-flow-hero {
        grid-template-columns: minmax(0, 1fr) minmax(350px, 430px) !important;
        min-height: 365px !important;
        padding: 34px !important;
        background:
            linear-gradient(90deg, rgba(8, 10, 15, 0.88), rgba(8, 10, 15, 0.58) 48%, rgba(8, 10, 15, 0.20)),
            linear-gradient(0deg, rgba(8, 10, 15, 0.82), rgba(8, 10, 15, 0.04)),
            url("https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=1600&auto=format&fit=crop") center 44% / cover no-repeat !important;
    }

    .staff-booking-user-ui .booking-flow-hero-copy {
        align-self: end !important;
        max-width: 760px !important;
    }

    .staff-booking-user-ui .booking-flow-hero-copy h1 {
        display: block !important;
        max-width: 720px !important;
        margin: 18px 0 16px !important;
        color: #fff !important;
        font-size: clamp(46px, 4.4vw, 68px) !important;
        font-weight: 950 !important;
        line-height: .96 !important;
        letter-spacing: -0.045em !important;
    }

    .staff-booking-user-ui .booking-flow-hero-copy p {
        max-width: 680px !important;
        margin: 0 !important;
        color: #d6deeb !important;
        font-size: 16px !important;
        line-height: 1.7 !important;
    }

    .staff-booking-user-ui .booking-stepper {
        width: 100% !important;
        max-width: 430px !important;
        justify-self: end !important;
    }

    .staff-booking-user-ui .booking-date-panel,
    .staff-booking-user-ui .booking-showtime-section {
        width: 100% !important;
    }

    @media (max-width: 1200px) {
        .staff-booking-user-ui .booking-flow-hero {
            grid-template-columns: minmax(0, 1fr) 340px !important;
        }

        .staff-booking-user-ui .booking-flow-hero-copy h1 {
            font-size: 48px !important;
        }
    }

    @media (max-width: 992px) {
        .staff-booking-user-ui .booking-flow-page {
            width: calc(100% - 16px) !important;
            margin: 0 8px !important;
        }

        .staff-booking-user-ui .booking-flow-hero {
            grid-template-columns: 1fr !important;
            min-height: auto !important;
        }

        .staff-booking-user-ui .booking-stepper {
            max-width: none !important;
            justify-self: stretch !important;
        }
    }

</style>
@endpush

@section('content')
    @php
        $showtimeCollection = collect($showtimes);

        /*
         * Giống trang User: thanh ngày luôn hiển thị, không phụ thuộc
         * ngày đó đã có suất chiếu hay chưa.
         * Hiển thị từ hôm nay đến 10 ngày tiếp theo.
         */
        $dateStart = now()->startOfDay();

        $dates = collect(range(0, 10))
            ->map(fn ($offset) => $dateStart->copy()->addDays($offset)->format('Y-m-d'));

        $requestedDate = request('ngay_chieu');

        $selectedDate = $requestedDate && $dates->contains($requestedDate)
            ? $requestedDate
            : $dateStart->format('Y-m-d');

        $selectedCarbon = \Carbon\Carbon::parse($selectedDate);

        $dayShowtimes = $showtimeCollection
            ->filter(fn ($item) =>
                $selectedDate &&
                $item->thoi_gian_chieu?->format('Y-m-d') === $selectedDate
            )
            ->sortBy('thoi_gian_chieu')
            ->values();

        $movieGroups = $dayShowtimes->groupBy('phim_id');

        $weekdayLabels = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];

        $activeDateLabel = $selectedCarbon->isToday()
            ? 'Hôm nay'
            : ($selectedCarbon->isTomorrow()
                ? 'Ngày mai'
                : $weekdayLabels[$selectedCarbon->dayOfWeek]);

        $staffCinemaName = $dayShowtimes->first()?->rapChieuPhim?->ten_rap
            ?? $showtimeCollection->first()?->rapChieuPhim?->ten_rap
            ?? 'CineHome';

        $staffCinemaAddress = $dayShowtimes->first()?->rapChieuPhim?->dia_chi
            ?? $showtimeCollection->first()?->rapChieuPhim?->dia_chi
            ?? 'Quầy vé CineHome';
    @endphp

    <div class="staff-booking-user-ui">
        @include('admin.partials.flash')

        <div class="booking-flow-page booking-movie-select-page" lang="vi" spellcheck="false">

            <section class="booking-flow-hero">
                <div class="booking-flow-hero-copy">
                    <span class="booking-eyebrow">
                        <i class="fa-solid fa-ticket"></i>
                        Bán vé CineHome
                    </span>

                    <h1>Chọn phim, chọn suất, bán vé thật nhanh.</h1>

                    <p>
                        Lịch chiếu tại <strong>{{ $staffCinemaName }}</strong>.
                        Chọn ngày bên dưới để xem các suất đang mở bán và tiếp tục phục vụ khách tại quầy.
                    </p>
                </div>

                <div class="booking-stepper" aria-label="Tiến trình bán vé">
                    <div class="booking-step is-active">
                        <span>1</span>
                        <strong>Chọn phim</strong>
                    </div>

                    <div class="booking-step">
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

            <section class="booking-date-panel" aria-label="Chọn ngày chiếu">
                <div class="booking-date-heading">
                    <div>
                        <span>Lịch chiếu</span>
                        <h2>{{ $activeDateLabel }} • {{ $selectedCarbon->format('d/m/Y') }}</h2>
                    </div>

                    <p>{{ $staffCinemaAddress }}</p>
                </div>

                <form id="staffDateForm"
                          action="{{ route('staff.ban-ve.index') }}"
                          method="GET"
                          class="booking-date-form">

                        <input type="hidden"
                               name="ngay_chieu"
                               id="staffSelectedDateInput"
                               value="{{ $selectedDate }}">

                        <button type="button"
                                id="staffPrevDate"
                                class="booking-date-nav"
                                aria-label="Ngày trước">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>

                        <div id="staffDateList" class="booking-date-track">
                            @foreach ($dates as $date)
                                @php
                                    $dateCarbon = \Carbon\Carbon::parse($date);

                                    $dateLabel = $dateCarbon->isToday()
                                        ? 'Hôm nay'
                                        : ($dateCarbon->isTomorrow()
                                            ? 'Ngày mai'
                                            : $weekdayLabels[$dateCarbon->dayOfWeek]);
                                @endphp

                                <button type="button"
                                        data-staff-date="{{ $date }}"
                                        class="booking-date-chip {{ $selectedDate === $date ? 'is-active' : '' }}"
                                        aria-pressed="{{ $selectedDate === $date ? 'true' : 'false' }}">
                                    <span>{{ $dateLabel }}</span>
                                    <strong>{{ $dateCarbon->format('d') }}</strong>
                                    <small>{{ $weekdayLabels[$dateCarbon->dayOfWeek] }}</small>
                                </button>
                            @endforeach
                        </div>

                        <button type="button"
                                id="staffNextDate"
                                class="booking-date-nav"
                                aria-label="Ngày sau">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </form>
            </section>

            <section class="booking-showtime-section">
                <div class="booking-section-head">
                    <div>
                        <span>Suất chiếu còn vé</span>
                        <h2>Chọn giờ bắt đầu</h2>
                    </div>

                    <div class="booking-section-meta">
                        {{ number_format($dayShowtimes->count()) }} suất
                    </div>
                </div>

                <div class="booking-showtime-list">
                    @forelse ($movieGroups as $movieId => $movieShowtimes)
                        @php
                            $movie = $movieShowtimes->first()?->phim;

                            $posterUrl = $movie?->poster
                                ? asset('storage/movies/' . $movie->poster)
                                : 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=800&auto=format&fit=crop';

                            $ageLabel = $movie?->gioi_han_tuoi
                                ?? $movie?->do_tuoi
                                ?? null;

                            $genreText = '';

                            try {
                                if ($movie && isset($movie->genres) && $movie->genres) {
                                    $genreText = $movie->genres
                                        ->pluck('ten_the_loai')
                                        ->filter()
                                        ->join(' • ');
                                }
                            } catch (\Throwable $e) {
                                $genreText = '';
                            }
                        @endphp

                        <article class="booking-showtime-card">
                            <div class="booking-showtime-poster">
                                <img src="{{ $posterUrl }}"
                                     alt="{{ $movie?->ten_phim ?? 'Poster phim' }}">
                            </div>

                            <div class="booking-showtime-body">
                                <div class="booking-showtime-top">
                                    <div>
                                        <div class="booking-movie-tags">
                                            @if (!empty($ageLabel))
                                                <span class="age">{{ $ageLabel }}</span>
                                            @endif

                                            <span>2D</span>

                                            @if (!empty($movie?->thoi_luong))
                                                <span>{{ $movie->thoi_luong }} phút</span>
                                            @endif
                                        </div>

                                        <h3>{{ $movie?->ten_phim ?? 'Phim chưa cập nhật' }}</h3>
                                    </div>

                                    @if (Route::has('staff.lich-su-ve.index'))
                                        <a href="{{ route('staff.lich-su-ve.index') }}"
                                           class="booking-detail-link">
                                            Lịch sử vé
                                        </a>
                                    @endif
                                </div>

                                @if ($genreText !== '')
                                    <p class="booking-movie-genres">
                                        {{ $genreText }}
                                    </p>
                                @endif

                                @if (!empty($movie?->mo_ta))
                                    <p class="booking-movie-desc">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($movie->mo_ta), 150) }}
                                    </p>
                                @endif

                                <div class="booking-time-grid"
                                     aria-label="Danh sách suất chiếu của {{ $movie?->ten_phim ?? 'phim' }}">

                                    @foreach ($movieShowtimes as $showtime)
                                        <a href="{{ route('staff.ban-ve.show', $showtime->id) }}"
                                           class="booking-time-chip">
                                            <strong>
                                                {{ $showtime->thoi_gian_chieu?->format('H:i') ?? '--:--' }}
                                            </strong>

                                            <span>
                                                {{ $showtime->phongChieu?->ten_phong ?? 'Phòng chiếu' }}
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="booking-flow-empty">
                            <i class="fa-solid fa-film"></i>
                            <h2>Chưa có suất chiếu</h2>
                            <p>
                                Hiện chưa có suất chiếu nào cho ngày này.
                                Hãy chọn một ngày khác để tiếp tục bán vé.
                            </p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        localStorage.removeItem('staff_food_cart');

        const dateForm = document.getElementById('staffDateForm');
        const dateInput = document.getElementById('staffSelectedDateInput');
        const dateButtons = Array.from(document.querySelectorAll('[data-staff-date]'));
        const prevDate = document.getElementById('staffPrevDate');
        const nextDate = document.getElementById('staffNextDate');

        let isSubmitting = false;

        if (!dateForm || !dateInput || dateButtons.length === 0) {
            return;
        }

        let activeIndex = dateButtons.findIndex(
            button => button.dataset.staffDate === dateInput.value
        );

        if (activeIndex < 0) {
            activeIndex = 0;
        }

        function submitOnce() {
            if (isSubmitting) return;

            isSubmitting = true;

            window.setTimeout(function () {
                dateForm.submit();
            }, 70);
        }

        function setActiveIndex(index, shouldSubmit) {
            if (index < 0 || index >= dateButtons.length) {
                return;
            }

            activeIndex = index;
            dateInput.value = dateButtons[activeIndex].dataset.staffDate;

            dateButtons.forEach(function (button, currentIndex) {
                const active = currentIndex === activeIndex;

                button.classList.toggle('is-active', active);
                button.setAttribute('aria-pressed', active ? 'true' : 'false');
            });

            dateButtons[activeIndex].scrollIntoView({
                inline: 'center',
                behavior: 'smooth',
                block: 'nearest'
            });

            if (shouldSubmit) {
                submitOnce();
            }
        }

        dateButtons.forEach(function (button, index) {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                setActiveIndex(index, true);
            });
        });

        prevDate?.addEventListener('click', function (event) {
            event.preventDefault();
            setActiveIndex(Math.max(0, activeIndex - 1), false);
        });

        nextDate?.addEventListener('click', function (event) {
            event.preventDefault();
            setActiveIndex(
                Math.min(dateButtons.length - 1, activeIndex + 1),
                false
            );
        });
    });
</script>
@endpush