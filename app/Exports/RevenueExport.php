<?php

namespace App\Exports;

use App\Services\ThongKeService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class RevenueExport implements FromArray, WithStyles, ShouldAutoSize
{
    protected ThongKeService $service;
    protected array $data;

    public function __construct(ThongKeService $service)
    {
        $this->service = $service;
        $this->data = $service->getFullExportData();
    }

    /**
     * Dữ liệu xuất Excel
     */
    public function array(): array
    {
        $rows = [];

        // =========================================
        // THÔNG TIN BÁO CÁO
        // =========================================

        // Row 1: Tiêu đề
        $rows[] = [
            'BÁO CÁO THỐNG KÊ DOANH THU CINEHOME'
        ];

        // Row 2: Ngày tháng
        $from = isset($this->data['from'])
            ? Carbon::parse($this->data['from'])->format('d/m/Y')
            : '';

        $to = isset($this->data['to'])
            ? Carbon::parse($this->data['to'])->format('d/m/Y')
            : '';

        $rows[] = [
            'Từ ngày: ' . $from . ' - Đến ngày: ' . $to
        ];

        // Row 3: Bộ lọc
        $phimInfo = !empty($this->data['phim_id'])
            ? 'Phim ID: ' . $this->data['phim_id']
            : 'Tất cả phim';

        $phongInfo = !empty($this->data['phong_chieu_id'])
            ? 'Phòng ID: ' . $this->data['phong_chieu_id']
            : 'Tất cả phòng';

        $rows[] = [
            $phimInfo . ' | ' . $phongInfo
        ];

        // Row 4: Khoảng trống
        $rows[] = [''];

        // =========================================
        // I. TÓM TẮT CÁC CHỈ SỐ CHÍNH
        // =========================================

        $rows[] = [
            'I. TÓM TẮT CÁC CHỈ SỐ CHÍNH'
        ];

        $rows[] = [
            'Chỉ số',
            'Giá trị'
        ];

        $rows[] = [
            'Tổng doanh thu',
            $this->toCurrency(
                $this->data['kpi']['total_revenue'] ?? 0
            )
        ];

        $rows[] = [
            'Doanh thu vé',
            $this->toCurrency(
                $this->data['kpi']['ticket_revenue'] ?? 0
            )
        ];

        $rows[] = [
            'Doanh thu combo',
            $this->toCurrency(
                $this->data['kpi']['combo_revenue'] ?? 0
            )
        ];

        $rows[] = [
            'Doanh thu đồ ăn & nước',
            $this->toCurrency(
                $this->data['kpi']['snack_revenue'] ?? 0
            )
        ];

        $rows[] = [
            'Số vé đã bán',
            $this->toNumber(
                $this->data['kpi']['tickets_sold'] ?? 0
            )
        ];

        $rows[] = [
            'Tổng số hóa đơn',
            $this->toNumber(
                $this->data['kpi']['total_invoices'] ?? 0
            )
        ];

        $rows[] = [
            'Giá vé trung bình',
            $this->toCurrency(
                $this->data['kpi']['average_ticket_price'] ?? 0
            )
        ];

        $rows[] = [
            'Tổng số suất chiếu',
            $this->toNumber(
                $this->data['kpi']['total_showtimes'] ?? 0
            )
        ];

        $rows[] = [
            'Voucher đã sử dụng',
            $this->toNumber(
                $this->data['kpi']['vouchers_used'] ?? 0
            )
        ];

        $rows[] = [''];

        // =========================================
        // II. CƠ CẤU DOANH THU
        // =========================================

        $rows[] = [
            'II. CƠ CẤU DOANH THU'
        ];

        $rows[] = [
            'Loại',
            'Doanh thu',
            'Tỷ lệ (%)'
        ];

        $structure = $this->data['revenue_structure'] ?? [];

        $rows[] = [
            'Vé xem phim',
            $this->toCurrency(
                $structure['ticket']['revenue'] ?? 0
            ),
            $this->toPercent(
                $structure['ticket']['percentage'] ?? 0
            )
        ];

        $rows[] = [
            'Combo',
            $this->toCurrency(
                $structure['combo']['revenue'] ?? 0
            ),
            $this->toPercent(
                $structure['combo']['percentage'] ?? 0
            )
        ];

        $rows[] = [
            'Đồ ăn & nước',
            $this->toCurrency(
                $structure['food']['revenue'] ?? 0
            ),
            $this->toPercent(
                $structure['food']['percentage'] ?? 0
            )
        ];

        $rows[] = [
            'TỔNG CỘNG',
            $this->toCurrency(
                $structure['total'] ?? 0
            ),
            '100%'
        ];

        $rows[] = [''];

        // =========================================
        // III. TOP PHIM DOANH THU CAO
        // =========================================

        $rows[] = [
            'III. TOP PHIM DOANH THU CAO'
        ];

        $rows[] = [
            'STT',
            'Tên phim',
            'Số vé bán',
            'Doanh thu'
        ];

        $topFilms = $this->data['top_films'] ?? [];

        if (!empty($topFilms)) {

            foreach ($topFilms as $index => $film) {

                $rows[] = [
                    $index + 1,
                    $film['ten_phim'] ?? 'Không xác định',
                    $this->toNumber(
                        $film['tickets_sold'] ?? 0
                    ),
                    $this->toCurrency(
                        $film['total_revenue'] ?? 0
                    ),
                ];
            }

        } else {

            $rows[] = [
                'Chưa có dữ liệu',
                '',
                '',
                ''
            ];
        }

        $rows[] = [''];

        // =========================================
        // IV. DOANH THU THEO PHÒNG CHIẾU
        // =========================================

        $rows[] = [
            'IV. DOANH THU THEO PHÒNG CHIẾU'
        ];

        $rows[] = [
            'STT',
            'Phòng chiếu',
            'Số vé bán',
            'Doanh thu'
        ];

        $revenueByRoom = $this->data['revenue_by_room'] ?? [];

        if (!empty($revenueByRoom)) {

            foreach ($revenueByRoom as $index => $room) {

                $rows[] = [
                    $index + 1,
                    $room['ten_phong'] ?? 'Không xác định',
                    $this->toNumber(
                        $room['tickets_sold'] ?? 0
                    ),
                    $this->toCurrency(
                        $room['total_revenue'] ?? 0
                    ),
                ];
            }

        } else {

            $rows[] = [
                'Chưa có dữ liệu',
                '',
                '',
                ''
            ];
        }

        $rows[] = [''];

        // =========================================
        // V. DOANH THU THEO LOẠI GHẾ
        // =========================================

        $rows[] = [
            'V. DOANH THU THEO LOẠI GHẾ'
        ];

        $rows[] = [
            'STT',
            'Loại ghế',
            'Số vé bán',
            'Doanh thu'
        ];

        $revenueBySeatType =
            $this->data['revenue_by_seat_type'] ?? [];

        if (!empty($revenueBySeatType)) {

            foreach ($revenueBySeatType as $index => $seatType) {

                $rows[] = [
                    $index + 1,
                    $seatType['ten_loai'] ?? 'Không xác định',
                    $this->toNumber(
                        $seatType['tickets_sold'] ?? 0
                    ),
                    $this->toCurrency(
                        $seatType['total_revenue'] ?? 0
                    ),
                ];
            }

        } else {

            $rows[] = [
                'Chưa có dữ liệu',
                '',
                '',
                ''
            ];
        }

        $rows[] = [''];

        // =========================================
        // VI. DOANH THU THEO KHUNG GIỜ
        // =========================================

        $rows[] = [
            'VI. DOANH THU THEO KHUNG GIỜ'
        ];

        $rows[] = [
            'STT',
            'Khung giờ',
            'Số vé bán',
            'Doanh thu'
        ];

        $revenueByTimeSlot =
            $this->data['revenue_by_time_slot'] ?? [];

        if (!empty($revenueByTimeSlot)) {

            foreach ($revenueByTimeSlot as $index => $slot) {

                $rows[] = [
                    $index + 1,
                    $slot['time_slot'] ?? 'Không xác định',
                    $this->toNumber(
                        $slot['tickets_sold'] ?? 0
                    ),
                    $this->toCurrency(
                        $slot['total_revenue'] ?? 0
                    ),
                ];
            }

        } else {

            $rows[] = [
                'Chưa có dữ liệu',
                '',
                '',
                ''
            ];
        }

        $rows[] = [''];

        // =========================================
        // VII. THỐNG KÊ PHƯƠNG THỨC THANH TOÁN
        // =========================================

        $rows[] = [
            'VII. THỐNG KÊ PHƯƠNG THỨC THANH TOÁN'
        ];

        $rows[] = [
            'STT',
            'Phương thức',
            'Số giao dịch',
            'Doanh thu'
        ];

        $paymentMethods =
            $this->data['payment_methods'] ?? [];

        if (!empty($paymentMethods)) {

            foreach ($paymentMethods as $index => $method) {

                $rows[] = [
                    $index + 1,
                    $method['label'] ?? 'Không xác định',
                    $this->toNumber(
                        $method['count'] ?? 0
                    ),
                    $this->toCurrency(
                        $method['total_revenue'] ?? 0
                    ),
                ];
            }

        } else {

            $rows[] = [
                'Chưa có dữ liệu',
                '',
                '',
                ''
            ];
        }

        $rows[] = [''];

        // =========================================
        // VIII. THỐNG KÊ VOUCHER
        // =========================================

        $rows[] = [
            'VIII. THỐNG KÊ VOUCHER'
        ];

        $voucherStats =
            $this->data['voucher_stats'] ?? [];

        $rows[] = [
            'Chỉ số',
            'Giá trị'
        ];

        $rows[] = [
            'Đã phát hành',
            $this->toNumber(
                $voucherStats['total_issued'] ?? 0
            )
        ];

        $rows[] = [
            'Đã sử dụng',
            $this->toNumber(
                $voucherStats['total_used'] ?? 0
            )
        ];

        $rows[] = [
            'Tỷ lệ sử dụng',
            $this->toPercent(
                $voucherStats['usage_rate'] ?? 0
            )
        ];

        $rows[] = [
            'Tổng tiền giảm giá',
            $this->toCurrency(
                $voucherStats['total_discount'] ?? 0
            )
        ];

        $rows[] = [''];

        // =========================================
        // IX. TOP SUẤT CHIẾU BÁN CHẠY
        // =========================================

        $rows[] = [
            'IX. TOP SUẤT CHIẾU BÁN CHẠY'
        ];

        $rows[] = [
            'STT',
            'Phim',
            'Phòng chiếu',
            'Giờ chiếu',
            'Số vé bán',
            'Doanh thu'
        ];

        $topShowtimes =
            $this->data['top_showtimes'] ?? [];

        if (!empty($topShowtimes)) {

            foreach ($topShowtimes as $index => $showtime) {

                $showtimeTime = '';

                if (!empty($showtime['thoi_gian_chieu'])) {

                    try {

                        $showtimeTime = Carbon::parse(
                            $showtime['thoi_gian_chieu']
                        )->format('d/m/Y H:i');

                    } catch (\Exception $e) {

                        $showtimeTime = '';
                    }
                }

                $rows[] = [
                    $index + 1,
                    $showtime['ten_phim'] ?? 'Không xác định',
                    $showtime['ten_phong'] ?? 'Không xác định',
                    $showtimeTime,
                    $this->toNumber(
                        $showtime['tickets_sold'] ?? 0
                    ),
                    $this->toCurrency(
                        $showtime['total_revenue'] ?? 0
                    ),
                ];
            }

        } else {

            $rows[] = [
                'Chưa có dữ liệu',
                '',
                '',
                '',
                '',
                ''
            ];
        }

        return $rows;
    }

    /**
     * Định dạng tiền
     */
    protected function toCurrency($value): string
    {
        if ($value === null || $value === '') {
            return '0';
        }

        return number_format(
            (float) $value,
            0,
            ',',
            '.'
        );
    }

    /**
     * Định dạng số
     */
    protected function toNumber($value): string
    {
        if ($value === null || $value === '') {
            return '0';
        }

        return number_format(
            (int) $value,
            0,
            ',',
            '.'
        );
    }

    /**
     * Định dạng phần trăm
     */
    protected function toPercent($value): string
    {
        if ($value === null || $value === '') {
            return '0%';
        }

        return number_format(
            (float) $value,
            2,
            ',',
            '.'
        ) . '%';
    }

    /**
     * Style Excel
     */
    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // =========================================
        // TIÊU ĐỀ CHÍNH - MÀU VÀNG
        // =========================================

        $sheet->getStyle(
            'A1:' . $highestColumn . '1'
        )->applyFromArray([

            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => [
                    'rgb' => 'FFFFFF'
                ],
            ],

            'alignment' => [
                'horizontal' =>
                    Alignment::HORIZONTAL_CENTER,

                'vertical' =>
                    Alignment::VERTICAL_CENTER,

                'wrapText' => true,
            ],

            'fill' => [
                'fillType' =>
                    Fill::FILL_SOLID,

                'startColor' => [
                    'rgb' => 'D99A32'
                ],
            ],
        ]);

        // =========================================
        // CÁC DÒNG CÒN LẠI
        // NỀN TRẮNG - CHỮ ĐEN
        // =========================================

        if ($highestRow >= 2) {

            $sheet->getStyle(
                'A2:' . $highestColumn . $highestRow
            )->applyFromArray([

                'font' => [
                    'color' => [
                        'rgb' => '000000'
                    ],
                ],

                'fill' => [
                    'fillType' =>
                        Fill::FILL_SOLID,

                    'startColor' => [
                        'rgb' => 'FFFFFF'
                    ],
                ],

                'alignment' => [
                    'horizontal' =>
                        Alignment::HORIZONTAL_CENTER,

                    'vertical' =>
                        Alignment::VERTICAL_CENTER,

                    'wrapText' => true,
                ],
            ]);
        }

        // =========================================
        // STYLE CÁC TIÊU ĐỀ MỤC
        // I. ...
        // II. ...
        // III. ...
        // =========================================

        for ($row = 1; $row <= $highestRow; $row++) {

            $cellA = $sheet
                ->getCell('A' . $row)
                ->getValue();

            if ($cellA && is_string($cellA)) {

                $trimmed = trim($cellA);

                // Tiêu đề mục I, II, III...
                if (
                    preg_match(
                        '/^[IVX]+\.\s/',
                        $trimmed
                    )
                ) {

                    $sheet->getStyle(
                        'A' . $row . ':' .
                        $highestColumn . $row
                    )->applyFromArray([

                        'font' => [
                            'bold' => true,
                            'size' => 12,
                            'color' => [
                                'rgb' => '000000'
                            ],
                        ],

                        'fill' => [
                            'fillType' =>
                                Fill::FILL_SOLID,

                            'startColor' => [
                                'rgb' => 'FFFFFF'
                            ],
                        ],

                        'alignment' => [
                            'horizontal' =>
                                Alignment::HORIZONTAL_CENTER,

                            'vertical' =>
                                Alignment::VERTICAL_CENTER,

                            'wrapText' => true,
                        ],
                    ]);
                }
            }
        }

        // =========================================
        // HEADER CÁC BẢNG
        // =========================================

        $headerTexts = [

            'Chỉ số',
            'Giá trị',

            'Loại',
            'Doanh thu',
            'Tỷ lệ (%)',

            'STT',
            'Tên phim',
            'Số vé bán',

            'Phòng chiếu',
            'Loại ghế',
            'Khung giờ',

            'Số giao dịch',
            'Phương thức',

            'Phim',
            'Giờ chiếu',

            'Tỷ lệ sử dụng',
            'Đã phát hành',
            'Đã sử dụng',
            'Tổng tiền giảm giá',
        ];

        for ($row = 1; $row <= $highestRow; $row++) {

            $cellA = $sheet
                ->getCell('A' . $row)
                ->getValue();

            if (
                $cellA &&
                is_string($cellA) &&
                in_array(trim($cellA), $headerTexts)
            ) {

                $sheet->getStyle(
                    'A' . $row . ':' .
                    $highestColumn . $row
                )->applyFromArray([

                    'font' => [
                        'bold' => true,
                        'size' => 11,
                        'color' => [
                            'rgb' => '000000'
                        ],
                    ],

                    'fill' => [
                        'fillType' =>
                            Fill::FILL_SOLID,

                        'startColor' => [
                            'rgb' => 'FFFFFF'
                        ],
                    ],

                    'alignment' => [
                        'horizontal' =>
                            Alignment::HORIZONTAL_CENTER,

                        'vertical' =>
                            Alignment::VERTICAL_CENTER,

                        'wrapText' => true,
                    ],
                ]);
            }
        }

        // =========================================
        // VIỀN TOÀN BỘ BẢNG
        // =========================================

        $sheet->getStyle(
            'A1:' . $highestColumn . $highestRow
        )->applyFromArray([

            'borders' => [

                'allBorders' => [

                    'borderStyle' =>
                        Border::BORDER_THIN,

                    'color' => [
                        'rgb' => 'D9D9D9'
                    ],
                ],
            ],

            'alignment' => [

                'horizontal' =>
                    Alignment::HORIZONTAL_CENTER,

                'vertical' =>
                    Alignment::VERTICAL_CENTER,

                'wrapText' => true,
            ],
        ]);

        // =========================================
        // CHIỀU CAO DÒNG
        // =========================================

        $sheet->getRowDimension(1)
            ->setRowHeight(30);

        for ($row = 2; $row <= $highestRow; $row++) {

            $sheet->getRowDimension($row)
                ->setRowHeight(22);
        }

        // =========================================
        // TỰ ĐỘNG ĐỘ RỘNG CỘT
        // =========================================

        foreach (
            range('A', $highestColumn)
            as $column
        ) {

            $sheet->getColumnDimension($column)
                ->setAutoSize(true);
        }

        return [];
    }
}