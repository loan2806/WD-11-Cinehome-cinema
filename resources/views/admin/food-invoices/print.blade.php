<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In hóa đơn {{ $invoice->invoice_code }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            font-family: Arial, "DejaVu Sans", "Segoe UI", sans-serif;
            color: #000;
            background: #eeeeee;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }

        body {
            padding: 15px 0;
        }

        .receipt {
            width: 80mm;
            margin: 0 auto;
            padding: 3mm;
            background: #ffffff;
        }

        .center {
            text-align: center;
        }

        .cinema-title {
            margin: 0;
            font-size: 17px;
            font-weight: 700;
            line-height: 1.25;
            text-align: center;
        }

        .cinema-name {
            margin-top: 3px;
            font-size: 15px;
            font-weight: 700;
            line-height: 1.25;
            text-align: center;
        }

        .receipt-heading {
            margin-top: 7px;
            font-size: 15px;
            font-weight: 700;
            line-height: 1.3;
            text-align: center;
        }

        .invoice-code-line {
            font-size: 9px;
            line-height: 1.4;
            text-align: center;
            overflow-wrap: anywhere;
        }

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

        .info-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8px;
            padding: 2px 0;
            font-size: 10px;
            line-height: 1.35;
        }

        .info-row span:first-child {
            flex: 0 0 auto;
            color: #333;
        }

        .info-row strong {
            max-width: 65%;
            text-align: right;
            overflow-wrap: anywhere;
        }

        .item-row {
            padding: 4px 0;
            font-size: 11px;
            line-height: 1.4;
        }

        .item-row .item-name {
            font-weight: 700;
        }

        .item-row .item-detail {
            display: flex;
            justify-content: space-between;
            color: #333;
        }

        .invoice-total {
            margin-top: 5px;
            padding-top: 5px;
            border-top: 1px solid #000;
            font-size: 13px;
            font-weight: 700;
        }

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

        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }

            html, body {
                width: 80mm;
                margin: 0;
                padding: 0;
                background: #ffffff;
            }

            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .receipt {
                width: 80mm;
                margin: 0;
                padding: 3mm;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>

@php
    $staffName = $invoice->user->ho_ten ?? $invoice->user->name ?? 'Nhân viên';
    $paymentMethodLabels = [
        'tiền mặt' => 'Tiền mặt',
        'chuyển khoản' => 'Chuyển khoản',
        'thẻ' => 'Thẻ',
    ];
@endphp

<section class="receipt">
    <div class="cinema-title">RẠP CHIẾU PHIM</div>
    <div class="cinema-name">CINEHOME</div>

    <div class="dashed-line"></div>

    <div class="receipt-heading">HÓA ĐƠN ĐỒ ĂN &amp; COMBO</div>
    <div class="invoice-code-line">Mã hóa đơn: {{ $invoice->invoice_code }}</div>

    <div class="solid-line"></div>

    <div class="info-row">
        <span>Thời gian</span>
        <strong>{{ $invoice->created_at->format('H:i d/m/Y') }}</strong>
    </div>

    <div class="info-row">
        <span>Nhân viên</span>
        <strong>{{ $staffName }}</strong>
    </div>

    <div class="info-row">
        <span>Khách hàng</span>
        <strong>{{ $invoice->customer_name ?: 'Khách lẻ' }}</strong>
    </div>

    @if ($invoice->customer_phone)
        <div class="info-row">
            <span>Số điện thoại</span>
            <strong>{{ $invoice->customer_phone }}</strong>
        </div>
    @endif

    <div class="dashed-line"></div>

    @foreach ($invoice->items as $item)
        <div class="item-row">
            <div class="item-name">{{ $item->food_name }}</div>
            <div class="item-detail">
                <span>{{ (int) $item->quantity }} x {{ number_format((float) $item->unit_price, 0, ',', '.') }}đ</span>
                <span>{{ number_format((float) $item->total_price, 0, ',', '.') }}đ</span>
            </div>
        </div>
    @endforeach

    <div class="dashed-line"></div>

    <div class="info-row">
        <span>Tạm tính</span>
        <strong>{{ number_format((float) $invoice->subtotal, 0, ',', '.') }}đ</strong>
    </div>

    @if ((float) $invoice->discount > 0)
        <div class="info-row">
            <span>Giảm giá</span>
            <strong>-{{ number_format((float) $invoice->discount, 0, ',', '.') }}đ</strong>
        </div>
    @endif

    <div class="info-row invoice-total">
        <span>TỔNG CỘNG</span>
        <strong>{{ number_format((float) $invoice->total, 0, ',', '.') }}đ</strong>
    </div>

    <div class="dashed-line"></div>

    <div class="info-row">
        <span>Phương thức</span>
        <strong>{{ $paymentMethodLabels[$invoice->payment_method] ?? $invoice->payment_method }}</strong>
    </div>

    @if ($invoice->payment_method === 'tiền mặt')
        <div class="info-row">
            <span>Khách đưa</span>
            <strong>{{ number_format((float) $invoice->received_amount, 0, ',', '.') }}đ</strong>
        </div>
        <div class="info-row">
            <span>Tiền thừa</span>
            <strong>{{ number_format((float) $invoice->change_amount, 0, ',', '.') }}đ</strong>
        </div>
    @endif

    <div class="dashed-line"></div>

    <div class="thank-you">Vui lòng đưa hóa đơn này ra quầy đồ ăn để nhận hàng</div>
    <div class="footer-note">Cảm ơn quý khách đã lựa chọn CineHome!</div>
    <div class="printed-at">Thời gian in: {{ now()->format('H:i:s d/m/Y') }}</div>
</section>

<script>
    window.addEventListener('load', function () {
        window.print();
    });
</script>

</body>
</html>
