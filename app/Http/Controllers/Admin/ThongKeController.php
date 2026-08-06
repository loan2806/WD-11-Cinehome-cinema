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
use Illuminate\Support\Facades\Validator;
use Exception;

class ThongKeController extends Controller
{
    /**
     * Hiển thị trang thống kê doanh thu
     */
    public function index(Request $request)
    {
        $params = $this->parseFilterParams($request);

        $service = new ThongKeService(
            $params['from'],
            $params['to'],
            $params['phim_id'],
            $params['phong_chieu_id']
        );

        $kpi = $service->getKPISummary();
        $revenueByTime = $service->getRevenueByTime($params['period_type']);
        $topFilms = $service->getRevenueByFilm();
        $revenueByRoom = $service->getRevenueByRoom();
        $revenueBySeatType = $service->getRevenueBySeatType();
        $revenueByTimeSlot = $service->getRevenueByTimeSlot();
        $paymentMethods = $service->getPaymentMethodStats();
        $voucherStats = $service->getVoucherStats();
        $revenueStructure = $service->getRevenueStructure();
        $topShowtimes = $service->getTopShowtimes();

        $movies = $service->getMoviesList();
        $rooms = $service->getRoomsList();

        return view('admin.thong_ke.index', [
            'kpi' => $kpi,
            'revenueByTime' => $revenueByTime,
            'topFilms' => $topFilms,
            'topShowtimes' => $topShowtimes,
            'revenueByRoom' => $revenueByRoom,
            'revenueBySeatType' => $revenueBySeatType,
            'revenueByTimeSlot' => $revenueByTimeSlot,
            'paymentMethods' => $paymentMethods,
            'voucherStats' => $voucherStats,
            'revenueStructure' => $revenueStructure,
            'movies' => $movies,
            'rooms' => $rooms,
            'from' => $params['from'],
            'to' => $params['to'],
            'periodType' => $params['period_type'],
            'phimId' => $params['phim_id'],
            'phongChieuId' => $params['phong_chieu_id'],
        ]);
    }

    /**
     * API: Lấy dữ liệu thống kê (JSON)
     * Endpoint: GET /admin/api/statistics
     */
    public function apiIndex(Request $request): JsonResponse
    {
        try {
            $params = $this->parseFilterParams($request);

            $service = new ThongKeService(
                $params['from'],
                $params['to'],
                $params['phim_id'],
                $params['phong_chieu_id']
            );

            return response()->json($service->getApiResponse());
        } catch (Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy dữ liệu thống kê',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Xuất báo cáo Excel
     */
    public function exportExcel(Request $request)
    {
        try {
            $params = $this->parseFilterParams($request);

            $service = new ThongKeService(
                $params['from'],
                $params['to'],
                $params['phim_id'],
                $params['phong_chieu_id']
            );

            $fileName = 'Thong-ke-doanh-thu-' . Carbon::parse($params['from'])->format('Y-m-d') . '.xlsx';

            return Excel::download(new RevenueExport($service), $fileName);
        } catch (Exception $e) {
            report($e);

            return redirect()->back()->with('error', 'Đã xảy ra lỗi khi xuất file Excel');
        }
    }

    /**
     * Xuất báo cáo PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            $params = $this->parseFilterParams($request);

            $service = new ThongKeService(
                $params['from'],
                $params['to'],
                $params['phim_id'],
                $params['phong_chieu_id']
            );

            $kpi = $service->getKPISummary();
            $revenueByTime = $service->getRevenueByTime('day');
            $topFilms = $service->getRevenueByFilm();
            $revenueStructure = $service->getRevenueStructure();

            $pdf = Pdf::loadView('admin.thong_ke.pdf', compact(
                'kpi',
                'revenueByTime',
                'topFilms',
                'revenueStructure',
                'from',
                'to'
            ) + [
                'from' => $params['from'],
                'to' => $params['to'],
            ]);

            $fileName = 'Thong-ke-doanh-thu-' . Carbon::parse($params['from'])->format('Y-m-d') . '.pdf';

            return $pdf->download($fileName);
        } catch (Exception $e) {
            report($e);

            return redirect()->back()->with('error', 'Đã xảy ra lỗi khi xuất file PDF');
        }
    }

    /**
     * Parse và validate các tham số filter từ request
     */
    protected function parseFilterParams(Request $request): array
    {
        $periodType = $request->input('period_type', 'day');
        $fromInput = $request->input('from');
        $toInput = $request->input('to');
        $phimId = $request->input('phim_id') ? (int) $request->input('phim_id') : null;
        $phongChieuId = $request->input('phong_chieu_id') ? (int) $request->input('phong_chieu_id') : null;

        // Xử lý from/to dựa trên period_type hoặc input trực tiếp
        if ($fromInput && $toInput) {
            $from = Carbon::parse($fromInput)->startOfDay()->toDateTimeString();
            $to = Carbon::parse($toInput)->endOfDay()->toDateTimeString();
        } else {
            $now = Carbon::now();
            switch ($periodType) {
                case 'month':
                    $from = $now->copy()->startOfMonth()->startOfDay()->toDateTimeString();
                    $to = $now->copy()->endOfMonth()->endOfDay()->toDateTimeString();
                    break;
                case 'quarter':
                    $currentQuarter = ceil($now->month / 3);
                    $from = $now->copy()->quarter($currentQuarter)->startOfQuarter()->startOfDay()->toDateTimeString();
                    $to = $now->copy()->quarter($currentQuarter)->endOfQuarter()->endOfDay()->toDateTimeString();
                    break;
                case 'year':
                    $from = $now->copy()->startOfYear()->startOfDay()->toDateTimeString();
                    $to = $now->copy()->endOfYear()->endOfDay()->toDateTimeString();
                    break;
                default: // day
                    $from = $now->copy()->startOfDay()->toDateTimeString();
                    $to = $now->copy()->endOfDay()->toDateTimeString();
                    break;
            }
        }

        return [
            'period_type' => $periodType,
            'from' => $from,
            'to' => $to,
            'phim_id' => $phimId,
            'phong_chieu_id' => $phongChieuId,
        ];
    }
}
