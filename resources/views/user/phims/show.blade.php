@extends('layouts.user')

@section('title', $movie->ten_phim . ' - CineHome')

@section('content')
    @php
        // Đọc trạng thái Tab từ URL (mặc định overview)
        $activeTab = request('tab', 'overview');

        $posterUrl = function ($movie) {
            $p = $movie->poster ?? '';
            if (empty($p)) {
                return 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=700&auto=format&fit=crop';
            }

            $p = ltrim($p, '/');

            if (strpos($p, 'http://') === 0 || strpos($p, 'https://') === 0) {
                return $p;
            }

            return asset('storage/movies/' . basename($p));
        };

        $poster = $posterUrl($movie);
        $genres = $movie->genres->pluck('ten_the_loai')->filter()->values();
        $actors = collect(explode(',', (string) $movie->dien_vien))
            ->map(fn($actor) => trim($actor))
            ->filter()
            ->values();
        $nextShowtime = $showtimes->sortBy('thoi_gian_chieu')->first();
        $showtimeGroups = $showtimes->groupBy(
            fn($showtime) => \Carbon\Carbon::parse($showtime->thoi_gian_chieu)->format('Y-m-d'),
        );
        $releaseDate = $movie->ngay_khoi_chieu
            ? \Carbon\Carbon::parse($movie->ngay_khoi_chieu)->format('d/m/Y')
            : 'Đang cập nhật';
        $canBook = in_array(
            $status,
            [\App\Models\SuatChieu::TRANG_THAI_DANG_CHIEU, \App\Models\SuatChieu::TRANG_THAI_SAP_CHIEU],
            true,
        ) && $showtimes->isNotEmpty();
        $statusInfo = match ($status) {
            \App\Models\SuatChieu::TRANG_THAI_DANG_CHIEU => [
                'label' => 'Đang chiếu',
                'class' => 'is-live',
                'icon' => 'fa-circle-play',
            ],
            \App\Models\SuatChieu::TRANG_THAI_SAP_CHIEU => [
                'label' => 'Sắp chiếu',
                'class' => 'is-soon',
                'icon' => 'fa-calendar-check',
            ],
            \App\Models\SuatChieu::TRANG_THAI_SAP_RA_MAT => [
                'label' => 'Sắp ra mắt',
                'class' => 'is-later',
                'icon' => 'fa-star',
            ],
            default => [
                'label' => 'Đã chiếu',
                'class' => 'is-ended',
                'icon' => 'fa-clock-rotate-left',
            ],
        };
        $backUrl = url()->previous() !== url()->current() ? url()->previous() : route('user.phims.index');
        $detailShots = collect([
            ['image' => $poster, 'label' => 'Poster chính'],
            [
                'image' => 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=900&q=80',
                'label' => 'Không gian rạp',
            ],
            [
                'image' => 'https://images.unsplash.com/photo-1524985069026-dd778a71c7b4?auto=format&fit=crop&w=900&q=80',
                'label' => 'Trải nghiệm điện ảnh',
            ],
        ]);
    @endphp

    <section class="movie-detail-page">
        <section class="movie-detail-hero">
            <div class="movie-detail-backdrop" style="background-image: url('{{ $poster }}');"></div>

            <div class="container-fluid px-5 movie-detail-hero-inner">
                <a href="{{ $backUrl }}" class="detail-back-link">
                    <i class="fa-solid fa-arrow-left"></i>
                    Quay lại
                </a>

                <div class="movie-detail-layout">
                    <aside class="detail-poster-card reveal-on-scroll">
                        <div class="detail-poster-frame">
                            <img src="{{ $poster }}" alt="{{ $movie->ten_phim }}">
                            <span class="detail-age-badge">{{ $movie->gioi_han_tuoi }}</span>
                        </div>

                        <div class="detail-poster-actions">
                            @if ($movie->trailer)
                                <a href="{{ $movie->trailer }}" target="_blank" rel="noopener noreferrer"
                                    class="detail-action-btn detail-action-btn--ghost">
                                    <i class="fa-solid fa-play"></i>
                                    Xem trailer
                                </a>
                            @endif

                            @if ($canBook)
                                <a href="#lich-chieu" id="btnHeroBooking"
                                    class="booking-link detail-action-btn detail-action-btn--primary">
                                    <i class="fa-solid fa-ticket"></i>
                                    Đặt vé ngay
                                </a>
                            @else
                                <button type="button" class="detail-action-btn detail-action-btn--disabled" disabled>
                                    <i class="fa-solid fa-ban"></i>
                                    Chưa mở đặt vé
                                </button>
                            @endif
                        </div>
                    </aside>

                    <div class="detail-hero-copy reveal-on-scroll">
                        <span class="detail-status {{ $statusInfo['class'] }}">
                            <i class="fa-solid {{ $statusInfo['icon'] }}"></i>
                            {{ $statusInfo['label'] }}
                        </span>

                        <h1>{{ $movie->ten_phim }}</h1>

                        <p class="detail-hero-desc">
                            {{ \Illuminate\Support\Str::limit($movie->mo_ta, 230) }}
                        </p>

                        <div class="detail-meta-grid">
                            <span>
                                <i class="fa-solid fa-clock"></i>
                                {{ $movie->thoi_luong }} phút
                            </span>
                            <span>
                                <i class="fa-solid fa-earth-asia"></i>
                                {{ optional($movie->country)->ten_quoc_gia ?? 'Đang cập nhật' }}
                            </span>
                            <span>
                                <i class="fa-solid fa-language"></i>
                                {{ $movie->ngon_ngu }}
                            </span>
                            <span>
                                <i class="fa-solid fa-tags"></i>
                                {{ $genres->join(', ') ?: 'Điện ảnh' }}
                            </span>
                        </div>

                        @if ($nextShowtime)
                            <div class="detail-next-showtime">
                                <span>
                                    <i class="fa-solid fa-calendar-day"></i>
                                    Suất gần nhất
                                </span>
                                <strong>{{ \Carbon\Carbon::parse($nextShowtime->thoi_gian_chieu)->format('H:i d/m/Y') }}</strong>
                                <small>
                                    {{ $nextShowtime->rapChieuPhim?->ten_rap ?? 'CineHome' }}
                                    · {{ $nextShowtime->phongChieu?->ten_phong ?? 'Phòng chiếu' }}
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <main class="movie-detail-main">
            <div class="container-fluid px-5">
                <section class="detail-tabs-shell reveal-on-scroll" data-detail-tabs id="lich-chieu">
                    <div class="detail-tabs">
                        <button type="button" class="{{ $activeTab === 'overview' ? 'active' : '' }}" data-detail-tab="overview">
                            <i class="fa-solid fa-film"></i>
                            Tổng quan
                        </button>
                        <button type="button" class="{{ $activeTab === 'showtimes' ? 'active' : '' }}" data-detail-tab="showtimes">
                            <i class="fa-solid fa-calendar-days"></i>
                            Lịch chiếu
                        </button>
                        <button type="button" class="{{ $activeTab === 'cast' ? 'active' : '' }}" data-detail-tab="cast">
                            <i class="fa-solid fa-users"></i>
                            Diễn viên
                        </button>
                    </div>

                    {{-- TAB PANE TỔNG QUAN --}}
                    <div class="detail-tab-panel {{ $activeTab === 'overview' ? 'active' : '' }}" data-detail-panel="overview">
                        <div class="detail-overview-grid">
                            <div class="detail-story-card">
                                <span class="detail-section-kicker">Nội dung phim</span>
                                <h2>{{ $movie->ten_phim }}</h2>
                                <p>{{ $movie->mo_ta }}</p>

                                <div class="detail-fact-grid">
                                    <div>
                                        <small>Đạo diễn</small>
                                        <strong>{{ $movie->dao_dien }}</strong>
                                    </div>
                                    <div>
                                        <small>Khởi chiếu</small>
                                        <strong>{{ $releaseDate }}</strong>
                                    </div>
                                    <div>
                                        <small>Giới hạn tuổi</small>
                                        <strong>{{ $movie->gioi_han_tuoi }}</strong>
                                    </div>
                                    <div>
                                        <small>Giá vé từ</small>
                                        <strong>
                                            {{ $nextShowtime ? number_format((float) $nextShowtime->gia_ve, 0, ',', '.') . 'đ' : 'Đang cập nhật' }}
                                        </strong>
                                    </div>
                                </div>
                            </div>

                            <div class="detail-gallery">
                                @foreach ($detailShots as $shot)
                                    <figure class="{{ $loop->first ? 'large' : '' }}">
                                        <img src="{{ $shot['image'] }}" alt="{{ $shot['label'] }}">
                                        <figcaption>{{ $shot['label'] }}</figcaption>
                                    </figure>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- TAB PANE LỊCH CHIẾU --}}
                    <div class="detail-tab-panel {{ $activeTab === 'showtimes' ? 'active' : '' }}" data-detail-panel="showtimes">
                        <div class="detail-panel-head">
                            <span class="detail-section-kicker">Chọn suất chiếu</span>
                            <h2>Lịch chiếu sắp tới</h2>
                        </div>

                        @forelse ($showtimeGroups as $date => $items)
                            <section class="detail-showtime-day">
                                <div class="detail-showtime-date">
                                    <strong>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</strong>
                                    <span>{{ $items->count() }} suất chiếu</span>
                                </div>

                                <div class="detail-showtime-list">
                                    @foreach ($items as $showtime)
                                        <a href="{{ route('dat_ve.chon_ghe', $showtime->id) }}"
                                            class="booking-link detail-showtime-card">
                                            <strong>{{ \Carbon\Carbon::parse($showtime->thoi_gian_chieu)->format('H:i') }}</strong>
                                            <span>{{ $showtime->rapChieuPhim?->ten_rap ?? 'CineHome' }}</span>
                                            <small>
                                                {{ $showtime->phongChieu?->ten_phong ?? 'Phòng chiếu' }}
                                                · {{ number_format((float) $showtime->gia_ve, 0, ',', '.') }}đ
                                            </small>
                                            <i class="fa-solid fa-arrow-right"></i>
                                        </a>
                                    @endforeach
                                </div>
                            </section>
                        @empty
                            <div class="detail-empty-state">
                                <i class="fa-regular fa-calendar"></i>
                                <strong>Lịch chiếu đang được cập nhật</strong>
                                <span>Quay lại sau để chọn suất chiếu phù hợp nhất.</span>
                            </div>
                        @endforelse
                    </div>

                    {{-- TAB PANE DIỄN VIÊN --}}
                    <div class="detail-tab-panel {{ $activeTab === 'cast' ? 'active' : '' }}" data-detail-panel="cast">
                        <div class="detail-cast-layout">
                            <div class="detail-director-card">
                                <span class="detail-section-kicker">Đạo diễn</span>
                                <div class="detail-person-avatar">
                                    {{ mb_strtoupper(mb_substr($movie->dao_dien, 0, 1)) }}
                                </div>
                                <h2>{{ $movie->dao_dien }}</h2>
                                <p>Người dẫn dắt phong cách hình ảnh và nhịp kể của bộ phim.</p>
                            </div>

                            <div class="detail-cast-grid">
                                @forelse ($actors as $actor)
                                    <article class="detail-cast-card">
                                        <span>{{ mb_strtoupper(mb_substr($actor, 0, 1)) }}</span>
                                        <div>
                                            <strong>{{ $actor }}</strong>
                                            <small>Diễn viên</small>
                                        </div>
                                    </article>
                                @empty
                                    <div class="detail-empty-state">
                                        <i class="fa-solid fa-user"></i>
                                        <strong>Diễn viên đang cập nhật</strong>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </section>

                @if (isset($relatedMovies) && $relatedMovies->count())
                    <section class="booking-section detail-related-section reveal-on-scroll">
                        <div class="booking-section-head">
                            <div>
                                <p>Gợi ý tiếp theo</p>
                                <h2>Phim liên quan</h2>
                            </div>
                            <a href="{{ route('user.phims.index') }}" class="detail-related-link">
                                Xem tất cả
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>

                        @include('partials.movie-section', [
                            'movies' => $relatedMovies,
                        ])
                    </section>
                @endif
            </div>
        </main>
    </section>

    {{-- SCRIPT TỰ ĐỘNG ÉP BẬT TAB LỊCH CHIẾU VÀ CUỘN MƯỢT --}}
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const tabShell = document.querySelector('[data-detail-tabs]');
            if (!tabShell) return;

            const tabButtons = tabShell.querySelectorAll('[data-detail-tab]');
            const tabPanels = tabShell.querySelectorAll('[data-detail-panel]');

            function switchTab(targetTab) {
                tabButtons.forEach(btn => {
                    if (btn.getAttribute('data-detail-tab') === targetTab) {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                });

                tabPanels.forEach(panel => {
                    if (panel.getAttribute('data-detail-panel') === targetTab) {
                        panel.classList.add('active');
                    } else {
                        panel.classList.remove('active');
                    }
                });
            }

            // Gán sự kiện click cho các nút Tab
            tabButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                    const tab = this.getAttribute('data-detail-tab');
                    switchTab(tab);
                });
            });

            function forceActivateShowtimes() {
                const urlParams = new URLSearchParams(window.location.search);
                const isShowtimes = urlParams.get('tab') === 'showtimes' || window.location.hash === '#lich-chieu';

                if (isShowtimes) {
                    // Chuyển tab sang Lịch chiếu
                    switchTab('showtimes');

                    // Tự động cuộn xuống đúng vị trí
                    const targetElement = document.getElementById('lich-chieu');
                    if (targetElement) {
                        targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            }

            // Chạy ngay khi DOM sẵn sàng
            forceActivateShowtimes();

            // Chạy trễ 150ms & 400ms để ghi đè JavaScript mặc định của Theme
            setTimeout(forceActivateShowtimes, 150);
            setTimeout(forceActivateShowtimes, 400);

            // Nút "Đặt vé ngay" trên Banner Hero
            const heroBookBtn = document.getElementById('btnHeroBooking');
            if (heroBookBtn) {
                heroBookBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    switchTab('showtimes');
                    const targetElement = document.getElementById('lich-chieu');
                    if (targetElement) {
                        targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            }
        });
    </script>
@endsection