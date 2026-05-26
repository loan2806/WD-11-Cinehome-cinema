@extends('layouts.user')

@section('title', 'Chi tiết vé')

@section('content')
<div class="min-h-screen bg-[#080808] px-6 pt-28 pb-10 text-white">
    <div class="mx-auto max-w-3xl rounded-3xl border border-white/10 bg-[#121212] p-8">

        <a href="{{ route('user.tickets.index') }}" class="mb-6 inline-block text-sm font-bold text-[#d99a32]">
            ← Quay lại vé của tôi
        </a>

        <h1 class="mb-6 text-3xl font-black text-[#d99a32]">
            {{ $ticket->ticket_code }}
        </h1>

        <div class="space-y-4">
            <div>
                <p class="text-sm text-gray-400">Phim</p>
                <p class="font-bold">{{ $ticket->movie->title ?? 'Chưa có phim' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-400">Rạp</p>
                <p class="font-bold">{{ $ticket->cinema_name }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-400">Phòng</p>
                <p class="font-bold">{{ $ticket->room_name }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-400">Ghế</p>
                <p class="font-bold">{{ $ticket->seat_code }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-400">Suất chiếu</p>
                <p class="font-bold">{{ optional($ticket->show_time)->format('H:i d/m/Y') }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-400">Tổng tiền</p>
                <p class="font-bold">{{ number_format($ticket->total_price) }}đ</p>
            </div>

            <div>
                <p class="text-sm text-gray-400">Trạng thái</p>
                <p class="font-bold">{{ $ticket->status }}</p>
            </div>

            @if($ticket->status === 'cancelled')
                <div>
                    <p class="text-sm text-gray-400">Số tiền hoàn</p>
                    <p class="font-bold text-green-400">
                        {{ number_format($ticket->refund_amount) }}đ
                    </p>
                </div>
            @endif
        </div>

        <div class="mt-8">
            @if ($ticket->canCancel())
                <form method="POST"
                      action="{{ route('user.tickets.cancel', $ticket) }}"
                      onsubmit="return confirm('Bạn chắc chắn muốn hủy vé? Bạn sẽ mất 50% giá trị vé.')">
                    @csrf
                    @method('PATCH')

                    <button class="rounded-xl bg-red-500 px-5 py-3 font-bold text-white hover:bg-red-600">
                        Hủy vé và hoàn 50%
                    </button>
                </form>
            @else
                <p class="text-sm text-gray-400">
                    Vé chỉ được hủy trong vòng 5 phút sau khi đặt.
                </p>
            @endif
        </div>

        <div class="mt-8 rounded-2xl border border-white/10 bg-white/5 p-5">
            <h2 class="mb-4 text-xl font-black text-[#d99a32]">Hoa don do an</h2>

            @forelse($ticket->foodOrders as $order)
                <div class="mb-5 rounded-xl bg-[#181818] p-4">
                    <div class="mb-3 flex items-center justify-between gap-4">
                        <strong>{{ $order->invoice_code }}</strong>
                        <span>{{ number_format($order->total_amount) }}d</span>
                    </div>

                    <div class="space-y-2">
                        @foreach($order->items as $item)
                            <div class="flex justify-between gap-4 text-sm text-gray-300">
                                <span>{{ $item->item_name }} x {{ $item->quantity }}</span>
                                <span>{{ number_format($item->line_total) }}d</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400">Ve nay chua co hoa don do an.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
