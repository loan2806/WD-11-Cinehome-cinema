@extends('layouts.user')

@section('title', 'CineHome - Đặt vé xem phim')

@push('styles')
<style>
    /* 🌟 CSS BẢNG XẾP HẠNG TOP 3 PHIM HOT CỦA THÁNG */
    .hot-movies-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-top: 24px;
    }

    @media (max-width: 1200px) {
        .hot-movies-grid {
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }
    }

    .hot-movie-card {
        background: #18181c;
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 18px;
        padding: 16px;
        position: relative;
        display: flex;
        gap: 16px;
        align-items: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-sizing: border-box;
        cursor: pointer;
    }

    .hot-movie-card:hover {
        transform: translateY(-5px);
        border-color: rgba(250, 204, 21, 0.5);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.6), 0 0 20px rgba(250, 204, 21, 0.12);
    }

    .hot-rank-badge {
        position: absolute;
        top: -12px;
        left: 16px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 800;
        z-index: 10;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
    }

    .hot-movie-poster {
        width: 100px;
        height: 145px;
        flex-shrink: 0;
        border-radius: 12px;
        overflow: hidden;
        position: relative;
        background: #27272a;
    }

    .hot-movie-poster img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .hot-custom-age-badge {
        position: absolute !important;
        bottom: 6px !important;
        right: 6px !important;
        background: rgba(0, 0, 0, 0.8) !important;
        color: #facc15 !important;
        font-size: 10px !important;
        font-weight: 800 !important;
        padding: 3px 6px !important;
        border-radius: 6px !important;
        border: 1px solid rgba(250, 204, 21, 0.4) !important;
        line-height: 1 !important;
        z-index: 5 !important;
        width: auto !important;
        height: auto !important;
        display: inline-block !important;
    }

    .hot-movie-info {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        flex-grow: 1;
        min-width: 0;
        height: 145px;
    }

    .hot-movie-title {
        color: #ffffff;
        font-size: 15px;
        font-weight: 700;
        margin: 0 0 4px 0;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .hot-movie-stats {
        margin-bottom: 4px;
    }

    .hot-movie-stats .ticket-count {
        color: #facc15;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: rgba(250, 204, 21, 0.12);
        border: 1px solid rgba(250, 204, 21, 0.25);
        padding: 3px 8px;
        border-radius: 6px;
    }

    .hot-movie-genres {
        color: #9ca3af;
        font-size: 12px;
        margin: 0 0 8px 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .hot-card-actions {
        display: flex;
        gap: 8px;
        margin-top: auto;
    }

    .hot-card-actions .btn-hot-book {
        background: #ef4444;
        color: #ffffff !important;
        padding: 7px 12px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        flex: 1;
        transition: all 0.2s ease;
        border: none;
    }

    .hot-card-actions .btn-hot-book:hover {
        background: #dc2626;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.35);
    }

    .hot-card-actions .btn-hot-detail {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: #d1d5db !important;
        padding: 7px 12px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .hot-card-actions .btn-hot-detail:hover {
        background: rgba(255, 255, 255, 0.18);
        color: #ffffff !important;
    }

    /* 🎬 CSS MODAL TRAILER POPUP */
    .trailer-modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.85);
        backdrop-filter: blur(8px);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .trailer-modal-backdrop.show {
        opacity: 1;
        visibility: visible;
    }

    .trailer-modal-content {
        position: relative;
        width: 90%;
        max-width: 900px;
        background: #111827;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.15);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
    }

    .trailer-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        background: #1f2937;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .trailer-modal-header h4 {
        margin: 0;
        color: #fff;
        font-size: 16px;
        font-weight: 700;
    }

    .btn-close-modal {
        background: transparent;
        border: none;
        color: #9ca3af;
        font-size: 20px;
        cursor: pointer;
        transition: color 0.2s;
    }

    .btn-close-modal:hover {
        color: #ef4444;
    }

    .trailer-iframe-wrapper {
        position: relative;
        padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
        height: 0;
        overflow: hidden;
    }

    .trailer-iframe-wrapper iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: 0;
    }

    /* 🎁 CSS PHẦN PROMO / MẸO ĐẶT VÉ */
    .promo-section {
        background: linear-gradient(135deg, #1e1b4b, #311b92);
        border-radius: 20px;
        padding: 32px 40px;
        margin-top: 50px;
        border: 1px solid rgba(255, 255, 255, 0.12);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.4);
    }

    .promo-content h3 {
        color: #fff;
        font-size: 22px;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .promo-content p {
        color: #cbd5e1;
        font-size: 14px;
        margin: 0;
    }

    .promo-actions {
        display: flex;
        gap: 12px;
        flex-shrink: 0;
    }

    .promo-primary {
        background: #facc15;
        color: #000 !important;
        font-weight: 700;
        padding: 10px 20px;
        border-radius: 10px;
        text-decoration: none;
        transition: background 0.2s;
    }

    .promo-primary:hover {
        background: #eab308;
    }

    .promo-secondary {
        background: rgba(255,255,255,0.1);
        color: #fff !important;
        font-weight: 600;
        padding: 10px 20px;
        border-radius: 10px;
        text-decoration: none;
        border: 1px solid rgba(255,255,255,0.2);
        transition: background 0.2s;
    }

    .promo-secondary:hover {
        background: rgba(255,255,255,0.2);
    }

    @media (max-width: 768px) {
        .promo-section {
            flex-direction: column;
            text-align: center;
            padding: 24px;
        }
        .promo-actions {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
    @php
        // 🛠️ HÀM TỰ ĐỘNG XỬ LÝ ĐƯỜNG DẪN ẢNH KHÔNG BỊ LỖI LINK
        $getPosterUrl = function($poster) {
            if (empty($poster)) {
                return 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=700&q=80';
            }
            if (\Illuminate\Support\Str::startsWith($poster, ['http://', 'https://'])) {
                return $poster;
            }
            if (\Illuminate\Support\Str::startsWith($poster, 'storage/')) {
                return asset($poster);
            }
            if (\Illuminate\Support\Str::startsWith($poster, 'movies/')) {
                return asset('storage/' . $poster);
            }
            return asset('storage/movies/' . $poster);
        };

        // 🎬 HÀM CHUYỂN LINK YOUTUBE THÀNH EMBED EMBED LINK
        $getTrailerEmbedUrl = function($url) {
            if (empty($url)) return '';
            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches);
            return isset($matches[1]) ? 'https://www.youtube.com/embed/' . $matches[1] . '?autoplay=1' : $url;
        };

        $heroMovies = $bannerMovies
            ->merge($comingSoonMovies)
            ->merge($nowShowingMovies)
            ->merge($comingLaterMovies)
            ->unique('id')
            ->take(5);

        $nowShowingRail = $nowShowingMovies->isNotEmpty()
            ? $nowShowingMovies->take(12)
            : $heroMovies->merge($comingSoonMovies)->unique('id')->take(12);
        $comingSoonRail = $comingSoonMovies->merge($comingLaterMovies)->unique('id')->take(12);
        $visualMovies = $heroMovies
            ->merge($nowShowingRail)
            ->merge($comingSoonRail)
            ->unique('id')
            ->take(10);

        $fallbackVisualMovies = collect([
            ['title' => 'Bom tấn hành động', 'image' => 'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?auto=format&fit=crop&w=700&q=80'],
            ['title' => 'Đêm phim cảm xúc', 'image' => 'https://images.unsplash.com/photo-1478720568477-152d9b164e26?auto=format&fit=crop&w=700&q=80'],
            ['title' => 'Suất chiếu đặc biệt', 'image' => 'https://images.unsplash.com/photo-1485846234645-a62644f84728?auto=format&fit=crop&w=700&q=80'],
            ['title' => 'Rạp phim cuối tuần', 'image' => 'https://images.unsplash.com/photo-1535016120720-40c646be5580?auto=format&fit=crop&w=700&q=80'],
            ['title' => 'Màn ảnh lớn', 'image' => 'https://images.unsplash.com/photo-1505686994434-e3cc5abf1330?auto=format&fit=crop&w=700&q=80'],
        ]);

        $cinemaShots = collect([
            ['title' => 'Phòng chiếu cao cấp', 'image' => 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=900&q=80'],
            ['title' => 'Khoảnh khắc trước giờ chiếu', 'image' => 'https://images.unsplash.com/photo-1524985069026-dd778a71c7b4?auto=format&fit=crop&w=900&q=80'],
            ['title' => 'Màn ảnh lớn', 'image' => 'https://images.unsplash.com/photo-1505686994434-e3cc5abf1330?auto=format&fit=crop&w=900&q=80'],
            ['title' => 'Đồ ăn rạp phim', 'image' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=900&q=80'],
        ]);
    @endphp

    <section class="cinema-home">
        <!-- 🌟 HERO BANNER SLIDER -->
        <section class="booking-hero hero-slider" data-home-slider>
            @forelse ($heroMovies as $movie)
                @php
                    $detailUrlWithSchedule = route('user.movies.show', $movie->slug) . '#lich-chieu';
                    $posterUrl = $getPosterUrl($movie->poster);
                    $trailerEmbed = $getTrailerEmbedUrl($movie->trailer_url ?? $movie->trailer ?? '');
                @endphp

                <article class="hero-slide booking-hero-slide {{ $loop->first ? 'active' : '' }}"
                    data-slide-index="{{ $loop->index }}"
                    style="background-image: url('{{ $posterUrl }}');">
                    <div class="container-fluid px-5 booking-hero-content hero-content">
                        <div class="booking-hero-copy hero-info">
                            <div class="booking-eyebrow">
                                <i class="fa-solid fa-bolt"></i>
                                Đặt vé nhanh tại CineHome
                            </div>

                            <h1 class="booking-hero-title hero-title">
                                {{ $movie->ten_phim }}
                            </h1>

                            <p class="booking-hero-desc hero-desc">
                                {{ \Illuminate\Support\Str::limit($movie->mo_ta, 190) }}
                            </p>

                            <div class="booking-hero-meta hero-meta">
                                <span>
                                    <i class="fa-solid fa-film"></i>
                                    {{ $movie->genres->pluck('ten_the_loai')->take(2)->join(', ') ?: 'Điện ảnh' }}
                                </span>

                                <span>
                                    <i class="fa-solid fa-clock"></i>
                                    {{ $movie->thoi_luong }} phút
                                </span>

                                <span>
                                    <i class="fa-solid fa-user-shield"></i>
                                    {{ $movie->gioi_han_tuoi }}
                                </span>
                            </div>

                            <div class="booking-hero-actions hero-buttons">
                                <a href="{{ $detailUrlWithSchedule }}" class="btn-book booking-primary-btn">
                                    <i class="fa-solid fa-ticket"></i>
                                    Đặt vé ngay
                                </a>

                                @if (!empty($trailerEmbed))
                                    <button type="button" class="btn-trailer booking-ghost-btn js-open-trailer"
                                        data-trailer-title="{{ $movie->ten_phim }}"
                                        data-trailer-url="{{ $trailerEmbed }}">
                                        <i class="fa-solid fa-play"></i>
                                        Xem Trailer
                                    </button>
                                @else
                                    <a href="{{ route('user.movies.show', $movie->slug) }}" class="btn-trailer booking-ghost-btn">
                                        <i class="fa-solid fa-circle-info"></i>
                                        Chi tiết phim
                                    </a>
                                @endif
                            </div>

                            <div class="booking-hero-stats">
                                <div>
                                    <strong>{{ $nowShowingMovies->count() }}</strong>
                                    <span>Phim đang chiếu</span>
                                </div>
                                <div>
                                    <strong>{{ $comingSoonMovies->count() + $comingLaterMovies->count() }}</strong>
                                    <span>Phim sắp chiếu</span>
                                </div>
                                <div>
                                    <strong>3</strong>
                                    <span>Bước nhận vé</span>
                                </div>
                            </div>
                        </div>

                        <div class="booking-hero-poster reveal-on-scroll">
                            <img src="{{ $posterUrl }}" alt="{{ $movie->ten_phim }}">
                            <div class="poster-ticket">
                                <i class="fa-solid fa-ticket"></i>
                                Vé điện tử
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <article class="hero-slide booking-hero-slide active empty-hero"
                    style="background-image: url('https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=1800&q=80');">
                    <div class="container-fluid px-5 booking-hero-content hero-content">
                        <div class="booking-hero-copy hero-info">
                            <div class="booking-eyebrow">
                                <i class="fa-solid fa-bolt"></i>
                                CineHome Cinema
                            </div>
                            <h1 class="booking-hero-title hero-title">Đặt vé xem phim dễ dàng</h1>
                            <p class="booking-hero-desc hero-desc">
                                Khám phá lịch chiếu mới nhất, chọn ghế yêu thích và nhận vé điện tử chỉ trong vài bước.
                            </p>
                            <div class="booking-hero-actions hero-buttons">
                                <a href="{{ route('dat_ve.chon_phim') }}" class="btn-book booking-primary-btn">
                                    <i class="fa-solid fa-ticket"></i>
                                    Đặt vé ngay
                                </a>
                            </div>
                        </div>
                    </div>
                </article>
            @endforelse

            @if ($heroMovies->count() > 1)
                <div class="booking-hero-controls">
                    <button type="button" class="hero-control" data-slide-prev aria-label="Phim trước">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <button type="button" class="hero-control" data-slide-next aria-label="Phim tiếp theo">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>

                <div class="booking-hero-dots">
                    @foreach ($heroMovies as $movie)
                        <button type="button" class="{{ $loop->first ? 'active' : '' }}"
                            data-slide-target="{{ $loop->index }}" aria-label="Chọn {{ $movie->ten_phim }}">
                            <span></span>
                        </button>
                    @endforeach
                </div>
            @endif
        </section>

        <main class="main-section booking-main">
            <div class="container-fluid px-5">
                <!-- 🚀 QUICK STEPS -->
                <section class="quick-booking-panel reveal-on-scroll">
                    <a href="{{ route('dat_ve.chon_phim') }}" class="quick-booking-step">
                        <span>01</span>
                        <strong>Chọn phim</strong>
                        <small>Phim hot và suất chiếu mới nhất</small>
                        <i class="fa-solid fa-film"></i>
                    </a>

                    <a href="{{ route('dat_ve.chon_phim') }}" class="quick-booking-step">
                        <span>02</span>
                        <strong>Chọn suất chiếu</strong>
                        <small>Lọc theo ngày, giờ và rạp gần bạn</small>
                        <i class="fa-solid fa-calendar-days"></i>
                    </a>

                    <a href="{{ route('user.cinemas.index') }}" class="quick-booking-step">
                        <span>03</span>
                        <strong>Nhận vé điện tử</strong>
                        <small>Thanh toán nhanh, vào rạp tiện lợi</small>
                        <i class="fa-solid fa-qrcode"></i>
                    </a>
                </section>

                <!-- 🏆 TOP 3 PHIM HOT CỦA THÁNG -->
                <section class="booking-section reveal-on-scroll" style="margin-top: 40px;">
                    <div class="booking-section-head">
                        <div>
                            <p style="color: #facc15; font-weight: 700; margin-bottom: 4px;">
                                <i class="fa-solid fa-fire" style="color: #ef4444; margin-right: 4px;"></i>
                                BẢNG XẾP HẠNG THÁNG {{ $tenThangHienTai ?? now()->month }}
                            </p>
                            <h2>Top 3 Phim Hot Của Tháng</h2>
                        </div>
                        <div class="booking-section-actions">
                            <span style="color: #9ca3af; font-size: 13px;">
                                <i class="fa-solid fa-rotate" style="margin-right: 4px;"></i> Tự động làm mới mỗi tháng
                            </span>
                        </div>
                    </div>

                    <div class="hot-movies-grid">
                        @forelse ($hotMovies as $index => $movie)
                            @php
                                $rank = $index + 1;
                                $badgeStyle = match($rank) {
                                    1 => 'background: linear-gradient(135deg, #f59e0b, #ef4444); color: #fff; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);',
                                    2 => 'background: linear-gradient(135deg, #9ca3af, #4b5563); color: #fff;',
                                    3 => 'background: linear-gradient(135deg, #b45309, #78350f); color: #fff;',
                                    default => 'background: #374151; color: #fff;'
                                };
                                $detailUrlWithSchedule = route('user.movies.show', $movie->slug) . '#lich-chieu';
                                $posterUrl = $getPosterUrl($movie->poster);
                            @endphp

                            <div class="hot-movie-card" onclick="window.location.href='{{ $detailUrlWithSchedule }}'">
                                <div class="hot-rank-badge" style="{{ $badgeStyle }}">
                                    <i class="fa-solid fa-crown"></i> TOP {{ $rank }}
                                </div>
                                <div class="hot-movie-poster">
                                    <img src="{{ $posterUrl }}" alt="{{ $movie->ten_phim }}">
                                    <span class="hot-custom-age-badge">{{ $movie->gioi_han_tuoi }}</span>
                                </div>
                                <div class="hot-movie-info">
                                    <h3 class="hot-movie-title">{{ $movie->ten_phim }}</h3>
                                    <div class="hot-movie-stats">
                                        <span class="ticket-count">
                                            <i class="fa-solid fa-ticket"></i>
                                            <strong>{{ number_format($movie->tong_ve_thang ?? 0) }}</strong> vé đã đặt
                                        </span>
                                    </div>
                                    <p class="hot-movie-genres">
                                        <i class="fa-solid fa-tags"></i>
                                        {{ $movie->genres->pluck('ten_the_loai')->take(2)->join(', ') ?: 'Điện ảnh' }}
                                    </p>
                                    <div class="hot-card-actions">
                                        <a href="{{ $detailUrlWithSchedule }}" class="btn-hot-book" onclick="event.stopPropagation();">
                                            <i class="fa-solid fa-ticket"></i> Đặt vé
                                        </a>
                                        <a href="{{ route('user.movies.show', $movie->slug) }}" class="btn-hot-detail" onclick="event.stopPropagation();">
                                            Chi tiết
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="booking-empty-state" style="grid-column: 1 / -1;">
                                <i class="fa-solid fa-fire"></i>
                                Chưa có dữ liệu phim hot cho tháng này.
                            </div>
                        @endforelse
                    </div>
                </section>

                <!-- 🖼️ BỘ SƯU TẬP POSTER -->
                <section class="booking-poster-wall reveal-on-scroll">
                    <div class="poster-wall-copy">
                        <span>
                            <i class="fa-solid fa-images"></i>
                            Bộ sưu tập nổi bật
                        </span>
                        <h2>Chọn phim bằng cảm xúc từ những khung hình đầu tiên</h2>
                        <p>
                            Trang chủ được phủ nhiều poster phim hơn để người dùng lướt nhanh, nhìn thấy phim nổi bật
                            và đi thẳng đến chi tiết hoặc đặt vé.
                        </p>
                        <a href="{{ route('user.phims.index') }}">
                            Khám phá phim
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>

                    <div class="poster-wall-grid" aria-label="Poster phim nổi bật">
                        @foreach ($visualMovies as $movie)
                            <a href="{{ route('user.movies.show', $movie->slug) }}#lich-chieu"
                                class="poster-wall-card {{ $loop->first ? 'large' : '' }}">
                                <img src="{{ $getPosterUrl($movie->poster) }}" alt="{{ $movie->ten_phim }}">
                                <span>{{ $movie->ten_phim }}</span>
                            </a>
                        @endforeach

                        @foreach ($fallbackVisualMovies->take(max(0, 9 - $visualMovies->count())) as $poster)
                            <a href="{{ route('user.phims.index') }}"
                                class="poster-wall-card {{ $visualMovies->isEmpty() && $loop->first ? 'large' : '' }}">
                                <img src="{{ $poster['image'] }}" alt="{{ $poster['title'] }}">
                                <span>{{ $poster['title'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </section>

                <!-- 🍿 KHÔNG GIAN RẠP CINEMA -->
                <section class="cinema-experience-board reveal-on-scroll">
                    <div class="experience-board-head">
                        <span>
                            <i class="fa-solid fa-clapperboard"></i>
                            Không gian CineHome
                        </span>
                        <h2>Trải nghiệm rạp phim được đưa lên ngay trang chủ</h2>
                    </div>

                    <div class="experience-shot-grid">
                        @foreach ($cinemaShots as $shot)
                            <figure class="experience-shot {{ $loop->first ? 'wide' : '' }}">
                                <img src="{{ $shot['image'] }}" alt="{{ $shot['title'] }}">
                                <figcaption>{{ $shot['title'] }}</figcaption>
                            </figure>
                        @endforeach
                    </div>
                </section>

                <!-- 💡 LỢI ÍCH -->
                <section class="booking-benefits reveal-on-scroll">
                    <div class="booking-benefit-card">
                        <span><i class="fa-solid fa-couch"></i></span>
                        <div>
                            <h3>Chọn ghế trực quan</h3>
                            <p>Xem sơ đồ ghế rõ ràng, chọn đúng vị trí yêu thích trước khi thanh toán.</p>
                        </div>
                    </div>

                    <div class="booking-benefit-card">
                        <span><i class="fa-solid fa-shield-halved"></i></span>
                        <div>
                            <h3>Giữ ghế tạm thời</h3>
                            <p>Ghế được khóa trong quá trình đặt vé để bạn thao tác yên tâm hơn.</p>
                        </div>
                    </div>

                    <div class="booking-benefit-card">
                        <span><i class="fa-solid fa-mobile-screen-button"></i></span>
                        <div>
                            <h3>Vé điện tử tiện lợi</h3>
                            <p>Nhận mã vé sau thanh toán và xuất trình nhanh khi đến rạp.</p>
                        </div>
                    </div>
                </section>

                <!-- 🎬 PHIM ĐANG CHIẾU -->
                <section class="booking-section reveal-on-scroll" data-rail-section>
                    <div class="booking-section-head">
                        <div>
                            <p>Đang chiếu</p>
                            <h2>Phim đang chiếu</h2>
                        </div>
                        <div class="booking-section-actions">
                            <a href="{{ route('dat_ve.chon_phim') }}">
                                Đặt vé ngay
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                            <div class="rail-controls">
                                <button type="button" data-rail-prev aria-label="Cuộn phim trước">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </button>
                                <button type="button" data-rail-next aria-label="Cuộn phim tiếp theo">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="booking-movie-rail">
                        @forelse ($nowShowingRail as $movie)
                            @php
                                $detailUrlWithSchedule = route('user.movies.show', $movie->slug) . '#lich-chieu';
                                $posterUrl = $getPosterUrl($movie->poster);
                                $trailerEmbed = $getTrailerEmbedUrl($movie->trailer_url ?? $movie->trailer ?? '');
                            @endphp

                            <article class="booking-movie-card">
                                <a href="{{ $detailUrlWithSchedule }}" class="booking-movie-poster">
                                    <img src="{{ $posterUrl }}" alt="{{ $movie->ten_phim }}">
                                    <span class="movie-age-badge">{{ $movie->gioi_han_tuoi }}</span>
                                    
                                    @if(!empty($trailerEmbed))
                                        <span class="movie-play-overlay js-open-trailer" data-trailer-title="{{ $movie->ten_phim }}" data-trailer-url="{{ $trailerEmbed }}" onclick="event.preventDefault(); event.stopPropagation();">
                                            <i class="fa-solid fa-play"></i>
                                        </span>
                                    @else
                                        <span class="movie-play-overlay">
                                            <i class="fa-solid fa-play"></i>
                                        </span>
                                    @endif
                                </a>

                                <div class="booking-movie-body">
                                    <h3>{{ $movie->ten_phim }}</h3>
                                    <p>
                                        <i class="fa-solid fa-clock"></i>
                                        {{ $movie->thoi_luong }} phút
                                    </p>
                                    <p>
                                        <i class="fa-solid fa-tags"></i>
                                        {{ $movie->genres->pluck('ten_the_loai')->take(2)->join(', ') ?: 'Điện ảnh' }}
                                    </p>

                                    <div class="booking-card-actions">
                                        <a href="{{ $detailUrlWithSchedule }}" class="card-book-btn">
                                            <i class="fa-solid fa-ticket"></i>
                                            Đặt vé
                                        </a>
                                        <a href="{{ route('user.movies.show', $movie->slug) }}" class="card-detail-btn">
                                            Chi tiết
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="booking-empty-state">
                                <i class="fa-solid fa-film"></i>
                                Chưa có phim đang chiếu.
                            </div>
                        @endforelse
                    </div>
                </section>

                <!-- ⏳ PHIM SẮP CHIẾU -->
                <section class="booking-section reveal-on-scroll" data-rail-section>
                    <div class="booking-section-head">
                        <div>
                            <p>Sắp chiếu</p>
                            <h2>Sắp chiếu tại CineHome</h2>
                        </div>
                        <div class="booking-section-actions">
                            <a href="{{ route('user.phims.index') }}">
                                Xem tất cả
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                            <div class="rail-controls">
                                <button type="button" data-rail-prev aria-label="Cuộn phim trước">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </button>
                                <button type="button" data-rail-next aria-label="Cuộn phim tiếp theo">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="booking-movie-rail compact">
                        @forelse ($comingSoonRail as $movie)
                            @php
                                $posterUrl = $getPosterUrl($movie->poster);
                                $trailerEmbed = $getTrailerEmbedUrl($movie->trailer_url ?? $movie->trailer ?? '');
                            @endphp

                            <article class="booking-movie-card compact">
                                <a href="{{ route('user.movies.show', $movie->slug) }}" class="booking-movie-poster">
                                    <img src="{{ $posterUrl }}" alt="{{ $movie->ten_phim }}">
                                    <span class="movie-age-badge">{{ $movie->gioi_han_tuoi }}</span>
                                    
                                    @if(!empty($trailerEmbed))
                                        <span class="movie-play-overlay js-open-trailer" data-trailer-title="{{ $movie->ten_phim }}" data-trailer-url="{{ $trailerEmbed }}" onclick="event.preventDefault(); event.stopPropagation();">
                                            <i class="fa-solid fa-play"></i>
                                        </span>
                                    @else
                                        <span class="movie-play-overlay">
                                            <i class="fa-solid fa-play"></i>
                                        </span>
                                    @endif
                                </a>

                                <div class="booking-movie-body">
                                    <h3>{{ $movie->ten_phim }}</h3>
                                    <p>
                                        <i class="fa-solid fa-clock"></i>
                                        {{ $movie->thoi_luong }} phút
                                    </p>
                                    <p>
                                        <i class="fa-solid fa-tags"></i>
                                        {{ $movie->genres->pluck('ten_the_loai')->take(2)->join(', ') ?: 'Điện ảnh' }}
                                    </p>
                                    @if (!empty($movie->ngay_khoi_chieu))
                                        <p style="color: #facc15; font-size: 12px;">
                                            <i class="fa-solid fa-calendar-day"></i>
                                            Khởi chiếu: {{ \Carbon\Carbon::parse($movie->ngay_khoi_chieu)->format('d/m/Y') }}
                                        </p>
                                    @endif

                                    <div class="booking-card-actions">
                                        <a href="{{ route('user.movies.show', $movie->slug) }}" class="card-detail-btn" style="width: 100%; text-align: center;">
                                            <i class="fa-solid fa-circle-info"></i>
                                            Chi tiết
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="booking-empty-state">
                                <i class="fa-solid fa-film"></i>
                                Chưa có phim sắp chiếu.
                            </div>
                        @endforelse
                    </div>
                </section>

                <!-- 🎁 BẢNG KHUYẾN MÃI / THÀNH VIÊN (Đoạn mã bổ sung của bạn) -->
                <section class="promo-section reveal-on-scroll">
                    <div class="promo-content">
                        <h3>Trở thành thành viên CineHome VIP</h3>
                        <p>
                            Nhận nhiều ưu đãi độc quyền, tích điểm đổi Voucher quà tặng, quản lý vé điện tử và theo dõi lịch sử đặt vé trong tài khoản của bạn.
                        </p>
                    </div>

                    <div class="promo-actions">
                        <a href="{{ route('user.thanh-vien.index') }}" class="promo-primary">
                            Xem quyền lợi
                        </a>
                        <a href="{{ route('user.voucher.index') }}" class="promo-secondary">
                            Đổi voucher
                        </a>
                    </div>
                </section>
            </div>
        </main>
    </section>

    <!-- 🎬 MODAL PHÁT TRAILER PHIM -->
    <div class="trailer-modal-backdrop" id="trailerModal">
        <div class="trailer-modal-content">
            <div class="trailer-modal-header">
                <h4 id="trailerModalTitle">Trailer Phim</h4>
                <button type="button" class="btn-close-modal" id="btnCloseTrailerModal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="trailer-iframe-wrapper">
                <iframe id="trailerIframe" src="" allow="autoplay; encrypted-media" allowfullscreen></iframe>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Tự động chuyển Banner Hero Slider
        const heroSlider = document.querySelector('[data-home-slider]');
        if (heroSlider) {
            const slides = heroSlider.querySelectorAll('.hero-slide');
            const dots = heroSlider.querySelectorAll('[data-slide-target]');
            const btnPrev = heroSlider.querySelector('[data-slide-prev]');
            const btnNext = heroSlider.querySelector('[data-slide-next]');
            let currentIndex = 0;
            let slideInterval;

            function showSlide(index) {
                if (slides.length === 0) return;
                slides.forEach(s => s.classList.remove('active'));
                dots.forEach(d => d.classList.remove('active'));

                currentIndex = (index + slides.length) % slides.length;
                slides[currentIndex].classList.add('active');
                if (dots[currentIndex]) {
                    dots[currentIndex].classList.add('active');
                }
            }

            function nextSlide() {
                showSlide(currentIndex + 1);
            }

            function startAutoPlay() {
                if (slides.length > 1) {
                    slideInterval = setInterval(nextSlide, 5000);
                }
            }

            function stopAutoPlay() {
                clearInterval(slideInterval);
            }

            if (btnPrev) {
                btnPrev.addEventListener('click', () => {
                    stopAutoPlay();
                    showSlide(currentIndex - 1);
                    startAutoPlay();
                });
            }

            if (btnNext) {
                btnNext.addEventListener('click', () => {
                    stopAutoPlay();
                    nextSlide();
                    startAutoPlay();
                });
            }

            dots.forEach(dot => {
                dot.addEventListener('click', () => {
                    stopAutoPlay();
                    const targetIndex = parseInt(dot.getAttribute('data-slide-target'));
                    showSlide(targetIndex);
                    startAutoPlay();
                });
            });

            startAutoPlay();
        }

        // 2. Cuộn danh sách phim ngang (Movie Rail)
        const railSections = document.querySelectorAll('[data-rail-section]');
        railSections.forEach(section => {
            const rail = section.querySelector('.booking-movie-rail');
            const btnPrev = section.querySelector('[data-rail-prev]');
            const btnNext = section.querySelector('[data-rail-next]');

            if (rail) {
                if (btnPrev) {
                    btnPrev.addEventListener('click', () => {
                        rail.scrollBy({ left: -320, behavior: 'smooth' });
                    });
                }
                if (btnNext) {
                    btnNext.addEventListener('click', () => {
                        rail.scrollBy({ left: 320, behavior: 'smooth' });
                    });
                }
            }
        });

        // 3. 🎬 XỬ LÝ PHÁT TRAILER BẰNG MODAL NỔI
        const trailerModal = document.getElementById('trailerModal');
        const trailerIframe = document.getElementById('trailerIframe');
        const trailerTitle = document.getElementById('trailerModalTitle');
        const btnCloseModal = document.getElementById('btnCloseTrailerModal');

        function openTrailerModal(title, url) {
            if (!url) return;
            trailerTitle.textContent = 'Trailer: ' + title;
            trailerIframe.src = url;
            trailerModal.classList.add('show');
            document.body.style.overflow = 'hidden'; // Khóa cuộn trang
        }

        function closeTrailerModal() {
            trailerModal.classList.remove('show');
            trailerIframe.src = ''; // Tắt video để không phát tiếng
            document.body.style.overflow = '';
        }

        document.querySelectorAll('.js-open-trailer').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const title = this.getAttribute('data-trailer-title') || 'Phim';
                const url = this.getAttribute('data-trailer-url');
                openTrailerModal(title, url);
            });
        });

        if (btnCloseModal) {
            btnCloseModal.addEventListener('click', closeTrailerModal);
        }

        if (trailerModal) {
            trailerModal.addEventListener('click', function (e) {
                if (e.target === trailerModal) {
                    closeTrailerModal();
                }
            });
        }
    });
</script>
@endpush