@extends('layouts.user')

@section('title', $movie->ten_phim . ' - CineHome')

@section('content')

    <section x-data="{ tab: 'description' }" class="relative min-h-screen bg-black text-white overflow-hidden">

        {{-- BACKDROP --}}
        <div class="absolute inset-0">

            <img src="{{ $movie->poster }}" class="w-full h-full object-cover opacity-20 blur-md">

            <div class="absolute inset-0 bg-gradient-to-r from-black via-black/90 to-black">
            </div>

        </div>

        <div class="relative max-w-7xl mx-auto px-6 lg:px-12 pt-32 pb-20">

            <div class="grid lg:grid-cols-12 gap-12">

                {{-- LEFT --}}
                <div class="lg:col-span-3">

                    <img src="{{ asset('storage/movies/' . $movie->poster) }}" alt="{{ $movie->ten_phim }}"
                        class="w-full aspect-[2/3.4] object-cover rounded-2xl shadow-2xl">

                    <div class="mt-5 space-y-3">

                        @if ($movie->trailer)
                            <a href="{{ $movie->trailer }}" target="_blank"
                                class="w-full flex items-center justify-center gap-2 bg-pink-600 hover:bg-pink-500 px-5 py-3 rounded-xl font-bold transition">

                                <i class="fa-solid fa-play"></i>

                                Xem Trailer
                            </a>
                        @endif

                        @if ($status === \App\Models\SuatChieu::TRANG_THAI_SAP_CHIEU)
                            <button
                                class="w-full flex items-center justify-center gap-2 bg-blue-500 text-white px-5 py-3 rounded-xl cursor-not-allowed"
                                disabled>
                                Sắp chiếu
                            </button>
                        @elseif ($status === \App\Models\SuatChieu::TRANG_THAI_SAP_RA_MAT)
                            <button
                                class="w-full flex items-center justify-center gap-2 bg-purple-500 text-white px-5 py-3 rounded-xl cursor-not-allowed"
                                disabled>
                                Sắp ra mắt
                            </button>
                        @endif

                    </div>

                </div>

                {{-- RIGHT --}}
                <div class="lg:col-span-9">
                    <div class="mb-6">
                        <a href="{{ url()->previous() }}"
                            class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/5 px-5 py-3 text-sm font-semibold text-white hover:bg-white/10 transition">
                            <i class="fa-solid fa-arrow-left"></i>
                            Quay lại
                        </a>
                    </div>

                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-white mb-3">

                        {{ $movie->ten_phim }}

                    </h1>



                    {{-- RATING --}}
                    {{-- <div class="bg-white/5 border border-white/10 rounded-xl p-4 mb-8">

                        <div class="flex flex-col md:flex-row md:items-center gap-3">

                            <div class="text-yellow-400 text-2xl tracking-wider">

                                ★★★★★★★★☆

                            </div>

                            <div class="text-2xl font-bold text-yellow-400">

                                8.5/10

                            </div>

                        </div>

                    </div> --}}

                    {{-- TAB MENU --}}
                    <div class="flex gap-8 border-b border-white/10 mb-8 overflow-x-auto">

                        <button @click="tab='description'"
                            :class="tab == 'description' ?
                                'border-yellow-400 text-yellow-400' :
                                'border-transparent text-gray-400'"
                            class="pb-4 border-b-2 font-bold whitespace-nowrap">

                            MÔ TẢ

                        </button>

                        <button @click="tab='review'"
                            :class="tab == 'review' ?
                                'border-yellow-400 text-yellow-400' :
                                'border-transparent text-gray-400'"
                            class="pb-4 border-b-2 font-bold whitespace-nowrap">

                            ĐÁNH GIÁ

                        </button>

                        <button @click="tab='cast'"
                            :class="tab == 'cast' ?
                                'border-yellow-400 text-yellow-400' :
                                'border-transparent text-gray-400'"
                            class="pb-4 border-b-2 font-bold whitespace-nowrap">

                            CAST & CREW

                        </button>

                    </div>

                    {{-- DESCRIPTION --}}
                    <div x-show="tab=='description'" x-transition>

                        <div class="grid lg:grid-cols-3 gap-10">

                            {{-- LEFT CONTENT --}}
                            <div class="lg:col-span-2">

                                <h3 class="text-sm uppercase tracking-widest text-yellow-400 mb-4">
                                    Mô tả
                                </h3>

                                <p class="text-gray-300 leading-8 mb-8">
                                    {{ $movie->mo_ta }}
                                </p>
                            </div>

                            {{-- RIGHT INFO --}}
                            <div>

                                <div class="space-y-6">

                                    <div>
                                        <p class="text-gray-500 text-sm mb-1">
                                            Director
                                        </p>

                                        <p class="font-semibold text-white">
                                            {{ $movie->dao_dien }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-gray-500 text-sm mb-1">
                                            Stars
                                        </p>

                                        <p class="font-semibold text-white">
                                            {{ $movie->dien_vien }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-gray-500 text-sm mb-1">
                                            Genre
                                        </p>

                                        <p class="font-semibold text-white">
                                            {{ $movie->genres->pluck('ten_the_loai')->join(', ') }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-gray-500 text-sm mb-1">
                                            Country
                                        </p>

                                        <p class="font-semibold text-white">
                                            {{ optional($movie->country)->ten_quoc_gia }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-gray-500 text-sm mb-1">
                                            Duration
                                        </p>

                                        <p class="font-semibold text-white">
                                            {{ $movie->thoi_luong }} phút
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-gray-500 text-sm mb-1">
                                            Age Rating
                                        </p>

                                        <p class="font-semibold text-white">
                                            {{ $movie->gioi_han_tuoi }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-gray-500 text-sm mb-1">
                                            Release Date
                                        </p>

                                        <p class="font-semibold text-white">
                                            {{ \Carbon\Carbon::parse($movie->ngay_khoi_chieu)->format('d/m/Y') }}
                                        </p>
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    {{-- REVIEW --}}
                    <div x-show="tab=='review'" x-transition class="space-y-4">

                        <div class="bg-white/5 border border-white/10 rounded-xl p-5">

                            <div class="flex items-center justify-between mb-2">

                                <h3 class="font-bold">
                                    Nguyễn Văn A
                                </h3>

                                <span class="text-yellow-400">
                                    ★★★★★
                                </span>

                            </div>

                            <p class="text-gray-400">
                                Phim rất hay, kỹ xảo đẹp và đáng xem.
                            </p>

                        </div>

                        <div class="bg-white/5 border border-white/10 rounded-xl p-5">

                            <div class="flex items-center justify-between mb-2">

                                <h3 class="font-bold">
                                    Trần Văn B
                                </h3>

                                <span class="text-yellow-400">
                                    ★★★★☆
                                </span>

                            </div>

                            <p class="text-gray-400">
                                Nội dung cuốn hút, diễn xuất tốt.
                            </p>

                        </div>

                    </div>

                    <div x-show="tab=='cast'" x-transition>

                        {{-- DIRECTOR --}}
                        <div class="mb-10">

                            <h3 class="text-yellow-400 font-bold uppercase tracking-wider mb-6">
                                Director
                            </h3>

                            <div class="flex items-center gap-4">

                                <div
                                    class="w-16 h-16 rounded-full bg-yellow-400 text-black flex items-center justify-center font-bold text-xl">

                                    {{ strtoupper(substr($movie->dao_dien, 0, 1)) }}

                                </div>

                                <div>

                                    <h4 class="text-xl font-semibold">
                                        {{ $movie->dao_dien }}
                                    </h4>

                                    <p class="text-gray-500">
                                        Director
                                    </p>

                                </div>

                            </div>

                        </div>

                        {{-- CAST --}}
                        <div>

                            <h3 class="text-yellow-400 font-bold uppercase tracking-wider mb-6">
                                Cast
                            </h3>

                            <div class="grid md:grid-cols-2 gap-4">

                                @foreach (explode(',', $movie->dien_vien) as $actor)
                                    <div class="flex items-center gap-4 border-b border-white/10 pb-4">

                                        <div
                                            class="w-14 h-14 rounded-full bg-white/10 flex items-center justify-center font-bold">

                                            {{ strtoupper(substr(trim($actor), 0, 1)) }}
                                        </div>

                                        <div>

                                            <h4 class="font-semibold">
                                                {{ trim($actor) }}
                                            </h4>

                                            <p class="text-gray-500 text-sm">
                                                Actor
                                            </p>

                                        </div>

                                    </div>
                                @endforeach

                            </div>

                        </div>

                    </div>

                    {{-- MEDIA --}}
                    <div x-show="tab=='media'" x-transition>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

                            <img src="{{ $movie->poster }}" class="rounded-xl">

                            <img src="{{ $movie->poster }}" class="rounded-xl">

                            <img src="{{ $movie->poster }}" class="rounded-xl">

                            <img src="{{ $movie->poster }}" class="rounded-xl">

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>

    {{-- PHIM LIÊN QUAN --}}
    <section class="bg-black py-20 text-white">

        <div class="max-w-7xl mx-auto px-6">

            <h2 class="text-3xl font-bold mb-8">

                Phim liên quan

            </h2>

            @if (isset($relatedMovies) && $relatedMovies->count())
                @include('partials.movie-section', [
                    'movies' => $relatedMovies,
                ])
            @endif

        </div>

    </section>

@endsection
