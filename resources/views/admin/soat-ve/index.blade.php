@extends('layouts.admin')

@section('title', 'Soát vé QR - CineHome')
@section('page-title', 'Soát vé QR')
@section('page-subtitle', 'Quét mã QR hoặc nhập mã vé để kiểm tra trước khi xác nhận khách vào phòng chiếu')

@section('content')

<style>
    /* Badge cho trạng thái Đã in trong giao diện Admin */
    .scan-status-pill.status-da_in {
        background: #0284c7 !important;
        color: #ffffff !important;
    }
    .scan-status-pill.status-da_thanh_toan {
        background: #eab308 !important;
        color: #000000 !important;
    }

    /* Cảnh báo vé chưa in */
    .unprinted-warning-box {
        background: rgba(239, 68, 68, 0.15);
        border: 1px solid rgba(239, 68, 68, 0.4);
        color: #fca5a5;
        padding: 10px 14px;
        border-radius: 10px;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
    }

    /* Nút in vé ngay */
    .scan-btn-print {
        background: #0284c7 !important;
        color: #ffffff !important;
        border: none;
        width: 100%;
        padding: 12px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 15px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s ease;
    }
    .scan-btn-print:hover {
        background: #0369a1 !important;
        box-shadow: 0 4px 12px rgba(2, 132, 199, 0.4);
    }
</style>

@php
$ticket = session('ticket');
$foods = [];

if ($ticket) {
    if (is_array($ticket)) {
        $foods = $ticket['foods'] ?? [];
    } else {
        $foods = $ticket->foods_list ?? [];
    }
}

$statusLabel = [
    'da_thanh_toan' => 'Đã thanh toán',
    'da_in' => 'Đã in',
    'da_su_dung' => 'Đã sử dụng',
    'da_huy' => 'Đã hủy',
];

$typeLabel = [
    'truc_tuyen' => 'Trực tuyến',
    'tai_quay' => 'Tại quầy',
];
@endphp

