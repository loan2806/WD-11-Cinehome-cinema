@extends('layouts.user')

@section('title', 'Gioi thieu rap - CineHome')

@section('content')
@php
    $rap = $rapChieuPhim ?? ($cinema ?? null);

    if (! $rap && isset($rapChieuPhims) && $rapChieuPhims->count() > 0) {
        $rap = $rapChieuPhims->first();
    }

    if (! $rap && isset($cinemas) && $cinemas->count() > 0) {
        $rap = $cinemas->first();
    }

    $cinemaName = $rap->ten_rap ?? 'CineHome Cinema';
    $cinemaAddress = $rap->dia_chi ?? '123 Duong ABC, Phuong XYZ, Thanh pho Hung Yen';
    $cinemaCity = $rap->thanh_pho ?? 'Hung Yen';
    $cinemaPhone = $rap->so_dien_thoai ?? '1900 1234';
    $cinemaImage = $rap->hinh_anh ?? 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=1600';
    $mapLat = $rap->vi_do ?? '20.852571';
    $mapLng = $rap->kinh_do ?? '106.016998';
    $mapDirectionUrl = "https://www.google.com/maps/dir/?api=1&destination={$mapLat},{$mapLng}";
    $mapEmbedUrl = "https://www.google.com/maps?q={$mapLat},{$mapLng}&z=15&output=embed";
    $showtimeUrl = route('user.showtimes.index', $rap ? ['rap_chieu_phim_id' => $rap->id] : []);

    $stats = [
        ['icon' => 'fa-solid fa-door-open', 'value' => '5', 'label' => 'Phòng chiếu'],
        ['icon' => 'fa-solid fa-couch', 'value' => '300+', 'label' => 'Ghế ngồi'],
        ['icon' => 'fa-solid fa-ticket', 'value' => ($showtimeCount ?? 0) > 0 ? $showtimeCount . '+' : '20+', 'label' => 'Suất chiếu/ngày'],
        ['icon' => 'fa-solid fa-award', 'value' => '2018', 'label' => 'Năm hoạt động'],
    ];

    $reasons = [
        ['icon' => 'fa-solid fa-display', 'title' => 'Màn hình 4K'],
        ['icon' => 'fa-solid fa-volume-high', 'title' => 'Âm thanh Dolby Atmos'],
        ['icon' => 'fa-solid fa-crown', 'title' => 'Ghế VIP cao cấp'],
        ['icon' => 'fa-solid fa-mobile-screen-button', 'title' => 'Đặt vé online nhanh chóng'],
        ['icon' => 'fa-solid fa-square-parking', 'title' => 'Bãi đỗ xe rộng rãi'],
        ['icon' => 'fa-solid fa-burger', 'title' => 'Khu vực ăn uống'],
    ];

    $gallery = [
        ['image' => $cinemaImage, 'title' => 'Sảnh đón khách sang trọng'],
        ['image' => 'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?q=80&w=1200', 'title' => 'Phòng chiếu hiện đại'],
        ['image' => 'https://images.unsplash.com/photo-1485871981521-5b1fd3805eee?q=80&w=1200', 'title' => 'Không gian chờ tiện nghi'],
        ['image' => 'https://images.unsplash.com/photo-1513106580091-1d82408b8cd6?q=80&w=1200', 'title' => 'Khu vực bắp nước'],
    ];

    $faqs = [
        ['question' => 'CineHome có hỗ trợ đặt vé online không?', 'answer' => 'Có, bạn có thể chọn phim, suất chiếu và ghế ngồi trực tiếp trên website.'],
        ['question' => 'Rạp có bãi gửi xe không?', 'answer' => 'Có, CineHome có khu vực gửi xe rộng rãi cho khách hàng.'],
        ['question' => 'Có bán bắp nước và đồ ăn nhẹ không?', 'answer' => 'Có, quầy dịch vụ luôn có nhiều combo tiện lợi cho bạn lựa chọn.'],
        ['question' => 'Rạp mở cửa vào khung giờ nào?', 'answer' => 'Rạp hoạt động mỗi ngày từ 08:00 đến 23:30.'],
    ];
@endphp

