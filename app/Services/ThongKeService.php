<?php

namespace App\Services;

use App\Models\VeXemPhim;
use App\Models\FoodInvoice;
use App\Models\SuatChieu;
use App\Models\Phims;
use App\Models\PhongChieu;
use App\Models\NguoiDungVoucher;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class ThongKeService
{
    protected string $from;
    protected string $to;
    protected ?int $phimId;
    protected ?int $phongChieuId;

    protected array $cachedQueries = [];

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

    // =========================================================
    // I. QUERY BUILDERS
    // =========================================================

    /**
     * Hóa đơn vé đã thanh toán.
     */
    protected function getPaidTicketBaseQuery()
    {
        return VeXemPhim::query()
            ->whereBetween('created_at', [
                $this->from,
                $this->to
            ])
            ->whereIn('trang_thai', [
                'da_thanh_toan',
                'da_su_dung',
            ]);
    }

    /**
     * Hóa đơn đồ ăn đã thanh toán.
     *
     * Đồ ăn mua ngoài không gắn với phim/phòng.
     */
    protected function getPaidFoodInvoiceBaseQuery()
    {
        return FoodInvoice::query()
            ->whereBetween('created_at', [
                $this->from,
                $this->to
            ])
            ->where('payment_status', 'paid');
    }

    /**
     * Có đang lọc phim hoặc phòng hay không.
     */
    protected function hasMovieOrRoomFilter(): bool
    {
        return !empty($this->phimId)
            || !empty($this->phongChieuId);
    }

    /**
     * Lọc vé theo phim.
     *
     * Quan hệ:
     * ve_xem_phims
     *      -> suat_chieus
     *      -> phim_id
     */
    protected function applyMovieFilter($query)
    {
        if ($this->phimId) {
            $query->whereHas('suatChieu', function ($q) {
                $q->where(
                    'phim_id',
                    $this->phimId
                );
            });
        }

        return $query;
    }

    /**
     * Lọc vé theo phòng.
     */
    protected function applyRoomFilter($query)
    {
        if ($this->phongChieuId) {
            $query->whereHas('suatChieu', function ($q) {
                $q->where(
                    'phong_chieu_id',
                    $this->phongChieuId
                );
            });
        }

        return $query;
    }

    // =========================================================
    // II. KPI
    // =========================================================

    public function getKPISummary(): array
    {
        try {
            $ticketRevenue = $this->getTicketRevenue();

            $foodStats = $this->getFoodRevenueStats();

            /*
             * Nếu lọc phim/phòng:
             * Không tính đồ ăn.
             */
            if ($this->hasMovieOrRoomFilter()) {
                $comboRevenue = 0;
                $snackRevenue = 0;
                $foodInvoicesCount = 0;
            } else {
                $comboRevenue = $foodStats['combo_revenue'];
                $snackRevenue = $foodStats['snack_revenue'];
                $foodInvoicesCount = $foodStats['count'];
            }

            $totalRevenue =
                $ticketRevenue
                + $comboRevenue
                + $snackRevenue;

            $ticketsSold = $this->getTicketsSoldCount();

            $totalInvoices =
                $ticketsSold
                + $foodInvoicesCount;

            $averageTicketPrice =
                $ticketsSold > 0
                    ? $ticketRevenue / $ticketsSold
                    : 0;

            $totalShowtimes =
                $this->getTotalShowtimes();

            $vouchersUsed =
                $this->getVouchersUsedCount();

            return [
                'total_revenue' => round(
                    $totalRevenue,
                    0
                ),

                'ticket_revenue' => round(
                    $ticketRevenue,
                    0
                ),

                'combo_revenue' => round(
                    $comboRevenue,
                    0
                ),

                'snack_revenue' => round(
                    $snackRevenue,
                    0
                ),

                'tickets_sold' => $ticketsSold,

                'total_invoices' => $totalInvoices,

                'food_invoices' => $foodInvoicesCount,

                'average_ticket_price' => round(
                    $averageTicketPrice,
                    0
                ),

                'total_showtimes' => $totalShowtimes,

                'vouchers_used' => $vouchersUsed,
            ];
        } catch (Exception $e) {
            report($e);

            return $this->getEmptyKPISummary();
        }
    }

    protected function getEmptyKPISummary(): array
    {
        return [
            'total_revenue' => 0,
            'ticket_revenue' => 0,
            'combo_revenue' => 0,
            'snack_revenue' => 0,
            'tickets_sold' => 0,
            'total_invoices' => 0,
            'food_invoices' => 0,
            'average_ticket_price' => 0,
            'total_showtimes' => 0,
            'vouchers_used' => 0,
        ];
    }

    // =========================================================
    // III. DOANH THU VÉ
    // =========================================================

    protected function getTicketRevenue(): float
    {
        $query = $this->getPaidTicketBaseQuery();

        $this->applyMovieFilter($query);
        $this->applyRoomFilter($query);

        return (float) $query->sum('tong_tien');
    }

    protected function getTicketsSoldCount(): int
    {
        $query = $this->getPaidTicketBaseQuery();

        $this->applyMovieFilter($query);
        $this->applyRoomFilter($query);

        return (int) $query->count();
    }

    // =========================================================
    // IV. HELPER ĐỒ ĂN
    // =========================================================

    protected function isComboCategory($category): bool
    {
        if (!$category) {
            return false;
        }

        if ($category->is_combo) {
            return true;
        }

        $name = strtolower(
            $category->name ?? ''
        );

        return str_contains(
            $name,
            'combo'
        );
    }

    protected function getFoodRevenueStats(): array
    {
        $foodInvoices =
            $this->getPaidFoodInvoiceBaseQuery()
                ->with([
                    'items.food',
                    'items.food.category',
                ])
                ->get();

        $comboRevenue = 0;
        $snackRevenue = 0;

        $count = $foodInvoices->count();

        foreach ($foodInvoices as $invoice) {

            foreach ($invoice->items as $item) {

                $itemTotal =
                    (float) (
                        $item->unit_price ?? 0
                    )
                    *
                    (int) (
                        $item->quantity ?? 1
                    );

                if (
                    $item->food_id
                    && $item->food
                ) {
                    $category =
                        $item->food->category;

                    if (
                        $this->isComboCategory(
                            $category
                        )
                    ) {
                        $comboRevenue += $itemTotal;
                    } else {
                        $snackRevenue += $itemTotal;
                    }
                } else {
                    $snackRevenue += $itemTotal;
                }
            }
        }

        return [
            'combo_revenue' => $comboRevenue,
            'snack_revenue' => $snackRevenue,
            'count' => $count,
        ];
    }

    // =========================================================
    // V. SUẤT CHIẾU
    // =========================================================

    protected function getTotalShowtimes(): int
    {
        return (int) SuatChieu::query()
            ->whereBetween(
                'thoi_gian_chieu',
                [
                    $this->from,
                    $this->to
                ]
            )
            ->when(
                $this->phimId,
                fn($q) => $q->where(
                    'phim_id',
                    $this->phimId
                )
            )
            ->when(
                $this->phongChieuId,
                fn($q) => $q->where(
                    'phong_chieu_id',
                    $this->phongChieuId
                )
            )
            ->whereNotIn(
                'trang_thai',
                ['huy']
            )
            ->count();
    }

    protected function getVouchersUsedCount(): int
    {
        return (int) NguoiDungVoucher::query()
            ->whereBetween(
                'ngay_su_dung',
                [
                    $this->from,
                    $this->to
                ]
            )
            ->where(
                'da_su_dung',
                true
            )
            ->count();
    }

    // =========================================================
    // VI. DOANH THU THEO THỜI GIAN
    // =========================================================

    public function getRevenueByTime(
        string $periodType = 'day'
    ): array {
        try {
            return match ($periodType) {
                'month' =>
                    $this->getMonthlyRevenue(),

                'quarter' =>
                    $this->getQuarterlyRevenue(),

                'year' =>
                    $this->getYearlyRevenue(),

                default =>
                    $this->getDailyRevenue(),
            };
        } catch (Exception $e) {

            report($e);

            return [];
        }
    }

    protected function getDailyRevenue(): array
    {
        $ticketData =
            $this->getTicketRevenueByPeriod(
                '%Y-%m-%d'
            );

        if (
            $this->hasMovieOrRoomFilter()
        ) {
            $foodData = [];
        } else {
            $foodData =
                $this->getFoodRevenueByPeriod(
                    '%Y-%m-%d'
                );
        }

        return $this->mergeRevenueData(
            $ticketData,
            $foodData,
            '%Y-%m-%d'
        );
    }

    protected function getMonthlyRevenue(): array
    {
        $ticketData =
            $this->getTicketRevenueByPeriod(
                '%Y-%m'
            );

        if (
            $this->hasMovieOrRoomFilter()
        ) {
            $foodData = [];
        } else {
            $foodData =
                $this->getFoodRevenueByPeriod(
                    '%Y-%m'
                );
        }

        return $this->mergeRevenueData(
            $ticketData,
            $foodData,
            '%Y-%m'
        );
    }

    protected function getQuarterlyRevenue(): array
    {
        $ticketData =
            $this->getTicketRevenueByPeriod(
                '%Y-%m'
            );

        if (
            $this->hasMovieOrRoomFilter()
        ) {
            $foodData = [];
        } else {
            $foodData =
                $this->getFoodRevenueByPeriod(
                    '%Y-%m'
                );
        }

        return $this->processQuarterlyData(
            $ticketData,
            $foodData
        );
    }

    protected function getYearlyRevenue(): array
    {
        $ticketData =
            $this->getTicketRevenueByPeriod(
                '%Y'
            );

        if (
            $this->hasMovieOrRoomFilter()
        ) {
            $foodData = [];
        } else {
            $foodData =
                $this->getFoodRevenueByPeriod(
                    '%Y'
                );
        }

        return $this->mergeRevenueData(
            $ticketData,
            $foodData,
            '%Y'
        );
    }

    // =========================================================
    // VII. DOANH THU VÉ THEO THỜI GIAN
    // =========================================================

    protected function getTicketRevenueByPeriod(
        string $format
    ): array {
        $query =
            $this->getPaidTicketBaseQuery();

        $this->applyMovieFilter($query);
        $this->applyRoomFilter($query);

        return $query
            ->select(
                DB::raw(
                    "DATE_FORMAT(
                        created_at,
                        '{$format}'
                    ) as period"
                ),
                DB::raw(
                    'SUM(tong_tien) as revenue'
                ),
                DB::raw(
                    'COUNT(*) as tickets'
                )
            )
            ->groupBy('period')
            ->get()
            ->keyBy('period')
            ->toArray();
    }

    // =========================================================
    // VIII. DOANH THU ĐỒ ĂN THEO THỜI GIAN
    // =========================================================

    protected function getFoodRevenueByPeriod(
        string $format
    ): array {
        $foodInvoices =
            $this->getPaidFoodInvoiceBaseQuery()
                ->with([
                    'items.food',
                    'items.food.category',
                ])
                ->get()
                ->groupBy(
                    function ($invoice) use ($format) {

                        return Carbon::parse(
                            $invoice->created_at
                        )->format(
                            str_replace(
                                '%',
                                '',
                                $format
                            )
                        );
                    }
                );

        $result = [];

        foreach (
            $foodInvoices as $period => $invoices
        ) {

            $comboRevenue = 0;
            $snackRevenue = 0;

            foreach ($invoices as $invoice) {

                foreach (
                    $invoice->items as $item
                ) {

                    $itemTotal =
                        (float) (
                            $item->unit_price ?? 0
                        )
                        *
                        (int) (
                            $item->quantity ?? 1
                        );

                    if (
                        $item->food_id
                        && $item->food
                        && $this->isComboCategory(
                            $item->food->category
                        )
                    ) {
                        $comboRevenue +=
                            $itemTotal;
                    } else {
                        $snackRevenue +=
                            $itemTotal;
                    }
                }
            }

            $result[$period] = [
                'combo_revenue' =>
                    $comboRevenue,

                'snack_revenue' =>
                    $snackRevenue,
            ];
        }

        return $result;
    }

    // =========================================================
    // IX. MERGE DOANH THU
    // =========================================================

    protected function mergeRevenueData(
        array $ticketData,
        array $foodData,
        string $format
    ): array {

        $allPeriods =
            collect(
                array_keys($ticketData)
            )
            ->merge(
                collect(
                    array_keys($foodData)
                )
            )
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        $result = [];

        foreach ($allPeriods as $period) {

            $ticket =
                $ticketData[$period]
                ?? null;

            $food =
                $foodData[$period]
                ?? null;

            $ticketRevenue =
                $ticket['revenue']
                ?? 0;

            $comboRevenue =
                $food['combo_revenue']
                ?? 0;

            $snackRevenue =
                $food['snack_revenue']
                ?? 0;

            $result[] = [
                'period' => $period,

                'ticket_revenue' =>
                    $ticketRevenue,

                'combo_revenue' =>
                    $comboRevenue,

                'snack_revenue' =>
                    $snackRevenue,

                'tickets' =>
                    $ticket['tickets']
                    ?? 0,

                'total_revenue' =>
                    $ticketRevenue
                    + $comboRevenue
                    + $snackRevenue,
            ];
        }

        return $result;
    }

    // =========================================================
    // X. QUÝ
    // =========================================================

    protected function processQuarterlyData(
        array $ticketData,
        array $foodData
    ): array {

        $result = [];

        foreach (
            $ticketData as $period => $ticket
        ) {

            $year =
                substr($period, 0, 4);

            $month =
                (int) substr(
                    $period,
                    5,
                    2
                );

            $quarter =
                ceil($month / 3);

            $key =
                "{$year}-Q{$quarter}";

            if (!isset($result[$key])) {

                $result[$key] = [
                    'period' => $key,

                    'ticket_revenue' => 0,

                    'combo_revenue' => 0,

                    'snack_revenue' => 0,

                    'tickets' => 0,

                    'total_revenue' => 0,
                ];
            }

            $result[$key]['ticket_revenue']
                += $ticket['revenue'];

            $result[$key]['tickets']
                += $ticket['tickets'];
        }

        foreach (
            $foodData as $period => $food
        ) {

            $year =
                substr($period, 0, 4);

            $month =
                (int) substr(
                    $period,
                    5,
                    2
                );

            $quarter =
                ceil($month / 3);

            $key =
                "{$year}-Q{$quarter}";

            if (!isset($result[$key])) {

                $result[$key] = [
                    'period' => $key,

                    'ticket_revenue' => 0,

                    'combo_revenue' => 0,

                    'snack_revenue' => 0,

                    'tickets' => 0,

                    'total_revenue' => 0,
                ];
            }

            $result[$key]['combo_revenue']
                += $food['combo_revenue'];

            $result[$key]['snack_revenue']
                += $food['snack_revenue'];
        }

        foreach ($result as &$item) {

            $item['total_revenue'] =
                $item['ticket_revenue']
                + $item['combo_revenue']
                + $item['snack_revenue'];
        }

        unset($item);

        ksort($result);

        return array_values($result);
    }

    // =========================================================
    // XI. CƠ CẤU DOANH THU
    // =========================================================

    public function getRevenueStructure(): array
    {
        try {

            $ticketRevenue =
                $this->getTicketRevenue();

            $foodStats =
                $this->getFoodRevenueStats();

            if (
                $this->hasMovieOrRoomFilter()
            ) {

                $comboRevenue = 0;
                $snackRevenue = 0;

            } else {

                $comboRevenue =
                    $foodStats['combo_revenue'];

                $snackRevenue =
                    $foodStats['snack_revenue'];
            }

            $totalRevenue =
                $ticketRevenue
                + $comboRevenue
                + $snackRevenue;

            if ($totalRevenue == 0) {

                return [
                    'ticket' => [
                        'revenue' => 0,
                        'percentage' => 0,
                        'label' => 'Vé',
                    ],

                    'combo' => [
                        'revenue' => 0,
                        'percentage' => 0,
                        'label' => 'Combo',
                    ],

                    'food' => [
                        'revenue' => 0,
                        'percentage' => 0,
                        'label' => 'Đồ ăn & Nước',
                    ],

                    'total' => 0,
                ];
            }

            return [
                'ticket' => [
                    'revenue' =>
                        round(
                            $ticketRevenue,
                            0
                        ),

                    'percentage' =>
                        round(
                            (
                                $ticketRevenue
                                / $totalRevenue
                            ) * 100,
                            2
                        ),

                    'label' => 'Vé',
                ],

                'combo' => [
                    'revenue' =>
                        round(
                            $comboRevenue,
                            0
                        ),

                    'percentage' =>
                        round(
                            (
                                $comboRevenue
                                / $totalRevenue
                            ) * 100,
                            2
                        ),

                    'label' => 'Combo',
                ],

                'food' => [
                    'revenue' =>
                        round(
                            $snackRevenue,
                            0
                        ),

                    'percentage' =>
                        round(
                            (
                                $snackRevenue
                                / $totalRevenue
                            ) * 100,
                            2
                        ),

                    'label' =>
                        'Đồ ăn & Nước',
                ],

                'total' =>
                    round(
                        $totalRevenue,
                        0
                    ),
            ];

        } catch (Exception $e) {

            report($e);

            return [
                'ticket' => [
                    'revenue' => 0,
                    'percentage' => 0,
                    'label' => 'Vé',
                ],

                'combo' => [
                    'revenue' => 0,
                    'percentage' => 0,
                    'label' => 'Combo',
                ],

                'food' => [
                    'revenue' => 0,
                    'percentage' => 0,
                    'label' => 'Đồ ăn & Nước',
                ],

                'total' => 0,
            ];
        }
    }

    // =========================================================
    // XII. TOP PHIM
    // =========================================================

    public function getRevenueByFilm(
        int $limit = 5
    ): array {

        try {

            $query =
                $this->getPaidTicketBaseQuery();

            /*
             * QUAN TRỌNG:
             * Áp dụng cả filter phim và phòng.
             */
            $this->applyMovieFilter($query);
            $this->applyRoomFilter($query);

            $result =
                $query
                    ->select(
                        'ten_phim',

                        DB::raw(
                            'SUM(tong_tien) as total_revenue'
                        ),

                        DB::raw(
                            'COUNT(*) as tickets_sold'
                        ),

                        DB::raw(
                            'AVG(tong_tien) as avg_price'
                        )
                    )
                    ->groupBy(
                        'ten_phim'
                    )
                    ->orderByDesc(
                        'total_revenue'
                    )
                    ->limit($limit)
                    ->get();

            return $result
                ->map(
                    function (
                        $item,
                        $index
                    ) {

                        return [
                            'stt' =>
                                $index + 1,

                            'ten_phim' =>
                                $item->ten_phim,

                            'tickets_sold' =>
                                (int)
                                $item->tickets_sold,

                            'total_revenue' =>
                                (float)
                                $item->total_revenue,

                            'avg_price' =>
                                (float)
                                $item->avg_price,
                        ];
                    }
                )
                ->toArray();

        } catch (Exception $e) {

            report($e);

            return [];
        }
    }

    // =========================================================
    // XIII. DOANH THU THEO PHÒNG
    // =========================================================

    public function getRevenueByRoom(): array
    {
        try {

            $query =
                $this->getPaidTicketBaseQuery();

            /*
             * Áp dụng filter phim + phòng.
             */
            $this->applyMovieFilter($query);
            $this->applyRoomFilter($query);

            $result =
                $query
                    ->select(
                        'ten_phong',

                        DB::raw(
                            'SUM(tong_tien) as total_revenue'
                        ),

                        DB::raw(
                            'COUNT(*) as tickets_sold'
                        )
                    )
                    ->whereNotNull(
                        'ten_phong'
                    )
                    ->groupBy(
                        'ten_phong'
                    )
                    ->orderByDesc(
                        'total_revenue'
                    )
                    ->get();

            return $result
                ->map(
                    function (
                        $item,
                        $index
                    ) {

                        return [
                            'id' =>
                                $index + 1,

                            'ten_phong' =>
                                $item->ten_phong,

                            'total_revenue' =>
                                (float)
                                $item->total_revenue,

                            'tickets_sold' =>
                                (int)
                                $item->tickets_sold,
                        ];
                    }
                )
                ->toArray();

        } catch (Exception $e) {

            report($e);

            return [];
        }
    }

    // =========================================================
    // XIV. DOANH THU THEO LOẠI GHẾ
    // =========================================================

    public function getRevenueBySeatType(): array
    {
        try {

            $query =
                $this->getPaidTicketBaseQuery();

            $this->applyMovieFilter($query);
            $this->applyRoomFilter($query);

            $tickets =
                $query
                    ->with([
                        'suatChieu.phongChieu.gheNgois.loaiGhe',
                        'suatChieu.phongChieu.hangGhes.loaiGheMacDinh',
                    ])
                    ->get();

            $seatTypeRevenue = [];

            foreach (
                $tickets as $ticket
            ) {

                $seatCodesRaw =
                    str_replace(
                        ['|', ' '],
                        ',',
                        (string)
                        $ticket->ma_ghe
                    );

                $seatCodes =
                    array_filter(
                        array_map(
                            'trim',
                            explode(
                                ',',
                                $seatCodesRaw
                            )
                        )
                    );

                $seatCount =
                    count($seatCodes);

                if (
                    $seatCount == 0
                ) {
                    continue;
                }

                $room =
                    $ticket
                        ->suatChieu
                        ?->phongChieu;

                if (!$room) {
                    continue;
                }

                $pricePerSeat =
                    (float)
                    $ticket->tong_tien
                    / $seatCount;

                foreach (
                    $seatCodes as $seatCode
                ) {

                    $seatCode =
                        strtoupper(
                            trim($seatCode)
                        );

                    if (
                        empty($seatCode)
                    ) {
                        continue;
                    }

                    $seat =
                        $room
                            ->gheNgois
                            ->first(
                                fn($ghe) =>
                                    strtoupper(
                                        trim(
                                            (string)
                                            $ghe->ma_ghe
                                        )
                                    ) === $seatCode
                            );

                    $seatTypeName =
                        $seat?->loaiGhe?->ten_loai
                        ?? 'Ghế thường';

                    $seatTypeId =
                        $seat?->loai_ghe_id
                        ?? 'default';

                    if (
                        !isset(
                            $seatTypeRevenue[
                                $seatTypeId
                            ]
                        )
                    ) {

                        $seatTypeRevenue[
                            $seatTypeId
                        ] = [

                            'id' =>
                                $seatTypeId,

                            'ten_loai' =>
                                $seatTypeName,

                            'total_revenue' =>
                                0,

                            'tickets_sold' =>
                                0,
                        ];
                    }

                    $seatTypeRevenue[
                        $seatTypeId
                    ]['total_revenue']
                        += $pricePerSeat;

                    $seatTypeRevenue[
                        $seatTypeId
                    ]['tickets_sold']
                        += 1;
                }
            }

            usort(
                $seatTypeRevenue,
                fn($a, $b) =>
                    $b['total_revenue']
                    <=>
                    $a['total_revenue']
            );

            return array_values(
                $seatTypeRevenue
            );

        } catch (Exception $e) {

            report($e);

            return [];
        }
    }

    // =========================================================
    // XV. PHƯƠNG THỨC THANH TOÁN
    // =========================================================

    public function getPaymentMethodStats(): array
    {
        try {

            /*
             * VÉ
             */
            $ticketQuery =
                $this->getPaidTicketBaseQuery();

            $this->applyMovieFilter(
                $ticketQuery
            );

            $this->applyRoomFilter(
                $ticketQuery
            );

            $ticketPayments =
                $ticketQuery
                    ->select(
                        DB::raw(
                            "COALESCE(
                                ve_xem_phims.payment_method,
                                'truc_tuyen'
                            ) as payment_method"
                        ),

                        DB::raw(
                            'SUM(
                                ve_xem_phims.tong_tien
                            ) as total'
                        ),

                        DB::raw(
                            'COUNT(
                                ve_xem_phims.id
                            ) as count'
                        )
                    )
                    ->groupBy(
                        DB::raw(
                            "COALESCE(
                                ve_xem_phims.payment_method,
                                'truc_tuyen'
                            )"
                        )
                    )
                    ->get()
                    ->keyBy(
                        'payment_method'
                    );

            /*
             * ĐỒ ĂN
             *
             * Khi lọc phim/phòng:
             * không tính đồ ăn.
             */
            if (
                $this->hasMovieOrRoomFilter()
            ) {

                $foodPayments =
                    collect();

            } else {

                $foodPayments =
                    $this
                        ->getPaidFoodInvoiceBaseQuery()
                        ->select(
                            DB::raw(
                                "COALESCE(
                                    payment_method,
                                    'cash'
                                ) as payment_method"
                            ),

                            DB::raw(
                                'SUM(total) as total'
                            ),

                            DB::raw(
                                'COUNT(*) as count'
                            )
                        )
                        ->groupBy(
                            DB::raw(
                                "COALESCE(
                                    payment_method,
                                    'cash'
                                )"
                            )
                        )
                        ->get()
                        ->keyBy(
                            'payment_method'
                        );
            }

            $allMethods =
                $ticketPayments
                    ->keys()
                    ->merge(
                        $foodPayments->keys()
                    )
                    ->unique();

            $methodLabels = [

                'truc_tuyen' =>
                    'Trực tuyến',

                'tai_quay' =>
                    'Tại quầy',

                'cash' =>
                    'Tiền mặt',

                'vnpay' =>
                    'VNPay',

                'vietqr' =>
                    'VietQR',

                'zalopay' =>
                    'ZaloPay',

                'momo' =>
                    'MoMo',

                'payos' =>
                    'PayOS',
            ];

            $methods = [];

            foreach (
                $allMethods as $method
            ) {

                $ticket =
                    $ticketPayments->get(
                        $method
                    );

                $food =
                    $foodPayments->get(
                        $method
                    );

                $ticketRevenue =
                    (float) (
                        $ticket->total
                        ?? 0
                    );

                $foodRevenue =
                    (float) (
                        $food->total
                        ?? 0
                    );

                $methods[] = [

                    'method' =>
                        $method,

                    'label' =>
                        $methodLabels[
                            $method
                        ]
                        ?? ucfirst($method),

                    'ticket_revenue' =>
                        $ticketRevenue,

                    'food_revenue' =>
                        $foodRevenue,

                    'total_revenue' =>
                        $ticketRevenue
                        + $foodRevenue,

                    'count' =>
                        (int) (
                            $ticket->count
                            ?? 0
                        )
                        +
                        (int) (
                            $food->count
                            ?? 0
                        ),
                ];
            }

            usort(
                $methods,
                fn($a, $b) =>
                    $b['total_revenue']
                    <=>
                    $a['total_revenue']
            );

            return $methods;

        } catch (Exception $e) {

            report($e);

            return [];
        }
    }

    // =========================================================
    // XVI. VOUCHER
    // =========================================================

    public function getVoucherStats(): array
    {
        try {

            $totalIssued =
                (int)
                NguoiDungVoucher::query()
                    ->whereBetween(
                        'ngay_nhan',
                        [
                            $this->from,
                            $this->to
                        ]
                    )
                    ->count();

            $usedVouchers =
                NguoiDungVoucher::query()
                    ->whereBetween(
                        'ngay_su_dung',
                        [
                            $this->from,
                            $this->to
                        ]
                    )
                    ->where(
                        'da_su_dung',
                        true
                    )
                    ->with('voucher')
                    ->get();

            $totalUsed =
                $usedVouchers->count();

            $totalDiscount = 0;

            foreach (
                $usedVouchers as $usedVoucher
            ) {

                if (
                    $usedVoucher->voucher
                ) {

                    $totalDiscount +=
                        (float)
                        $usedVoucher
                            ->voucher
                            ->gia_tri_giam;
                }
            }

            return [

                'total_issued' =>
                    $totalIssued,

                'total_used' =>
                    $totalUsed,

                'total_discount' =>
                    round(
                        $totalDiscount,
                        0
                    ),

                'usage_rate' =>
                    $totalIssued > 0
                        ? round(
                            (
                                $totalUsed
                                / $totalIssued
                            ) * 100,
                            2
                        )
                        : 0,
            ];

        } catch (Exception $e) {

            report($e);

            return [
                'total_issued' => 0,
                'total_used' => 0,
                'total_discount' => 0,
                'usage_rate' => 0,
            ];
        }
    }

    // =========================================================
    // XVII. TOP SUẤT CHIẾU
    // =========================================================

    public function getTopShowtimes(
        int $limit = 5
    ): array {

        try {

            $query =
                $this->getPaidTicketBaseQuery();

            $this->applyMovieFilter($query);
            $this->applyRoomFilter($query);

            return $query
                ->join(
                    'suat_chieus',
                    've_xem_phims.suat_chieu_id',
                    '=',
                    'suat_chieus.id'
                )
                ->join(
                    'phims',
                    'suat_chieus.phim_id',
                    '=',
                    'phims.id'
                )
                ->join(
                    'phong_chieus',
                    'suat_chieus.phong_chieu_id',
                    '=',
                    'phong_chieus.id'
                )
                ->select(

                    'suat_chieus.id',

                    'phims.ten_phim',

                    'phong_chieus.ten_phong',

                    'suat_chieus.thoi_gian_chieu',

                    DB::raw(
                        'SUM(
                            ve_xem_phims.tong_tien
                        ) as total_revenue'
                    ),

                    DB::raw(
                        'COUNT(
                            ve_xem_phims.id
                        ) as tickets_sold'
                    )
                )
                ->groupBy(
                    'suat_chieus.id',
                    'phims.ten_phim',
                    'phong_chieus.ten_phong',
                    'suat_chieus.thoi_gian_chieu'
                )
                ->orderByDesc(
                    'tickets_sold'
                )
                ->limit($limit)
                ->get()
                ->toArray();

        } catch (Exception $e) {

            report($e);

            return [];
        }
    }

    // =========================================================
    // XVIII. DOANH THU THEO KHUNG GIỜ
    // =========================================================

    public function getRevenueByTimeSlot(): array
    {
        try {

            $query =
                $this->getPaidTicketBaseQuery();

            $this->applyMovieFilter($query);
            $this->applyRoomFilter($query);

            return $query
                ->join(
                    'suat_chieus',
                    've_xem_phims.suat_chieu_id',
                    '=',
                    'suat_chieus.id'
                )
                ->select(

                    DB::raw(
                        "CASE
                            WHEN HOUR(
                                suat_chieus.thoi_gian_chieu
                            ) BETWEEN 6 AND 11
                            THEN 'Sáng (06:00 - 12:00)'

                            WHEN HOUR(
                                suat_chieus.thoi_gian_chieu
                            ) BETWEEN 12 AND 17
                            THEN 'Chiều (12:00 - 18:00)'

                            WHEN HOUR(
                                suat_chieus.thoi_gian_chieu
                            ) BETWEEN 18 AND 21
                            THEN 'Tối (18:00 - 22:00)'

                            ELSE 'Khuya (22:00 - 06:00)'
                        END as time_slot"
                    ),

                    DB::raw(
                        'SUM(
                            ve_xem_phims.tong_tien
                        ) as total_revenue'
                    ),

                    DB::raw(
                        'COUNT(
                            ve_xem_phims.id
                        ) as tickets_sold'
                    )
                )
                ->groupBy(
                    'time_slot'
                )
                ->orderByRaw(
                    "FIELD(
                        time_slot,
                        'Sáng (06:00 - 12:00)',
                        'Chiều (12:00 - 18:00)',
                        'Tối (18:00 - 22:00)',
                        'Khuya (22:00 - 06:00)'
                    )"
                )
                ->get()
                ->toArray();

        } catch (Exception $e) {

            report($e);

            return [];
        }
    }

    // =========================================================
    // XIX. DANH SÁCH PHIM
    // =========================================================

    public function getMoviesList(): array
    {
        try {

            return Phims::query()
                ->orderBy(
                    'ten_phim'
                )
                ->pluck(
                    'ten_phim',
                    'id'
                )
                ->toArray();

        } catch (Exception $e) {

            report($e);

            return [];
        }
    }

    // =========================================================
    // XX. DANH SÁCH PHÒNG
    // =========================================================

    public function getRoomsList(): array
    {
        try {

            return PhongChieu::query()
                ->orderBy(
                    'ten_phong'
                )
                ->pluck(
                    'ten_phong',
                    'id'
                )
                ->toArray();

        } catch (Exception $e) {

            report($e);

            return [];
        }
    }

    // =========================================================
    // XXI. FULL EXPORT
    // =========================================================

    public function getFullExportData(): array
    {
        try {

            return [

                'kpi' =>
                    $this->getKPISummary(),

                'revenue_by_time' =>
                    $this->getRevenueByTime(
                        'day'
                    ),

                'revenue_structure' =>
                    $this->getRevenueStructure(),

                'top_films' =>
                    $this->getRevenueByFilm(
                        10
                    ),

                'revenue_by_room' =>
                    $this->getRevenueByRoom(),

                'revenue_by_seat_type' =>
                    $this->getRevenueBySeatType(),

                'revenue_by_time_slot' =>
                    $this->getRevenueByTimeSlot(),

                'payment_methods' =>
                    $this->getPaymentMethodStats(),

                'voucher_stats' =>
                    $this->getVoucherStats(),

                'top_showtimes' =>
                    $this->getTopShowtimes(
                        10
                    ),

                'from' =>
                    $this->from,

                'to' =>
                    $this->to,

                'phim_id' =>
                    $this->phimId,

                'phong_chieu_id' =>
                    $this->phongChieuId,
            ];

        } catch (Exception $e) {

            report($e);

            return [];
        }
    }

    // =========================================================
    // XXII. API RESPONSE
    // =========================================================

    public function getApiResponse(): array
    {
        try {

            return [

                'success' => true,

                'data' => [

                    'summary' =>
                        $this->getKPISummary(),

                    'lineChart' =>
                        $this->getRevenueByTime(
                            'day'
                        ),

                    'revenueStructure' =>
                        $this->getRevenueStructure(),

                    'topMovies' =>
                        $this->getRevenueByFilm(
                            5
                        ),

                    'roomRevenue' =>
                        $this->getRevenueByRoom(),

                    'seatRevenue' =>
                        $this->getRevenueBySeatType(),

                    'paymentMethods' =>
                        $this->getPaymentMethodStats(),

                    'voucherStatistics' =>
                        $this->getVoucherStats(),

                    'movies' =>
                        $this->getMoviesList(),

                    'rooms' =>
                        $this->getRoomsList(),
                ],

                'filters' => [

                    'from' =>
                        $this->from,

                    'to' =>
                        $this->to,

                    'period_type' =>
                        'day',

                    'phim_id' =>
                        $this->phimId,

                    'phong_chieu_id' =>
                        $this->phongChieuId,
                ],
            ];

        } catch (Exception $e) {

            report($e);

            return [

                'success' => false,

                'message' =>
                    'Đã xảy ra lỗi khi lấy dữ liệu thống kê',

                'error' =>
                    config('app.debug')
                        ? $e->getMessage()
                        : null,
            ];
        }
    }
}