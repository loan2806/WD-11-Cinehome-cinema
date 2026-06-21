@extends('layouts.staff')

@section('title', 'Chọn ghế bán vé')
@section('page-title', 'Chọn ghế bán vé')

@section('content')
<div class="seat-sale-page">
    <div class="page-header">
        <div>
            <h2>Chọn ghế bán vé</h2>
            <p>Click ghế trống để chọn, hệ thống tự tính tổng tiền trước khi tạo vé.</p>
        </div>

        <a href="{{ route('staff.ban-ve.index') }}" class="btn-back">
            <i class="fa-solid fa-arrow-left"></i>
            Quay lại
        </a>
    </div>

    @if (session('error'))
        <div class="alert-box alert-error">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="showtime-info">
        <div>
            <span>Phim</span>
            <strong>{{ $suatChieu->phim->ten_phim ?? 'Không rõ phim' }}</strong>
        </div>

        <div>
            <span>Rạp</span>
            <strong>{{ $suatChieu->rapChieuPhim->ten_rap ?? 'Không rõ rạp' }}</strong>
        </div>

        <div>
            <span>Phòng</span>
            <strong>{{ $suatChieu->phongChieu->ten_phong ?? 'Phòng chiếu' }}</strong>
        </div>

        <div>
            <span>Giá vé</span>
            <strong id="ticketPriceText">{{ number_format($suatChieu->gia_ve, 0, ',', '.') }}đ</strong>
        </div>
    </div>

    <form action="{{ route('staff.ban-ve.store', $suatChieu->id) }}" method="POST" id="seatSaleForm">
        @csrf

        <div id="selectedSeatsContainer"></div>

        <div class="seat-layout">
            <div class="seat-card">
                <div class="screen-box">Màn hình</div>

                <div class="seat-legend">
                    <div><span class="legend available"></span> Ghế trống</div>
                    <div><span class="legend selected"></span> Đang chọn</div>
                    <div><span class="legend sold"></span> Đã bán</div>
                    <div><span class="legend maintenance"></span> Bảo trì</div>
                </div>

                @if ($seatsByRow->count())
                    <div class="seat-map">
                        @foreach ($seatsByRow as $rowName => $seats)
                            <div class="seat-row">
                                <div class="row-name">{{ $rowName }}</div>

                                <div class="seat-list">
                                    @foreach ($seats as $seat)
                                        @php
                                            $seatCode = strtoupper(trim($seat->ma_ghe));
                                            $isSold = $soldSeatCodes->contains($seatCode);
                                            $isMaintenance = $seat->trang_thai === 'bao_tri' || in_array($seatCode, $maintenanceSeatCodes, true);
                                        @endphp

                                        <button
                                            type="button"
                                            class="seat-btn {{ $isSold ? 'is-sold' : '' }} {{ $isMaintenance ? 'is-maintenance' : '' }}"
                                            data-seat="{{ $seatCode }}"
                                            {{ $isSold || $isMaintenance ? 'disabled' : '' }}
                                            title="{{ $isMaintenance ? 'Ghế đang bảo trì' : ($isSold ? 'Đã bán' : 'Ghế trống') }}"
                                        >
                                            {{ $seat->ma_ghe }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-box">
                        <i class="fa-solid fa-chair"></i>
                        <p>Phòng chiếu này chưa có dữ liệu ghế.</p>
                    </div>
                @endif
            </div>

            <div class="summary-card">
                <h3>Thông tin vé</h3>

                <div class="summary-row">
                    <span>Ghế đã chọn</span>
                    <strong id="selectedSeatText">Chưa chọn</strong>
                </div>

                <div class="summary-row">
                    <span>Số lượng</span>
                    <strong id="seatCountText">0</strong>
                </div>

                <div class="summary-row">
                    <span>Tổng tiền</span>
                    <strong id="totalPriceText">0đ</strong>
                </div>

                @error('seats')
                    <div class="field-error">{{ $message }}</div>
                @enderror

                <button type="submit" class="btn-create-ticket">
                    <i class="fa-solid fa-circle-check"></i>
                    Xác nhận bán vé
                </button>

                <p class="summary-note">
                    Sau khi xác nhận, vé sẽ được lưu là vé tại quầy và trạng thái đã thanh toán.
                </p>
            </div>
        </div>
    </form>
</div>

<style>
    .seat-sale-page { animation: fadeIn .35s ease; }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .page-header h2 {
        margin: 0;
        color: #fff;
        font-size: 30px;
        font-weight: 900;
    }

    .page-header p {
        margin-top: 8px;
        color: #aaa;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: 18px;
        padding: 12px 18px;
        color: #ddd;
        text-decoration: none;
        background: rgba(255,255,255,.08);
        transition: all .28s ease;
        font-weight: 800;
    }

    .btn-back:hover {
        color: #fff;
        transform: translateY(-3px);
        background: rgba(255,255,255,.14);
    }

    .alert-box {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 18px;
        padding: 15px 18px;
        border-radius: 20px;
        font-weight: 700;
    }

    .alert-error {
        color: #ffcccc;
        background: rgba(239,68,68,.13);
        border: 1px solid rgba(239,68,68,.35);
    }

    .showtime-info {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 22px;
    }

    .showtime-info div,
    .seat-card,
    .summary-card {
        background: linear-gradient(145deg, #171717, #101010);
        border: 1px solid rgba(245,166,35,.26);
        border-radius: 28px;
        box-shadow: 0 20px 55px rgba(0,0,0,.28);
        transition: all .32s ease;
    }

    .showtime-info div {
        padding: 18px;
    }

    .showtime-info div:hover,
    .seat-card:hover,
    .summary-card:hover {
        transform: translateY(-4px);
        border-color: rgba(245,166,35,.65);
        box-shadow: 0 26px 70px rgba(0,0,0,.45), 0 0 28px rgba(245,166,35,.12);
    }

    .showtime-info span {
        display: block;
        color: #aaa;
        margin-bottom: 6px;
        font-size: 13px;
    }

    .showtime-info strong {
        color: #fff;
        font-weight: 900;
    }

    .seat-layout {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 24px;
        align-items: start;
    }

    .seat-card,
    .summary-card {
        padding: 26px;
    }

    .screen-box {
        max-width: 680px;
        margin: 0 auto 26px;
        height: 46px;
        border-radius: 0 0 50px 50px;
        display: grid;
        place-items: center;
        color: #f5a623;
        font-weight: 900;
        letter-spacing: 2px;
        text-transform: uppercase;
        background: linear-gradient(180deg, rgba(245,166,35,.28), rgba(245,166,35,.04));
        border: 1px solid rgba(245,166,35,.28);
        box-shadow: 0 18px 50px rgba(245,166,35,.12);
    }

    .seat-legend {
        display: flex;
        justify-content: center;
        gap: 18px;
        flex-wrap: wrap;
        margin-bottom: 28px;
        color: #ddd;
        font-weight: 700;
    }

    .seat-legend div {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .legend {
        width: 18px;
        height: 18px;
        border-radius: 6px;
        display: inline-block;
    }

    .legend.available { background: #22c55e; }
    .legend.selected { background: #f5a623; }
    .legend.sold { background: #ef4444; }
    .legend.maintenance { background: #94a3b8; }

    .seat-map {
        display: grid;
        gap: 14px;
        overflow-x: auto;
        padding-bottom: 8px;
    }

    .seat-row {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .row-name {
        width: 42px;
        height: 42px;
        flex-shrink: 0;
        display: grid;
        place-items: center;
        border-radius: 14px;
        color: #f5a623;
        font-weight: 900;
        background: rgba(245,166,35,.1);
        border: 1px solid rgba(245,166,35,.25);
    }

    .seat-list {
        display: flex;
        gap: 10px;
        flex-wrap: nowrap;
    }

    .seat-btn {
        width: 54px;
        height: 44px;
        border: none;
        border-radius: 15px 15px 10px 10px;
        color: #fff;
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
        background: linear-gradient(135deg, #15803d, #22c55e);
        box-shadow: 0 10px 22px rgba(34,197,94,.15);
        transition: all .25s ease;
    }

    .seat-btn:hover {
        transform: translateY(-4px) scale(1.06);
        box-shadow: 0 16px 34px rgba(34,197,94,.28);
    }

    .seat-btn.is-selected {
        background: linear-gradient(135deg, #d89227, #f5a623);
        box-shadow: 0 16px 34px rgba(245,166,35,.28);
    }

    .seat-btn.is-sold {
        background: linear-gradient(135deg, #991b1b, #ef4444);
        cursor: not-allowed;
        opacity: .85;
    }

    .seat-btn.is-maintenance {
        background: linear-gradient(135deg, #475569, #94a3b8);
        cursor: not-allowed;
        opacity: .75;
    }

    .summary-card {
        position: sticky;
        top: 98px;
    }

    .summary-card h3 {
        margin: 0 0 20px;
        color: #fff;
        font-size: 22px;
        font-weight: 900;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        padding: 14px 0;
        border-bottom: 1px solid rgba(255,255,255,.08);
    }

    .summary-row span {
        color: #aaa;
    }

    .summary-row strong {
        color: #fff;
        text-align: right;
    }

    #totalPriceText {
        color: #f5a623;
        font-size: 22px;
    }

    .btn-create-ticket {
        width: 100%;
        margin-top: 22px;
        border: none;
        border-radius: 20px;
        padding: 15px 18px;
        color: #fff;
        font-weight: 900;
        cursor: pointer;
        background: linear-gradient(135deg, #d89227, #f5a623);
        box-shadow: 0 12px 28px rgba(245,166,35,.18);
        transition: all .28s ease;
    }

    .btn-create-ticket:hover {
        transform: translateY(-3px) scale(1.01);
        box-shadow: 0 18px 42px rgba(245,166,35,.32);
        filter: brightness(1.06);
    }

    .summary-note {
        margin: 14px 0 0;
        color: #888;
        font-size: 14px;
        line-height: 1.5;
    }

    .field-error {
        color: #ffb4b4;
        margin-top: 12px;
        font-size: 14px;
    }

    .empty-box {
        min-height: 220px;
        display: grid;
        place-items: center;
        text-align: center;
        color: #888;
    }

    .empty-box i {
        font-size: 46px;
        color: rgba(245,166,35,.45);
        margin-bottom: 12px;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 1200px) {
        .seat-layout { grid-template-columns: 1fr; }
        .summary-card { position: static; }
    }

    @media (max-width: 900px) {
        .showtime-info { grid-template-columns: 1fr 1fr; }
    }

    @media (max-width: 650px) {
        .showtime-info { grid-template-columns: 1fr; }
    }
</style>

<script>
    const ticketPrice = Number(@json((float) $suatChieu->gia_ve));
    const selectedSeats = new Set();

    const selectedSeatsContainer = document.getElementById('selectedSeatsContainer');
    const selectedSeatText = document.getElementById('selectedSeatText');
    const seatCountText = document.getElementById('seatCountText');
    const totalPriceText = document.getElementById('totalPriceText');

    function formatMoney(value) {
        return new Intl.NumberFormat('vi-VN').format(value) + 'đ';
    }

    function renderSelectedSeats() {
        selectedSeatsContainer.innerHTML = '';

        selectedSeats.forEach((seat) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'seats[]';
            input.value = seat;
            selectedSeatsContainer.appendChild(input);
        });

        const seats = Array.from(selectedSeats);

        selectedSeatText.textContent = seats.length ? seats.join(', ') : 'Chưa chọn';
        seatCountText.textContent = seats.length;
        totalPriceText.textContent = formatMoney(seats.length * ticketPrice);
    }

    document.querySelectorAll('.seat-btn:not(:disabled)').forEach((button) => {
        button.addEventListener('click', () => {
            const seat = button.dataset.seat;

            if (selectedSeats.has(seat)) {
                selectedSeats.delete(seat);
                button.classList.remove('is-selected');
            } else {
                selectedSeats.add(seat);
                button.classList.add('is-selected');
            }

            renderSelectedSeats();
        });
    });

    document.getElementById('seatSaleForm').addEventListener('submit', function (event) {
        if (selectedSeats.size === 0) {
            event.preventDefault();
            alert('Vui lòng chọn ít nhất một ghế trước khi bán vé.');
        }
    });

    renderSelectedSeats();
</script>
@endsection