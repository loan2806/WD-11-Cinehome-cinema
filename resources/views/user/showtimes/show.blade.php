@extends('layouts.user')

@section('title', 'Chi tiet lich chieu - CineHome')

@section('content')
<section class="min-h-screen bg-[#0b0705] px-6 pt-32 pb-12 text-white">
    <div class="mx-auto max-w-5xl rounded-3xl border border-white/10 bg-[#151515] p-6">
        <div class="grid gap-8 md:grid-cols-[320px_1fr]">
            <img
                src="{{ $suatChieu->phim->poster ?? 'https://via.placeholder.com/300x450?text=Poster' }}"
                alt="{{ $suatChieu->phim->ten_phim }}"
                class="w-full rounded-2xl object-cover"
            >

            <div>
                <h1 class="text-4xl font-black text-[#f5a623]">{{ $suatChieu->phim->ten_phim }}</h1>
                <p class="mt-4 text-gray-300">{{ $suatChieu->phim->mo_ta }}</p>

                <div class="mt-6 space-y-3 text-gray-300">
                    <p><i class="fa-solid fa-building text-[#f5a623]"></i> {{ $suatChieu->rapChieuPhim->ten_rap }}</p>
                    <p><i class="fa-solid fa-location-dot text-[#f5a623]"></i> {{ $suatChieu->rapChieuPhim->dia_chi }}</p>
                    <p><i class="fa-solid fa-calendar text-[#f5a623]"></i> {{ $suatChieu->thoi_gian_chieu->format('d/m/Y') }}</p>
                    <p><i class="fa-solid fa-clock text-[#f5a623]"></i> {{ $suatChieu->thoi_gian_chieu->format('H:i') }}</p>
                    <p><i class="fa-solid fa-ticket text-[#f5a623]"></i> {{ number_format($suatChieu->gia_ve, 0, ',', '.') }} VND</p>
                </div>

                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="{{ route('dat_ve.chon_ghe', ['suat_chieu_id' => $suatChieu->id]) }}" class="rounded-xl bg-[#f5a623] px-8 py-3 font-black text-black">
                        Dat ve
                    </a>

                    <a href="{{ route('user.showtimes.index', ['rap_chieu_phim_id' => $suatChieu->rap_chieu_phim_id]) }}" class="rounded-xl bg-white/10 px-8 py-3 font-bold text-white">
                        Quay lai
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
