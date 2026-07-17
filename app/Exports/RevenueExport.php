<?php

namespace App\Exports;

use App\Services\ThongKeService;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class RevenueExport implements WithHeadings, WithStyles, ShouldAutoSize
{
    protected ThongKeService $service;
    protected array $data;

    public function __construct(ThongKeService $service)
    {
        $this->service = $service;
        $this->data = $service->getFullExportData();
    }

    public function headings(): array
    {
        $rows = [];

        // Row 1: Tiêu đề báo cáo
        $rows[] = ['BAO CAO DOANH THU CINEHOME'];

        // Row 2: Ngày tháng
        $from = isset($this->data['from']) ? Carbon::parse($this->data['from'])->format('d/m/Y') : '';
        $to = isset($this->data['to']) ? Carbon::parse($this->data['to'])->format('d/m/Y') : '';
        $rows[] = ['Tu ngay: ' . $from . ' - Den ngay: ' . $to];

        // Row 3: Empty
        $rows[] = [''];

        // KPI Summary
        $rows[] = ['TOM TAT KPI'];
        $rows[] = ['Tong doanh thu', $this->toNumber($this->data['kpi']['total_revenue'] ?? 0)];
        $rows[] = ['Doanh thu ve', $this->toNumber($this->data['kpi']['ticket_revenue'] ?? 0)];
        $rows[] = ['Doanh thu combo', $this->toNumber($this->data['kpi']['food_revenue'] ?? 0)];
        $rows[] = ['Tong so ve da ban', $this->toNumber($this->data['kpi']['tickets_sold'] ?? 0)];
        $rows[] = ['Tong so hoa don', $this->toNumber($this->data['kpi']['total_invoices'] ?? 0)];
        $rows[] = ['Gia ve trung binh', $this->toNumber($this->data['kpi']['average_ticket_price'] ?? 0)];
        $rows[] = ['Ty le lap day', $this->toNumber($this->data['kpi']['seat_occupancy_rate'] ?? 0) . '%'];
        $rows[] = ['Tong so suat chieu', $this->toNumber($this->data['kpi']['total_showtimes'] ?? 0)];
        $rows[] = ['Voucher da su dung', $this->toNumber($this->data['kpi']['vouchers_used'] ?? 0)];
        $rows[] = ['Tong tien giam gia', $this->toNumber($this->data['kpi']['voucher_discount'] ?? 0)];
        $rows[] = [''];

        // Cơ cấu doanh thu
        $rows[] = ['CO CAU DOANH THU'];
        $rows[] = ['Loai', 'Doanh thu', 'Ty le'];
        $structure = $this->data['revenue_structure'] ?? [];
        $rows[] = ['Ve xem phim', $this->toNumber($structure['ticket']['revenue'] ?? 0), $this->toNumber($structure['ticket']['percentage'] ?? 0) . '%'];
        $rows[] = ['Combo', $this->toNumber($structure['food']['revenue'] ?? 0), $this->toNumber($structure['food']['percentage'] ?? 0) . '%'];
        $rows[] = ['Dich vu khac', $this->toNumber($structure['service']['revenue'] ?? 0), $this->toNumber($structure['service']['percentage'] ?? 0) . '%'];
        $rows[] = ['TONG CONG', $this->toNumber($structure['total'] ?? 0), '100%'];
        $rows[] = [''];

        // Doanh thu theo phim
        $rows[] = ['TOP PHIM DOANH THU CAO NHAT'];
        $rows[] = ['STT', 'Ten phim', 'Doanh thu', 'Ve ban', 'Gia ve TB'];
        $topFilms = $this->data['top_films'] ?? [];
        foreach ($topFilms as $index => $film) {
            $rows[] = [
                $index + 1,
                $film['ten_phim'] ?? '',
                $this->toNumber($film['total_revenue'] ?? 0),
                $this->toNumber($film['tickets_sold'] ?? 0),
                $this->toNumber($film['avg_price'] ?? 0),
            ];
        }
        $rows[] = [''];

        // Doanh thu theo phòng chiếu
        $rows[] = ['DOANH THU THEO PHONG CHIEU'];
        $rows[] = ['STT', 'Phong chieu', 'Doanh thu', 'Ve ban'];
        $revenueByRoom = $this->data['revenue_by_room'] ?? [];
        foreach ($revenueByRoom as $index => $room) {
            $rows[] = [
                $index + 1,
                $room['ten_phong'] ?? '',
                $this->toNumber($room['total_revenue'] ?? 0),
                $this->toNumber($room['tickets_sold'] ?? 0),
            ];
        }
        $rows[] = [''];

        // Doanh thu theo loại ghế
        $rows[] = ['DOANH THU THEO LOAI GHE'];
        $rows[] = ['STT', 'Loai ghe', 'Doanh thu', 'Ve ban'];
        $revenueBySeatType = $this->data['revenue_by_seat_type'] ?? [];
        foreach ($revenueBySeatType as $index => $seatType) {
            $rows[] = [
                $index + 1,
                $seatType['ten_loai'] ?? '',
                $this->toNumber($seatType['total_revenue'] ?? 0),
                $this->toNumber($seatType['tickets_sold'] ?? 0),
            ];
        }
        $rows[] = [''];

        // Doanh thu theo khung giờ
        $rows[] = ['DOANH THU THEO KHUNG GIO'];
        $rows[] = ['STT', 'Khung gio', 'Doanh thu', 'Ve ban'];
        $revenueByTimeSlot = $this->data['revenue_by_time_slot'] ?? [];
        foreach ($revenueByTimeSlot as $index => $slot) {
            $rows[] = [
                $index + 1,
                $slot['time_slot'] ?? '',
                $this->toNumber($slot['total_revenue'] ?? 0),
                $this->toNumber($slot['tickets_sold'] ?? 0),
            ];
        }
        $rows[] = [''];

        // Thống kê phương thức thanh toán
        $rows[] = ['THONG KE PHUONG THUC THANH TOAN'];
        $rows[] = ['STT', 'Phuong thuc', 'DT ve', 'DT combo', 'Tong DT', 'So giao dich'];
        $paymentMethods = $this->data['payment_methods'] ?? [];
        foreach ($paymentMethods as $index => $method) {
            $rows[] = [
                $index + 1,
                $method['label'] ?? '',
                $this->toNumber($method['ticket_revenue'] ?? 0),
                $this->toNumber($method['food_revenue'] ?? 0),
                $this->toNumber($method['total_revenue'] ?? 0),
                $this->toNumber($method['count'] ?? 0),
            ];
        }
        $rows[] = [''];

        // Thống kê voucher
        $rows[] = ['THONG KE VOUCHER'];
        $voucherStats = $this->data['voucher_stats'] ?? [];
        $rows[] = ['Tong voucher phat hanh', $this->toNumber($voucherStats['total_issued'] ?? 0)];
        $rows[] = ['Tong voucher da su dung', $this->toNumber($voucherStats['total_used'] ?? 0)];
        $rows[] = ['Ty le su dung', $this->toNumber($voucherStats['usage_rate'] ?? 0) . '%'];
        $rows[] = ['Tong tien giam gia', $this->toNumber($voucherStats['total_discount'] ?? 0)];
        $rows[] = [''];

        // Top suất chiếu bán chạy
        $rows[] = ['TOP SUAT CHIEU BAN CHAY'];
        $rows[] = ['STT', 'Phim', 'Phong chieu', 'Gio chieu', 'Doanh thu', 'Ve ban'];
        $topShowtimes = $this->data['top_showtimes'] ?? [];
        foreach ($topShowtimes as $index => $showtime) {
            $showtimeTime = '';
            if (!empty($showtime['thoi_gian_chieu'])) {
                try {
                    $showtimeTime = Carbon::parse($showtime['thoi_gian_chieu'])->format('d/m/Y H:i');
                } catch (\Exception $e) {
                    $showtimeTime = '';
                }
            }
            $rows[] = [
                $index + 1,
                $showtime['ten_phim'] ?? '',
                $showtime['ten_phong'] ?? '',
                $showtimeTime,
                $this->toNumber($showtime['total_revenue'] ?? 0),
                $this->toNumber($showtime['tickets_sold'] ?? 0),
            ];
        }

        return $rows;
    }

    /**
     * Convert value to safe number/string for Excel
     */
    protected function toNumber($value): float|int|string
    {
        if ($value === null) {
            return 0;
        }
        
        // If it's already a number, return as float/int
        if (is_numeric($value)) {
            return $value;
        }
        
        // For strings that might look like numbers, still return as-is
        return $value;
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // Set data format for number columns
        $sheet->getStyle('B1:' . $highestColumn . $highestRow)
            ->getNumberFormat()
            ->setFormatCode('#,##0');

        // Style header row (row 1)
        $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D99A32']],
        ]);

        // Style section headers
        for ($row = 1; $row <= $highestRow; $row++) {
            $cellA = $sheet->getCell('A' . $row)->getValue();
            if ($cellA && is_string($cellA) && strlen($cellA) > 0) {
                // Check if it looks like a section header
                $trimmed = trim($cellA);
                if (str_contains($trimmed, 'TOM TAT') || 
                    str_contains($trimmed, 'CO CAU') || 
                    str_contains($trimmed, 'TOP') || 
                    str_contains($trimmed, 'DOANH THU') || 
                    str_contains($trimmed, 'THONG KE') ||
                    str_contains($trimmed, 'PHUONG THUC') ||
                    str_contains($trimmed, 'VOUCHER') ||
                    str_contains($trimmed, 'SUAT CHIEU') ||
                    str_contains($trimmed, 'KHUNG GIO') ||
                    str_contains($trimmed, 'LOAI GHE')) {
                    $sheet->getStyle('A' . $row . ':' . $highestColumn . $row)->applyFromArray([
                        'font' => ['bold' => true, 'size' => 12],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '374151']],
                    ]);
                }
            }
        }

        // Borders for all data
        $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '4B5563'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Auto size columns
        foreach (range('A', $highestColumn) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }
}
