<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>In vé {{ $ve->ma_ve }}</title>

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

        .ticket {
            width: 80mm;
            min-height: 125mm;
            margin: 0 auto 20px;
            padding: 11px;
            background: #ffffff;
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

        .ticket-title {
            margin-top: 10px;
            font-size: 17px;
            font-weight: 900;
        }

        .divider {
            margin: 10px 0;
            border-top: 1px dashed #111111;
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

        .row strong {
            max-width: 62%;
            text-align: right;
            overflow-wrap: anywhere;
        }

        .seat-box {
            margin: 10px 0;
            padding: 10px;
            border: 2px solid #111111;
            text-align: center;
        }

        .seat-box span {
            display: block;
            font-size: 11px;
            font-weight: 700;
        }

        .seat-box strong {
            display: block;
            margin-top: 2px;
            font-size: 31px;
            font-weight: 900;
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

        .page-break {
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

            .ticket {
                width: 80mm;
                min-height: 125mm;
                margin: 0;
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
    @endphp

    @forelse ($seatTickets as $seatTicket)
    <section class="ticket">
        <div class="center">
            <div class="brand">
                CineHome
            </div>

            <div class="cinema">
                {{ $ve->ten_rap }}
            </div>

            <div class="ticket-title">
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
            <span>GHẾ</span>

            <strong>
                {{ $seatTicket->ma_ghe }}
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

    @if (!$loop->last)
    <div class="page-break"></div>
    @endif
    @empty
    <section class="ticket">
        <div class="center">
            Không tìm thấy dữ liệu ghế để in.
        </div>
    </section>
    @endforelse

    <div class="actions">
        <button type="button" onclick="window.print()">
            In tất cả vé
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
