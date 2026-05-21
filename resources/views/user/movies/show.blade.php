@extends('layouts.user')

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