@extends('layouts.user')

@section('title', 'Bản đồ rạp - CineHome')

@push('styles')
    {{-- Leaflet: bản đồ nền OpenStreetMap, không cần API key Google --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin="">
    <link rel="stylesheet" href="{{ asset('assets/css/cinema-map.css') }}">
@endpush

@section('content')

<section class="cinema-map-page">
    <div class="cinema-map-inner">
        <header class="cinema-map-header">
            <h1>Bản đồ <span>rạp chiếu</span></h1>
            <p class="cinema-map-lead">
                Cho phép trình duyệt lấy vị trí để sắp xếp <strong>rạp gần bạn</strong>
            </p>
        </header>

        {{-- Thông báo khi user từ chối quyền vị trí --}}
        <div id="location-denied-banner" class="cinema-map-alert cinema-map-alert--warn is-hidden" role="status">
            <i class="fa-solid fa-location-crosshairs"></i>
            <div>
                <strong>Không lấy được vị trí của bạn.</strong>
                Bạn có thể chọn thành phố để lọc danh sách, hoặc xem toàn bộ rạp bên dưới — khoảng cách sẽ hiển thị khi bật lại quyền định vị.
            </div>
        </div>

        <div id="location-loading" class="cinema-map-loading">
            <i class="fa-solid fa-spinner fa-spin"></i> Đang xin quyền và tải dữ liệu rạp…
        </div>

        <div id="location-fallback" class="cinema-map-fallback is-hidden">
            <label for="city-filter">Lọc theo thành phố</label>
            <select id="city-filter" class="cinema-map-select" aria-label="Chọn thành phố">
                <option value="">— Tất cả —</option>
            </select>
        </div>

        <div class="cinema-map-layout">
            <div class="cinema-map-wrap">
                <div id="cinema-map" class="cinema-map-canvas" role="application" aria-label="Bản đồ các rạp"></div>
            </div>

            <aside class="cinema-map-sidebar">
                <h2 id="list-heading" class="cinema-map-sidebar-title">Danh sách rạp</h2>
                <ul id="cinema-list" class="cinema-map-list" aria-labelledby="list-heading"></ul>
            </aside>
        </div>

        <div id="cinema-detail-panel" class="cinema-map-detail is-hidden" aria-live="polite">
            <h3 id="detail-name"></h3>
            <p id="detail-address" class="cinema-map-detail-address"></p>
            <p id="detail-distance" class="cinema-map-detail-distance"></p>
            <a id="detail-directions" class="cinema-map-btn-directions" href="#" target="_blank" rel="noopener noreferrer">
                <i class="fa-solid fa-diamond-turn-right"></i> Chỉ đường
            </a>
        </div>
    </div>
</section>

@endsection

@section('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    <script>
        // URL API JSON — dùng trong cinema-map.js
        window.CINEMA_MAP_API_URL = @json(route('api.cinemas.index'));
    </script>
    <script src="{{ asset('assets/js/cinema-map.js') }}"></script>
@endsection
