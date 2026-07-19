@extends('layouts.admin')

@section('title', 'Quản lý vé - CineHome')
@section('page-title', 'Quản lý vé')
@section('page-subtitle', 'Theo dõi vé online, vé tại quầy và trạng thái sử dụng vé')

@section('content')
    @php
        $summary = $summary ?? [
            'total' => $totalTickets ?? 0,
            'online' => $onlineTickets ?? 0,
            'counter' => $counterTickets ?? 0,
            'paid' => $paidTickets ?? 0,
            'used' => $usedTickets ?? 0,
            'cancelled' => $cancelledTickets ?? 0,
            'revenue' => 0,
        ];

        $statusLabels = [
            'da_thanh_toan' => 'Đã thanh toán',
            'da_su_dung' => 'Đã sử dụng',
            'da_huy' => 'Đã hủy',
        ];

        $typeLabels = [
            'truc_tuyen' => 'Online',
            'tai_quay' => 'Tại quầy',
        ];
    @endphp

    <div class="ticket-page">
        @include('admin.partials.flash')

        <section class="ticket-hero">
            <div class="ticket-hero-copy">
                <span class="ticket-kicker">
                    <i class="fa-solid fa-ticket"></i>
                    Trung tâm quản lý vé
                </span>
                <h1>Quản lý vé xem phim</h1>
                <p>
                    Tra cứu vé, kiểm tra suất chiếu, cập nhật trạng thái sử dụng và xử lý hủy vé trong một màn hình.
                    Giao diện được tối ưu để nhân sự vận hành đọc nhanh, thao tác chắc tay hơn.
                </p>

                <div class="ticket-hero-metrics">
                    <span><i class="fa-solid fa-sack-dollar"></i> {{ number_format((float) $summary['revenue'], 0, ',', '.') }}đ doanh thu hợp lệ</span>
                    <span><i class="fa-solid fa-filter"></i> {{ number_format($tickets->total()) }} kết quả đang hiển thị</span>
                </div>
            </div>

            <div class="ticket-hero-actions">
                <a href="{{ route('admin.soat-ve.index') }}" class="movie-action-btn is-soft">
                    <i class="fa-solid fa-qrcode"></i>
                    Soát vé QR
                </a>
                <a href="{{ route('admin.ve-xem-phims.index') }}" class="movie-action-btn is-ghost">
                    <i class="fa-solid fa-rotate-left"></i>
                    Làm mới
                </a>
            </div>
        </section>

        <section class="ticket-stat-grid" aria-label="Thống kê vé">
            <article class="ticket-stat-card accent-red">
                <span class="ticket-stat-icon"><i class="fa-solid fa-ticket"></i></span>
                <div>
                    <small>Tổng vé</small>
                    <strong>{{ number_format($summary['total']) }}</strong>
                </div>
            </article>

            <article class="ticket-stat-card accent-green">
                <span class="ticket-stat-icon"><i class="fa-solid fa-circle-check"></i></span>
                <div>
                    <small>Đã thanh toán</small>
                    <strong>{{ number_format($summary['paid']) }}</strong>
                </div>
            </article>

            <article class="ticket-stat-card accent-blue">
                <span class="ticket-stat-icon"><i class="fa-solid fa-door-open"></i></span>
                <div>
                    <small>Đã sử dụng</small>
                    <strong>{{ number_format($summary['used']) }}</strong>
                </div>
            </article>

            <article class="ticket-stat-card accent-gold">
                <span class="ticket-stat-icon"><i class="fa-solid fa-globe"></i></span>
                <div>
                    <small>Online</small>
                    <strong>{{ number_format($summary['online']) }}</strong>
                </div>
            </article>

            <article class="ticket-stat-card accent-purple">
                <span class="ticket-stat-icon"><i class="fa-solid fa-store"></i></span>
                <div>
                    <small>Tại quầy</small>
                    <strong>{{ number_format($summary['counter']) }}</strong>
                </div>
            </article>

            <article class="ticket-stat-card accent-neutral">
                <span class="ticket-stat-icon"><i class="fa-solid fa-ban"></i></span>
                <div>
                    <small>Đã hủy</small>
                    <strong>{{ number_format($summary['cancelled']) }}</strong>
                </div>
            </article>
        </section>

        <section class="ticket-panel">
            <div class="ticket-panel-header">
                <div>
                    <span class="ticket-kicker">
                        <i class="fa-solid fa-list-check"></i>
                        Danh sách vận hành
                    </span>
                    <h2>Danh sách vé xem phim</h2>
                    <p>Tra cứu theo mã vé, phim, rạp, phòng hoặc ghế và cập nhật trạng thái ngay trên từng dòng.</p>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.ve-xem-phims.index') }}" class="ticket-filter">
                <label class="ticket-filter-field is-search">
                    <span>Tìm kiếm</span>
                    <div class="ticket-filter-control">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input
                            type="text"
                            name="tim_kiem"
                            value="{{ request('tim_kiem') }}"
                            placeholder="Mã vé, tên phim, rạp, phòng, ghế..."
                        >
                    </div>
                </label>

                <label class="ticket-filter-field">
                    <span>Trạng thái</span>
                    <div class="ticket-filter-control">
                        <i class="fa-solid fa-sliders"></i>
                        <select name="trang_thai">
                            <option value="">Tất cả trạng thái</option>
                            @foreach ($statusLabels as $value => $label)
                                <option value="{{ $value }}" @selected(request('trang_thai') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </label>

                <label class="ticket-filter-field">
                    <span>Loại vé</span>
                    <div class="ticket-filter-control">
                        <i class="fa-solid fa-layer-group"></i>
                        <select name="loai_ve">
                            <option value="">Tất cả loại vé</option>
                            @foreach ($typeLabels as $value => $label)
                                <option value="{{ $value }}" @selected(request('loai_ve') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </label>

                <div class="ticket-filter-actions">
                    <button type="submit" class="movie-action-btn is-primary">
                        <i class="fa-solid fa-filter"></i>
                        Lọc vé
                    </button>
                    <a href="{{ route('admin.ve-xem-phims.index') }}" class="movie-action-btn is-soft">
                        <i class="fa-solid fa-rotate-left"></i>
                        Đặt lại
                    </a>
                </div>
            </form>

            <div class="ticket-table-wrap">
                <table class="ticket-table">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Mã vé</th>
                            <th>Phim & khách</th>
                            <th>Ghế</th>
                            <th>Suất chiếu</th>
                            <th>Loại</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th class="is-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tickets as $key => $ticket)
                            @php
                                $buyerName = $ticket->nguoiDung?->ho_ten
                                    ?? $ticket->nguoiDung?->name
                                    ?? $ticket->nguoiDung?->email
                                    ?? 'Khách vãng lai';
                                $sellerName = $ticket->nhanVien?->ho_ten
                                    ?? $ticket->nhanVien?->name
                                    ?? $ticket->nhanVien?->email;
                            @endphp

                            <tr>
                                <td class="ticket-index">#{{ ($tickets->firstItem() ?? 0) + $key }}</td>
                                <td>
                                    <span class="ticket-code">{{ $ticket->ma_ve }}</span>
                                </td>
                                <td>
                                    <div class="ticket-movie">
                                        <strong>{{ $ticket->ten_phim ?? 'Chưa có tên phim' }}</strong>
                                        <small>
                                            <i class="fa-solid fa-user"></i>
                                            {{ $buyerName }}
                                        </small>
                                        <small>
                                            <i class="fa-solid fa-location-dot"></i>
                                            {{ $ticket->ten_rap ?? 'Chưa có rạp' }} · Phòng {{ $ticket->ten_phong ?? '--' }}
                                        </small>
                                        @if ($sellerName)
                                            <small>
                                                <i class="fa-solid fa-user-tie"></i>
                                                Nhân viên: {{ $sellerName }}
                                            </small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="ticket-seat">
                                        <i class="fa-solid fa-couch"></i>
                                        {{ $ticket->ma_ghe ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="ticket-time">
                                        <i class="fa-regular fa-clock"></i>
                                        {{ $ticket->thoi_gian_chieu?->format('d/m/Y H:i') ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="ticket-type is-{{ $ticket->loai_ve === 'tai_quay' ? 'counter' : 'online' }}">
                                        <i class="fa-solid {{ $ticket->loai_ve === 'tai_quay' ? 'fa-store' : 'fa-globe' }}"></i>
                                        {{ $typeLabels[$ticket->loai_ve] ?? 'Không rõ' }}
                                    </span>
                                </td>
                                <td>
                                    <strong class="ticket-money">
                                        {{ number_format((float) $ticket->tong_tien, 0, ',', '.') }}đ
                                    </strong>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.ve-xem-phims.cap-nhat-trang-thai', $ticket) }}">
                                        @csrf
                                        @method('PATCH')

                                        <select
                                            name="trang_thai"
                                            onchange="confirmTicketStatus(this)"
                                            data-current="{{ $ticket->trang_thai }}"
                                            class="ticket-status-select status-{{ $ticket->trang_thai }}"
                                            aria-label="Cập nhật trạng thái vé {{ $ticket->ma_ve }}"
                                        >
                                            @foreach ($statusLabels as $value => $label)
                                                <option value="{{ $value }}" @selected($ticket->trang_thai === $value)>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <div class="ticket-actions">
                                        <a
                                            href="{{ route('admin.ve-xem-phims.show', $ticket) }}"
                                            class="ticket-action-btn view"
                                            title="Xem chi tiết"
                                            aria-label="Xem chi tiết vé {{ $ticket->ma_ve }}"
                                        >
                                            <i class="fa-solid fa-eye"></i>
                                        </a>

                                        @if ($ticket->trang_thai === 'da_thanh_toan')
                                            <form method="POST" action="{{ route('admin.ve-xem-phims.su-dung', $ticket) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button
                                                    type="submit"
                                                    onclick="return confirm('Xác nhận vé này đã sử dụng?')"
                                                    class="ticket-action-btn success"
                                                    title="Đánh dấu đã sử dụng"
                                                    aria-label="Đánh dấu vé {{ $ticket->ma_ve }} đã sử dụng"
                                                >
                                                    <i class="fa-solid fa-check"></i>
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('admin.ve-xem-phims.huy', $ticket) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button
                                                    type="submit"
                                                    onclick="return confirm('Bạn có chắc muốn hủy vé này?')"
                                                    class="ticket-action-btn danger"
                                                    title="Hủy vé"
                                                    aria-label="Hủy vé {{ $ticket->ma_ve }}"
                                                >
                                                    <i class="fa-solid fa-ban"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="ticket-action-note">Đã khóa</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="ticket-empty">
                                        <span><i class="fa-solid fa-ticket"></i></span>
                                        <h3>Chưa có vé phù hợp</h3>
                                        <p>Thử đổi từ khóa, bỏ bộ lọc hoặc kiểm tra lại trạng thái vé.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="ticket-pagination">
                @include('components.admin-pagination', ['paginator' => $tickets])
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmTicketStatus(select) {
            const oldValue = select.dataset.current;
            const newValue = select.value;

            if (oldValue === newValue) {
                return;
            }

            const statusMap = {
                da_thanh_toan: 'Đã thanh toán',
                da_su_dung: 'Đã sử dụng',
                da_huy: 'Đã hủy'
            };

            const submitChange = () => {
                select.dataset.current = newValue;
                select.form.submit();
            };

            if (window.Swal) {
                Swal.fire({
                    title: 'Xác nhận cập nhật',
                    text: `Bạn có chắc muốn chuyển vé sang "${statusMap[newValue] || newValue}" không?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Xác nhận',
                    cancelButtonText: 'Hủy',
                    confirmButtonColor: '#ff2f45',
                    cancelButtonColor: '#6b7280',
                    background: '#151923',
                    color: '#ffffff'
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitChange();
                    } else {
                        select.value = oldValue;
                    }
                });
            } else if (confirm(`Chuyển vé sang "${statusMap[newValue] || newValue}"?`)) {
                submitChange();
            } else {
                select.value = oldValue;
            }
        }
    </script>
@endsection
