@extends('layouts.user')

@section('title', 'Vé của tôi')

@section('content')
<div class="min-h-screen bg-[#080808] px-6 pt-28 pb-10 text-white">
    <div class="mx-auto max-w-7xl">
        <h1 class="mb-8 text-3xl font-black">
            Vé <span class="text-[#d99a32]">của tôi</span>
        </h1>

        @if(session('success'))
            <div class="mb-4 rounded-xl bg-green-500/15 px-4 py-3 text-green-400">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded-xl bg-red-500/15 px-4 py-3 text-red-400">
                {{ session('error') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-white/10 bg-[#121212]">
            <table class="w-full text-left">
                <thead class="bg-[#1f1f1f] text-sm uppercase text-gray-400">
                    <tr>
                        <th class="px-5 py-4">Mã vé</th>
                        <th class="px-5 py-4">Phim</th>
                        <th class="px-5 py-4">Ghế</th>
                        <th class="px-5 py-4">Tổng tiền</th>
                        <th class="px-5 py-4">Trạng thái</th>
                        <th class="px-5 py-4 text-right">Thao tác</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-white/10">
                    @forelse ($tickets as $ticket)
                        <tr class="hover:bg-white/[0.03]">
                            <td class="px-5 py-4 font-bold text-[#d99a32]">
                                {{ $ticket->ticket_code }}
                            </td>

                            <td class="px-5 py-4">
                                {{ $ticket->movie->title ?? 'Chưa có phim' }}
                            </td>

                            <td class="px-5 py-4">
                                {{ $ticket->seat_code }}
                            </td>

                            <td class="px-5 py-4">
                                {{ number_format($ticket->total_price) }}đ
                            </td>

                            <td class="px-5 py-4">
                                {{ $ticket->status }}
                            </td>

                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('user.tickets.show', $ticket) }}"
                                   class="rounded-lg bg-[#d99a32] px-4 py-2 text-sm font-bold text-[#2b1208]">
                                    Chi tiết
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-gray-400">
                                Chưa có vé nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $tickets->links() }}
        </div>
    </div>
</div>
@endsection