<!DOCTYPE html>

<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta
        http-equiv="Content-Type"
        content="text/html; charset=UTF-8"
    >

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        In vé {{ $ve->ma_ve }}
    </title>


    <style>

        /* =====================================================
           RESET
        ===================================================== */

        * {
            box-sizing: border-box;
        }


        html,
        body {
            margin: 0;
            padding: 0;

            font-family:
                Arial,
                "DejaVu Sans",
                "Segoe UI",
                sans-serif;

            color: #000;

            background: #eeeeee;

            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }


        body {
            padding: 15px 0;
        }



        /* =====================================================
           TỜ VÉ 80MM
        ===================================================== */

        .ticket {
            width: 80mm;

            margin: 0 auto 16px auto;

            padding: 3mm;

            background: #ffffff;

            overflow: hidden;

            page-break-after: always;
            break-after: page;
        }


        .ticket:last-child {
            margin-bottom: 0;

            page-break-after: auto;
            break-after: auto;
        }



        /* =====================================================
           FONT
        ===================================================== */

        .ticket,
        .ticket div,
        .ticket span,
        .ticket strong,
        .ticket p {
            font-family:
                Arial,
                "DejaVu Sans",
                "Segoe UI",
                sans-serif;
        }


        .center {
            text-align: center;
        }


        .cinema-title {
            margin: 0;

            font-size: 17px;

            font-weight: 700;

            line-height: 1.25;

            letter-spacing: 0;

            text-align: center;
        }


        .cinema-name {
            margin-top: 3px;

            font-size: 15px;

            font-weight: 700;

            line-height: 1.25;

            text-align: center;
        }


        .cinema-subtitle {
            margin-top: 3px;

            font-size: 10px;

            font-weight: 600;

            line-height: 1.3;

            text-align: center;
        }


        .ticket-heading {
            margin-top: 7px;

            font-size: 16px;

            font-weight: 700;

            line-height: 1.3;

            letter-spacing: 0;

            text-align: center;
        }


        .ticket-type {
            margin-top: 2px;

            font-size: 13px;

            font-weight: 700;

            line-height: 1.3;

            text-align: center;
        }



        /* =====================================================
           ĐƯỜNG KẺ
        ===================================================== */

        .dashed-line {
            width: 100%;

            margin: 8px 0;

            border-top: 1px dashed #000;
        }


        .solid-line {
            width: 100%;

            margin: 7px 0;

            border-top: 1px solid #000;
        }



        /* =====================================================
           MÃ VÉ
        ===================================================== */

        .ticket-code {
            font-size: 8px;

            line-height: 1.4;

            text-align: center;

            overflow-wrap: anywhere;
        }



        /* =====================================================
           SUẤT CHIẾU
        ===================================================== */

        .showtime {
            margin-top: 8px;

            font-size: 15px;

            font-weight: 700;

            line-height: 1.3;

            text-align: center;
        }


        .movie-name {
            margin-top: 6px;

            font-size: 16px;

            font-weight: 700;

            line-height: 1.3;

            text-align: center;

            text-transform: uppercase;

            overflow-wrap: anywhere;
        }


        .movie-format {
            margin-top: 3px;

            font-size: 13px;

            font-weight: 700;

            line-height: 1.25;

            text-align: center;
        }



        /* =====================================================
           GHẾ
        ===================================================== */

        .seat-box {
            display: flex;

            width: 100%;

            margin-top: 8px;

            border: 1px solid #000;

            border-radius: 4px;

            overflow: hidden;
        }


        .seat-column,
        .room-column {
            width: 50%;

            padding: 7px 4px;

            text-align: center;
        }


        .seat-column {
            border-right: 1px dashed #777;
        }


        .box-label {
            display: block;

            margin-bottom: 3px;

            font-size: 9px;

            line-height: 1.25;
        }


        .seat-value {
            display: block;

            font-size: 23px;

            font-weight: 700;

            line-height: 1.15;
        }


        .room-value {
            display: block;

            font-size: 12px;

            font-weight: 700;

            line-height: 1.3;
        }


        .price-value {
            display: block;

            margin-top: 3px;

            font-size: 11px;

            font-weight: 700;

            line-height: 1.3;
        }



        /* =====================================================
           QR
        ===================================================== */

        .qr-wrapper {
            display: flex;

            justify-content: center;

            margin-top: 8px;
        }


        .qr-wrapper svg {
            display: block;

            width: 26mm !important;

            height: 26mm !important;
        }


        .qr-code-text {
            margin-top: 4px;

            font-size: 7px;

            line-height: 1.25;

            text-align: center;

            overflow-wrap: anywhere;
        }



        /* =====================================================
           FOOTER
        ===================================================== */

        .thank-you {
            margin-top: 8px;

            font-size: 10px;

            font-weight: 700;

            line-height: 1.4;

            text-align: center;
        }


        .footer-note {
            margin-top: 2px;

            font-size: 8px;

            line-height: 1.4;

            text-align: center;
        }


        .printed-at {
            margin-top: 3px;

            font-size: 7px;

            line-height: 1.3;

            text-align: center;

            color: #444;
        }



        /* =====================================================
           HÓA ĐƠN
        ===================================================== */

        .invoice {
            margin-top: 11px;

            padding-top: 7px;

            border-top: 2px dashed #000;
        }


        .invoice-title {
            margin-bottom: 6px;

            font-size: 14px;

            font-weight: 700;

            text-align: center;
        }


        .invoice-row {
            display: flex;

            align-items: flex-start;

            justify-content: space-between;

            gap: 8px;

            padding: 3px 0;

            font-size: 9px;

            line-height: 1.35;
        }


        .invoice-row span:first-child {
            flex: 1;
        }


        .invoice-row strong {
            max-width: 55%;

            text-align: right;

            overflow-wrap: anywhere;
        }


        .invoice-section-title {
            margin-top: 6px;

            font-size: 9px;

            font-weight: 700;

            text-transform: uppercase;
        }


        .invoice-total {
            margin-top: 5px;

            padding-top: 5px;

            border-top: 1px solid #000;

            font-size: 12px;

            font-weight: 700;
        }



        /* =====================================================
           KHI IN
        ===================================================== */

        @media print {

            @page {
                size: 80mm auto;
                margin: 0;
            }


            html,
            body {
                width: 80mm;

                margin: 0;

                padding: 0;

                background: #ffffff;
            }


            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }


            .ticket {
                width: 80mm;

                margin: 0;

                padding: 3mm;

                box-shadow: none;

                page-break-after: always;
                break-after: page;
            }


            .ticket:last-child {
                page-break-after: auto;
                break-after: auto;
            }

        }

    </style>

