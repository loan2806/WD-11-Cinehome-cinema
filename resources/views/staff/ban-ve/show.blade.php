@extends('layouts.admin')

@section('title','Chọn ghế bán vé')
@section('page-title','Chọn ghế bán vé')

@section('content')

@php
$poster=$suatChieu->phim->poster??null;
$posterUrl=$poster?asset('storage/movies/'.$poster):'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=1200';
$weekdayLabels=['CN','T2','T3','T4','T5','T6','T7'];
$showDate=$suatChieu->thoi_gian_chieu;
$totalSeats=$seatsByRow->flatten()->count();
$availableSeats=$totalSeats-$soldSeatCodes->count()-count($maintenanceSeatCodes);
@endphp

<div class="dat-ve-page booking-seat-page">
    {{-- Top Hero --}}
    <section class="booking-seat-hero">
        <div class="booking-flow-hero-copy">
            <span class="booking-eyebrow">
                <i class="fa-solid fa-couch"></i>
                Nhiệp vụ quầy vé
            </span>
            <h1>Chọn ghế hệ thống</h1>
            <p>
                <strong>{{$suatChieu->phim->ten_phim}}</strong> |
                Suất: <strong>{{$suatChieu->thoi_gian_chieu->format('H:i')}}</strong> -
                Phòng: <strong>{{$suatChieu->phongChieu->ten_phong??'Không rõ'}}</strong>
            </p>
            <div class="booking-seat-mini-stats">
                <div>
                    <strong>{{$availableSeats}}</strong>
                    <span>Ghế trống</span>
                </div>
                <div>
                    <strong>{{$soldSeatCodes->count()}}</strong>
                    <span>Đã bán</span>
                </div>
                <div>
                    <strong>{{number_format($suatChieu->gia_ve,0,',','.')}}đ</strong>
                    <span>Giá vé gốc</span>
                </div>
            </div>
        </div>

        <div class="booking-steps-flow">
            <div class="step-item completed">
                <span class="step-icon"><i class="fa-solid fa-check"></i></span>
                <span class="step-text">Chọn phim</span>
            </div>
            <div class="step-item active">
                <span class="step-number">2</span>
                <span class="step-text">Chọn ghế</span>
            </div>
            <div class="step-item">
                <span class="step-number">3</span>
                <span class="step-text">Đồ ăn</span>
            </div>
            <div class="step-item">
                <span class="step-number">4</span>
                <span class="step-text">Thanh toán</span>
            </div>
        </div>
    </section>

    {{-- Main Layout 3 Cột --}}
    <div class="booking-seat-layout-admin">

        {{-- Cột 1: Thông tin phim --}}
        <aside class="booking-seat-movie-card">
            <div class="booking-seat-poster-wrap">
                <img src="{{$posterUrl}}" alt="{{$suatChieu->phim->ten_phim}}">
                <div class="booking-seat-poster-overlay">
                    <span>CineHome Staff</span>
                    <strong>{{$suatChieu->phim->ten_phim}}</strong>
                </div>
            </div>

            <div class="booking-seat-info-card">
                <h2>{{$suatChieu->phim->ten_phim}}</h2>
                <dl>
                    <div>
                        <dt><i class="fa-solid fa-location-dot"></i> Rạp</dt>
                        <dd>{{$suatChieu->rapChieuPhim->ten_rap??'Không rõ'}}</dd>
                    </div>
                    <div>
                        <dt><i class="fa-solid fa-door-open"></i> Phòng</dt>
                        <dd>{{$suatChieu->phongChieu->ten_phong??'Không rõ'}}</dd>
                    </div>
                    <div>
                        <dt><i class="fa-solid fa-calendar-days"></i> Ngày</dt>
                        <dd>{{$weekdayLabels[$showDate->dayOfWeek]}}, {{$showDate->format('d/m/Y')}}</dd>
                    </div>
                    <div>
                        <dt><i class="fa-solid fa-clock"></i> Giờ</dt>
                        <dd>{{$showDate->format('H:i')}}</dd>
                    </div>
                </dl>
            </div>

            <a href="{{route('staff.ban-ve.index')}}" class="booking-seat-back-link">
                <i class="fa-solid fa-arrow-left"></i> Đổi suất chiếu
            </a>
        </aside>

        {{-- Cột 2: Sơ đồ chọn ghế --}}
        <section class="booking-seat-map-panel" aria-label="Sơ đồ chọn ghế">
            <div class="booking-seat-toolbar">
                <div>
                    <span class="booking-eyebrow"><i class="fa-solid fa-ticket"></i> Sơ đồ phòng</span>
                    <h2>Vị trí ghế ngồi</h2>
                </div>
                <div class="booking-seat-timer">
                    <span><i class="fa-regular fa-clock"></i> Thời gian giữ ghế</span>
                    <strong id="countdown">07:00</strong>
                </div>
            </div>

            <div class="booking-theater">
                <div class="booking-screen-wrap">
                    <div class="screen-line"></div>
                    <span>MÀN HÌNH</span>
                </div>

                <div class="booking-seat-scroll">
                    <div class="booking-seat-grid">
                        @foreach($seatsByRow as $hang=>$cacGhe)
                        <div class="seat-row">
                            <span class="row-label">{{$hang}}</span>
                            <div class="seat-list">
                                @foreach($cacGhe as $ghe)
                                @php
                                $seatCode=strtoupper(trim($ghe->ma_ghe));
                                $isBooked=$soldSeatCodes->contains($seatCode);
                                $isMaintenance=in_array($seatCode,$maintenanceSeatCodes,true);

                                $seatType=strtolower($ghe->loaiGhe->ten_loai??'thường');
                                if(str_contains($seatType,'vip')) {
                                $seatClass='seat-vip';
                                $seatPrice=120000;
                                } elseif(str_contains($seatType,'đôi')||str_contains($seatType,'doi')||str_contains($seatType,'couple')) {
                                $seatClass='seat-couple';
                                $seatPrice=$ghe->gia ?? 200000;
                                } else {
                                $seatClass='seat-normal';
                                $seatPrice=100000;
                                }
                                @endphp
                                <div class="seat-wrapper">
                                    <button type="button" class="seat-button {{$seatClass}} {{$isBooked?'booked':''}} {{$isMaintenance?'maintenance':''}}" data-seat="{{$seatCode}}" data-price="{{$seatPrice}}" @if($isBooked||$isMaintenance) disabled @endif>
                                        <span>{{$seatCode}}</span>
                                    </button>
                                </div>
                                @endforeach
                            </div>
                            <span class="row-label">{{$hang}}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="booking-seat-legend">
                    <span><i class="seat-swatch is-empty"></i> Ghế thường</span>
                    <span><i class="seat-swatch is-vip"></i> Ghế VIP</span>
                    <span><i class="seat-swatch is-couple"></i> Ghế Đôi</span>
                    <span><i class="seat-swatch is-selected"></i> Đang chọn</span>
                    <span><i class="seat-swatch is-locked"></i> Đã bán / Bảo trì</span>
                </div>
            </div>
        </section>

        {{-- Cột 3: Tóm tắt đơn hàng --}}
        <aside class="booking-seat-order-card">
            <div class="booking-order-card-head">
                <span>Quầy bán vé</span>
                <h2>Thông tin chọn</h2>
            </div>

            <div class="booking-seat-price-info">
                <span class="price-info-title">LOẠI GHẾ</span>
                <div class="price-info-row">
                    <span><i class="price-dot is-empty"></i> Thường</span>
                    <strong>100.000đ</strong>
                </div>
                <div class="price-info-row">
                    <span><i class="price-dot is-vip"></i> VIP</span>
                    <strong>120.000đ</strong>
                </div>
            </div>

            <div class="booking-order-section">
                <h3>Vé đã chọn</h3>
                <div class="booking-order-line">
                    <span>Số lượng:</span>
                    <strong id="seatCount">0 ghế</strong>
                </div>
                <div class="booking-order-line">
                    <span>Vị trí:</span>
                    <strong id="selectedSeatText">—</strong>
                </div>
                <div id="selectedList" class="booking-selected-list">
                    <span class="booking-seat-empty-selection">Chưa chọn ghế</span>
                </div>
            </div>

            <div class="booking-seat-total-card">
                <span>Tổng thanh toán</span>
                <strong id="totalPrice">0đ</strong>
                <p style="font-size: 11px; color: #555; margin-top: 5px;">Chưa bao gồm đồ ăn và voucher</p>
            </div>

            <form id="seatSaleForm" action="{{route('staff.ban-ve.food',$suatChieu->id)}}" method="POST">
                @csrf
                <div id="selectedSeatsContainer"></div>
                <button type="submit" id="btnFood" class="booking-seat-primary-cta" disabled>
                    Tiếp tục chọn đồ ăn <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>
        </aside>

    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const selectedContainer = document.getElementById("selectedSeatsContainer");
        const selectedText = document.getElementById("selectedSeatText");
        const seatCount = document.getElementById("seatCount");
        const totalPrice = document.getElementById("totalPrice");
        const selectedList = document.getElementById("selectedList");
        const btnFood = document.getElementById("btnFood");
        const seats = document.querySelectorAll(".seat-button:not(:disabled)");
        let selected = {};

        function money(value) {
            return Number(value || 0).toLocaleString("vi-VN") + "đ";
        }

        function render() {
            let list = Object.keys(selected);
            let total = 0;
            list.forEach(seat => total += selected[seat]);

            selectedContainer.innerHTML = "";
            if (list.length) {
                let input = document.createElement("input");
                input.type = "hidden";
                input.name = "seats";
                input.value = list.join(",");
                selectedContainer.appendChild(input);
            }

            seatCount.innerText = list.length + " ghế";
            selectedText.innerText = list.length ? list.join(", ") : "—";
            totalPrice.innerText = money(total);

            if (list.length) {
                selectedList.innerHTML = list.map(seat => `
                    <button type="button" class="booking-seat-chip" data-remove="${seat}">
                        ${seat} <i class="fa-solid fa-xmark"></i>
                    </button>
                `).join("");
            } else {
                selectedList.innerHTML = `<span class="booking-seat-empty-selection">Chưa chọn ghế</span>`;
            }
            btnFood.disabled = list.length === 0;
        }

        seats.forEach(seat => {
            seat.addEventListener("click", function() {
                let code = this.dataset.seat;
                let price = Number(this.dataset.price || 0);

                if (selected[code]) {
                    delete selected[code];
                    this.classList.remove("selected");
                } else {
                    if (Object.keys(selected).length >= 8) {
                        alert("Chỉ được chọn tối đa 8 ghế");
                        return;
                    }
                    selected[code] = price;
                    this.classList.add("selected");
                }
                render();
            });
        });

        selectedList.addEventListener("click", function(e) {
            let btn = e.target.closest("[data-remove]");
            if (!btn) return;
            let seat = btn.dataset.remove;
            delete selected[seat];
            let seatBtn = document.querySelector(`.seat-button[data-seat="${seat}"]`);
            if (seatBtn) seatBtn.classList.remove("selected");
            render();
        });

        const countdownEl = document.getElementById("countdown");
        let duration = 7 * 60;
        const timer = setInterval(function() {
            let minutes = Math.floor(duration / 60);
            let seconds = duration % 60;
            countdownEl.textContent = (minutes < 10 ? "0" : "") + minutes + ":" + (seconds < 10 ? "0" : "") + seconds;
            if (--duration < 0) {
                clearInterval(timer);
                alert("Hết thời gian giữ ghế!");
                window.location.reload();
            }
        }, 1000);
    });

