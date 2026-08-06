@extends('layouts.admin')

@section('title', 'Bán vé tại quầy')
@section('page-title', 'Bán vé tại quầy')

@section('content')
    @php
        $showtimeCollection = collect($showtimes);
        $dates = $showtimeCollection
            ->map(fn ($item) => $item->thoi_gian_chieu?->format('Y-m-d'))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $selectedDate = request('ngay_chieu') ?: $dates->first();
        $keyword = trim((string) request('q', ''));
        $keywordLower = \Illuminate\Support\Str::lower($keyword);

        $dayShowtimes = $showtimeCollection
            ->filter(fn ($item) => $selectedDate && $item->thoi_gian_chieu?->format('Y-m-d') === $selectedDate)
            ->filter(function ($item) use ($keyword, $keywordLower) {
                if ($keyword === '') {
                    return true;
                }

                $haystack = \Illuminate\Support\Str::lower(implode(' ', [
                    $item->phim?->ten_phim,
                    $item->rapChieuPhim?->ten_rap,
                    $item->phongChieu?->ten_phong,
                    $item->trang_thai,
                ]));

                return str_contains($haystack, $keywordLower);
            })
            ->sortBy('thoi_gian_chieu')
            ->values();

        $movieGroups = $dayShowtimes->groupBy('phim_id');
        $selectedCarbon = $selectedDate ? \Carbon\Carbon::parse($selectedDate) : null;
        $nextShowtime = $dayShowtimes->first();
        $roomCount = $dayShowtimes->pluck('phong_chieu_id')->filter()->unique()->count();
        $soldTickets = $dayShowtimes->sum(fn ($item) => (int) ($item->sold_tickets_count ?? 0));

        $statusLabels = [
            'sap_ra_mat' => 'Sắp ra mắt',
            'sap_chieu' => 'Sắp chiếu',
            'dang_chieu' => 'Đang chiếu',
            'da_chieu' => 'Đã chiếu',
            'huy' => 'Đã hủy',
        ];
    @endphp

    <div class="counter-sale-page">
        @include('admin.partials.flash')

        <section class="counter-sale-hero">
            <div class="counter-sale-hero-copy">
                <span class="counter-sale-kicker">
                    <i class="fa-solid fa-ticket"></i>
                    Quầy vé CineHome
                </span>
                <h1>Bán vé tại quầy</h1>
                <p>
                    Chọn ngày, tìm phim hoặc phòng chiếu, sau đó vào suất chiếu để chọn ghế, đồ ăn và thanh toán cho khách.
                    Giao diện này ưu tiên tốc độ thao tác trong ca vận hành.
                </p>

                <div class="counter-sale-hero-meta">
                    <span><i class="fa-solid fa-calendar-day"></i> {{ $selectedCarbon?->format('d/m/Y') ?? 'Chưa có lịch' }}</span>
                    <span><i class="fa-solid fa-clock"></i> Suất gần nhất: {{ $nextShowtime?->thoi_gian_chieu?->format('H:i') ?? '--:--' }}</span>
                    <span><i class="fa-solid fa-chair"></i> {{ number_format($soldTickets) }} vé đã bán</span>
                </div>
            </div>

            <div class="counter-sale-hero-actions">
                <a href="{{ route('staff.lich-su-ve.index') }}" class="movie-action-btn is-soft">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Lịch sử vé
                </a>
                <a href="{{ route('staff.ban-ve.index') }}" class="movie-action-btn is-ghost">
                    <i class="fa-solid fa-rotate-left"></i>
                    Làm mới
                </a>
            </div>
        </section>

        <section class="counter-sale-stats" aria-label="Tổng quan bán vé tại quầy">
            <article>
                <span class="counter-sale-stat-icon is-showtime"><i class="fa-solid fa-calendar-check"></i></span>
                <div>
                    <small>Suất trong ngày</small>
                    <strong>{{ number_format($dayShowtimes->count()) }}</strong>
                </div>
            </article>
            <article>
                <span class="counter-sale-stat-icon is-movie"><i class="fa-solid fa-film"></i></span>
                <div>
                    <small>Phim đang bán</small>
                    <strong>{{ number_format($movieGroups->count()) }}</strong>
                </div>
            </article>
            <article>
                <span class="counter-sale-stat-icon is-room"><i class="fa-solid fa-door-open"></i></span>
                <div>
                    <small>Phòng chiếu</small>
                    <strong>{{ number_format($roomCount) }}</strong>
                </div>
            </article>
            <article>
                <span class="counter-sale-stat-icon is-ticket"><i class="fa-solid fa-ticket-simple"></i></span>
                <div>
                    <small>Vé đã bán</small>
                    <strong>{{ number_format($soldTickets) }}</strong>
                </div>
            </article>
        </section>

        <section class="counter-sale-filter">
            <div>
                <span class="counter-sale-kicker">
                    <i class="fa-solid fa-calendar-days"></i>
                    Chọn ngày xem
                </span>
                <div class="counter-date-list">
                    @forelse ($dates as $date)
                        @php
                            $carbonDate = \Carbon\Carbon::parse($date);
                            $dateShowtimeCount = $showtimeCollection
                                ->filter(fn ($item) => $item->thoi_gian_chieu?->format('Y-m-d') === $date)
                                ->count();
                        @endphp

                        <a
                            href="{{ route('staff.ban-ve.index', array_filter(['ngay_chieu' => $date, 'q' => $keyword ?: null])) }}"
                            class="counter-date-item {{ $selectedDate === $date ? 'is-active' : '' }}"
                        >
                            <span>{{ $carbonDate->translatedFormat('D') }}</span>
                            <strong>{{ $carbonDate->format('d') }}</strong>
                            <small>{{ $carbonDate->format('m/Y') }}</small>
                            <em>{{ $dateShowtimeCount }} suất</em>
                        </a>
                    @empty
                        <div class="counter-date-empty">Chưa có ngày chiếu sắp tới.</div>
                    @endforelse
                </div>
            </div>

            <form method="GET" action="{{ route('staff.ban-ve.index') }}" class="counter-sale-search">
                <input type="hidden" name="ngay_chieu" value="{{ $selectedDate }}">
                <label>
                    <span>Tìm nhanh</span>
                    <div>
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="q" value="{{ $keyword }}" placeholder="Tên phim, rạp hoặc phòng chiếu...">
                    </div>
                </label>
                <button type="submit" class="movie-action-btn is-primary">
                    <i class="fa-solid fa-filter"></i>
                    Lọc
                </button>
            </form>
        </section>

        <section class="counter-movie-section">
            <div class="counter-section-head">
                <div>
                    <span class="counter-sale-kicker">
                        <i class="fa-solid fa-clapperboard"></i>
                        Suất chiếu khả dụng
                    </span>
                    <h2>{{ $selectedCarbon ? 'Lịch ngày ' . $selectedCarbon->format('d/m/Y') : 'Chưa có lịch chiếu' }}</h2>
                </div>
                <span>{{ number_format($dayShowtimes->count()) }} suất</span>
            </div>

            <div class="counter-movie-list">
                @forelse ($movieGroups as $movieId => $movieShowtimes)
                    @php
                        $movie = $movieShowtimes->first()->phim;
                        $posterRaw = $movie?->poster ?? '';

                        if (empty($posterRaw)) {
                            $poster = 'https://placehold.co/300x450/1e293b/94a3b8?text=No+Poster';
                        } elseif (\Illuminate\Support\Str::startsWith($posterRaw, ['http://', 'https://'])) {
                            $poster = $posterRaw;
                        } else {
                            $cleanPoster = str_replace(['storage/movies/', 'storage/', 'movies/movies/'], '', ltrim($posterRaw, '/'));
                            $cleanPoster = ltrim($cleanPoster, '/');
                            
                            if (!\Illuminate\Support\Str::startsWith($cleanPoster, 'movies/')) {
                                $cleanPoster = 'movies/' . $cleanPoster;
                            }

                            if (file_exists(public_path('storage/' . $cleanPoster))) {
                                $poster = asset('storage/' . $cleanPoster);
                            } else {
                                $poster = 'https://placehold.co/300x450/1e293b/94a3b8?text=No+Poster';
                            }
                        }
                    @endphp

                    <article class="counter-movie-card">
                        <div class="counter-movie-poster">
                            <img src="{{ $poster }}" alt="{{ $movie?->ten_phim ?? 'Poster phim' }}" onerror="this.onerror=null; this.src='https://placehold.co/300x450/1e293b/94a3b8?text=No+Poster';">
                        </div>

                        <div class="counter-movie-body">
                            <div class="counter-movie-info">
                                <div>
                                    <h3>{{ $movie?->ten_phim ?? 'Phim chưa cập nhật' }}</h3>
                                    <p>
                                        <i class="fa-solid fa-location-dot"></i>
                                        {{ $movieShowtimes->first()->rapChieuPhim?->ten_rap ?? 'CineHome' }}
                                    </p>
                                </div>

                                <div class="counter-movie-tags">
                                    @if ($movie?->do_tuoi)
                                        <span class="is-age">{{ $movie->do_tuoi }}</span>
                                    @endif
                                    <span>2D</span>
                                    <span>{{ $movieShowtimes->count() }} suất</span>
                                </div>
                            </div>

                            <div class="counter-showtime-grid">
                                @foreach ($movieShowtimes as $showtime)
                                    @php
                                        $statusClass = match ($showtime->trang_thai) {
                                            'dang_chieu' => 'is-live',
                                            'huy' => 'is-cancelled',
                                            default => 'is-upcoming',
                                        };
                                    @endphp

                                    <a href="{{ route('staff.ban-ve.show', $showtime->id) }}" class="counter-showtime-card">
                                        <strong>{{ $showtime->thoi_gian_chieu?->format('H:i') ?? '--:--' }}</strong>
                                        <span>
                                            <i class="fa-solid fa-door-open"></i>
                                            {{ $showtime->phongChieu?->ten_phong ?? 'Phòng chiếu' }}
                                        </span>
                                        <span>
                                            <i class="fa-solid fa-ticket-simple"></i>
                                            {{ number_format((int) ($showtime->sold_tickets_count ?? 0)) }} vé
                                        </span>
                                        <em class="{{ $statusClass }}">
                                            {{ $statusLabels[$showtime->trang_thai] ?? 'Sắp chiếu' }}
                                        </em>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="counter-sale-empty">
                        <span><i class="fa-solid fa-calendar-xmark"></i></span>
                        <h3>Không có suất chiếu phù hợp</h3>
                        <p>Thử chọn ngày khác hoặc bỏ từ khóa tìm kiếm để xem toàn bộ lịch bán vé.</p>
                        <a href="{{ route('staff.ban-ve.index') }}" class="movie-action-btn is-primary">
                            <i class="fa-solid fa-rotate-left"></i>
                            Xem tất cả
                        </a>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        localStorage.removeItem("staff_food_cart");
    </script>
@endpush