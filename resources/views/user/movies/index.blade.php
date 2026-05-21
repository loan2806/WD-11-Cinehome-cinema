@extends('layouts.user')

@section('title', 'Danh sách phim - CineHome')

@section('content')

    <section class="min-h-screen bg-[#0b0705] text-white pt-32 pb-10">
        <div class="max-w-[1800px] mx-auto px-8">

            <h1 class="text-4xl font-extrabold mb-2">
                Danh sách <span class="text-[#f5a623]">phim</span>
            </h1>

            <p class="text-gray-400 mb-8">
                Xem phim đang chiếu, sắp chiếu và đặt vé nhanh chóng tại CineHome.
            </p>

            <form action="{{ route('user.movies.index') }}" method="GET"
                class="bg-[#151515] border border-white/10 rounded-2xl p-5 mb-8 grid grid-cols-1 md:grid-cols-5 gap-4">

                <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Tìm tên phim..."
                    class="bg-[#0b0705] border border-white/10 text-white placeholder:text-gray-500 rounded-xl px-4 py-3 outline-none focus:border-[#f5a623]">

                <select name="genre"
                    class="bg-[#0b0705] border border-white/10 text-white rounded-xl px-4 py-3 outline-none focus:border-[#f5a623]">
                    <option value="">Thể loại</option>
                    @foreach ($genres as $genre)
                        <option value="{{ $genre }}" {{ request('genre') == $genre ? 'selected' : '' }}>
                            {{ $genre }}
                        </option>
                    @endforeach
                </select>

                <select name="country"
                    class="bg-[#0b0705] border border-white/10 text-white rounded-xl px-4 py-3 outline-none focus:border-[#f5a623]">
                    <option value="">Quốc gia</option>
                    @foreach ($countries as $country)
                        <option value="{{ $country }}" {{ request('country') == $country ? 'selected' : '' }}>
                            {{ $country }}
                        </option>
                    @endforeach
                </select>

                <select name="status"
                    class="bg-[#0b0705] border border-white/10 text-white rounded-xl px-4 py-3 outline-none focus:border-[#f5a623]">
                    <option value="">Trạng thái</option>

                    <option value="now_showing" {{ request('status') == 'now_showing' ? 'selected' : '' }}>
                        Đang chiếu
                    </option>

                    <option value="coming_soon" {{ request('status') == 'coming_soon' ? 'selected' : '' }}>
                        Sắp chiếu
                    </option>

                    <option value="coming_later" {{ request('status') == 'coming_later' ? 'selected' : '' }}>
                        Sắp ra mắt
                    </option>
                </select>

                <div class="flex gap-3">
                    <button type="submit"
                        class="flex-1 bg-[#f5a623] text-black font-extrabold rounded-xl hover:bg-[#ffc04d] transition">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>

                    <a href="{{ route('user.movies.index') }}"
                        class="w-[52px] flex items-center justify-center bg-white/10 text-white rounded-xl hover:bg-white/20 transition">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>
            </form>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-6">
                @forelse ($movies as $movie)
                    @php

                        $badgeText = $movie->schedule_status;

                        // SẮP RA MẮT
                        if ($badgeText === 'Sắp ra mắt') {
                            $badgeClass = 'bg-pink-600 text-white';

                            $buttonText = 'Quan tâm';

                            $buttonClass = 'border border-pink-500 text-pink-400 hover:bg-pink-500 hover:text-white';

                            $buttonUrl = route('user.movies.show', $movie->slug);

                            $buttonIcon = 'fa-regular fa-heart';
                        }

                        // SẮP CHIẾU
                        elseif ($badgeText === 'Sắp chiếu') {
                            $badgeClass = 'bg-blue-500 text-white';

                            $buttonText = 'Đặt vé';

                            $buttonClass = 'bg-[#f5a623] text-black hover:bg-[#ffc04d]';

                            $buttonUrl = route('user.movies.show', $movie->slug) . '#showtimes';

                            $buttonIcon = 'fa-solid fa-ticket';
                        }

                        // ĐANG CHIẾU
                        else {
                            $badgeClass = 'bg-[#f5a623] text-black';
                        }

                    @endphp

                    <div
                        class="bg-[#151515] border border-white/10 rounded-2xl overflow-hidden shadow-lg transition duration-300 hover:-translate-y-2 hover:shadow-2xl flex flex-col min-h-[430px]">
                        <div class="relative">
                            <a href="{{ route('user.movies.show', $movie->id) }}">
                                <img src="{{ $movie->poster }}" alt="{{ $movie->title }}"
                                    class="w-full h-[250px] object-cover shrink-0 hover:opacity-80 transition cursor-pointer">
                            </a>

                            <div
                                class="absolute top-3 left-3 z-10 min-w-[95px] text-center text-xs font-extrabold px-3 py-2 rounded-full shadow-lg {{ $badgeClass }}">
                                {{ $badgeText }}
                            </div>

                            <div
                                class="absolute top-3 right-3 bg-black/70 text-white text-xs font-extrabold px-3 py-1 rounded-full">
                                {{ $movie->age_rating }}
                            </div>
                        </div>

                        <div class="p-4 flex flex-col flex-1">
                            <h2 class="text-lg font-extrabold text-white mb-3 min-h-[48px]">
                                {{ $movie->title }}
                            </h2>

                            <p class="text-gray-300 mb-1 text-sm">
                                <i class="fa-solid fa-film text-[#f5a623] mr-1"></i>
                                {{ $movie->genre }}
                            </p>

                            <p class="text-gray-400 mb-1 text-sm">
                                <i class="fa-solid fa-location-dot text-[#f5a623] mr-1"></i>
                                {{ $movie->country }}
                            </p>

                            <p class="text-gray-400 mb-1 text-sm">
                                <i class="fa-solid fa-clock text-[#f5a623] mr-1"></i>
                                {{ $movie->duration }} phút
                            </p>

                            <p class="text-gray-400 mb-4 text-sm">
                                <i class="fa-solid fa-calendar-days text-[#f5a623] mr-1"></i>
                                {{ $movie->release_date?->format('d/m/Y') }}
                            </p>

                            <div class="flex gap-3 mt-auto">

                                {{-- SẮP CHIẾU --}}
                                @if ($badgeText === 'Sắp chiếu')
                                    <a href="{{ $buttonUrl }}"
                                        class="flex-1 text-center font-extrabold py-2 rounded-xl transition text-sm {{ $buttonClass }}">

                                        <i class="{{ $buttonIcon }} mr-1"></i>

                                        {{ $buttonText }}

                                    </a>

                                    {{-- SẮP RA MẮT --}}
                                @elseif ($badgeText === 'Sắp ra mắt')
                                    <a href="{{ $buttonUrl }}"
                                        class="flex-1 text-center font-extrabold py-2 rounded-xl transition text-sm {{ $buttonClass }}">

                                        <i class="{{ $buttonIcon }} mr-1"></i>

                                        {{ $buttonText }}

                                    </a>
                                @endif

                                {{-- CHI TIẾT --}}
                                <a href="{{ route('user.movies.show', $movie->slug) }}"
                                    class="flex-1 text-center bg-white/10 text-white font-bold py-2 rounded-xl hover:bg-white/20 transition text-sm">

                                    Chi tiết

                                </a>

                            </div>
                        </div>
                    </div>
                @empty
                    <div
                        class="xl:col-span-5 text-center py-20 text-gray-400 bg-[#151515] rounded-2xl border border-white/10">
                        Không tìm thấy phim.
                    </div>
                @endforelse
            </div>

            {{-- <div class="mt-8">
                {{ $movies->links('pagination::bootstrap-5') }}
            </div> --}}

        </div>
    </section>

@endsection
