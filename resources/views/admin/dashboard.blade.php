@extends('layouts.admin')

@section('title', 'Dashboard Admin - CineHome')
@section('page-title', 'Dashboard quản lý')
@section('page-subtitle', 'Theo dõi doanh thu, vé bán, suất chiếu và hoạt động vận hành')

@section('content')
    @php
        $user = Auth::user();
        $canSeeRevenue = $user->can('thong_ke_doanh_thu');
        $canSellTicket = $user->can('ban_ve_tai_quay');
        $canManageMovie = $user->can('quan_ly_phim_suat_chieu');
        $canScanTicket = $user->can('soat_ve_vao_cua');
        $canManageCustomer = $user->can('quan_ly_khach_hang');

        $posterUrl = function (?string $poster): string {
            if (blank($poster)) {
                return 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=360&q=80';
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

        $showingCount = $latestMovies->filter(
            fn($movie) => $movie->showtimes->contains('trang_thai', \App\Models\SuatChieu::TRANG_THAI_DANG_CHIEU),
        )->count();

        $comingCount = $latestMovies->filter(
            fn($movie) => $movie->showtimes->contains('trang_thai', \App\Models\SuatChieu::TRANG_THAI_SAP_CHIEU) ||
                $movie->showtimes->contains('trang_thai', \App\Models\SuatChieu::TRANG_THAI_SAP_RA_MAT),
        )->count();
    @endphp

    <div class="admin-dashboard">
        <section class="admin-dashboard-hero">
            <div class="admin-dashboard-hero__copy">
                <span class="admin-dashboard-eyebrow">
                    <i class="fa-solid fa-chart-line"></i>
                    Trung tâm điều hành
                </span>
                <h2>Xin chào, {{ $user->ho_ten ?? $user->name ?? 'Admin CineHome' }}</h2>
                <p>
                    Nắm nhanh tình hình vận hành trong ngày, theo dõi phim mới, suất chiếu và các tác vụ quan trọng của hệ thống rạp CineHome.
                </p>

                <div class="admin-dashboard-actions">
                    @if ($canSellTicket)
                        <a href="{{ route('staff.ban-ve.index') }}" class="dashboard-primary-action">
                            <i class="fa-solid fa-ticket"></i>
                            Bán vé tại quầy
                        </a>
                    @endif

                    @if ($canManageMovie)
                        <a href="{{ route('admin.suat-chieus.index') }}" class="dashboard-secondary-action">
                            <i class="fa-solid fa-calendar-plus"></i>
                            Quản lý suất chiếu
                        </a>
                    @endif

                    @if ($canSeeRevenue)
                        <a href="{{ route('admin.thong-ke.index') }}" class="dashboard-secondary-action">
                            <i class="fa-solid fa-chart-pie"></i>
                            Báo cáo doanh thu
                        </a>
                    @endif
                </div>
            </div>

            <div class="admin-dashboard-hero__panel">
                <span>Phiên vận hành</span>
                <strong>{{ now()->format('d/m/Y') }}</strong>
                <div class="hero-operation-list">
                    <div>
                        <i class="fa-solid fa-film"></i>
                        <p>
                            <b>{{ $latestMovies->count() }}</b>
                            phim mới cập nhật
                        </p>
                    </div>
                    <div>
                        <i class="fa-solid fa-clock"></i>
                        <p>
                            <b>{{ $todaySchedules->count() }}</b>
                            suất chiếu hôm nay
                        </p>
                    </div>
                    <div>
                        <i class="fa-solid fa-bolt"></i>
                        <p>
                            <b>{{ $canSeeRevenue ? 'Live' : 'Ready' }}</b>
                            trạng thái hệ thống
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="dashboard-stat-grid">
            @can('thong_ke_doanh_thu')
                <article class="dashboard-stat-card stat-revenue">
                    <div class="dashboard-stat-top">
                        <span><i class="fa-solid fa-sack-dollar"></i></span>
                        <small class="dashboard-stat-badge is-up">+12%</small>
                    </div>
                    <p>Doanh thu hôm nay</p>
                    <strong>{{ $statData['doanh_thu_hom_nay'] }}</strong>
                    <div class="dashboard-stat-foot">
                        <span>So với hôm qua</span>
                        <i class="fa-solid fa-arrow-trend-up"></i>
                    </div>
                </article>
            @endcan

            @if ($canSeeRevenue || $canSellTicket)
                <article class="dashboard-stat-card">
                    <div class="dashboard-stat-top">
                        <span><i class="fa-solid fa-ticket"></i></span>
                        <small class="dashboard-stat-badge">Vé</small>
                    </div>
                    <p>Vé đã bán</p>
                    <strong>{{ $statData['ve_da_ban'] }}</strong>
                    <div class="dashboard-stat-foot">
                        <span>Giao dịch trong ngày</span>
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                </article>
            @endif

            @if ($canSeeRevenue || $canSellTicket)
                <article class="dashboard-stat-card">
                    <div class="dashboard-stat-top">
                        <span><i class="fa-solid fa-users"></i></span>
                        <small class="dashboard-stat-badge">Khách</small>
                    </div>
                    <p>Lượng khách</p>
                    <strong>{{ $statData['luong_khach'] }}</strong>
                    <div class="dashboard-stat-foot">
                        <span>Khách vào rạp</span>
                        <i class="fa-solid fa-person-walking"></i>
                    </div>
                </article>
            @endif

            @can('thong_ke_doanh_thu')
                <article class="dashboard-stat-card">
                    <div class="dashboard-stat-top">
                        <span><i class="fa-solid fa-burger"></i></span>
                        <small class="dashboard-stat-badge">F&B</small>
                    </div>
                    <p>Doanh thu đồ ăn</p>
                    <strong>{{ $statData['doanh_thu_do_an'] }}</strong>
                    <div class="dashboard-stat-foot">
                        <span>Bắp nước & combo</span>
                        <i class="fa-solid fa-mug-hot"></i>
                    </div>
                </article>
            @endcan
        </section>

        @unless ($canSeeRevenue)
            <section class="dashboard-staff-note">
                <div>
                    <span><i class="fa-solid fa-wand-magic-sparkles"></i></span>
                    <div>
                        <h4>Chế độ vận hành quầy</h4>
                        <p>
                            Tài khoản của bạn đang được phân quyền theo nghiệp vụ. Các tác vụ khả dụng sẽ hiển thị ngay bên dưới để thao tác nhanh hơn.
                        </p>
                    </div>
                </div>

                <div class="dashboard-staff-actions">
                    @can('ban_ve_tai_quay')
                        <a href="{{ route('staff.ban-ve.index') }}">Bán vé trực tiếp</a>
                    @endcan
                    @can('soat_ve_vao_cua')
                        <a href="{{ route('admin.soat-ve.index') }}">Soát vé QR</a>
                    @endcan
                </div>
            </section>
        @endunless

        <section class="dashboard-command-grid">
            <div class="admin-panel dashboard-panel dashboard-movie-panel">
                <div class="panel-header dashboard-panel-header">
                    <div>
                        <span class="dashboard-section-kicker">Thư viện phim</span>
                        <h5>Phim mới cập nhật</h5>
                        <small>{{ $showingCount }} đang chiếu, {{ $comingCount }} sắp chiếu trong danh sách gần nhất</small>
                    </div>

                    @if ($canManageMovie)
                        <a href="{{ route('admin.phims.index') }}" class="dashboard-panel-link">
                            Quản lý phim
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="admin-table dashboard-table">
                        <thead>
                            <tr>
                                <th>Phim</th>
                                <th>Thể loại</th>
                                <th>Thời lượng</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($latestMovies as $movie)
                                @php
                                    $statuses = $movie->showtimes->pluck('trang_thai');

                                    if ($statuses->contains(\App\Models\SuatChieu::TRANG_THAI_DANG_CHIEU)) {
                                        $statusText = 'Đang chiếu';
                                        $statusClass = 'status-showing';
                                    } elseif ($statuses->contains(\App\Models\SuatChieu::TRANG_THAI_SAP_CHIEU)) {
                                        $statusText = 'Sắp chiếu';
                                        $statusClass = 'status-coming';
                                    } elseif ($statuses->contains(\App\Models\SuatChieu::TRANG_THAI_SAP_RA_MAT)) {
                                        $statusText = 'Sắp ra mắt';
                                        $statusClass = 'status-coming';
                                    } else {
                                        $statusText = 'Chưa có suất';
                                        $statusClass = 'status-stop';
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        <div class="dashboard-movie-cell">
                                            <img src="{{ $posterUrl($movie->poster) }}" alt="{{ $movie->ten_phim }}">
                                            <div>
                                                <strong>{{ $movie->ten_phim }}</strong>
                                                <small>{{ $movie->country?->ten_quoc_gia ?? 'Đang cập nhật quốc gia' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $movie->genres->pluck('ten_the_loai')->take(2)->join(', ') ?: 'Điện ảnh' }}</td>
                                    <td>{{ $movie->thoi_luong }} phút</td>
                                    <td>
                                        <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <div class="dashboard-empty-state">
                                            <i class="fa-solid fa-film"></i>
                                            <strong>Chưa có phim nào</strong>
                                            <span>Thêm phim mới để bắt đầu vận hành lịch chiếu.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <aside class="dashboard-side-stack">
                <section class="admin-panel dashboard-panel">
                    <div class="panel-header dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">Tác vụ nhanh</span>
                            <h5>Lối tắt vận hành</h5>
                        </div>
                    </div>

                    <div class="dashboard-quick-actions">
                        @if ($canManageMovie)
                            <a href="{{ route('admin.phims.create') }}">
                                <span><i class="fa-solid fa-plus"></i></span>
                                <div>
                                    <strong>Thêm phim mới</strong>
                                    <small>Cập nhật poster, thể loại, trailer</small>
                                </div>
                            </a>

                            <a href="{{ route('admin.suat-chieus.create') }}">
                                <span><i class="fa-solid fa-calendar-plus"></i></span>
                                <div>
                                    <strong>Tạo suất chiếu</strong>
                                    <small>Lên lịch theo phòng và khung giờ</small>
                                </div>
                            </a>
                        @endif

                        @if ($canSellTicket)
                            <a href="{{ route('staff.ban-ve.index') }}">
                                <span><i class="fa-solid fa-cash-register"></i></span>
                                <div>
                                    <strong>Bán vé tại quầy</strong>
                                    <small>Chọn suất, ghế và thanh toán</small>
                                </div>
                            </a>
                        @endif

                        @if ($canScanTicket)
                            <a href="{{ route('admin.soat-ve.index') }}">
                                <span><i class="fa-solid fa-qrcode"></i></span>
                                <div>
                                    <strong>Soát vé QR</strong>
                                    <small>Xác thực vé trước cửa phòng</small>
                                </div>
                            </a>
                        @endif

                        @if ($canManageCustomer)
                            <a href="{{ route('admin.khach-hang.index') }}">
                                <span><i class="fa-solid fa-user-group"></i></span>
                                <div>
                                    <strong>Khách hàng</strong>
                                    <small>Hồ sơ, thành viên và voucher</small>
                                </div>
                            </a>
                        @endif
                    </div>
                </section>

                <section class="admin-panel dashboard-panel">
                    <div class="panel-header dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">Hôm nay</span>
                            <h5>Suất chiếu gần nhất</h5>
                            <small>{{ $todaySchedules->count() }} suất đang trong lịch</small>
                        </div>

                        @if ($canManageMovie)
                            <a href="{{ route('admin.suat-chieus.index') }}" class="dashboard-panel-link compact">
                                Tất cả
                            </a>
                        @endif
                    </div>

                    <div class="dashboard-showtime-list">
                        @forelse ($todaySchedules as $schedule)
                            @php
                                $startTime = \Carbon\Carbon::parse($schedule->thoi_gian_chieu);
                            @endphp
                            <article class="dashboard-showtime-card">
                                <time>{{ $startTime->format('H:i') }}</time>
                                <div>
                                    <strong>{{ $schedule->phim?->ten_phim ?? 'Phim đang cập nhật' }}</strong>
                                    <span>
                                        {{ $schedule->phongChieu?->ten_phong ?? 'Phòng chiếu' }}
                                        @if ($schedule->rapChieuPhim?->ten_rap)
                                            · {{ $schedule->rapChieuPhim->ten_rap }}
                                        @endif
                                    </span>
                                </div>
                                <b>{{ number_format((float) $schedule->gia_ve) }}đ</b>
                            </article>
                        @empty
                            <div class="dashboard-empty-state compact">
                                <i class="fa-regular fa-calendar"></i>
                                <strong>Chưa có suất chiếu hôm nay</strong>
                                <span>Lên lịch suất chiếu để hệ thống bắt đầu nhận đặt vé.</span>
                            </div>
                        @endforelse
                    </div>
                </section>
            </aside>
        </section>
    </div>
@endsection
