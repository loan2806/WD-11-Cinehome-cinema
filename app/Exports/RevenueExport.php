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
        $rows[] = ['BAO CAO THONG KE DOANH THU CINEHOME'];

        // Row 2: Ngày tháng
        $from = isset($this->data['from']) ? Carbon::parse($this->data['from'])->format('d/m/Y') : '';
        $to = isset($this->data['to']) ? Carbon::parse($this->data['to'])->format('d/m/Y') : '';
        $rows[] = ['Tu ngay: ' . $from . ' - Den ngay: ' . $to];

        // Row 3: Thông tin bộ lọc
        $phimInfo = !empty($this->data['phim_id']) ? 'Phim ID: ' . $this->data['phim_id'] : 'Tat ca phim';
        $phongInfo = !empty($this->data['phong_chieu_id']) ? 'Phong ID: ' . $this->data['phong_chieu_id'] : 'Tat ca phong';
        $rows[] = [$phimInfo . ' | ' . $phongInfo];

        // Row 4: Empty
        $rows[] = [''];

        // =========================================
        // I. TOM TAT KPI
        // =========================================
        $rows[] = ['I. TOM TAT CAC CHI SO CHINH'];
        $rows[] = ['Chi so', 'Gia tri'];
        $rows[] = ['Tong doanh thu', $this->toCurrency($this->data['kpi']['total_revenue'] ?? 0)];
        $rows[] = ['Doanh thu ve', $this->toCurrency($this->data['kpi']['ticket_revenue'] ?? 0)];
        $rows[] = ['Doanh thu combo', $this->toCurrency($this->data['kpi']['combo_revenue'] ?? 0)];
        $rows[] = ['Doanh thu do an & nuoc', $this->toCurrency($this->data['kpi']['snack_revenue'] ?? 0)];
        $rows[] = ['So ve da ban', $this->toNumber($this->data['kpi']['tickets_sold'] ?? 0)];
        $rows[] = ['Tong so hoa don', $this->toNumber($this->data['kpi']['total_invoices'] ?? 0)];
        $rows[] = ['Gia ve trung binh', $this->toCurrency($this->data['kpi']['average_ticket_price'] ?? 0)];
        $rows[] = ['Tong so suat chieu', $this->toNumber($this->data['kpi']['total_showtimes'] ?? 0)];
        $rows[] = ['Voucher da su dung', $this->toNumber($this->data['kpi']['vouchers_used'] ?? 0)];
        $rows[] = [''];

        // =========================================
        // II. CO CAU DOANH THU
        // =========================================
        $rows[] = ['II. CO CAU DOANH THU'];
        $rows[] = ['Loai', 'Doanh thu', 'Ti le (%)'];
        $structure = $this->data['revenue_structure'] ?? [];
        $rows[] = [
            'Ve xem phim',
            $this->toCurrency($structure['ticket']['revenue'] ?? 0),
            $this->toPercent($structure['ticket']['percentage'] ?? 0)
        ];
        $rows[] = [
            'Combo',
            $this->toCurrency($structure['combo']['revenue'] ?? 0),
            $this->toPercent($structure['combo']['percentage'] ?? 0)
        ];
        $rows[] = [
            'Do an & Nuoc',
            $this->toCurrency($structure['food']['revenue'] ?? 0),
            $this->toPercent($structure['food']['percentage'] ?? 0)
        ];
        $rows[] = [
            'TONG CONG',
            $this->toCurrency($structure['total'] ?? 0),
            '100%'
        ];
        $rows[] = [''];

        // =========================================
        // III. TOP PHIM DOANH THU CAO
        // =========================================
        $rows[] = ['III. TOP PHIM DOANH THU CAO'];
        $rows[] = ['STT', 'Ten phim', 'So ve ban', 'Doanh thu'];
        $topFilms = $this->data['top_films'] ?? [];
        if (!empty($topFilms)) {
            foreach ($topFilms as $index => $film) {
                $rows[] = [
                    $index + 1,
                    $film['ten_phim'] ?? 'N/A',
                    $this->toNumber($film['tickets_sold'] ?? 0),
                    $this->toCurrency($film['total_revenue'] ?? 0),
                ];
            }
        } else {
            $rows[] = ['Chua co du lieu', '', '', ''];
        }
        $rows[] = [''];

        // =========================================
        // IV. DOANH THU THEO PHONG CHIEU
        // =========================================
        $rows[] = ['IV. DOANH THU THEO PHONG CHIEU'];
        $rows[] = ['STT', 'Phong chieu', 'So ve ban', 'Doanh thu'];
        $revenueByRoom = $this->data['revenue_by_room'] ?? [];
        if (!empty($revenueByRoom)) {
            foreach ($revenueByRoom as $index => $room) {
                $rows[] = [
                    $index + 1,
                    $room['ten_phong'] ?? 'N/A',
                    $this->toNumber($room['tickets_sold'] ?? 0),
                    $this->toCurrency($room['total_revenue'] ?? 0),
                ];
            }
        } else {
            $rows[] = ['Chua co du lieu', '', '', ''];
        }
        $rows[] = [''];

        // =========================================
        // V. DOANH THU THEO LOAI GHE
        // =========================================
        $rows[] = ['V. DOANH THU THEO LOAI GHE'];
        $rows[] = ['STT', 'Loai ghe', 'So ve ban', 'Doanh thu'];
        $revenueBySeatType = $this->data['revenue_by_seat_type'] ?? [];
        if (!empty($revenueBySeatType)) {
            foreach ($revenueBySeatType as $index => $seatType) {
                $rows[] = [
                    $index + 1,
                    $seatType['ten_loai'] ?? 'N/A',
                    $this->toNumber($seatType['tickets_sold'] ?? 0),
                    $this->toCurrency($seatType['total_revenue'] ?? 0),
                ];
            }
        } else {
            $rows[] = ['Chua co du lieu', '', '', ''];
        }
        $rows[] = [''];

        // =========================================
        // VI. DOANH THU THEO KHUNG GIO
        // =========================================
        $rows[] = ['VI. DOANH THU THEO KHUNG GIO'];
        $rows[] = ['STT', 'Khung gio', 'So ve ban', 'Doanh thu'];
        $revenueByTimeSlot = $this->data['revenue_by_time_slot'] ?? [];
        if (!empty($revenueByTimeSlot)) {
            foreach ($revenueByTimeSlot as $index => $slot) {
                $rows[] = [
                    $index + 1,
                    $slot['time_slot'] ?? 'N/A',
                    $this->toNumber($slot['tickets_sold'] ?? 0),
                    $this->toCurrency($slot['total_revenue'] ?? 0),
                ];
            }
        } else {
            $rows[] = ['Chua co du lieu', '', '', ''];
        }
        $rows[] = [''];

        // =========================================
        // VII. PHUONG THUC THANH TOAN
        // =========================================
        $rows[] = ['VII. THONG KE PHUONG THUC THANH TOAN'];
        $rows[] = ['STT', 'Phuong thuc', 'So giao dich', 'Doanh thu'];
        $paymentMethods = $this->data['payment_methods'] ?? [];
        if (!empty($paymentMethods)) {
            foreach ($paymentMethods as $index => $method) {
                $rows[] = [
                    $index + 1,
                    $method['label'] ?? 'N/A',
                    $this->toNumber($method['count'] ?? 0),
                    $this->toCurrency($method['total_revenue'] ?? 0),
                ];
            }
        } else {
            $rows[] = ['Chua co du lieu', '', '', ''];
        }
        $rows[] = [''];

        // =========================================
        // VIII. THONG KE VOUCHER
        // =========================================
        $rows[] = ['VIII. THONG KE VOUCHER'];
        $voucherStats = $this->data['voucher_stats'] ?? [];
        $rows[] = ['Chi so', 'Gia tri'];
        $rows[] = ['Da phat hanh', $this->toNumber($voucherStats['total_issued'] ?? 0)];
        $rows[] = ['Da su dung', $this->toNumber($voucherStats['total_used'] ?? 0)];
        $rows[] = ['Ti le su dung', $this->toPercent($voucherStats['usage_rate'] ?? 0)];
        $rows[] = ['Tong tien giam gia', $this->toCurrency($voucherStats['total_discount'] ?? 0)];
        $rows[] = [''];

        // =========================================
        // IX. TOP SUAT CHIEU BAN CHAY
        // =========================================
        $rows[] = ['IX. TOP SUAT CHIEU BAN CHAY'];
        $rows[] = ['STT', 'Phim', 'Phong chieu', 'Gio chieu', 'So ve ban', 'Doanh thu'];
        $topShowtimes = $this->data['top_showtimes'] ?? [];
        if (!empty($topShowtimes)) {
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
                    $showtime['ten_phim'] ?? 'N/A',
                    $showtime['ten_phong'] ?? 'N/A',
                    $showtimeTime,
                    $this->toNumber($showtime['tickets_sold'] ?? 0),
                    $this->toCurrency($showtime['total_revenue'] ?? 0),
                ];
            }
        } else {
            $rows[] = ['Chua co du lieu', '', '', '', '', ''];
        }

        return $rows;
    }

    /**
     * Convert value to currency format
     */
    protected function toCurrency($value): string
    {
        if ($value === null || $value === '') {
            return '0';
        }

        return number_format((float) $value, 0, ',', '.');
    }

    /**
     * Convert value to number format
     */
    protected function toNumber($value): string
    {
        if ($value === null || $value === '') {
            return '0';
        }

        return number_format((int) $value, 0, ',', '.');
    }

    /**
     * Convert value to percentage format
     */
    protected function toPercent($value): string
    {
        if ($value === null || $value === '') {
            return '0%';
        }

        return number_format((float) $value, 2, ',', '.') . '%';
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // Style header row (row 1)
        $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D99A32']],
        ]);

        // Style section headers
        for ($row = 1; $row <= $highestRow; $row++) {
            $cellA = $sheet->getCell('A' . $row)->getValue();
            if ($cellA && is_string($cellA)) {
                $trimmed = trim($cellA);

                // Main section headers (Roman numerals)
                if (preg_match('/^[IVX]+\.\s/', $trimmed)) {
                    $sheet->getStyle('A' . $row . ':' . $highestColumn . $row)->applyFromArray([
                        'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'D99A32']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F2937']],
                    ]);
                }
                // Sub-headers (columns)
                elseif (in_array($trimmed, [
                    'Chi so', 'Gia tri', 'Loai', 'Doanh thu', 'Ti le (%)',
                    'STT', 'Ten phim', 'So ve ban', 'Doanh thu',
                    'Phong chieu', 'Loai ghe', 'Khung gio', 'So giao dich',
                    'Phim', 'Gio chieu', 'Phuong thuc', 'Ti le su dung',
                    'Da phat hanh', 'Da su dung', 'Tong tien giam gia'
                ])) {
                    $sheet->getStyle('A' . $row . ':' . $highestColumn . $row)->applyFromArray([
                        'font' => ['bold' => true, 'size' => 11],
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
