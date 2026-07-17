@extends('layouts.admin')

@section('page-title', 'Quản lý suất chiếu')
@section('page-subtitle', 'Điều phối lịch chiếu theo từng phim, phòng và trạng thái vận hành')

@php
    $posterUrl = function (?string $poster): string {
        if (empty($poster)) {
            return asset('assets/images/LOGO copy.png');
        }

        $poster = ltrim($poster, '/');

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

    $visibleMovies = $phimsPhanTrang->getCollection();
    $visibleShowtimes = $visibleMovies->flatMap(fn ($movie) => $movie->showtimes);
    $activeFilterCount = collect([
        request('phim_id'),
        request('phong_chieu_id'),
        request('trang_thai'),
        request('ngay_chieu'),
    ])->filter(fn ($value) => filled($value))->count();
    $moviesWithShowtimes = $visibleMovies->filter(fn ($movie) => $movie->showtimes->isNotEmpty())->count();
    $todayShowtimes = $visibleShowtimes->filter(fn ($showtime) => $showtime->thoi_gian_chieu?->isToday())->count();
    $upcomingShowtimes = $visibleShowtimes->filter(fn ($showtime) => $showtime->thoi_gian_chieu?->isFuture())->count();
    $statusLabels = \App\Models\SuatChieu::TRANG_THAI_LIST + [
        'dung_nhan_ve' => 'Dừng nhận vé',
    ];
@endphp

@section('content')
<div class="showtime-index-page">
    @include('admin.partials.flash')

    <section class="showtime-index-hero">
        <div>
            <span class="showtime-kicker">
                <i class="fa-solid fa-layer-group"></i>
                Điều phối lịch chiếu
            </span>
            <h2>Quản lý suất chiếu theo phim</h2>
            <p>
                Theo dõi từng phim, kiểm tra rạp, phòng, khung giờ, giá vé và trạng thái vận hành trong một giao diện
                gọn, dễ mở rộng và dễ thao tác.
            </p>
        </div>

        <div class="showtime-index-actions">
            <a href="{{ route('admin.suat-chieus.create') }}" class="movie-action-btn is-primary">
                <i class="fa-solid fa-calendar-plus"></i>
                Thêm suất chiếu
            </a>
            <a href="{{ route('admin.phims.index') }}" class="movie-action-btn is-ghost">
                <i class="fa-solid fa-film"></i>
                Kho phim
            </a>
        </div>
    </section>

    <section class="showtime-index-stats" aria-label="Tổng quan suất chiếu">
        <article>
            <span><i class="fa-solid fa-clapperboard"></i></span>
            <div>
                <small>Phim trong trang</small>
                <strong>{{ number_format($visibleMovies->count()) }}</strong>
            </div>
        </article>

        <article>
            <span><i class="fa-solid fa-calendar-check"></i></span>
            <div>
                <small>Suất đang hiển thị</small>
                <strong>{{ number_format($visibleShowtimes->count()) }}</strong>
            </div>
        </article>

        <article>
            <span><i class="fa-solid fa-clock"></i></span>
            <div>
                <small>Suất hôm nay</small>
                <strong>{{ number_format($todayShowtimes) }}</strong>
            </div>
        </article>

        <article>
            <span><i class="fa-solid fa-filter"></i></span>
            <div>
                <small>Bộ lọc đang dùng</small>
                <strong>{{ number_format($activeFilterCount) }}</strong>
            </div>
        </article>
    </section>

    <form method="GET" action="{{ route('admin.suat-chieus.index') }}" class="showtime-index-filter">
        <label class="showtime-filter-field">
            <span>Phim</span>
            <select name="phim_id">
                <option value="">Tất cả phim</option>
                @foreach ($phims as $itemPhim)
                    <option value="{{ $itemPhim->id }}" @selected(request('phim_id') == $itemPhim->id)>
                        {{ $itemPhim->ten_phim }}
                    </option>
                @endforeach
            </select>
        </label>

        <label class="showtime-filter-field">
            <span>Phòng chiếu</span>
            <select name="phong_chieu_id">
                <option value="">Tất cả phòng</option>
                @foreach ($phongChieus as $phong)
                    <option value="{{ $phong->id }}" @selected(request('phong_chieu_id') == $phong->id)>
                        {{ $phong->ten_phong }} ({{ strtoupper($phong->loai_phong) }})
                    </option>
                @endforeach
            </select>
        </label>

        <label class="showtime-filter-field">
            <span>Trạng thái</span>
            <select name="trang_thai">
                <option value="">Tất cả trạng thái</option>
                @foreach ($statusLabels as $value => $label)
                    <option value="{{ $value }}" @selected(request('trang_thai') == $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </label>

        <label class="showtime-filter-field">
            <span>Ngày chiếu</span>
            <input type="date" name="ngay_chieu" value="{{ request('ngay_chieu') }}">
        </label>

        <div class="showtime-filter-actions">
            <button type="submit" class="movie-action-btn is-primary">
                <i class="fa-solid fa-magnifying-glass"></i>
                Lọc lịch
            </button>
            <a href="{{ route('admin.suat-chieus.index') }}" class="movie-action-btn is-ghost">
                <i class="fa-solid fa-rotate-left"></i>
                Đặt lại
            </a>
        </div>
    </form>

    <section class="showtime-index-list">
        <div class="showtime-index-list-head">
            <div>
                <span class="showtime-kicker">
                    <i class="fa-solid fa-list-check"></i>
                    Kết quả điều phối
                </span>
                <h3>{{ number_format($moviesWithShowtimes) }} phim có suất chiếu trong trang này</h3>
            </div>
            <p>
                Hiển thị
                <strong>{{ $phimsPhanTrang->firstItem() ?? 0 }} - {{ $phimsPhanTrang->lastItem() ?? 0 }}</strong>
                trong
                <strong>{{ number_format($phimsPhanTrang->total()) }}</strong>
                phim
            </p>
        </div>

        <div class="showtime-movie-stack">
            @forelse ($phimsPhanTrang as $phim)
                @php
                    $movieShowtimes = $phim->showtimes;
                    $movieTodayCount = $movieShowtimes->filter(fn ($showtime) => $showtime->thoi_gian_chieu?->isToday())->count();
                    $movieUpcomingCount = $movieShowtimes->filter(fn ($showtime) => $showtime->thoi_gian_chieu?->isFuture())->count();
                    $firstShowtime = $movieShowtimes->first();
                @endphp

                <details class="showtime-movie-card" {{ $loop->first ? 'open' : '' }}>
                    <summary class="showtime-movie-summary">
                        <div class="showtime-movie-title">
                            <img src="{{ $posterUrl($phim->poster) }}" alt="{{ $phim->ten_phim }}">
                            <div>
                                <strong>{{ $phim->ten_phim }}</strong>
                                <span>
                                    <i class="fa-regular fa-clock"></i>
                                    {{ $phim->thoi_luong ?? 90 }} phút
                                    <b></b>
                                    {{ $phim->country?->ten_quoc_gia ?? 'Chưa cập nhật quốc gia' }}
                                </span>
                            </div>
                        </div>

                        <div class="showtime-movie-metrics">
                            <span>
                                <small>Tổng suất</small>
                                <strong>{{ $movieShowtimes->count() }}</strong>
                            </span>
                            <span>
                                <small>Hôm nay</small>
                                <strong>{{ $movieTodayCount }}</strong>
                            </span>
                            <span>
                                <small>Sắp tới</small>
                                <strong>{{ $movieUpcomingCount }}</strong>
                            </span>
                        </div>

                        <div class="showtime-movie-chevron">
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                    </summary>

                    <div class="showtime-movie-body">
                        @if ($movieShowtimes->isNotEmpty())
                            <div class="showtime-mobile-list">
                                @foreach ($movieShowtimes as $suat)
                                    @php
                                        $status = $suat->trang_thai;
                                        $statusClass = match ($status) {
                                            'dang_chieu' => 'is-live',
                                            'sap_chieu', 'sap_ra_mat' => 'is-upcoming',
                                            'huy' => 'is-cancelled',
                                            default => 'is-muted',
                                        };
                                    @endphp
                                    <article class="showtime-mobile-item">
                                        <div>
                                            <strong>{{ $suat->thoi_gian_chieu?->format('H:i d/m/Y') ?? 'N/A' }}</strong>
                                            <span>
                                                {{ $suat->rapChieuPhim?->ten_rap ?? 'N/A' }}
                                                •
                                                {{ $suat->phongChieu?->ten_phong ?? 'N/A' }}
                                            </span>
                                        </div>
                                        <em class="{{ $statusClass }}">{{ $statusLabels[$status] ?? $status }}</em>
                                    </article>
                                @endforeach
                            </div>

                            <div class="showtime-table-wrap">
                                <table class="showtime-index-table">
                                    <thead>
                                        <tr>
                                            <th>Mã suất</th>
                                            <th>Rạp</th>
                                            <th>Phòng</th>
                                            <th>Ngày chiếu</th>
                                            <th>Khung giờ</th>
                                            <th>Giá vé</th>
                                            <th>Trạng thái</th>
                                            <th class="is-actions">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($movieShowtimes as $suat)
                                            @php
                                                $status = $suat->trang_thai;
                                                $statusClass = match ($status) {
                                                    'dang_chieu' => 'is-live',
                                                    'sap_chieu', 'sap_ra_mat' => 'is-upcoming',
                                                    'huy' => 'is-cancelled',
                                                    default => 'is-muted',
                                                };
                                            @endphp
                                            <tr>
                                                <td>
                                                    <span class="showtime-code">#{{ sprintf('%04d', $suat->id) }}</span>
                                                </td>
                                                <td>{{ $suat->rapChieuPhim?->ten_rap ?? 'N/A' }}</td>
                                                <td>
                                                    <span class="showtime-room-chip">
                                                        {{ $suat->phongChieu?->ten_phong ?? 'N/A' }}
                                                        @if ($suat->phongChieu?->loai_phong)
                                                            <b>{{ strtoupper($suat->phongChieu->loai_phong) }}</b>
                                                        @endif
                                                    </span>
                                                </td>
                                                <td>{{ $suat->thoi_gian_chieu?->format('d/m/Y') ?? 'N/A' }}</td>
                                                <td>
                                                    <span class="showtime-time-range">
                                                        {{ $suat->thoi_gian_chieu?->format('H:i') ?? '--:--' }}
                                                        <i class="fa-solid fa-arrow-right"></i>
                                                        {{ $suat->thoi_gian_ket_thuc?->format('H:i') ?? '--:--' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong class="showtime-price">{{ number_format((float) $suat->gia_ve) }}đ</strong>
                                                </td>
                                                <td>
                                                    <span class="showtime-status {{ $statusClass }}">
                                                        {{ $statusLabels[$status] ?? $status }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="showtime-row-actions">
                                                        <a href="{{ route('admin.suat-chieus.show', $suat) }}" class="movie-icon-btn is-view" title="Xem chi tiết">
                                                            <i class="fa-solid fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('admin.suat-chieus.edit', $suat) }}" class="movie-icon-btn is-edit" title="Sửa suất chiếu">
                                                            <i class="fa-solid fa-pen"></i>
                                                        </a>
                                                        <form action="{{ route('admin.suat-chieus.destroy', $suat) }}" method="POST" onsubmit="return confirm('Bạn chắc chắn muốn xóa suất chiếu này?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="movie-icon-btn is-delete" title="Xóa suất chiếu">
                                                                <i class="fa-solid fa-trash-can"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="showtime-empty-card">
                                <i class="fa-regular fa-calendar-xmark"></i>
                                <strong>Phim này chưa có suất chiếu phù hợp</strong>
                                <span>Thêm suất chiếu mới hoặc thay đổi bộ lọc để xem các lịch khác.</span>
                                <a href="{{ route('admin.suat-chieus.create', ['phim_id' => $phim->id]) }}" class="movie-action-btn is-primary">
                                    <i class="fa-solid fa-calendar-plus"></i>
                                    Tạo suất chiếu
                                </a>
                            </div>
                        @endif
                    </div>
                </details>
            @empty
                <div class="showtime-empty-card is-page-empty">
                    <i class="fa-solid fa-box-open"></i>
                    <strong>Không tìm thấy phim phù hợp</strong>
                    <span>Thử thay đổi bộ lọc hoặc thêm phim mới vào kho trước khi lên lịch.</span>
                </div>
            @endforelse
        </div>

        <div class="showtime-index-pagination">
            @include('components.admin-pagination', ['paginator' => $phimsPhanTrang])
        </div>
    </section>
</div>
@endsection
