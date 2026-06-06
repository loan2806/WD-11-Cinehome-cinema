@extends('layouts.user')

@section('title', 'Danh sách phim')

@section('content')

<section class="min-h-screen bg-[#0b0705] text-white pt-32 pb-10">
    <div class="max-w-[1800px] mx-auto px-8">

        {{-- FILTER --}}
        <form action="{{ route('user.phims.index') }}" method="GET"
            class="bg-[#151515] border border-white/10 rounded-2xl p-5 mb-8 grid grid-cols-1 md:grid-cols-5 gap-4">

            {{-- SEARCH --}}
            <input type="text" name="tim_kiem" value="{{ request('tim_kiem') }}"
                placeholder="Tìm tên phim..."
                class="bg-[#0b0705] border border-white/10 text-white rounded-xl px-4 py-3">

            {{-- GENRE --}}
            <select name="the_loai"
                class="bg-[#0b0705] border border-white/10 text-white rounded-xl px-4 py-3">

                <option value="">Thể loại</option>

                @foreach ($genres as $genre)
                    <option value="{{ $genre->ten_the_loai }}"
                        {{ request('the_loai') == $genre->ten_the_loai ? 'selected' : '' }}>

                        {{ $genre->ten_the_loai }}

                    </option>
                @endforeach
            </select>

            {{-- COUNTRY --}}
            <select name="quoc_gia"
                class="bg-[#0b0705] border border-white/10 text-white rounded-xl px-4 py-3">

                <option value="">Quốc gia</option>

                @foreach ($countries as $country)
                    <option value="{{ $country->ten_quoc_gia }}"
                        {{ request('quoc_gia') == $country->ten_quoc_gia ? 'selected' : '' }}>

                        {{ $country->ten_quoc_gia }}

                    </option>
                @endforeach
            </select>

            {{-- STATUS --}}
            <select name="status"
                class="bg-[#0b0705] border border-white/10 text-white rounded-xl px-4 py-3">

                <option value="">Trạng thái</option>
                <option value="dang_chieu" {{ request('status') == 'dang_chieu' ? 'selected' : '' }}>Đang chiếu</option>
                <option value="sap_chieu" {{ request('status') == 'sap_chieu' ? 'selected' : '' }}>Sắp chiếu</option>
                <option value="sap_ra_mat" {{ request('status') == 'sap_ra_mat' ? 'selected' : '' }}>Sắp ra mắt</option>

            </select>

            {{-- BUTTON --}}
            <div class="flex gap-3">
                <button class="flex-1 bg-[#f5a623] text-black font-bold rounded-xl">
                    Tìm
                </button>

                <a href="{{ route('user.phims.index') }}"
                    class="w-[52px] flex items-center justify-center bg-white/10 rounded-xl">
                    ⟳
                </a>
            </div>

        </form>

        {{-- MOVIES --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

            @forelse ($movies as $movie)

                <div class="bg-[#151515] border border-white/10 rounded-2xl overflow-hidden">

                    {{-- POSTER --}}
                    <a href="{{ route('user.phims.show', $movie->id) }}">
                        <img src="{{ asset('storage/' . $movie->poster) }}"
                            class="w-full h-[250px] object-cover">
                    </a>

                    <div class="p-4">

                        {{-- TITLE --}}
                        <h2 class="font-bold text-lg mb-2">
                            {{ $movie->ten_phim }}
                        </h2>

                        {{-- GENRE NAME --}}
                        <p class="text-sm text-gray-400">
                            🎬
                            {{ $movie->genres->pluck('ten_the_loai')->join(', ') }}
                        </p>

                        {{-- COUNTRY NAME --}}
                        <p class="text-sm text-gray-400">
                            🌍 {{ $movie->country->ten_quoc_gia ?? 'Chưa cập nhật' }}
                        </p>

                        {{-- DURATION --}}
                        <p class="text-sm text-gray-400">
                            ⏱ {{ $movie->thoi_luong }} phút
                        </p>

                        {{-- BUTTON --}}
                        <a href="{{ route('user.phims.show', $movie->id) }}"
                            class="block mt-3 text-center bg-[#f5a623] text-black font-bold py-2 rounded-xl">

                            Chi tiết

                        </a>

                    </div>
                </div>

            @empty

                <div class="col-span-full text-center text-gray-400 py-20">
                    Không tìm thấy phim
                </div>

            @endforelse

        </div>

    </div>
</section>

@endsection