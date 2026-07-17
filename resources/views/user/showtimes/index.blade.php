@extends('layouts.user')

@section('title', 'Lịch chiếu - CineHome')

@section('content')
    @php
        $showtimeGroups = $suatChieus->groupBy(
            fn($showtime) => \Carbon\Carbon::parse($showtime->thoi_gian_chieu)->format('Y-m-d'),
        );
        $activeFilters = collect([
            request('phim_id'),
            request('rap_chieu_phim_id'),
            request('ngay_chieu'),
            request('trang_thai'),
        ])->filter()->count();
        $totalMovies = $suatChieus->pluck('phim_id')->unique()->count();
        $totalCinemas = $suatChieus->pluck('rap_chieu_phim_id')->unique()->count();
        $firstShowtime = $suatChieus->first();
    @endphp

    <section class="schedule-page">
        <section class="schedule-hero">
            <div class="container-fluid px-5">
                <div class="schedule-hero-grid">
                    <div class="schedule-hero-copy reveal-on-scroll">
                        <span class="schedule-kicker">
                            <i class="fa-solid fa-calendar-days"></i>
                            CineHome Showtime
                        </span>
                        <h1>Lịch chiếu phim</h1>
                        <p>Chọn nhanh phim, rạp và ngày chiếu để tìm suất phù hợp. Giao diện được tối ưu cho thao tác đặt vé nhanh.</p>

                        <div class="schedule-hero-stats">
                            <div>
                                <strong>{{ $suatChieus->count() }}</strong>
                                <span>Suất chiếu</span>
                            </div>
                            <div>
                                <strong>{{ $totalMovies }}</strong>
                                <span>Phim có lịch</span>
                            </div>
                            <div>
                                <strong>{{ $totalCinemas }}</strong>
                                <span>Rạp đang mở</span>
                            </div>
                        </div>
                    </div>

                    <div class="schedule-spotlight reveal-on-scroll">
                        @if ($firstShowtime)
                            <img src="{{ asset('storage/movies/' . $firstShowtime->phim->poster) }}"
                                alt="{{ $firstShowtime->phim->ten_phim }}">
                            <div>
                                <span>Suất gần nhất</span>
                                <h2>{{ $firstShowtime->phim->ten_phim }}</h2>
                                <p>
                                    {{ $firstShowtime->thoi_gian_chieu->format('H:i d/m') }}
                                    · {{ $firstShowtime->rapChieuPhim?->ten_rap ?? 'CineHome' }}
                                </p>
                            </div>
                        @else
                            <div class="schedule-spotlight-empty">
                                <i class="fa-regular fa-calendar"></i>
                                <strong>Đang cập nhật lịch chiếu</strong>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <main class="schedule-main">
            <div class="container-fluid px-5">
                <section class="schedule-filter-panel reveal-on-scroll">
                    <form method="GET" action="{{ route('user.showtimes.index') }}" class="schedule-filter-form">
                        <label>
                            <span>Phim</span>
                            <select name="phim_id">
                                <option value="">Tất cả phim</option>
                                @foreach ($movies as $movie)
                                    <option value="{{ $movie->id }}" @selected(request('phim_id') == $movie->id)>
                                        {{ $movie->ten_phim }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label>
                            <span>Rạp</span>
                            <select name="rap_chieu_phim_id">
                                <option value="">Tất cả rạp</option>
                                @foreach ($rapChieuPhims as $rap)
                                    <option value="{{ $rap->id }}" @selected(request('rap_chieu_phim_id') == $rap->id)>
                                        {{ $rap->ten_rap }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label>
                            <span>Ngày</span>
                            <input type="date" name="ngay_chieu" value="{{ request('ngay_chieu') }}">
                        </label>

                        <label>
                            <span>Trạng thái</span>
                            <select name="trang_thai">
                                <option value="">Tất cả lịch</option>
                                <option value="dang_chieu" @selected(request('trang_thai') === 'dang_chieu')>Hôm nay</option>
                                <option value="sap_chieu" @selected(request('trang_thai') === 'sap_chieu')>Sắp chiếu</option>
                            </select>
                        </label>

                        <div class="schedule-filter-actions">
                            <button type="submit">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                Tìm lịch
                            </button>
                            <a href="{{ route('user.showtimes.index') }}" aria-label="Xóa bộ lọc">
                                <i class="fa-solid fa-rotate-left"></i>
                            </a>
                        </div>
                    </form>
                </section>

                <section class="schedule-date-strip reveal-on-scroll" aria-label="Chọn nhanh ngày chiếu">
                    <a href="{{ route('user.showtimes.index', request()->except('ngay_chieu')) }}"
                        class="{{ request('ngay_chieu') ? '' : 'active' }}">
                        <span>Tất cả</span>
                        <strong>10 ngày</strong>
                    </a>

                    @foreach ($dateOptions as $dateOption)
                        <a href="{{ route('user.showtimes.index', array_merge(request()->except('ngay_chieu'), ['ngay_chieu' => $dateOption['value']])) }}"
                            class="{{ $dateOption['active'] ? 'active' : '' }}">
                            <span>{{ $dateOption['label'] }}</span>
                            <strong>{{ $dateOption['day'] }}</strong>
                        </a>
                    @endforeach
                </section>

                <section class="schedule-results reveal-on-scroll">
                    <div class="schedule-results-head">
                        <div>
                            <span>{{ $activeFilters ? $activeFilters . ' bộ lọc đang áp dụng' : 'Tất cả lịch chiếu' }}</span>
                            <h2>{{ $suatChieus->count() }} suất chiếu phù hợp</h2>
                        </div>
                    </div>

                    @forelse ($showtimeGroups as $date => $items)
                        <div class="schedule-day-group">
                            <div class="schedule-day-heading">
                                <strong>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</strong>
                                <span>{{ $items->count() }} suất</span>
                            </div>

                            <div class="schedule-card-grid">
                                @foreach ($items as $suatChieu)
                                    <article class="schedule-card">
                                        <a href="{{ route('user.movies.show', $suatChieu->phim->slug) }}"
                                            class="schedule-card-poster">
                                            <img src="{{ asset('storage/movies/' . $suatChieu->phim->poster) }}"
                                                alt="{{ $suatChieu->phim->ten_phim }}">
                                            <span>{{ $suatChieu->phim->gioi_han_tuoi }}</span>
                                        </a>

                                        <div class="schedule-card-body">
                                            <div class="schedule-card-time">
                                                <strong>{{ $suatChieu->thoi_gian_chieu->format('H:i') }}</strong>
                                                <span>{{ $suatChieu->thoi_gian_chieu->format('d/m') }}</span>
                                            </div>

                                            <h3>{{ $suatChieu->phim->ten_phim }}</h3>
                                            <p>
                                                <i class="fa-solid fa-building"></i>
                                                {{ $suatChieu->rapChieuPhim?->ten_rap ?? 'CineHome' }}
                                            </p>
                                            <p>
                                                <i class="fa-solid fa-couch"></i>
                                                {{ $suatChieu->phongChieu?->ten_phong ?? 'Phòng chiếu' }}
                                            </p>
                                            <p>
                                                <i class="fa-solid fa-tags"></i>
                                                {{ $suatChieu->phim->genres->pluck('ten_the_loai')->take(2)->join(', ') ?: 'Điện ảnh' }}
                                            </p>

                                            <div class="schedule-card-footer">
                                                <span>{{ number_format((float) $suatChieu->gia_ve, 0, ',', '.') }}đ</span>
                                                <div>
                                                    <a href="{{ route('user.showtimes.show', $suatChieu) }}"
                                                        class="schedule-detail-btn">Chi tiết</a>
                                                    <a href="{{ route('dat_ve.chon_ghe', $suatChieu->id) }}"
                                                        class="booking-link schedule-book-btn">
                                                        Đặt vé
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="schedule-empty-state">
                            <i class="fa-regular fa-calendar-xmark"></i>
                            <strong>Không có lịch chiếu phù hợp</strong>
                            <span>Hãy thử đổi phim, rạp hoặc ngày chiếu khác.</span>
                            <a href="{{ route('user.showtimes.index') }}">Xóa bộ lọc</a>
                        </div>
                    @endforelse
                </section>
            </div>
        </main>
    </section>
@endsection
