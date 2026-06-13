@extends('layouts.admin')

@section('title', 'Dashboard Admin - CineHome')
@section('page-title', 'Dashboard quản lý')
@section('page-subtitle', 'Theo dõi doanh thu, vé bán, lịch chiếu và hoạt động hệ thống')

@section('content')

{{-- KHU VỰC CÁC THẺ THỐNG KÊ (STAT CARDS) - ẨN/HIỆN ĐỘNG THEO PHÂN QUYỀN --}}
<div class="row g-4 mb-4">

    {{-- THẺ 1: DOANH THU HÔM NAY (Chì Quản lý/Admin có quyền xem) --}}
    @can('thong_ke_doanh_thu')
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
                <div class="stat-label">Doanh thu hôm nay</div>
                <div class="stat-value">{{ $statData['doanh_thu_hom_nay'] }}</div>
                <div class="stat-change up">
                    <i class="fa-solid fa-arrow-up"></i>
                    Tăng 12% so với hôm qua
                </div>
            </div>
        </div>
    @endcan

    {{-- THẺ 2: VÉ ĐÃ BÁN (Hiển thị khi có quyền xem doanh thu HOẶC bán vé) --}}
    @if(auth()->user()->can('thong_ke_doanh_thu') || auth()->user()->can('ban_ve_tai_quay'))
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-ticket"></i>
                </div>
                <div class="stat-label">Vé đã bán</div>
                <div class="stat-value">{{ $statData['ve_da_ban'] }}</div>
            </div>
        </div>
    @endif

    {{-- THẺ 3: LƯỢNG KHÁCH ĐẾN RẠP --}}
    @if(auth()->user()->can('thong_ke_doanh_thu') || auth()->user()->can('ban_ve_tai_quay'))
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="stat-label">Lượng khách</div>
                <div class="stat-value">{{ $statData['luong_khach'] }}</div>
            </div>
        </div>
    @endif

    {{-- THẺ 4: DOANH THU ĐỒ ĂN BẮP NƯỚC (Chỉ hiển thị khi có quyền xem doanh thu) --}}
    @can('thong_ke_doanh_thu')
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-burger"></i>
                </div>
                <div class="stat-label">Doanh thu đồ ăn</div>
                <div class="stat-value">{{ $statData['doanh_thu_do_an'] }}</div>
            </div>
        </div>
    @endcan

</div>

{{-- BIỂU NGỮ CHÀO MỪNG THÔNG MINH KHI TÀI KHOẢN KHÔNG CÓ QUYỀN XEM DOANH THU (NHÂN VIÊN QUẦY) --}}
@if(!auth()->user()->can('thong_ke_doanh_thu'))
    <div class="rounded-2xl border border-white/10 bg-gradient-to-r from-[#1a0b04] to-[#2b1208] p-5 mb-4 shadow-xl">
        <div class="max-w-3xl">
            <h4 class="text-lg font-black text-white flex items-center gap-2">
                <i class="fa-solid fa-wand-magic-sparkles text-[#d99a32]"></i> Xin chào, {{ Auth::user()->ho_ten }}!
            </h4>
            <p class="text-sm text-gray-300 mt-1.5 leading-relaxed">
                Chào mừng bạn đến với hệ thống quản trị rạp phim <strong>CineHome</strong>. Tài khoản của bạn đã được phân quyền vận hành nghiệp vụ tại quầy. Bạn có thể sử dụng các menu được cấp ở thanh điều hướng bên cạnh hoặc sử dụng nhanh các nút tác vụ bên dưới:
            </p>
            <div class="flex flex-wrap items-center gap-3 mt-4">
                @can('ban_ve_tai_quay')
                    <a href="#" class="inline-flex h-9 items-center justify-center gap-2 rounded-xl bg-[#d99a32] px-4 text-xs font-black text-[#2b1208] transition hover:opacity-90 no-underline shadow-lg shadow-[#d99a32]/10">
                        <i class="fa-solid fa-desktop"></i> Đi tới màn hình bán vé tại quầy
                    </a>
                @endcan
                @can('soat_ve_vao_cua')
                    <a href="#" class="inline-flex h-9 items-center justify-center gap-2 rounded-xl bg-white/10 px-4 text-xs font-black text-white transition hover:bg-white/15 no-underline border border-white/5">
                        <i class="fa-solid fa-qrcode"></i> Quét mã soát vé phòng chiếu
                    </a>
                @endcan
            </div>
        </div>
    </div>
@endif

{{-- KHU VỰC BẢNG DỮ LIỆU PHIM VÀ LICH CHIẾU --}}
<div class="row g-4">

    {{-- BẢNG PHIM MỚI CẬP NHẬT --}}
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
                                    $statusText = 'Không có suất';
                                    $statusClass = 'status-coming';
                                }
                            @endphp
                            <tr>
                                <td>
                                    <div class="table-movie">
                                        <img src="{{ $movie->poster }}" alt="{{ $movie->ten_phim }}">
                                        <div>
                                            <strong>{{ $movie->ten_phim }}</strong>
                                            <small>{{ $movie->genres ? $movie->genres->pluck('ten_the_loai')->join(', ') : 'Thể loại' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $movie->thoi_luong }} phút</td>
                                <td>
                                    <span class="status-badge {{ $statusClass }}">
                                        {{ $statusText }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-4">Chưa có phim nào</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- BẢNG LỊCH CHIẾU HÔM NAY --}}
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
                            <th>Giá vé</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($todaySchedules as $schedule)
                            <tr>
                                <td>
                                    <strong>{{ $schedule->phim->ten_phim }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $schedule->phongChieu->ten_phong ?? 'Phòng chiếu' }}
                                    </small>
                                </td>
                                <td>
                                    <span class="status-badge status-coming">
                                        {{ \Carbon\Carbon::parse($schedule->thoi_gian_chieu)->format('H:i') }}
                                    </span>
                                </td>
                                <td>
                                    {{ number_format($schedule->price ?? 0) }}đ
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-4">Chưa có lịch chiếu hôm nay</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@endsection