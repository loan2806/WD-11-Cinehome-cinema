@extends('layouts.user')

@section('title', 'Chi tiết lịch chiếu - CineHome')

@section('content')
    @php
        $posterUrl = asset('storage/movies/' . $suatChieu->phim->poster);
        $genres = $suatChieu->phim->genres->pluck('ten_the_loai')->filter()->take(3)->join(', ');
        $startsAt = \Carbon\Carbon::parse($suatChieu->thoi_gian_chieu);
        $endsAt = $suatChieu->thoi_gian_ket_thuc
            ? \Carbon\Carbon::parse($suatChieu->thoi_gian_ket_thuc)
            : $startsAt->copy()->addMinutes($suatChieu->phim->thoi_luong);
    @endphp

    <section class="schedule-detail-page">
        <div class="schedule-detail-backdrop" style="background-image: url('{{ $posterUrl }}');"></div>

        <main class="container-fluid px-5 schedule-detail-main">
            <a href="{{ route('user.showtimes.index', ['rap_chieu_phim_id' => $suatChieu->rap_chieu_phim_id]) }}"
                class="schedule-detail-back">
                <i class="fa-solid fa-arrow-left"></i>
                Quay lại lịch chiếu
            </a>

            <section class="schedule-detail-shell reveal-on-scroll">
                <div class="schedule-detail-poster">
                    <img src="{{ $posterUrl }}" alt="{{ $suatChieu->phim->ten_phim }}">
                    <span>{{ $suatChieu->phim->gioi_han_tuoi }}</span>
                </div>

                <div class="schedule-detail-content">
                    <span class="schedule-kicker">
                        <i class="fa-solid fa-ticket"></i>
                        Suất chiếu đã chọn
                    </span>
                    <h1>{{ $suatChieu->phim->ten_phim }}</h1>
                    <p>{{ \Illuminate\Support\Str::limit($suatChieu->phim->mo_ta, 220) }}</p>

                    <div class="schedule-detail-info-grid">
                        <div>
                            <i class="fa-solid fa-calendar-day"></i>
                            <small>Ngày chiếu</small>
                            <strong>{{ $startsAt->format('d/m/Y') }}</strong>
                        </div>
                        <div>
                            <i class="fa-solid fa-clock"></i>
                            <small>Thời gian</small>
                            <strong>{{ $startsAt->format('H:i') }} - {{ $endsAt->format('H:i') }}</strong>
                        </div>
                        <div>
                            <i class="fa-solid fa-building"></i>
                            <small>Rạp</small>
                            <strong>{{ $suatChieu->rapChieuPhim?->ten_rap ?? 'CineHome' }}</strong>
                        </div>
                        <div>
                            <i class="fa-solid fa-couch"></i>
                            <small>Phòng</small>
                            <strong>{{ $suatChieu->phongChieu?->ten_phong ?? 'Phòng chiếu' }}</strong>
                        </div>
                        <div>
                            <i class="fa-solid fa-ticket"></i>
                            <small>Giá vé</small>
                            <strong>{{ number_format((float) $suatChieu->gia_ve_cuoi_cung, 0, ',', '.') }}đ</strong>
                        </div>
                        <div>
                            <i class="fa-solid fa-tags"></i>
                            <small>Thể loại</small>
                            <strong>{{ $genres ?: 'Điện ảnh' }}</strong>
                        </div>
                    </div>

                    <div class="schedule-detail-location">
                        <i class="fa-solid fa-location-dot"></i>
                        <span>{{ $suatChieu->rapChieuPhim?->dia_chi ?? 'Địa chỉ rạp đang cập nhật' }}</span>
                    </div>

                    <div class="schedule-detail-actions">
                        <a href="{{ route('dat_ve.chon_ghe', $suatChieu->id) }}"
                            class="booking-link schedule-detail-primary">
                            <i class="fa-solid fa-ticket"></i>
                            Đặt vé ngay
                        </a>
                        <a href="{{ route('user.movies.show', $suatChieu->phim->slug) }}"
                            class="schedule-detail-secondary">
                            <i class="fa-solid fa-circle-info"></i>
                            Xem phim
                        </a>
                    </div>
                </div>
            </section>
        </main>
    </section>
@endsection
