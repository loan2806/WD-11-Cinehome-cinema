@extends('layouts.user')

@section('title', $cinema->ten_rap . ' - CineHome')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin="">
    <link rel="stylesheet" href="{{ asset('assets/css/cinema-map.css') }}">
@endpush

@section('content')

<section class="min-h-screen bg-[#0b0705] text-white pt-32 pb-10">
    <div class="max-w-7xl mx-auto px-6">

        <a href="{{ route('user.cinemas.index') }}" class="inline-block mb-6 text-gray-400 hover:text-[#f5a623]">
            <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại danh sách rạp
        </a>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-stretch">

            <div class="cinema-map-show-block">
                <div id="location-denied-banner" class="cinema-map-alert cinema-map-alert--warn is-hidden" role="status">
                    <i class="fa-solid fa-location-crosshairs"></i>
                    <div>
                        <strong>Không lấy được vị trí của bạn.</strong>
                        Bật quyền định vị để xem khoảng cách từ chỗ bạn đến rạp này.
                    </div>
                </div>

                <div id="location-loading" class="cinema-map-loading">
                    <i class="fa-solid fa-spinner fa-spin"></i> Đang lấy vị trí và tải bản đồ…
                </div>

                <div class="cinema-map-layout">
                    <div class="cinema-map-wrap">
                        <div
                            id="cinema-map"
                            class="cinema-map-canvas cinema-map-canvas--show"
                            role="application"
                            aria-label="Bản đồ vị trí rạp {{ $cinema->ten_rap }}"
                        ></div>
                    </div>
                </div>

                <div id="cinema-detail-panel" class="cinema-map-detail" aria-live="polite">
                    <h3 id="detail-name" class="!text-lg">{{ $cinema->ten_rap }}</h3>
                    <p id="detail-address" class="cinema-map-detail-address">{{ $cinema->dia_chi }}@if($cinema->thanh_pho) — {{ $cinema->thanh_pho }}@endif</p>
                    <p id="detail-distance" class="cinema-map-detail-distance">Đang tính khoảng cách…</p>
                    <a id="detail-directions" class="cinema-map-btn-directions @if(!$cinema->vi_do || !$cinema->kinh_do) is-hidden @endif"
                       href="@if($cinema->vi_do && $cinema->kinh_do)https://www.google.com/maps/dir/?api=1&destination={{ $cinema->vi_do }},{{ $cinema->kinh_do }}@else#@endif"
                       target="_blank" rel="noopener noreferrer">
                        <i class="fa-solid fa-diamond-turn-right"></i> Chỉ đường
                    </a>
                </div>
            </div>

            <div class="bg-[#151515] border border-white/10 rounded-3xl p-8 flex flex-col">
                <span class="text-[#f5a623] font-bold mb-3">
                    <i class="fa-solid fa-film mr-2"></i> CineHome Cinema
                </span>

                <h1 class="text-5xl font-extrabold text-[#f5a623] mb-5">
                    {{ $cinema->ten_rap }}
                </h1>

                <p class="text-gray-400 mb-8 leading-relaxed">
                    Rạp chiếu phim hiện đại, không gian rộng rãi, âm thanh sống động,
                    phù hợp để xem phim cùng bạn bè và gia đình.
                </p>

                <div class="space-y-4 mb-8">
                    <div class="bg-black/30 rounded-2xl p-4">
                        <p class="text-gray-400 text-sm mb-1">Địa chỉ</p>
                        <p class="text-white font-bold">
                            <i class="fa-solid fa-location-dot text-[#f5a623] mr-2"></i>
                            {{ $cinema->dia_chi }}
                        </p>
                    </div>

                    <div class="bg-black/30 rounded-2xl p-4">
                        <p class="text-gray-400 text-sm mb-1">Khoảng cách từ bạn</p>
                        <p id="cinema-show-distance" class="cinema-show-distance text-lg">
                            Đang tính khoảng cách…
                        </p>
                    </div>

                    <div class="bg-black/30 rounded-2xl p-4">
                        <p class="text-gray-400 text-sm mb-1">Số điện thoại</p>
                        <p class="text-white font-bold">
                            <i class="fa-solid fa-phone text-[#f5a623] mr-2"></i>
                            {{ $cinema->so_dien_thoai ?? 'Đang cập nhật' }}
                        </p>
                    </div>

                    <div class="bg-black/30 rounded-2xl p-4">
                        <p class="text-gray-400 text-sm mb-1">Khu vực</p>
                        <p class="text-white font-bold">
                            <i class="fa-solid fa-map text-[#f5a623] mr-2"></i>
                            {{ $cinema->thanh_pho ?? 'Đang cập nhật' }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 mb-8">
                    <div class="bg-black/30 rounded-2xl p-4 text-center">
                        <div class="text-2xl font-extrabold text-[#f5a623]">
                            {{ $movieCount ?? 0 }}
                        </div>
                        <div class="text-gray-400 text-sm">Phim đang chiếu</div>
                    </div>

                    <div class="bg-black/30 rounded-2xl p-4 text-center">
                        <div class="text-2xl font-extrabold text-[#f5a623]">
                            {{ $showtimeCount ?? 0 }}
                        </div>
                        <div class="text-gray-400 text-sm">Suất chiếu</div>
                    </div>

                    <div class="bg-black/30 rounded-2xl p-4 text-center">
                        <div class="text-2xl font-extrabold text-[#f5a623]">
                            08:00
                        </div>
                        <div class="text-gray-400 text-sm">Mở cửa</div>
                    </div>
                </div>

                <div class="flex gap-4 mt-auto">
                    <a href="{{ route('user.showtimes.index', ['cinema_id' => $cinema->id]) }}"
                       class="flex-1 text-center bg-[#f5a623] text-black font-extrabold px-6 py-3 rounded-xl hover:bg-[#ffc04d] transition">
                        Xem lịch chiếu
                    </a>

                    <a href="{{ route('user.cinemas.map') }}"
                       class="flex-1 text-center bg-white/10 text-white font-bold px-6 py-3 rounded-xl hover:bg-white/20 transition">
                        Tất cả rạp trên bản đồ
                    </a>
                </div>
            </div>
        </div>

    </div>
</section>

@endsection

@section('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    @php
        $cinemaForMap = [
            'id' => $cinema->id,
            'name' => $cinema->ten_rap,
            'address' => $cinema->dia_chi,
            'city' => $cinema->thanh_pho,
            'latitude' => $cinema->vi_do,
            'longitude' => $cinema->kinh_do,
            'status' => $cinema->status ?? 'active',
        ];
    @endphp
    <script>
        window.CINEMA_MAP_SINGLE_CINEMA = @json($cinemaForMap);
    </script>
    <script src="{{ asset('assets/js/cinema-map.js') }}"></script>
@endsection