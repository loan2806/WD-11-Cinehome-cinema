@extends('layouts.user')

@section('title', 'Danh sách phim')

@section('content')
    @php
        $statusLabels = [
            \App\Models\SuatChieu::TRANG_THAI_DANG_CHIEU => 'Đang chiếu',
            \App\Models\SuatChieu::TRANG_THAI_SAP_CHIEU => 'Sắp chiếu',
            \App\Models\SuatChieu::TRANG_THAI_SAP_RA_MAT => 'Sắp ra mắt',
        ];

        $posterUrl = function ($movie) {
            if (!empty($movie->poster) && file_exists(public_path('storage/movies/' . $movie->poster))) {
                return asset('storage/movies/' . $movie->poster);
            }

            return 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=700&auto=format&fit=crop';
        };
    @endphp

    <div class="movie-list-page" lang="vi" spellcheck="false">
        <section class="movie-list-hero">
            <div>
                <span class="movie-list-eyebrow">
                    <i class="fa-solid fa-film"></i>
                    CineHome Movies
                </span>
                <h1>Chọn phim bạn muốn xem tại CineHome.</h1>
                <p>
                    Lọc nhanh theo tên phim, thể loại, quốc gia và trạng thái chiếu. Giao diện được tối ưu để xem poster,
                    so sánh phim và đặt vé nhanh hơn.
                </p>
            </div>

            <div class="movie-list-summary">
                <strong>{{ $movies->count() }}</strong>
                <span>phim phù hợp</span>
            </div>
        </section>

        <form action="{{ route('user.phims.index') }}" method="GET" class="movie-filter movie-filter-form">
            <label>
                <span>Tên phim</span>
                <input type="text" name="tim_kiem" value="{{ request('tim_kiem') }}" placeholder="Tìm tên phim..."
                    class="filter-input">
            </label>

            <label>
                <span>Thể loại</span>
                <select name="the_loai" class="filter-input">
                    <option value="">Tất cả thể loại</option>
                    @foreach ($genres as $genre)
                        <option value="{{ $genre->ten_the_loai }}" {{ request('the_loai') == $genre->ten_the_loai ? 'selected' : '' }}>
                            {{ $genre->ten_the_loai }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label>
                <span>Quốc gia</span>
                <select name="quoc_gia" class="filter-input">
                    <option value="">Tất cả quốc gia</option>
                    @foreach ($countries as $country)
                        <option value="{{ $country->ten_quoc_gia }}" {{ request('quoc_gia') == $country->ten_quoc_gia ? 'selected' : '' }}>
                            {{ $country->ten_quoc_gia }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label>
                <span>Trạng thái</span>
                <select name="status" class="filter-input">
                    <option value="">Tất cả trạng thái</option>
                    @foreach ($statusLabels as $statusValue => $statusLabel)
                        <option value="{{ $statusValue }}" {{ request('status') == $statusValue ? 'selected' : '' }}>
                            {{ $statusLabel }}
                        </option>
                    @endforeach
                </select>
            </label>

            <div class="movie-filter-actions">
                <button type="submit" class="btn-filter">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    Tìm
                </button>
                <a href="{{ route('user.phims.index') }}" class="btn-reset" aria-label="Xóa bộ lọc">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </form>

        @if ($movies->count() > 0)
            <section class="movie-list-grid" aria-label="Danh sách phim">
                @foreach ($movies as $movie)
                    @php
                        $now = now('Asia/Ho_Chi_Minh');
                        $futureShowtime = $movie->showtimes
                            ->filter(fn($showtime) => $showtime->thoi_gian_chieu && \Carbon\Carbon::parse($showtime->thoi_gian_chieu)->gte($now))
                            ->sortBy('thoi_gian_chieu')
                            ->first();

                        $movieStatus = $futureShowtime?->trang_thai ?? \App\Models\SuatChieu::TRANG_THAI_SAP_RA_MAT;
                        $canBook = $futureShowtime && $futureShowtime->trang_thai === \App\Models\SuatChieu::TRANG_THAI_SAP_CHIEU;
                    @endphp

                    <article class="movie-card movie-list-card">
                        <a href="{{ route('user.movies.show', $movie->slug) }}" class="movie-poster" aria-label="Xem chi tiết {{ $movie->ten_phim }}">
                            <img src="{{ $posterUrl($movie) }}" alt="{{ $movie->ten_phim }}">
                            <span class="movie-status">{{ $statusLabels[$movieStatus] ?? 'Sắp chiếu' }}</span>
                            @if (!empty($movie->gioi_han_tuoi))
                                <span class="movie-age">{{ $movie->gioi_han_tuoi }}</span>
                            @endif
                        </a>

                        <div class="movie-body">
                            <h2 class="movie-title">{{ $movie->ten_phim }}</h2>

                            <div class="movie-info">
                                <p>
                                    <i class="fa-solid fa-clapperboard"></i>
                                    {{ $movie->genres->pluck('ten_the_loai')->filter()->take(2)->join(', ') ?: 'Đang cập nhật' }}
                                </p>
                                <p>
                                    <i class="fa-solid fa-globe"></i>
                                    {{ $movie->country->ten_quoc_gia ?? 'Đang cập nhật' }}
                                </p>
                                <p>
                                    <i class="fa-solid fa-clock"></i>
                                    {{ $movie->thoi_luong ?? '--' }} phút
                                </p>
                            </div>

                            <div class="movie-actions">
                                @if ($canBook)
                                    <a href="{{ route('dat_ve.chon_ghe', $movie->slug) }}" class="btn-small-book booking-link">
                                        <i class="fa-solid fa-ticket"></i>
                                        Đặt vé
                                    </a>
                                @else
                                    <span class="btn-small-book is-disabled">
                                        <i class="fa-solid fa-calendar-days"></i>
                                        Chờ lịch
                                    </span>
                                @endif

                                <a href="{{ route('user.movies.show', $movie->slug) }}" class="btn-small-detail">
                                    Chi tiết
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>
        @else
            <section class="empty-movies">
                <i class="fa-solid fa-film"></i>
                <h2>Không tìm thấy phim</h2>
                <p>Thử đổi từ khóa, thể loại hoặc trạng thái chiếu để xem thêm phim phù hợp.</p>
            </section>
        @endif
    </div>
@endsection
