@extends('layouts.user')

@section('title', 'Chọn phim và suất chiếu')

@section('content')
<div class="min-h-screen bg-[#080808] px-6 pt-28 pb-12 text-white">
    <div class="mx-auto max-w-7xl">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-black">Chọn phim và suất chiếu</h1>
            <p class="mt-2 text-gray-400">{{ $rap->ten_rap }}{{ $rap->dia_chi ? ' · ' . $rap->dia_chi : '' }}</p>
        </div>

        <div class="space-y-6">
            @forelse($suatChieuTheoPhim as $suatChieus)
                @php
                    $phim = $suatChieus->first()->phim;
                    $suatTheoNgay = $suatChieus->groupBy(fn ($suat) => $suat->thoi_gian_chieu->format('Y-m-d'));
                @endphp

                <div class="overflow-hidden rounded-2xl border border-white/10 bg-[#121212]">
                    <div class="grid gap-0 md:grid-cols-[220px_1fr]">
                        <img
                            src="{{ $phim->poster ?? 'https://via.placeholder.com/300x450?text=Poster' }}"
                            class="h-full min-h-[320px] w-full object-cover"
                            alt="{{ $phim->ten_phim }}"
                        >

                        <div class="p-6">
                            <h2 class="text-2xl font-black text-[#d99a32]">{{ $phim->ten_phim }}</h2>
                            <p class="mt-2 text-sm text-gray-400">{{ $phim->thoi_luong }} phut</p>
                            <p class="mt-4 max-w-3xl text-gray-300">{{ \Illuminate\Support\Str::limit($phim->mo_ta, 180) }}</p>

                            <div class="mt-6 space-y-5">
                                @foreach($suatTheoNgay as $ngay => $cacSuat)
                                    <div>
                                        <div class="mb-3 text-sm font-bold text-gray-300">
                                            {{ \Carbon\Carbon::parse($ngay)->format('d/m/Y') }}
                                        </div>
                                        <div class="flex flex-wrap gap-3">
                                            @foreach($cacSuat as $suat)
                                                <a
                                                    href="{{ route('dat_ve.chon_ghe', ['suat_chieu_id' => $suat->id]) }}"
                                                    class="rounded-xl border border-[#d99a32]/50 px-4 py-2 font-bold text-[#f4c56a] transition hover:bg-[#d99a32] hover:text-[#2b1208]"
                                                >
                                                    {{ $suat->thoi_gian_chieu->format('H:i') }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-white/10 bg-[#121212] p-10 text-center text-gray-400">
                    Hiện chưa có suất chiếu nào.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
