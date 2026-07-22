<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TicketCheckInService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SoatVeController extends Controller
{
    /**
     * Hiển thị giao diện soát vé tại quầy.
     */
    public function index()
    {
        return view('admin.soat-ve.index');
    }

    /**
     * Kiểm tra thông tin vé (Quét QR hoặc nhập thủ công), nạp đồ ăn đi kèm & độ tuổi phim.
     */
    public function check(Request $request, TicketCheckInService $ticketCheckInService)
    {
        $data = $request->validate([
            'ma_ve' => ['required', 'string', 'max:1000'],
        ], [
            'ma_ve.required' => 'Vui lòng nhập hoặc quét mã vé.',
        ]);

        $result = $ticketCheckInService->inspect($data['ma_ve']);
        $ticket = $result['ticket'];
        $foodsFromCache = [];

        if ($ticket) {
            // 🌟 Lấy danh sách đồ ăn kèm theo vé từ Cache
            $foodsFromCache = Cache::get("ve_foods:{$ticket->id}", []);
            $ticket->foods = $foodsFromCache;

            // 🌟 Lấy thông tin Giới hạn độ tuổi của phim (P, K, T13, T16, T18, T19...)
            $gioiHanTuoi = $ticket->phim->gioi_han_tuoi 
                ?? $ticket->suatChieu->phim->gioi_han_tuoi 
                ?? $ticket->phim->do_tuoi 
                ?? $ticket->suatChieu->phim->do_tuoi 
                ?? $ticket->gioi_han_tuoi 
                ?? 'P';

            $ticket->gioi_han_tuoi = $gioiHanTuoi;
        }

        if ($request->expectsJson()) {
            $payload = $ticketCheckInService->ticketPayload($ticket, $foodsFromCache);
            
            if ($ticket && is_array($payload)) {
                $payload['gioi_han_tuoi'] = $ticket->gioi_han_tuoi;
            }

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'ticket'  => $payload,
            ], $result['success'] ? 200 : 422);
        }

        return back()
            ->withInput()
            ->with('ticket', $ticket)
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Xác nhận in vé & cập nhật trạng thái vé trong Database thành 'da_in'.
     */
    public function confirm(Request $request, TicketCheckInService $ticketCheckInService)
    {
        $data = $request->validate([
            'ma_ve' => ['required', 'string', 'max:1000'],
        ], [
            'ma_ve.required' => 'Vui lòng nhập hoặc quét mã vé.',
        ]);

        $result = $ticketCheckInService->checkIn($data['ma_ve']);
        $ticket = $result['ticket'];

        if ($ticket && $result['success']) {
            // 🌟 CẬP NHẬT TRỰC TIẾP DATABASE TRẠNG THÁI 'da_in'
            $ticket->update(['trang_thai' => 'da_in']);
            $ticket->trang_thai = 'da_in';
        }

        if ($request->expectsJson()) {
            $payload = $ticketCheckInService->ticketPayload($ticket);
            
            if ($ticket && is_array($payload)) {
                $payload['trang_thai']       = 'da_in';
                $payload['trang_thai_label'] = 'Đã in';
                $payload['gioi_han_tuoi']    = $ticket->phim->gioi_han_tuoi 
                    ?? $ticket->suatChieu->phim->gioi_han_tuoi 
                    ?? $ticket->gioi_han_tuoi 
                    ?? 'P';
            }

            return response()->json([
                'success' => $result['success'],
                'message' => 'Đã in vé cứng và cập nhật trạng thái "Đã in" thành công!',
                'ticket'  => $payload,
            ], $result['success'] ? 200 : 422);
        }

        return back()
            ->withInput()
            ->with('ticket', $ticket)
            ->with($result['success'] ? 'success' : 'error', 'Đã in vé cứng và cập nhật trạng thái "Đã in" thành công!');
    }
}