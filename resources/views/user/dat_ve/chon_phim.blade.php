@extends('layouts.user')

@section('title', 'Chọn phim và suất chiếu')

@section('content')
    <div class="booking-flow-page booking-movie-select-page" lang="vi" spellcheck="false">
        <section class="booking-flow-hero">
            <div class="booking-flow-hero-copy">
                <span class="booking-eyebrow">
                    <i class="fa-solid fa-ticket"></i>
                    Đặt vé CineHome
                </span>
                <h1>Chọn phim, chọn suất, giữ ghế thật nhanh.</h1>
                <p>
                    Lịch chiếu tại <strong>{{ $rap->ten_rap }}</strong>. Chọn ngày bên dưới để xem các suất đang mở bán
                    và tiếp tục đặt vé đúng giờ bạn muốn.
                </p>
            </div>

            <div class="booking-stepper" aria-label="Tiến trình đặt vé">
                <div class="booking-step is-active">
                    <span>1</span>
                    <strong>Chọn phim</strong>
                </div>
                <div class="booking-step">
                    <span>2</span>
                    <strong>Chọn ghế</strong>
                </div>
                <div class="booking-step">
                    <span>3</span>
                    <strong>Đồ ăn</strong>
                </div>
                <div class="booking-step">
                    <span>4</span>
                    <strong>Thanh toán</strong>
                </div>
            </div>
        </section>

        @php
            $activeDateOption = collect($dateOptions)->firstWhere('active');
            $activeDateLabel = $activeDateOption['label'] ?? $selectedDate->format('d/m/Y');
            $weekdayLabels = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
            $defaultPoster = 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=800&auto=format&fit=crop';
        @endphp

        <section class="booking-date-panel" aria-label="Chọn ngày chiếu">
            <div class="booking-date-heading">
                <div>
                    <span>Lịch chiếu</span>
                    <h2>{{ $activeDateLabel }} • {{ $selectedDate->format('d/m/Y') }}</h2>
                </div>
                <p>{{ $rap->dia_chi }}</p>
            </div>

            <form id="dateForm" action="{{ request()->url() }}" method="GET" class="booking-date-form">
                <input type="hidden" name="ngay_chieu" id="selectedDateInput" value="{{ $selectedDate->toDateString() }}">

                <button type="button" id="prevDate" class="booking-date-nav" aria-label="Ngày trước">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>

                <div id="dateList" class="booking-date-track">
                    @foreach ($dateOptions as $dateOption)
                        @php
                            $dateCarbon = \Carbon\Carbon::parse($dateOption['date']);
                        @endphp
                        <button type="button" data-date="{{ $dateOption['date'] }}"
                            class="booking-date-chip {{ $dateOption['active'] ? 'is-active' : '' }}"
                            aria-pressed="{{ $dateOption['active'] ? 'true' : 'false' }}">
                            <span>{{ $dateOption['label'] }}</span>
                            <strong>{{ $dateCarbon->format('d') }}</strong>
                            <small>{{ $weekdayLabels[$dateCarbon->dayOfWeek] ?? $dateCarbon->format('D') }}</small>
                        </button>
                    @endforeach
                </div>

                <button type="button" id="nextDate" class="booking-date-nav" aria-label="Ngày sau">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </form>
        </section>

        <section class="booking-showtime-section">
            <div class="booking-section-head">
                <div>
                    <span>Suất chiếu còn vé</span>
                    <h2>Chọn giờ bắt đầu</h2>
                </div>
                <a href="{{ route('user.showtimes.index', ['ngay_chieu' => $selectedDate->toDateString()]) }}">
                    Xem lịch chiếu đầy đủ
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <div class="booking-showtime-list">
                @forelse($suatChieuTheoPhim as $suatChieus)
                    @php
                        $phim = $suatChieus->first()->phim;
                        $showtimes = $suatChieus;

                        // Tối ưu hóa xử lý đường dẫn ảnh poster
                        $rawPoster = trim($phim->poster ?? '');

                        if (empty($rawPoster)) {
                            $posterUrl = $defaultPoster;
                        } elseif (\Illuminate\Support\Str::startsWith($rawPoster, ['http://', 'https://'])) {
                            $posterUrl = $rawPoster;
                        } elseif (\Illuminate\Support\Str::startsWith($rawPoster, ['storage/', 'uploads/'])) {
                            $posterUrl = asset($rawPoster);
                        } else {
                            $path = \Illuminate\Support\Str::startsWith($rawPoster, 'movies/') 
                                ? $rawPoster 
                                : 'movies/' . ltrim($rawPoster, '/');
                            $posterUrl = asset('storage/' . $path);
                        }
                    @endphp

                    <article class="booking-showtime-card">
                        <a href="{{ route('user.movies.show', $phim->slug) }}" class="booking-showtime-poster"
                            aria-label="Xem chi tiết {{ $phim->ten_phim }}">
                            <img src="{{ $posterUrl }}" 
                                 alt="{{ $phim->ten_phim }}"
                                 onerror="this.onerror=null; this.src='{{ $defaultPoster }}';">
                        </a>

                        <div class="booking-showtime-body">
                            <div class="booking-showtime-top">
                                <div>
                                    <div class="booking-movie-tags">
                                        @if (!empty($phim->gioi_han_tuoi))
                                            <span class="age">{{ $phim->gioi_han_tuoi }}</span>
                                        @endif
                                        <span>2D</span>
                                        @if (!empty($phim->thoi_luong))
                                            <span>{{ $phim->thoi_luong }} phút</span>
                                        @endif
                                    </div>
                                    <h3>{{ $phim->ten_phim }}</h3>
                                </div>
                                <a href="{{ route('user.movies.show', $phim->slug) }}" class="booking-detail-link">
                                    Chi tiết
                                </a>
                            </div>

                            @if ($phim->genres->isNotEmpty())
                                <p class="booking-movie-genres">
                                    {{ $phim->genres->pluck('ten_the_loai')->join(' • ') }}
                                </p>
                            @endif

                            @if (!empty($phim->mo_ta))
                                <p class="booking-movie-desc">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($phim->mo_ta), 150) }}
                                </p>
                            @endif

                            <div class="booking-time-grid" aria-label="Danh sách suất chiếu của {{ $phim->ten_phim }}">
                                @foreach ($showtimes as $suat)
                                    @if ($suat->ghe_trong > 0)
                                        <a href="{{ route('dat_ve.chon_ghe', ['movie' => $suat->id]) }}"
                                            class="booking-time-chip">
                                            <strong>{{ $suat->thoi_gian_chieu->format('H:i') }}</strong>
                                            <span>{{ $suat->ghe_trong }}/{{ $suat->tong_ghe }} ghế</span>
                                        </a>
                                    @else
                                        <span class="booking-time-chip is-disabled">
                                            <strong>{{ $suat->thoi_gian_chieu->format('H:i') }}</strong>
                                            <span>Hết vé</span>
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="booking-flow-empty">
                        <i class="fa-solid fa-film"></i>
                        <h2>Chưa có suất chiếu</h2>
                        <p>Hiện chưa có suất chiếu nào cho ngày này. Hãy chọn một ngày khác để tiếp tục đặt vé.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dateForm = document.getElementById('dateForm');
            const dateInput = document.getElementById('selectedDateInput');
            const dateButtons = Array.from(document.querySelectorAll('[data-date]'));
            const prevDate = document.getElementById('prevDate');
            const nextDate = document.getElementById('nextDate');
            let isSubmitting = false;

            if (!dateForm || !dateInput || dateButtons.length === 0) return;

            let activeIndex = dateButtons.findIndex((button) => button.dataset.date === dateInput.value);
            if (activeIndex < 0) activeIndex = 0;

            function submitOnce() {
                if (isSubmitting) return;
                isSubmitting = true;
                window.setTimeout(() => dateForm.submit(), 70);
            }

            function setActiveIndex(index, shouldSubmit) {
                if (index < 0 || index >= dateButtons.length) return;

                activeIndex = index;
                dateInput.value = dateButtons[activeIndex].dataset.date;

                dateButtons.forEach((button, currentIndex) => {
                    const active = currentIndex === activeIndex;
                    button.classList.toggle('is-active', active);
                    button.setAttribute('aria-pressed', active ? 'true' : 'false');
                });

                dateButtons[activeIndex].scrollIntoView({
                    inline: 'center',
                    behavior: 'smooth',
                    block: 'nearest'
                });

                if (shouldSubmit) submitOnce();
            }

            dateButtons.forEach((button, index) => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    setActiveIndex(index, true);
                });
            });

            prevDate?.addEventListener('click', function(event) {
                event.preventDefault();
                setActiveIndex(Math.max(0, activeIndex - 1), false);
            });

            nextDate?.addEventListener('click', function(event) {
                event.preventDefault();
                setActiveIndex(Math.min(dateButtons.length - 1, activeIndex + 1), false);
            });
        });
    </script>
@endsection