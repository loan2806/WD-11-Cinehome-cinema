@extends('layouts.user')

@section('title', 'Chi tiết lịch chiếu - CineHome')

@section('content')

<section class="min-h-screen bg-[#0b0705] text-white pt-32 pb-10">
    <div class="max-w-6xl mx-auto px-6">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 bg-[#151515] border border-white/10 rounded-3xl p-6">

            <img 
                src="{{ $showtime->movie->poster }}"
                alt="{{ $showtime->movie->title }}"
                class="w-full rounded-2xl object-cover"
            >

            <div class="md:col-span-2">
                <h1 class="text-4xl font-extrabold text-[#f5a623] mb-4">
                    {{ $showtime->movie->title }}
                </h1>

                <p class="text-gray-300 mb-5">
                    {{ $showtime->movie->description }}
                </p>

                <div class="space-y-3 text-gray-300">
                    <p><i class="fa-solid fa-building text-[#f5a623] mr-2"></i>{{ $showtime->cinema->name }}</p>
                    <p><i class="fa-solid fa-location-dot text-[#f5a623] mr-2"></i>{{ $showtime->cinema->address }}</p>
                    <p><i class="fa-solid fa-door-open text-[#f5a623] mr-2"></i>{{ $showtime->room_name }}</p>
                    <p><i class="fa-solid fa-calendar text-[#f5a623] mr-2"></i>{{ \Carbon\Carbon::parse($showtime->show_date)->format('d/m/Y') }}</p>
                    <p><i class="fa-solid fa-clock text-[#f5a623] mr-2"></i>{{ \Carbon\Carbon::parse($showtime->show_time)->format('H:i') }}</p>
                    <p><i class="fa-solid fa-ticket text-[#f5a623] mr-2"></i>{{ number_format($showtime->price) }}đ</p>
                </div>

                <div class="mt-8 flex gap-4">
                    <a href="#" class="bg-[#f5a623] text-black font-extrabold px-8 py-3 rounded-xl">
                        Đặt vé
                    </a>

                    <a href="{{ route('user.showtimes.index', ['cinema_id' => $showtime->cinema_id]) }}"
                       class="bg-white/10 text-white font-bold px-8 py-3 rounded-xl">
                        Quay lại
                    </a>
                </div>
            </div>

        </div>

    </div>
</section>

@endsection