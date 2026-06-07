@extends('layouts.user')

@section('title', 'Giới thiệu rạp - CineHome')

@section('content')
@php
    $cinemaName = 'CineHome Cinema';
    $cinemaAddress = '123 Đường ABC, Phường XYZ, Thành phố Hưng Yên';
    $cinemaCity = 'Hưng Yên';
    $cinemaPhone = '1900 1234';
    $cinemaImage = 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=1600';
    $mapLat = '20.852571';
    $mapLng = '106.016998';
    $mapDirectionUrl = "https://www.google.com/maps/dir/?api=1&destination={$mapLat},{$mapLng}";
    $mapEmbedUrl = "https://www.google.com/maps?q={$mapLat},{$mapLng}&z=15&output=embed";

    $stats = [
        ['icon' => 'fa-solid fa-door-open', 'value' => '5', 'label' => 'Phòng chiếu'],
        ['icon' => 'fa-solid fa-couch', 'value' => '300+', 'label' => 'Ghế ngồi'],
        ['icon' => 'fa-solid fa-ticket', 'value' => $showtimeCount > 0 ? $showtimeCount . '+' : '20+', 'label' => 'Suất chiếu/ngày'],
        ['icon' => 'fa-solid fa-award', 'value' => '2018', 'label' => 'Năm hoạt động'],
    ];

    $reasons = [
        ['icon' => 'fa-solid fa-display', 'title' => 'Màn hình 4K', 'desc' => 'Hình ảnh sắc nét, sống động trong từng khung hình.'],
        ['icon' => 'fa-solid fa-volume-high', 'title' => 'Âm thanh Dolby Atmos', 'desc' => 'Âm thanh đa chiều chân thực, bùng nổ cảm xúc.'],
        ['icon' => 'fa-solid fa-crown', 'title' => 'Ghế VIP cao cấp', 'desc' => 'Ghế ngồi êm ái, khoảng cách rộng rãi và thoải mái.'],
        ['icon' => 'fa-solid fa-mobile-screen-button', 'title' => 'Đặt vé online nhanh chóng', 'desc' => 'Chọn phim và ghế chỉ với vài thao tác đơn giản.'],
        ['icon' => 'fa-solid fa-square-parking', 'title' => 'Bãi đỗ xe rộng rãi', 'desc' => 'Thuận tiện cho cả xe máy và ô tô.'],
        ['icon' => 'fa-solid fa-burger', 'title' => 'Khu vực ăn uống', 'desc' => 'Bắp nước, đồ ăn nhẹ và không gian chờ hiện đại.'],
    ];

    $gallery = [
        ['image' => $cinemaImage, 'title' => 'Sảnh đón khách sang trọng'],
        ['image' => 'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?q=80&w=1200', 'title' => 'Phòng chiếu hiện đại'],
        ['image' => 'https://images.unsplash.com/photo-1485871981521-5b1fd3805eee?q=80&w=1200', 'title' => 'Không gian chờ tiện nghi'],
        ['image' => 'https://images.unsplash.com/photo-1513106580091-1d82408b8cd6?q=80&w=1200', 'title' => 'Khu vực bắp nước'],
    ];

    $timeline = [
        ['year' => '2018', 'title' => 'Khởi đầu CineHome', 'desc' => 'CineHome ra đời với định hướng xây dựng không gian điện ảnh hiện đại và gần gũi.'],
        ['year' => '2020', 'title' => 'Nâng cấp công nghệ', 'desc' => 'Đầu tư mạnh vào màn hình 4K và hệ thống âm thanh Dolby Atmos.'],
        ['year' => '2023', 'title' => 'Tối ưu trải nghiệm', 'desc' => 'Hoàn thiện đặt vé online, nâng cấp khu vực chờ và quầy dịch vụ.'],
        ['year' => '2026', 'title' => 'Chuẩn cao cấp', 'desc' => 'Mang đến trải nghiệm điện ảnh chỉn chu, chuyên nghiệp và tiện lợi hơn.'],
    ];

    $testimonials = [
        ['name' => 'Ngọc Anh', 'role' => 'Sinh viên', 'content' => 'Rạp đẹp, sạch sẽ và đặt vé rất nhanh. Mình cực kỳ thích âm thanh ở đây.'],
        ['name' => 'Minh Quân', 'role' => 'Nhân viên văn phòng', 'content' => 'Ghế ngồi thoải mái, không gian sang và xem phim cuối tuần rất thích hợp.'],
        ['name' => 'Gia Hân', 'role' => 'Khách hàng thân thiết', 'content' => 'Từ đặt vé đến vào rạp đều mượt, cảm giác rất chuyên nghiệp và hiện đại.'],
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
        color: #ffffff;
    }

    .cinema-section {
        padding: 70px 0;
    }

    .cinema-card {
        background: #151515;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 28px;
        box-shadow: 0 18px 45px rgba(0, 0, 0, 0.25);
        transition: all 0.3s ease;
        height: 100%;
    }

    .cinema-card:hover {
        transform: translateY(-5px);
        border-color: rgba(245, 166, 35, 0.35);
        box-shadow: 0 24px 55px rgba(0, 0, 0, 0.32);
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
        font-size: 2.3rem;
        font-weight: 900;
        line-height: 1.25;
        margin-top: 18px;
        color: #ffffff;
    }

    .cinema-text {
        color: #d1d5db;
        line-height: 1.9;
        font-size: 1rem;
    }

    .cinema-hero {
        position: relative;
        min-height: 88vh;
        display: flex;
        align-items: end;
        background-size: cover;
        background-position: center;
        background-image: linear-gradient(to bottom, rgba(0,0,0,0.35), rgba(0,0,0,0.78)), url('{{ $cinemaImage }}');
    }

    .cinema-hero-content {
        padding: 140px 0 90px;
    }

    .cinema-hero h1 {
        font-size: 4rem;
        font-weight: 900;
        line-height: 1.1;
        margin: 22px 0;
    }

    .cinema-hero p {
        max-width: 760px;
        color: #e5e7eb;
        line-height: 1.9;
        font-size: 1.05rem;
    }

    .cinema-btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        background: #f5a623;
        color: #000;
        border-radius: 16px;
        padding: 14px 26px;
        font-weight: 800;
        text-decoration: none;
        box-shadow: 0 14px 30px rgba(245, 166, 35, 0.24);
        transition: all 0.3s ease;
    }

    .cinema-btn-primary:hover {
        color: #000;
        background: #ffc04d;
        transform: translateY(-2px);
    }

    .cinema-btn-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        background: rgba(255, 255, 255, 0.08);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 16px;
        padding: 14px 26px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .cinema-btn-secondary:hover {
        color: #fff;
        background: rgba(255, 255, 255, 0.14);
    }

    .cinema-stat {
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 24px;
        padding: 22px 16px;
    }

    .cinema-stat-icon,
    .cinema-feature-icon {
        width: 56px;
        height: 56px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(245, 166, 35, 0.14);
        color: #f5a623;
        font-size: 22px;
    }

    .cinema-stat-value {
        color: #f5a623;
        font-size: 2rem;
        font-weight: 900;
        margin-top: 18px;
    }

    .cinema-stat-label {
        color: #fff;
        font-size: 1.05rem;
        font-weight: 700;
        margin-top: 8px;
    }

    .cinema-stat-desc {
        color: #c9c9c9;
        margin-top: 10px;
        line-height: 1.8;
    }

    .cinema-gallery-item {
        position: relative;
        overflow: hidden;
        border-radius: 28px;
        min-height: 250px;
    }

    .cinema-gallery-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }

    .cinema-gallery-item:hover img {
        transform: scale(1.06);
    }

    .cinema-gallery-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.8), rgba(0,0,0,0.2));
    }

    .cinema-gallery-caption {
        position: absolute;
        left: 24px;
        bottom: 20px;
        right: 24px;
        font-size: 1.1rem;
        font-weight: 800;
    }

    .cinema-timeline-item {
        position: relative;
        padding-left: 32px;
        border-left: 2px solid rgba(245, 166, 35, 0.25);
    }

    .cinema-timeline-dot {
        position: absolute;
        left: -9px;
        top: 8px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #f5a623;
        box-shadow: 0 0 0 6px rgba(245, 166, 35, 0.12);
    }

    .cinema-faq-item {
        background: #151515;
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 24px;
        padding: 24px;
        height: 100%;
    }

    .cinema-map-frame {
        width: 100%;
        min-height: 420px;
        border: 0;
        border-radius: 24px;
    }

