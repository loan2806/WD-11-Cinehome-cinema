@extends('layouts.admin')

@section('title', 'Dashboard Admin - CineHome')
@section('page-title', 'Dashboard quản lý')
@section('page-subtitle', 'Theo dõi doanh thu, vé bán, lịch chiếu và hoạt động hệ thống')

@section('content')

@php
    /*
        Dữ liệu mẫu.
        Sau này thay bằng dữ liệu từ controller/database.
    */

    $latestMovies = [
        [
            'title' => 'Buồn Thần Bán Thành',
            'genre' => 'Kinh dị',
            'duration' => '120 phút',
            'status' => 'Đang chiếu',
            'poster' => 'https://images.unsplash.com/photo-1574267432553-4b4628081c31?q=80&w=300&auto=format&fit=crop',
        ],
        [
            'title' => 'Thành Phố Ánh Đèn',
            'genre' => 'Tình cảm',
            'duration' => '110 phút',
            'status' => 'Đang chiếu',
            'poster' => 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?q=80&w=300&auto=format&fit=crop',
        ],
        [
            'title' => 'Cuộc Đua Cuối Cùng',
            'genre' => 'Hành động',
            'duration' => '130 phút',
            'status' => 'Sắp chiếu',
            'poster' => 'https://images.unsplash.com/photo-1524985069026-dd778a71c7b4?q=80&w=300&auto=format&fit=crop',
        ],
        [
            'title' => 'Bí Mật Sau Màn Ảnh',
            'genre' => 'Trinh thám',
            'duration' => '118 phút',
            'status' => 'Tạm dừng',
            'poster' => 'https://images.unsplash.com/photo-1512149177596-f817c7ef5d4c?q=80&w=300&auto=format&fit=crop',
        ],
    ];

    $todaySchedules = [
        [
            'movie' => 'Buồn Thần Bán Thành',
            'cinema' => 'CineHome Hà Nội',
            'room' => 'Phòng 01',
            'time' => '19:30',
            'sold' => '84/120',
        ],
        [
            'movie' => 'Thành Phố Ánh Đèn',
            'cinema' => 'CineHome Hồ Chí Minh',
            'room' => 'Phòng 03',
            'time' => '20:15',
            'sold' => '66/100',
        ],
        [
            'movie' => 'Cuộc Đua Cuối Cùng',
            'cinema' => 'CineHome Đà Nẵng',
            'room' => 'Phòng 02',
            'time' => '21:00',
            'sold' => '90/110',
        ],
    ];
@endphp

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
                <i class="fa-solid fa-arrow-up"></i> Tăng 12% so với hôm qua
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
            <div class="stat-change up">
                <i class="fa-solid fa-arrow-up"></i> Tăng 8% trong ngày
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fa-solid fa-users"></i>
            </div>

            <div class="stat-label">Lượng khách</div>
            <div class="stat-value">980</div>
            <div class="stat-change up">
                <i class="fa-solid fa-arrow-up"></i> Tăng 5% theo tuần
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fa-solid fa-burger"></i>
            </div>

            <div class="stat-label">Doanh thu đồ ăn</div>
            <div class="stat-value">12.300.000đ</div>
            <div class="stat-change down">
                <i class="fa-solid fa-arrow-down"></i> Giảm 2% hôm nay
            </div>
        </div>
    </div>

</div>

{{-- CHART + QUICK ACTIONS --}}
<div class="row g-4 mb-4">

    <div class="col-xl-8">
        <div class="admin-panel">
            <div class="panel-header">
                <div>
                    <h5>Biểu đồ doanh thu</h5>
                    <small>Doanh thu vé và đồ ăn trong 7 ngày gần nhất</small>
                </div>

                <button class="btn-admin-outline">
                    <i class="fa-solid fa-download"></i> Xuất báo cáo
                </button>
            </div>

            <div class="panel-body">
                <canvas id="revenueChart" height="120"></canvas>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="admin-panel h-100">
            <div class="panel-header">
                <div>
                    <h5>Thao tác nhanh</h5>
                    <small>Chức năng thường dùng</small>
                </div>
            </div>

            <div class="panel-body">
                <a href="#" class="quick-action">
                    <i class="fa-solid fa-plus"></i>
                    <div>
                        <strong>Thêm phim mới</strong>
                        <div class="text-muted small">Upload poster, trailer, thông tin phim</div>
                    </div>
                </a>

                <a href="#" class="quick-action">
                    <i class="fa-solid fa-calendar-plus"></i>
                    <div>
                        <strong>Tạo lịch chiếu</strong>
                        <div class="text-muted small">Chọn phim, rạp, phòng và giờ chiếu</div>
                    </div>
                </a>

                <a href="#" class="quick-action">
                    <i class="fa-solid fa-user-plus"></i>
                    <div>
                        <strong>Tạo nhân viên</strong>
                        <div class="text-muted small">Cấp tài khoản Staff cho nhân viên</div>
                    </div>
                </a>

                <a href="#" class="quick-action">
                    <i class="fa-solid fa-chair"></i>
                    <div>
                        <strong>Quản lý sơ đồ ghế</strong>
                        <div class="text-muted small">Thiết lập ghế theo từng phòng chiếu</div>
                    </div>
                </a>
            </div>
        </div>
    </div>

