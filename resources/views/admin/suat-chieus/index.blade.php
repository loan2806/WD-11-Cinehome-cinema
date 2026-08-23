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
    
    // Đếm tổng số tham số lọc đang áp dụng
    $activeFilterCount = collect([
        request('phim_id'),
        request('phong_chieu_id'),
        request('tu_ngay'),
        request('den_ngay'),
    ])->filter(fn ($value) => filled($value))->count();
    
    $moviesWithShowtimes = $visibleMovies->filter(fn ($movie) => $movie->showtimes->isNotEmpty())->count();
    $todayShowtimes = $visibleShowtimes->filter(fn ($showtime) => $showtime->thoi_gian_chieu?->isToday())->count();
    $statusLabels = \App\Models\SuatChieu::TRANG_THAI_LIST + [
        'dung_nhan_ve' => 'Dừng nhận vé',
    ];
@endphp

@push('styles')
<style>
    details.showtime-movie-card .showtime-movie-chevron i {
        transition: transform 0.25s ease !important;
    }

    details.showtime-movie-card[open] .showtime-movie-chevron i {
        transform: rotate(180deg) !important;
    }

    /* BỘ LỌC TỰ ĐỘNG CHỈNH CỘT */
    .showtime-index-filter {
        position: relative !important;
        z-index: 10 !important;
        overflow: visible !important;
        display: grid !important;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) !important;
        gap: 16px !important;
        align-items: end !important;
    }

    .showtime-filter-field {
        position: relative !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 6px !important;
    }

    .cine-select-wrapper {
        position: relative !important;
        width: 100% !important;
        user-select: none !important;
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
        border-radius: 12px !important;
        padding: 10px 14px !important;
        color: #f3f4f6 !important;
        font-size: 14px !important;
        font-weight: 500 !important;
        cursor: pointer !important;
        height: 42px !important;
        transition: all 0.2s ease !important;
        outline: none !important;
    }

    .cine-select-trigger:hover,
    .cine-select-wrapper.open .cine-select-trigger {
        border-color: #facc15 !important;
        box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.2) !important;
    }

    .cine-select-trigger i {
        color: #facc15 !important;
        font-size: 12px !important;
        transition: transform 0.2s ease !important;
    }

    .cine-select-wrapper.open .cine-select-trigger i {
        transform: rotate(180deg) !important;
    }

    .cine-select-menu {
        position: absolute !important;
        top: calc(100% + 6px) !important;
        left: 0 !important;
        right: 0 !important;
        min-width: 200px !important;
        background: #18181c !important;
        border: 1px solid rgba(250, 204, 21, 0.3) !important;
        border-radius: 16px !important;
        padding: 8px !important;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.95), 0 0 0 1px rgba(255, 255, 255, 0.05) !important;
        z-index: 1000 !important;
        max-height: 260px !important;
        overflow-y: auto !important;
        display: none !important;
    }

    .cine-select-wrapper.open .cine-select-menu {
        display: block !important;
    }

    .cine-select-option {
        display: flex !important;
        align-items: center !important;
        padding: 10px 12px !important;
        border-radius: 10px !important;
        color: #d1d5db !important;
        font-size: 13.5px !important;
        font-weight: 500 !important;
        cursor: pointer !important;
        transition: all 0.15s ease !important;
        margin-bottom: 2px !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
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

    .cine-select-menu::-webkit-scrollbar {
        width: 6px;
    }
    .cine-select-menu::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 4px;
    }

    .custom-modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.75);
        backdrop-filter: blur(6px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 999999;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.25s ease;
    }
    .custom-modal-backdrop.active {
        opacity: 1;
        pointer-events: auto;
    }
    .custom-modal-card {
        background: #18181c;
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 20px;
        width: 100%;
        max-width: 480px;
        padding: 24px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.5);
    }

    .showtime-local-pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 14px 0 4px;
    }

    .showtime-page-btn,
    .showtime-page-numbers button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        height: 34px;
        padding: 0 10px;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.14);
        background: #18181c;
        color: #d1d5db;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .showtime-page-numbers {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .showtime-page-btn:hover,
    .showtime-page-numbers button:hover {
        border-color: #facc15;
        color: #facc15;
    }

    .showtime-page-btn:disabled {
        opacity: 0.35;
        cursor: not-allowed;
    }

    .showtime-page-btn:disabled:hover {
        border-color: rgba(255, 255, 255, 0.14);
        color: #d1d5db;
    }

    .showtime-page-numbers button.is-active {
        background: rgba(250, 204, 21, 0.18);
        border-color: #facc15;
        color: #facc15;
    }
