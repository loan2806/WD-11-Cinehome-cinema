<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::with('movie')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('user.tickets.index', compact('tickets'));
    }

    public function show(Ticket $ticket)
    {
        abort_if($ticket->user_id !== auth()->id(), 403);

        $ticket->load('movie');

        return view('user.tickets.show', compact('ticket'));
    }

    public function cancel(Ticket $ticket)
    {
        abort_if($ticket->user_id !== auth()->id(), 403);

        if (!$ticket->canCancel()) {
            return back()->with('error', 'Chỉ được hủy vé trong vòng 5 phút sau khi đặt.');
        }

        $ticket->update([
            'status' => 'cancelled',
            'refund_amount' => $ticket->total_price * 0.5,
            'cancelled_at' => now(),
        ]);

        return redirect()
            ->route('user.tickets.index')
            ->with('success', 'Hủy vé thành công. Bạn được hoàn 50% giá trị vé.');
    }
}