</head>


<body>

@php

    /*
    |--------------------------------------------------------------------------
    | ĐỒ ĂN
    |--------------------------------------------------------------------------
    */

    $foods = collect(
        $ve->food_items
        ?? $ve->foods_list
        ?? []
    );


    $foodTotal = (float) ($ve->food_total ?? 0);


    if ($foodTotal <= 0) {

        $foodTotal = $foods->sum(function ($food) {

            $price = (float) (
                $food['price']
                ?? $food['don_gia']
                ?? 0
            );


            $quantity = (int) (
                $food['qty']
                ?? $food['quantity']
                ?? $food['so_luong']
                ?? 1
            );


            return $price * $quantity;

        });

    }



    /*
    |--------------------------------------------------------------------------
    | TIỀN VÉ
    |--------------------------------------------------------------------------
    */

    $seatTotal = (float) ($ve->seat_total ?? 0);


    if ($seatTotal <= 0) {

        $seatTotal = max(
            (float) $ve->tong_tien - $foodTotal,
            0
        );

    }



    /*
    |--------------------------------------------------------------------------
    | NHÂN VIÊN
    |--------------------------------------------------------------------------
    */

    $staffName = $ve->nhanVien->ho_ten
        ?? $ve->nhanVien->ten
        ?? 'Nhân viên';



    /*
    |--------------------------------------------------------------------------
    | DANH SÁCH GHẾ
    |--------------------------------------------------------------------------
    */

    $ticketSeats = collect();


    /*
     * Ưu tiên quan hệ gheVes vì mỗi ghế
     * có thể có ma_qr riêng.
     */
    if (
        isset($ve->gheVes)
        &&
        $ve->gheVes->count() > 0
    ) {

        $ticketSeats = $ve->gheVes->values();

    } else {

        /*
         * Fallback nếu chưa load quan hệ gheVes.
         */
        $ticketSeats = collect(
            array_filter(
                array_map(
                    'trim',
                    explode(
                        ',',
                        $ve->ma_ghe ?? ''
                    )
                )
            )
        )->values();

    }


    $ticketCount = $ticketSeats->count();



    /*
    |--------------------------------------------------------------------------
    | GIÁ TRUNG BÌNH / GHẾ
    |--------------------------------------------------------------------------
    |
    | Nếu dữ liệu gheVe không có giá riêng thì
    | dùng seatTotal / số ghế.
    |
    */

    $defaultSeatPrice = $ticketCount > 0
        ? ($seatTotal / $ticketCount)
        : $seatTotal;

