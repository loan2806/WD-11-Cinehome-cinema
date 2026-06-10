@extends('layouts.user')

@section('title', $movie->title . ' - CineHome')

@section('content')

    <section class="min-h-screen bg-[#0b0705] text-white overflow-hidden">

        <div class="min-h-screen grid grid-cols-1 lg:grid-cols-2">

            {{-- LEFT - POSTER --}}
            <div class="relative min-h-screen flex items-center justify-center overflow-hidden px-8 py-24 bg-black">

                <div class="absolute inset-0 bg-cover bg-center scale-110 opacity-25 blur-md"
                    style="background-image: url('{{ $movie->cover_image ?? $movie->poster }}');">
                </div>

                <div class="absolute inset-0 bg-gradient-to-r from-black via-black/80 to-[#0b0705]"></div>

                <div class="relative flex flex-col items-center">

                    {{-- POSTER --}}
                    <img src="{{ $movie->poster }}" alt="{{ $movie->title }}"
                        class="poster-pop-left relative z-10 w-[320px] md:w-[410px] lg:w-[450px] h-[520px] md:h-[620px] object-cover rounded-3xl shadow-2xl border border-white/10">

                    {{-- BUTTONS UNDER POSTER --}}
                    <div class="relative z-10 mt-6 w-[320px] md:w-[410px] lg:w-[450px] space-y-3">

                        {{-- TRAILER --}}
                        <a href="{{ $movie->trailer_url }}" target="_blank"
                            class="w-full flex items-center justify-center gap-2 bg-white/10 text-white font-bold px-6 py-3 rounded-xl hover:bg-white/20 transition">
                            <i class="fa-solid fa-play"></i>
                            Xem trailer
                        </a>

                        {{-- BOOKING / INTEREST --}}
                        {{-- BUTTON ACTION --}}
                        @php
                            $status = $movie->schedule_status;
                        @endphp

                        {{-- SẮP RA MẮT --}}
                        @if ($status === 'Sắp ra mắt')
                            <button
                                class="w-full flex items-center justify-center gap-2 bg-pink-500 text-white font-extrabold px-6 py-3 rounded-xl hover:bg-pink-400 transition">
                                <i class="fa-regular fa-heart"></i>
                                Quan tâm
                            </button>

                            {{-- SẮP CHIẾU --}}
                        @elseif ($status === 'Sắp chiếu')
                            <a href="#showtimes"
                                class="w-full flex items-center justify-center gap-2 bg-[#f5a623] text-black font-extrabold px-6 py-3 rounded-xl hover:bg-[#ffc04d] transition">
                                <i class="fa-solid fa-ticket"></i>
                                Đặt vé ngay
                            </a>

                            {{-- ĐANG CHIẾU --}}
                        @else
                            <div
                                class="w-full flex items-center justify-center gap-2 bg-white/10 text-white font-extrabold px-6 py-3 rounded-xl border border-white/10">
                                <i class="fa-solid fa-film"></i>
                                Đang chiếu
                            </div>
                        @endif

                    </div>

                </div>
            </div>

            {{-- RIGHT - CONTENT --}}
            <div class="relative min-h-screen flex items-center px-8 md:px-14 lg:px-20 py-24 bg-[#0b0705]">

                <div class="content-fade-right max-w-2xl">

                    <div class="flex flex-wrap items-center gap-3 mb-8">

                        <a href="{{ route('home') }}"
                            class="group inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-5 py-2.5 text-sm font-bold text-gray-300 hover:border-[#f5a623]/60 hover:bg-[#f5a623]/10 hover:text-[#f5a623] transition">

                            <span
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-white/10 group-hover:bg-[#f5a623] group-hover:text-black transition">
                                <i class="fa-solid fa-arrow-left"></i>
                            </span>

                            Quay lại
                        </a>
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-[#f5a623] to-[#ffc04d] px-5 py-2.5 text-sm font-extrabold text-black shadow-lg shadow-[#f5a623]/20">
                            <i class="fa-solid fa-clapperboard"></i>
                            {{ $movie->status_text ?? $movie->schedule_status }}
                        </span>

                    </div>

                    <h1 class="text-5xl md:text-6xl lg:text-7xl font-extrabold text-[#f5a623] mb-6 leading-tight">
                        {{ $movie->title }}
                    </h1>

                    <p class="text-gray-300 leading-relaxed mb-8 text-lg">
                        {{ $movie->description }}
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">

                        <div class="bg-white/10 rounded-2xl p-4">
                            <p class="text-gray-400 text-sm mb-1">Thể loại</p>
                            <p class="font-bold">{{ $movie->genre }}</p>
                        </div>

                        <div class="bg-white/10 rounded-2xl p-4">
                            <p class="text-gray-400 text-sm mb-1">Quốc gia</p>
                            <p class="font-bold">{{ $movie->country }}</p>
                        </div>

                        <div class="bg-white/10 rounded-2xl p-4">
                            <p class="text-gray-400 text-sm mb-1">Thời lượng</p>
                            <p class="font-bold">{{ $movie->duration }} phút</p>
                        </div>

                        <div class="bg-white/10 rounded-2xl p-4">
                            <p class="text-gray-400 text-sm mb-1">Độ tuổi</p>
                            <p class="font-bold">{{ $movie->age_rating }}</p>
                        </div>

                        <div class="bg-white/10 rounded-2xl p-4 md:col-span-2">
                            <p class="text-gray-400 text-sm mb-1">Diễn viên</p>
                            <p class="font-bold">
                                @if (is_array($movie->cast ?? null))
                                    {{ implode(', ', $movie->cast) }}
                                @else
                                    {{ $movie->cast ?? 'Đang cập nhật' }}
                                @endif
                            </p>
                        </div>

                        <div class="bg-white/10 rounded-2xl p-4 md:col-span-2">
                            <p class="text-gray-400 text-sm mb-1">Ngày khởi chiếu</p>
                            <p class="font-bold">
                                {{ $movie->release_date?->format('d/m/Y') }}
                            </p>
                        </div>

                    </div>

                </div>
            </div>

        </div>
    </section>

    <section id="showtimes" class="bg-[#080808] px-6 py-14 text-white">
        <div class="mx-auto max-w-7xl">
            <h2 class="mb-8 text-3xl font-black">
                Suat <span class="text-[#f5a623]">chieu</span>
            </h2>

            @php
                $showtimesByCinema = $showtimes->groupBy('rap_chieu_phim_id');
            @endphp

            <div class="space-y-5">
                @forelse($showtimesByCinema as $cinemaShowtimes)
                    @php
                        $rap = $cinemaShowtimes->first()->rapChieuPhim;
                        $showtimesByDate = $cinemaShowtimes->groupBy(fn ($suat) => $suat->thoi_gian_chieu->format('Y-m-d'));
                    @endphp

                    <div class="rounded-2xl border border-white/10 bg-[#121212] p-6">
                        <h3 class="text-xl font-black text-[#f5a623]">{{ $rap->ten_rap }}</h3>
                        <p class="mt-1 text-sm text-gray-400">{{ $rap->dia_chi }}</p>

                        <div class="mt-5 space-y-4">
                            @foreach($showtimesByDate as $date => $items)
                                <div>
                                    <div class="mb-3 text-sm font-bold text-gray-300">
                                        {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                                    </div>
                                    <div class="flex flex-wrap gap-3">
                                        @foreach($items as $suat)
                                            <a href="{{ route('dat_ve.chon_ghe', ['suat_chieu_id' => $suat->id]) }}"
                                               class="rounded-xl border border-[#f5a623]/50 px-4 py-2 font-bold text-[#f5a623] transition hover:bg-[#f5a623] hover:text-black">
                                                {{ $suat->thoi_gian_chieu->format('H:i') }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-white/10 bg-[#121212] p-8 text-center text-gray-400">
                        Hien chua co suat chieu phu hop.
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <script>
        function backWithAnimation(url) {
            const poster = document.querySelector('.poster-pop-left');
            const content = document.querySelector('.content-fade-right');

            if (!poster || !content) {
                window.location.href = url;
                return;
            }

            poster.classList.remove('poster-pop-left');
            content.classList.remove('content-fade-right');

            void poster.offsetWidth;

            poster.classList.add('poster-back');
            content.classList.add('content-back');

            setTimeout(function() {
                window.location.href = url;
            }, 600);
        }
    </script>

@endsection
