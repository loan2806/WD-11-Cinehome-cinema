<?php

namespace App\Services;

use App\Models\VeXemPhim;
use App\Models\FoodInvoice;
use App\Models\SuatChieu;
use App\Models\Phims;
use App\Models\PhongChieu;
use App\Models\GheNgoi;
use App\Models\LoaiGhe;
use App\Models\NguoiDungVoucher;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ThongKeService
{
    protected string $from;
    protected string $to;
    protected ?int $phimId;
    protected ?int $phongChieuId;

    public function __construct(
        string $from,
        string $to,
        ?int $phimId = null,
        ?int $phongChieuId = null
    ) {
        $this->from = $from;
        $this->to = $to;
        $this->phimId = $phimId;
        $this->phongChieuId = $phongChieuId;
    }

    /**
     * Lấy danh sách vé đã thanh toán/sử dụng với các điều kiện lọc
     */
    protected function getPaidTicketQuery()
    {
        return VeXemPhim::query()
            ->whereBetween('created_at', [$this->from, $this->to])
            ->whereIn('trang_thai', ['da_thanh_toan', 'da_su_dung'])
            ->when($this->phimId, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('suatChieu', function ($sq) {
                        $sq->where('phim_id', $this->phimId);
                    })
                    ->orWhereRaw('ten_phim IN (SELECT ten_phim FROM phims WHERE id = ?)', [$this->phimId]);
                });
            })
            ->when($this->phongChieuId, function ($query) {
                $query->whereHas('suatChieu', function ($q) {
                    $q->where('phong_chieu_id', $this->phongChieuId);
                });
            });
    }

    /**
     * Lấy danh sách hóa đơn đồ ăn đã thanh toán với các điều kiện lọc
     */
    protected function getPaidFoodInvoiceQuery()
    {
        return FoodInvoice::query()
            ->whereBetween('created_at', [$this->from, $this->to])
            ->where('payment_status', 'paid');
    }

    /**
     * Lấy tổng hợp KPI
     */
    public function getKPISummary(): array
    {
        $ticketRevenue = (clone $this->getPaidTicketQuery())->sum('tong_tien');
        $foodRevenue = (clone $this->getPaidFoodInvoiceQuery())->sum('total');
        $totalRevenue = $ticketRevenue + $foodRevenue;
        $ticketsSold = (clone $this->getPaidTicketQuery())->count();
        $foodInvoices = (clone $this->getPaidFoodInvoiceQuery())->count();
        $totalInvoices = $ticketsSold + $foodInvoices;
        $averageTicketPrice = $ticketsSold > 0 ? $ticketRevenue / $ticketsSold : 0;

        // Tổng số suất chiếu trong khoảng thời gian
        $totalShowtimes = SuatChieu::query()
            ->whereBetween('thoi_gian_chieu', [$this->from, $this->to])
            ->when($this->phimId, fn($q) => $q->where('phim_id', $this->phimId))
            ->when($this->phongChieuId, fn($q) => $q->where('phong_chieu_id', $this->phongChieuId))
            ->count();

        // Tổng số voucher đã sử dụng
        $vouchersUsed = NguoiDungVoucher::query()
            ->whereBetween('ngay_su_dung', [$this->from, $this->to])
            ->where('da_su_dung', true)
            ->count();

        // Tổng tiền giảm giá voucher
        $voucherDiscount = (clone $this->getPaidFoodInvoiceQuery())->sum('discount');

        // Tỷ lệ lấp đầy ghế
        $seatOccupancyRate = $this->calculateSeatOccupancyRate();

        return [
            'total_revenue' => $totalRevenue,
            'ticket_revenue' => $ticketRevenue,
            'food_revenue' => $foodRevenue,
            'tickets_sold' => $ticketsSold,
            'total_invoices' => $totalInvoices,
            'food_invoices' => $foodInvoices,
            'average_ticket_price' => $averageTicketPrice,
            'seat_occupancy_rate' => $seatOccupancyRate,
            'total_showtimes' => $totalShowtimes,
            'vouchers_used' => $vouchersUsed,
            'voucher_discount' => $voucherDiscount,
        ];
    }

    /**
     * Tính tỷ lệ lấp đầy ghế
     */
    protected function calculateSeatOccupancyRate(): float
    {
        $totalSeatsAvailable = PhongChieu::query()
            ->when($this->phongChieuId, fn($q) => $q->where('id', $this->phongChieuId))
            ->withCount('gheNgois')
            ->get()
            ->sum('ghe_ngois_count');

        if ($totalSeatsAvailable == 0) {
            return 0;
        }

        // Đếm số ghế đã bán trong khoảng thời gian
        $ticketsSold = (clone $this->getPaidTicketQuery())->count();

        return $totalSeatsAvailable > 0 ? round(($ticketsSold / $totalSeatsAvailable) * 100, 2) : 0;
    }

    /**
     * Doanh thu theo thời gian (ngày, tháng, quý, năm)
     */
    public function getRevenueByTime(string $periodType = 'day'): array
    {
        $groupFormat = match ($periodType) {
            'month' => '%Y-%m',
            'quarter' => 'Y-quarter',
            'year' => '%Y',
            default => '%Y-%m-%d',
        };

        $ticketRevenue = VeXemPhim::query()
            ->whereBetween('created_at', [$this->from, $this->to])
            ->whereIn('trang_thai', ['da_thanh_toan', 'da_su_dung'])
            ->when($this->phimId, fn($q) => $q->whereHas('suatChieu', fn($sq) => $sq->where('phim_id', $this->phimId)))
            ->when($this->phongChieuId, fn($q) => $q->whereHas('suatChieu', fn($sq) => $sq->where('phong_chieu_id', $this->phongChieuId)))
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$groupFormat}') as period"),
                DB::raw('SUM(tong_tien) as revenue'),
                DB::raw('COUNT(*) as tickets')
            )
            ->groupBy('period')
            ->get()
            ->keyBy('period');

        $foodRevenue = FoodInvoice::query()
            ->whereBetween('created_at', [$this->from, $this->to])
            ->where('payment_status', 'paid')
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$groupFormat}') as period"),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as invoices')
            )
            ->groupBy('period')
            ->get()
            ->keyBy('period');

        // Xử lý theo quý
        if ($periodType === 'quarter') {
            return $this->processQuarterlyData($ticketRevenue, $foodRevenue);
        }

        return $this->mergeRevenueData($ticketRevenue, $foodRevenue);
    }

    /**
     * Xử lý dữ liệu theo quý
     */
    protected function processQuarterlyData($ticketRevenue, $foodRevenue): array
    {
        $result = [];

        // Gộp dữ liệu vé theo quý
        foreach ($ticketRevenue as $item) {
            $year = substr($item->period, 0, 4);
            $month = (int) substr($item->period, 5, 2);
            $quarter = ceil($month / 3);
            $key = "{$year}-Q{$quarter}";

            if (!isset($result[$key])) {
                $result[$key] = [
                    'period' => $key,
                    'ticket_revenue' => 0,
                    'food_revenue' => 0,
                    'tickets' => 0,
                    'invoices' => 0,
                ];
            }
            $result[$key]['ticket_revenue'] += $item->revenue;
            $result[$key]['tickets'] += $item->tickets;
        }

        // Gộp dữ liệu đồ ăn theo quý
        foreach ($foodRevenue as $item) {
            $year = substr($item->period, 0, 4);
            $month = (int) substr($item->period, 5, 2);
            $quarter = ceil($month / 3);
            $key = "{$year}-Q{$quarter}";

            if (!isset($result[$key])) {
                $result[$key] = [
                    'period' => $key,
                    'ticket_revenue' => 0,
                    'food_revenue' => 0,
                    'tickets' => 0,
                    'invoices' => 0,
                ];
            }
            $result[$key]['food_revenue'] += $item->revenue;
            $result[$key]['invoices'] += $item->invoices;
        }

        // Tính tổng cho mỗi quý
        foreach ($result as $key => &$item) {
            $item['total_revenue'] = $item['ticket_revenue'] + $item['food_revenue'];
        }

        ksort($result);
        return array_values($result);
    }

    /**
     * Gộp dữ liệu vé và đồ ăn
     */
    protected function mergeRevenueData($ticketRevenue, $foodRevenue): array
    {
        $allPeriods = $ticketRevenue->keys()->merge($foodRevenue->keys())->unique()->sort();

        $result = [];
        foreach ($allPeriods as $period) {
            $ticket = $ticketRevenue->get($period);
            $food = $foodRevenue->get($period);

            $result[] = [
                'period' => $period,
                'ticket_revenue' => $ticket->revenue ?? 0,
                'food_revenue' => $food->revenue ?? 0,
                'tickets' => $ticket->tickets ?? 0,
                'invoices' => $food->invoices ?? 0,
                'total_revenue' => ($ticket->revenue ?? 0) + ($food->revenue ?? 0),
            ];
        }

        return $result;
    }

    /**
     * Doanh thu theo phim (Top 10)
     */
    public function getRevenueByFilm(int $limit = 10): array
    {
        $query = VeXemPhim::query()
            ->whereBetween('created_at', [$this->from, $this->to])
            ->whereIn('trang_thai', ['da_thanh_toan', 'da_su_dung'])
            ->when($this->phongChieuId, fn($q) => $q->whereHas('suatChieu', fn($sq) => $sq->where('phong_chieu_id', $this->phongChieuId)));

        // Filter by movie if specified
        if ($this->phimId) {
            $query->where(function ($q) {
                $q->whereHas('suatChieu', fn($sq) => $sq->where('phim_id', $this->phimId))
                  ->orWhereRaw('ten_phim IN (SELECT ten_phim FROM phims WHERE id = ?)', [$this->phimId]);
            });
        }

        return $query->select(
                'ten_phim',
                DB::raw('SUM(tong_tien) as total_revenue'),
                DB::raw('COUNT(*) as tickets_sold'),
                DB::raw('AVG(tong_tien) as avg_price')
            )
            ->groupBy('ten_phim')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Top suất chiếu bán chạy
     */
    public function getTopShowtimes(int $limit = 10): array
    {
        return VeXemPhim::query()
            ->whereBetween('ve_xem_phims.created_at', [$this->from, $this->to])
            ->whereIn('ve_xem_phims.trang_thai', ['da_thanh_toan', 'da_su_dung'])
            ->when($this->phimId, fn($q) => $q->whereHas('suatChieu', fn($sq) => $sq->where('phim_id', $this->phimId)))
            ->when($this->phongChieuId, fn($q) => $q->whereHas('suatChieu', fn($sq) => $sq->where('phong_chieu_id', $this->phongChieuId)))
            ->join('suat_chieus', 've_xem_phims.suat_chieu_id', '=', 'suat_chieus.id')
            ->join('phims', 'suat_chieus.phim_id', '=', 'phims.id')
            ->join('phong_chieus', 'suat_chieus.phong_chieu_id', '=', 'phong_chieus.id')
            ->select(
                'suat_chieus.id',
                'phims.ten_phim',
                'phong_chieus.ten_phong',
                'suat_chieus.thoi_gian_chieu',
                DB::raw('SUM(ve_xem_phims.tong_tien) as total_revenue'),
                DB::raw('COUNT(ve_xem_phims.id) as tickets_sold')
            )
            ->groupBy('suat_chieus.id', 'phims.ten_phim', 'phong_chieus.ten_phong', 'suat_chieus.thoi_gian_chieu')
            ->orderByDesc('tickets_sold')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Doanh thu theo phòng chiếu
     */
    public function getRevenueByRoom(): array
    {
        return VeXemPhim::query()
            ->whereBetween('ve_xem_phims.created_at', [$this->from, $this->to])
            ->whereIn('ve_xem_phims.trang_thai', ['da_thanh_toan', 'da_su_dung'])
            ->when($this->phimId, fn($q) => $q->whereHas('suatChieu', fn($sq) => $sq->where('phim_id', $this->phimId)))
            ->join('suat_chieus', 've_xem_phims.suat_chieu_id', '=', 'suat_chieus.id')
            ->join('phong_chieus', 'suat_chieus.phong_chieu_id', '=', 'phong_chieus.id')
            ->select(
                'phong_chieus.id',
                'phong_chieus.ten_phong',
                DB::raw('SUM(ve_xem_phims.tong_tien) as total_revenue'),
                DB::raw('COUNT(ve_xem_phims.id) as tickets_sold')
            )
            ->groupBy('phong_chieus.id', 'phong_chieus.ten_phong')
            ->orderByDesc('total_revenue')
            ->get()
            ->toArray();
    }

    /**
     * Doanh thu theo loại ghế (Thường, VIP, Couple)
     */
    public function getRevenueBySeatType(): array
    {
        return VeXemPhim::query()
            ->whereBetween('ve_xem_phims.created_at', [$this->from, $this->to])
            ->whereIn('ve_xem_phims.trang_thai', ['da_thanh_toan', 'da_su_dung'])
            ->when($this->phimId, fn($q) => $q->whereHas('suatChieu', fn($sq) => $sq->where('phim_id', $this->phimId)))
            ->when($this->phongChieuId, fn($q) => $q->whereHas('suatChieu', fn($sq) => $sq->where('phong_chieu_id', $this->phongChieuId)))
            ->join('suat_chieus', 've_xem_phims.suat_chieu_id', '=', 'suat_chieus.id')
            ->join('ghe_ngois', function ($join) {
                $join->on('ghe_ngois.ma_ghe', '=', 've_xem_phims.ma_ghe')
                    ->whereColumn('ghe_ngois.phong_chieu_id', 'suat_chieus.phong_chieu_id');
            })
            ->join('loai_ghes', 'ghe_ngois.loai_ghe_id', '=', 'loai_ghes.id')
            ->select(
                'loai_ghes.id',
                'loai_ghes.ten_loai',
                DB::raw('SUM(ve_xem_phims.tong_tien) as total_revenue'),
                DB::raw('COUNT(ve_xem_phims.id) as tickets_sold')
            )
            ->groupBy('loai_ghes.id', 'loai_ghes.ten_loai')
            ->orderByDesc('total_revenue')
            ->get()
            ->toArray();
    }

    /**
     * Doanh thu theo khung giờ
     */
    public function getRevenueByTimeSlot(): array
    {
        return VeXemPhim::query()
            ->whereBetween('ve_xem_phims.created_at', [$this->from, $this->to])
            ->whereIn('ve_xem_phims.trang_thai', ['da_thanh_toan', 'da_su_dung'])
            ->when($this->phimId, fn($q) => $q->whereHas('suatChieu', fn($sq) => $sq->where('phim_id', $this->phimId)))
            ->when($this->phongChieuId, fn($q) => $q->whereHas('suatChieu', fn($sq) => $sq->where('phong_chieu_id', $this->phongChieuId)))
            ->join('suat_chieus', 've_xem_phims.suat_chieu_id', '=', 'suat_chieus.id')
            ->select(
                DB::raw("CASE 
                    WHEN HOUR(suat_chieus.thoi_gian_chieu) BETWEEN 6 AND 11 THEN 'Sáng (06:00 - 12:00)'
                    WHEN HOUR(suat_chieus.thoi_gian_chieu) BETWEEN 12 AND 17 THEN 'Chiều (12:00 - 18:00)'
                    WHEN HOUR(suat_chieus.thoi_gian_chieu) BETWEEN 18 AND 21 THEN 'Tối (18:00 - 22:00)'
                    ELSE 'Khuya (22:00 - 06:00)'
                END as time_slot"),
                DB::raw('SUM(ve_xem_phims.tong_tien) as total_revenue'),
                DB::raw('COUNT(ve_xem_phims.id) as tickets_sold')
            )
            ->groupBy('time_slot')
            ->orderByRaw("FIELD(time_slot, 'Sáng (06:00 - 12:00)', 'Chiều (12:00 - 18:00)', 'Tối (18:00 - 22:00)', 'Khuya (22:00 - 06:00)')")
            ->get()
            ->toArray();
    }

    /**
     * Thống kê phương thức thanh toán
     */
    public function getPaymentMethodStats(): array
    {
        $ticketPayments = VeXemPhim::query()
            ->whereBetween('ve_xem_phims.created_at', [$this->from, $this->to])
            ->whereIn('ve_xem_phims.trang_thai', ['da_thanh_toan', 'da_su_dung'])
            ->select(
                DB::raw("COALESCE(ve_xem_phims.loai_ve, 'truc_tuyen') as payment_method"),
                DB::raw('SUM(ve_xem_phims.tong_tien) as total'),
                DB::raw('COUNT(ve_xem_phims.id) as count')
            )
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        $foodPayments = FoodInvoice::query()
            ->whereBetween('created_at', [$this->from, $this->to])
            ->where('payment_status', 'paid')
            ->select(
                DB::raw("COALESCE(payment_method, 'cash') as payment_method"),
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        $methods = [];
        $allMethods = $ticketPayments->keys()->merge($foodPayments->keys())->unique();

        $methodLabels = [
            'truc_tuyen' => 'Trực tuyến',
            'tai_quay' => 'Tại quầy',
            'cash' => 'Tiền mặt',
            'vnpay' => 'VNPay',
            'vietqr' => 'VietQR',
            'zalopay' => 'ZaloPay',
            'momo' => 'MoMo',
        ];

        foreach ($allMethods as $method) {
            $ticket = $ticketPayments->get($method);
            $food = $foodPayments->get($method);

            $methods[] = [
                'method' => $method,
                'label' => $methodLabels[$method] ?? ucfirst($method),
                'ticket_revenue' => $ticket->total ?? 0,
                'food_revenue' => $food->total ?? 0,
                'total_revenue' => ($ticket->total ?? 0) + ($food->total ?? 0),
                'count' => ($ticket->count ?? 0) + ($food->count ?? 0),
            ];
        }

        usort($methods, fn($a, $b) => $b['total_revenue'] <=> $a['total_revenue']);

        return $methods;
    }

    /**
     * Thống kê voucher
     */
    public function getVoucherStats(): array
    {
        $totalIssued = NguoiDungVoucher::query()
            ->whereBetween('ngay_nhan', [$this->from, $this->to])
            ->count();

        $totalUsed = NguoiDungVoucher::query()
            ->whereBetween('ngay_su_dung', [$this->from, $this->to])
            ->where('da_su_dung', true)
            ->count();

        $totalDiscount = FoodInvoice::query()
            ->whereBetween('created_at', [$this->from, $this->to])
            ->where('payment_status', 'paid')
            ->where('discount', '>', 0)
            ->sum('discount');

        $vouchersByType = NguoiDungVoucher::query()
            ->join('vouchers', 'nguoi_dung_vouchers.voucher_id', '=', 'vouchers.id')
            ->whereBetween('nguoi_dung_vouchers.ngay_su_dung', [$this->from, $this->to])
            ->where('nguoi_dung_vouchers.da_su_dung', true)
            ->select(
                'vouchers.loai_voucher',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(vouchers.gia_tri_giam) as total_discount')
            )
            ->groupBy('vouchers.loai_voucher')
            ->get()
            ->toArray();

        return [
            'total_issued' => $totalIssued,
            'total_used' => $totalUsed,
            'total_discount' => $totalDiscount,
            'usage_rate' => $totalIssued > 0 ? round(($totalUsed / $totalIssued) * 100, 2) : 0,
            'by_type' => $vouchersByType,
        ];
    }

    /**
     * Cơ cấu doanh thu (vé, combo, dịch vụ)
     */
    public function getRevenueStructure(): array
    {
        $ticketRevenue = (clone $this->getPaidTicketQuery())->sum('tong_tien');
        $foodRevenue = (clone $this->getPaidFoodInvoiceQuery())->sum('total');
        $totalRevenue = $ticketRevenue + $foodRevenue;

        // Doanh thu dịch vụ khác (nếu có trong tương lai)
        $serviceRevenue = 0;

        return [
            'ticket' => [
                'revenue' => $ticketRevenue,
                'percentage' => $totalRevenue > 0 ? round(($ticketRevenue / $totalRevenue) * 100, 2) : 0,
                'label' => 'Doanh thu vé',
            ],
            'food' => [
                'revenue' => $foodRevenue,
                'percentage' => $totalRevenue > 0 ? round(($foodRevenue / $totalRevenue) * 100, 2) : 0,
                'label' => 'Doanh thu combo',
            ],
            'service' => [
                'revenue' => $serviceRevenue,
                'percentage' => $totalRevenue > 0 ? round(($serviceRevenue / $totalRevenue) * 100, 2) : 0,
                'label' => 'Dịch vụ khác',
            ],
            'total' => $totalRevenue,
        ];
    }

    /**
     * Lấy danh sách phim cho bộ lọc
     */
    public function getMoviesList(): array
    {
        return Phims::query()
            ->orderBy('ten_phim')
            ->pluck('ten_phim', 'id')
            ->toArray();
    }

    /**
     * Lấy danh sách phòng chiếu cho bộ lọc
     */
    public function getRoomsList(): array
    {
        return PhongChieu::query()
            ->orderBy('ten_phong')
            ->pluck('ten_phong', 'id')
            ->toArray();
    }

    /**
     * Lấy dữ liệu chi tiết hóa đơn cho bảng
     */
    public function getDetailedInvoiceData(int $limit = 50): array
    {
        // Lấy dữ liệu vé
        $tickets = VeXemPhim::query()
            ->whereBetween('ve_xem_phims.created_at', [$this->from, $this->to])
            ->whereIn('ve_xem_phims.trang_thai', ['da_thanh_toan', 'da_su_dung'])
            ->when($this->phimId, fn($q) => $q->whereHas('suatChieu', fn($sq) => $sq->where('phim_id', $this->phimId)))
            ->when($this->phongChieuId, fn($q) => $q->whereHas('suatChieu', fn($sq) => $sq->where('phong_chieu_id', $this->phongChieuId)))
            ->with(['suatChieu.phim', 'suatChieu.phongChieu'])
            ->orderByDesc('ve_xem_phims.created_at')
            ->limit($limit)
            ->get()
            ->map(function ($ticket) {
                return [
                    'type' => 'ticket',
                    'invoice_code' => $ticket->ma_ve,
                    'payment_date' => $ticket->created_at,
                    'movie' => $ticket->ten_phim,
                    'showtime' => $ticket->suatChieu?->thoi_gian_chieu,
                    'room' => $ticket->ten_phong,
                    'seats' => $ticket->ma_ghe,
                    'ticket_count' => 1,
                    'ticket_revenue' => $ticket->tong_tien,
                    'food_revenue' => 0,
                    'voucher_discount' => 0,
                    'payment_method' => $ticket->loai_ve === 'tai_quay' ? 'Tại quầy' : 'Trực tuyến',
                    'total' => $ticket->tong_tien,
                ];
            });

        // Lấy dữ liệu đồ ăn
        $foodInvoices = FoodInvoice::query()
            ->whereBetween('created_at', [$this->from, $this->to])
            ->where('payment_status', 'paid')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function ($invoice) {
                return [
                    'type' => 'food',
                    'invoice_code' => $invoice->invoice_code,
                    'payment_date' => $invoice->created_at,
                    'movie' => null,
                    'showtime' => null,
                    'room' => null,
                    'seats' => null,
                    'ticket_count' => 0,
                    'ticket_revenue' => 0,
                    'food_revenue' => $invoice->total,
                    'voucher_discount' => $invoice->discount,
                    'payment_method' => $invoice->payment_method ?? 'Tiền mặt',
                    'total' => $invoice->total,
                ];
            });

        // Gộp và sắp xếp theo ngày
        $combined = $tickets->merge($foodInvoices)
            ->sortByDesc('payment_date')
            ->take($limit)
            ->values()
            ->toArray();

        return $combined;
    }

    /**
     * Export dữ liệu đầy đủ cho Excel/PDF
     */
    public function getFullExportData(): array
    {
        return [
            'kpi' => $this->getKPISummary(),
            'revenue_by_time' => $this->getRevenueByTime('day'),
            'revenue_structure' => $this->getRevenueStructure(),
            'top_films' => $this->getRevenueByFilm(),
            'revenue_by_room' => $this->getRevenueByRoom(),
            'revenue_by_seat_type' => $this->getRevenueBySeatType(),
            'revenue_by_time_slot' => $this->getRevenueByTimeSlot(),
            'payment_methods' => $this->getPaymentMethodStats(),
            'voucher_stats' => $this->getVoucherStats(),
            'top_showtimes' => $this->getTopShowtimes(),
            'from' => $this->from,
            'to' => $this->to,
        ];
    }
}
