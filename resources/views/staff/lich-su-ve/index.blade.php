@extends('layouts.admin')

@section('title', 'Lịch sử vé')
@section('page-title', 'Lịch sử vé')

@section('content')
<div class="ticket-history-page">
    <div class="history-header">
        <div>
            <h2>Lịch sử vé</h2>
            <p>Tra cứu vé đã bán, vé online, vé tại quầy và trạng thái sử dụng vé.</p>
        </div>

        <div class="history-icon">
            <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
    </div>

    {{-- Bộ lọc tìm kiếm --}}
    <form method="GET" action="{{ route('staff.lich-su-ve.index') }}" class="filter-card">
        <div class="filter-grid">
            <div class="filter-group">
                <label>Tìm kiếm</label>
                <div class="input-soft">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input
                        type="text"
                        name="keyword"
                        value="{{ request('keyword') }}"
                        placeholder="Nhập mã vé, tên phim hoặc mã ghế..."
                    >
                </div>
            </div>

            <div class="filter-group">
                <label>Loại vé</label>
                <select name="loai_ve" class="select-soft">
                    <option value="">Tất cả</option>
                    <option value="truc_tuyen" {{ request('loai_ve') === 'truc_tuyen' ? 'selected' : '' }}>Trực tuyến</option>
                    <option value="tai_quay" {{ request('loai_ve') === 'tai_quay' ? 'selected' : '' }}>Tại quầy</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Trạng thái</label>
                <select name="trang_thai" class="select-soft">
                    <option value="">Tất cả</option>
                    <option value="da_thanh_toan" {{ request('trang_thai') === 'da_thanh_toan' ? 'selected' : '' }}>Đã thanh toán</option>
                    <option value="da_su_dung" {{ request('trang_thai') === 'da_su_dung' ? 'selected' : '' }}>Đã sử dụng</option>
                    <option value="da_huy" {{ request('trang_thai') === 'da_huy' ? 'selected' : '' }}>Đã hủy</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-filter">
                    <i class="fa-solid fa-filter"></i>
                    Lọc
                </button>

                <a href="{{ route('staff.lich-su-ve.index') }}" class="btn-reset">
                    <i class="fa-solid fa-rotate-left"></i>
                    Reset
                </a>
            </div>
        </div>
    </form>

    {{-- Bảng lịch sử vé --}}
    <div class="table-card">
        <div class="table-title">
            <div>
                <h3>Danh sách vé</h3>
                <p>Tổng cộng: {{ $tickets->total() }} vé</p>
            </div>
        </div>

        <div class="table-responsive-custom">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Mã vé</th>
                        <th>Phim</th>
                        <th>Ghế</th>
                        <th>Suất chiếu</th>
                        <th>Loại vé</th>
                        <th>Trạng thái</th>
                        <th>Người tạo</th>
                        <th>Tổng tiền</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($tickets as $ticket)
                        <tr>
                            <td>
                                <span class="ticket-code">{{ $ticket->ma_ve }}</span>
                            </td>

                            <td>
                                <strong>{{ $ticket->ten_phim }}</strong>
                                <div class="sub-text">{{ $ticket->ten_rap ?? 'Chưa có rạp' }}</div>
                            </td>

                            <td>
                                <span class="seat-badge">{{ $ticket->ma_ghe ?? 'N/A' }}</span>
                            </td>

                            <td>
                                {{ $ticket->thoi_gian_chieu ? $ticket->thoi_gian_chieu->format('d/m/Y H:i') : 'Chưa có' }}
                            </td>

                            <td>
                                @if ($ticket->loai_ve === 'tai_quay')
                                    <span class="type-badge type-offline">Tại quầy</span>
                                @else
                                    <span class="type-badge type-online">Trực tuyến</span>
                                @endif
                            </td>

                            <td>
                                @if ($ticket->trang_thai === 'da_thanh_toan')
                                    <span class="status-badge status-paid">Đã thanh toán</span>
                                @elseif ($ticket->trang_thai === 'da_su_dung')
                                    <span class="status-badge status-used">Đã sử dụng</span>
                                @else
                                    <span class="status-badge status-cancel">Đã hủy</span>
                                @endif
                            </td>

                            <td>
                                @if ($ticket->loai_ve === 'tai_quay')
                                    {{ $ticket->nhanVien->ho_ten ?? 'Nhân viên' }}
                                @else
                                    {{ $ticket->nguoiDung->ho_ten ?? 'Khách hàng' }}
                                @endif
                            </td>

                            <td>
                                <strong class="money">{{ number_format($ticket->tong_tien, 0, ',', '.') }}đ</strong>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-box">
                                    <i class="fa-solid fa-ticket-simple"></i>
                                    <p>Chưa có vé nào phù hợp với điều kiện tìm kiếm.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            {{ $tickets->links() }}
        </div>
    </div>
</div>

