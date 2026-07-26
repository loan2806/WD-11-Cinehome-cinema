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
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
        }

        .ticket {
            width: 80mm;
            margin: 0 auto 20px;
            padding: 10px 11px 12px;
            background: #fff;
            text-align: center;
        }

        .cinema-name {
            font-size: 19px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .cinema-sub {
            margin-top: 5px;
            font-size: 14px;
            font-weight: 700;
        }

        .cinema-address {
            margin-top: 4px;
            font-size: 11px;
        }

        .divider {
            margin: 10px 0;
            border-top: 1px dashed #000;
        }

        .solid-divider {
            margin: 8px 0;
            border-top: 1px solid #000;
        }

        .ticket-title {
            font-size: 18px;
            font-weight: 900;
        }

        .ticket-source {
            margin-top: 3px;
            font-size: 15px;
            font-weight: 800;
        }

        .ticket-code {
            margin-top: 5px;
            font-size: 10px;
            line-height: 1.4;
        }

        .showtime {
            margin-top: 10px;
            font-size: 15px;
            font-weight: 900;
        }

        .movie-name {
            margin-top: 8px;
            font-size: 18px;
            font-weight: 900;
            line-height: 1.25;
            text-transform: uppercase;
        }

        .movie-type {
            margin-top: 5px;
            font-size: 14px;
            font-weight: 800;
        }

        .seat-room-box {
            display: flex;
            margin: 8px auto 0;
            border: 1.5px solid #000;
            border-radius: 5px;
            overflow: hidden;
        }

        .seat-column,
        .room-column {
            width: 50%;
            padding: 10px 5px;
        }

        .seat-column {
            border-right: 1px dashed #777;
        }

        .box-label {
            font-size: 12px;
        }

        .seat-code {
            margin-top: 3px;
            font-size: 27px;
            font-weight: 900;
        }

        .room-name {
            margin-top: 5px;
            font-size: 14px;
            font-weight: 900;
        }

        .seat-price {
            margin-top: 5px;
            font-size: 13px;
            font-weight: 900;
        }

        .age-note {
            margin-top: 8px;
            font-size: 10px;
            line-height: 1.4;
            font-weight: 700;
            font-style: italic;
        }

        .thanks {
            margin-top: 10px;
            font-size: 13px;
            font-weight: 900;
        }

        .wish {
            margin-top: 6px;
            font-size: 11px;
        }

        .printed-at {
            margin-top: 8px;
            font-size: 9px;
            color: #555;
        }

        .page-break {
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
            background: #111;
            color: #fff;
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
                background: #fff;
            }

            .ticket {
                width: 80mm;
                margin: 0;
            }

            .actions {
                display: none;
            }
        }

    </style>
</head>

