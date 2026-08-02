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
            'cho_thanh_toan' => 'Chờ thanh toán',
            'da_thanh_toan' => 'Đã thanh toán',
            'da_in' => 'Đã in',
            'da_su_dung' => 'Đã sử dụng',
            'da_huy' => 'Đã hủy',
            'het_han' => 'Hết hạn',
        ];

        // Chỉ các trạng thái nghiệp vụ này mới cho Admin sửa thủ công.
        // "Chờ thanh toán" phải do PayOS xác nhận, không được xác nhận tay.
        $editableStatusLabels = [
            'da_thanh_toan' => 'Đã thanh toán',
            'da_in' => 'Đã in',
            'da_su_dung' => 'Đã sử dụng',
            'da_huy' => 'Đã hủy',
        ];

        $typeLabels = [
            'truc_tuyen' => 'Online',
            'tai_quay' => 'Tại quầy',
        ];

        $statusIcons = [
            'cho_thanh_toan' => 'fa-clock',
            'da_thanh_toan' => 'fa-circle-check',
            'da_in' => 'fa-print',
            'da_su_dung' => 'fa-ticket',
            'da_huy' => 'fa-circle-xmark',
            'het_han' => 'fa-hourglass-end',
        ];

        $typeIcons = [
            'truc_tuyen' => 'fa-globe',
            'tai_quay' => 'fa-store',
        ];

        $currentTrangThaiFilter = (string) request('trang_thai', '');
        if ($currentTrangThaiFilter !== '' && !isset($statusLabels[$currentTrangThaiFilter])) {
            $currentTrangThaiFilter = '';
        }

        $currentLoaiVeFilter = (string) request('loai_ve', '');
        if ($currentLoaiVeFilter !== '' && !isset($typeLabels[$currentLoaiVeFilter])) {
            $currentLoaiVeFilter = '';
        }
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
                    <div class="ticket-filter-dropdown" data-value="{{ $currentTrangThaiFilter }}">
                        <input type="hidden" name="trang_thai" value="{{ $currentTrangThaiFilter }}">

                        <button type="button" class="ticket-filter-dropdown-trigger">
                            <i class="fa-solid {{ $statusIcons[$currentTrangThaiFilter] ?? 'fa-sliders' }}"></i>
                            <span class="label">{{ $statusLabels[$currentTrangThaiFilter] ?? 'Tất cả trạng thái' }}</span>
                            <i class="fa-solid fa-chevron-down chevron"></i>
                        </button>

                        <div class="ticket-filter-dropdown-menu hidden">
                            <button type="button" class="ticket-filter-dropdown-option {{ $currentTrangThaiFilter === '' ? 'is-selected' : '' }}" data-value="" data-label="Tất cả trạng thái" data-icon="fa-sliders">
                                <i class="fa-solid fa-sliders"></i>
                                <span>Tất cả trạng thái</span>
                            </button>
                            @foreach ($statusLabels as $value => $label)
                                <button type="button" class="ticket-filter-dropdown-option {{ $value === $currentTrangThaiFilter ? 'is-selected' : '' }}" data-value="{{ $value }}" data-label="{{ $label }}" data-icon="{{ $statusIcons[$value] }}">
                                    <i class="fa-solid {{ $statusIcons[$value] }}"></i>
                                    <span>{{ $label }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </label>

                <label class="ticket-filter-field">
                    <span>Loại vé</span>
                    <div class="ticket-filter-dropdown" data-value="{{ $currentLoaiVeFilter }}">
                        <input type="hidden" name="loai_ve" value="{{ $currentLoaiVeFilter }}">

                        <button type="button" class="ticket-filter-dropdown-trigger">
                            <i class="fa-solid {{ $typeIcons[$currentLoaiVeFilter] ?? 'fa-layer-group' }}"></i>
                            <span class="label">{{ $typeLabels[$currentLoaiVeFilter] ?? 'Tất cả loại vé' }}</span>
                            <i class="fa-solid fa-chevron-down chevron"></i>
                        </button>

                        <div class="ticket-filter-dropdown-menu hidden">
                            <button type="button" class="ticket-filter-dropdown-option {{ $currentLoaiVeFilter === '' ? 'is-selected' : '' }}" data-value="" data-label="Tất cả loại vé" data-icon="fa-layer-group">
                                <i class="fa-solid fa-layer-group"></i>
                                <span>Tất cả loại vé</span>
                            </button>
                            @foreach ($typeLabels as $value => $label)
                                <button type="button" class="ticket-filter-dropdown-option {{ $value === $currentLoaiVeFilter ? 'is-selected' : '' }}" data-value="{{ $value }}" data-label="{{ $label }}" data-icon="{{ $typeIcons[$value] }}">
                                    <i class="fa-solid {{ $typeIcons[$value] }}"></i>
                                    <span>{{ $label }}</span>
                                </button>
                            @endforeach
                        </div>
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
                                    @if ($ticket->trang_thai === 'cho_thanh_toan')
                                        <span class="ticket-status-static status-cho_thanh_toan">
                                            <i class="fa-regular fa-clock"></i>
                                            Chờ thanh toán
                                        </span>
                                    @elseif ($ticket->trang_thai === 'het_han')
                                        <span class="ticket-status-static status-het_han">
                                            <i class="fa-solid fa-hourglass-end"></i>
                                            Hết hạn
                                        </span>
                                    @else
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
                                                @foreach ($editableStatusLabels as $value => $label)
                                                    <option value="{{ $value }}" @selected($ticket->trang_thai === $value)>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </form>
                                    @endif
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

                                        @if ($ticket->trang_thai === 'cho_thanh_toan')
                                            <span class="ticket-action-note pending">
                                                <i class="fa-regular fa-clock"></i>
                                                Chờ PayOS
                                            </span>
                                        @elseif ($ticket->trang_thai === 'het_han')
                                            <span class="ticket-action-note expired">
                                                <i class="fa-solid fa-hourglass-end"></i>
                                                Hết hạn
                                            </span>
                                        @elseif ($ticket->trang_thai === 'da_thanh_toan')
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

    <style>
        .ticket-status-static {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-height: 36px;
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 900;
            white-space: nowrap;
        }

        .ticket-status-static.status-cho_thanh_toan {
            color: #fde68a;
            background: rgba(234, 179, 8, .12);
            border: 1px solid rgba(234, 179, 8, .35);
        }

        .ticket-status-static.status-het_han {
            color: #cbd5e1;
            background: rgba(100, 116, 139, .14);
            border: 1px solid rgba(100, 116, 139, .35);
        }

        .ticket-action-note.pending {
            color: #facc15;
        }

        .ticket-action-note.expired {
            color: #94a3b8;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmTicketStatus(select) {
            const oldValue = select.dataset.current;
            const newValue = select.value;

            if (oldValue === newValue) {
                return;
            }

            const statusMap = {
                cho_thanh_toan: 'Chờ thanh toán',
                da_thanh_toan: 'Đã thanh toán',
                da_in: 'Đã in',
                da_su_dung: 'Đã sử dụng',
                da_huy: 'Đã hủy',
                het_han: 'Hết hạn'
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.ticket-filter-dropdown').forEach(function (wrap) {
                const trigger = wrap.querySelector('.ticket-filter-dropdown-trigger');
                const menu = wrap.querySelector('.ticket-filter-dropdown-menu');
                const hiddenInput = wrap.querySelector('input[type="hidden"]');
                const labelEl = trigger.querySelector('.label');
                const iconEl = trigger.querySelector('i:first-child');
                const options = wrap.querySelectorAll('.ticket-filter-dropdown-option');

                // Đưa menu ra làm con trực tiếp của <body> để tránh bị các
                // container cha (overflow/backdrop-filter) làm lệch hoặc cắt mất.
                document.body.appendChild(menu);

                function closeMenu() {
                    wrap.classList.remove('is-open');
                    menu.classList.add('hidden');
                }

                function positionMenu() {
                    const rect = trigger.getBoundingClientRect();
                    menu.style.left = rect.left + 'px';
                    menu.style.width = rect.width + 'px';

                    const menuHeight = menu.offsetHeight;
                    const spaceBelow = window.innerHeight - rect.bottom;
                    const spaceAbove = rect.top;

                    if (spaceBelow < menuHeight + 8 && spaceAbove > spaceBelow) {
                        menu.style.top = (rect.top - menuHeight - 8) + 'px';
                    } else {
                        menu.style.top = (rect.bottom + 8) + 'px';
                    }
                }

                trigger.addEventListener('click', function (event) {
                    event.stopPropagation();
                    const willOpen = menu.classList.contains('hidden');
                    closeMenu();
                    if (willOpen) {
                        wrap.classList.add('is-open');
                        menu.classList.remove('hidden');
                        positionMenu();
                    }
                });

                window.addEventListener('scroll', closeMenu, true);
                window.addEventListener('resize', closeMenu);

                options.forEach(function (opt) {
                    opt.addEventListener('click', function () {
                        hiddenInput.value = opt.dataset.value;
                        labelEl.textContent = opt.dataset.label;
                        iconEl.className = 'fa-solid ' + opt.dataset.icon;

                        options.forEach((o) => o.classList.toggle('is-selected', o === opt));
                        closeMenu();
                    });
                });

                document.addEventListener('click', function (event) {
                    if (!wrap.contains(event.target) && !menu.contains(event.target)) {
                        closeMenu();
                    }
                });
            });
        });
    </script>
@endsection