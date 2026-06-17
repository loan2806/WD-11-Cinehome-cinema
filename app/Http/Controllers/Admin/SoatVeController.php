<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TicketCheckInService;
use Illuminate\Http\Request;

class SoatVeController extends Controller
{
    public function index()
    {
        return view('admin.soat-ve.index');
    }

    public function check(Request $request, TicketCheckInService $ticketCheckInService)
    {
        $data = $request->validate([
            'ma_ve' => ['required', 'string', 'max:1000'],
        ], [
            'ma_ve.required' => 'Vui lòng nhập hoặc quét mã vé.',
        ]);

        $result = $ticketCheckInService->checkIn($data['ma_ve']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'ticket' => $ticketCheckInService->ticketPayload($result['ticket']),
            ], $result['success'] ? 200 : 422);
        }

        return back()
            ->withInput()
            ->with('ticket', $result['ticket'])
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}
