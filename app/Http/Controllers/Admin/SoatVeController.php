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
     * Kiểm tra thông tin vé trước khi xác nhận khách vào rạp.
     */
    public function check(
        Request $request,
        TicketCheckInService $ticketCheckInService
    ) {
        $data = $request->validate([
            'ma_ve' => [
                'required',
                'string',
                'max:1000',
            ],
        ], [
            'ma_ve.required' => 'Vui lòng nhập hoặc quét mã vé.',
        ]);

        $result = $ticketCheckInService->inspect(
            $data['ma_ve']
        );

        $ticket = $result['ticket'];
        $foodsFromCache = [];

        if ($ticket) {
            $foodsFromCache = Cache::get(
                "ve_foods:{$ticket->id}",
                []
            );

            $ticket->foods = $foodsFromCache;

            $gioiHanTuoi =
                $ticket->phim->gioi_han_tuoi
                ?? $ticket->suatChieu?->phim?->gioi_han_tuoi
                ?? $ticket->phim->do_tuoi
                ?? $ticket->suatChieu?->phim?->do_tuoi
                ?? $ticket->gioi_han_tuoi
                ?? 'P';

            $ticket->gioi_han_tuoi = $gioiHanTuoi;
        }

        if ($request->expectsJson()) {
            $payload = $ticketCheckInService->ticketPayload(
                $ticket,
                $foodsFromCache
            );

            if ($ticket && is_array($payload)) {
                $payload['gioi_han_tuoi'] = $ticket->gioi_han_tuoi;
            }

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'ticket' => $payload,
            ], $result['success'] ? 200 : 422);
        }

        return back()
            ->withInput()
            ->with('ticket', $ticket)
            ->with(
                $result['success'] ? 'success' : 'error',
                $result['message']
            );
    }

    /**
     * Đánh dấu vé đã được in (chuyển trạng thái sang da_in).
     */
    public function printTicket(
        Request $request,
        TicketCheckInService $ticketCheckInService
    ) {
        $data = $request->validate([
            'ma_ve' => [
                'required',
                'string',
                'max:1000',
            ],
        ], [
            'ma_ve.required' => 'Vui lòng nhập mã vé cần in.',
        ]);

        if (method_exists($ticketCheckInService, 'printTicket')) {
            $result = $ticketCheckInService->printTicket($data['ma_ve']);
        } elseif (method_exists($ticketCheckInService, 'markAsPrinted')) {
            $result = $ticketCheckInService->markAsPrinted($data['ma_ve']);
        } else {
            $ticketModel = \App\Models\VeXemPhim::where('ma_ve', $data['ma_ve'])->first();

            if (!$ticketModel) {
                $result = [
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin vé trong hệ thống.',
                    'ticket' => null,
                ];
            } elseif ($ticketModel->trang_thai === 'da_su_dung') {
                $result = [
                    'success' => false,
                    'message' => 'Vé này đã được sử dụng trước đó.',
                    'ticket' => $ticketModel,
                ];
            } elseif ($ticketModel->trang_thai === 'da_huy') {
                $result = [
                    'success' => false,
                    'message' => 'Vé này đã bị hủy.',
                    'ticket' => $ticketModel,
                ];
            } else {
                $ticketModel->update(['trang_thai' => 'da_in']);
                $result = [
                    'success' => true,
                    'message' => 'Đã in vé thành công. Giờ đây bạn có thể xác nhận khách vào rạp.',
                    'ticket' => $ticketModel,
                ];
            }
        }

        $ticket = $result['ticket'] ?? null;
        $foodsFromCache = [];

        if ($ticket) {
            $foodsFromCache = Cache::get("ve_foods:{$ticket->id}", []);
            $ticket->foods = $foodsFromCache;
        }

        if ($request->expectsJson()) {
            $payload = $ticketCheckInService->ticketPayload($ticket, $foodsFromCache);

            if ($ticket && is_array($payload)) {
                $payload['trang_thai'] = 'da_in';
                $payload['trang_thai_label'] = 'Đã in';
                $payload['can_check_in'] = true;
            }

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'ticket' => $payload,
            ], $result['success'] ? 200 : 422);
        }

        return back()
            ->withInput()
            ->with('ticket', $ticket)
            ->with(
                $result['success'] ? 'success' : 'error',
                $result['message']
            );
    }

    /**
     * Xác nhận khách đã sử dụng vé (chuyển trạng thái sang da_su_dung).
     */
    public function confirm(
        Request $request,
        TicketCheckInService $ticketCheckInService
    ) {
        $data = $request->validate([
            'ma_ve' => [
                'required',
                'string',
                'max:1000',
            ],
        ], [
            'ma_ve.required' => 'Vui lòng nhập hoặc quét mã vé.',
        ]);

        $result = $ticketCheckInService->checkIn(
            $data['ma_ve']
        );

        $ticket = $result['ticket'];

        if ($request->expectsJson()) {
            $payload = $ticketCheckInService->ticketPayload(
                $ticket
            );

            if ($ticket && is_array($payload)) {
                $payload['gioi_han_tuoi'] =
                    $ticket->phim->gioi_han_tuoi
                    ?? $ticket->suatChieu?->phim?->gioi_han_tuoi
                    ?? $ticket->gioi_han_tuoi
                    ?? 'P';
            }

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'ticket' => $payload,
            ], $result['success'] ? 200 : 422);
        }

        return back()
            ->withInput()
            ->with('ticket', $ticket)
            ->with(
                $result['success'] ? 'success' : 'error',
                $result['message']
            );
    }
}