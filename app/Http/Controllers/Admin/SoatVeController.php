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
            'ma_ve.required' =>
                'Vui lòng nhập hoặc quét mã vé.',
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

            $ticket->gioi_han_tuoi =
                $gioiHanTuoi;
        }

        if ($request->expectsJson()) {
            $payload =
                $ticketCheckInService->ticketPayload(
                    $ticket,
                    $foodsFromCache
                );

            if ($ticket && is_array($payload)) {
                $payload['gioi_han_tuoi'] =
                    $ticket->gioi_han_tuoi;
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
                $result['success']
                    ? 'success'
                    : 'error',
                $result['message']
            );
    }

    /**
     * Xác nhận khách đã sử dụng vé.
     *
     * Vé phải ở trạng thái da_in.
     * Sau khi xác nhận sẽ chuyển sang da_su_dung.
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
            'ma_ve.required' =>
                'Vui lòng nhập hoặc quét mã vé.',
        ]);

        $result = $ticketCheckInService->checkIn(
            $data['ma_ve']
        );

        $ticket = $result['ticket'];

        if ($request->expectsJson()) {
            $payload =
                $ticketCheckInService->ticketPayload(
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
                $result['success']
                    ? 'success'
                    : 'error',
                $result['message']
            );
    }
}