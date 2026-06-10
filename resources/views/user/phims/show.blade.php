@extends('layouts.user')

@section('title', $movie->ten_phim . ' - CineHome')

@section('content')

    <section class="min-h-screen bg-[#0b0705] text-white overflow-hidden">

        <div class="min-h-screen grid grid-cols-1 lg:grid-cols-2">

            {{-- LEFT - POSTER --}}
            <div class="relative min-h-screen flex items-center justify-center overflow-hidden px-8 py-24 bg-black">

                <div class="absolute inset-0 bg-cover bg-center scale-110 opacity-25 blur-md"
                    style="background-image: url('{{ $movie->poster }}');">
                </div>

                <div class="absolute inset-0 bg-gradient-to-r from-black via-black/80 to-[#0b0705]"></div>

                <div class="relative flex flex-col items-center">

                    {{-- POSTER --}}
                    <img src="{{ $movie->poster }}" alt="{{ $movie->ten_phim }}"
                        class="poster-pop-left relative z-10 w-[320px] md:w-[410px] lg:w-[450px] h-[520px] md:h-[620px] object-cover rounded-3xl shadow-2xl border border-white/10">

                    {{-- BUTTONS --}}
                    <div class="relative z-10 mt-6 w-[320px] md:w-[410px] lg:w-[450px] space-y-3">

                        {{-- TRAILER --}}
                        <a href="{{ $movie->trailer_url }}" target="_blank"
                            class="w-full flex items-center justify-center gap-2 bg-white/10 text-white font-bold px-6 py-3 rounded-xl hover:bg-white/20 transition">
                            <i class="fa-solid fa-play"></i>
                            Xem trailer
                        </a>

                        @php
                            $status = optional($movie->showtimes->sortBy('thoi_gian_chieu')->first())?->trang_thai;
                        @endphp

                        {{-- SẮP RA MẮT --}}
                        @if ($status === \App\Models\SuatChieu::TRANG_THAI_SAP_RA_MAT)
                            <button
                                class="w-full flex items-center justify-center gap-2 bg-pink-500 text-white font-extrabold px-6 py-3 rounded-xl hover:bg-pink-400 transition">
                                <i class="fa-regular fa-heart"></i>
                                Quan tâm
                            </button>

                            {{-- SẮP CHIẾU --}}
                        @elseif ($status === \App\Models\SuatChieu::TRANG_THAI_SAP_CHIEU)
                            <a href="{{ route('booking', $movie) }}"
                                class="w-full flex items-center justify-center gap-2 bg-[#f5a623] text-black font-extrabold px-6 py-3 rounded-xl hover:bg-[#ffc04d] transition">
                                <i class="fa-solid fa-ticket"></i>
                                Đặt vé ngay
                            </a>

                            {{-- ĐANG CHIẾU --}}
                        @elseif ($status === \App\Models\SuatChieu::TRANG_THAI_DANG_CHIEU)
                            <div
                                class="w-full flex items-center justify-center gap-2 bg-white/10 text-white font-extrabold px-6 py-3 rounded-xl border border-white/10">
                                <i class="fa-solid fa-film"></i>
                                Đang chiếu
                            </div>
                        @else
                            <div
                                class="w-full flex items-center justify-center gap-2 bg-gray-700 text-white font-extrabold px-6 py-3 rounded-xl">
                                Không xác định
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

                    </div>

                    <h1 class="text-5xl md:text-6xl lg:text-7xl font-extrabold text-[#f5a623] mb-6 leading-tight">
                        {{ $movie->ten_phim }}
                    </h1>

                    <p class="text-gray-300 leading-relaxed mb-8 text-lg">
                        {{ $movie->mo_ta }}
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">

                        <div class="bg-white/10 rounded-2xl p-4">
                            <p class="text-gray-400 text-sm mb-1">Thể loại</p>
                            <p class="font-bold">{{ $movie->genres->pluck('ten_the_loai')->join(', ') }}</p>
                        </div>

                        <div class="bg-white/10 rounded-2xl p-4">
                            <p class="text-gray-400 text-sm mb-1">Quốc gia</p>
                            <p class="font-bold">{{ $movie->country->ten_quoc_gia }}</p>
                        </div>

                        <div class="bg-white/10 rounded-2xl p-4">
                            <p class="text-gray-400 text-sm mb-1">Thời lượng</p>
                            <p class="font-bold">{{ $movie->thoi_luong }} phút</p>
                        </div>

                        <div class="bg-white/10 rounded-2xl p-4">
                            <p class="text-gray-400 text-sm mb-1">Độ tuổi</p>
                            <p class="font-bold">{{ $movie->gioi_han_tuoi }}</p>
                        </div>

                        <div class="bg-white/10 rounded-2xl p-4 md:col-span-2">
                            <p class="text-gray-400 text-sm mb-1">Diễn viên</p>
                            <p class="font-bold">
                                @if (is_array($movie->dien_vien) || is_object($movie->dien_vien))
                                    {{ implode(', ', (array) $movie->dien_vien) }}
                                @else
                                    {{ $movie->dien_vien }}
                                @endif
                            </p>
                        </div>

                        <div class="bg-white/10 rounded-2xl p-4 md:col-span-2">
                            <p class="text-gray-400 text-sm mb-1">Ngày khởi chiếu</p>
                            <p class="font-bold">
                                {{ $movie->ngay_khoi_chieu ? \Carbon\Carbon::parse($movie->ngay_khoi_chieu)->format('d/m/Y') : 'Chưa có' }}
                            </p>
                        </div>

                    </div>

                </div>
            </div>

        </div>

    </section>

    {{-- RELATED MOVIES --}}
    <section class="bg-[#0b0705] text-white py-20">
        <div class="container-fluid px-8">

            <div class="section-title-wrap mb-8">
                <h2 class="section-title">
                    Phim <span>liên quan</span>
                </h2>
            </div>

            @if (isset($relatedMovies) && $relatedMovies->isNotEmpty())
                @include('partials.movie-section', ['movies' => $relatedMovies])
            @else
                <div class="text-center text-gray-400 py-14">
                    Không tìm thấy phim liên quan.
                </div>
            @endif

        </div>
    </section>

@endsection
