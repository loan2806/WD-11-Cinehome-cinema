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

@section('title', $movie->title . ' - CineHome')

@section('content')

<section class="min-h-screen bg-[#0b0705] text-white">

    {{-- BANNER --}}
    <div class="relative min-h-[520px] bg-cover bg-center"
         style="background-image: url('{{ $movie->cover_image }}');">
        <div class="absolute inset-0 bg-gradient-to-r from-black via-black/80 to-black/30"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-[#0b0705] via-transparent to-transparent"></div>

        <div class="relative max-w-7xl mx-auto px-6 pt-20 pb-12">
            <a href="{{ route('user.movies.index') }}"
               class="inline-block mb-8 text-gray-300 hover:text-[#f5a623] transition">
                <i class="fa-solid fa-arrow-left mr-2"></i>
                Quay lại danh sách phim
            </a>

            <div class="grid grid-cols-1 md:grid-cols-[300px_1fr] gap-8 items-end">
                <img src="{{ $movie->poster }}"
                     alt="{{ $movie->title }}"
                     class="w-full max-w-[300px] h-[430px] object-cover rounded-3xl shadow-2xl border border-white/10">

                <div>
                    <div class="inline-flex items-center gap-2 bg-[#f5a623] text-black font-extrabold px-4 py-2 rounded-full mb-4">
                        <i class="fa-solid fa-film"></i>
                        {{ $movie->status_text }}
                    </div>

                    <h1 class="text-5xl md:text-6xl font-extrabold mb-5">
                        {{ $movie->title }}
                    </h1>

                    <p class="text-gray-300 max-w-3xl leading-relaxed mb-6">
                        {{ $movie->description }}
                    </p>

                    <div class="flex flex-wrap gap-3 mb-8">
                        <span class="bg-white/10 px-4 py-2 rounded-full">
                            <i class="fa-solid fa-clapperboard text-[#f5a623] mr-1"></i>
                            {{ $movie->genre }}
                        </span>

                        <span class="bg-white/10 px-4 py-2 rounded-full">
                            <i class="fa-solid fa-location-dot text-[#f5a623] mr-1"></i>
                            {{ $movie->country }}
                        </span>

                        <span class="bg-white/10 px-4 py-2 rounded-full">
                            <i class="fa-solid fa-clock text-[#f5a623] mr-1"></i>
                            {{ $movie->duration }} phút
                        </span>

                        <span class="bg-white/10 px-4 py-2 rounded-full">
                            <i class="fa-solid fa-user-shield text-[#f5a623] mr-1"></i>
                            {{ $movie->age_rating }}
                        </span>

                        <span class="bg-white/10 px-4 py-2 rounded-full">
                            <i class="fa-solid fa-calendar text-[#f5a623] mr-1"></i>
                            {{ $movie->release_date?->format('d/m/Y') }}
                        </span>
                    </div>

                    <div class="flex flex-wrap gap-4">
                        <a href="#showtimes"
                           class="bg-[#f5a623] text-black font-extrabold px-7 py-3 rounded-xl hover:bg-[#ffc04d] transition">
                            <i class="fa-solid fa-ticket mr-1"></i>
                            Đặt vé ngay
                        </a>

                        <a href="{{ $movie->trailer_url }}"
                           target="_blank"
                           class="bg-white/10 text-white font-bold px-7 py-3 rounded-xl hover:bg-white/20 transition">
                            <i class="fa-solid fa-play mr-1"></i>
                            Xem trailer
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection


    {{-- LỊCH CHIẾU --}}
    <div id="showtimes" class="max-w-7xl mx-auto px-6 py-12">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-3xl font-extrabold">
                    Lịch <span class="text-[#f5a623]">chiếu</span>
                </h2>
                <p class="text-gray-400">
                    Chọn suất chiếu phù hợp để đặt vé.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @forelse ($showtimes as $showtime)
                <div class="bg-[#151515] border border-white/10 rounded-2xl p-5 hover:-translate-y-1 transition">
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div>
                            <h3 class="text-xl font-extrabold text-[#f5a623] mb-2">
                                {{ $showtime->cinema->name }}
                            </h3>

                            <p class="text-gray-400 text-sm">
                                <i class="fa-solid fa-location-dot text-[#f5a623] mr-1"></i>
                                {{ $showtime->cinema->address }}
                            </p>
                        </div>

                        <div class="text-right">
                            <div class="text-2xl font-extrabold text-[#f5a623]">
                                {{ \Carbon\Carbon::parse($showtime->show_time)->format('H:i') }}
                            </div>
                            <div class="text-gray-400 text-sm">
                                {{ \Carbon\Carbon::parse($showtime->show_date)->format('d/m/Y') }}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-gray-300 mb-5">
                        <span>
                            <i class="fa-solid fa-door-open text-[#f5a623] mr-1"></i>
                            {{ $showtime->room_name }}
                        </span>

                        <span class="font-bold">
                            {{ number_format($showtime->price) }}đ
                        </span>
                    </div>

                    <a href="#"
                       class="block text-center bg-[#f5a623] text-black font-extrabold py-2.5 rounded-xl hover:bg-[#ffc04d] transition">
                        Đặt vé
                    </a>
                </div>
            @empty
                <div class="md:col-span-2 xl:col-span-3 text-center py-16 text-gray-400 bg-[#151515] rounded-2xl border border-white/10">
                    Phim này hiện chưa có lịch chiếu.
                </div>
            @endforelse
        </div>
    </div>

</section>

@endsection
