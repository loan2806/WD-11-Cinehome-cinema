@extends('layouts.staff')

@section('title', 'Bán vé tại quầy')
@section('page-title', 'Bán vé tại quầy')

@section('content')
<div class="sell-page">
    <div class="sell-header">
        <div>
            <h2>Bán vé tại quầy</h2>
            <p>Chọn suất chiếu để mở sơ đồ ghế và bán vé trực tiếp cho khách hàng.</p>
        </div>

        <div class="sell-icon">
            <i class="fa-solid fa-ticket"></i>
        </div>
    </div>

    <div class="showtime-grid">
        @forelse ($showtimes as $showtime)
            <a href="{{ route('staff.ban-ve.show', $showtime->id) }}" class="showtime-card">
                <div class="movie-icon">
                    <i class="fa-solid fa-film"></i>
                </div>

                <div class="showtime-content">
                    <h3>{{ $showtime->phim->ten_phim ?? 'Không rõ phim' }}</h3>

                    <p>
                        <i class="fa-solid fa-building"></i>
                        {{ $showtime->rapChieuPhim->ten_rap ?? 'Không rõ rạp' }}
                    </p>

                    <p>
                        <i class="fa-solid fa-door-open"></i>
                        {{ $showtime->phongChieu->ten_phong ?? 'Phòng chiếu' }}
                    </p>

                    <div class="showtime-bottom">
                        <span>
                            <i class="fa-solid fa-clock"></i>
                            {{ $showtime->thoi_gian_chieu ? $showtime->thoi_gian_chieu->format('d/m/Y H:i') : '' }}
                        </span>

                        <strong>{{ number_format($showtime->gia_ve, 0, ',', '.') }}đ</strong>
                    </div>
                </div>
            </a>
        @empty
            <div class="empty-box">
                <i class="fa-solid fa-calendar-xmark"></i>
                <p>Chưa có suất chiếu sắp tới.</p>
            </div>
        @endforelse
    </div>
</div>

<style>
    .sell-page { animation: fadeIn .35s ease; }

    .sell-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .sell-header h2 {
        margin: 0;
        color: #fff;
        font-size: 30px;
        font-weight: 900;
    }

    .sell-header p {
        margin-top: 8px;
        color: #aaa;
    }

    .sell-icon {
        width: 64px;
        height: 64px;
        display: grid;
        place-items: center;
        border-radius: 22px;
        color: #f5a623;
        background: radial-gradient(circle at top, rgba(245,166,35,.28), rgba(245,166,35,.08));
        box-shadow: 0 0 30px rgba(245,166,35,.18);
        transition: all .3s ease;
    }

    .sell-icon:hover {
        transform: translateY(-4px) scale(1.04);
        box-shadow: 0 0 42px rgba(245,166,35,.3);
    }

    .showtime-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 22px;
    }

    .showtime-card {
        display: flex;
        gap: 18px;
        padding: 24px;
        border-radius: 30px;
        text-decoration: none;
        color: #fff;
        background: linear-gradient(145deg, #171717, #101010);
        border: 1px solid rgba(245,166,35,.26);
        box-shadow: 0 20px 55px rgba(0,0,0,.28);
        transition: all .32s ease;
    }

    .showtime-card:hover {
        transform: translateY(-6px);
        border-color: rgba(245,166,35,.65);
        box-shadow: 0 26px 70px rgba(0,0,0,.45), 0 0 28px rgba(245,166,35,.12);
    }

    .movie-icon {
        width: 54px;
        height: 54px;
        flex-shrink: 0;
        display: grid;
        place-items: center;
        border-radius: 20px;
        color: #f5a623;
        background: rgba(245,166,35,.1);
        transition: all .3s ease;
    }

    .showtime-card:hover .movie-icon {
        transform: rotate(-4deg) scale(1.06);
        background: rgba(245,166,35,.18);
    }

    .showtime-content h3 {
        margin: 0 0 12px;
        color: #fff;
        font-size: 18px;
        font-weight: 900;
    }

    .showtime-content p {
        margin: 8px 0;
        color: #aaa;
        font-size: 14px;
    }

    .showtime-content p i {
        width: 18px;
        color: #f5a623;
    }

    .showtime-bottom {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        margin-top: 16px;
        padding-top: 14px;
        border-top: 1px solid rgba(255,255,255,.08);
    }

    .showtime-bottom span {
        color: #ddd;
        font-size: 14px;
    }

    .showtime-bottom strong {
        color: #f5a623;
        white-space: nowrap;
    }

    .empty-box {
        grid-column: 1 / -1;
        min-height: 220px;
        display: grid;
        place-items: center;
        text-align: center;
        color: #888;
        border-radius: 30px;
        background: linear-gradient(145deg, #171717, #101010);
        border: 1px solid rgba(245,166,35,.26);
    }

    .empty-box i {
        font-size: 44px;
        color: rgba(245,166,35,.45);
        margin-bottom: 12px;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 1200px) {
        .showtime-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 800px) {
        .showtime-grid { grid-template-columns: 1fr; }
    }
</style>
@endsection