</style>
@endpush

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
                Theo dõi từng phim, kiểm tra rạp, phòng, khung giờ, giá vé và trạng thái vận hành trong một giao diện gọn.
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

    <!-- BỘ TAB PHÂN LOẠI TRẠNG THÁI VẬN HÀNH -->
    <div class="flex items-center gap-3 overflow-x-auto pb-2">
        <a href="{{ route('admin.suat-chieus.index', array_merge(request()->query(), ['tab' => 'hoat_dong'])) }}" 
           class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ ($tab ?? 'hoat_dong') === 'hoat_dong' ? 'bg-amber-500 text-black shadow-lg shadow-amber-500/20' : 'bg-[#18181c] text-gray-400 hover:text-white border border-white/10' }}">
            <i class="fa-solid fa-play text-xs"></i>
            Đang & Sắp chiếu
            <span class="px-2 py-0.5 rounded-md text-[10px] font-black {{ ($tab ?? 'hoat_dong') === 'hoat_dong' ? 'bg-black/20 text-black' : 'bg-white/10 text-gray-300' }}">
                {{ number_format($tabCounts['hoat_dong'] ?? 0) }}
            </span>
        </a>

        <a href="{{ route('admin.suat-chieus.index', array_merge(request()->query(), ['tab' => 'da_chieu'])) }}" 
           class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ ($tab ?? 'hoat_dong') === 'da_chieu' ? 'bg-amber-500 text-black shadow-lg shadow-amber-500/20' : 'bg-[#18181c] text-gray-400 hover:text-white border border-white/10' }}">
            <i class="fa-solid fa-clock-rotate-left text-xs"></i>
            Lịch sử đã chiếu
            <span class="px-2 py-0.5 rounded-md text-[10px] font-black {{ ($tab ?? 'hoat_dong') === 'da_chieu' ? 'bg-black/20 text-black' : 'bg-white/10 text-gray-300' }}">
                {{ number_format($tabCounts['da_chieu'] ?? 0) }}
            </span>
        </a>

        <a href="{{ route('admin.suat-chieus.index', array_merge(request()->query(), ['tab' => 'tat_ca'])) }}" 
           class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ ($tab ?? 'hoat_dong') === 'tat_ca' ? 'bg-amber-500 text-black shadow-lg shadow-amber-500/20' : 'bg-[#18181c] text-gray-400 hover:text-white border border-white/10' }}">
            <i class="fa-solid fa-layer-group text-xs"></i>
            Tất cả suất chiếu
            <span class="px-2 py-0.5 rounded-md text-[10px] font-black {{ ($tab ?? 'hoat_dong') === 'tat_ca' ? 'bg-black/20 text-black' : 'bg-white/10 text-gray-300' }}">
                {{ number_format($tabCounts['tat_ca'] ?? 0) }}
            </span>
        </a>
    </div>

    <!-- FORM LỌC LỊCH (Khoảng ngày Từ -> Đến) -->
    <form method="GET" action="{{ route('admin.suat-chieus.index') }}" class="showtime-index-filter">
        <input type="hidden" name="tab" value="{{ $tab ?? 'hoat_dong' }}">

        <!-- BỘ LỌC PHIM -->
        <div class="showtime-filter-field">
            <span>Phim</span>
            <div class="cine-select-wrapper">
                <input type="hidden" name="phim_id" value="{{ request('phim_id') }}">
                <div class="cine-select-trigger" tabindex="0">
                    <span class="cine-select-value">Tất cả phim</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
                <div class="cine-select-menu">
                    <div class="cine-select-option {{ request('phim_id') == '' ? 'selected' : '' }}" data-value="">Tất cả phim</div>
                    @foreach ($phims as $itemPhim)
                        <div class="cine-select-option {{ request('phim_id') == $itemPhim->id ? 'selected' : '' }}" data-value="{{ $itemPhim->id }}">
                            {{ $itemPhim->ten_phim }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- BỘ LỌC PHÒNG CHIẾU -->
        <div class="showtime-filter-field">
            <span>Phòng chiếu</span>
            <div class="cine-select-wrapper">
                <input type="hidden" name="phong_chieu_id" value="{{ request('phong_chieu_id') }}">
                <div class="cine-select-trigger" tabindex="0">
                    <span class="cine-select-value">Tất cả phòng</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
                <div class="cine-select-menu">
                    <div class="cine-select-option {{ request('phong_chieu_id') == '' ? 'selected' : '' }}" data-value="">Tất cả phòng</div>
                    @foreach ($phongChieus as $phong)
                        <div class="cine-select-option {{ request('phong_chieu_id') == $phong->id ? 'selected' : '' }}" data-value="{{ $phong->id }}">
                            {{ $phong->ten_phong }} ({{ strtoupper($phong->loai_phong) }})
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- BỘ LỌC TỪ NGÀY -->
        <div class="showtime-filter-field">
            <span>Từ ngày</span>
            <input type="date" id="tu_ngay" name="tu_ngay" value="{{ request('tu_ngay') }}" style="color-scheme: dark; background: #18181c; border: 1px solid rgba(255,255,255,0.18); color: #fff; padding: 9px 14px; border-radius: 12px; height: 42px; width: 100%;">
        </div>

        <!-- BỘ LỌC ĐẾN NGÀY -->
        <div class="showtime-filter-field">
            <span>Đến ngày</span>
            <input type="date" id="den_ngay" name="den_ngay" value="{{ request('den_ngay') }}" style="color-scheme: dark; background: #18181c; border: 1px solid rgba(255,255,255,0.18); color: #fff; padding: 9px 14px; border-radius: 12px; height: 42px; width: 100%;">
        </div>

        <div class="showtime-filter-actions">
            <button type="submit" class="movie-action-btn is-primary">
                <i class="fa-solid fa-magnifying-glass"></i>
                Lọc lịch
            </button>
            <a href="{{ route('admin.suat-chieus.index', ['tab' => $tab ?? 'hoat_dong']) }}" class="movie-action-btn is-ghost">
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
                @endphp

                <details class="showtime-movie-card">
                    <summary class="showtime-movie-summary">
                        <div class="showtime-movie-title">
                            <img src="{{ $posterUrl($phim->poster) }}" alt="{{ $phim->ten_phim }}">
                            <div>
                                <strong>{{ $phim->ten_phim }}</strong>
                                <span>
                                    <i class="fa-regular fa-clock"></i>
                                    {{ $phim->thoi_luong ?? 90 }} phút
                                    <b></b>
                                    {{ $phim->country?->ten_quoc_gia ?? 'Việt Nam' }}
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
                            @php
                                $showtimePerPage = 10;
                                $showtimeTableId = 'showtime-table-' . $phim->id;
                            @endphp
                            <div class="showtime-table-wrap" data-per-page="{{ $showtimePerPage }}">
                                <table class="showtime-index-table" id="{{ $showtimeTableId }}">
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
                                            <tr data-row-index="{{ $loop->index }}">
                                                <td><span class="showtime-code">#{{ sprintf('%04d', $suat->id) }}</span></td>
                                                <td>{{ $suat->rapChieuPhim?->ten_rap ?? 'CineHome Cinema' }}</td>
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
                                                <td><strong class="showtime-price">{{ number_format((float) $suat->gia_ve_cuoi_cung) }}đ</strong></td>
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
                                                        
                                                        <button type="button" 
                                                                class="movie-icon-btn is-delete" 
                                                                title="Xóa suất chiếu"
                                                                onclick="openDeleteModal('{{ route('admin.suat-chieus.destroy', $suat) }}', '{{ sprintf('%04d', $suat->id) }}', '{{ $phim->ten_phim }}', '{{ $suat->thoi_gian_chieu?->format('d/m/Y H:i') }}')">
                                                            <i class="fa-solid fa-trash-can"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if ($movieShowtimes->count() > $showtimePerPage)
                                <div class="showtime-local-pagination" data-target="{{ $showtimeTableId }}">
                                    <button type="button" class="showtime-page-btn is-prev" aria-label="Trang trước">
                                        <i class="fa-solid fa-chevron-left"></i>
                                    </button>
                                    <div class="showtime-page-numbers"></div>
                                    <button type="button" class="showtime-page-btn is-next" aria-label="Trang sau">
                                        <i class="fa-solid fa-chevron-right"></i>
                                    </button>
                                </div>
                            @endif
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

<!-- MODAL XÁC NHẬN XÓA SUẤT CHIẾU -->
<div id="deleteModal" class="custom-modal-backdrop">
    <div class="custom-modal-card">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
            <div style="background: rgba(239, 68, 68, 0.2); color: #ef4444; width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <h4 style="color: #fff; font-size: 18px; font-weight: 800; margin: 0;">Xác nhận xóa suất chiếu</h4>
                <small style="color: #9ca3af;" id="deleteModalSub">Suất chiếu #0000</small>
            </div>
        </div>

        <div style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 12px; padding: 12px; margin-bottom: 16px; font-size: 12.5px; color: #fcd34d;">
            <i class="fa-solid fa-circle-info" style="margin-right: 4px;"></i>
            Chỉ cho phép xóa suất chiếu <strong>trước ít nhất 3 ngày</strong> và <strong>chưa có khách hàng đặt vé</strong>.
        </div>

        <form id="deleteForm" method="POST" action="">
            @csrf
            @method('DELETE')
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; color: #d1d5db; font-size: 13px; font-weight: 600; margin-bottom: 8px;">
                    Lý do xóa / hủy suất chiếu <span style="color: #ef4444;">*</span>
                </label>
                <textarea name="ly_do_huy" id="ly_do_huy_input" required rows="3" placeholder="Ví dụ: Đổi lịch phòng, sự cố thiết bị, thay đổi khung giờ chiếu..." style="width: 100%; background: #111113; border: 1px solid rgba(255,255,255,0.15); color: #fff; border-radius: 12px; padding: 12px; font-size: 14px; outline: none;"></textarea>
                <small style="color: #6b7280; margin-top: 4px; display: block;">Lý do xóa sẽ được lưu lại nhật ký hệ thống (Audit Log).</small>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeDeleteModal()" class="movie-action-btn is-ghost" style="padding: 8px 16px; font-size: 13px;">Hủy bỏ</button>
                <button type="submit" class="movie-action-btn is-primary" style="background: #ef4444; border-color: #ef4444; color: #fff; padding: 8px 18px; font-size: 13px;">Xác nhận xóa</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // RÀNG BUỘC TỰ ĐỘNG CHỈNH KHOẢNG NGÀY TỪ NGÀY - ĐẾN NGÀY
    const tuNgayInput = document.getElementById('tu_ngay');
    const denNgayInput = document.getElementById('den_ngay');

    if (tuNgayInput && denNgayInput) {
        if (tuNgayInput.value) denNgayInput.min = tuNgayInput.value;
        if (denNgayInput.value) tuNgayInput.max = denNgayInput.value;

        tuNgayInput.addEventListener('change', function() {
            denNgayInput.min = this.value;
            if (denNgayInput.value && denNgayInput.value < this.value) {
                denNgayInput.value = this.value;
            }
        });

        denNgayInput.addEventListener('change', function() {
            tuNgayInput.max = this.value;
            if (tuNgayInput.value && tuNgayInput.value > this.value) {
                tuNgayInput.value = this.value;
            }
        });
    }

    document.querySelectorAll('.cine-select-wrapper').forEach(function(wrapper) {
        const hiddenInput = wrapper.querySelector('input[type="hidden"]');
        const trigger = wrapper.querySelector('.cine-select-trigger');
        const triggerText = wrapper.querySelector('.cine-select-value');
        const options = wrapper.querySelectorAll('.cine-select-option');

        options.forEach(function(opt) {
            if (opt.classList.contains('selected')) {
                triggerText.textContent = opt.textContent.trim();
            }
        });

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
                hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
    });

    document.addEventListener('click', function() {
        document.querySelectorAll('.cine-select-wrapper').forEach(function(w) {
            w.classList.remove('open');
        });
    });

    document.querySelectorAll('.showtime-local-pagination').forEach(function(pager) {
        const table = document.getElementById(pager.dataset.target);
        if (!table) return;

        const wrap = table.closest('.showtime-table-wrap');
        const perPage = parseInt(wrap?.dataset.perPage || '10', 10);
        const rows = Array.from(table.querySelectorAll('tbody tr[data-row-index]'));
        const totalPages = Math.max(1, Math.ceil(rows.length / perPage));

        const prevBtn = pager.querySelector('.is-prev');
        const nextBtn = pager.querySelector('.is-next');
        const numbersEl = pager.querySelector('.showtime-page-numbers');
        let currentPage = 1;

        function renderNumbers() {
            numbersEl.innerHTML = '';
            for (let p = 1; p <= totalPages; p++) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = p;
                btn.classList.toggle('is-active', p === currentPage);
                btn.addEventListener('click', function() { goToPage(p); });
                numbersEl.appendChild(btn);
            }
        }

        function goToPage(page) {
            currentPage = Math.min(Math.max(page, 1), totalPages);
            rows.forEach(function(row) {
                const rowPage = Math.floor(parseInt(row.dataset.rowIndex, 10) / perPage) + 1;
                row.style.display = rowPage === currentPage ? '' : 'none';
            });
            prevBtn.disabled = currentPage === 1;
            nextBtn.disabled = currentPage === totalPages;
            renderNumbers();
        }

        prevBtn.addEventListener('click', function() { goToPage(currentPage - 1); });
        nextBtn.addEventListener('click', function() { goToPage(currentPage + 1); });

        goToPage(1);
    });
});

function openDeleteModal(actionUrl, code, phimTitle, showTimeText) {
    document.getElementById('deleteForm').action = actionUrl;
    document.getElementById('deleteModalSub').textContent = 'Suất chiếu #' + code + ' - Phim: ' + phimTitle + (showTimeText ? ' (' + showTimeText + ')' : '');
    document.getElementById('ly_do_huy_input').value = '';
    document.getElementById('deleteModal').classList.add('active');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
}
</script>
@endpush