.cinema-cta {
        background: linear-gradient(135deg, #1a1208, #16110c, #1b1208);
        border: 1px solid rgba(245, 166, 35, 0.18);
        border-radius: 32px;
        padding: 50px 24px;
        text-align: center;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.28);
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

        .cinema-title {
            font-size: 1.9rem;
        }
    }

    @media (max-width: 575.98px) {
        .cinema-hero h1 {
            font-size: 2.2rem;
        }

        .cinema-title {
            font-size: 1.65rem;
        }

        .cinema-card,
        .cinema-cta {
            border-radius: 24px;
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
                        <a href="{{ route('user.showtimes.index', ['cinema_id' => $rapChieuPhim->id]) }}" class="cinema-btn-primary">
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
                            CineHome được xây dựng với mong muốn mang đến một điểm đến điện ảnh chuyên nghiệp, hiện đại và giàu cảm xúc.
                            Từ sảnh chờ sang trọng, phòng chiếu chuẩn quốc tế đến dịch vụ chăm sóc khách hàng tận tâm, mọi chi tiết đều được tối ưu
                            để tạo nên một hành trình xem phim trọn vẹn.
                        </p>

                        <p class="cinema-text mt-3">
                            Không chỉ là nơi thưởng thức phim bom tấn, CineHome còn là không gian giải trí cuối tuần, nơi bạn có thể thư giãn,
                            gặp gỡ bạn bè và tận hưởng những khoảnh khắc đáng nhớ trong bầu không khí cao cấp và ấm cúng.
                        </p>

                        <div class="row g-3 mt-2">
                            <div class="col-12 col-md-6">
                                <div class="p-4 rounded-4" style="background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.06);">
                                    <div class="text-secondary small">Địa chỉ</div>
                                    <div class="text-white fw-bold mt-2 lh-lg">
                                        <i class="fa-solid fa-location-dot me-2" style="color:#f5a623;"></i>
                                        {{ $cinemaAddress }}, {{ $cinemaCity }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="p-4 rounded-4" style="background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.06);">
                                    <div class="text-secondary small">Liên hệ</div>
                                    <div class="text-white fw-bold mt-2 lh-lg">
                                        <i class="fa-solid fa-phone-volume me-2" style="color:#f5a623;"></i>
                                        {{ $cinemaPhone }}
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 rounded-4" style="background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.06);">
                                <div class="text-secondary small">Giờ hoạt động</div>
                                <div class="text-white fw-bold mt-2 lh-lg">
                                    <i class="fa-solid fa-clock me-2" style="color:#f5a623;"></i>
                                    08:00 - 23:30 mỗi ngày
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
                            <div class="cinema-stat-icon mx-auto mb-2"><i class="{{ $stat['icon'] }}"></i></div>
                            <div class="cinema-stat-value mt-0" style="font-size:1.75rem;">{{ $stat['value'] }}</div>
                            <div class="cinema-stat-label" style="font-size:0.88rem;">{{ $stat['label'] }}</div>
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
                <span class="cinema-badge"><i class="fa-solid fa-images"></i> Bộ sưu tập hình ảnh rạp</span>
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
            <div class="text-center mb-5">
                <span class="cinema-badge"><i class="fa-solid fa-timeline"></i> Timeline phát triển</span>
                <h2 class="cinema-title">Hành trình phát triển của CineHome</h2>
            </div>

            <div class="row g-4">
                @foreach ($timeline as $item)
                    <div class="col-12 col-md-6">
                        <div class="cinema-card p-4 p-lg-5 cinema-timeline-item">
                            <span class="cinema-timeline-dot"></span>
                            <div class="small fw-bold" style="color:#f5a623;">{{ $item['year'] }}</div>
                            <h3 class="h4 fw-bold text-white mt-3">{{ $item['title'] }}</h3>
                            <p class="cinema-text mt-3 mb-0">{{ $item['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="cinema-section pt-0">
        <div class="container">
            <div class="text-center mb-5">
                <span class="cinema-badge"><i class="fa-solid fa-quote-left"></i> Đánh giá khách hàng</span>
                <h2 class="cinema-title">Khách hàng nói gì về CineHome</h2>
            </div>

            <div class="row g-4">
                @foreach ($testimonials as $item)
                    <div class="col-12 col-lg-4">
                        <div class="cinema-card p-4 p-lg-5">
                            <div class="mb-3" style="color:#f5a623;">
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                            </div>
                            <p class="cinema-text">“{{ $item['content'] }}”</p>
                            <div class="mt-4">
                                <div class="fw-bold text-white">{{ $item['name'] }}</div>
                                <div class="text-secondary small">{{ $item['role'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
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
                        <div class="cinema-faq-item">
                            <h3 class="h5 fw-bold text-white">{{ $faq['question'] }}</h3>
                            <p class="cinema-text mt-3 mb-0">{{ $faq['answer'] }}</p>
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
                            title="Bản đồ vị trí {{ $cinemaName }}">
                        </iframe>
                    </div>
                </div>

                <div class="col-12 col-xl-5">
                    <div class="cinema-card p-4 p-lg-5">
                        <span class="cinema-badge">
                            <i class="fa-solid fa-location-crosshairs"></i>
                            Bản đồ & thông tin liên hệ
                        </span>

                        <h2 class="cinema-title">Dễ dàng tìm đến CineHome</h2>
                        <p class="cinema-text mt-4">
                            CineHome nằm tại vị trí thuận tiện, dễ di chuyển và phù hợp cho cả khách đi cùng gia đình, bạn bè hay đồng nghiệp.
                            Bạn có thể mở bản đồ để xem đường đi nhanh nhất đến rạp.
                        </p>

                        <div class="mt-4 d-grid gap-3">
                            <div class="p-4 rounded-4" style="background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.06);">
                                <div class="text-secondary small">Địa chỉ</div>
                                <div class="text-white fw-bold mt-2 lh-lg">
                                    <i class="fa-solid fa-location-dot me-2" style="color:#f5a623;"></i>
                                    {{ $cinemaAddress }}, {{ $cinemaCity }}
                                </div>
                            </div>


                        </div>

                        <div class="d-flex flex-wrap gap-3 mt-4">
                            <a href="{{ $mapDirectionUrl }}" target="_blank" rel="noopener noreferrer" class="cinema-btn-primary">
                                <i class="fa-solid fa-diamond-turn-right"></i>
                                Mở Google Maps
                            </a>
                            <a href="{{ route('user.showtimes.index', ['cinema_id' => $rapChieuPhim->id]) }}" class="cinema-btn-secondary">
                                <i class="fa-solid fa-calendar-days"></i>
                                Xem lịch chiếu
                            </a>
                        </div>
                    </div>
                </div>
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
                    <a href="{{ route('user.showtimes.index', ['cinema_id' => $rapChieuPhim->id]) }}" class="cinema-btn-primary">
                        <i class="fa-solid fa-ticket"></i>
                        Đặt vé ngay hôm nay
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
