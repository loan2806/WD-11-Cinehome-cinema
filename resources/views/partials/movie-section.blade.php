<div class="row g-4">

    @forelse ($movies as $movie)
        @php
            // LẤY TRẠNG THÁI TỪ SUẤT CHIẾU
            if ($movie->showtimes->contains('trang_thai', \App\Models\SuatChieu::TRANG_THAI_DANG_CHIEU)) {
                $status = \App\Models\SuatChieu::TRANG_THAI_DANG_CHIEU;
            } elseif ($movie->showtimes->contains('trang_thai', \App\Models\SuatChieu::TRANG_THAI_SAP_CHIEU)) {
                $status = \App\Models\SuatChieu::TRANG_THAI_SAP_CHIEU;
            } elseif ($movie->showtimes->contains('trang_thai', \App\Models\SuatChieu::TRANG_THAI_SAP_RA_MAT)) {
                $status = \App\Models\SuatChieu::TRANG_THAI_SAP_RA_MAT;
            } else {
                $status = \App\Models\SuatChieu::TRANG_THAI_DA_CHIEU;
            }

            // DEFAULT
            $badgeClass = '';
            $buttonText = '';
            $buttonClass = '';
            $buttonUrl = '';
            $buttonIcon = '';
            $buttonStyle = '';
        @endphp

        {{-- BỎ PHIM ĐÃ CHIẾU --}}
        @if ($status === \App\Models\SuatChieu::TRANG_THAI_DA_CHIEU)
            @continue
        @endif

        @php
            /*
        |--------------------------------------------------------------------------
        | SẮP RA MẮT
        |--------------------------------------------------------------------------
        */
            if ($status === \App\Models\SuatChieu::TRANG_THAI_SAP_RA_MAT) {
                $badgeClass = 'bg-pink-600 text-white';

                $buttonText = 'Quan tâm';

                $buttonClass = 'bg-pink-600 text-white hover:bg-pink-500';

                $buttonUrl = route('user.movies.show', $movie->slug);

                $buttonIcon = 'fa-regular fa-heart';
            } /*
        |--------------------------------------------------------------------------
        | SẮP CHIẾU
        |--------------------------------------------------------------------------
        */ elseif (
                $status === \App\Models\SuatChieu::TRANG_THAI_SAP_CHIEU
            ) {
                $badgeClass = 'bg-blue-500 text-white';

                $buttonText = 'Đặt vé';

                $buttonClass = 'bg-[#f5a623] text-black hover:bg-[#ffc04d]';

                $showtime = $movie->showtimes
                    ->where('thoi_gian_chieu', '>=', now())
                    ->sortBy('thoi_gian_chieu')
                    ->first();

                if ($showtime) {
                    $buttonUrl = route('dat_ve.chon_ghe', $movie);
                } else {
                    $buttonUrl = route('user.movies.show', $movie->slug);
                }
                $buttonIcon = 'fa-solid fa-ticket';
            } /*
        |--------------------------------------------------------------------------
        | ĐANG CHIẾU
        |--------------------------------------------------------------------------
        */ else {
                $badgeClass = 'bg-[#f5a623] text-black';

                $buttonText = 'Chi tiết';

                $buttonClass =
                    'flex-1 text-center bg-[#1d1d1d] text-white font-bold py-3 rounded-xl  hover:bg-[#2b2b2b] transition';

                $buttonUrl = route('user.movies.show', $movie->slug);

                $buttonStyle = '';
            }
        @endphp

        <div class="col-6 col-md-4 col-lg-3 col-xl-2">

            <div class="movie-card">

                {{-- POSTER --}}
                <div class="movie-poster relative overflow-hidden">

                    <img src="{{ asset('storage/movies/' . $movie->poster) }}" alt="{{ $movie->ten_phim }}"
                        class="w-full h-full object-cover">

                    {{-- AGE --}}
                    <div
                        class="absolute top-3 right-3 z-10 px-3 py-2 rounded-full bg-black/70 text-white text-xs font-bold shadow-lg">
                        {{ $movie->gioi_han_tuoi }}
                    </div>

                </div>

                {{-- BODY --}}
                <div class="movie-body">

                    {{-- TITLE --}}
                    <h3 class="movie-title line-clamp-2 min-h-[56px]">
                        {{ $movie->ten_phim }}
                    </h3>

                    {{-- INFO --}}
                    <div class="movie-info space-y-1 text-sm text-gray-300">

                        <p class="mb-0">
                            <i class="fa-solid fa-film mr-2"></i>
                            {{ $movie->genres->pluck('ten_the_loai')->join(', ') }}
                        </p>

                        <p class="mb-0 text-xs text-gray-400">
                            <i class="fa-solid fa-flag mr-2"></i>
                            {{ $movie->country?->ten_quoc_gia }}
                        </p>

                        <p class="mb-0 text-xs text-gray-400">
                            <i class="fa-solid fa-clock mr-2"></i>
                            {{ $movie->thoi_luong }} phút
                        </p>

                        <p class="mb-0 text-xs text-gray-400">
                            <i class="fa-solid fa-user-shield mr-2"></i>
                            {{ $movie->gioi_han_tuoi }}
                        </p>

                    </div>

                    {{-- BUTTONS --}}
                    <div class="movie-actions">

                        <a href="{{ $buttonUrl }}" class="btn-small-book {{ $buttonClass }}"
                            style="{{ $buttonStyle }}">

                            <i class="{{ $buttonIcon }} mr-1"></i>
                            {{ $buttonText }}

                        </a>

                        @if ($buttonText !== 'Chi tiết')
                            <a href="{{ route('user.movies.show', $movie->slug) }}" class="btn-small-detail">
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
