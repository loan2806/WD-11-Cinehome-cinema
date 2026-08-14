{{--
    CSS sơ đồ ghế cho trang "Bán vé tại quầy" — sao chép & scope lại (tiền tố
    .booking-seat-page) từ đúng bộ CSS trang "Đặt vé online"
    (public/assets/css/user-home.css, các khối .booking-seat-*/.seat-*) để 2
    trang có giao diện và hành vi ghế giống hệt nhau. Các biến màu
    (--cinema-gold, --cinema-red, --cinema-muted, --cinema-shadow...) đã có
    sẵn trong public/assets/css/admin.css nên không cần khai báo lại.
--}}
.dat-ve-page.booking-seat-page {
    min-height: calc(100vh - 120px);
    padding: 4px 4px 40px;
    color: var(--cinema-text);
    isolation: isolate;
    background: transparent;
}

.booking-seat-page .booking-eyebrow,
.booking-seat-page .booking-showtime-chip {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.08);
    color: rgba(255, 248, 239, 0.86);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06);
    backdrop-filter: blur(12px);
}
.booking-seat-page .booking-eyebrow {
    margin-bottom: 18px;
    padding: 8px 14px;
    font-size: 13px;
    font-weight: 900;
    text-transform: uppercase;
}
.booking-seat-page .booking-stepper {
    display: grid;
    gap: 12px;
}
.booking-seat-page .booking-step {
    display: grid;
    grid-template-columns: 42px minmax(0, 1fr);
    gap: 12px;
    align-items: center;
    padding: 12px;
    border: 1px solid rgba(255, 255, 255, 0.13);
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(14px);
}
.booking-seat-page .booking-step span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    color: #ffffff;
    font-size: 14px;
    font-weight: 950;
}
.booking-seat-page .booking-step strong {
    color: #ffffff;
    font-size: 14px;
}
.booking-seat-page .booking-step.is-active {
    border-color: rgba(229, 9, 20, 0.52);
    background: rgba(229, 9, 20, 0.2);
    box-shadow: 0 16px 34px rgba(229, 9, 20, 0.18);
}
.booking-seat-page .booking-step.is-active span {
    background: var(--cinema-red);
}
.booking-seat-page .booking-seat-hero {
    position: relative;
    display: grid;
    grid-template-columns: minmax(0, 1fr) 430px;
    gap: 32px;
    align-items: end;
    width: 100%;
    min-height: 260px;
    margin: 0 auto 24px;
    overflow: hidden;
    padding: 32px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 28px;
    background:
        linear-gradient(90deg, rgba(8, 10, 15, 0.98), rgba(8, 10, 15, 0.74) 52%, rgba(8, 10, 15, 0.3)),
        linear-gradient(0deg, rgba(8, 10, 15, 0.92), rgba(8, 10, 15, 0.12)),
        url("https://images.unsplash.com/photo-1513106580091-1d82408b8cd6?q=80&w=1700&auto=format&fit=crop") center/cover;
    box-shadow: var(--cinema-shadow);
}
.booking-seat-page .booking-seat-hero::after {
    content: "";
    position: absolute;
    inset: auto 0 0;
    height: 46%;
    background: linear-gradient(180deg, transparent, rgba(8, 10, 15, 0.96));
    pointer-events: none;
}
.booking-seat-page .booking-seat-hero > * {
    position: relative;
    z-index: 1;
}
.booking-seat-page .booking-step.is-done {
    border-color: rgba(52, 211, 153, 0.36);
    background: rgba(52, 211, 153, 0.12);
}
.booking-seat-page .booking-step.is-done span {
    background: rgba(52, 211, 153, 0.95);
    color: #06110c;
}
.booking-seat-page .booking-seat-mini-stats {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 20px;
}
.booking-seat-page .booking-seat-mini-stats div {
    display: grid;
    min-width: 122px;
    gap: 3px;
    padding: 12px 14px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(12px);
}
.booking-seat-page .booking-seat-mini-stats strong {
    color: #ffffff;
    font-size: 20px;
    font-weight: 950;
    line-height: 1.1;
}
.booking-seat-page .booking-seat-mini-stats span {
    color: var(--cinema-muted);
    font-size: 12px;
    font-weight: 850;
}
.booking-seat-page .booking-seat-layout {
    display: grid;
    grid-template-columns: minmax(250px, 300px) minmax(560px, 1fr) minmax(310px, 360px);
    gap: 22px;
    align-items: start;
    width: 100%;
    margin: 0 auto;
}
.booking-seat-page .booking-seat-movie-card,
.booking-seat-page .booking-seat-map-panel,
.booking-seat-page .booking-seat-order-card {
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 26px !important;
    background:
        linear-gradient(145deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.035)),
        rgba(16, 20, 29, 0.94) !important;
    box-shadow: 0 24px 60px rgba(0, 0, 0, 0.36);
}
.booking-seat-page .booking-seat-movie-card,
.booking-seat-page .booking-seat-order-card {
    position: sticky;
    top: 16px;
}
.booking-seat-page .booking-seat-movie-card {
    display: grid;
    gap: 16px;
    padding: 18px;
}
.booking-seat-page .booking-seat-poster-wrap {
    position: relative;
    overflow: hidden;
    border-radius: 22px;
    background: #080a0f;
}
.booking-seat-page .booking-seat-poster-wrap img {
    display: block;
    width: 100%;
    aspect-ratio: 2 / 3;
    object-fit: cover;
}
.booking-seat-page .booking-seat-poster-overlay {
    position: absolute;
    inset: auto 0 0;
    display: grid;
    gap: 4px;
    padding: 46px 16px 16px;
    background: linear-gradient(180deg, transparent, rgba(4, 6, 10, 0.96));
}
.booking-seat-page .booking-seat-poster-overlay span {
    color: var(--cinema-red-soft);
    font-size: 11px;
    font-weight: 950;
    letter-spacing: 0.16em;
    text-transform: uppercase;
}
.booking-seat-page .booking-seat-poster-overlay strong {
    color: #ffffff;
    font-size: 20px;
    font-weight: 950;
    line-height: 1.12;
}
.booking-seat-page .booking-seat-info-card {
    display: grid;
    gap: 14px;
    padding: 16px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    background: rgba(0, 0, 0, 0.2);
}
.booking-seat-page .booking-seat-info-card h2,
.booking-seat-page .booking-seat-order-card h2,
.booking-seat-page .booking-seat-toolbar h2 {
    margin: 0;
    color: #ffffff !important;
    font-size: 21px;
    font-weight: 950;
    line-height: 1.2;
}
.booking-seat-page .booking-seat-info-card dl {
    display: grid;
    gap: 12px;
    margin: 0;
}
.booking-seat-page .booking-seat-info-card dl > div {
    display: grid;
    gap: 3px;
}
.booking-seat-page .booking-seat-info-card dt {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: var(--cinema-muted);
    font-size: 12px;
    font-weight: 850;
}
.booking-seat-page .booking-seat-info-card dt i {
    color: var(--cinema-gold);
}
.booking-seat-page .booking-seat-info-card dd {
    margin: 0;
    color: #ffffff;
    font-size: 14px;
    font-weight: 850;
}
.booking-seat-page .booking-seat-back-link,
.booking-seat-page .booking-seat-secondary-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    min-height: 46px;
    border: 1px solid rgba(255, 255, 255, 0.13);
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.06);
    color: #d6deeb !important;
    font-size: 13px;
    font-weight: 900;
    text-decoration: none !important;
}
.booking-seat-page .booking-seat-back-link:hover,
.booking-seat-page .booking-seat-secondary-link:hover {
    border-color: rgba(247, 184, 75, 0.5);
    background: rgba(247, 184, 75, 0.11);
    color: #ffffff !important;
}
.booking-seat-page .booking-seat-map-panel {
    min-width: 0;
    overflow: hidden;
    padding: 22px;
}
.booking-seat-page .booking-seat-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 18px;
    padding: 18px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 22px;
    background: rgba(0, 0, 0, 0.2);
}
.booking-seat-page .booking-seat-toolbar p,
.booking-seat-page .booking-seat-map-panel p {
    margin: 8px 0 0;
    color: var(--cinema-muted);
    font-size: 14px;
}
.booking-seat-page .booking-seat-timer {
    display: grid;
    min-width: 190px;
    gap: 5px;
    padding: 13px 16px;
    border: 1px solid rgba(255, 255, 255, 0.13);
    border-radius: 18px;
    background: rgba(8, 10, 15, 0.58);
    text-align: right;
}
.booking-seat-page .booking-seat-timer span {
    display: inline-flex;
    align-items: center;
    justify-content: flex-end;
    gap: 7px;
    color: var(--cinema-muted);
    font-size: 11px;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}
