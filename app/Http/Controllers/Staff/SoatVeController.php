<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Services\TicketCheckInService;
use Illuminate\Http\Request;

class SoatVeController extends Controller
{
    public function index()
    {
        return view('staff.soat-ve.index');
    }

    public function check(Request $request, TicketCheckInService $ticketCheckInService)
    {
        $request->validate([
            'ma_ve' => 'required|string|max:1000',
        ], [
            'ma_ve.required' => 'Vui lòng nhập mã vé cần kiểm tra.',
        ]);

        $result = $ticketCheckInService->checkIn($request->ma_ve);

        return back()
            ->withInput()
            ->with('ticket', $result['ticket'])
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}
