@extends('layouts.user')

@section('title', 'Lịch chiếu - CineHome')

@section('content')

<section class="min-h-screen bg-[#0b0705] text-white pt-32 pb-10">
    <div class="max-w-[1800px] mx-auto px-8">

        <h1 class="text-4xl font-extrabold mb-2">
            Lịch <span class="text-[#f5a623]">chiếu phim</span>
        </h1>

        <p class="text-gray-400 mb-8">
            Tìm lịch chiếu theo phim, rạp, ngày chiếu và trạng thái.
        </p>

        <form method="GET" action="{{ route('user.showtimes.index') }}"
            class="bg-[#151515] border border-white/10 rounded-2xl p-5 mb-8 grid grid-cols-1 md:grid-cols-5 gap-4">

            <select name="movie_id"
                class="bg-[#0b0705] border border-white/10 text-white rounded-xl px-4 py-3 outline-none">
                <option value="">Tất cả phim</option>
                @foreach ($movies as $movie)
                    <option value="{{ $movie->id }}" {{ request('movie_id') == $movie->id ? 'selected' : '' }}>
                        {{ $movie->title }}
                    </option>
                @endforeach
            </select>

            <select name="cinema_id"
                class="bg-[#0b0705] border border-white/10 text-white rounded-xl px-4 py-3 outline-none">
                <option value="">Tất cả rạp</option>
                @foreach ($cinemas as $cinema)
                    <option value="{{ $cinema->id }}" {{ request('cinema_id') == $cinema->id ? 'selected' : '' }}>
                        {{ $cinema->name }}
                    </option>
                @endforeach
            </select>

            <input type="date"
                name="show_date"
                value="{{ request('show_date') }}"
                class="bg-[#0b0705] border border-white/10 text-white rounded-xl px-4 py-3 outline-none">

            <select name="status"
                class="bg-[#0b0705] border border-white/10 text-white rounded-xl px-4 py-3 outline-none">
                <option value="">Trạng thái</option>

                <option value="now_showing" {{ request('status') == 'now_showing' ? 'selected' : '' }}>
                    Đang chiếu
                </option>

                <option value="coming_soon" {{ request('status') == 'coming_soon' ? 'selected' : '' }}>
                    Sắp chiếu
                </option>
            </select>

            <div class="flex gap-3">
                <button type="submit"
                    class="flex-1 bg-[#f5a623] text-black font-extrabold rounded-xl hover:bg-[#ffc04d] transition">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>

                <a href="{{ route('user.showtimes.index') }}"
                    class="w-[52px] flex items-center justify-center bg-white/10 text-white rounded-xl hover:bg-white/20 transition">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-6">
            @forelse ($showtimes ?? [] as $showtime)
                @php
                    $badgeText = $showtime->movie->schedule_status;

                    if ($badgeText === 'Sắp chiếu') {
                        $badgeClass = 'bg-blue-500 text-white';
                    } else {
                        $badgeClass = 'bg-[#f5a623] text-black';
                    }
                @endphp

                <div
                    class="bg-[#151515] border border-white/10 rounded-2xl overflow-hidden shadow-lg transition duration-300 hover:-translate-y-2 hover:shadow-2xl flex flex-col min-h-[430px]">

                    <div class="relative">
                        <img
                            src="{{ $showtime->movie->poster ?? 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=800' }}"
                            alt="{{ $showtime->movie->title }}"
                            class="w-full h-[190px] object-cover shrink-0">

                        <div
                            class="absolute top-3 left-3 px-3 py-1 rounded-full text-xs font-extrabold {{ $badgeClass }}">
                            {{ $badgeText }}
                        </div>

                        <div
                            class="absolute top-3 right-3 bg-black/70 text-white text-xs font-extrabold px-3 py-1 rounded-full">
                            {{ $showtime->movie->age_rating }}
                        </div>
                    </div>

                    <div class="p-4 flex flex-col flex-1">
                        <h2 class="text-lg font-extrabold text-[#f5a623] mb-3 min-h-[48px]">
                            {{ $showtime->movie->title }}
                        </h2>

                        <p class="text-gray-300 mb-1 text-sm">
                            <i class="fa-solid fa-building text-[#f5a623] mr-1"></i>
                            {{ $showtime->cinema->name }}
                        </p>

                        <p class="text-gray-400 mb-1 text-sm">
                            <i class="fa-solid fa-door-open text-[#f5a623] mr-1"></i>
                            {{ $showtime->room_name }}
                        </p>

                        <p class="text-gray-400 mb-1 text-sm">
                            <i class="fa-solid fa-calendar-days text-[#f5a623] mr-1"></i>
                            {{ \Carbon\Carbon::parse($showtime->show_date)->format('d/m/Y') }}
                        </p>

                        <p class="text-gray-400 mb-1 text-sm">
                            <i class="fa-solid fa-clock text-[#f5a623] mr-1"></i>
                            {{ \Carbon\Carbon::parse($showtime->show_time)->format('H:i') }}
                        </p>

                        <p class="text-gray-400 mb-4 text-sm">
                            <i class="fa-solid fa-money-bill text-[#f5a623] mr-1"></i>
                            {{ number_format($showtime->price) }}đ
                        </p>

                        <div class="flex gap-3 mt-auto">
                            <a href="#"
                                class="flex-1 text-center bg-[#f5a623] text-black font-extrabold py-2 rounded-xl hover:bg-[#ffc04d] transition text-sm">
                                Đặt vé
                            </a>

                            <a href="{{ route('user.showtimes.show', $showtime->id) }}"
                                class="flex-1 text-center bg-white/10 text-white font-bold py-2 rounded-xl hover:bg-white/20 transition text-sm">
                                Chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div
                    class="xl:col-span-5 text-center py-20 text-gray-400 bg-[#151515] rounded-2xl border border-white/10">
                    Không có lịch chiếu phù hợp.
                </div>
            @endforelse
        </div>

    </div>
</section>

@endsection