</script>
@endpush

@push('styles')
<style>
    .dat-ve-page {
        color: #fff;
        animation: fadeIn .3s ease;
    }

    .booking-seat-layout-admin {
        display: grid;
        grid-template-columns: 290px 1fr 320px;
        gap: 20px;
        align-items: start;
    }

    .booking-seat-hero {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #121212;
        border: 1px solid rgba(255, 255, 255, .08);
        border-radius: 20px;
        padding: 24px 30px;
        margin-bottom: 20px;
    }

    .booking-flow-hero-copy {
        flex: 1;
    }

    .booking-steps-flow {
        display: flex;
        flex-direction: column;
        gap: 10px;
        width: 260px;
    }

    .step-item {
        display: flex;
        align-items: center;
        gap: 12px;
        background: rgba(255, 255, 255, 0.04);
        padding: 8px 14px;
        border-radius: 8px;
        color: #444;
    }

    .step-item.completed {
        background: rgba(46, 213, 115, 0.1);
        color: #2ed573;
    }

    .step-item.active {
        background: linear-gradient(90deg, #e50914, #b20710);
        color: #fff;
        font-weight: bold;
    }

    .step-icon,
    .step-number {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
    }

    .step-item.completed .step-icon {
        background: #2ed573;
        color: #fff;
    }

    .step-text {
        font-size: 13px;
    }

    .booking-eyebrow {
        color: #f4c56a;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .booking-seat-hero h1 {
        font-size: 28px;
        font-weight: 900;
        margin: 10px 0;
    }

    .booking-seat-mini-stats {
        display: flex;
        gap: 15px;
        margin-top: 15px;
    }

    .booking-seat-mini-stats div {
        background: #1a1a1a;
        border-radius: 12px;
        padding: 10px 20px;
        text-align: center;
    }

    .booking-seat-mini-stats strong {
        display: block;
        color: #f4c56a;
        font-size: 18px;
    }

    .booking-seat-mini-stats span {
        color: #888;
        font-size: 11px;
    }

    .booking-seat-movie-card,
    .booking-seat-map-panel,
    .booking-seat-order-card {
        background: #121212;
        border: 1px solid rgba(255, 255, 255, .08);
        border-radius: 20px;
        padding: 20px;
    }

    .booking-seat-poster-wrap {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
    }

    .booking-seat-poster-wrap img {
        width: 100%;
        height: 380px;
        object-fit: cover;
    }

    .booking-seat-poster-overlay {
        position: absolute;
        bottom: 0;
        padding: 15px;
        background: linear-gradient(transparent, #000);
        width: 100%;
    }

    .booking-seat-info-card {
        background: #1a1a1a;
        padding: 15px;
        border-radius: 12px;
        margin-top: 15px;
    }

    .booking-seat-info-card h2 {
        color: #f4c56a;
        font-size: 16px;
        margin-bottom: 10px;
    }

    .booking-seat-info-card dl div {
        display: flex;
        justify-content: space-between;
        font-size: 13px;
        margin: 10px 0;
    }

    .booking-seat-info-card dt {
        color: #888;
    }

    .booking-seat-info-card dd {
        font-weight: bold;
        color: #fff;
    }

    .booking-seat-back-link {
        display: block;
        text-align: center;
        margin-top: 15px;
        color: #aaa;
        text-decoration: none;
        font-size: 13px;
    }

    .booking-seat-back-link:hover {
        color: #f4c56a;
    }

    .booking-seat-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .booking-seat-toolbar h2 {
        font-size: 22px;
        margin: 5px 0;
    }

    .booking-seat-timer {
        background: #1a1a1a;
        padding: 10px 15px;
        border-radius: 12px;
        text-align: center;
    }

    .booking-seat-timer strong {
        display: block;
        color: #f4c56a;
        font-size: 22px;
    }

    .screen-line {
        width: 85%;
        height: 25px;
        margin: auto;
        border-radius: 50% 50% 0 0;
        background: linear-gradient(180deg, #f4c56a, transparent);
    }

    .booking-screen-wrap {
        text-align: center;
        margin-bottom: 30px;
    }

    .booking-screen-wrap span {
        color: #f4c56a;
        font-size: 12px;
        letter-spacing: 4px;
        font-weight: bold;
    }

    .booking-seat-scroll {
        overflow-x: auto;
        padding: 20px;
        background: #161616;
        border-radius: 12px;
    }

    .booking-seat-grid {
        display: flex;
        flex-direction: column;
        gap: 12px;
        align-items: center;
        min-width: max-content;
    }

    .seat-row {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .row-label {
        width: 25px;
        color: #d99a32;
        font-weight: bold;
        font-size: 14px;
        text-align: center;
    }

    .seat-list {
        display: flex;
        gap: 8px;
    }

    .seat-button {
        width: 40px;
        height: 38px;
        border: none;
        border-radius: 8px 8px 4px 4px;
        font-size: 10px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.2s;
    }

    .seat-button:hover:not(:disabled) {
        transform: translateY(-3px);
    }

    /* Ghế thường: Xám xanh đen */
    .seat-normal {
        background: #2b3139 !important;
        color: #7b8794 !important;
        border: 1px solid rgba(255, 255, 255, 0.05) !important;
    }

    .seat-normal:hover:not(:disabled) {
        background: #3c4450 !important;
        color: #ffffff !important;
    }

    /* Ghế VIP: Vàng hoàng gia chuẩn UI của bạn */
    .seat-vip {
        background: #e4a81e !important;
        color: #000000 !important;
    }

    .seat-vip:hover:not(:disabled) {
        background: #f5b92b !important;
        box-shadow: 0 0 10px rgba(228, 168, 30, 0.5) !important;
    }

    /* Ghế đôi: Đỏ đậm ấm cúng */
    .seat-couple {
        background: linear-gradient(135deg, #e50914, #b20710) !important;
        color: #ffffff !important;
        width: 88px;
    }

    .seat-couple:hover:not(:disabled) {
        background: linear-gradient(135deg, #ff1a26, #d60a15) !important;
    }

    /* Trạng thái ghế ĐANG CHỌN: Phát sáng lung linh giống ảnh mẫu khách hàng */
    .seat-button.selected {
        background: #ffffff !important;
        color: #000000 !important;
        font-weight: 900 !important;
        border: 1px solid rgba(255, 255, 255, 0.8) !important;

        /* Hiệu ứng bóng phát sáng tỏa lan ra xung quanh nền đen */
        box-shadow: 0 0 12px #f4c56a,
            0 0 25px rgba(244, 197, 106, 0.6),
            inset 0 0 4px rgba(255, 255, 255, 0.5) !important;

        text-shadow: 0px 0px 1px rgba(0, 0, 0, 0.5) !important;
        transform: translateY(-2px) scale(1.03) !important;
    }

    .seat-button.selected:hover {
        background: #ffffff !important;
        box-shadow: 0 0 18px #ffffff, 0 0 35px #f4c56a !important;
    }

    /* Trạng thái ghế đã bán/bảo trì: Ẩn tối tệp vào nền */
    .seat-button:disabled,
    .seat-button.booked,
    .seat-button.maintenance {
        background: #1c2024 !important;
        color: #4a525e !important;
        cursor: not-allowed;
        transform: none !important;
        box-shadow: none !important;
        border: none !important;
        opacity: 0.6;
    }

    /* Legend chú thích */
    .booking-seat-legend {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-top: 25px;
        font-size: 12px;
        flex-wrap: wrap;
    }

    .booking-seat-legend span {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #aaa;
    }

    .seat-swatch {
        width: 14px;
        height: 14px;
        border-radius: 4px;
    }

    .seat-swatch.is-empty {
        background: #2b3139;
    }

    .seat-swatch.is-vip {
        background: #e4a81e;
    }

    .seat-swatch.is-couple {
        background: #e50914;
    }

    .seat-swatch.is-selected {
        background: #fff;
        box-shadow: 0 0 8px #f4c56a;
    }

    .seat-swatch.is-locked {
        background: #1c2024;
    }

    /* Khối bảng giá bên cột 3 */
    .booking-seat-price-info {
        background: #1a1a1a;
        border-radius: 12px;
        padding: 15px;
        margin-top: 15px;
    }

    .price-info-title {
        display: block;
        font-size: 11px;
        color: #555;
        font-weight: bold;
        margin-bottom: 10px;
        letter-spacing: 1px;
    }

    .price-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        font-size: 13px;
    }

    .price-info-row:last-child {
        margin-bottom: 0;
    }

    .price-info-row span {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #aaa;
    }

    .price-dot {
        width: 10px;
        height: 10px;
        border-radius: 2px;
    }

    .price-dot.is-empty {
        background: #2b3139;
    }

    .price-dot.is-vip {
        background: #e4a81e;
    }

    /* Order Card Cột 3 */
    .booking-order-card-head h2 {
        color: #f4c56a;
        font-size: 20px;
        margin-top: 5px;
    }

    .booking-order-card-head span {
        color: #888;
        font-size: 11px;
        text-transform: uppercase;
    }

    .booking-order-section {
        background: #1a1a1a;
        padding: 15px;
        border-radius: 12px;
        margin-top: 15px;
    }

    .booking-order-section h3 {
        font-size: 12px;
        color: #888;
        margin-bottom: 10px;
        text-transform: uppercase;
    }

    .booking-order-line {
        display: flex;
        justify-content: space-between;
        font-size: 13px;
        padding: 10px 0;
        border-bottom: 1px solid #2d2d2d;
    }

    .booking-order-line span {
        color: #888;
    }

    .booking-selected-list {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 12px;
    }

    .booking-seat-chip {
        background: #f4c56a;
        color: #000;
        border: none;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: bold;
        cursor: pointer;
    }

    .booking-seat-total-card {
        background: rgba(244, 197, 106, 0.1);
        border: 1px solid rgba(244, 197, 106, 0.4);
        border-radius: 12px;
        padding: 18px;
        text-align: center;
        margin: 15px 0;
    }

    .booking-seat-total-card strong {
        display: block;
        font-size: 28px;
        color: #f4c56a;
        margin-top: 4px;
    }

    .booking-seat-primary-cta {
        width: 100%;
        background: #f4c56a;
        color: #000;
        border: none;
        padding: 14px;
        border-radius: 10px;
        font-weight: bold;
        cursor: pointer;
        transition: cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.2s;
    }

    .booking-seat-primary-cta:hover:not(:disabled) {
        transform: translateY(-2px);
    }

    .booking-seat-primary-cta:disabled {
        opacity: 0.3;
        cursor: not-allowed;
    }

    @media (max-width: 1300px) {
        .booking-seat-layout-admin {
            grid-template-columns: 1fr;
        }

        .booking-seat-movie-card {
            display: none;
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(8px);
        }

        to {
            opacity: 1;
            transform: none;
        }
    }

</style>
@endpush
