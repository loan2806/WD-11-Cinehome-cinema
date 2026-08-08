<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ThongKeService;
use App\Exports\RevenueExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;

class ThongKeController extends Controller
{
    /**
     * =========================================================
     * TRANG THỐNG KÊ
     * =========================================================
     */
    public function index(Request $request)
    {
        $params =
            $this->parseFilterParams(
                $request
            );

        $service =
            new ThongKeService(
                $params['from'],
                $params['to'],
                $params['phim_id'],
                $params['phong_chieu_id']
            );

        $kpi =
            $service->getKPISummary();

        $revenueByTime =
            $service->getRevenueByTime(
                $params['period_type']
            );

        $topFilms =
            $service->getRevenueByFilm();

        $topShowtimes =
            $service->getTopShowtimes();

        $revenueByRoom =
            $service->getRevenueByRoom();

        $revenueBySeatType =
            $service->getRevenueBySeatType();

        $revenueByTimeSlot =
            $service->getRevenueByTimeSlot();

        $paymentMethods =
            $service->getPaymentMethodStats();

        $voucherStats =
            $service->getVoucherStats();

        $revenueStructure =
            $service->getRevenueStructure();

        $movies =
            $service->getMoviesList();

        $rooms =
            $service->getRoomsList();

        return view(
            'admin.thong_ke.index',
            [

                'kpi' =>
                    $kpi,

                'revenueByTime' =>
                    $revenueByTime,

                'topFilms' =>
                    $topFilms,

                'topShowtimes' =>
                    $topShowtimes,

                'revenueByRoom' =>
                    $revenueByRoom,

                'revenueBySeatType' =>
                    $revenueBySeatType,

                'revenueByTimeSlot' =>
                    $revenueByTimeSlot,

                'paymentMethods' =>
                    $paymentMethods,

                'voucherStats' =>
                    $voucherStats,

                'revenueStructure' =>
                    $revenueStructure,

                'movies' =>
                    $movies,

                'rooms' =>
                    $rooms,

                // Khoảng query DB
                'from' =>
                    $params['from'],

                'to' =>
                    $params['to'],

                // Loại kỳ
                'periodType' =>
                    $params['period_type'],

                // Phim / phòng
                'phimId' =>
                    $params['phim_id'],

                'phongChieuId' =>
                    $params['phong_chieu_id'],

                // Ngày
                'fromDate' =>
                    $params['from_date'],

                'toDate' =>
                    $params['to_date'],

                // Tháng
                'fromMonth' =>
                    $params['from_month'],

                'toMonth' =>
                    $params['to_month'],

                // Quý
                'fromQuarter' =>
                    $params['from_quarter'],

                'fromQuarterYear' =>
                    $params['from_quarter_year'],

                'toQuarter' =>
                    $params['to_quarter'],

                'toQuarterYear' =>
                    $params['to_quarter_year'],

                // Năm
                'fromYear' =>
                    $params['from_year'],

                'toYear' =>
                    $params['to_year'],
            ]
        );
    }

    /**
     * =========================================================
     * API THỐNG KÊ
     * =========================================================
     */
    public function apiIndex(
        Request $request
    ): JsonResponse {

        try {

            $params =
                $this->parseFilterParams(
                    $request
                );

            $service =
                new ThongKeService(
                    $params['from'],
                    $params['to'],
                    $params['phim_id'],
                    $params['phong_chieu_id']
                );

            $response =
                $service->getApiResponse();

            /*
             * Ghi đè filters bằng giá trị
             * thực tế frontend gửi lên.
             */
            $response['filters'] = [

                'period_type' =>
                    $params['period_type'],

                'from' =>
                    $params['from'],

                'to' =>
                    $params['to'],

                'from_date' =>
                    $params['from_date'],

                'to_date' =>
                    $params['to_date'],

                'from_month' =>
                    $params['from_month'],

                'to_month' =>
                    $params['to_month'],

                'from_quarter' =>
                    $params['from_quarter'],

                'from_quarter_year' =>
                    $params['from_quarter_year'],

                'to_quarter' =>
                    $params['to_quarter'],

                'to_quarter_year' =>
                    $params['to_quarter_year'],

                'from_year' =>
                    $params['from_year'],

                'to_year' =>
                    $params['to_year'],

                'phim_id' =>
                    $params['phim_id'],

                'phong_chieu_id' =>
                    $params['phong_chieu_id'],
            ];

            return response()->json(
                $response
            );

        } catch (Exception $e) {

            report($e);

            return response()->json(
                [

                    'success' => false,

                    'message' =>
                        'Đã xảy ra lỗi khi lấy dữ liệu thống kê',

                    'error' =>
                        config('app.debug')
                            ? $e->getMessage()
                            : null,
                ],
                500
            );
        }
    }

