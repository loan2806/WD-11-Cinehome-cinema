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

        <form action="{{ route('user.phims.index') }}" method="GET" class="movie-filter movie-filter-form" id="movieFilterForm">
            <label class="filter-label {{ request('tim_kiem') ? 'is-active' : '' }}">
                <span class="filter-title">
                    <i class="fa-solid fa-film text-red-500"></i>
                    Tên phim
                </span>
                <input type="text" name="tim_kiem" value="{{ request('tim_kiem') }}" placeholder="Nhập tên phim cần tìm..."
                    class="filter-input">
            </label>

            <div class="filter-label {{ request('the_loai') ? 'is-active' : '' }}">
                <span class="filter-title">
                    <i class="fa-solid fa-tags text-amber-400"></i>
                    Thể loại
                </span>
                <div class="cine-custom-select" data-select-name="the_loai">
                    <input type="hidden" name="the_loai" value="{{ request('the_loai') }}">
                    <button type="button" class="cine-select-trigger">
                        <span class="cine-select-value">{{ request('the_loai') ?: 'Tất cả thể loại' }}</span>
                        <i class="fa-solid fa-chevron-down cine-select-arrow"></i>
                    </button>
                    <div class="cine-select-dropdown">
                        <div class="cine-option {{ !request('the_loai') ? 'selected' : '' }}" data-value="">
                            <span>Tất cả thể loại</span>
                            <i class="fa-solid fa-check check-icon"></i>
                        </div>
                        @foreach ($genres as $genre)
                            <div class="cine-option {{ request('the_loai') == $genre->ten_the_loai ? 'selected' : '' }}" data-value="{{ $genre->ten_the_loai }}">
                                <span>{{ $genre->ten_the_loai }}</span>
                                <i class="fa-solid fa-check check-icon"></i>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="filter-label {{ request('quoc_gia') ? 'is-active' : '' }}">
                <span class="filter-title">
                    <i class="fa-solid fa-earth-americas text-blue-400"></i>
                    Quốc gia
                </span>
                <div class="cine-custom-select" data-select-name="quoc_gia">
                    <input type="hidden" name="quoc_gia" value="{{ request('quoc_gia') }}">
                    <button type="button" class="cine-select-trigger">
                        <span class="cine-select-value">{{ request('quoc_gia') ?: 'Tất cả quốc gia' }}</span>
                        <i class="fa-solid fa-chevron-down cine-select-arrow"></i>
                    </button>
                    <div class="cine-select-dropdown">
                        <div class="cine-option {{ !request('quoc_gia') ? 'selected' : '' }}" data-value="">
                            <span>Tất cả quốc gia</span>
                            <i class="fa-solid fa-check check-icon"></i>
                        </div>
                        @foreach ($countries as $country)
                            <div class="cine-option {{ request('quoc_gia') == $country->ten_quoc_gia ? 'selected' : '' }}" data-value="{{ $country->ten_quoc_gia }}">
                                <span>{{ $country->ten_quoc_gia }}</span>
                                <i class="fa-solid fa-check check-icon"></i>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="filter-label {{ request('status') ? 'is-active' : '' }}">
                <span class="filter-title">
                    <i class="fa-solid fa-circle-play text-emerald-400"></i>
                    Trạng thái
                </span>
                <div class="cine-custom-select" data-select-name="status">
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <button type="button" class="cine-select-trigger">
                        <span class="cine-select-value">{{ isset($statusLabels[request('status')]) ? $statusLabels[request('status')] : 'Tất cả trạng thái' }}</span>
                        <i class="fa-solid fa-chevron-down cine-select-arrow"></i>
                    </button>
                    <div class="cine-select-dropdown">
                        <div class="cine-option {{ !request('status') ? 'selected' : '' }}" data-value="">
                            <span>Tất cả trạng thái</span>
                            <i class="fa-solid fa-check check-icon"></i>
                        </div>
                        @foreach ($statusLabels as $statusValue => $statusLabel)
                            <div class="cine-option {{ request('status') == $statusValue ? 'selected' : '' }}" data-value="{{ $statusValue }}">
                                <span>{{ $statusLabel }}</span>
                                <i class="fa-solid fa-check check-icon"></i>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="movie-filter-actions">
                <button type="submit" class="btn-filter" title="Tìm kiếm">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <span>Lọc phim</span>
                </button>
                <a href="{{ route('user.phims.index') }}" class="btn-reset" title="Xóa bộ lọc" aria-label="Xóa bộ lọc">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </form>

        @if ($movies->count() > 0)
            <section class="movie-list-grid" aria-label="Danh sách phim">
                @foreach ($movies as $movie)
                    @php
                        $movieStatus = $movie->calculated_status ?? \App\Models\SuatChieu::TRANG_THAI_SAP_RA_MAT;
                        $canBook = in_array($movieStatus, [
                            \App\Models\SuatChieu::TRANG_THAI_DANG_CHIEU,
                            \App\Models\SuatChieu::TRANG_THAI_SAP_CHIEU
                        ]);
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
                                    {{-- ĐÃ SỬA: TRUYỀN THÊM ?tab=showtimes#lich-chieu --}}
                                    <a href="{{ route('user.movies.show', $movie->slug) }}?tab=showtimes#lich-chieu" class="btn-small-book booking-link">
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