@endphp



@forelse ($ticketSeats as $index => $seatItem)

    @php

        /*
         * gheVes là model:
         * $seatItem->ma_ghe
         *
         * fallback là string:
         * D5
         */

        $seatCode = is_object($seatItem)
            ? (
                $seatItem->ma_ghe
                ?? $seatItem->ghe
                ?? ''
            )
            : $seatItem;
        /*
         * Nếu model có giá riêng thì dùng.
         * Không có thì dùng giá trung bình.
         */
        $seatPrice = is_object($seatItem)
            ? (
                $seatItem->gia_ve
                ?? $seatItem->gia
                ?? $defaultSeatPrice
            )
            : $defaultSeatPrice;
        /*
         * Vé cuối cùng sẽ chứa hóa đơn tổng.
         */
        $isLastTicket =
            ($index === $ticketCount - 1);

    @endphp


    <section class="ticket">

        {{-- ============================= --}}
        {{-- HEADER                         --}}
        {{-- ============================= --}}

        <div class="cinema-title">
            RẠP CHIẾU PHIM
        </div>

        <div class="cinema-name">
            CINEHOME
        </div>

        <div class="cinema-subtitle">
            {{ $ve->ten_rap ?: 'CineHome Cinema' }}
        </div>


        <div class="dashed-line"></div>


        <div class="ticket-heading">
            VÉ XEM PHIM
        </div>

        <div class="ticket-type">
            TẠI QUẦY
        </div>


        <div class="ticket-code">

            Ký hiệu: N/A

            &nbsp;|&nbsp;

            Mã vé:
            {{ $ve->ma_ve }}

            ({{ $index + 1 }}/{{ $ticketCount }})

        </div>


        <div class="solid-line"></div>


        {{-- ============================= --}}
        {{-- PHIM                           --}}
        {{-- ============================= --}}

        <div class="showtime">

            {{
                optional($ve->thoi_gian_chieu)
                    ->format('d/m/Y H:i')
            }}

        </div>


        <div class="movie-name">

            {{ $ve->ten_phim }}

        </div>


        <div class="movie-format">

            {{
                $ve->dinh_dang
                ?? $ve->format
                ?? '2D'
            }}

            @if (
                !empty($ve->loai_ve)
                &&
                !in_array(
                    strtolower($ve->loai_ve),
                    ['thuong', 'thường']
                )
            )

                {{ strtoupper($ve->loai_ve) }}

            @endif

        </div>


        <div class="dashed-line"></div>


        {{-- ============================= --}}
        {{-- GHẾ                            --}}
        {{-- ============================= --}}

        <div class="seat-box">

            <div class="seat-column">

                <span class="box-label">
                    Ghế / Seat:
                </span>

                <strong class="seat-value">
                    {{ $seatCode }}
                </strong>

            </div>


            <div class="room-column">

                <span class="box-label">
                    Phòng / Cinema:
                </span>

                <strong class="room-value">
                    {{ $ve->ten_phong }}
                </strong>

                <strong class="price-value">
                    {{
                        number_format(
                            (float) $seatPrice,
                            0,
                            ',',
                            '.'
                        )
                    }}đ
                </strong>

            </div>

        </div>

        {{-- ============================= --}}
        {{-- QR RIÊNG CHO TỪNG GHẾ           --}}
        {{-- ============================= --}}

        @if (
            is_object($seatItem)
            && !empty($seatItem->ma_qr)
        )

            <div class="dashed-line"></div>

            <div class="qr-wrapper">
                {!!
                    QrCode::format('svg')
                        ->size(180)
                        ->margin(0)
                        ->generate($seatItem->ma_qr)
                !!}
            </div>

            <div class="qr-code-text">
                {{ $seatItem->ma_qr }}
            </div>

        @endif


        {{-- ============================= --}}
        {{-- CẢM ƠN                         --}}
        {{-- ============================= --}}

        <div class="dashed-line"></div>


        <div class="thank-you">
            Xin chân thành cảm ơn quý khách!
        </div>


        <div class="footer-note">
            Chúc bạn xem phim vui vẻ tại CineHome
        </div>


        <div class="printed-at">

            Thời gian in:
            {{ now()->format('H:i:s d/m/Y') }}

        </div>



        {{-- ================================================= --}}
        {{-- HÓA ĐƠN CHỈ HIỆN Ở VÉ CUỐI                       --}}
        {{-- ================================================= --}}

        @if ($isLastTicket)

            <div class="invoice">

                <div class="invoice-title">
                    HÓA ĐƠN THANH TOÁN
                </div>


                <div class="invoice-row">

                    <span>
                        Mã đơn
                    </span>

                    <strong>
                        {{ $ve->ma_ve }}
                    </strong>

                </div>


                <div class="invoice-row">

                    <span>
                        Nhân viên
                    </span>

                    <strong>
                        {{ $staffName }}
                    </strong>

                </div>


                <div class="invoice-row">

                    <span>
                        Phương thức
                    </span>

                    <strong>
                        {{
                            $ve->payment_method === 'vietqr'
                                ? 'VietQR'
                                : 'Tiền mặt'
                        }}
                    </strong>

                </div>


                <div class="dashed-line"></div>


                <div class="invoice-row">

                    <span>
                        Vé xem phim
                    </span>

                    <strong>
                        {{
                            number_format(
                                $seatTotal,
                                0,
                                ',',
                                '.'
                            )
                        }}đ
                    </strong>

                </div>



                @if ($foods->isNotEmpty())

                    <div class="invoice-section-title">
                        Đồ ăn & combo
                    </div>


                    @foreach ($foods as $food)

                        @php

                            $foodName =
                                $food['name']
                                ?? $food['ten_mon']
                                ?? 'Đồ ăn';


                            $foodPrice = (float) (
                                $food['price']
                                ?? $food['don_gia']
                                ?? 0
                            );


                            $foodQuantity = (int) (
                                $food['qty']
                                ?? $food['quantity']
                                ?? $food['so_luong']
                                ?? 1
                            );

                        @endphp


                        <div class="invoice-row">

                            <span>

                                {{ $foodName }}

                                x{{ $foodQuantity }}

                            </span>

                            <strong>

                                {{
                                    number_format(
                                        $foodPrice
                                        *
                                        $foodQuantity,
                                        0,
                                        ',',
                                        '.'
                                    )
                                }}đ

                            </strong>

                        </div>

                    @endforeach


                    <div class="invoice-row">

                        <span>
                            Tổng đồ ăn
                        </span>

                        <strong>

                            {{
                                number_format(
                                    $foodTotal,
                                    0,
                                    ',',
                                    '.'
                                )
                            }}đ

                        </strong>

                    </div>

                @endif



                <div class="invoice-row invoice-total">

                    <span>
                        TỔNG CỘNG
                    </span>

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



                @if ($ve->payment_method !== 'vietqr')

                    <div class="invoice-row">

                        <span>
                            Khách đưa
                        </span>

                        <strong>

                            {{
                                number_format(
                                    (float) (
                                        $ve->received_amount
                                        ?? 0
                                    ),
                                    0,
                                    ',',
                                    '.'
                                )
                            }}đ

                        </strong>

                    </div>


                    <div class="invoice-row">

                        <span>
                            Tiền thừa
                        </span>

                        <strong>

                            {{
                                number_format(
                                    (float) (
                                        $ve->change_amount
                                        ?? 0
                                    ),
                                    0,
                                    ',',
                                    '.'
                                )
                            }}đ

                        </strong>

                    </div>

                @endif


                <div class="dashed-line"></div>


                <div class="thank-you">

                    Cảm ơn quý khách đã lựa chọn CineHome!

                </div>

            </div>

        @endif

    </section>


