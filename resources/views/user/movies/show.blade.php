@extends('layouts.user')

@section('title', $movie->title)

@section('content')
<div class="min-h-screen bg-[#080808] px-6 pt-28 pb-10 text-white">
    <div class="mx-auto max-w-6xl">
        <div class="grid gap-8 lg:grid-cols-[320px_1fr]">
            <div>
                <img src="{{ $movie->poster ?: asset('assets/images/logo.png') }}" alt="{{ $movie->title }}" class="w-full rounded-2xl border border-white/10 object-cover">
            </div>

            <div>
                <h1 class="text-4xl font-black text-[#d99a32]">{{ $movie->title }}</h1>
                <div class="mt-3 flex flex-wrap gap-3 text-sm text-gray-300">
                    <span>{{ $movie->genre }}</span>
                    <span>{{ $movie->duration }} phut</span>
                    <span>{{ optional($movie->release_date)->format('d/m/Y') }}</span>
                </div>
                <p class="mt-5 leading-7 text-gray-200">{{ $movie->description }}</p>

                <div class="mt-8 rounded-2xl border border-white/10 bg-[#121212] p-5">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-black">Danh gia phim</h2>
                        <div class="text-[#d99a32]">
                            {{ number_format($movie->approvedReviews->avg('rating') ?: 0, 1) }}/5
                        </div>
                    </div>

                    @auth
                        <form method="POST" action="{{ route('user.movies.reviews.store', $movie) }}" class="mt-5 grid gap-4">
                            @csrf
                            <select name="rating" class="rounded-xl border border-white/10 bg-[#111] px-4 py-3 text-white" required>
                                @for($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}">{{ $i }} sao</option>
                                @endfor
                            </select>
                            <textarea name="content" rows="3" class="rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-white" placeholder="Cam nhan cua ban ve phim"></textarea>
                            <button class="w-fit rounded-xl bg-[#d99a32] px-5 py-3 font-bold text-black">Gui danh gia</button>
                        </form>
                    @else
                        <p class="mt-4 text-gray-400">Dang nhap de danh gia phim.</p>
                    @endauth

                    <div class="mt-6 space-y-3">
                        @forelse($movie->approvedReviews as $review)
                            <div class="rounded-xl bg-white/5 p-4">
                                <div class="flex justify-between gap-4">
                                    <strong>{{ $review->user?->name }}</strong>
                                    <span class="text-[#d99a32]">{{ str_repeat('★', $review->rating) }}</span>
                                </div>
                                <p class="mt-2 text-gray-300">{{ $review->content }}</p>
                            </div>
                        @empty
                            <p class="text-gray-400">Chua co danh gia nao.</p>
                        @endforelse
                    </div>
                </div>

                <div class="mt-8 rounded-2xl border border-white/10 bg-[#121212] p-5">
                    <h2 class="text-2xl font-black">Suat chieu</h2>
                    <div class="mt-4 grid gap-3 md:grid-cols-2">
                        @forelse($showtimes as $showtime)
                            <div class="rounded-xl bg-white/5 p-4">
                                <strong>{{ $showtime->cinema->name ?? 'CineHome' }}</strong>
                                <div class="text-sm text-gray-300">{{ $showtime->show_date ?? '' }} {{ $showtime->show_time ?? '' }}</div>
                            </div>
                        @empty
                            <p class="text-gray-400">Chua co suat chieu phu hop.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
