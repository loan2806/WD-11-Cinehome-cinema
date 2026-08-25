@extends('layouts.user')

@section('title', 'CineHome - Đặt vé xem phim')

@push('styles')
<style>
    /* 🌟 BẢNG XẾP HẠNG TOP 3 PHIM HOT CỦA THÁNG (NETFLIX STYLE) */
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
        border-color: rgba(229, 9, 20, 0.6);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.7), 0 0 20px rgba(229, 9, 20, 0.2);
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
</style>
@endpush

@section('content')
    @php
        // Ưu tiên hiển thị TẤT CẢ các phim đang có suất chiếu / đang chiếu tại rạp
        $moviesWithShowtimes = $nowShowingMovies->merge($comingSoonMovies)->unique('id');
        $heroMovies = $moviesWithShowtimes->isNotEmpty()
            ? $moviesWithShowtimes
            : ($bannerMovies->isNotEmpty() ? $bannerMovies->merge($comingLaterMovies)->unique('id') : collect());

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
            [
                'title' => 'Bom tấn hành động',
                'image' => 'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?auto=format&fit=crop&w=700&q=80',
            ],
            [
                'title' => 'Đêm phim cảm xúc',
                'image' => 'https://images.unsplash.com/photo-1478720568477-152d9b164e26?auto=format&fit=crop&w=700&q=80',
            ],
            [
                'title' => 'Suất chiếu đặc biệt',
                'image' => 'https://images.unsplash.com/photo-1485846234645-a62644f84728?auto=format&fit=crop&w=700&q=80',
            ],
            [
                'title' => 'Rạp phim cuối tuần',
                'image' => 'https://images.unsplash.com/photo-1535016120720-40c646be5580?auto=format&fit=crop&w=700&q=80',
            ],
            [
                'title' => 'Màn ảnh lớn',
                'image' => 'https://images.unsplash.com/photo-1505686994434-e3cc5abf1330?auto=format&fit=crop&w=700&q=80',
            ],
            [
                'title' => 'Không khí điện ảnh',
                'image' => 'https://images.unsplash.com/photo-1524985069026-dd778a71c7b4?auto=format&fit=crop&w=700&q=80',
            ],
            [
                'title' => 'Ghế ngồi êm ái',
                'image' => 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=700&q=80',
            ],
            [
                'title' => 'Trước giờ chiếu',
                'image' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=700&q=80',
            ],
        ]);
        $cinemaShots = collect([
            [
                'title' => 'Phòng chiếu cao cấp',
                'image' => 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'title' => 'Khoảnh khắc trước giờ chiếu',
                'image' => 'https://images.unsplash.com/photo-1524985069026-dd778a71c7b4?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'title' => 'Màn ảnh lớn',
                'image' => 'https://images.unsplash.com/photo-1505686994434-e3cc5abf1330?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'title' => 'Đồ ăn rạp phim',
                'image' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=900&q=80',
            ],
        ]);
    @endphp

    <section class="cinema-home">
        <!-- CINEMA HERO BANNER BANNER NỔI BẬT RẠP PHIM CINEHOME -->
        <section class="booking-hero hero-slider netflix-hero-billboard" data-home-slider>
            @forelse ($heroMovies as $movie)
                @php
                    $detailUrlWithSchedule = route('user.movies.show', $movie->slug) . '#lich-chieu';
                    $bannerImage = $movie->banner 
                        ? (str_contains($movie->banner, '/') ? asset('storage/' . $movie->banner) : asset('storage/movies/' . $movie->banner))
                        : ($movie->poster 
                            ? (str_contains($movie->poster, '/') ? asset('storage/' . $movie->poster) : asset('storage/movies/' . $movie->poster))
                            : asset('assets/images/LOGO copy.png'));

                    $posterImage = $movie->poster 
                        ? (str_contains($movie->poster, '/') ? asset('storage/' . $movie->poster) : asset('storage/movies/' . $movie->poster))
                        : $bannerImage;

                    // Lấy suất chiếu hôm nay nếu có
                    $todayShowtimes = $movie->showtimes
                        ? $movie->showtimes->filter(function($st) {
                            return !empty($st->thoi_gian_chieu) && \Carbon\Carbon::parse($st->thoi_gian_chieu)->isToday();
                        })->sortBy('thoi_gian_chieu')->take(4)
                        : collect();
                @endphp

                <article class="hero-slide booking-hero-slide {{ $loop->first ? 'active' : '' }}"
                    data-slide-index="{{ $loop->index }}"
                    style="background-image: url('{{ $bannerImage }}');">
                    <div class="netflix-hero-overlay"></div>
                    <div class="container-fluid px-5 booking-hero-content hero-content">
                        <div class="booking-hero-copy hero-info netflix-hero-copy">
                            
                            {{-- Tiêu đề phim --}}
                            <h1 class="netflix-hero-title">
                                {{ $movie->ten_phim }}
                            </h1>

                            {{-- Dãy thẻ Meta chuẩn Rạp Phim CineHome --}}
                            <div class="netflix-hero-meta">
                                <span class="cinema-status-tag">
                                    <i class="fa-solid fa-fire"></i> Đang chiếu tại rạp
                                </span>
                                @if(!empty($movie->ngay_ra_rap))
                                    <span class="netflix-meta-year">{{ \Carbon\Carbon::parse($movie->ngay_ra_rap)->format('Y') }}</span>
                                @else
                                    <span class="netflix-meta-year">{{ now()->format('Y') }}</span>
                                @endif
                                <span class="netflix-age-badge">{{ $movie->gioi_han_tuoi ?: '16+' }}</span>
                                <span class="netflix-meta-duration">{{ $movie->thoi_luong ? $movie->thoi_luong . ' phút' : '120 phút' }}</span>
                                <span class="netflix-quality-tag">2D / 3D / IMAX</span>
                            </div>

                            {{-- KHUNG POSTER KHỔ DỌC VÀ NỘI DUNG --}}
                            <div class="hero-poster-and-info-flex">
                                {{-- Ảnh poster phim khổ dọc --}}
                                <div class="hero-vertical-poster-card">
                                    <img src="{{ $posterImage }}" alt="{{ $movie->ten_phim }}" class="hero-vertical-poster-img">
                                    <span class="hero-poster-age-badge">{{ $movie->gioi_han_tuoi ?: '16+' }}</span>
                                </div>

                                {{-- Khung chữ & Thao tác --}}
                                <div class="hero-info-text-side">
                                    {{-- Mô tả phim --}}
                                    <p class="netflix-hero-desc">
                                        {{ \Illuminate\Support\Str::limit($movie->mo_ta, 190) }}
                                    </p>

                                    {{-- Suất chiếu hôm nay (Nếu có) --}}
                                    @if($todayShowtimes->isNotEmpty())
                                        <div class="hero-today-showtimes">
                                            <span class="showtimes-label"><i class="fa-solid fa-clock"></i> Suất chiếu hôm nay:</span>
                                            <div class="showtimes-pills">
                                                @foreach($todayShowtimes as $st)
                                                    <a href="{{ $detailUrlWithSchedule }}" class="showtime-pill">
                                                        {{ \Carbon\Carbon::parse($st->thoi_gian_chieu)->format('H:i') }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Bộ nút bấm ĐẶT VÉ NGAY & CHI TIẾT PHIM --}}
                                    <div class="netflix-hero-actions">
                                        <a href="{{ $detailUrlWithSchedule }}" class="btn-booking-primary">
                                            <i class="fa-solid fa-ticket"></i> ĐẶT VÉ NGAY
                                        </a>

                                        <a href="{{ route('user.movies.show', $movie->slug) }}" class="btn-booking-secondary">
                                            <i class="fa-solid fa-circle-info"></i> CHI TIẾT PHIM
                                        </a>
                                    </div>

                                    {{-- Dòng thông tin phụ --}}
                                    <div class="netflix-hero-subinfo">
                                        @if(!empty($movie->dien_vien))
                                            <p><strong>Diễn viên:</strong> {{ \Illuminate\Support\Str::limit($movie->dien_vien, 75) }}</p>
                                        @endif
                                        <p><strong>Thể loại:</strong> {{ $movie->genres->pluck('ten_the_loai')->take(3)->join(', ') ?: 'Hành động, Phiêu lưu' }}</p>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </article>
            @empty
                <article class="hero-slide booking-hero-slide active empty-hero"
                    style="background-image: url('https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=1800&q=80');">
                    <div class="netflix-hero-overlay"></div>
                    <div class="container-fluid px-5 booking-hero-content hero-content">
                        <div class="booking-hero-copy hero-info netflix-hero-copy">
                            <h1 class="netflix-hero-title">CINEHOME CINEMA</h1>
                            <div class="netflix-hero-meta">
                                <span class="cinema-status-tag"><i class="fa-solid fa-film"></i> Rạp Phim Cao Cấp</span>
                                <span class="netflix-meta-year">{{ now()->format('Y') }}</span>
                                <span class="netflix-age-badge">16+</span>
                                <span class="netflix-quality-tag">IMAX 4K</span>
                            </div>
                            <p class="netflix-hero-desc">
                                Khám phá thế giới điện ảnh đẳng cấp tại CineHome. Đặt vé xem phim bom tấn, chọn ghế yêu thích và nhận vé điện tử nhanh chóng.
                            </p>
                            <div class="netflix-hero-actions">
                                <a href="{{ route('dat_ve.chon_phim') }}" class="btn-booking-primary">
                                    <i class="fa-solid fa-ticket"></i> ĐẶT VÉ NGAY
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

                <!-- 🌟 SECTION TOP 3 PHIM HOT CỦA THÁNG -->
                <section class="booking-section reveal-on-scroll" style="margin-top: 40px;">
                    <div class="booking-section-head">
                        <div>
                            <p style="color: #facc15; font-weight: 700; margin-bottom: 4px;">
                                <i class="fa-solid fa-fire" style="color: #ef4444; margin-right: 4px;"></i>
                                BẢNG XẾP HẠNG THÁNG {{ $tenThangHienTai }}
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
                            @endphp

                            <div class="hot-movie-card" onclick="window.location.href='{{ $detailUrlWithSchedule }}'">
                                <div class="hot-rank-badge" style="{{ $badgeStyle }}">
                                    <i class="fa-solid fa-crown"></i> TOP {{ $rank }}
                                </div>
                                <div class="hot-movie-poster">
                                    <img src="{{ asset('storage/movies/' . $movie->poster) }}" alt="{{ $movie->ten_phim }}">
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
                                <img src="{{ asset('storage/movies/' . $movie->poster) }}"
                                    alt="{{ $movie->ten_phim }}">
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

                <section class="booking-benefits reveal-on-scroll">
                    <div class="booking-benefit-card">
                        <span>
                            <i class="fa-solid fa-couch"></i>
                        </span>
                        <div>
                            <h3>Chọn ghế trực quan</h3>
                            <p>Xem sơ đồ ghế rõ ràng, chọn đúng vị trí yêu thích trước khi thanh toán.</p>
                        </div>
                    </div>

                    <div class="booking-benefit-card">
                        <span>
                            <i class="fa-solid fa-shield-halved"></i>
                        </span>
                        <div>
                            <h3>Giữ ghế tạm thời</h3>
                            <p>Ghế được khóa trong quá trình đặt vé để bạn thao tác yên tâm hơn.</p>
                        </div>
                    </div>

                    <div class="booking-benefit-card">
                        <span>
                            <i class="fa-solid fa-mobile-screen-button"></i>
                        </span>
                        <div>
                            <h3>Vé điện tử tiện lợi</h3>
                            <p>Nhận mã vé sau thanh toán và xuất trình nhanh khi đến rạp.</p>
                        </div>
                    </div>
                </section>

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
                            @endphp

                            <article class="booking-movie-card">
                                <a href="{{ $detailUrlWithSchedule }}" class="booking-movie-poster">
                                    <img src="{{ asset('storage/movies/' . $movie->poster) }}"
                                        alt="{{ $movie->ten_phim }}">
                                    <span class="movie-age-badge">{{ $movie->gioi_han_tuoi }}</span>
                                    <span class="movie-play-overlay">
                                        <i class="fa-solid fa-play"></i>
                                    </span>
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
                            <article class="booking-movie-card compact">
                                <a href="{{ route('user.movies.show', $movie->slug) }}" class="booking-movie-poster">
                                    <img src="{{ asset('storage/movies/' . $movie->poster) }}"
                                        alt="{{ $movie->ten_phim }}">
                                    <span class="movie-age-badge">{{ $movie->gioi_han_tuoi }}</span>
                                    <span class="movie-play-overlay">
                                        <i class="fa-solid fa-play"></i>
                                    </span>
                                </a>

                                <div class="booking-movie-body">
                                    <h3>{{ $movie->ten_phim }}</h3>
                                    <p>
                                        <i class="fa-solid fa-earth-asia"></i>
                                        {{ $movie->country?->ten_quoc_gia ?? 'Đang cập nhật' }}
                                    </p>
                                    <div class="booking-card-actions">
                                        <a href="{{ route('user.movies.show', $movie->slug) }}" class="card-book-btn">
                                            Quan tâm
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="booking-empty-state">
                                <i class="fa-regular fa-calendar"></i>
                                Chưa có phim sắp chiếu.
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="booking-promo-strip reveal-on-scroll">
                    <div class="promo-copy">
                        <span>
                            <i class="fa-solid fa-crown"></i>
                            Thành viên CineHome
                        </span>
                        <h2>Đặt vé hôm nay, tích điểm cho lần xem tiếp theo</h2>
                        <p>Nhận ưu đãi voucher, quản lý vé điện tử và theo dõi lịch sử đặt vé trong tài khoản của bạn.</p>
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
@endsection