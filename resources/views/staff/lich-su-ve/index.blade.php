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
                    <option value="cho_thanh_toan" {{ request('trang_thai') === 'cho_thanh_toan' ? 'selected' : '' }}>Chờ thanh toán</option>
                    <option value="da_thanh_toan" {{ request('trang_thai') === 'da_thanh_toan' ? 'selected' : '' }}>Đã thanh toán</option>
                    <option value="da_in" {{ request('trang_thai') === 'da_in' ? 'selected' : '' }}>Đã in</option>
                    <option value="da_su_dung" {{ request('trang_thai') === 'da_su_dung' ? 'selected' : '' }}>Đã sử dụng</option>
                    <option value="da_huy" {{ request('trang_thai') === 'da_huy' ? 'selected' : '' }}>Đã hủy</option>
                    <option value="het_han" {{ request('trang_thai') === 'het_han' ? 'selected' : '' }}>Hết hạn</option>
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
                        <th>Thao tác</th>
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
                                @if ($ticket->trang_thai === 'cho_thanh_toan')
                                    <span class="status-badge status-pending">Chờ thanh toán</span>
                                @elseif ($ticket->trang_thai === 'da_thanh_toan')
                                    <span class="status-badge status-paid">Đã thanh toán</span>
                                @elseif ($ticket->trang_thai === 'da_in')
                                    <span class="status-badge status-printed">Đã in</span>
                                @elseif ($ticket->trang_thai === 'da_su_dung')
                                    <span class="status-badge status-used">Đã sử dụng</span>
                                @elseif ($ticket->trang_thai === 'da_huy')
                                    <span class="status-badge status-cancel">Đã hủy</span>
                                @elseif ($ticket->trang_thai === 'het_han')
                                    <span class="status-badge status-expired">Hết hạn</span>
                                @else
                                    <span class="status-badge">
                                        {{ $ticket->trang_thai ?: 'Không xác định' }}
                                    </span>
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

                            <td>
                                @if (
                                    $ticket->loai_ve === 'tai_quay'
                                    && $ticket->payment_method === 'vietqr'
                                    && $ticket->trang_thai === 'cho_thanh_toan'
                                )
                                    <div class="pending-actions">
                                        <a
                                            href="{{ route('staff.ban-ve.vietqr-waiting', ['id' => $ticket->id]) }}"
                                            class="btn-continue-payment"
                                            title="Tiếp tục giao dịch VietQR"
                                        >
                                            <i class="fa-solid fa-qrcode"></i>
                                            <span>Tiếp tục</span>
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route('staff.ban-ve.vietqr-cancel', ['id' => $ticket->id]) }}"
                                            onsubmit="return confirm('Hủy giao dịch này và giải phóng ghế {{ $ticket->ma_ghe }}?')"
                                        >
                                            @csrf
                                            <button
                                                type="submit"
                                                class="btn-cancel-pending"
                                                title="Hủy và giải phóng ghế"
                                            >
                                                <i class="fa-solid fa-xmark"></i>
                                                <span>Hủy</span>
                                            </button>
                                        </form>
                                    </div>
                                @elseif (
                                    $ticket->loai_ve === 'tai_quay'
                                    && in_array(
                                        $ticket->trang_thai,
                                        ['da_thanh_toan', 'da_in'],
                                        true
                                    )
                                )
                                    <button
                                        type="button"
                                        class="btn-print-history"
                                        data-ticket-id="{{ $ticket->id }}"
                                        data-ticket-status="{{ $ticket->trang_thai }}"
                                    >
                                        <i class="fa-solid fa-print"></i>
                                        <span>
                                            {{ $ticket->trang_thai === 'da_in'
                                                ? 'In lại'
                                                : 'In vé'
                                            }}
                                        </span>
                                    </button>
                                @elseif ($ticket->trang_thai === 'da_su_dung')
                                    <span class="action-note action-used">
                                        <i class="fa-solid fa-circle-check"></i>
                                        Đã sử dụng
                                    </span>
                                @elseif ($ticket->trang_thai === 'da_huy')
                                    <span class="action-note action-cancel">
                                        <i class="fa-solid fa-ban"></i>
                                        Đã hủy
                                    </span>
                                @elseif ($ticket->trang_thai === 'het_han')
                                    <span class="action-note action-expired">
                                        <i class="fa-solid fa-clock"></i>
                                        Hết hạn
                                    </span>
                                @else
                                    <span class="action-note">---</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
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

    .status-pending {
        color: #fde68a;
        background: rgba(234,179,8,.12);
        border: 1px solid rgba(234,179,8,.35);
    }

    .status-paid {
        color: #ffe4a3;
        background: rgba(245,166,35,.14);
        border: 1px solid rgba(245,166,35,.35);
    }

    .status-printed {
        color: #c7d2fe;
        background: rgba(99,102,241,.14);
        border: 1px solid rgba(99,102,241,.35);
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

    .status-expired {
        color: #cbd5e1;
        background: rgba(100,116,139,.14);
        border: 1px solid rgba(100,116,139,.35);
    }

    .pending-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .pending-actions form {
        margin: 0;
    }

    .btn-continue-payment,
    .btn-cancel-pending {
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 0 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
        text-decoration: none;
        cursor: pointer;
        transition: all .25s ease;
    }

    .btn-continue-payment {
        color: #16100a;
        background: linear-gradient(135deg, #f5a623, #ffd166);
        border: 1px solid rgba(245,166,35,.55);
    }

    .btn-continue-payment:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(245,166,35,.25);
    }

    .btn-cancel-pending {
        color: #fecaca;
        background: rgba(239,68,68,.12);
        border: 1px solid rgba(239,68,68,.35);
    }

    .btn-cancel-pending:hover {
        color: #fff;
        background: rgba(239,68,68,.28);
        transform: translateY(-2px);
    }

    .action-expired {
        color: #94a3b8;
    }

    .money {
        color: #f5a623;
    }

    .btn-print-history {
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0 14px;
        border: 1px solid rgba(245,166,35,.55);
        border-radius: 12px;
        color: #16100a;
        background: linear-gradient(135deg, #f5a623, #ffd166);
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
        white-space: nowrap;
        transition: all .25s ease;
        box-shadow: 0 8px 22px rgba(245,166,35,.16);
    }

    .btn-print-history:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(245,166,35,.3);
    }

    .btn-print-history:disabled {
        opacity: .6;
        cursor: wait;
        transform: none;
    }

    .action-note {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #888;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .action-used {
        color: #86efac;
    }

    .action-cancel {
        color: #fca5a5;
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


<iframe
    id="historyPrintFrame"
    title="In vé"
    style="
        position: fixed;
        width: 1px;
        height: 1px;
        right: 0;
        bottom: 0;
        border: 0;
        opacity: 0;
        pointer-events: none;
    "
></iframe>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const printFrame = document.getElementById('historyPrintFrame');
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? '';

        let currentButton = null;
        let isPrinting = false;

        function restoreButton() {
            if (!currentButton) {
                isPrinting = false;
                return;
            }

            const status = currentButton.dataset.ticketStatus;

            currentButton.disabled = false;

            currentButton.innerHTML = status === 'da_in'
                ? '<i class="fa-solid fa-print"></i><span>In lại</span>'
                : '<i class="fa-solid fa-print"></i><span>In vé</span>';

            currentButton = null;
            isPrinting = false;
        }

        async function markTicketAsPrinted(ticketId) {
            const urlTemplate = @json(
                route(
                    'staff.ban-ve.mark-printed',
                    ['id' => '__TICKET_ID__']
                )
            );

            const url = urlTemplate.replace(
                '__TICKET_ID__',
                ticketId
            );

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(
                    data.message
                    ?? 'Không thể cập nhật trạng thái vé.'
                );
            }

            return data;
        }

        function openPrintDialog(ticketId) {
            const urlTemplate = @json(
                route(
                    'staff.ban-ve.print-ticket',
                    ['id' => '__TICKET_ID__']
                )
            );

            const printUrl = urlTemplate.replace(
                '__TICKET_ID__',
                ticketId
            );

            printFrame.onload = function () {
                setTimeout(function () {
                    try {
                        const printWindow = printFrame.contentWindow;

                        printWindow.focus();
                        printWindow.print();
                    } catch (error) {
                        console.error(
                            'Không thể mở hộp thoại in:',
                            error
                        );

                        alert(
                            'Không thể mở hộp thoại in. '
                            + 'Vui lòng thử lại.'
                        );
                    } finally {
                        restoreButton();
                    }
                }, 500);
            };

            printFrame.src =
                printUrl
                + '?embedded=1&t='
                + Date.now();
        }

        document.addEventListener('click', async function (event) {
            const button = event.target.closest(
                '.btn-print-history'
            );

            if (!button || isPrinting) {
                return;
            }

            const ticketId = button.dataset.ticketId;
            const status = button.dataset.ticketStatus;

            if (!ticketId) {
                return;
            }

            if (!['da_thanh_toan', 'da_in'].includes(status)) {
                alert(
                    'Vé này không ở trạng thái cho phép in.'
                );
                return;
            }

            isPrinting = true;
            currentButton = button;

            button.disabled = true;
            button.innerHTML =
                '<i class="fa-solid fa-spinner fa-spin"></i>'
                + '<span>Đang chuẩn bị...</span>';

            try {
                if (status === 'da_thanh_toan') {
                    await markTicketAsPrinted(ticketId);

                    /*
                     * Cập nhật ngay trên giao diện để người dùng
                     * thấy vé đã chuyển sang trạng thái Đã in.
                     */
                    button.dataset.ticketStatus = 'da_in';

                    const row = button.closest('tr');
                    const statusCell = row?.children[5];

                    if (statusCell) {
                        statusCell.innerHTML =
                            '<span class="status-badge status-printed">'
                            + 'Đã in'
                            + '</span>';
                    }
                }

                openPrintDialog(ticketId);
            } catch (error) {
                console.error(error);

                alert(
                    error.message
                    ?? 'Không thể chuẩn bị vé để in.'
                );

                restoreButton();
            }
        });
    });
</script>

@if(session('clear_food_cart'))
<script>
    localStorage.removeItem(
        'staff_food_cart_{{ session("clear_food_cart") }}'
    );
</script>
@endif
@endsection