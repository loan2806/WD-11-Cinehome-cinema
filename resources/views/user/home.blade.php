@extends('layouts.user')

@section('title', 'CineHome - Đặt vé xem phim')

@section('content')

    {{-- HERO SLIDER --}}
    <section class="hero-slider">

        @forelse ($bannerMovies as $index => $movie)
            @php
                $status = $movie->schedule_status;
            @endphp
            <div class="hero-slide {{ $index === 0 ? 'active' : '' }}"
                style="background-image: url('{{ $movie->cover_image }}');">
                <div class="container-fluid px-5 hero-content">
                    <div class="hero-info">

                        <div class="hero-badge">
                            <i class="fa-solid fa-fire"></i>
                            Phim hot trong tháng
                        </div>

                        <h1 class="hero-title">
                            {{ $movie->title }}
                        </h1>

                        <p class="hero-desc">
                            {{ $movie->description }}
                        </p>

                        <div class="hero-meta">
                            <span><i class="fa-solid fa-film"></i> {{ $movie->genre }}</span>
                            <span><i class="fa-solid fa-clock"></i> {{ $movie->duration }} phút</span>
                            <span><i class="fa-solid fa-user-shield"></i> {{ $movie->age_rating }}</span>
                            <span><i class="fa-solid fa-calendar"></i> {{ $movie->release_date?->format('d/m/Y') }}</span>
                        </div>

                        <div class="hero-buttons">
                            @if ($status === 'Sắp chiếu')
                                <a href="{{ route('user.bookings.index', $movie) }}" class="btn-book">
                                    <i class="fa-solid fa-ticket"></i> Đặt vé ngay
                                </a>
                            @else
                                <a href="{{ route('user.movies.show', $movie->slug) }}" class="btn-book">
                                    <i class="fa-solid fa-film"></i> Xem thêm
                                </a>
                            @endif

                            <a href="{{ $movie->trailer_url }}" target="_blank" class="btn-trailer">
                                <i class="fa-solid fa-play"></i> Xem trailer
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        @empty
            <div class="hero-slide active">
                <div class="container-fluid px-5 hero-content">
                    <div class="hero-info">
                        <h1 class="hero-title">Chưa có phim</h1>
                        <p class="hero-desc">Hãy seed dữ liệu phim vào database.</p>
                    </div>
                </div>
            </div>
        @endforelse

        {{-- TOP 5 HOT --}}
        <div class="hot-movies">
            <div class="section-label">
                <i class="fa-solid fa-ranking-star"></i>
                Top 5 phim
            </div>

            <div class="hot-list">
                @foreach ($bannerMovies as $index => $movie)
                    <div class="hot-item {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}">
                        <div class="hot-rank">{{ $index + 1 }}</div>
                        <img src="{{ $movie->poster }}" alt="{{ $movie->title }}">
                        <p>{{ $movie->title }}</p>
                    </div>
                @endforeach
            </div>
        </div>

    </section>

    {{-- DANH SÁCH PHIM --}}
    <main class="main-section">
        <div class="container-fluid px-5">

            {{-- PHIM ĐANG CHIẾU --}}
            <div class="section-title-wrap">
                <h2 class="section-title">
                    Phim <span>đang chiếu</span>
                </h2>
            </div>

            @include('partials.movie-section', ['movies' => $nowShowingMovies])

            {{-- PHIM SẮP CHIẾU --}}
            <div class="section-title-wrap mt-5">
                <h2 class="section-title">
                    Phim <span>sắp chiếu</span>
                </h2>
            </div>

            @include('partials.movie-section', ['movies' => $comingSoonMovies])

            {{-- PHIM SẮP RA MẮT --}}
            <div class="section-title-wrap mt-5">
                <h2 class="section-title">
                    Phim <span>sắp ra mắt</span>
                </h2>
            </div>

            @include('partials.movie-section', ['movies' => $comingLaterMovies])

        </div>
    </main>

@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const slides = document.querySelectorAll('.hero-slide');
            const hotItems = document.querySelectorAll('.hot-item');

            hotItems.forEach(function(item) {
                item.addEventListener('click', function() {
                    const index = Number(this.dataset.slide);

                    slides.forEach(function(slide) {
                        slide.classList.remove('active');
                    });

                    hotItems.forEach(function(hot) {
                        hot.classList.remove('active');
                    });

                    if (slides[index]) {
                        slides[index].classList.add('active');
                    }

                    this.classList.add('active');
                });
            });
        });
    </script>
@endsection