    /**
     * =========================================================
     * EXPORT EXCEL
     * =========================================================
     */
    public function exportExcel(
        Request $request
    ) {

        try {

            $params =
                $this->parseFilterParams(
                    $request
                );

            $service =
                new ThongKeService(
                    $params['from'],
                    $params['to'],
                    $params['phim_id'],
                    $params['phong_chieu_id']
                );

            $fileName =
                'Thong-ke-doanh-thu-'
                .
                Carbon::parse(
                    $params['from']
                )->format('Y-m-d')
                .
                '.xlsx';

            return Excel::download(
                new RevenueExport(
                    $service
                ),
                $fileName
            );

        } catch (Exception $e) {

            report($e);

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Đã xảy ra lỗi khi xuất file Excel'
                );
        }
    }

    /**
     * =========================================================
     * EXPORT PDF
     * =========================================================
     */
    public function exportPdf(
        Request $request
    ) {

        try {

            $params =
                $this->parseFilterParams(
                    $request
                );

            $service =
                new ThongKeService(
                    $params['from'],
                    $params['to'],
                    $params['phim_id'],
                    $params['phong_chieu_id']
                );

            $kpi =
                $service->getKPISummary();

            $revenueByTime =
                $service->getRevenueByTime(
                    'day'
                );

            $topFilms =
                $service->getRevenueByFilm();

            $revenueStructure =
                $service->getRevenueStructure();

            $from =
                $params['from'];

            $to =
                $params['to'];

            $pdf =
                Pdf::loadView(
                    'admin.thong_ke.pdf',
                    compact(
                        'kpi',
                        'revenueByTime',
                        'topFilms',
                        'revenueStructure',
                        'from',
                        'to'
                    )
                );

            $fileName =
                'Thong-ke-doanh-thu-'
                .
                Carbon::parse(
                    $params['from']
                )->format('Y-m-d')
                .
                '.pdf';

            return $pdf->download(
                $fileName
            );

        } catch (Exception $e) {

            report($e);

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Đã xảy ra lỗi khi xuất file PDF'
                );
        }
    }

    /**
     * =========================================================
     * PARSE FILTER
     * =========================================================
     */
    protected function parseFilterParams(
        Request $request
    ): array {

        $periodType =
            $request->input(
                'period_type',
                'day'
            );

        /*
         * =====================================================
         * PHIM
         * =====================================================
         */
        $phimId =
            $request->filled('phim_id')
                ? (int) $request->input(
                    'phim_id'
                )
                : null;

        /*
         * =====================================================
         * PHÒNG
         * =====================================================
         */
        $phongChieuId =
            $request->filled(
                'phong_chieu_id'
            )
                ? (int) $request->input(
                    'phong_chieu_id'
                )
                : null;

        $now =
            Carbon::now();

        // =====================================================
        // KHỞI TẠO
        // =====================================================

        $from = null;
        $to = null;

        $fromDate = null;
        $toDate = null;

        $fromMonth = null;
        $toMonth = null;

        $fromQuarter = null;
        $fromQuarterYear = null;

        $toQuarter = null;
        $toQuarterYear = null;

        $fromYear = null;
        $toYear = null;

        // =====================================================
        // NGÀY
        // =====================================================

        if (
            $periodType === 'day'
        ) {

            $fromDate =
                $request->input(
                    'from_date'
                )
                ?:
                $request->input(
                    'from'
                );

            $toDate =
                $request->input(
                    'to_date'
                )
                ?:
                $request->input(
                    'to'
                );

            $fromDate =
                $fromDate
                ? substr(
                    $fromDate,
                    0,
                    10
                )
                : $now->format(
                    'Y-m-d'
                );

            $toDate =
                $toDate
                ? substr(
                    $toDate,
                    0,
                    10
                )
                : $fromDate;

            try {

                $fromCarbon =
                    Carbon::createFromFormat(
                        'Y-m-d',
                        $fromDate
                    )->startOfDay();

                $toCarbon =
                    Carbon::createFromFormat(
                        'Y-m-d',
                        $toDate
                    )->endOfDay();

            } catch (Exception $e) {

                $fromDate =
                    $now->format(
                        'Y-m-d'
                    );

                $toDate =
                    $fromDate;

                $fromCarbon =
                    $now->copy()
                        ->startOfDay();

                $toCarbon =
                    $now->copy()
                        ->endOfDay();
            }

            /*
             * Nếu người dùng chọn ngược ngày:
             * tự đổi lại cho đúng.
             */
            if (
                $fromCarbon->gt(
                    $toCarbon
                )
            ) {

                [$fromCarbon, $toCarbon] =
                    [
                        $toCarbon
                            ->copy()
                            ->startOfDay(),

                        $fromCarbon
                            ->copy()
                            ->endOfDay(),
                    ];

                $fromDate =
                    $fromCarbon->format(
                        'Y-m-d'
                    );

                $toDate =
                    $toCarbon->format(
                        'Y-m-d'
                    );
            }

            $from =
                $fromCarbon
                    ->toDateTimeString();

            $to =
                $toCarbon
                    ->toDateTimeString();
        }

        // =====================================================
        // THÁNG
        // =====================================================

        elseif (
            $periodType === 'month'
        ) {

            $fromMonth =
                $request->input(
                    'from_month'
                )
                ?:
                $request->input(
                    'from'
                );

            $toMonth =
                $request->input(
                    'to_month'
                )
                ?:
                $request->input(
                    'to'
                );

            $fromMonth =
                $fromMonth
                ? substr(
                    $fromMonth,
                    0,
                    7
                )
                : $now->format(
                    'Y-m'
                );

            $toMonth =
                $toMonth
                ? substr(
                    $toMonth,
                    0,
                    7
                )
                : $fromMonth;

            try {

                $fromCarbon =
                    Carbon::createFromFormat(
                        'Y-m',
                        $fromMonth
                    )->startOfMonth();

                $toCarbon =
                    Carbon::createFromFormat(
                        'Y-m',
                        $toMonth
                    )->endOfMonth();

            } catch (Exception $e) {

                $fromMonth =
                    $now->format(
                        'Y-m'
                    );

                $toMonth =
                    $fromMonth;

                $fromCarbon =
                    $now->copy()
                        ->startOfMonth();

                $toCarbon =
                    $now->copy()
                        ->endOfMonth();
            }

            /*
             * Nếu chọn ngược tháng.
             */
            if (
                $fromCarbon->gt(
                    $toCarbon
                )
            ) {

                [$fromCarbon, $toCarbon] =
                    [
                        $toCarbon
                            ->copy()
                            ->startOfMonth(),

                        $fromCarbon
                            ->copy()
                            ->endOfMonth(),
                    ];
            }

            $fromMonth =
                $fromCarbon->format(
                    'Y-m'
                );

            $toMonth =
                $toCarbon->format(
                    'Y-m'
                );

            $from =
                $fromCarbon
                    ->startOfDay()
                    ->toDateTimeString();

            $to =
                $toCarbon
                    ->endOfDay()
                    ->toDateTimeString();
        }

        // =====================================================
        // QUÝ
        // =====================================================

        elseif (
            $periodType === 'quarter'
        ) {

            $fromQuarterInput =
                $request->input(
                    'from_quarter'
                );

            $fromQuarterYearInput =
                $request->input(
                    'from_quarter_year'
                );

            $toQuarterInput =
                $request->input(
                    'to_quarter'
                );

            $toQuarterYearInput =
                $request->input(
                    'to_quarter_year'
                );

            /*
             * Hỗ trợ frontend gửi:
             *
             * from = Q3/2026
             * to   = Q4/2026
             *
             * REGEX ĐÃ SỬA.
             */
            if (
                (
                    $fromQuarterInput === null
                    ||
                    $fromQuarterYearInput === null
                )
                &&
                preg_match(
                    '/^Q([1-4])\/(\d{4})$/i',
                    (string)
                    $request->input(
                        'from'
                    ),
                    $matches
                )
            ) {

                $fromQuarterInput =
                    (int) $matches[1];

                $fromQuarterYearInput =
                    (int) $matches[2];
            }

            if (
                (
                    $toQuarterInput === null
                    ||
                    $toQuarterYearInput === null
                )
                &&
                preg_match(
                    '/^Q([1-4])\/(\d{4})$/i',
                    (string)
                    $request->input(
                        'to'
                    ),
                    $matches
                )
            ) {

                $toQuarterInput =
                    (int) $matches[1];

                $toQuarterYearInput =
                    (int) $matches[2];
            }

            /*
             * Quý hiện tại.
             */
            $currentQuarter =
                (int) ceil(
                    $now->month / 3
                );

            $fromQuarter =
                $fromQuarterInput !== null
                    ? (int)
                        $fromQuarterInput
                    : $currentQuarter;

            $fromQuarterYear =
                $fromQuarterYearInput !== null
                    ? (int)
                        $fromQuarterYearInput
                    : $now->year;

            $toQuarter =
                $toQuarterInput !== null
                    ? (int)
                        $toQuarterInput
                    : $fromQuarter;

            $toQuarterYear =
                $toQuarterYearInput !== null
                    ? (int)
                        $toQuarterYearInput
                    : $fromQuarterYear;

            /*
             * Bảo vệ dữ liệu.
             */
            $fromQuarter =
                max(
                    1,
                    min(
                        4,
                        $fromQuarter
                    )
                );

            $toQuarter =
                max(
                    1,
                    min(
                        4,
                        $toQuarter
                    )
                );

            $fromQuarterYear =
                max(
                    1900,
                    min(
                        2100,
                        $fromQuarterYear
                    )
                );

            $toQuarterYear =
                max(
                    1900,
                    min(
                        2100,
                        $toQuarterYear
                    )
                );

            /*
             * Nếu chọn ngược quý/năm.
             */
            $fromQuarterIndex =
                ($fromQuarterYear * 4)
                + $fromQuarter;

            $toQuarterIndex =
                ($toQuarterYear * 4)
                + $toQuarter;

            if (
                $fromQuarterIndex
                > $toQuarterIndex
            ) {

                [
                    $fromQuarter,
                    $toQuarter
                ] = [
                    $toQuarter,
                    $fromQuarter
                ];

                [
                    $fromQuarterYear,
                    $toQuarterYear
                ] = [
                    $toQuarterYear,
                    $fromQuarterYear
                ];
            }

            /*
             * Tháng bắt đầu quý.
             */
            $fromStartMonth =
                (
                    ($fromQuarter - 1)
                    * 3
                ) + 1;

            $toStartMonth =
                (
                    ($toQuarter - 1)
                    * 3
                ) + 1;

            /*
             * Ngày bắt đầu.
             */
            $fromCarbon =
                Carbon::create(
                    $fromQuarterYear,
                    $fromStartMonth,
                    1,
                    0,
                    0,
                    0
                )->startOfMonth();

            /*
             * Ngày kết thúc.
             */
            $toCarbon =
                Carbon::create(
                    $toQuarterYear,
                    $toStartMonth,
                    1,
                    0,
                    0,
                    0
                )
                ->addMonths(2)
                ->endOfMonth();

            $from =
                $fromCarbon
                    ->startOfDay()
                    ->toDateTimeString();

            $to =
                $toCarbon
                    ->endOfDay()
                    ->toDateTimeString();
        }

        // =====================================================
        // NĂM
        // =====================================================

        elseif (
            $periodType === 'year'
        ) {

            $fromYearInput =
                $request->input(
                    'from_year'
                );

            $toYearInput =
                $request->input(
                    'to_year'
                );

            /*
             * Hỗ trợ frontend cũ:
             * from = 2025
             * to   = 2026
             */
            if (
                $fromYearInput === null
                &&
                is_numeric(
                    $request->input(
                        'from'
                    )
                )
            ) {

                $fromYearInput =
                    $request->input(
                        'from'
                    );
            }

            if (
                $toYearInput === null
                &&
                is_numeric(
                    $request->input(
                        'to'
                    )
                )
            ) {

                $toYearInput =
                    $request->input(
                        'to'
                    );
            }

            $fromYear =
                $fromYearInput !== null
                    ? (int)
                        $fromYearInput
                    : $now->year;

            $toYear =
                $toYearInput !== null
                    ? (int)
                        $toYearInput
                    : $fromYear;

            $fromYear =
                max(
                    1900,
                    min(
                        2100,
                        $fromYear
                    )
                );

            $toYear =
                max(
                    1900,
                    min(
                        2100,
                        $toYear
                    )
                );

            /*
             * Nếu chọn ngược năm.
             */
            if (
                $fromYear > $toYear
            ) {

                [
                    $fromYear,
                    $toYear
                ] = [
                    $toYear,
                    $fromYear
                ];
            }

            $fromCarbon =
                Carbon::create(
                    $fromYear,
                    1,
                    1,
                    0,
                    0,
                    0
                )->startOfYear();

            $toCarbon =
                Carbon::create(
                    $toYear,
                    12,
                    31,
                    23,
                    59,
                    59
                )->endOfYear();

            $from =
                $fromCarbon
                    ->startOfDay()
                    ->toDateTimeString();

            $to =
                $toCarbon
                    ->endOfDay()
                    ->toDateTimeString();
        }

        // =====================================================
        // FALLBACK
        // =====================================================

        else {

            $periodType = 'day';

            $fromDate =
                $now->format(
                    'Y-m-d'
                );

            $toDate =
                $fromDate;

            $from =
                $now
                    ->copy()
                    ->startOfDay()
                    ->toDateTimeString();

            $to =
                $now
                    ->copy()
                    ->endOfDay()
                    ->toDateTimeString();
        }

        // =====================================================
        // RETURN
        // =====================================================

        return [

            'from' =>
                $from,

            'to' =>
                $to,

            'period_type' =>
                $periodType,

            'phim_id' =>
                $phimId,

            'phong_chieu_id' =>
                $phongChieuId,

            // Ngày
            'from_date' =>
                $fromDate,

            'to_date' =>
                $toDate,

            // Tháng
            'from_month' =>
                $fromMonth,

            'to_month' =>
                $toMonth,

            // Quý
            'from_quarter' =>
                $fromQuarter,

            'from_quarter_year' =>
                $fromQuarterYear,

            'to_quarter' =>
                $toQuarter,

            'to_quarter_year' =>
                $toQuarterYear,

            // Năm
            'from_year' =>
                $fromYear,

            'to_year' =>
                $toYear,
        ];
    }
}