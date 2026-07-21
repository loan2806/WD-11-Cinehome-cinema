<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>In vé và hóa đơn {{ $ve->ma_ve }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 20px;
            background: #eeeeee;
            color: #111111;
            font-family: Arial, Helvetica, sans-serif;
        }

        .print-page {
            width: 80mm;
            margin: 0 auto 20px;
            padding: 11px;
            background: #ffffff;
        }

        .ticket {
            min-height: 125mm;
        }

        .invoice {
            min-height: 100mm;
        }

        .center {
            text-align: center;
        }

        .brand {
            font-size: 25px;
            font-weight: 900;
        }

        .cinema {
            margin-top: 3px;
            font-size: 12px;
        }

        .document-title {
            margin-top: 10px;
            font-size: 17px;
            font-weight: 900;
        }

        .divider {
            margin: 10px 0;
            border-top: 1px dashed #111111;
        }

        .double-divider {
            margin: 10px 0;
            border-top: 2px solid #111111;
        }

        .movie-name {
            margin: 10px 0;
            font-size: 17px;
            font-weight: 900;
            line-height: 1.35;
            text-align: center;
            text-transform: uppercase;
        }

        .row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
            padding: 4px 0;
            font-size: 13px;
        }

        .row>span:first-child {
            flex-shrink: 0;
        }

        .row strong {
            max-width: 65%;
            text-align: right;
            overflow-wrap: anywhere;
        }

        .seat-box {
            margin: 10px 0;
            padding: 10px;
            border: 2px solid #111111;
            text-align: center;
        }

        .seat-box .seat-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
        }

        .seat-box .seat-code {
            display: block;
            margin-top: 2px;
            font-size: 31px;
            font-weight: 900;
        }

        .seat-meta {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 6px;
            font-size: 11px;
            font-weight: 700;
        }

        .seat-type {
            padding: 3px 7px;
            border: 1px solid #111111;
            border-radius: 12px;
        }

        .seat-price {
            padding: 3px 7px;
            background: #111111;
            color: #ffffff;
            border-radius: 12px;
        }

        .qr-box {
            margin-top: 10px;
            text-align: center;
        }

        .qr-box svg {
            display: block;
            width: 42mm;
            height: 42mm;
            margin: 0 auto;
        }

        .qr-label {
            margin-top: 5px;
            font-size: 10px;
            font-weight: 700;
        }

        .ticket-code {
            margin-top: 3px;
            font-family: monospace;
            font-size: 10px;
            overflow-wrap: anywhere;
        }

        .note {
            font-size: 10px;
            line-height: 1.45;
            text-align: center;
        }

        .invoice-code {
            margin-top: 5px;
            font-family: monospace;
            font-size: 11px;
        }

        .section-title {
            margin: 10px 0 5px;
            font-size: 13px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .invoice-item {
            padding: 5px 0;
            border-bottom: 1px dotted #777777;
            font-size: 12px;
        }

        .invoice-item:last-child {
            border-bottom: 0;
        }

        .item-name {
            font-weight: 700;
        }

        .item-detail {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin-top: 2px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            padding: 5px 0;
            font-size: 13px;
        }

        .grand-total {
            padding: 9px 0;
            font-size: 17px;
            font-weight: 900;
        }

        .page-break {
            display: block;
            height: 0;
            break-after: page;
            page-break-after: always;
        }

        .actions {
            width: 80mm;
            margin: 15px auto;
            display: flex;
            gap: 8px;
        }

        .actions button,
        .actions a {
            flex: 1;
            padding: 11px;
            border: 0;
            border-radius: 6px;
            background: #111111;
            color: #ffffff;
            font-weight: 700;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
        }

        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }

            body {
                padding: 0;
                background: #ffffff;
            }

            .print-page {
                width: 80mm;
                margin: 0;
            }

            .ticket {
                min-height: 125mm;
            }

            .actions {
                display: none;
            }

            .page-break {
                display: block;
                height: 0;
                break-after: page;
                page-break-after: always;
            }
        }

    </style>
</head>

