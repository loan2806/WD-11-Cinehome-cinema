@extends('layouts.admin')

@section('title', 'Dashboard Admin - CineHome')
@section('page-title', 'Dashboard quản lý')
@section('page-subtitle', 'Theo dõi doanh thu, vé bán, lịch chiếu và hoạt động hệ thống')

@section('content')

{{-- STAT CARDS --}}
<div class="row g-4 mb-4">

    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>

            <div class="stat-label">Doanh thu hôm nay</div>
            <div class="stat-value">82.500.000đ</div>

            <div class="stat-change up">
                <i class="fa-solid fa-arrow-up"></i>
                Tăng 12% so với hôm qua
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fa-solid fa-ticket"></i>
            </div>

            <div class="stat-label">Vé đã bán</div>
            <div class="stat-value">1.240</div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fa-solid fa-users"></i>
            </div>

            <div class="stat-label">Lượng khách</div>
            <div class="stat-value">980</div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fa-solid fa-burger"></i>
            </div>

            <div class="stat-label">Doanh thu đồ ăn</div>
            <div class="stat-value">12.300.000đ</div>
        </div>
    </div>

</div>

{{-- TABLES --}}
<div class="row g-4">

    {{-- MOVIES --}}
    <div class="col-xl-7">

        <div class="admin-panel">

            <div class="panel-header">
                <div>
                    <h5>Phim mới cập nhật</h5>
                    <small>Danh sách phim đang chiếu và sắp chiếu</small>
                </div>
            </div>

            <div class="table-responsive">

                <table class="admin-table">

                    <thead>
                        <tr>
                            <th>Phim</th>
                            <th>Thời lượng</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($latestMovies as $movie)

                            <tr>

                                <td>
                                    <div class="table-movie">

                                        <img src="{{ $movie->poster }}" alt="{{ $movie->title }}">

                                        <div>
                                            <strong>{{ $movie->title }}</strong>
                                            <small>{{ $movie->genre }}</small>
                                        </div>

                                    </div>
                                </td>

                                <td>{{ $movie->duration }} phút</td>

                                <td>
                                    @if ($movie->release_date > now())
                                        <span class="status-badge status-coming">
                                            Sắp chiếu
                                        </span>
                                    @else
                                        <span class="status-badge status-showing">
                                            Đang chiếu
                                        </span>
                                    @endif
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="3" class="text-center py-4">
                                    Chưa có phim nào
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    {{-- SHOWTIMES --}}
    <div class="col-xl-5">

        <div class="admin-panel">

            <div class="panel-header">
                <div>
                    <h5>Lịch chiếu hôm nay</h5>
                    <small>Theo dõi suất chiếu và số vé bán</small>
                </div>
            </div>

            <div class="table-responsive">

                <table class="admin-table">

                    <thead>
                        <tr>
                            <th>Phim</th>
                            <th>Giờ</th>
                            <th>Rạp</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($todaySchedules as $schedule)

                            <tr>

                                <td>
                                    <strong>
                                        {{ $schedule->movie->title }}
                                    </strong>

                                    <br>

                                    <small class="text-muted">
                                        {{ $schedule->cinema->name }}
                                        - {{ $schedule->room_name }}
                                    </small>
                                </td>

                                <td>
                                    <span class="status-badge status-coming">
                                        {{ $schedule->show_time }}
                                    </span>
                                </td>

                                <td>
                                    {{ $schedule->price }}đ
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="3" class="text-center py-4">
                                    Chưa có lịch chiếu
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

@endsection