.booking-seat-page .booking-seat-map-panel #countdown {
    display: inline-flex;
    align-items: center;
    justify-content: flex-end;
    min-height: 42px;
    color: #ff6b6b !important;
    font-size: 30px;
    font-weight: 950;
    letter-spacing: 0.12em;
}
.booking-seat-page .booking-seat-alert {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 18px;
    padding: 12px 14px;
    border: 1px solid rgba(255, 82, 82, 0.34);
    border-radius: 16px;
    background: rgba(229, 9, 20, 0.12);
    color: #ffb4b4;
    font-size: 13px;
    font-weight: 800;
}
.booking-seat-page .booking-theater {
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 24px;
    background:
        linear-gradient(180deg, rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.02)),
        rgba(7, 9, 14, 0.56);
}
.booking-seat-page .booking-screen-wrap {
    display: grid;
    gap: 9px;
    padding: 24px 28px 10px;
    text-align: center;
}
.booking-seat-page .booking-screen-wrap .screen-line {
    width: min(84%, 720px);
    height: 38px;
    margin: 0 auto;
    border-radius: 100% 100% 0 0;
    background: linear-gradient(180deg, rgba(255, 226, 155, 0.96), rgba(247, 184, 75, 0.18));
    box-shadow: 0 18px 44px rgba(247, 184, 75, 0.24);
}
.booking-seat-page .booking-screen-wrap span {
    color: var(--cinema-gold);
    font-size: 12px;
    font-weight: 950;
    letter-spacing: 0.2em;
    text-transform: uppercase;
}
.booking-seat-page .booking-seat-scroll {
    overflow-x: auto;
    padding: 18px 20px 20px;
}
.booking-seat-page .booking-seat-grid {
    display: grid;
    gap: 10px;
    min-width: max-content;
    margin: 0 auto;
}
.booking-seat-page .seat-row {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: nowrap;
    gap: 8px;
}
.booking-seat-page .row-label {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 28px;
    width: 28px;
    height: 36px;
    color: var(--cinema-gold);
    font-size: 13px;
    font-weight: 950;
}
.booking-seat-page .seat-wrapper {
    position: relative;
    flex: 0 0 auto;
}
.booking-seat-page .seat-button {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 46px;
    height: 42px;
    border: 1px solid rgba(255, 255, 255, 0.16);
    border-radius: 10px 10px 6px 6px;
    background: var(--seat-color, #4b5563);
    color: #ffffff;
    cursor: pointer;
    font-size: 11px;
    font-weight: 950;
    line-height: 1;
    box-shadow: inset 0 -8px 14px rgba(0, 0, 0, 0.18);
}
.booking-seat-page .seat-button:hover:not(:disabled) {
    border-color: rgba(247, 184, 75, 0.72);
    box-shadow:
        inset 0 -8px 14px rgba(0, 0, 0, 0.18),
        0 10px 24px rgba(247, 184, 75, 0.2);
    transform: translateY(-3px);
}
.booking-seat-page .seat-button.selected {
    border-color: rgba(255, 226, 155, 0.96);
    background: linear-gradient(135deg, #ffe29b, var(--cinema-gold));
    color: #211102;
    box-shadow:
        0 0 0 3px rgba(247, 184, 75, 0.18),
        0 14px 28px rgba(247, 184, 75, 0.32);
    transform: translateY(-3px);
}
.booking-seat-page .seat-button.booked,
.booking-seat-page .seat-button.maintenance,
.booking-seat-page .seat-button.locked {
    cursor: not-allowed;
    opacity: 0.62;
    background: #151923 !important;
    color: #697386;
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.03);
}
.booking-seat-page .seat-button.booked::after,
.booking-seat-page .seat-button.maintenance::after,
.booking-seat-page .seat-button.locked::after {
    content: "×";
    position: absolute;
    inset: 0;
    display: grid;
    place-items: center;
    color: #7b8496;
    font-size: 19px;
    font-weight: 950;
}
.booking-seat-page .seat-button.booked span,
.booking-seat-page .seat-button.maintenance span,
.booking-seat-page .seat-button.locked span {
    opacity: 0;
}
.booking-seat-page .seat-button--couple {
    width: 100px !important;
    flex-basis: 100px !important;
}
.booking-seat-page .seat-couple-label {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
}
.booking-seat-page .seat-tooltip {
    pointer-events: none;
    position: absolute;
    bottom: calc(100% + 10px);
    left: 50%;
    z-index: 30;
    display: grid;
    min-width: 160px;
    gap: 3px;
    padding: 10px 12px;
    border: 1px solid rgba(247, 184, 75, 0.5);
    border-radius: 14px;
    background: rgba(10, 13, 20, 0.96);
    box-shadow: 0 14px 34px rgba(0, 0, 0, 0.42);
    opacity: 0;
    text-align: left;
    transform: translateX(-50%) translateY(7px);
}
.booking-seat-page .seat-tooltip strong {
    color: #ffffff;
    font-size: 14px;
}
.booking-seat-page .seat-tooltip span,
.booking-seat-page .seat-tooltip small {
    color: var(--cinema-muted);
    font-size: 12px;
}
.booking-seat-page .seat-wrapper:hover .seat-tooltip {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
}
.booking-seat-page .booking-seat-legend {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 16px;
    padding: 16px 20px 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    color: #d6deeb;
    font-size: 13px;
    font-weight: 800;
}
.booking-seat-page .booking-seat-legend span,
.booking-seat-page .booking-seat-price-list span {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.booking-seat-page .seat-swatch {
    display: inline-flex;
    flex: 0 0 auto;
    width: 16px;
    height: 16px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 5px;
}
.booking-seat-page .seat-swatch.is-empty {
    background: #4b5563;
}
.booking-seat-page .seat-swatch.is-vip,
.booking-seat-page .seat-swatch.is-selected {
    background: linear-gradient(135deg, #ffe29b, var(--cinema-gold));
}
.booking-seat-page .seat-swatch.is-couple {
    width: 28px;
    background: linear-gradient(135deg, #f59e0b, #ef4444);
}
.booking-seat-page .seat-swatch.is-locked {
    background: #151923;
}
.booking-seat-page .booking-seat-order-card {
    display: grid;
    gap: 16px;
    padding: 20px;
    color: #cbd4e4;
}
.booking-seat-page .booking-order-card-head span,
.booking-seat-page .booking-order-section h3,
.booking-seat-page .booking-seat-total-card span {
    display: block;
    margin-bottom: 8px;
    color: var(--cinema-muted);
    font-size: 11px;
    font-weight: 950;
    letter-spacing: 0.16em;
    text-transform: uppercase;
}
.booking-seat-page .booking-order-section {
    display: grid;
    gap: 12px;
    padding: 16px 0;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}
.booking-seat-page .booking-seat-price-list {
    display: grid;
    gap: 10px;
}
.booking-seat-page .booking-seat-price-list > div,
.booking-seat-page .booking-order-line {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    color: #d6deeb;
    font-size: 14px;
}
.booking-seat-page .booking-seat-price-list strong,
.booking-seat-page .booking-order-line strong {
    color: #ffffff;
    font-weight: 950;
    text-align: right;
}
.booking-seat-page #seatLabels {
    max-width: 190px;
    overflow-wrap: anywhere;
}
.booking-seat-page .booking-selected-list,
.booking-seat-page #selected-list {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
    min-height: 34px;
    color: var(--cinema-muted);
    font-size: 13px;
}
.booking-seat-page .booking-seat-empty-selection {
    display: inline-flex;
    align-items: center;
    min-height: 34px;
    color: var(--cinema-muted);
}
.booking-seat-page .booking-seat-chip {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    min-height: 34px;
    padding: 0 11px;
    border: 1px solid rgba(247, 184, 75, 0.36);
    border-radius: 999px;
    background: rgba(247, 184, 75, 0.14);
    color: #ffe29b;
    cursor: pointer;
    font-size: 12px;
    font-weight: 950;
}
.booking-seat-page .booking-seat-chip:hover {
    border-color: rgba(255, 255, 255, 0.28);
    background: rgba(229, 9, 20, 0.18);
    color: #ffffff;
}
.booking-seat-page .booking-seat-total-card {
    display: grid;
    gap: 5px;
    padding: 18px;
    border: 1px solid rgba(247, 184, 75, 0.28);
    border-radius: 22px;
    background: rgba(247, 184, 75, 0.1);
    text-align: center;
}
.booking-seat-page #totalPrice {
    color: var(--cinema-gold) !important;
}
.booking-seat-page .booking-seat-total-card strong {
    font-size: 34px;
    font-weight: 950;
    line-height: 1;
}
.booking-seat-page .booking-seat-total-card small {
    color: var(--cinema-muted);
    font-size: 12px;
}
.booking-seat-page #btnFood,
.booking-seat-page .booking-seat-primary-cta {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    min-height: 52px;
    width: 100%;
    border: 0;
    border-radius: 17px;
    background: linear-gradient(135deg, var(--cinema-red), #ff563a) !important;
    color: #ffffff !important;
    cursor: pointer;
    font-weight: 950;
    letter-spacing: 0.04em;
    text-decoration: none;
    box-shadow: 0 18px 36px rgba(229, 9, 20, 0.24);
}
.booking-seat-page #btnFood:disabled,
.booking-seat-page .booking-seat-primary-cta:disabled {
    cursor: not-allowed;
    opacity: 0.38;
    box-shadow: none;
}
.booking-seat-page #btnFood:not(:disabled):hover,
.booking-seat-page .booking-seat-primary-cta:not(:disabled):hover {
    transform: translateY(-2px);
    box-shadow: 0 24px 44px rgba(229, 9, 20, 0.34);
}
.booking-seat-page .booking-seat-secondary-link {
    width: 100%;
}

/* Responsive — thu gọn 3 cột về 1 cột trên màn hình quầy hẹp hơn */
@media (max-width: 1400px) {
    .booking-seat-page .booking-seat-layout {
        grid-template-columns: minmax(230px, 270px) minmax(0, 1fr) minmax(300px, 340px);
    }
}

@media (max-width: 1100px) {
    .booking-seat-page .booking-seat-hero {
        grid-template-columns: 1fr;
    }
    .booking-seat-page .booking-stepper {
        grid-template-columns: repeat(4, 1fr);
    }
    .booking-seat-page .booking-step strong {
        display: none;
    }
    .booking-seat-page .booking-seat-layout {
        grid-template-columns: minmax(0, 1fr) minmax(300px, 340px);
    }
    .booking-seat-page .booking-seat-movie-card {
        display: none;
    }
}

@media (max-width: 760px) {
    .booking-seat-page .booking-seat-layout {
        grid-template-columns: 1fr;
    }
    .booking-seat-page .booking-seat-order-card {
        position: static;
    }
    .booking-seat-page .booking-seat-toolbar {
        flex-direction: column;
        align-items: flex-start;
    }
    .booking-seat-page .booking-seat-timer {
        width: 100%;
        text-align: left;
    }
    .booking-seat-page .booking-seat-timer span,
    .booking-seat-page .booking-seat-map-panel #countdown {
        justify-content: flex-start;
    }
    .booking-seat-page .seat-button {
        width: 40px;
        height: 38px;
    }
    .booking-seat-page .seat-button--couple {
        width: 84px !important;
        flex-basis: 84px !important;
    }
}
