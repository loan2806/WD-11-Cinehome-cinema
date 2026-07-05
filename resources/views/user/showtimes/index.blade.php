@extends('layouts.user')

@section('title', 'Lich chieu - CineHome')

@section('content')
<section class="min-h-screen bg-[#0b0705] px-6 pt-32 pb-12 text-white">
    <div class="mx-auto max-w-7xl">
        <h1 class="text-4xl font-black">Lich <span class="text-[#f5a623]">chieu phim</span></h1>
        <p class="mt-2 text-gray-400">Tim lich chieu theo phim, rap va ngay chieu.</p>

        <form method="GET" action="{{ route('user.showtimes.index') }}" class="mt-8 grid gap-4 rounded-2xl border border-white/10 bg-[#151515] p-5 md:grid-cols-5">
            <select name="phim_id" class="rounded-xl border border-white/10 bg-[#0b0705] px-4 py-3 text-white">
                <option value="">Tat ca phim</option>
                @foreach($movies as $movie)
                    <option value="{{ $movie->id }}" @selected(request('phim_id') == $movie->id)>{{ $movie->ten_phim }}</option>
                @endforeach
            </select>

            <select name="rap_chieu_phim_id" class="rounded-xl border border-white/10 bg-[#0b0705] px-4 py-3 text-white">
                <option value="">Tat ca rap</option>
                @foreach($rapChieuPhims as $rap)
                    <option value="{{ $rap->id }}" @selected(request('rap_chieu_phim_id') == $rap->id)>{{ $rap->ten_rap }}</option>
                @endforeach
            </select>

            <input type="date" name="ngay_chieu" value="{{ request('ngay_chieu') }}" class="rounded-xl border border-white/10 bg-[#0b0705] px-4 py-3 text-white">

            <select name="trang_thai" class="rounded-xl border border-white/10 bg-[#0b0705] px-4 py-3 text-white">
                <option value="">Trang thai</option>
                <option value="dang_chieu" @selected(request('trang_thai') === 'dang_chieu')>Dang chieu</option>
                <option value="sap_chieu" @selected(request('trang_thai') === 'sap_chieu')>Sap chieu</option>
            </select>

            <div class="flex gap-3">
                <button class="flex-1 rounded-xl bg-[#f5a623] font-black text-black hover:bg-[#ffc04d]">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
                <a href="{{ route('user.showtimes.index') }}" class="flex w-14 items-center justify-center rounded-xl bg-white/10 text-white hover:bg-white/20">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>

        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @forelse($suatChieus as $suatChieu)
                <article class="flex min-h-[430px] flex-col overflow-hidden rounded-2xl border border-white/10 bg-[#151515]">
                    <img
                        src="{{ asset('storage/movies/' .  $suatChieu->phim->poster)  }}"
                        alt="{{ $suatChieu->phim->ten_phim }}"
                        class="h-52 w-full object-cover"
                    >

                    <div class="flex flex-1 flex-col p-4">
                        <h2 class="min-h-[52px] text-lg font-black text-[#f5a623]">{{ $suatChieu->phim->ten_phim }}</h2>
                        <p class="mt-3 text-sm text-gray-300"><i class="fa-solid fa-building text-[#f5a623]"></i> {{ $suatChieu->rapChieuPhim->ten_rap }}</p>
                        <p class="mt-2 text-sm text-gray-400"><i class="fa-solid fa-calendar-days text-[#f5a623]"></i> {{ $suatChieu->thoi_gian_chieu->format('d/m/Y') }}</p>
                        <p class="mt-2 text-sm text-gray-400"><i class="fa-solid fa-clock text-[#f5a623]"></i> {{ $suatChieu->thoi_gian_chieu->format('H:i') }}</p>
                        <p class="mt-2 text-sm text-gray-400"><i class="fa-solid fa-ticket text-[#f5a623]"></i> {{ number_format($suatChieu->gia_ve, 0, ',', '.') }} VND</p>

                        <div class="mt-auto flex gap-3 pt-5">
                            <a href="{{route('dat_ve.chon_ghe', ['movie' => $movie->slug]) }}" class="flex-1 rounded-xl bg-[#f5a623] py-2 text-center text-sm font-black text-black hover:bg-[#ffc04d]">
                                Dat ve
                            </a>
                            <a href="{{ route('user.showtimes.show', $suatChieu) }}" class="flex-1 rounded-xl bg-white/10 py-2 text-center text-sm font-bold text-white hover:bg-white/20">
                                Chi tiet
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-white/10 bg-[#151515] p-10 text-center text-gray-400 lg:col-span-4">
                    Khong co lich chieu phu hop.
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