<div class="admin-qr-page">
    @if (session('success'))
    <div class="admin-qr-alert is-success">
        <i class="fa-solid fa-circle-check"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if (session('error'))
    <div class="admin-qr-alert is-error">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <div class="admin-qr-layout">
        <section class="admin-qr-panel admin-qr-camera-panel">
            <div class="admin-qr-panel-head">
                <div>
                    <span class="admin-eyebrow">Camera</span>
                    <h3>Khung quét QR</h3>
                    <p>Đặt mã QR nằm gọn trong khung sáng. Hệ thống kiểm tra vé đã in trước khi xác nhận khách vào rạp.</p>
                </div>
                <span class="admin-qr-panel-icon"><i class="fa-solid fa-camera"></i></span>
            </div>

            <div class="camera-box">
                <video id="qrVideo" playsinline muted></video>
                <div id="html5QrReader"></div>
                <div class="scan-frame" aria-hidden="true">
                    <span></span>
                </div>
                <div class="camera-empty">
                    <i class="fa-solid fa-camera-retro"></i>
                    <strong>Camera chưa bật</strong>
                    <small>Bấm bật camera để bắt đầu quét mã QR</small>
                </div>
            </div>

            <div class="scan-toolbar">
                <button type="button" id="startScannerBtn" class="scan-btn scan-btn-primary">
                    <i class="fa-solid fa-camera"></i>
                    Bật camera
                </button>
                <button type="button" id="stopScannerBtn" class="scan-btn scan-btn-secondary" disabled>
                    <i class="fa-solid fa-stop"></i>
                    Tắt camera
                </button>
            </div>

            <div id="scannerStatus" class="scanner-status">
                <i class="fa-solid fa-circle-info"></i>
                <span>Quét QR hoặc nhập mã vé thủ công để kiểm tra vé.</span>
            </div>

            <form id="ticketCheckForm" method="POST" action="{{ route('admin.soat-ve.check') }}" class="manual-form">
                @csrf
                <div class="manual-form-head">
                    <label for="ticketCodeInput">Nhập mã vé thủ công</label>
                    <small>Dùng khi camera không đọc được QR.</small>
                </div>
                <div class="manual-input-row">
                    <div class="manual-input-wrap">
                        <i class="fa-solid fa-barcode"></i>
                        <input id="ticketCodeInput" type="text" name="ma_ve" value="{{ old('ma_ve') }}" placeholder="VD: VE260617ABC123" autocomplete="off">
                    </div>
                    <button type="submit" class="scan-btn scan-btn-submit">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        Kiểm tra
                    </button>
                </div>
                @error('ma_ve')
                <p class="scan-field-error">{{ $message }}</p>
                @enderror
            </form>
        </section>

        <aside class="admin-qr-panel admin-qr-result-panel">
            <div class="admin-qr-panel-head">
                <div>
                    <span class="admin-eyebrow">Kết quả</span>
                    <h3>Thông tin vé</h3>
                    <p>Chỉ xác nhận sử dụng vé khi khách đã ở trước cổng vào phòng chiếu.</p>
                </div>
                <span class="admin-qr-panel-icon"><i class="fa-solid fa-ticket"></i></span>
            </div>

            <div id="ticketResult">
                @if ($ticket)
                @php
                $ticketObj = is_array($ticket) ? (object)$ticket : $ticket;
                $printTicketData = [
                    'ma_ve' => $ticketObj->ma_ve,
                    'trang_thai' => $ticketObj->trang_thai,
                    'ten_phim' => $ticketObj->ten_phim ?? 'N/A',
                    'gioi_han_tuoi' => $ticketObj->gioi_han_tuoi ?? 'P',
                    'ten_rap' => $ticketObj->ten_rap ?? 'CineHome Cinema',
                    'ten_phong' => $ticketObj->ten_phong ?? 'Chưa có',
                    'ma_ghe' => $ticketObj->ma_ghe ?? 'Chưa có',
                    'thoi_gian_chieu' => isset($ticketObj->thoi_gian_chieu) ? (is_string($ticketObj->thoi_gian_chieu) ? $ticketObj->thoi_gian_chieu : $ticketObj->thoi_gian_chieu->format('d/m/Y H:i')) : 'Chưa có',
                    'tong_tien' => number_format((float) $ticketObj->tong_tien, 0, ',', '.') . 'đ',
                    'loai_ve_label' => $typeLabel[$ticketObj->loai_ve] ?? 'TRỰC TUYẾN',
                    'foods' => $foods,
                ];
                @endphp

                <div class="ticket-result">
                    <div class="ticket-result-top">
                        <div>
                            <span>Mã vé</span>
                            <strong>{{ $ticketObj->ma_ve }}</strong>
                        </div>
                        <em class="scan-status-pill status-{{ $ticketObj->trang_thai }}">
                            {{ $statusLabel[$ticketObj->trang_thai] ?? 'Không rõ' }}
                        </em>
                    </div>

                    <div class="ticket-result-grid">
                        <div><span>Phim</span><strong>{{ $ticketObj->ten_phim }}</strong></div>
                        <div><span>Rạp</span><strong>{{ $ticketObj->ten_rap ?? 'Chưa có' }}</strong></div>
                        <div><span>Phòng</span><strong>{{ $ticketObj->ten_phong ?? 'Chưa có' }}</strong></div>
                        <div><span>Ghế</span><strong>{{ $ticketObj->ma_ghe ?? 'Chưa có' }}</strong></div>
                        <div><span>Suất chiếu</span><strong>{{ isset($ticketObj->thoi_gian_chieu) ? (is_string($ticketObj->thoi_gian_chieu) ? $ticketObj->thoi_gian_chieu : $ticketObj->thoi_gian_chieu->format('d/m/Y H:i')) : 'Chưa có' }}</strong></div>
                        <div><span>Tổng tiền</span><strong>{{ number_format((float) $ticketObj->tong_tien, 0, ',', '.') }}đ</strong></div>
                        <div><span>Loại vé</span><strong>{{ $typeLabel[$ticketObj->loai_ve] ?? 'Không rõ' }}</strong></div>
                    </div>

                    <div class="food-result-section">
                        <h3 class="food-section-title">
                            <i class="fa-solid fa-utensils"></i>
                            Đồ ăn & Combo kèm theo
                        </h3>

                        @if (count($foods) > 0)
                        <table class="food-table">
                            <tbody>
                                @foreach ($foods as $food)
                                @php
                                $tenMon = is_array($food) ? ($food['ten_mon'] ?? $food['name'] ?? 'Đồ ăn') : ($food->ten_mon ?? $food->name ?? 'Đồ ăn');
                                $soLuong = is_array($food) ? ($food['so_luong'] ?? $food['quantity'] ?? $food['qty'] ?? 1) : ($food->so_luong ?? $food->quantity ?? $food->qty ?? 1);
                                @endphp
                                <tr>
                                    <td class="food-name">{{ $tenMon }}</td>
                                    <td class="food-qty">x{{ $soLuong }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @else
                        <div class="no-food-alert">
                            <i class="fa-solid fa-circle-info"></i>
                            Không có đồ ăn/bắp nước đi kèm với vé này.
                        </div>
                        @endif
                    </div>

                    @if ($ticketObj->trang_thai === 'da_thanh_toan')
                    <div class="unprinted-warning-box">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span>Vé đã thanh toán nhưng chưa được in. Vui lòng in vé trước khi soát.</span>
                    </div>
                    <div class="confirm-form">
                        <button type="button" class="scan-btn scan-btn-print" id="btnPrintTicket" data-ticket-code="{{ $ticketObj->ma_ve }}" data-ticket-data="{{ e(json_encode($printTicketData, JSON_UNESCAPED_UNICODE)) }}">
                            <i class="fa-solid fa-print"></i>
                            In vé ngay
                        </button>
                    </div>
                    @elseif ($ticketObj->trang_thai === 'da_in' || $ticketObj->trang_thai === 'da_su_dung')
                    <div class="confirm-form" style="text-align: center; color: #38bdf8; font-weight: 600; padding: 12px; background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.25); border-radius: 10px;">
                        <i class="fa-solid fa-circle-check"></i> Vé đã được in & hoàn tất soát vé
                    </div>
                    @endif
                </div>
                @else
                <div class="empty-result">
                    <i class="fa-solid fa-qrcode"></i>
                    <h3>Chưa có vé nào được kiểm tra</h3>
                    <p>Bật camera để quét QR hoặc nhập mã vé thủ công.</p>
                </div>
                @endif
            </div>
        </aside>
    </div>
</div>

<!-- MODAL XÁC NHẬN ĐÃ IN VÉ THÀNH CÔNG -->
<div id="confirmPrintTicketModal" class="admin-checkin-modal" aria-hidden="true">
    <div class="admin-checkin-modal-content">
        <div class="admin-checkin-modal-header">
            <h3>
                <i class="fa-solid fa-print"></i>
                Xác nhận kết quả in vé
            </h3>
        </div>

        <div class="admin-checkin-modal-body">
            <p>Màn hình in nhiệt đã được mở. Máy in đã xuất vé thành công chưa?</p>
            <p class="admin-checkin-modal-highlight">
                Nếu chọn <strong>"Đã in thành công"</strong>, hệ thống sẽ lưu trạng thái và hoàn tất quy trình cho vé này.
            </p>
        </div>

        <div class="admin-checkin-modal-footer">
            <button type="button" id="modalBtnPrintCancel" class="admin-checkin-modal-btn is-secondary">
                <i class="fa-solid fa-xmark"></i>
                Chưa in / Lỗi in
            </button>

            <button type="button" id="modalBtnPrintConfirm" class="admin-checkin-modal-btn is-primary" style="background: #0284c7; border-color: #0284c7;">
                <i class="fa-solid fa-check"></i>
                Đã in thành công
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const html5QrcodeSrc = 'https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js';
        const video = document.getElementById('qrVideo');
        const html5QrReader = document.getElementById('html5QrReader');
        const cameraBox = document.querySelector('.camera-box');
        const startBtn = document.getElementById('startScannerBtn');
        const stopBtn = document.getElementById('stopScannerBtn');
        const statusBox = document.getElementById('scannerStatus');
        const statusText = statusBox.querySelector('span');
        const form = document.getElementById('ticketCheckForm');
        const input = document.getElementById('ticketCodeInput');
        const resultBox = document.getElementById('ticketResult');

        const checkUrl = @json(route('admin.soat-ve.check'));
        const printUrl = @json(route('admin.soat-ve.print'));
        const csrfToken = @json(csrf_token());

        // Modal xác nhận in vé
        const printModal = document.getElementById('confirmPrintTicketModal');
        const modalBtnPrintConfirm = document.getElementById('modalBtnPrintConfirm');
        const modalBtnPrintCancel = document.getElementById('modalBtnPrintCancel');

        let stream = null;
        let detector = null;
        let html5QrCode = null;
        let scanning = false;
        let requestBusy = false;
        let activeScanner = null;
        let lastValue = '';
        let lastScanAt = 0;

        let pendingPrintTicketCode = '';
        let pendingPrintTicketData = null;
        let html5QrcodeLoader = null;

        function ensureHtml5Qrcode() {
            if (window.Html5Qrcode) return Promise.resolve();
            if (html5QrcodeLoader) return html5QrcodeLoader;

            html5QrcodeLoader = new Promise(function(resolve, reject) {
                const script = document.createElement('script');
                script.src = html5QrcodeSrc;
                script.async = true;
                script.onload = () => resolve();
                script.onerror = () => {
                    html5QrcodeLoader = null;
                    reject(new Error('Không tải được thư viện quét QR.'));
                };
                document.head.appendChild(script);
            });

            return html5QrcodeLoader;
        }

        function setStatus(message, type = '') {
            statusText.textContent = message;
            statusBox.classList.remove('success', 'error');
            if (type) statusBox.classList.add(type);
        }

        function setScannerButtons(isActive) {
            startBtn.disabled = isActive;
            stopBtn.disabled = !isActive;
        }

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, function(char) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;',
                }[char];
            });
        }

        function renderEmpty(message) {
            resultBox.innerHTML = `
            <div class="empty-result">
                <i class="fa-solid fa-circle-info"></i>
                <h3>Chưa có thông tin vé</h3>
                <p>${escapeHtml(message)}</p>
            </div>
            `;
        }

        function renderTicket(ticket) {
            if (!ticket) {
                renderEmpty('Không tìm thấy vé phù hợp với mã vừa quét.');
                return;
            }

            let rawFoods = ticket.foods_list || ticket.foods || ticket.food || ticket.do_an || ticket.danh_sach_do_an || [];

            if (typeof rawFoods === 'string') {
                try {
                    rawFoods = JSON.parse(rawFoods);
                } catch (error) {
                    rawFoods = [];
                }
            }

            const foodHtml = Array.isArray(rawFoods) && rawFoods.length > 0 ? `
            <div class="food-result-section">
                <h3 class="food-section-title">
                    <i class="fa-solid fa-utensils"></i>
                    Đồ ăn & Combo kèm theo
                </h3>
                <table class="food-table">
                    <tbody>
                        ${rawFoods.map(function (food) {
                            const tenMon = food.ten_mon || food.name || 'Đồ ăn';
                            const soLuong = food.so_luong || food.quantity || food.qty || 1;
                            return `
                                <tr>
                                    <td class="food-name">${escapeHtml(tenMon)}</td>
                                    <td class="food-qty">x${escapeHtml(soLuong)}</td>
                                </tr>
                            `;
                        }).join('')}
                    </tbody>
                </table>
            </div>
            ` : `
            <div class="food-result-section">
                <h3 class="food-section-title">
                    <i class="fa-solid fa-utensils"></i>
                    Đồ ăn & Combo kèm theo
                </h3>
                <div class="no-food-alert">
                    <i class="fa-solid fa-circle-info"></i>
                    Không có đồ ăn/bắp nước đi kèm với vé này.
                </div>
            </div>
            `;

            let actionButton = '';
            let warningBox = '';

            if (ticket.trang_thai === 'da_thanh_toan') {
                warningBox = `
                <div class="unprinted-warning-box">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>Vé đã thanh toán nhưng chưa được in. Vui lòng in vé trước khi soát.</span>
                </div>
                `;
                actionButton = `
                <div class="confirm-form">
                    <button type="button" class="scan-btn scan-btn-print" id="btnPrintTicket" data-ticket-code="${escapeHtml(ticket.ma_ve)}" data-ticket-data="${escapeHtml(JSON.stringify(ticket))}">
                        <i class="fa-solid fa-print"></i>
                        In vé ngay
                    </button>
                </div>
                `;
            } else if (ticket.trang_thai === 'da_in' || ticket.trang_thai === 'da_su_dung') {
                actionButton = `
                <div class="confirm-form" style="text-align: center; color: #38bdf8; font-weight: 600; padding: 12px; background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.25); border-radius: 10px;">
                    <i class="fa-solid fa-circle-check"></i> Vé đã được in & hoàn tất soát vé
                </div>
                `;
            }

            const statusTextMap = {
                'da_thanh_toan': 'Đã thanh toán',
                'da_in': 'Đã in',
                'da_su_dung': 'Đã sử dụng',
                'da_huy': 'Đã hủy',
            };

            resultBox.innerHTML = `
            <div class="ticket-result">
                <div class="ticket-result-top">
                    <div>
                        <span>Mã vé</span>
                        <strong>${escapeHtml(ticket.ma_ve)}</strong>
                    </div>
                    <em class="scan-status-pill status-${escapeHtml(ticket.trang_thai)}">
                        ${escapeHtml(ticket.trang_thai_label || statusTextMap[ticket.trang_thai] || 'Không rõ')}
                    </em>
                </div>
                <div class="ticket-result-grid">
                    <div><span>Phim</span><strong>${escapeHtml(ticket.ten_phim || 'Chưa có')}</strong></div>
                    <div><span>Rạp</span><strong>${escapeHtml(ticket.ten_rap || 'Chưa có')}</strong></div>
                    <div><span>Phòng</span><strong>${escapeHtml(ticket.ten_phong || 'Chưa có')}</strong></div>
                    <div><span>Ghế</span><strong>${escapeHtml(ticket.ma_ghe || 'Chưa có')}</strong></div>
                    <div><span>Suất chiếu</span><strong>${escapeHtml(ticket.thoi_gian_chieu || 'Chưa có')}</strong></div>
                    <div><span>Tổng tiền</span><strong>${escapeHtml(ticket.tong_tien || '0đ')}</strong></div>
                    <div><span>Loại vé</span><strong>${escapeHtml(ticket.loai_ve_label || 'Không rõ')}</strong></div>
                </div>
                ${foodHtml}
                ${warningBox}
                ${actionButton}
            </div>
            `;
        }

        async function postTicket(url, value) {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ ma_ve: value })
            });

            let data = {};
            try {
                data = await response.json();
            } catch (error) {
                data = {
                    success: false,
                    message: 'Máy chủ không trả về dữ liệu hợp lệ.'
                };
            }
            return { response, data };
        }

        async function inspectTicket(rawValue) {
            const value = String(rawValue || '').trim();

            if (!value) {
                setStatus('Vui lòng nhập mã vé hoặc quét QR.', 'error');
                input.focus();
                return;
            }

            requestBusy = true;
            input.value = value;
            setStatus('Đang kiểm tra vé...');

            try {
                const { response, data } = await postTicket(checkUrl, value);

                if (data.ticket) {
                    input.value = data.ticket.ma_ve;
                    renderTicket(data.ticket);
                } else {
                    renderEmpty(data.message || 'Không tìm thấy vé.');
                }

                if (response.ok && data.success) {
                    if (data.ticket && data.ticket.trang_thai === 'da_thanh_toan') {
                        setStatus('Vé đã thanh toán nhưng chưa được in. Vui lòng bấm in vé.', 'error');
                    } else {
                        setStatus(data.message || 'Vé hợp lệ và đã hoàn tất.', 'success');
                    }
                } else {
                    setStatus(data.message || Object.values(data.errors || {})[0]?.[0] || 'Vé không hợp lệ.', 'error');
                }
            } catch (error) {
                setStatus('Không thể kết nối máy chủ. Vui lòng thử lại.', 'error');
            } finally {
                setTimeout(() => { requestBusy = false; }, 700);
            }
        }

        function getAgeRatingNotice(rating) {
            if (!rating) return '** Phim dành cho khán giả đúng độ tuổi theo quy định **';
            const r = String(rating).trim().toUpperCase();
            if (r === 'P') return '** Phim dán nhãn [P]: Phim dành cho mọi lứa tuổi **';
            if (r === 'K') return '** Phim dán nhãn [K]: Khán giả dưới 13 tuổi xem cùng người giám hộ **';
            if (r.includes('13')) return '** Phim dán nhãn [T13]: Phim dành cho khán giả từ đủ 13 tuổi trở lên **';
            if (r.includes('16')) return '** Phim dán nhãn [T16]: Phim dành cho khán giả từ đủ 16 tuổi trở lên **';
            if (r.includes('18')) return '** Phim dán nhãn [T18]: Phim dành cho khán giả từ đủ 18 tuổi trở lên **';
            return `** Phim dán nhãn [${r}]: Khán giả lưu ý tuân thủ đúng quy định độ tuổi **`;
        }

        // HÀM XÁC ĐỊNH LOẠI GHẾ (THƯỜNG / VIP / COUPLE) THEO MÃ GHẾ
        function getSeatTypeName(seatCode, ticket) {
            if (ticket && (ticket.ten_loai_ghe || ticket.loai_ghe)) {
                const lg = String(ticket.ten_loai_ghe || ticket.loai_ghe).toLowerCase();
                if (lg.includes('couple') || lg.includes('đôi')) return 'Ghế Couple';
                if (lg.includes('vip')) return 'Ghế VIP';
                if (lg.includes('thường') || lg.includes('thuong')) return 'Ghế Thường';
            }

            if (!seatCode || seatCode === 'Chưa xếp') return 'Ghế Thường';
            const match = String(seatCode).trim().match(/^([A-Z]+)/i);
            if (!match) return 'Ghế Thường';

            const row = match[1].toUpperCase();
            const rowIndex = row.charCodeAt(0) - 64; // A=1, B=2, C=3,...

            if (rowIndex <= 3) return 'Ghế Thường';
            if (rowIndex >= 8) return 'Ghế Couple';
            return 'Ghế VIP';
        }

        // HÀM XUẤT VÉ IN NHIỆT VỚI CĂN GIỮA PHIM, PHÒNG CHIẾU VÀ LOẠI GHẾ
        function printTicketThermal(ticket) {
            const printWindow = window.open('', '_blank', 'width=420,height=650');
            if (!printWindow) {
                alert('Trình duyệt đã chặn cửa sổ bật lên (popup). Vui lòng cho phép bật popup để in vé.');
                return;
            }

            let seatList = [];
            if (ticket.ma_ghe) {
                seatList = String(ticket.ma_ghe).split(',').map(s => s.trim()).filter(Boolean);
            }
            if (seatList.length === 0) {
                seatList = ['Chưa xếp'];
            }

            const foods = ticket.foods || ticket.foods_list || [];
            let foodsHtml = '';
            if (Array.isArray(foods) && foods.length > 0) {
                foodsHtml = `
                    <div style="border-top: 1px dashed #000; margin-top: 8px; padding-top: 8px;">
                        <strong>ĐỒ ĂN & COMBO KÈM THEO:</strong><br>
                        ${foods.map(f => `• ${f.ten_mon || f.name || 'Đồ ăn'} x${f.so_luong || f.quantity || 1}`).join('<br>')}
                    </div>
                `;
            }

            const ageNotice = getAgeRatingNotice(ticket.gioi_han_tuoi);

            const pagesHtml = seatList.map((seat, index) => {
                const currentFoodsHtml = (index === 0) ? foodsHtml : '';
                const ticketIndexLabel = seatList.length > 1 ? ` (${index + 1}/${seatList.length})` : '';
                const seatTypeStr = getSeatTypeName(seat, ticket);

                return `
                    <div class="ticket-page">
                        <div class="text-center">
                            <div class="title">CINEHOME CINEMA</div>
                            <div class="sub-title">VÉ XEM PHIM / CINEMA TICKET</div>
                        </div>
                        <div class="divider"></div>
                        <div class="text-center"><strong>MÃ VÉ:</strong> ${ticket.ma_ve}${ticketIndexLabel}</div>
                        
                        <!-- TÊN PHIM CĂN GIỮA NỔI BẬT -->
                        <div class="movie-title-center">${ticket.ten_phim}</div>
                        
                        <!-- PHÒNG CHIẾU CĂN GIỮA NỔI BẬT -->
                        <div class="room-title-center">PHÒNG: ${ticket.ten_phong || 'CHƯA XẾP'}</div>
                        
                        <div class="notice">${ageNotice}</div>
                        <div class="divider"></div>
                        <div class="info-row"><span>Rạp:</span> <strong>${ticket.ten_rap || 'CineHome Cinema'}</strong></div>
                        <div class="info-row"><span>Suất chiếu:</span> <strong>${ticket.thoi_gian_chieu || 'N/A'}</strong></div>
                        
                        <!-- KHUNG GHẾ CĂN GIỮA & HIỂN THỊ LOẠI GHẾ -->
                        <div class="seat-box-center">
                            <div>GHẾ: ${seat}</div>
                            <span class="seat-type-label">(${seatTypeStr})</span>
                        </div>
                        
                        <div class="info-row"><span>Giá vé:</span> <strong>${ticket.tong_tien || '0đ'}</strong></div>
                        <div class="info-row"><span>Loại vé:</span> <span>${ticket.loai_ve_label || 'TRỰC TUYẾN'}</span></div>
                        ${currentFoodsHtml}
                        <div class="divider"></div>
                        <div class="notice">
                            Cảm ơn quý khách. Chúc quý khách xem phim vui vẻ!<br>
                            * Vé đã mua vui lòng kiểm tra lại trước khi ra khỏi quầy *
                        </div>
                    </div>
                `;
            }).join('');

            const html = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>In vé - ${ticket.ma_ve}</title>
                    <style>
                        @page { size: 80mm auto; margin: 0; }
                        body {
                            font-family: 'Courier New', Courier, monospace, sans-serif;
                            width: 76mm;
                            padding: 8px;
                            margin: 0 auto;
                            color: #000;
                            font-size: 13px;
                            line-height: 1.35;
                        }
                        .ticket-page {
                            page-break-after: always;
                            padding-bottom: 12px;
                        }
                        .ticket-page:last-child {
                            page-break-after: avoid;
                        }
                        .text-center { text-align: center; }
                        .title { font-size: 18px; font-weight: bold; margin-bottom: 2px; }
                        .sub-title { font-size: 11px; margin-bottom: 6px; }
                        .divider { border-top: 1px dashed #000; margin: 6px 0; }
                        .info-row { display: flex; justify-content: space-between; margin-bottom: 3px; }
                        
                        /* CĂN GIỮA VÀ NỔI BẬT TÊN PHIM & PHÒNG */
                        .movie-title-center { 
                            font-size: 16px; 
                            font-weight: bold; 
                            text-align: center; 
                            margin: 8px 0 4px 0; 
                            text-transform: uppercase; 
                            line-height: 1.25;
                        }
                        .room-title-center { 
                            font-size: 14px; 
                            font-weight: bold; 
                            text-align: center; 
                            background: #f0f0f0; 
                            padding: 4px; 
                            margin: 4px 0; 
                            border: 1px dashed #000;
                            border-radius: 4px; 
                            text-transform: uppercase;
                        }
                        
                        /* KHUNG GHẾ KÈM LOẠI GHẾ CĂN GIỮA */
                        .seat-box-center { 
                            font-size: 18px; 
                            font-weight: bold; 
                            background: #000; 
                            color: #fff; 
                            padding: 6px 8px; 
                            text-align: center; 
                            margin: 8px 0; 
                            border-radius: 4px; 
                        }
                        .seat-type-label {
                            font-size: 11px;
                            font-weight: normal;
                            display: block;
                            margin-top: 2px;
                            letter-spacing: 0.5px;
                            text-transform: uppercase;
                        }

                        .notice { font-size: 10px; text-align: center; margin-top: 8px; }
                    </style>
                </head>
                <body>
                    ${pagesHtml}
                    <script>
                        window.onload = function() {
                            window.print();
                            setTimeout(function() { window.close(); }, 500);
                        };
                    <\/script>
                </body>
                </html>
            `;

            printWindow.document.open();
            printWindow.document.write(html);
            printWindow.document.close();
        }

        // BƯỚC XÁC NHẬN KẾT QUẢ IN VÉ (MỞ SAU KHI MÀN HÌNH IN HIỂN THỊ)
        function openPrintConfirmModal(ticketCode, ticketData) {
            pendingPrintTicketCode = ticketCode;
            pendingPrintTicketData = ticketData;
            printModal.classList.add('is-active');
        }

        modalBtnPrintCancel.addEventListener('click', function() {
            printModal.classList.remove('is-active');
            setStatus(`Chưa xác nhận in. Vé "${pendingPrintTicketCode}" vẫn giữ nguyên trạng thái Đã thanh toán.`, 'error');
            pendingPrintTicketCode = '';
            pendingPrintTicketData = null;
        });

        modalBtnPrintConfirm.addEventListener('click', async function() {
            printModal.classList.remove('is-active');

            if (!pendingPrintTicketCode) return;

            // Gửi AJAX cập nhật trạng thái đổi từ da_thanh_toan sang da_in
            setStatus('Đang lưu trạng thái vé...');
            try {
                const { response, data } = await postTicket(printUrl, pendingPrintTicketCode);

                if (data.ticket) {
                    renderTicket(data.ticket);
                }

                if (response.ok && data.success) {
                    setStatus('Xác nhận in vé thành công và đã hoàn tất soát vé!', 'success');
                } else {
                    setStatus(data.message || 'Lỗi khi cập nhật trạng thái in vé.', 'error');
                }
            } catch (err) {
                setStatus('Lỗi kết nối máy chủ khi cập nhật trạng thái vé.', 'error');
            } finally {
                pendingPrintTicketCode = '';
                pendingPrintTicketData = null;
            }
        });

        // LẮNG NGHE SỰ KIỆN CLICK BẤM "IN VÉ NGAY"
        resultBox.addEventListener('click', function(event) {
            const printBtn = event.target.closest('#btnPrintTicket');

            if (printBtn) {
                event.preventDefault();
                const ticketCode = printBtn.getAttribute('data-ticket-code');
                const ticketDataStr = printBtn.getAttribute('data-ticket-data');
                let ticketData = null;

                try {
                    ticketData = JSON.parse(ticketDataStr);
                } catch (e) {}

                if (!ticketCode) {
                    setStatus('Không xác định được mã vé để in.', 'error');
                    return;
                }

                // 1. Bật cửa sổ in nhiệt TRƯỚC
                if (ticketData) {
                    printTicketThermal(ticketData);
                }

                // 2. Mở modal xác nhận SAU KHI đã xuất màn hình in
                openPrintConfirmModal(ticketCode, ticketData);
                return;
            }
        });

        // CAMERA SCANNER FUNCTIONS
        function handleDecodedQr(value) {
            value = String(value || '').trim();
            const now = Date.now();

            if (!value || requestBusy || (value === lastValue && now - lastScanAt <= 3500)) return;

            lastValue = value;
            lastScanAt = now;
            inspectTicket(value);
        }

        async function scanFrame() {
            if (!scanning || !detector || !video.srcObject) return;

            if (!requestBusy && video.readyState >= 2) {
                try {
                    const barcodes = await detector.detect(video);
                    if (barcodes.length > 0) handleDecodedQr(barcodes[0].rawValue);
                } catch (error) {}
            }

            requestAnimationFrame(scanFrame);
        }

        async function startScanner() {
            if (scanning) return;
            if ('BarcodeDetector' in window) {
                await startNativeScanner();
                if (activeScanner === 'native') return;
            }
            await startFallbackScanner();
        }

        async function startNativeScanner() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                setStatus('Thiết bị này không cấp được camera cho trình duyệt.', 'error');
                return;
            }

            try {
                detector = detector || new BarcodeDetector({ formats: ['qr_code'] });
                stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: { ideal: 'environment' },
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    },
                    audio: false
                });

                video.srcObject = stream;
                await video.play();

                scanning = true;
                activeScanner = 'native';
                cameraBox.classList.add('is-live');
                cameraBox.classList.remove('is-fallback');
                setScannerButtons(true);
                setStatus('Camera đã bật. Đưa QR vào khung để kiểm tra vé.');
                requestAnimationFrame(scanFrame);
            } catch (error) {
                setStatus('Không mở được camera. Hãy cấp quyền camera và thử lại.', 'error');
            }
        }

        async function startFallbackScanner() {
            try {
                await ensureHtml5Qrcode();
            } catch (error) {
                setStatus('Không tải được thư viện quét QR dự phòng. Hãy nhập mã vé thủ công.', 'error');
                return;
            }

            try {
                html5QrCode = html5QrCode || new Html5Qrcode('html5QrReader');
                scanning = true;
                activeScanner = 'fallback';
                cameraBox.classList.add('is-live', 'is-fallback');
                setScannerButtons(true);
                setStatus('Đang mở camera bằng trình quét dự phòng...');

                await html5QrCode.start(
                    { facingMode: 'environment' },
                    {
                        fps: 10,
                        qrbox: (w, h) => {
                            const size = Math.floor(Math.min(w, h) * 0.68);
                            return { width: size, height: size };
                        }
                    },
                    (decodedText) => handleDecodedQr(decodedText),
                    () => {}
                );

                setStatus('Camera đã bật. Đưa QR vào khung để kiểm tra vé.');
            } catch (error) {
                scanning = false;
                activeScanner = null;
                cameraBox.classList.remove('is-live', 'is-fallback');
                setScannerButtons(false);
                setStatus('Không mở được camera. Hãy cấp quyền camera và thử lại.', 'error');
            }
        }

        async function stopScanner() {
            scanning = false;
            if (activeScanner === 'fallback' && html5QrCode) {
                try {
                    await html5QrCode.stop();
                    html5QrCode.clear();
                } catch (error) {}
                html5QrCode = null;
            }

            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }

            stream = null;
            activeScanner = null;
            video.srcObject = null;
            html5QrReader.innerHTML = '';
            cameraBox.classList.remove('is-live', 'is-fallback');
            setScannerButtons(false);
            setStatus('Camera đã tắt. Bạn có thể bật lại hoặc nhập mã vé thủ công.');
        }

        startBtn.addEventListener('click', startScanner);
        stopBtn.addEventListener('click', stopScanner);

        form.addEventListener('submit', function(event) {
            if (!window.fetch) return;
            event.preventDefault();
            inspectTicket(input.value);
        });
    });
</script>
@endpush