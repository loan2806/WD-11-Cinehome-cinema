<div class="row g-4">

@forelse ($movies as $movie)

    {{-- BỎ PHIM ĐÃ KẾT THÚC --}}
    @if ($movie->schedule_status === 'Đã kết thúc')
        @continue
    @endif

    @php

        $status = $movie->schedule_status;

        // DEFAULT
        $badgeClass = '';
        $buttonText = '';
        $buttonClass = '';
        $buttonUrl = '';
        $buttonIcon = '';

        /*
        |--------------------------------------------------------------------------
        | SẮP RA MẮT
        |--------------------------------------------------------------------------
        */
        if ($status === 'Sắp ra mắt') {

            $badgeClass = 'bg-pink-600 text-white';

            $buttonText = 'Quan tâm';

            $buttonClass =
                'bg-pink-600 text-white hover:bg-pink-500';

            $buttonUrl =
                route('user.movies.show', $movie->slug);

            $buttonIcon =
                'fa-regular fa-heart';
        }

        /*
        |--------------------------------------------------------------------------
        | SẮP CHIẾU
        |--------------------------------------------------------------------------
        */
        elseif ($status === 'Sắp chiếu') {

            $badgeClass =
                'bg-blue-500 text-white';

            $buttonText =
                'Đặt vé';

            $buttonClass =
                'bg-[#f5a623] text-black hover:bg-[#ffc04d]';

            $buttonUrl =
                route('user.bookings.index', $movie);

            $buttonIcon =
                'fa-solid fa-ticket';
        }

        /*
        |--------------------------------------------------------------------------
        | ĐANG CHIẾU
        |--------------------------------------------------------------------------
        */
        else {

            $badgeClass =
                'bg-[#f5a623] text-black';

            $buttonText =
                'Chi tiết';

            $buttonClass =
                'bg-white text-black hover:bg-gray-200';

            $buttonUrl =
                route('user.movies.show', $movie->slug);

            $buttonIcon =
                'fa-solid fa-circle-info';
        }

    @endphp

    <div class="col-6 col-md-4 col-lg-3 col-xl-2">

        <div class="movie-card">

            {{-- POSTER --}}
            <div class="movie-poster relative overflow-hidden">

                <img
                    src="{{ asset('storage/' . $movie->poster) }}"
                    alt="{{ $movie->ten_phim }}"
                    class="w-full h-full object-cover"
                >

                {{-- STATUS --}}
                <div class="absolute top-3 left-3 z-10 px-4 py-2 rounded-full text-xs font-bold shadow-lg {{ $badgeClass }}">

                    {{ $status }}

                </div>

                {{-- AGE --}}
                <div class="absolute top-3 right-3 z-10 px-3 py-2 rounded-full bg-black/70 text-white text-xs font-bold shadow-lg">

                    {{ $movie->gioi_han_tuoi ?? 'P' }}

                </div>

            </div>

            {{-- BODY --}}
            <div class="movie-body">

                {{-- TITLE --}}
                <h3 class="movie-title">

                    {{ $movie->ten_phim }}

                </h3>

                {{-- INFO --}}
                <div class="movie-info space-y-1 text-sm text-gray-300">

                    {{-- GENRES --}}
                    <p class="mb-0">

                        <i class="fa-solid fa-film mr-2"></i>

                        {{
                            $movie->genres
                                ->pluck('ten_the_loai')
                                ->join(', ')
                            ?: '—'
                        }}

                    </p>

                    {{-- COUNTRY --}}
                    <p class="mb-0 text-xs text-gray-400">

                        <i class="fa-solid fa-flag mr-2"></i>

                        {{
                            $movie->country?->ten_quoc_gia
                            ?? '—'
                        }}

                    </p>

                    {{-- DURATION --}}
                    <p class="mb-0 text-xs text-gray-400">

                        <i class="fa-solid fa-clock mr-2"></i>

                        {{ $movie->thoi_luong }} phút

                    </p>

                    {{-- RELEASE DATE --}}
                    <p class="mb-0 text-xs text-gray-400">

                        <i class="fa-solid fa-calendar mr-2"></i>

                        {{
                            $movie->ngay_khoi_chieu
                            ? \Carbon\Carbon::parse($movie->ngay_khoi_chieu)->format('d/m/Y')
                            : '—'
                        }}

                    </p>

                    {{-- AGE --}}
                    <p class="mb-0 text-xs text-gray-400">

                        <i class="fa-solid fa-user-shield mr-2"></i>

                        {{ $movie->gioi_han_tuoi ?? 'P' }}

                    </p>

                </div>

                {{-- BUTTONS --}}
                <div class="movie-actions">

                    {{-- MAIN BUTTON --}}
                    <a href="{{ $buttonUrl }}"
                       class="btn-small-book {{ $buttonClass }}">

                        <i class="{{ $buttonIcon }} mr-1"></i>

                        {{ $buttonText }}

                    </a>

                    {{-- DETAIL --}}
                    @if ($status !== 'Đang chiếu')

                        <a href="{{ route('user.movies.show', $movie->slug) }}"
                           class="btn-small-detail">

                            Chi tiết

                        </a>

                    @endif

                </div>

            </div>

        </div>

    </div>

@empty

    <div class="col-12 text-center text-secondary py-5">

        Chưa có phim.

    </div>

@endforelse

</div>
