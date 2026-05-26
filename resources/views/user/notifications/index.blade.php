@extends('layouts.user')

@section('title', 'Thong bao')

@section('content')
<div class="min-h-screen bg-[#080808] px-6 pt-28 pb-10 text-white">
    <div class="mx-auto max-w-4xl">
        <h1 class="mb-6 text-3xl font-black text-[#d99a32]">Thong bao</h1>

        <div class="space-y-4">
            @forelse($notifications as $notification)
                <div class="rounded-2xl border border-white/10 bg-[#121212] p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-xs uppercase text-gray-400">{{ $notification->type }} - {{ $notification->created_at->format('d/m/Y H:i') }}</div>
                            <h2 class="mt-1 text-xl font-bold">{{ $notification->title }}</h2>
                            <p class="mt-2 text-gray-300">{{ $notification->message }}</p>
                        </div>
                        @if(!$notification->read_at)
                            <form method="POST" action="{{ route('user.notifications.read', $notification) }}">
                                @csrf
                                @method('PATCH')
                                <button class="rounded-xl bg-[#d99a32] px-4 py-2 text-sm font-bold text-black">Da doc</button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-white/10 bg-[#121212] p-8 text-center text-gray-400">Chua co thong bao.</div>
            @endforelse
        </div>

        <div class="mt-6">{{ $notifications->links() }}</div>
    </div>
</div>
@endsection
