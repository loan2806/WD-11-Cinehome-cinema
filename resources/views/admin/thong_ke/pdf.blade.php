<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Báo cáo doanh thu CineHome</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e50914;
        }
        
        .header h1 {
            font-size: 24px;
            color: #e50914;
            margin-bottom: 5px;
        }
        
        .header h2 {
            font-size: 18px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .header .date-range {
            color: #666;
            font-size: 14px;
        }
        
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .kpi-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        
        .kpi-card .label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .kpi-card .value {
            font-size: 18px;
            font-weight: bold;
            color: #e50914;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            padding: 10px 0;
            border-bottom: 2px solid #e50914;
            margin-bottom: 15px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            padding: 10px 8px;
            text-align: left;
            border: 1px solid #dee2e6;
        }
        
        th {
            background: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        
        tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .total-row {
            font-weight: bold;
            background: #ffe6e6 !important;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            color: #666;
            font-size: 10px;
        }
        
        .two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        @page {
            margin: 20mm;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>CINEHOME CINEMA</h1>
        <h2>BÁO CÁO DOANH THU</h2>
        <p class="date-range">
            Từ ngày: {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} 
            Đến ngày: {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}
        </p>
    </div>

    {{-- KPI Summary --}}
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="label">Tổng doanh thu</div>
            <div class="value">{{ number_format($kpi['total_revenue'], 0, ',', '.') }}đ</div>
        </div>
        <div class="kpi-card">
            <div class="label">Doanh thu vé</div>
            <div class="value">{{ number_format($kpi['ticket_revenue'], 0, ',', '.') }}đ</div>
        </div>
        <div class="kpi-card">
            <div class="label">Doanh thu combo</div>
            <div class="value">{{ number_format($kpi['food_revenue'], 0, ',', '.') }}đ</div>
        </div>
        <div class="kpi-card">
            <div class="label">Tổng số vé</div>
            <div class="value">{{ number_format($kpi['tickets_sold']) }}</div>
        </div>
        <div class="kpi-card">
            <div class="label">Tổng hóa đơn</div>
            <div class="value">{{ number_format($kpi['total_invoices']) }}</div>
        </div>
        <div class="kpi-card">
            <div class="label">Giá vé TB</div>
            <div class="value">{{ number_format($kpi['average_ticket_price'], 0, ',', '.') }}đ</div>
        </div>
        <div class="kpi-card">
            <div class="label">Tỷ lệ lấp đầy</div>
            <div class="value">{{ $kpi['seat_occupancy_rate'] }}%</div>
        </div>
        <div class="kpi-card">
            <div class="label">Số suất chiếu</div>
            <div class="value">{{ number_format($kpi['total_showtimes']) }}</div>
        </div>
        <div class="kpi-card">
            <div class="label">Voucher sử dụng</div>
            <div class="value">{{ number_format($kpi['vouchers_used']) }}</div>
        </div>
    </div>

    {{-- Revenue Structure --}}
    <div class="section">
        <div class="section-title">CƠ CẤU DOANH THU</div>
        <table>
            <thead>
                <tr>
                    <th>Loại</th>
                    <th class="text-right">Doanh thu</th>
                    <th class="text-center">Tỷ lệ</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Vé xem phim</td>
                    <td class="text-right">{{ number_format($revenueStructure['ticket']['revenue'], 0, ',', '.') }}đ</td>
                    <td class="text-center">{{ $revenueStructure['ticket']['percentage'] }}%</td>
                </tr>
                <tr>
                    <td>Combo & Đồ ăn</td>
                    <td class="text-right">{{ number_format($revenueStructure['food']['revenue'], 0, ',', '.') }}đ</td>
                    <td class="text-center">{{ $revenueStructure['food']['percentage'] }}%</td>
                </tr>
                <tr class="total-row">
                    <td>TỔNG CỘNG</td>
                    <td class="text-right">{{ number_format($revenueStructure['total'], 0, ',', '.') }}đ</td>
                    <td class="text-center">100%</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Top Films --}}
    <div class="section">
        <div class="section-title">TOP 10 PHIM DOANH THU CAO NHẤT</div>
        <table>
            <thead>
                <tr>
                    <th class="text-center">STT</th>
                    <th>Tên phim</th>
                    <th class="text-right">Doanh thu</th>
                    <th class="text-right">Vé bán</th>
                    <th class="text-right">Giá TB</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topFilms as $index => $film)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $film['ten_phim'] }}</td>
                    <td class="text-right">{{ number_format($film['total_revenue'], 0, ',', '.') }}đ</td>
                    <td class="text-right">{{ number_format($film['tickets_sold']) }}</td>
                    <td class="text-right">{{ number_format($film['avg_price'], 0, ',', '.') }}đ</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p>Báo cáo được tạo lúc: {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>CineHome Cinema - Hệ thống quản lý rạp chiếu phim</p>
    </div>
</body>
</html>