<style>
    .ticket-history-page {
        animation: fadeIn 0.35s ease;
    }

    .history-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
    }

    .history-header h2 {
        margin: 0;
        color: #fff;
        font-size: 30px;
        font-weight: 900;
    }

    .history-header p {
        margin-top: 8px;
        color: #aaa;
    }

    .history-icon {
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

    .history-icon:hover {
        transform: translateY(-4px) scale(1.04);
        box-shadow: 0 0 42px rgba(245,166,35,.3);
    }

    .history-icon i {
        font-size: 28px;
    }

    .filter-card,
    .table-card {
        background: linear-gradient(145deg, #171717, #101010);
        border: 1px solid rgba(245,166,35,.26);
        border-radius: 30px;
        padding: 26px;
        box-shadow: 0 20px 55px rgba(0,0,0,.28);
        transition: all .32s ease;
    }

    .filter-card:hover,
    .table-card:hover {
        transform: translateY(-4px);
        border-color: rgba(245,166,35,.65);
        box-shadow: 0 26px 70px rgba(0,0,0,.45), 0 0 28px rgba(245,166,35,.12);
    }

    .filter-card {
        margin-bottom: 24px;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: 1.5fr .8fr .8fr auto;
        gap: 18px;
        align-items: end;
    }

    .filter-group label {
        display: block;
        margin-bottom: 10px;
        color: #eee;
        font-weight: 800;
    }

    .input-soft {
        display: flex;
        align-items: center;
        gap: 12px;
        height: 52px;
        border-radius: 18px;
        padding: 0 16px;
        background: rgba(255,255,255,.04);
        border: 1px solid rgba(245,166,35,.28);
        transition: all .28s ease;
    }

    .input-soft:focus-within {
        border-color: #f5a623;
        box-shadow: 0 0 0 5px rgba(245,166,35,.12);
    }

    .input-soft i {
        color: #f5a623;
    }

    .input-soft input {
        width: 100%;
        background: transparent;
        border: none;
        outline: none;
        color: #fff;
        font-weight: 600;
    }

    .input-soft input::placeholder {
        color: #777;
    }

    .select-soft {
        width: 100%;
        height: 52px;
        border-radius: 18px;
        padding: 0 14px;
        color: #fff;
        background: #121212;
        border: 1px solid rgba(245,166,35,.28);
        outline: none;
        transition: all .28s ease;
    }

    .select-soft:focus {
        border-color: #f5a623;
        box-shadow: 0 0 0 5px rgba(245,166,35,.12);
    }

    .filter-actions {
        display: flex;
        gap: 10px;
    }

    .btn-filter,
    .btn-reset {
        height: 52px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border-radius: 18px;
        padding: 0 18px;
        font-weight: 900;
        text-decoration: none;
        border: none;
        transition: all .28s ease;
        white-space: nowrap;
    }

    .btn-filter {
        color: #fff;
        background: linear-gradient(135deg, #d89227, #f5a623);
        box-shadow: 0 12px 28px rgba(245,166,35,.18);
    }

    .btn-filter:hover {
        transform: translateY(-3px) scale(1.01);
        box-shadow: 0 18px 42px rgba(245,166,35,.32);
    }

    .btn-reset {
        color: #ddd;
        background: rgba(255,255,255,.08);
    }

    .btn-reset:hover {
        color: #fff;
        background: rgba(255,255,255,.14);
        transform: translateY(-3px);
    }

    .table-title {
        display: flex;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    .table-title h3 {
        margin: 0;
        color: #fff;
        font-size: 22px;
        font-weight: 900;
    }

    .table-title p {
        margin-top: 6px;
        color: #aaa;
    }

    .table-responsive-custom {
        width: 100%;
        overflow-x: auto;
    }

    .history-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 10px;
    }

    .history-table thead th {
        color: #f5a623;
        font-size: 13px;
        text-transform: uppercase;
        padding: 12px 14px;
        white-space: nowrap;
    }

    .history-table tbody tr {
        background: rgba(255,255,255,.04);
        transition: all .25s ease;
    }

    .history-table tbody tr:hover {
        background: rgba(245,166,35,.08);
        transform: translateY(-2px);
    }

    .history-table tbody td {
        padding: 16px 14px;
        color: #ddd;
        vertical-align: middle;
        white-space: nowrap;
    }

    .history-table tbody td:first-child {
        border-top-left-radius: 18px;
        border-bottom-left-radius: 18px;
    }

    .history-table tbody td:last-child {
        border-top-right-radius: 18px;
        border-bottom-right-radius: 18px;
    }

    .ticket-code {
        color: #f5a623;
        font-weight: 900;
    }

    .sub-text {
        margin-top: 4px;
        color: #888;
        font-size: 13px;
    }

    .seat-badge,
    .type-badge,
    .status-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 7px 12px;
        font-size: 13px;
        font-weight: 900;
    }

    .seat-badge {
        color: #fff;
        background: rgba(255,255,255,.1);
    }

    .type-online {
        color: #b9e4ff;
        background: rgba(59,130,246,.16);
        border: 1px solid rgba(59,130,246,.35);
    }

    .type-offline {
        color: #ffe4a3;
        background: rgba(245,166,35,.14);
        border: 1px solid rgba(245,166,35,.35);
    }

    .status-paid {
        color: #ffe4a3;
        background: rgba(245,166,35,.14);
        border: 1px solid rgba(245,166,35,.35);
    }

    .status-used {
        color: #b9ffd2;
        background: rgba(34,197,94,.14);
        border: 1px solid rgba(34,197,94,.35);
    }

    .status-cancel {
        color: #ffcccc;
        background: rgba(239,68,68,.14);
        border: 1px solid rgba(239,68,68,.35);
    }

    .money {
        color: #f5a623;
    }

    .empty-box {
        min-height: 200px;
        display: grid;
        place-items: center;
        text-align: center;
        color: #888;
    }

    .empty-box i {
        font-size: 42px;
        color: rgba(245,166,35,.45);
        margin-bottom: 12px;
    }

    .pagination-wrap {
        margin-top: 18px;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 1100px) {
        .filter-grid {
            grid-template-columns: 1fr;
        }

        .filter-actions {
            width: 100%;
        }

        .btn-filter,
        .btn-reset {
            flex: 1;
        }
    }
</style>

@if(session('clear_food_cart'))
<script>
    localStorage.removeItem(
        'staff_food_cart_{{ session("clear_food_cart") }}'
    );
</script>
@endif
@endsection