@extends('layouts.user')

@section('title', 'Rạp chiếu - CineHome')

@section('content')
    @php
        use Illuminate\Support\Str;

        $rap = $rapChieuPhim ?? null;
        $cinemaName = $rap->ten_rap ?? 'CineHome Cinema';
        $cinemaAddress = $rap->dia_chi ?? '123 Đường Nguyễn Văn Linh, Quận 7';
        $cinemaCity = $rap->thanh_pho ?? 'TP.HCM';
        $cinemaPhone = $rap->so_dien_thoai ?? '1900 1234';
        $mapLat = $rap->vi_do ?? '10.7290257';
        $mapLng = $rap->kinh_do ?? '106.6968571';
        $mapDirectionUrl = "https://www.google.com/maps/dir/?api=1&destination={$mapLat},{$mapLng}";
        $mapEmbedUrl = "https://www.google.com/maps?q={$mapLat},{$mapLng}&z=15&output=embed";
        $showtimeUrl = route('user.showtimes.index', $rap ? ['rap_chieu_phim_id' => $rap->id] : []);

        $fallbackExterior = 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=1600&auto=format&fit=crop';
        $fallbackLobby = 'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?q=80&w=1300&auto=format&fit=crop';
        $fallbackSeats = 'https://images.unsplash.com/photo-1505686994434-e3cc5abf1330?q=80&w=1300&auto=format&fit=crop';
        $fallbackFood = 'https://images.unsplash.com/photo-1585647347483-22b66260dfff?q=80&w=1300&auto=format&fit=crop';
        $fallbackWaiting = 'https://images.unsplash.com/photo-1485871981521-5b1fd3805eee?q=80&w=1300&auto=format&fit=crop';
        $fallbackPoster = 'https://placehold.co/300x450/1e293b/94a3b8?text=No+Poster';

        $resolveImage = function ($path, $fallback) {
            if (empty($path)) {
                return $fallback;
            }

            if (Str::startsWith($path, ['http://', 'https://'])) {
                return $path;
            }

            // Làm sạch path loại bỏ tiền tố lặp
            $cleanPath = str_replace(['storage/', 'public/'], '', ltrim($path, '/'));

            if (file_exists(public_path('storage/' . $cleanPath))) {
                return asset('storage/' . $cleanPath);
            }

            if (file_exists(public_path($cleanPath))) {
                return asset($cleanPath);
            }

            return $fallback;
        };

        $cinemaImage = $resolveImage($rap->hinh_anh ?? null, $fallbackExterior);
        $seatCountDisplay = ($seatCount ?? 0) > 0 ? number_format($seatCount) : '300+';
        $roomCountDisplay = ($roomCount ?? 0) > 0 ? number_format($roomCount) : '5';
        $activeRoomDisplay = ($activeRoomCount ?? 0) > 0 ? number_format($activeRoomCount) : $roomCountDisplay;
        $showtimeDisplay = ($showtimeCount ?? 0) > 0 ? number_format($showtimeCount) : '20+';
        $movieDisplay = ($movieCount ?? 0) > 0 ? number_format($movieCount) : (($hotMovies ?? collect())->count() ?: '4+');

        $stats = [
            ['icon' => 'fa-solid fa-door-open', 'value' => $roomCountDisplay, 'label' => 'Phòng chiếu'],
            ['icon' => 'fa-solid fa-couch', 'value' => $seatCountDisplay, 'label' => 'Ghế ngồi'],
            ['icon' => 'fa-solid fa-ticket', 'value' => $showtimeDisplay, 'label' => 'Suất hôm nay'],
            ['icon' => 'fa-solid fa-film', 'value' => $movieDisplay, 'label' => 'Phim đang có'],
        ];

        $features = [
            ['icon' => 'fa-solid fa-display', 'title' => 'Màn hình sắc nét', 'desc' => 'Không gian chiếu được tối ưu ánh sáng, tầm nhìn và độ tương phản.'],
            ['icon' => 'fa-solid fa-volume-high', 'title' => 'Âm thanh sống động', 'desc' => 'Hệ thống âm thanh vòm cho cảm giác nhập vai trong từng cảnh phim.'],
            ['icon' => 'fa-solid fa-crown', 'title' => 'Ghế ngồi êm ái', 'desc' => 'Khoảng cách ghế thoải mái, phù hợp cả các suất chiếu dài.'],
            ['icon' => 'fa-solid fa-mobile-screen-button', 'title' => 'Đặt vé nhanh', 'desc' => 'Chọn phim, chọn ghế, thêm combo và thanh toán trong một luồng mượt.'],
            ['icon' => 'fa-solid fa-square-parking', 'title' => 'Di chuyển thuận tiện', 'desc' => 'Vị trí dễ tìm, có hướng dẫn bản đồ và thông tin liên hệ rõ ràng.'],
            ['icon' => 'fa-solid fa-burger', 'title' => 'Combo bắp nước', 'desc' => 'Nhiều lựa chọn đồ ăn nhẹ để buổi xem phim trọn vẹn hơn.'],
        ];

        $gallery = [
            ['image' => $cinemaImage, 'title' => 'Sảnh đón khách CineHome', 'wide' => true],
            ['image' => $fallbackLobby, 'title' => 'Phòng chiếu hiện đại', 'wide' => false],
            ['image' => $fallbackSeats, 'title' => 'Ghế ngồi thoải mái', 'wide' => false],
            ['image' => $fallbackFood, 'title' => 'Quầy combo bắp nước', 'wide' => false],
            ['image' => $fallbackWaiting, 'title' => 'Không gian chờ thư giãn', 'wide' => true],
        ];

        $moviePoster = function ($movie) use ($fallbackPoster) {
            $rawPoster = $movie->poster ?? '';
            if (empty($rawPoster)) {
                return $fallbackPoster;
            }

            if (Str::startsWith($rawPoster, ['http://', 'https://'])) {
                return $rawPoster;
            }

            $cleanPoster = str_replace(['storage/movies/', 'storage/', 'movies/movies/'], '', ltrim($rawPoster, '/'));
            if (!Str::startsWith($cleanPoster, 'movies/')) {
                $cleanPoster = 'movies/' . $cleanPoster;
            }

            if (file_exists(public_path('storage/' . $cleanPoster))) {
                return asset('storage/' . $cleanPoster);
            }

            return $fallbackPoster;
        };
    @endphp

    <div class="cinema-landing" lang="vi" spellcheck="false">
        <section class="cinema-hero-new" style="--cinema-hero-image: url('{{ $cinemaImage }}')">
            <div class="cinema-hero-copy">
                <span class="cinema-eyebrow">
                    <i class="fa-solid fa-location-dot"></i>
                    Rạp chiếu CineHome tại {{ $cinemaCity }}
                </span>
                <h1>{{ $cinemaName }}</h1>
                <p>
                    Không gian xem phim hiện đại, dịch vụ gọn gàng và luồng đặt vé online được tối ưu để bạn đến rạp
                    nhanh hơn, xem phim thoải mái hơn.
                </p>

                <div class="cinema-hero-actions">
                    <a href="{{ $showtimeUrl }}" class="cinema-primary-action">
                        <i class="fa-solid fa-ticket"></i>
                        Xem lịch chiếu
                    </a>
                    <a href="{{ $mapDirectionUrl }}" target="_blank" rel="noopener noreferrer" class="cinema-secondary-action">
                        <i class="fa-solid fa-diamond-turn-right"></i>
                        Chỉ đường
                    </a>
                </div>
            </div>

            <aside class="cinema-hero-panel">
                <div>
                    <span>Địa chỉ</span>
                    <strong>{{ $cinemaAddress }}</strong>
                    <small>{{ $cinemaCity }}</small>
                </div>
                <div>
                    <span>Liên hệ</span>
                    <strong>{{ $cinemaPhone }}</strong>
                    <small>08:00 - 23:30 mỗi ngày</small>
                </div>
                <div>
                    <span>Phòng hoạt động</span>
                    <strong>{{ $activeRoomDisplay }}/{{ $roomCountDisplay }}</strong>
                    <small>Sẵn sàng phục vụ suất chiếu hôm nay</small>
                </div>
            </aside>
        </section>

        <section class="cinema-stat-strip" aria-label="Thống kê rạp">
            @foreach ($stats as $stat)
                <div class="cinema-stat-card">
                    <span><i class="{{ $stat['icon'] }}"></i></span>
                    <strong>{{ $stat['value'] }}</strong>
                    <small>{{ $stat['label'] }}</small>
                </div>
            @endforeach
        </section>

        <section class="cinema-about-grid">
            <div class="cinema-about-copy">
                <span class="cinema-eyebrow">
                    <i class="fa-solid fa-film"></i>
                    Trải nghiệm tại rạp
                </span>
                <h2>Được thiết kế cho những buổi xem phim thật đã.</h2>
                <p>
                    CineHome kết hợp phòng chiếu tiện nghi, âm thanh sống động, dịch vụ đồ ăn nhanh và hệ thống đặt vé
                    rõ ràng. Từ lúc chọn suất đến khi nhận vé, mọi thao tác đều được giữ gọn và dễ hiểu.
                </p>
                <div class="cinema-info-list">
                    <div>
                        <i class="fa-solid fa-map-location-dot"></i>
                        <span>{{ $cinemaAddress }}, {{ $cinemaCity }}</span>
                    </div>
                    <div>
                        <i class="fa-solid fa-phone-volume"></i>
                        <span>{{ $cinemaPhone }}</span>
                    </div>
                    <div>
                        <i class="fa-solid fa-clock"></i>
                        <span>08:00 - 23:30 mỗi ngày</span>
                    </div>
                </div>
            </div>

            <figure class="cinema-about-image">
                <img src="{{ $cinemaImage }}" alt="{{ $cinemaName }}" onerror="this.onerror=null; this.src='{{ $fallbackExterior }}';">
            </figure>
        </section>

        <section class="cinema-feature-section">
            <div class="cinema-section-head">
                <div>
                    <span>Tiện ích nổi bật</span>
                    <h2>Lý do nên chọn CineHome</h2>
                </div>
                <a href="{{ route('dat_ve.chon_phim') }}">
                    Đặt vé nhanh
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <div class="cinema-feature-grid">
                @foreach ($features as $feature)
                    <article class="cinema-feature-card">
                        <span><i class="{{ $feature['icon'] }}"></i></span>
                        <h3>{{ $feature['title'] }}</h3>
                        <p>{{ $feature['desc'] }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        @if (($rooms ?? collect())->count() > 0)
            <section class="cinema-room-section">
                <div class="cinema-section-head">
                    <div>
                        <span>Phòng chiếu</span>
                        <h2>Không gian đang vận hành</h2>
                    </div>
                </div>

                <div class="cinema-room-grid">
                    @foreach ($rooms as $room)
                        <article class="cinema-room-card">
                            <div>
                                <span>{{ strtoupper($room->loai_phong ?? '2D') }}</span>
                                <h3>{{ $room->ten_phong }}</h3>
                            </div>
                            <div class="cinema-room-meta">
                                <span><i class="fa-solid fa-couch"></i>{{ number_format($room->suc_chua ?? 0) }} ghế</span>
                                <span><i class="fa-solid fa-circle"></i>{{ \App\Models\PhongChieu::TRANG_THAI[$room->trang_thai] ?? $room->trang_thai }}</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if (($hotMovies ?? collect())->count() > 0)
            <section class="cinema-movie-section">
                <div class="cinema-section-head">
                    <div>
                        <span>Phim tại rạp</span>
                        <h2>Đang được quan tâm</h2>
                    </div>
                    <a href="{{ route('user.phims.index') }}">
                        Xem tất cả phim
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <div class="cinema-movie-rail">
                    @foreach ($hotMovies as $movie)
                        <a href="{{ route('user.movies.show', $movie->slug) }}" class="cinema-movie-card">
                            <img src="{{ $moviePoster($movie) }}" alt="{{ $movie->ten_phim }}" onerror="this.onerror=null; this.src='{{ $fallbackPoster }}';">
                            <div>
                                <h3>{{ $movie->ten_phim }}</h3>
                                <p>
                                    {{ $movie->genres?->pluck('ten_the_loai')->take(2)->join(' • ') ?: 'Đang cập nhật' }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="cinema-gallery-section">
            <div class="cinema-section-head">
                <div>
                    <span>Hình ảnh rạp</span>
                    <h2>Không gian CineHome</h2>
                </div>
            </div>

            <div class="cinema-gallery-grid">
                @foreach ($gallery as $item)
                    <figure class="cinema-gallery-card {{ $item['wide'] ? 'wide' : '' }}">
                        <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" onerror="this.onerror=null; this.src='{{ $fallbackExterior }}';">
                        <figcaption>{{ $item['title'] }}</figcaption>
                    </figure>
                @endforeach
            </div>
        </section>

        <section class="cinema-map-section">
            <div class="cinema-map-card">
                <iframe
                    src="{{ $mapEmbedUrl }}"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Bản đồ vị trí {{ $cinemaName }}">
                </iframe>
            </div>

            <div class="cinema-map-copy">
                <span class="cinema-eyebrow">
                    <i class="fa-solid fa-location-crosshairs"></i>
                    Bản đồ và liên hệ
                </span>
                <h2>Dễ dàng tìm đến CineHome.</h2>
                <p>
                    Rạp nằm tại vị trí thuận tiện, phù hợp cho các buổi xem phim cùng gia đình, bạn bè hoặc đồng nghiệp.
                    Bạn có thể mở bản đồ để được chỉ đường trực tiếp.
                </p>
                <div class="cinema-hero-actions">
                    <a href="{{ $mapDirectionUrl }}" target="_blank" rel="noopener noreferrer" class="cinema-primary-action">
                        <i class="fa-solid fa-diamond-turn-right"></i>
                        Mở Google Maps
                    </a>
                    <a href="{{ $showtimeUrl }}" class="cinema-secondary-action">
                        <i class="fa-solid fa-calendar-days"></i>
                        Xem lịch chiếu
                    </a>
                </div>
            </div>
        </section>

        <section class="cinema-cta-new">
            <span class="cinema-eyebrow">
                <i class="fa-solid fa-bolt"></i>
                Sẵn sàng cho suất chiếu tiếp theo?
            </span>
            <h2>Chọn phim, chọn ghế và đến rạp thật gọn.</h2>
            <p>
                Khám phá những bộ phim hấp dẫn nhất tại CineHome và đặt vé online trước khi đến rạp.
            </p>
            <a href="{{ $showtimeUrl }}" class="cinema-primary-action">
                <i class="fa-solid fa-ticket"></i>
                Đặt vé ngay
            </a>
        </section>
    </div>
@endsection