<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ThongKeService;
use App\Exports\RevenueExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ThongKeController extends Controller
{
    public function __construct()
    {
        // No dependency injection needed - service is instantiated with params
    }

    public function index(Request $request)
    {
        $from = $request->input('from') 
            ? Carbon::parse($request->input('from'))->startOfDay()->toDateTimeString() 
            : Carbon::now()->startOfMonth()->toDateTimeString();
        
        $to = $request->input('to') 
            ? Carbon::parse($request->input('to'))->endOfDay()->toDateTimeString() 
            : Carbon::now()->endOfDay()->toDateTimeString();
        
        $periodType = $request->input('period_type', 'day');
        $phimId = $request->input('phim_id') ? (int) $request->input('phim_id') : null;
        $phongChieuId = $request->input('phong_chieu_id') ? (int) $request->input('phong_chieu_id') : null;

        $service = new ThongKeService($from, $to, $phimId, $phongChieuId);

        $kpi = $service->getKPISummary();
        $revenueByTime = $service->getRevenueByTime($periodType);
        $topFilms = $service->getRevenueByFilm();
        $topShowtimes = $service->getTopShowtimes();
        $revenueByRoom = $service->getRevenueByRoom();
        $revenueBySeatType = $service->getRevenueBySeatType();
        $revenueByTimeSlot = $service->getRevenueByTimeSlot();
        $paymentMethods = $service->getPaymentMethodStats();
        $voucherStats = $service->getVoucherStats();
        $revenueStructure = $service->getRevenueStructure();
        $detailedInvoices = $service->getDetailedInvoiceData(100);

        $movies = $service->getMoviesList();
        $rooms = $service->getRoomsList();

        return view('admin.thong_ke.index', compact(
            'kpi',
            'revenueByTime',
            'topFilms',
            'topShowtimes',
            'revenueByRoom',
            'revenueBySeatType',
            'revenueByTimeSlot',
            'paymentMethods',
            'voucherStats',
            'revenueStructure',
            'detailedInvoices',
            'movies',
            'rooms',
            'from',
            'to',
            'periodType',
            'phimId',
            'phongChieuId'
        ));
    }

    public function exportExcel(Request $request)
    {
        $from = $request->input('from') 
            ? Carbon::parse($request->input('from'))->startOfDay()->toDateTimeString() 
            : Carbon::now()->startOfMonth()->toDateTimeString();
        
        $to = $request->input('to') 
            ? Carbon::parse($request->input('to'))->endOfDay()->toDateTimeString() 
            : Carbon::now()->endOfDay()->toDateTimeString();
        
        $phimId = $request->input('phim_id') ? (int) $request->input('phim_id') : null;
        $phongChieuId = $request->input('phong_chieu_id') ? (int) $request->input('phong_chieu_id') : null;

        $service = new ThongKeService($from, $to, $phimId, $phongChieuId);

        $fileName = 'bao-cao-doanh-thu-cinehome-' . Carbon::parse($from)->format('Y-m-d') . '-to-' . Carbon::parse($to)->format('Y-m-d') . '.xlsx';

        return Excel::download(new RevenueExport($service), $fileName);
    }

    public function exportPdf(Request $request)
    {
        $from = $request->input('from') 
            ? Carbon::parse($request->input('from'))->startOfDay()->toDateTimeString() 
            : Carbon::now()->startOfMonth()->toDateTimeString();
        
        $to = $request->input('to') 
            ? Carbon::parse($request->input('to'))->endOfDay()->toDateTimeString() 
            : Carbon::now()->endOfDay()->toDateTimeString();
        
        $phimId = $request->input('phim_id') ? (int) $request->input('phim_id') : null;
        $phongChieuId = $request->input('phong_chieu_id') ? (int) $request->input('phong_chieu_id') : null;

        $service = new ThongKeService($from, $to, $phimId, $phongChieuId);

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
        ));

        $fileName = 'bao-cao-doanh-thu-cinehome-' . Carbon::parse($from)->format('Y-m-d') . '-to-' . Carbon::parse($to)->format('Y-m-d') . '.pdf';

        return $pdf->download($fileName);
    }
}
