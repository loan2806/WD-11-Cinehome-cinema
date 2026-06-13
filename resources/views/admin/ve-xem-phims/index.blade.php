@extends('layouts.admin')

@section('title', 'Quản lý vé - CineHome')
@section('page-title', 'Quản lý vé')
@section('page-subtitle', 'Theo dõi vé online, vé tại quầy và trạng thái sử dụng vé')

@section('content')

<div class="ticket-page">

    {{-- Thống kê nhanh --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4 mb-6">

        <div class="ticket-stat-card accent-gold">
            <div class="ticket-stat-icon">
                <i class="fa-solid fa-ticket"></i>
            </div>
            <div>
                <p>Tổng vé</p>
                <h3>{{ $totalTickets }}</h3>
            </div>
        </div>

        <div class="ticket-stat-card accent-blue">
            <div class="ticket-stat-icon">
                <i class="fa-solid fa-globe"></i>
            </div>
            <div>
                <p>Vé online</p>
                <h3>{{ $onlineTickets }}</h3>
            </div>
        </div>

        <div class="ticket-stat-card accent-green">
            <div class="ticket-stat-icon">
                <i class="fa-solid fa-store"></i>
            </div>
            <div>
                <p>Vé tại quầy</p>
                <h3>{{ $counterTickets }}</h3>
            </div>
        </div>

        <div class="ticket-stat-card accent-red">
            <div class="ticket-stat-icon">
                <i class="fa-solid fa-ban"></i>
            </div>
            <div>
                <p>Vé đã hủy</p>
                <h3>{{ $cancelledTickets }}</h3>
            </div>
        </div>

    </div>

    {{-- Khung danh sách --}}
    <div class="ticket-panel">

        <div class="ticket-panel-header">
            <div>
                <h5>Danh sách vé</h5>
                <p>Quản lý toàn bộ vé xem phim trong hệ thống</p>
            </div>
        </div>

        {{-- Bộ lọc --}}
        <form method="GET" action="{{ route('admin.ve-xem-phims.index') }}" class="ticket-filter">

            <input type="text" name="tim_kiem" value="{{ request('tim_kiem') }}"
                placeholder="Tìm mã vé, phim, rạp, phòng, ghế..." class="ticket-input xl:col-span-2">

            <select name="trang_thai" class="ticket-input ticket-select">
                <option value="">Tất cả trạng thái</option>
                <option value="da_thanh_toan" {{ request('trang_thai')=='da_thanh_toan' ? 'selected' : '' }}>
                    Đã thanh toán
                </option>
                <option value="da_su_dung" {{ request('trang_thai')=='da_su_dung' ? 'selected' : '' }}>
                    Đã sử dụng
                </option>
                <option value="da_huy" {{ request('trang_thai')=='da_huy' ? 'selected' : '' }}>
                    Đã hủy
                </option>
            </select>

            <select name="loai_ve" class="ticket-input ticket-select">
                <option value="">Tất cả loại vé</option>
                <option value="truc_tuyen" {{ request('loai_ve')=='truc_tuyen' ? 'selected' : '' }}>
                    Trực tuyến
                </option>
                <option value="tai_quay" {{ request('loai_ve')=='tai_quay' ? 'selected' : '' }}>
                    Tại quầy
                </option>
            </select>

            <div class="flex gap-3">
                <button type="submit" class="ticket-btn-primary">
                    <i class="fa-solid fa-filter"></i>
                    Lọc
                </button>

                <a href="{{ route('admin.ve-xem-phims.index') }}" class="ticket-btn-secondary">
                    Reset
                </a>
            </div>

        </form>

        {{-- Bảng vé --}}
        <div class="ticket-table-wrap">
            <table class="ticket-table">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Mã vé</th>
                        <th>Thông tin phim</th>
                        <th>Ghế</th>
                        <th>Thời gian</th>
                        <th>Loại</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th class="text-right">Hành động</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($tickets as $key => $ticket)
                    <tr>
                        <td class="ticket-index">
                            #{{ $tickets->firstItem() + $key }}
                        </td>

                        <td>
                            <span class="ticket-code">
                                {{ $ticket->ma_ve }}
                            </span>
                        </td>

                        <td>
                            <div class="ticket-movie">
                                <strong>{{ $ticket->ten_phim }}</strong>
                                <small>
                                    {{ $ticket->ten_rap ?? 'N/A' }}
                                    ·
                                    {{ $ticket->ten_phong ?? 'N/A' }}
                                </small>
                            </div>
                        </td>

                        <td>
                            <span class="ticket-seat">
                                {{ $ticket->ma_ghe ?? '-' }}
                            </span>
                        </td>

                        <td class="ticket-time">
                            {{ $ticket->thoi_gian_chieu ? $ticket->thoi_gian_chieu->format('d/m/Y H:i') : '-' }}
                        </td>

                        <td>
                            <span class="ticket-type">
                                {{ $ticket->loai_ve === 'tai_quay' ? 'Tại quầy' : 'Online' }}
                            </span>
                        </td>

                        <td class="ticket-money">
                            {{ number_format($ticket->tong_tien) }}đ
                        </td>

                        <td>
                            <form method="POST" action="{{ route('admin.ve-xem-phims.cap-nhat-trang-thai', $ticket) }}">
                                @csrf
                                @method('PATCH')

                                <select name="trang_thai" onchange="confirmTicketStatus(this)"
                                    data-current="{{ $ticket->trang_thai }}"
                                    class="ticket-status-select status-{{ $ticket->trang_thai }}">
                                    <option value="da_thanh_toan" {{ $ticket->trang_thai === 'da_thanh_toan' ?
                                        'selected' : '' }}>
                                        Đã thanh toán
                                    </option>
                                    <option value="da_su_dung" {{ $ticket->trang_thai === 'da_su_dung' ? 'selected' : ''
                                        }}>
                                        Đã sử dụng
                                    </option>
                                    <option value="da_huy" {{ $ticket->trang_thai === 'da_huy' ? 'selected' : '' }}>
                                        Đã hủy
                                    </option>
                                </select>
                            </form>
                        </td>

                        <td>
                            <div class="ticket-actions">
                                <a href="{{ route('admin.ve-xem-phims.show', $ticket) }}" class="ticket-action-btn view"
                                    title="Xem chi tiết">
                                    <i class="fa-solid fa-eye"></i>
                                </a>

                                @if ($ticket->trang_thai === 'da_thanh_toan')
                                <form method="POST" action="{{ route('admin.ve-xem-phims.su-dung', $ticket) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit" onclick="return confirm('Xác nhận vé này đã sử dụng?')"
                                        class="ticket-action-btn success" title="Đánh dấu đã sử dụng">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.ve-xem-phims.huy', $ticket) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit" onclick="return confirm('Bạn có chắc muốn hủy vé này?')"
                                        class="ticket-action-btn danger" title="Hủy vé">
                                        <i class="fa-solid fa-ban"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="ticket-empty">
                            Chưa có vé nào trong hệ thống
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ticket-pagination">
            {{ $tickets->links() }}
        </div>

    </div>

</div>

<style>
    .ticket-page {
        --gold: #d99a32;
        --gold-dark: #8a4a21;
        --panel: #101010;
        --panel-soft: rgba(255, 255, 255, 0.04);
        --border: rgba(255, 255, 255, 0.1);
    }

    .ticket-stat-card {
        position: relative;
        display: flex;
        align-items: center;
        gap: 16px;
        min-height: 116px;
        padding: 22px;
        border: 1px solid var(--border);
        border-radius: 26px;
        background:
            radial-gradient(circle at top right, rgba(217, 154, 50, 0.12), transparent 36%),
            linear-gradient(145deg, rgba(255, 255, 255, 0.075), rgba(255, 255, 255, 0.025));
        overflow: hidden;
        transition: transform .35s ease, border-color .35s ease, box-shadow .35s ease, background .35s ease;
    }

    .ticket-stat-card::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(120deg, transparent, rgba(255, 255, 255, 0.12), transparent);
        transform: translateX(-120%);
        transition: transform .65s ease;
    }

    .ticket-stat-card:hover {
        transform: translateY(-6px);
        border-color: rgba(217, 154, 50, 0.55);
        box-shadow: 0 22px 45px rgba(217, 154, 50, 0.12);
    }

    .ticket-stat-card:hover::before {
        transform: translateX(120%);
    }

    .ticket-stat-icon {
        position: relative;
        z-index: 1;
        width: 52px;
        height: 52px;
        display: grid;
        place-items: center;
        border-radius: 18px;
        font-size: 20px;
        background: rgba(217, 154, 50, 0.18);
        color: var(--gold);
        transition: transform .35s ease;
    }

    .ticket-stat-card:hover .ticket-stat-icon {
        transform: rotate(-6deg) scale(1.08);
    }

    .ticket-stat-card p {
        position: relative;
        z-index: 1;
        margin: 0;
        color: #9ca3af;
        font-size: 13px;
        font-weight: 700;
    }

    .ticket-stat-card h3 {
        position: relative;
        z-index: 1;
        margin-top: 4px;
        color: white;
        font-size: 32px;
        font-weight: 900;
        line-height: 1;
    }

    .accent-blue .ticket-stat-icon {
        background: rgba(59, 130, 246, .16);
        color: #93c5fd;
    }

    .accent-green .ticket-stat-icon {
        background: rgba(34, 197, 94, .16);
        color: #86efac;
    }

    .accent-red .ticket-stat-icon {
        background: rgba(239, 68, 68, .16);
        color: #fca5a5;
    }

    .ticket-panel {
        border: 1px solid var(--border);
        border-radius: 28px;
        background: rgba(16, 16, 16, .96);
        box-shadow: 0 24px 70px rgba(0, 0, 0, .25);
        overflow: hidden;
    }

    .ticket-panel-header {
        padding: 26px;
        border-bottom: 1px solid var(--border);
    }

    .ticket-panel-header h5 {
        margin: 0;
        color: white;
        font-size: 24px;
        font-weight: 900;
    }

    .ticket-panel-header p {
        margin-top: 6px;
        color: #9ca3af;
        font-size: 14px;
    }

    .ticket-filter {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 14px;
        padding: 22px 26px;
        border-bottom: 1px solid var(--border);
    }

    @media (min-width: 768px) {
        .ticket-filter {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (min-width: 1280px) {
        .ticket-filter {
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }
    }

    .ticket-input {
        height: 48px;
        width: 100%;
        border: 1px solid var(--border);
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.055);
        padding: 0 16px;
        color: white;
        font-size: 14px;
        outline: none;
        transition: all .3s ease;
    }

    .ticket-input:hover,
    .ticket-input:focus {
        border-color: rgba(217, 154, 50, .65);
        background: rgba(255, 255, 255, .085);
        box-shadow: 0 0 0 4px rgba(217, 154, 50, .08);
    }

    .ticket-input::placeholder {
        color: #6b7280;
    }

    .ticket-select,
    .ticket-select option {
        color: white;
        background: #151515;
    }

    .ticket-btn-primary,
    .ticket-btn-secondary {
        height: 48px;
        border-radius: 18px;
        padding: 0 18px;
        font-size: 14px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all .3s ease;
        white-space: nowrap;
    }

    .ticket-btn-primary {
        flex: 1;
        color: white;
        background: linear-gradient(135deg, var(--gold-dark), var(--gold));
        box-shadow: 0 10px 25px rgba(217, 154, 50, .14);
    }

    .ticket-btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 18px 35px rgba(217, 154, 50, .25);
    }

    .ticket-btn-secondary {
        color: white;
        border: 1px solid var(--border);
        background: rgba(255, 255, 255, .055);
    }

    .ticket-btn-secondary:hover {
        transform: translateY(-3px);
        background: rgba(255, 255, 255, .1);
        border-color: rgba(255, 255, 255, .18);
    }

    .ticket-table-wrap {
        padding: 22px 26px;
        overflow-x: auto;
    }

    .ticket-table {
        width: 100%;
        min-width: 1120px;
        border-collapse: separate;
        border-spacing: 0 10px;
        text-align: left;
    }

    .ticket-table thead th {
        padding: 0 14px 10px;
        color: #64748b;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .ticket-table tbody tr {
        background: rgba(255, 255, 255, .025);
        transition: transform .28s ease, background .28s ease, box-shadow .28s ease;
    }

    .ticket-table tbody tr:hover {
        transform: translateY(-3px);
        background: rgba(217, 154, 50, .06);
        box-shadow: 0 16px 36px rgba(217, 154, 50, .08);
    }

    .ticket-table tbody tr td {
        padding: 16px 14px;
        color: #d1d5db;
        font-size: 14px;
        border-top: 1px solid rgba(255, 255, 255, .055);
        border-bottom: 1px solid rgba(255, 255, 255, .055);
    }

    .ticket-table tbody tr td:first-child {
        border-left: 1px solid rgba(255, 255, 255, .055);
        border-radius: 18px 0 0 18px;
    }

    .ticket-table tbody tr td:last-child {
        border-right: 1px solid rgba(255, 255, 255, .055);
        border-radius: 0 18px 18px 0;
    }

    .ticket-index {
        color: #64748b !important;
        font-weight: 700;
    }

    .ticket-code {
        color: var(--gold);
        font-weight: 900;
        white-space: nowrap;
    }

    .ticket-movie strong {
        display: block;
        color: white;
        font-weight: 900;
        transition: color .25s ease;
    }

    tr:hover .ticket-movie strong {
        color: var(--gold);
    }

    .ticket-movie small {
        display: block;
        margin-top: 4px;
        color: #64748b;
        font-size: 13px;
    }

    .ticket-seat {
        color: #e5e7eb;
        font-weight: 700;
    }

    .ticket-time {
        white-space: nowrap;
        color: #cbd5e1 !important;
    }

    .ticket-type {
        display: inline-flex;
        border-radius: 999px;
        padding: 6px 10px;
        background: rgba(59, 130, 246, .15);
        color: #93c5fd;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }

    .ticket-money {
        color: white !important;
        font-weight: 900;
        white-space: nowrap;
    }

    .ticket-status-select {
        min-width: 132px;
        height: 36px;
        border-radius: 999px;
        padding: 0 12px;
        border: 1px solid transparent;
        outline: none;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
        transition: all .28s ease;
    }

    .ticket-status-select:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, .25);
    }

    .ticket-status-select option {
        color: white;
        background: #151515;
    }

    .status-da_thanh_toan {
        color: #86efac;
        background: rgba(34, 197, 94, .14);
        border-color: rgba(34, 197, 94, .25);
    }

    .status-da_su_dung {
        color: #d1d5db;
        background: rgba(107, 114, 128, .18);
        border-color: rgba(107, 114, 128, .35);
    }

    .status-da_huy {
        color: #fca5a5;
        background: rgba(239, 68, 68, .14);
        border-color: rgba(239, 68, 68, .25);
    }

    .ticket-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }

    .ticket-action-btn {
        width: 40px;
        height: 40px;
        display: grid;
        place-items: center;
        border: 0;
        border-radius: 14px;
        transition: all .28s ease;
    }

    .ticket-action-btn:hover {
        transform: translateY(-4px) scale(1.08);
    }

    .ticket-action-btn.view {
        color: #93c5fd;
        background: rgba(59, 130, 246, .15);
    }

    .ticket-action-btn.view:hover {
        background: rgba(59, 130, 246, .28);
        box-shadow: 0 12px 25px rgba(59, 130, 246, .2);
    }

    .ticket-action-btn.success {
        color: #86efac;
        background: rgba(34, 197, 94, .15);
    }

    .ticket-action-btn.success:hover {
        background: rgba(34, 197, 94, .28);
        box-shadow: 0 12px 25px rgba(34, 197, 94, .18);
    }

    .ticket-action-btn.danger {
        color: #fca5a5;
        background: rgba(239, 68, 68, .15);
    }

    .ticket-action-btn.danger:hover {
        background: rgba(239, 68, 68, .28);
        box-shadow: 0 12px 25px rgba(239, 68, 68, .18);
    }

    .ticket-empty {
        padding: 60px 20px !important;
        text-align: center;
        color: #6b7280 !important;
        border-radius: 18px !important;
    }

    .ticket-pagination {
        padding: 18px 26px;
        border-top: 1px solid var(--border);
    }

    </style>

<script>
    function confirmTicketStatus(select) {
        const oldValue = select.dataset.current;
        const newValue = select.value;

        if (oldValue === newValue) {
            return;
        }

        let statusText = '';

        if (newValue === 'da_thanh_toan') {
            statusText = 'Đã thanh toán';
        }

        if (newValue === 'da_su_dung') {
            statusText = 'Đã sử dụng';
        }

        if (newValue === 'da_huy') {
            statusText = 'Đã hủy';
        }

        Swal.fire({
            title: 'Xác nhận cập nhật',
            text: `Bạn có chắc muốn chuyển vé sang "${statusText}" không?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Xác nhận',
            cancelButtonText: 'Hủy',
            confirmButtonColor: '#d99a32',
            cancelButtonColor: '#6b7280',
            background: '#151515',
            color: '#ffffff'
        }).then((result) => {
            if (result.isConfirmed) {
                select.form.submit();
            } else {
                select.value = oldValue;
            }
        });
    }
</script>

@endsection