<body>

    @php

    /*
    |--------------------------------------------------------------------------
    | DANH SÁCH GHẾ
    |--------------------------------------------------------------------------
    */

    $seatTickets = $ve->gheVes
    ->sortBy('ma_ghe')
    ->values();

    $totalSeats = $seatTickets->count();


    /*
    |--------------------------------------------------------------------------
    | GIÁ VÉ CƠ BẢN
    |--------------------------------------------------------------------------
    */

    $baseTicketPrice = (float) (
    $ve->suatChieu->gia_ve ?? 0
    );


    /*
    |--------------------------------------------------------------------------
    | THÔNG TIN RẠP
    |--------------------------------------------------------------------------
    */

    $cinemaName = $ve->ten_rap ?: 'CINEHOME CINEMA';


    /*
    |--------------------------------------------------------------------------
    | NHÃN ĐỘ TUỔI
    |--------------------------------------------------------------------------
    |
    | Nếu model của bạn có field khác, chỉ cần thay phần này.
    |
    */

    $ageRating =
    $ve->phim->do_tuoi
    ?? $ve->phim->nhan_do_tuoi
    ?? $ve->phim->phan_loai
    ?? null;

    @endphp


    @forelse ($seatTickets as $index => $seatTicket)

    @php

    /*
    |--------------------------------------------------------------------------
    | TÌM THÔNG TIN GHẾ
    |--------------------------------------------------------------------------
    */

    $normalizedSeatCode = strtoupper(
    trim((string) $seatTicket->ma_ghe)
    );

    $seat = $seatDetails->get($normalizedSeatCode);

    $seatType = $seat?->loaiGhe;


    /*
    |--------------------------------------------------------------------------
    | LOẠI GHẾ
    |--------------------------------------------------------------------------
    */

    $seatTypeName =
    $seatType?->ten_loai
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
    $seatTypeName = 'Ghế Đôi';

    } elseif ($seatSurcharge > 0) {
    $seatTypeName = 'Ghế VIP';

    } else {
    $seatTypeName = 'Ghế Thường';
    }
    }


    /*
    |--------------------------------------------------------------------------
    | GIÁ CỦA GHẾ
    |--------------------------------------------------------------------------
    */

    $seatPrice = $isCoupleSeat
    ? ($baseTicketPrice * 2) + $seatSurcharge
    : $baseTicketPrice + $seatSurcharge;


    /*
    |--------------------------------------------------------------------------
    | SỐ THỨ TỰ VÉ
    |--------------------------------------------------------------------------
    */

    $ticketPosition = $index + 1;

    @endphp


    <section class="ticket">

        {{-- ================= HEADER ================= --}}

        <div class="cinema-name">
            RẠP CHIẾU PHIM CINEHOME
        </div>

        <div class="cinema-sub">
            {{ strtoupper($cinemaName) }}
        </div>

        <div class="cinema-address">
            CineHome Cinema
        </div>


        <div class="divider"></div>


        {{-- ================= TIÊU ĐỀ ================= --}}

        <div class="ticket-title">
            VÉ XEM PHIM
        </div>

        <div class="ticket-source">
            TẠI QUẦY
        </div>

        <div class="ticket-code">

            Ký hiệu: N/A

            &nbsp;|&nbsp;

            Mã vé:
            <strong>
                {{ $ve->ma_ve }}
            </strong>

            ({{ $ticketPosition }}/{{ $totalSeats }})

        </div>


        <div class="solid-divider"></div>


        {{-- ================= SUẤT CHIẾU ================= --}}

        <div class="showtime">

            {{ optional($ve->thoi_gian_chieu)->format('d/m/Y H:i') }}

        </div>


        {{-- ================= PHIM ================= --}}

        <div class="movie-name">

            {{ $ve->ten_phim }}

        </div>


        <div class="movie-type">

            2D {{ $seatTypeName }}

        </div>


        <div class="divider"></div>


        {{-- ================= GHẾ + PHÒNG ================= --}}

        <div class="seat-room-box">

            <div class="seat-column">

                <div class="box-label">
                    Ghế / Seat:
                </div>

                <div class="seat-code">
                    {{ $seatTicket->ma_ghe }}
                </div>

            </div>


            <div class="room-column">

                <div class="box-label">
                    Phòng / Cinema:
                </div>

                <div class="room-name">
                    {{ $ve->ten_phong }}
                </div>

                <div class="seat-price">
                    VND {{ number_format($seatPrice, 0, ',', '.') }}
                </div>

            </div>

        </div>


        {{-- ================= NHÃN ĐỘ TUỔI ================= --}}

        @if ($ageRating)

        <div class="age-note">

            ** Phim dán nhãn [{{ strtoupper($ageRating) }}]

            @if (strtoupper($ageRating) === 'T18')
            : Phim dành cho khán giả từ đủ 18 tuổi trở lên

            @elseif (strtoupper($ageRating) === 'T16')
            : Phim dành cho khán giả từ đủ 16 tuổi trở lên

            @elseif (strtoupper($ageRating) === 'T13')
            : Phim dành cho khán giả từ đủ 13 tuổi trở lên

            @elseif (strtoupper($ageRating) === 'K')
            : Phim dành cho khán giả dưới 13 tuổi có người giám hộ

            @elseif (strtoupper($ageRating) === 'P')
            : Phim được phép phổ biến đến người xem ở mọi độ tuổi
            @endif

            **

        </div>

        @endif


        <div class="divider"></div>


        {{-- ================= FOOTER ================= --}}

        <div class="thanks">
            Xin chân thành Cảm ơn quý khách!
        </div>

        <div class="wish">
            Chúc bạn xem phim vui vẻ tại CineHome
        </div>

        <div class="printed-at">
            Thời gian in:
            {{ now()->format('H:i:s d/m/Y') }}
        </div>

    </section>


    @if (!$loop->last)
    <div class="page-break"></div>
    @endif


    @empty

    <section class="ticket">

        Không tìm thấy dữ liệu ghế để in.

    </section>

    @endforelse


    <div class="actions">

        <button type="button" onclick="window.print()">
            In vé
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
