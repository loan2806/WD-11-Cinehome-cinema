<div class="row g-4">

    @forelse ($movies as $movie)
        @php

            $badgeText = $movie->schedule_status;

            // SẮP RA MẮT
            if ($badgeText === 'Sắp ra mắt') {
                $badgeClass = 'bg-pink-600 text-white';

                $buttonText = 'Quan tâm';

                $buttonClass = 'bg-pink-600 text-white hover:bg-pink-500';

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

                $buttonText = 'Chi tiết';

                $buttonClass = 'bg-white text-black hover:bg-gray-200';

                $buttonUrl = route('user.movies.show', $movie->slug);

                $buttonIcon = 'fa-solid fa-circle-info';
            }

        @endphp

        <div class="col-6 col-md-4 col-lg-3 col-xl-2">

            <div class="movie-card">

                <div class="movie-poster relative overflow-hidden">

                    <img src="{{ $movie->poster }}" alt="{{ $movie->title }}">

                    {{-- STATUS --}}
                    <div
                        class="absolute top-3 left-3 z-10 px-4 py-2 rounded-full text-xs font-bold shadow-lg {{ $badgeClass }}">
                        {{ $badgeText }}
                    </div>

                    {{-- AGE --}}
                    <div
                        class="absolute top-3 right-3 z-10 px-3 py-2 rounded-full bg-black/70 text-white text-xs font-bold shadow-lg">
                        {{ $movie->age_rating }}
                    </div>

                </div>

                <div class="movie-body">

                    <h3 class="movie-title">
                        {{ $movie->title }}
                    </h3>

                    <div class="movie-info">

                        <p class="mb-1">
                            <i class="fa-solid fa-film"></i>
                            {{ $movie->genre }}
                        </p>

                        <p class="mb-0">
                            <i class="fa-solid fa-clock"></i>
                            {{ $movie->duration }} phút
                        </p>

                    </div>

                    <div class="movie-actions">

                        {{-- SẮP CHIẾU --}}
                        @if ($badgeText === 'Sắp chiếu')
                            <a href="{{ $buttonUrl }}" class="btn-small-book {{ $buttonClass }}">

                                <i class="{{ $buttonIcon }} mr-1"></i>

                                {{ $buttonText }}

                            </a>

                            {{-- SẮP RA MẮT --}}
                        @elseif ($badgeText === 'Sắp ra mắt')
                            <a href="{{ $buttonUrl }}" class="btn-small-book {{ $buttonClass }}">

                                <i class="{{ $buttonIcon }} mr-1"></i>

                                {{ $buttonText }}

                            </a>
                        @endif


                        {{-- CHI TIẾT --}}
                        <a href="{{ route('user.movies.show', $movie->slug) }}" class="btn-small-detail">

                            Chi tiết

                        </a>

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