<body>
    @php
    $seatTickets = $ve->gheVes
    ->sortBy('ma_ghe')
    ->values();

    $foods = collect(
    $ve->foods_list
    ?? $ve->food_items
    ?? []
    );

    $foodTotal = (float) ($ve->food_total ?? 0);

    if ($foodTotal <= 0) { $foodTotal=$foods->sum(function ($food) {
        $price = (float) (
        $food['don_gia']
        ?? $food['price']
        ?? 0
        );

        $quantity = (int) (
        $food['so_luong']
        ?? $food['qty']
        ?? $food['quantity']
        ?? 1
        );

        return $price * $quantity;
        });
        }

        $seatTotal = (float) ($ve->seat_total ?? 0);

        if ($seatTotal <= 0) { $seatTotal=max( (float) $ve->tong_tien - $foodTotal,
            0
            );
            }

            $staffName = $ve->nhanVien->ho_ten
            ?? $ve->nhanVien->ten
            ?? 'Nhân viên';

            $baseTicketPrice = (float) (
            $ve->suatChieu->gia_ve ?? 0
            );
            @endphp

            @forelse ($seatTickets as $seatTicket)
            @php
            $normalizedSeatCode = strtoupper(
            trim((string) $seatTicket->ma_ghe)
            );

            $seat = $seatDetails->get($normalizedSeatCode);

            $seatType = $seat?->loaiGhe;

            $seatTypeName = $seatType?->ten_loai
            ?? $seatType?->ten_loai_ghe
            ?? $seatType?->name
            ?? null;

            $seatSurcharge = (float) (
            $seatType?->phu_thu ?? 0
            );

            $isCoupleSeat = (bool) (
            $seatType?->la_couple ?? false
            );

            if (!$seatTypeName) {
            if ($isCoupleSeat) {
            $seatTypeName = 'Ghế đôi';
            } elseif ($seatSurcharge > 0) {
            $seatTypeName = 'Ghế VIP';
            } else {
            $seatTypeName = 'Ghế thường';
            }
            }

            $seatPrice = $isCoupleSeat
            ? ($baseTicketPrice * 2) + $seatSurcharge
            : $baseTicketPrice + $seatSurcharge;
            @endphp

            <section class="print-page ticket">
                <div class="center">
                    <div class="brand">
                        CineHome
                    </div>

                    <div class="cinema">
                        {{ $ve->ten_rap }}
                    </div>

                    <div class="document-title">
                        VÉ XEM PHIM
                    </div>
                </div>

                <div class="divider"></div>

                <div class="movie-name">
                    {{ $ve->ten_phim }}
                </div>

                <div class="row">
                    <span>Ngày chiếu</span>

                    <strong>
                        {{ optional($ve->thoi_gian_chieu)->format('d/m/Y') }}
                    </strong>
                </div>

                <div class="row">
                    <span>Giờ chiếu</span>

                    <strong>
                        {{ optional($ve->thoi_gian_chieu)->format('H:i') }}
                    </strong>
                </div>

                <div class="row">
                    <span>Phòng</span>

                    <strong>
                        {{ $ve->ten_phong }}
                    </strong>
                </div>

                <div class="seat-box">
                    <span class="seat-label">
                        GHẾ
                    </span>

                    <strong class="seat-code">
                        {{ $seatTicket->ma_ghe }}
                    </strong>

                    <div class="seat-meta">
                        <span class="seat-type">
                            {{ $seatTypeName }}
                        </span>

                        <span class="seat-price">
                            {{ number_format($seatPrice, 0, ',', '.') }}đ
                        </span>
                    </div>
                </div>

                <div class="row">
                    <span>Loại ghế</span>

                    <strong>
                        {{ $seatTypeName }}
                    </strong>
                </div>

                <div class="row">
                    <span>Giá ghế</span>

                    <strong>
                        {{ number_format($seatPrice, 0, ',', '.') }}đ
                    </strong>
                </div>

                <div class="qr-box">
                    {!!
                    QrCode::format('svg')
                    ->size(190)
                    ->margin(1)
                    ->errorCorrection('M')
                    ->generate($seatTicket->ma_qr)
                    !!}

                    <div class="qr-label">
                        QUÉT MÃ KHI VÀO PHÒNG CHIẾU
                    </div>

                    <div class="ticket-code">
                        {{ $ve->ma_ve }}
                        /
                        {{ $seatTicket->ma_ghe }}
                    </div>
                </div>

                <div class="divider"></div>

                <div class="note">
                    Mỗi QR chỉ được sử dụng một lần.<br>
                    Vui lòng có mặt trước giờ chiếu ít nhất 10 phút.
                </div>
            </section>

            <div class="page-break"></div>
            @empty
            <section class="print-page ticket">
                <div class="center">
                    Không tìm thấy dữ liệu ghế để in.
                </div>
            </section>

            <div class="page-break"></div>
            @endforelse

            <section class="print-page invoice">
                <div class="center">
                    <div class="brand">
                        CineHome
                    </div>

                    <div class="cinema">
                        {{ $ve->ten_rap }}
                    </div>

                    <div class="document-title">
                        HÓA ĐƠN THANH TOÁN
                    </div>

                    <div class="invoice-code">
                        {{ $ve->ma_ve }}
                    </div>
                </div>

                <div class="divider"></div>

                <div class="row">
                    <span>Thời gian bán</span>

                    <strong>
                        {{ optional($ve->created_at)->format('d/m/Y H:i') }}
                    </strong>
                </div>

                <div class="row">
                    <span>Nhân viên</span>

                    <strong>
                        {{ $staffName }}
                    </strong>
                </div>

                <div class="row">
                    <span>Phim</span>

                    <strong>
                        {{ $ve->ten_phim }}
                    </strong>
                </div>

                <div class="row">
                    <span>Suất chiếu</span>

                    <strong>
                        {{ optional($ve->thoi_gian_chieu)->format('d/m/Y H:i') }}
                    </strong>
                </div>

                <div class="row">
                    <span>Phòng</span>

                    <strong>
                        {{ $ve->ten_phong }}
                    </strong>
                </div>

                <div class="row">
                    <span>Ghế</span>

                    <strong>
                        {{ str_replace(',', ', ', $ve->ma_ghe) }}
                    </strong>
                </div>

                <div class="divider"></div>

                <div class="section-title">
                    Chi tiết vé
                </div>

                @foreach ($seatTickets as $seatTicket)
                @php
                $normalizedSeatCode = strtoupper(
                trim((string) $seatTicket->ma_ghe)
                );

                $seat = $seatDetails->get($normalizedSeatCode);

                $seatType = $seat?->loaiGhe;

                $seatTypeName = $seatType?->ten_loai
                ?? $seatType?->ten_loai_ghe
                ?? $seatType?->name
                ?? null;

                $seatSurcharge = (float) (
                $seatType?->phu_thu ?? 0
                );

                $isCoupleSeat = (bool) (
                $seatType?->la_couple ?? false
                );

                if (!$seatTypeName) {
                if ($isCoupleSeat) {
                $seatTypeName = 'Ghế đôi';
                } elseif ($seatSurcharge > 0) {
                $seatTypeName = 'Ghế VIP';
                } else {
                $seatTypeName = 'Ghế thường';
                }
                }

                $seatPrice = $isCoupleSeat
                ? ($baseTicketPrice * 2) + $seatSurcharge
                : $baseTicketPrice + $seatSurcharge;
                @endphp

                <div class="invoice-item">
                    <div class="item-name">
                        Ghế {{ $seatTicket->ma_ghe }}
                        — {{ $seatTypeName }}
                    </div>

                    <div class="item-detail">
                        <span>1 vé</span>

                        <strong>
                            {{ number_format($seatPrice, 0, ',', '.') }}đ
                        </strong>
                    </div>
                </div>
                @endforeach

                @if ($foods->isNotEmpty())
                <div class="section-title">
                    Đồ ăn & combo
                </div>

                @foreach ($foods as $food)
                @php
                $foodName = $food['ten_mon']
                ?? $food['name']
                ?? 'Đồ ăn';

                $foodPrice = (float) (
                $food['don_gia']
                ?? $food['price']
                ?? 0
                );

                $foodQuantity = (int) (
                $food['so_luong']
                ?? $food['qty']
                ?? $food['quantity']
                ?? 1
                );
                @endphp

                <div class="invoice-item">
                    <div class="item-name">
                        {{ $foodName }}
                    </div>

                    <div class="item-detail">
                        <span>
                            {{ $foodQuantity }}
                            x
                            {{ number_format($foodPrice, 0, ',', '.') }}đ
                        </span>

                        <strong>
                            {{
                                number_format(
                                    $foodPrice * $foodQuantity,
                                    0,
                                    ',',
                                    '.'
                                )
                            }}đ
                        </strong>
                    </div>
                </div>
                @endforeach
                @endif

                <div class="divider"></div>

                <div class="total-row">
                    <span>Tiền vé</span>

                    <strong>
                        {{ number_format($seatTotal, 0, ',', '.') }}đ
                    </strong>
                </div>

                <div class="total-row">
                    <span>Tiền đồ ăn</span>

                    <strong>
                        {{ number_format($foodTotal, 0, ',', '.') }}đ
                    </strong>
                </div>

                <div class="total-row">
                    <span>Phương thức</span>

                    <strong>
                        {{
                    $ve->payment_method === 'vietqr'
                        ? 'VietQR'
                        : 'Tiền mặt'
                }}
                    </strong>
                </div>

                @if ($ve->payment_method !== 'vietqr')
                <div class="total-row">
                    <span>Khách đưa</span>

                    <strong>
                        {{
                        number_format(
                            (float) $ve->received_amount,
                            0,
                            ',',
                            '.'
                        )
                    }}đ
                    </strong>
                </div>

                <div class="total-row">
                    <span>Tiền thừa</span>

                    <strong>
                        {{
                        number_format(
                            (float) $ve->change_amount,
                            0,
                            ',',
                            '.'
                        )
                    }}đ
                    </strong>
                </div>
                @endif

                <div class="double-divider"></div>

                <div class="total-row grand-total">
                    <span>TỔNG CỘNG</span>

                    <strong>
                        {{
                    number_format(
                        (float) $ve->tong_tien,
                        0,
                        ',',
                        '.'
                    )
                }}đ
                    </strong>
                </div>

                <div class="double-divider"></div>

                <div class="note">
                    Cảm ơn quý khách đã sử dụng dịch vụ CineHome.<br>
                    Vui lòng giữ hóa đơn để được hỗ trợ khi cần thiết.
                </div>
            </section>

            <div class="actions">
                <button type="button" onclick="window.print()">
                    In vé & hóa đơn
                </button>

                <a href="{{ route('staff.ban-ve.success', ['id' => $ve->id]) }}">
                    Quay lại
                </a>
            </div>

            <script>
                window.addEventListener('load', function() {
                    window.print();
                });

            </script>
</body>

</html>