@empty

    {{-- =====================================================
         FALLBACK TRƯỜNG HỢP KHÔNG CÓ GHEVES
    ====================================================== --}}

    <section class="ticket">

        <div class="cinema-title">
            RẠP CHIẾU PHIM
        </div>

        <div class="cinema-name">
            CINEHOME
        </div>

        <div class="dashed-line"></div>

        <div class="ticket-heading">
            VÉ XEM PHIM
        </div>

        <div class="ticket-type">
            TẠI QUẦY
        </div>

        <div class="solid-line"></div>

        <div class="movie-name">
            {{ $ve->ten_phim }}
        </div>

        <div class="showtime">
            {{
                optional($ve->thoi_gian_chieu)
                    ->format('d/m/Y H:i')
            }}
        </div>

        <div class="seat-box">

            <div class="seat-column">

                <span class="box-label">
                    Ghế
                </span>

                <strong class="seat-value">
                    {{ $ve->ma_ghe ?: '---' }}
                </strong>

            </div>

            <div class="room-column">

                <span class="box-label">
                    Phòng
                </span>

                <strong class="room-value">
                    {{ $ve->ten_phong }}
                </strong>

            </div>

        </div>

    </section>

@endforelse


{{-- QUAN TRỌNG:
     KHÔNG đặt window.print() ở file này.
     success.blade.php sẽ gọi print() từ iframe.
--}}

</body>

</html>