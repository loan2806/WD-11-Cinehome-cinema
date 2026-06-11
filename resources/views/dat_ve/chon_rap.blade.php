@extends('layouts.user')

@section('title', 'Chon rap chieu phim')

@section('content')
<div class="min-h-screen bg-[#080808] px-6 pt-28 pb-12 text-white">
    <div class="mx-auto max-w-7xl">
        <h1 class="mb-8 text-center text-3xl font-black">
            Chon <span class="text-[#d99a32]">rap chieu phim</span>
        </h1>

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @forelse($danhSachRap as $rap)
                <div class="overflow-hidden rounded-2xl border border-white/10 bg-[#121212]">
                    <img
                        src="{{ $rap->hinh_anh ?? 'https://via.placeholder.com/600x320?text=CineHome' }}"
                        class="h-52 w-full object-cover"
                        alt="{{ $rap->ten_rap }}"
                    >
                    <div class="p-5">
                        <h2 class="text-xl font-black text-[#d99a32]">{{ $rap->ten_rap }}</h2>
                        <p class="mt-2 text-sm text-gray-300">{{ $rap->dia_chi }}</p>
                        <p class="mt-1 text-sm text-gray-400">{{ $rap->thanh_pho }}</p>

                        <a
                            href="{{ route('dat_ve.chon_phim', ['rap_id' => $rap->id]) }}"
                            class="mt-5 inline-flex w-full items-center justify-center rounded-xl bg-[#d99a32] px-4 py-3 font-black text-[#2b1208] transition hover:bg-[#f4c56a]"
                        >
                            Chon rap nay
                        </a>
                    </div>
                </div>
            @empty
                <div class="md:col-span-2 xl:col-span-3 rounded-2xl border border-white/10 bg-[#121212] p-10 text-center text-gray-400">
                    Hien chua co rap nao co suat chieu sap toi.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
