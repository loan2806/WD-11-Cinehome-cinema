<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TicketCheckInService;
use Illuminate\Http\Request;

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
     * Kiểm tra thông tin vé (Quét QR hoặc nhập thủ công) và nạp đồ ăn đi kèm.
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

        // 🌟 LẤY TRỰC TIẾP TỪ USER SIDE: Kéo đồ ăn từ Cache theo ID của vé giống hệt Controller của User
        if ($ticket) {
            $foodsFromCache = \Illuminate\Support\Facades\Cache::get("ve_foods:{$ticket->id}", []);
            $ticket->foods = $foodsFromCache;
        }

        if ($request->expectsJson()) {
            $payload = $ticketCheckInService->ticketPayload($ticket, $foodsFromCache);
            
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'ticket' => $payload,
            ], $result['success'] ? 200 : 422);
        }

        return back()
            ->withInput()
            ->with('ticket', $ticket)
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }
    /**
     * Xác nhận sử dụng vé sau khi in thành công.
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

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'ticket' => $ticketCheckInService->ticketPayload($ticket),
            ], $result['success'] ? 200 : 422);
        }

        return back()
            ->withInput()
            ->with('ticket', $ticket)
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}