<style>
    .cinema-page {
        background: #0b0705;
        color: #fff;
    }

    .cinema-section {
        padding: 70px 0;
    }

    .cinema-card {
        height: 100%;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 28px;
        background: #151515;
        box-shadow: 0 18px 45px rgba(0, 0, 0, 0.25);
    }

    .cinema-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 999px;
        background: rgba(245, 166, 35, 0.14);
        color: #ffd27a;
        font-size: 14px;
        font-weight: 700;
    }

    .cinema-title {
        margin-top: 18px;
        color: #fff;
        font-size: 2.3rem;
        font-weight: 900;
        line-height: 1.25;
    }

    .cinema-text {
        color: #d1d5db;
        font-size: 1rem;
        line-height: 1.9;
    }

    .cinema-hero {
        position: relative;
        display: flex;
        min-height: 88vh;
        align-items: end;
        background-image: linear-gradient(to bottom, rgba(0,0,0,.35), rgba(0,0,0,.78)), url('{{ $cinemaImage }}');
        background-position: center;
        background-size: cover;
    }

    .cinema-hero-content {
        padding: 140px 0 90px;
    }

    .cinema-hero h1 {
        margin: 22px 0;
        font-size: 4rem;
        font-weight: 900;
        line-height: 1.1;
    }

    .cinema-hero p {
        max-width: 760px;
        color: #e5e7eb;
        font-size: 1.05rem;
        line-height: 1.9;
    }

    .cinema-btn-primary,
    .cinema-btn-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        border-radius: 16px;
        padding: 14px 26px;
        font-weight: 800;
        text-decoration: none;
        transition: all .25s ease;
    }

    .cinema-btn-primary {
        background: #f5a623;
        color: #000;
        box-shadow: 0 14px 30px rgba(245, 166, 35, 0.24);
    }

    .cinema-btn-primary:hover {
        background: #ffc04d;
        color: #000;
        transform: translateY(-2px);
    }

    .cinema-btn-secondary {
        border: 1px solid rgba(255, 255, 255, 0.12);
        background: rgba(255, 255, 255, 0.08);
        color: #fff;
    }

    .cinema-btn-secondary:hover {
        background: rgba(255, 255, 255, 0.14);
        color: #fff;
    }

    .cinema-stat-icon,
    .cinema-feature-icon {
        display: flex;
        width: 56px;
        height: 56px;
        align-items: center;
        justify-content: center;
        border-radius: 18px;
        background: rgba(245, 166, 35, 0.14);
        color: #f5a623;
        font-size: 22px;
    }

    .cinema-stat-value {
        margin-top: 12px;
        color: #f5a623;
        font-size: 1.8rem;
        font-weight: 900;
    }

    .cinema-gallery-item {
        position: relative;
        min-height: 250px;
        overflow: hidden;
        border-radius: 28px;
    }

    .cinema-gallery-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .cinema-gallery-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,.8), rgba(0,0,0,.2));
    }

    .cinema-gallery-caption {
        position: absolute;
        right: 24px;
        bottom: 20px;
        left: 24px;
        font-size: 1.1rem;
        font-weight: 800;
    }

    .cinema-map-frame {
        width: 100%;
        min-height: 420px;
        border: 0;
        border-radius: 24px;
    }

    .cinema-cta {
        border: 1px solid rgba(245, 166, 35, 0.18);
        border-radius: 32px;
        background: linear-gradient(135deg, #1a1208, #16110c, #1b1208);
        padding: 50px 24px;
        text-align: center;
    }

    @media (max-width: 991.98px) {
        .cinema-section {
            padding: 56px 0;
        }

        .cinema-hero {
            min-height: auto;
        }

        .cinema-hero-content {
            padding: 120px 0 70px;
        }

        .cinema-hero h1 {
            font-size: 2.7rem;
        }
    }
</style>

<div class="cinema-page">
    <section class="cinema-hero">
        <div class="container cinema-hero-content">
            <div class="row">
                <div class="col-12 col-xl-9">
                    <span class="cinema-badge">
                        <i class="fa-solid fa-star"></i>
                        Trải nghiệm điện ảnh cao cấp tại {{ $cinemaCity }}
                    </span>

                    <h1>{{ $cinemaName }}</h1>

                    <p>
                        Điểm hẹn giải trí hiện đại với không gian sang trọng, công nghệ trình chiếu tiên tiến,
                        dịch vụ chuyên nghiệp và trải nghiệm đặt vé mượt mà dành cho mọi tín đồ điện ảnh.
                    </p>

                    <div class="d-flex flex-wrap gap-3 mt-4">
                        <a href="{{ $showtimeUrl }}" class="cinema-btn-primary">
                            <i class="fa-solid fa-ticket"></i>
                            Đặt vé ngay hôm nay
                        </a>
                        <a href="#gioi-thieu-rap" class="cinema-btn-secondary">
                            <i class="fa-solid fa-circle-info"></i>
                            Khám phá CineHome
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="gioi-thieu-rap" class="cinema-section">
        <div class="container">
            <div class="row g-4 align-items-stretch">
                <div class="col-12 col-lg-7">
                    <div class="cinema-card p-4 p-lg-5">
                    <span class="cinema-badge">
                        <i class="fa-solid fa-film"></i>
                        Giới thiệu CineHome
                    </span>

                    <h2 class="cinema-title">Nơi cảm xúc điện ảnh thăng hoa trong từng khung hình</h2>

                    <p class="cinema-text mt-4">
                        CineHome được xây dựng với mong muốn mang đến một điểm đến điện ảnh chuyên nghiệp,
                        hiện đại và gần gũi. Từ sảnh chờ sang trọng, phòng chiếu chuẩn quốc tế đến dịch vụ
                        chăm sóc khách hàng tận tâm, mọi chi tiết đều được tối ưu để tạo nên một hành trình xem phim trọn vẹn.
                    </p>

                        <div class="row g-3 mt-2">
                            <div class="col-12 col-md-6">
                                <div class="p-4 rounded-4" style="background: rgba(0,0,0,.25); border: 1px solid rgba(255,255,255,.06);">
                                    <div class="text-secondary small">Dia chi</div>
                                    <div class="text-white fw-bold mt-2 lh-lg">
                                        <i class="fa-solid fa-location-dot me-2" style="color:#f5a623;"></i>
                                        {{ $cinemaAddress }}, {{ $cinemaCity }}
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="p-4 rounded-4" style="background: rgba(0,0,0,.25); border: 1px solid rgba(255,255,255,.06);">
                                    <div class="text-secondary small">Liên hệ</div>
                                    <div class="text-white fw-bold mt-2 lh-lg">
                                        <i class="fa-solid fa-phone-volume me-2" style="color:#f5a623;"></i>
                                        {{ $cinemaPhone }}
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="p-4 rounded-4" style="background: rgba(0,0,0,.25); border: 1px solid rgba(255,255,255,.06);">
                                    <div class="text-secondary small">Giờ hoạt động</div>
                                    <div class="text-white fw-bold mt-2 lh-lg">
                                        <i class="fa-solid fa-clock me-2" style="color:#f5a623;"></i>
                                        08:00 - 23:30 mỗi ngày
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-5">
                    <div class="cinema-card overflow-hidden">
                        <img src="{{ $cinemaImage }}" alt="{{ $cinemaName }}" style="width:100%; height:100%; min-height:420px; object-fit:cover;">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="cinema-section pt-0">
        <div class="container">
            <div class="text-center mb-4">
                <span class="cinema-badge"><i class="fa-solid fa-chart-column"></i> Thống kê nổi bật</span>
                <h2 class="cinema-title">CineHome bằng con số</h2>
            </div>

            <div class="row g-3">
                @foreach ($stats as $stat)
                    <div class="col-6 col-lg-3">
                        <div class="cinema-card p-3 p-lg-4 text-center">
                            <div class="cinema-stat-icon mx-auto"><i class="{{ $stat['icon'] }}"></i></div>
                            <div class="cinema-stat-value">{{ $stat['value'] }}</div>
                            <div class="text-white fw-bold">{{ $stat['label'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="cinema-section pt-0">
        <div class="container">
            <div class="text-center mb-4">
                <span class="cinema-badge"><i class="fa-solid fa-gem"></i> Tại sao chọn CineHome</span>
                <h2 class="cinema-title">Trải nghiệm cao cấp tại CineHome</h2>
            </div>

            <div class="row g-3">
                @foreach ($reasons as $reason)
                    <div class="col-6 col-lg-4">
                        <div class="cinema-card p-3 text-center">
                            <div class="cinema-feature-icon mx-auto mb-2"><i class="{{ $reason['icon'] }}"></i></div>
                            <h3 class="h6 fw-bold text-white">{{ $reason['title'] }}</h3>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="cinema-section pt-0">
        <div class="container">
            <div class="text-center mb-5">
                <span class="cinema-badge"><i class="fa-solid fa-images"></i> Hình ảnh rạp</span>
                <h2 class="cinema-title">Khám phá không gian hiện đại tại CineHome</h2>
            </div>

            <div class="row g-4">
                @foreach ($gallery as $item)
                    <div class="col-12 col-md-6">
                        <div class="cinema-gallery-item cinema-card">
                            <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}">
                            <div class="cinema-gallery-overlay"></div>
                            <div class="cinema-gallery-caption">{{ $item['title'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="cinema-section pt-0">
        <div class="container">
            <div class="row g-4 align-items-stretch">
                <div class="col-12 col-xl-7">
                    <div class="cinema-card p-3 p-md-4">
                        <iframe
                            src="{{ $mapEmbedUrl }}"
                            class="cinema-map-frame"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Ban do vi tri {{ $cinemaName }}">
                        </iframe>
                    </div>
                </div>

                <div class="col-12 col-xl-5">
                    <div class="cinema-card p-4 p-lg-5">
                    <span class="cinema-badge">
                        <i class="fa-solid fa-location-crosshairs"></i>
                        Bản đồ và liên hệ
                    </span>

                    <h2 class="cinema-title">Dễ dàng tìm đến CineHome</h2>
                    <p class="cinema-text mt-4">
                        CineHome nằm tại vị trí thuận tiện, dễ di chuyển và phù hợp cho khách đi cùng gia đình,
                        bạn bè hay đồng nghiệp.
                    </p>

                    <div class="d-flex flex-wrap gap-3 mt-4">
                        <a href="{{ $mapDirectionUrl }}" target="_blank" rel="noopener noreferrer" class="cinema-btn-primary">
                            <i class="fa-solid fa-diamond-turn-right"></i>
                            Mở Google Maps
                        </a>
                        <a href="{{ $showtimeUrl }}" class="cinema-btn-secondary">
                            <i class="fa-solid fa-calendar-days"></i>
                            Xem lịch chiếu
                        </a>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="cinema-section pt-0">
        <div class="container">
            <div class="text-center mb-5">
                <span class="cinema-badge"><i class="fa-solid fa-circle-question"></i> FAQ</span>
                <h2 class="cinema-title">Câu hỏi thường gặp</h2>
            </div>

            <div class="row g-4">
                @foreach ($faqs as $faq)
                    <div class="col-12 col-lg-6">
                        <div class="cinema-card p-4">
                            <h3 class="h5 fw-bold text-white">{{ $faq['question'] }}</h3>
                            <p class="cinema-text mt-3 mb-0">{{ $faq['answer'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="cinema-section pt-0 pb-5">
        <div class="container">
            <div class="cinema-cta">
                <span class="cinema-badge">
                    <i class="fa-solid fa-bolt"></i>
                    Sẵn sàng cho suất chiếu tiếp theo?
                </span>
                <h2 class="cinema-title mb-0">Đặt vé ngay hôm nay</h2>
                <p class="cinema-text mx-auto mt-4" style="max-width: 760px;">
                    Khám phá những bộ phim hấp dẫn nhất tại CineHome, chọn chỗ ngồi yêu thích và tận hưởng một đêm điện ảnh trọn vẹn.
                </p>
                <div class="mt-4">
                    <a href="{{ $showtimeUrl }}" class="cinema-btn-primary">
                        <i class="fa-solid fa-ticket"></i>
                        Đặt vé ngay hôm nay
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
