@extends('layouts.admin')

@section('page-title', 'Danh sách phim')
@section('page-subtitle', 'Quản lý kho phim, poster, thể loại và lịch chiếu')

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

    $currentMovies = $movies->getCollection();
    $activeFilterCount = collect([
        request('tim_kiem'),
        request('the_loai'),
        request('quoc_gia'),
    ])->filter(fn ($value) => filled($value))->count();
    $moviesWithShowtimes = $currentMovies->filter(fn ($movie) => $movie->showtimes->isNotEmpty())->count();
    
    $genreList = $genres ?? $theLoais ?? [];
    $countryList = $countries ?? $quocGias ?? [];
@endphp

@push('styles')
<style>
    /* 🌟 BỐ CỤC TỔNG THỂ TỶ LỆ 100% CHUẨN */
    .movie-admin-page {
        padding-top: 15px !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    /* 🌟 KHUNG TÌM KIẾM GỌN GÀNG CHUẨN BỐ CỤC */
    .movie-filter-box {
        background: #151518 !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        border-radius: 16px !important;
        padding: 16px 20px !important;
        margin-bottom: 20px !important;
        width: 100% !important;
        box-sizing: border-box !important;
        position: relative !important;
        z-index: 10 !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5) !important;
    }

    .movie-filter-grid {
        display: grid !important;
        grid-template-columns: 2fr 1fr 1fr auto !important;
        gap: 12px !important;
        align-items: end !important;
        width: 100% !important;
    }

    /* 🌟 DROPDOWN POPUP NỔI HIGHLIGHT Z-INDEX CAO TRONG NỘI DUNG */
    .cine-select-wrapper {
        position: relative !important;
        width: 100% !important;
        user-select: none !important;
        z-index: 20 !important;
    }

    .cine-select-wrapper.open {
        z-index: 200 !important;
    }

    .cine-select-trigger {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        background: #18181c !important;
        border: 1px solid rgba(255, 255, 255, 0.18) !important;
        border-radius: 10px !important;
        padding: 8px 12px !important;
        color: #f3f4f6 !important;
        font-size: 13.5px !important;
        font-weight: 500 !important;
        cursor: pointer !important;
        height: 40px !important;
        box-sizing: border-box !important;
        transition: all 0.2s ease !important;
    }

    .cine-select-trigger:hover,
    .cine-select-wrapper.open .cine-select-trigger {
        border-color: #facc15 !important;
        box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.2) !important;
    }

    .cine-select-trigger i {
        color: #facc15 !important;
        font-size: 11px !important;
        transition: transform 0.2s ease !important;
    }

    .cine-select-wrapper.open .cine-select-trigger i {
        transform: rotate(180deg) !important;
    }

    .cine-select-menu {
        position: absolute !important;
        top: calc(100% + 4px) !important;
        left: 0 !important;
        right: 0 !important;
        background: #18181c !important;
        border: 1px solid rgba(250, 204, 21, 0.35) !important;
        border-radius: 12px !important;
        padding: 6px !important;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.95) !important;
        z-index: 1000 !important;
        max-height: 220px !important;
        overflow-y: auto !important;
        display: none !important;
    }

    .cine-select-wrapper.open .cine-select-menu {
        display: block !important;
    }

    .cine-select-option {
        padding: 8px 10px !important;
        border-radius: 6px !important;
        color: #d1d5db !important;
        font-size: 13px !important;
        cursor: pointer !important;
        transition: all 0.15s ease !important;
        white-space: nowrap !important;
    }

    .cine-select-option:hover {
        background: rgba(250, 204, 21, 0.15) !important;
        color: #facc15 !important;
    }

    .cine-select-option.selected {
        background: rgba(250, 204, 21, 0.25) !important;
        color: #facc15 !important;
        font-weight: 700 !important;
    }

    /* 🌟 BẢNG QUẢN LÝ GỌN GÀNG HIỂN THỊ ĐỦ THAO TÁC Ở TỶ LỆ 100% */
    .movie-table-card {
        background: #151518 !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        border-radius: 16px !important;
        padding: 16px 20px !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    .movie-table-responsive {
        width: 100% !important;
        overflow-x: auto !important;
    }

    .cine-movie-table {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    .cine-movie-table th {
        color: #9ca3af !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        padding: 12px 8px !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        text-align: left !important;
    }

    .cine-movie-table td {
        padding: 10px 8px !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
        vertical-align: middle !important;
        color: #e5e7eb !important;
        font-size: 13px !important;
    }

    /* TINH CHỈNH CHIỀU RỘNG & PHẦN TỬ TRONG BẢNG */
    .poster-mini {
        width: 42px !important;
        height: 58px !important;
        object-fit: cover !important;
        border-radius: 6px !important;
        border: 1px solid rgba(255,255,255,0.12) !important;
        flex-shrink: 0 !important;
    }

    .badge-chip {
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
        padding: 4px 8px !important;
        border-radius: 14px !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        background: rgba(255,255,255,0.06) !important;
        border: 1px solid rgba(255,255,255,0.1) !important;
        white-space: nowrap !important;
    }

    .col-lang-text {
        font-size: 12.5px !important;
        color: #d1d5db !important;
        line-height: 1.3 !important;
        max-width: 130px !important;
        display: inline-block !important;
    }

    .action-btns-group {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 6px !important;
    }

    .btn-icon-act {
        width: 32px !important;
        height: 32px !important;
        border-radius: 8px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        color: #fff !important;
        font-size: 12px !important;
        transition: all 0.2s ease !important;
        text-decoration: none !important;
        border: none !important;
        cursor: pointer !important;
    }

    .btn-icon-act.view { background: rgba(59, 130, 246, 0.18) !important; color: #60a5fa !important; }
    .btn-icon-act.edit { background: rgba(234, 179, 8, 0.18) !important; color: #facc15 !important; }
    .btn-icon-act.delete { background: rgba(239, 68, 68, 0.18) !important; color: #f87171 !important; }

    .btn-icon-act:hover {
        transform: translateY(-2px) !important;
        filter: brightness(1.25) !important;
    }
</style>
@endpush

@section('content')
<div class="movie-admin-page">
    @include('admin.partials.flash')

    <!-- NÚT TẠO PHIM HEADER -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <div>
            <h2 style="font-size: 24px; font-weight: 900; color: #fff; margin: 0;">Danh sách phim</h2>
            <p style="color: #9ca3af; font-size: 13px; margin: 4px 0 0 0;">Quản lý kho phim, poster, thể loại và lịch chiếu.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('admin.phims.create') }}" class="movie-action-btn is-primary">
                <i class="fa-solid fa-plus"></i> Thêm phim mới
            </a>
            <a href="{{ route('admin.suat-chieus.index') }}" class="movie-action-btn is-ghost">
                <i class="fa-solid fa-calendar-days"></i> Suất chiếu
            </a>
        </div>
    </div>

    <!-- KHUNG TÌM KIẾM VÀ DROPDOWN ĐỀ NỔI -->
    <div class="movie-filter-box">
        <form method="GET" action="{{ route('admin.phims.index') }}" class="movie-filter-grid">
            <!-- Ô TÌM KIẾM -->
            <div>
                <span style="display: block; color: #d1d5db; font-size: 12px; font-weight: 700; margin-bottom: 6px;">Tìm kiếm</span>
                <div style="position: relative;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #ef4444; font-size: 13px;"></i>
                    <input type="text" name="tim_kiem" value="{{ request('tim_kiem') }}" placeholder="Nhập tên phim cần tìm..." style="width: 100%; background: #18181c; border: 1px solid rgba(255,255,255,0.18); color: #fff; padding: 8px 12px 8px 34px; border-radius: 10px; height: 40px; font-size: 13.5px; outline: none; box-sizing: border-box;">
                </div>
            </div>

            <!-- DROPDOWN THỂ LOẠI -->
            <div>
                <span style="display: block; color: #d1d5db; font-size: 12px; font-weight: 700; margin-bottom: 6px;">Thể loại</span>
                <div class="cine-select-wrapper">
                    <input type="hidden" name="the_loai" value="{{ request('the_loai') }}">
                    <div class="cine-select-trigger" tabindex="0">
                        <span class="cine-select-value">{{ request('the_loai') ?: 'Tất cả thể loại' }}</span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                    <div class="cine-select-menu">
                        <div class="cine-select-option {{ empty(request('the_loai')) ? 'selected' : '' }}" data-value="">Tất cả thể loại</div>
                        @foreach ($genreList as $genre)
                            @php $gName = is_object($genre) ? $genre->ten_the_loai : $genre; @endphp
                            <div class="cine-select-option {{ request('the_loai') == $gName ? 'selected' : '' }}" data-value="{{ $gName }}">
                                {{ $gName }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- DROPDOWN QUỐC GIA -->
            <div>
                <span style="display: block; color: #d1d5db; font-size: 12px; font-weight: 700; margin-bottom: 6px;">Quốc gia</span>
                <div class="cine-select-wrapper">
                    <input type="hidden" name="quoc_gia" value="{{ request('quoc_gia') }}">
                    <div class="cine-select-trigger" tabindex="0">
                        <span class="cine-select-value">{{ request('quoc_gia') ?: 'Tất cả quốc gia' }}</span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                    <div class="cine-select-menu">
                        <div class="cine-select-option {{ empty(request('quoc_gia')) ? 'selected' : '' }}" data-value="">Tất cả quốc gia</div>
                        @foreach ($countryList as $country)
                            @php $cName = is_object($country) ? $country->ten_quoc_gia : $country; @endphp
                            <div class="cine-select-option {{ request('quoc_gia') == $cName ? 'selected' : '' }}" data-value="{{ $cName }}">
                                {{ $cName }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- NÚT THAO TÁC LỌC -->
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="movie-action-btn is-primary" style="height: 40px; padding: 0 16px;">
                    <i class="fa-solid fa-sliders"></i> Lọc phim
                </button>
                <a href="{{ route('admin.phims.index') }}" class="movie-action-btn is-ghost" style="height: 40px; padding: 0 12px;" title="Đặt lại">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- KHUNG BẢNG DANH SÁCH -->
    <div class="movie-table-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <span style="color: #facc15; font-size: 12px; font-weight: 800; text-transform: uppercase;">
                <i class="fa-solid fa-list-check"></i> KHO PHIM CINEHOME
            </span>
            <small style="color: #9ca3af; font-size: 12.5px;">
                Hiển thị <b>{{ $movies->firstItem() ?? 0 }} - {{ $movies->lastItem() ?? 0 }}</b> trong tổng <b>{{ number_format($movies->total()) }}</b> phim
            </small>
        </div>

        <div class="movie-table-responsive">
            <table class="cine-movie-table">
                <thead>
                    <tr>
                        <th style="width: 240px;">Phim</th>
                        <th style="width: 120px;">Quốc gia</th>
                        <th style="width: 180px;">Thể loại</th>
                        <th style="width: 140px;">Ngôn ngữ</th>
                        <th style="width: 100px;">Thời lượng</th>
                        <th style="width: 110px;">Suất chiếu</th>
                        <th style="width: 110px; text-align: right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movies as $movie)
                        @php
                            $showtimeCount = $movie->showtimes->count();
                            $visibleGenres = $movie->genres->take(2);
                            $hiddenGenreCount = max($movie->genres->count() - $visibleGenres->count(), 0);
                        @endphp
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <img src="{{ $posterUrl($movie->poster) }}" alt="{{ $movie->ten_phim }}" class="poster-mini">
                                    <div>
                                        <strong style="color: #fff; font-size: 14px; display: block; margin-bottom: 3px;">{{ $movie->ten_phim }}</strong>
                                        <div style="display: flex; align-items: center; gap: 5px; font-size: 11px;">
                                            <span style="color: #facc15; font-weight: 700;">#{{ $movie->id }}</span>
                                            @if ($movie->gioi_han_tuoi)
                                                <span style="background: #ef4444; color: #fff; padding: 1px 5px; border-radius: 4px; font-weight: 800;">{{ $movie->gioi_han_tuoi }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="badge-chip">
                                    <i class="fa-solid fa-location-dot" style="color: #facc15;"></i>
                                    {{ $movie->country?->ten_quoc_gia ?? 'Chưa rõ' }}
                                </span>
                            </td>

                            <td>
                                <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                    @forelse ($visibleGenres as $genre)
                                        <span style="background: rgba(255,255,255,0.08); color: #d1d5db; padding: 2px 8px; border-radius: 6px; font-size: 11.5px;">{{ $genre->ten_the_loai }}</span>
                                    @empty
                                        <span style="color: #6b7280; font-size: 11.5px;">Chưa xếp</span>
                                    @endforelse

                                    @if ($hiddenGenreCount > 0)
                                        <span style="background: rgba(250, 204, 21, 0.2); color: #facc15; padding: 2px 6px; border-radius: 6px; font-size: 11px; font-weight: 700;">+{{ $hiddenGenreCount }}</span>
                                    @endif
                                </div>
                            </td>

                            <td>
                                <span class="col-lang-text">{{ $movie->ngon_ngu ?: 'Tiếng Việt / Phụ đề' }}</span>
                            </td>

                            <td>
                                <span style="color: #facc15; font-weight: 700; font-size: 12.5px; white-space: nowrap;">
                                    <i class="fa-regular fa-clock"></i> {{ (int) $movie->thoi_luong }} phút
                                </span>
                            </td>

                            <td>
                                @if ($showtimeCount > 0)
                                    <span style="background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 11.5px; white-space: nowrap;">
                                        <i class="fa-solid fa-calendar-check"></i> {{ $showtimeCount }} suất
                                    </span>
                                @else
                                    <span style="background: rgba(255, 255, 255, 0.05); color: #9ca3af; padding: 3px 8px; border-radius: 6px; font-size: 11.5px; white-space: nowrap;">
                                        Chưa có
                                    </span>
                                @endif
                            </td>

                            <td>
                                <div class="action-btns-group">
                                    <a href="{{ route('admin.phims.show', $movie) }}" class="btn-icon-act view" title="Xem chi tiết">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.phims.edit', $movie) }}" class="btn-icon-act edit" title="Sửa phim">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.phims.destroy', $movie) }}" onsubmit="return confirm('Bạn chắc chắn muốn xóa phim này?')" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon-act delete" title="Xóa phim">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px; color: #9ca3af;">
                                Không tìm thấy phim phù hợp.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 16px;">
            @include('components.admin-pagination', ['paginator' => $movies])
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 🌟 SCRIPT KÍCH HOẠT DROPDOWN CINE-SELECT POPUP NỔI
    document.querySelectorAll('.cine-select-wrapper').forEach(function(wrapper) {
        const hiddenInput = wrapper.querySelector('input[type="hidden"]');
        const trigger = wrapper.querySelector('.cine-select-trigger');
        const triggerText = wrapper.querySelector('.cine-select-value');
        const options = wrapper.querySelectorAll('.cine-select-option');

        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            
            document.querySelectorAll('.cine-select-wrapper').forEach(function(w) {
                if (w !== wrapper) {
                    w.classList.remove('open');
                }
            });

            wrapper.classList.toggle('open');
        });

        options.forEach(function(opt) {
            opt.addEventListener('click', function(e) {
                e.stopPropagation();
                const val = opt.dataset.value;
                hiddenInput.value = val;
                triggerText.textContent = opt.textContent.trim();

                options.forEach(o => o.classList.remove('selected'));
                opt.classList.add('selected');

                wrapper.classList.remove('open');
            });
        });
    });

    document.addEventListener('click', function() {
        document.querySelectorAll('.cine-select-wrapper').forEach(function(w) {
            w.classList.remove('open');
        });
    });
});
</script>
@endpush