</div>

{{-- TABLES --}}
<div class="row g-4">

    {{-- LATEST MOVIES --}}
    <div class="col-xl-7">
        <div class="admin-panel">
            <div class="panel-header">
                <div>
                    <h5>Phim mới cập nhật</h5>
                    <small>Danh sách phim đang chiếu và sắp chiếu</small>
                </div>

                <a href="#" class="btn-admin">
                    <i class="fa-solid fa-plus"></i> Thêm phim
                </a>
            </div>

            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Phim</th>
                            <th>Thời lượng</th>
                            <th>Trạng thái</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($latestMovies as $movie)
                            <tr>
                                <td>
                                    <div class="table-movie">
                                        <img src="{{ $movie['poster'] }}" alt="{{ $movie['title'] }}">
                                        <div>
                                            <strong>{{ $movie['title'] }}</strong>
                                            <small>{{ $movie['genre'] }}</small>
                                        </div>
                                    </div>
                                </td>

                                <td>{{ $movie['duration'] }}</td>

                                <td>
                                    @if ($movie['status'] === 'Đang chiếu')
                                        <span class="status-badge status-showing">Đang chiếu</span>
                                    @elseif ($movie['status'] === 'Sắp chiếu')
                                        <span class="status-badge status-coming">Sắp chiếu</span>
                                    @else
                                        <span class="status-badge status-stop">Tạm dừng</span>
                                    @endif
                                </td>

                                <td class="text-end">
                                    <button class="action-btn action-view">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>

                                    <button class="action-btn action-edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>

                                    <button class="action-btn action-delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>
    </div>

    {{-- TODAY SCHEDULES --}}
    <div class="col-xl-5">
        <div class="admin-panel">
            <div class="panel-header">
                <div>
                    <h5>Lịch chiếu hôm nay</h5>
                    <small>Theo dõi suất chiếu và số vé bán</small>
                </div>

                <a href="#" class="btn-admin-outline">
                    Xem tất cả
                </a>
            </div>

            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Phim</th>
                            <th>Giờ</th>
                            <th>Vé</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($todaySchedules as $schedule)
                            <tr>
                                <td>
                                    <strong>{{ $schedule['movie'] }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $schedule['cinema'] }} - {{ $schedule['room'] }}
                                    </small>
                                </td>

                                <td>
                                    <span class="status-badge status-coming">
                                        {{ $schedule['time'] }}
                                    </span>
                                </td>

                                <td>{{ $schedule['sold'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>
    </div>

</div>

@endsection

@section('scripts')
<script>
    const revenueCtx = document.getElementById('revenueChart');

    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'CN'],
                datasets: [
                    {
                        label: 'Doanh thu vé',
                        data: [12000000, 18500000, 15000000, 23000000, 28000000, 36000000, 42000000],
                        borderWidth: 3,
                        tension: 0.4
                    },
                    {
                        label: 'Doanh thu đồ ăn',
                        data: [3000000, 4200000, 3800000, 5200000, 7000000, 8500000, 9600000],
                        borderWidth: 3,
                        tension: 0.4
                    }
                ]
            },
            options: {
                plugins: {
                    legend: {
                        labels: {
                            color: '#ffffff'
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: '#bdbdbd'
                        },
                        grid: {
                            color: 'rgba(255,255,255,0.08)'
                        }
                    },
                    y: {
                        ticks: {
                            color: '#bdbdbd',
                            callback: function(value) {
                                return value.toLocaleString('vi-VN') + 'đ';
                            }
                        },
                        grid: {
                            color: 'rgba(255,255,255,0.08)'
                        }
                    }
                }
            }
        });
    }
</script>
@endsection