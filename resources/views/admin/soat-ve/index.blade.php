@extends('layouts.admin')

@section('title', 'Soát vé QR - CineHome')
@section('page-title', 'Soát vé QR')
@section('page-subtitle', 'Quét mã QR hoặc nhập mã vé để in vé cứng và xác nhận khách vào phòng chiếu')

@section('content')
@php
    $ticket = session('ticket');
    
    // 🌟 KHẮC PHỤC TRIỆT ĐỂ: Thuật toán tự động nhận diện và bóc tách Đồ ăn (Foods) từ mọi nguồn dữ liệu
    $foods = [];
   if ($ticket) {
        $ticketId = is_array($ticket) ? ($ticket['id'] ?? null) : ($ticket->id ?? null);
        if ($ticketId) {
            $rawFoods = \Illuminate\Support\Facades\Cache::get("ve_foods:{$ticketId}", []);
            if (is_array($rawFoods)) {
                $foods = $rawFoods;
            }
        }
    }

    $statusLabel = [
        'da_thanh_toan' => 'Đã thanh toán',
        'da_su_dung' => 'Đã sử dụng',
        'da_huy' => 'Đã hủy',
    ];

    $typeLabel = [
        'truc_tuyen' => 'Trực tuyến',
        'tai_quay' => 'Tại quầy',
    ];
@endphp

<div class="admin-scan-page">
    {{-- Thông báo trạng thái --}}
    @if (session('success'))
        <div class="scan-alert scan-alert-success">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="scan-alert scan-alert-error">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="scan-layout">
        {{-- PANEL TRÁI: Camera QR & Nhập tay --}}
        <section class="scan-panel">
            <div class="scan-panel-head">
                <div class="head-info">
                    <h2>Camera quét QR</h2>
                    <p>Đưa mã QR trên vé vào khung hình để kiểm tra thông tin, thực phẩm đi kèm và in vé cứng.</p>
                </div>
                <span class="scan-head-icon"><i class="fa-solid fa-qrcode"></i></span>
            </div>

            <div class="camera-box">
                <div id="html5QrReader"></div>
                <div class="scan-frame" aria-hidden="true"></div>
                <div class="camera-empty">
                    <i class="fa-solid fa-camera"></i>
                    <span>Camera chưa được bật</span>
                </div>
            </div>

            <div class="scan-toolbar">
                <button type="button" id="startScannerBtn" class="scan-btn scan-btn-primary">
                    <i class="fa-solid fa-camera"></i> Bật camera
                </button>
                <button type="button" id="stopScannerBtn" class="scan-btn scan-btn-secondary" disabled>
                    <i class="fa-solid fa-stop"></i> Tắt camera
                </button>
            </div>

            <div id="scannerStatus" class="scanner-status">
                <i class="fa-solid fa-circle-info"></i>
                <span>Vui lòng đưa mã QR vào camera hoặc nhập tay mã vé dưới đây.</span>
            </div>

            <form id="ticketCheckForm" method="POST" action="{{ route('admin.soat-ve.check') }}" class="manual-form">
                @csrf
                <label for="ticketCodeInput">Mã vé thủ công</label>
                <div class="manual-input-wrap">
                    <i class="fa-solid fa-barcode"></i>
                    <input
                        id="ticketCodeInput"
                        type="text"
                        name="ma_ve"
                        value="{{ old('ma_ve') }}"
                        placeholder="VD: VE123456789"
                        autocomplete="off"
                    >
                </div>
                @error('ma_ve')
                    <p class="scan-field-error">{{ $message }}</p>
                @enderror
                <button type="submit" class="scan-btn scan-btn-submit">
                    <i class="fa-solid fa-magnifying-glass"></i> Kiểm tra vé
                </button>
            </form>
        </section>

        {{-- PANEL PHẢI: Chi tiết vé & Đồ ăn đi kèm --}}
        <section class="scan-panel">
            <div class="scan-panel-head">
                <div class="head-info">
                    <h2>Kết quả kiểm tra</h2>
                    <p>Vé hợp lệ sẽ cho phép in vé cứng kèm đồ ăn trước khi cập nhật trạng thái đã sử dụng.</p>
                </div>
                <span class="scan-head-icon"><i class="fa-solid fa-ticket"></i></span>
            </div>

            <div id="ticketResult">
                @if ($ticket)
                    <div class="ticket-result">
                        <div class="ticket-result-top">
                            <div>
                                <span>Mã vé</span>
                                <strong>{{ is_array($ticket) ? ($ticket['ma_ve'] ?? '') : $ticket->ma_ve }}</strong>
                            </div>
                            <em class="scan-status-pill status-{{ is_array($ticket) ? ($ticket['trang_thai'] ?? '') : $ticket->trang_thai }}">
                                {{ $statusLabel[is_array($ticket) ? ($ticket['trang_thai'] ?? '') : $ticket->trang_thai] ?? 'Không rõ' }}
                            </em>
                        </div>

                        <div class="ticket-result-grid">
                            <div><span>Phim</span><strong>{{ is_array($ticket) ? ($ticket['ten_phim'] ?? '') : $ticket->ten_phim }}</strong></div>
                            <div><span>Phòng</span><strong>{{ is_array($ticket) ? ($ticket['ten_phong'] ?? 'Chưa có') : ($ticket->ten_phong ?? 'Chưa có') }}</strong></div>
                            <div><span>Ghế</span><strong>{{ is_array($ticket) ? ($ticket['ma_ghe'] ?? 'Chưa có') : ($ticket->ma_ghe ?? 'Chưa có') }}</strong></div>
                            <div><span>Suất chiếu</span><strong>
                                @if(is_array($ticket))
                                    {{ $ticket['thoi_gian_chieu'] ?? 'Chưa có' }}
                                @else
                                    {{ $ticket->thoi_gian_chieu ? $ticket->thoi_gian_chieu->format('d/m/Y H:i') : 'Chưa có' }}
                                @endif
                            </strong></div>
                            <div><span>Tổng tiền</span><strong>
                                @if(is_array($ticket))
                                    {{ number_format((float) ($ticket['tong_tien'] ?? 0), 0, ',', '.') }}đ
                                @else
                                    {{ number_format((float) $ticket->tong_tien, 0, ',', '.') }}đ
                                @endif
                            </strong></div>
                            <div><span>Loại vé</span><strong>{{ $typeLabel[is_array($ticket) ? ($ticket['loai_ve'] ?? '') : $ticket->loai_ve] ?? 'Không rõ' }}</strong></div>
                        </div>

                        {{-- HIỂN THỊ ĐỒ ĂN & NƯỚC UỐNG ĐI KÈM --}}
                        <div class="food-result-section">
                            <h3 class="food-section-title">
                                <i class="fa-solid fa-utensils"></i> Đồ ăn & Combo kèm theo
                            </h3>
                            @if(count($foods) > 0)
                                <table class="food-table">
                                    <tbody>
                                        @foreach($foods as $food)
                                            @php
                                                // Đề phòng key mảng trả về dạng Object hay Array
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
                                    <i class="fa-solid fa-circle-info"></i> Không có đồ ăn/bắp nước đi kèm với vé này.
                                </div>
                            @endif
                        </div>

                        @if ((is_array($ticket) ? ($ticket['trang_thai'] ?? '') : $ticket->trang_thai) === 'da_thanh_toan')
                            <div class="confirm-form">
                                <button type="button" class="scan-btn scan-btn-confirm" id="btnPrintConfirm" data-ticket-data="{{ json_encode([
                                    'ma_ve' => is_array($ticket) ? ($ticket['ma_ve'] ?? '') : $ticket->ma_ve,
                                    'ten_phim' => is_array($ticket) ? ($ticket['ten_phim'] ?? '') : $ticket->ten_phim,
                                    'ten_phong' => is_array($ticket) ? ($ticket['ten_phong'] ?? 'Chưa có') : ($ticket->ten_phong ?? 'Chưa có'),
                                    'ma_ghe' => is_array($ticket) ? ($ticket['ma_ghe'] ?? 'Chưa có') : ($ticket->ma_ghe ?? 'Chưa có'),
                                    'thoi_gian_chieu' => is_array($ticket) ? ($ticket['thoi_gian_chieu'] ?? 'Chưa có') : ($ticket->thoi_gian_chieu ? $ticket->thoi_gian_chieu->format('d/m/Y H:i') : 'Chưa có'),
                                    'tong_tien' => number_format((float) (is_array($ticket) ? ($ticket['tong_tien'] ?? 0) : $ticket->tong_tien), 0, ',', '.') . 'đ',
                                    'loai_ve_label' => $typeLabel[is_array($ticket) ? ($ticket['loai_ve'] ?? '') : $ticket->loai_ve] ?? 'Tại quầy',
                                    'foods' => $foods
                                ]) }}">
                                    <i class="fa-solid fa-print"></i> In vé cứng & Xác nhận sử dụng
                                </button>
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
        </section>
    </div>
</div>

{{-- BẢN IN NHIỆT K80 --}}
<div id="printTicketSection"></div>

{{-- MODAL XÁC NHẬN SOÁT VÉ TRÁNH BỊ TRÙNG LẶP --}}
<div id="confirmCheckInModal" class="custom-modal" aria-hidden="true">
    <div class="custom-modal-content">
        <div class="modal-header">
            <h3><i class="fa-solid fa-print"></i> Xác nhận soát vé</h3>
        </div>
        <div class="modal-body">
            <p>Yêu cầu in vé đã được gửi tới trình duyệt.</p>
            <p class="modal-highlight">Bạn đã hoàn thành in vé cứng và sẵn sàng cho khách vào rạp chưa?</p>
        </div>
        <div class="modal-footer">
            <button type="button" id="modalBtnCancel" class="modal-btn modal-btn-secondary">
                <i class="fa-solid fa-xmark"></i> Hủy lệnh soát
            </button>
            <button type="button" id="modalBtnConfirm" class="modal-btn modal-btn-primary">
                <i class="fa-solid fa-check"></i> Đã in & Cho khách vào
            </button>
        </div>
    </div>
</div>

<style>
    .admin-scan-page {
        --gold: #d99a32;
        --gold-dark: #8a4a21;
        --panel: rgba(16, 16, 16, .96);
        --border: rgba(255, 255, 255, .1);
        color: #fff;
    }

    .scan-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.05fr) minmax(360px, 0.95fr);
        gap: 22px;
        align-items: stretch;
    }

    .scan-panel {
        border: 1px solid var(--border);
        border-radius: 24px;
        background: var(--panel);
        box-shadow: 0 24px 70px rgba(0, 0, 0, .28);
        padding: 24px;
        overflow: hidden;
    }

    .scan-panel-head {
        display: flex;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 20px;
    }

    .scan-panel-head .head-info h2 {
        margin: 0;
        font-size: 22px;
        font-weight: 900;
    }

    .scan-panel-head .head-info p {
        margin: 6px 0 0;
        max-width: 560px;
        color: #9ca3af;
        line-height: 1.5;
        font-size: 13px;
    }

    .scan-head-icon {
        width: 52px;
        height: 52px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        border-radius: 18px;
        color: var(--gold);
        background: rgba(217, 154, 50, .15);
        font-size: 22px;
    }

    .camera-box {
        position: relative;
        aspect-ratio: 16 / 10;
        min-height: 320px;
        overflow: hidden;
        border-radius: 20px;
        border: 1px solid rgba(217, 154, 50, .26);
        background: #050505;
    }

    #html5QrReader {
        position: absolute;
        inset: 0;
        display: none;
        overflow: hidden;
        border: 0 !important;
        background: #050505;
        z-index: 2;
    }

    #html5QrReader video {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
    }

    #html5QrReader__dashboard_section,
    #html5QrReader__scan_region img,
    #html5QrReader__header_message {
        display: none !important;
    }

    .scan-frame {
        position: absolute;
        left: 50%;
        top: 50%;
        width: min(58%, 260px);
        aspect-ratio: 1;
        transform: translate(-50%, -50%);
        border: 3px solid var(--gold);
        border-radius: 18px;
        box-shadow: 0 0 0 999px rgba(0, 0, 0, .5);
        pointer-events: none;
        z-index: 3;
    }

    .camera-empty {
        position: absolute;
        inset: 0;
        z-index: 1;
        display: grid;
        place-items: center;
        align-content: center;
        gap: 10px;
        color: #d1d5db;
        font-weight: 800;
    }

    .camera-empty i {
        color: var(--gold);
        font-size: 36px;
    }

    .camera-box.is-live .camera-empty {
        display: none;
    }

    .camera-box.is-live #html5QrReader {
        display: block !important;
    }

    .scan-toolbar {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin-top: 16px;
    }

    .scan-btn {
        min-height: 48px;
        border: 0;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        padding: 0 16px;
        color: #fff;
        font-weight: 900;
        text-decoration: none;
        transition: transform .25s ease, opacity .25s ease;
        cursor: pointer;
    }

    .scan-btn:hover:not(:disabled) {
        transform: translateY(-2px);
    }

    .scan-btn:disabled {
        cursor: not-allowed;
        opacity: .5;
    }

    .scan-btn-primary,
    .scan-btn-submit {
        background: linear-gradient(135deg, var(--gold-dark), var(--gold));
        box-shadow: 0 14px 30px rgba(217, 154, 50, .16);
    }

    .scan-btn-confirm {
        width: 100%;
        margin-top: 16px;
        background: linear-gradient(135deg, #0284c7, #0ea5e9);
        box-shadow: 0 14px 30px rgba(14, 165, 233, .2);
    }

    .scan-btn-secondary {
        border: 1px solid var(--border);
        background: rgba(255, 255, 255, .08);
    }

    .scanner-status,
    .scan-alert {
        display: flex;
        align-items: center;
        gap: 10px;
        border-radius: 16px;
        line-height: 1.45;
    }

    .scanner-status {
        margin: 16px 0;
        padding: 13px 14px;
        color: #d1d5db;
        background: rgba(255, 255, 255, .055);
    }

    .scanner-status i {
        color: var(--gold);
    }

    .scanner-status.success {
        color: #bbf7d0;
        background: rgba(34, 197, 94, .12);
    }

    .scanner-status.error {
        color: #fecaca;
        background: rgba(239, 68, 68, .12);
    }

    .manual-form {
        display: grid;
        gap: 12px;
    }

    .manual-form label {
        color: #f3f4f6;
        font-weight: 900;
    }

    .manual-input-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
        height: 52px;
        border: 1px solid var(--border);
        border-radius: 16px;
        background: rgba(255, 255, 255, .055);
        padding: 0 14px;
    }

    .manual-input-wrap:focus-within {
        border-color: rgba(217, 154, 50, .75);
        box-shadow: 0 0 0 4px rgba(217, 154, 50, .1);
    }

    .manual-input-wrap i {
        color: var(--gold);
    }

    .manual-input-wrap input {
        width: 100%;
        height: 100%;
        border: 0;
        outline: none;
        color: #fff;
        background: transparent;
        font-weight: 800;
    }

    .scan-alert {
        margin-bottom: 18px;
        padding: 14px 16px;
        font-weight: 800;
    }

    .scan-alert-success {
        color: #bbf7d0;
        border: 1px solid rgba(34, 197, 94, .35);
        background: rgba(34, 197, 94, .12);
    }

    .scan-alert-error,
    .scan-field-error {
        color: #fecaca;
    }

    .scan-alert-error {
        border: 1px solid rgba(239, 68, 68, .35);
        background: rgba(239, 68, 68, .12);
    }

    .scan-field-error {
        margin: -4px 0 0;
        font-size: 14px;
    }

    .ticket-result {
        display: grid;
        gap: 16px;
    }

    .ticket-result-top {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        padding: 18px;
        border-radius: 18px;
        background: rgba(255, 255, 255, .055);
    }

    .ticket-result-top span,
    .ticket-result-grid span {
        display: block;
        color: #9ca3af;
        font-size: 13px;
        font-weight: 800;
    }

    .ticket-result-top strong {
        display: block;
        margin-top: 5px;
        color: var(--gold);
        font-size: 20px;
        word-break: break-word;
    }

    .scan-status-pill {
        height: fit-content;
        border-radius: 999px;
        padding: 8px 12px;
        font-size: 12px;
        font-style: normal;
        font-weight: 900;
        white-space: nowrap;
    }

    .status-da_su_dung {
        color: #bbf7d0;
        border: 1px solid rgba(34, 197, 94, .35);
        background: rgba(34, 197, 94, .14);
    }

    .status-da_thanh_toan {
        color: #fde68a;
        border: 1px solid rgba(217, 154, 50, .35);
        background: rgba(217, 154, 50, .14);
    }

    .status-da_huy {
        color: #fecaca;
        border: 1px solid rgba(239, 68, 68, .35);
        background: rgba(239, 68, 68, .14);
    }

    .ticket-result-grid {
        display: grid;
        gap: 10px;
    }

    .ticket-result-grid div {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        padding: 14px 16px;
        border-radius: 16px;
        background: rgba(255, 255, 255, .04);
    }

    .ticket-result-grid strong {
        color: #fff;
        text-align: right;
    }

    .food-result-section {
        margin-top: 5px;
        padding: 18px;
        background: rgba(255, 255, 255, 0.04);
        border-radius: 16px;
        border: 1px dashed var(--border);
    }

    .food-section-title {
        margin: 0 0 12px;
        font-size: 15px;
        color: var(--gold);
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .food-table {
        width: 100%;
        border-collapse: collapse;
    }

    .food-table tr {
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .food-table tr:last-child {
        border: 0;
    }

    .food-table td {
        padding: 8px 0;
    }

    .food-name {
        font-weight: 600;
        color: #e5e7eb;
    }

    .food-qty {
        text-align: right;
        font-weight: 800;
        color: #fff;
    }

    .no-food-alert {
        color: #9ca3af;
        font-size: 13.5px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .empty-result {
        min-height: 420px;
        display: grid;
        place-items: center;
        align-content: center;
        gap: 10px;
        text-align: center;
        color: #9ca3af;
    }

    .empty-result i {
        display: grid;
        place-items: center;
        width: 86px;
        height: 86px;
        border-radius: 24px;
        color: var(--gold);
        background: rgba(217, 154, 50, .12);
        font-size: 36px;
    }

    .empty-result h3 {
        margin: 8px 0 0;
        color: #fff;
        font-size: 18px;
        font-weight: 900;
    }

    .empty-result p,
    .confirm-form {
        margin: 0;
    }

    .custom-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.82);
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(5px);
    }

    .custom-modal.is-active {
        display: flex;
    }

    .custom-modal-content {
        background: #101010;
        border: 1px solid rgba(217, 154, 50, 0.35);
        border-radius: 24px;
        width: 90%;
        max-width: 440px;
        padding: 26px;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.85);
        color: #fff;
        animation: modalFadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes modalFadeIn {
        from { transform: translateY(15px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .modal-header h3 {
        margin: 0 0 12px;
        color: var(--gold);
        font-size: 20px;
        font-weight: 900;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .modal-body p {
        margin: 8px 0;
        color: #9ca3af;
        font-size: 14px;
        line-height: 1.6;
    }

    .modal-body .modal-highlight {
        color: #fff;
        font-weight: 800;
        font-size: 15px;
    }

    .modal-footer {
        display: flex;
        gap: 12px;
        margin-top: 24px;
    }

    .modal-btn {
        flex: 1;
        height: 48px;
        border: 0;
        border-radius: 14px;
        font-weight: 800;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 14px;
        transition: all 0.2s ease;
    }

    .modal-btn-primary {
        background: linear-gradient(135deg, #147a3d, #22c55e);
        color: #fff;
    }

    .modal-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(34, 197, 94, 0.25);
    }

    .modal-btn-secondary {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #d1d5db;
    }

    .modal-btn-secondary:hover {
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
    }

    #printTicketSection {
        display: none;
    }

    @media print {
        body * {
            visibility: hidden;
        }
        #printTicketSection, #printTicketSection * {
            visibility: visible;
        }
        #printTicketSection {
            display: block !important;
            position: absolute;
            left: 0;
            top: 0;
            width: 74mm;
            color: #000;
            background: #fff;
            padding: 4mm;
            font-family: 'Courier New', Courier, monospace, Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .print-header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .print-movie-title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin: 10px 0;
            text-transform: uppercase;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }
        .print-info-table {
            width: 100%;
            margin-bottom: 10px;
            border-collapse: collapse;
        }
        .print-info-table td {
            padding: 4px 0;
        }
        .print-info-table td.label {
            width: 40%;
        }
        .print-info-table td.value {
            width: 60%;
            font-weight: bold;
            text-align: right;
        }
        .print-seat {
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            border: 1px solid #000;
            padding: 8px;
            margin: 10px 0;
        }
        .print-foods {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 8px 0;
            margin: 10px 0;
        }
        .print-foods-title {
            font-weight: bold;
            text-align: center;
            margin-bottom: 6px;
            font-size: 11px;
            letter-spacing: 0.5px;
        }
        .print-footer {
            text-align: center;
            font-size: 10px;
            margin-top: 14px;
            border-top: 1px dashed #000;
            padding-top: 8px;
        }
        @page {
            size: auto;
            margin: 0mm;
        }
    }
</style>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cameraBox = document.querySelector('.camera-box');
        const startBtn = document.getElementById('startScannerBtn');
        const stopBtn = document.getElementById('stopScannerBtn');
        const statusBox = document.getElementById('scannerStatus');
        const statusText = statusBox.querySelector('span');
        const form = document.getElementById('ticketCheckForm');
        const input = document.getElementById('ticketCodeInput');
        const resultBox = document.getElementById('ticketResult');
        const checkUrl = @json(route('admin.soat-ve.check'));
        const confirmUrl = @json(route('admin.soat-ve.confirm'));
        const csrfToken = @json(csrf_token());

        const modal = document.getElementById('confirmCheckInModal');
        const modalBtnConfirm = document.getElementById('modalBtnConfirm');
        const modalBtnCancel = document.getElementById('modalBtnCancel');
        
        let html5QrCode = null;
        let scanning = false;
        let requestBusy = false;
        let lastValue = '';
        let lastScanAt = 0;
        let pendingTicketCode = '';

        function setStatus(message, type = '') {
            statusText.textContent = message;
            statusBox.classList.remove('success', 'error');
            if (type) {
                statusBox.classList.add(type);
            }
        }

        function setScannerButtons(isActive) {
            startBtn.disabled = isActive;
            stopBtn.disabled = !isActive;
        }

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, function (char) {
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

            // Đồng bộ cả trên JS khi dữ liệu trả về qua Ajax
            let rawFoods = ticket.foods_list || ticket.foods || ticket.food || ticket.do_an || ticket.danh_sach_do_an || [];
            
            // Giải mã nếu ajax trả về chuỗi JSON
            if (typeof rawFoods === 'string') {
                try { rawFoods = JSON.parse(rawFoods); } catch(e) { rawFoods = []; }
            }

            let foodHtml = '';
            if (Array.isArray(rawFoods) && rawFoods.length > 0) {
                foodHtml = `
                    <div class="food-result-section">
                        <h3 class="food-section-title">
                            <i class="fa-solid fa-utensils"></i> Đồ ăn & Combo kèm theo
                        </h3>
                        <table class="food-table">
                            <tbody>
                                ${rawFoods.map(f => {
                                    const tenMon = f.ten_mon || f.name || 'Đồ ăn';
                                    const soLuong = f.so_luong || f.quantity || f.qty || 1;
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
                `;
            } else {
                foodHtml = `
                    <div class="food-result-section">
                        <h3 class="food-section-title">
                            <i class="fa-solid fa-utensils"></i> Đồ ăn & Combo kèm theo
                        </h3>
                        <div class="no-food-alert">
                            <i class="fa-solid fa-circle-info"></i> Không có đồ ăn/bắp nước đi kèm với vé này.
                        </div>
                    </div>
                `;
            }

            const confirmButton = ticket.can_check_in ? `
                <button type="button" class="scan-btn scan-btn-confirm" id="btnPrintConfirm" data-ticket-data="${escapeHtml(JSON.stringify(ticket))}">
                    <i class="fa-solid fa-print"></i>
                    In vé cứng & Xác nhận sử dụng
                </button>
            ` : '';

            resultBox.innerHTML = `
                <div class="ticket-result">
                    <div class="ticket-result-top">
                        <div>
                            <span>Mã vé</span>
                            <strong>${escapeHtml(ticket.ma_ve)}</strong>
                        </div>
                        <em class="scan-status-pill status-${escapeHtml(ticket.trang_thai)}">
                            ${escapeHtml(ticket.trang_thai_label || 'Không rõ')}
                        </em>
                    </div>
                    <div class="ticket-result-grid">
                        <div><span>Phim</span><strong>${escapeHtml(ticket.ten_phim || 'Chưa có')}</strong></div>
                        <div><span>Phòng</span><strong>${escapeHtml(ticket.ten_phong || 'Chưa có')}</strong></div>
                        <div><span>Ghế</span><strong>${escapeHtml(ticket.ma_ghe || 'Chưa có')}</strong></div>
                        <div><span>Suất chiếu</span><strong>${escapeHtml(ticket.thoi_gian_chieu || 'Chưa có')}</strong></div>
                        <div><span>Tổng tiền</span><strong>${escapeHtml(ticket.tong_tien || '0đ')}</strong></div>
                        <div><span>Loại vé</span><strong>${escapeHtml(ticket.loai_ve_label || 'Không rõ')}</strong></div>
                    </div>
                    ${foodHtml}
                    ${confirmButton}
                </div>
            `;
        }

        async function postTicket(url, value) {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ ma_ve: value }),
            });

            let data = {};
            try {
                data = await response.json();
            } catch (error) {
                data = {
                    success: false,
                    message: 'Máy chủ không phản hồi dữ liệu JSON hợp lệ.',
                };
            }
            return { response, data };
        }

        async function inspectTicket(rawValue) {
            const value = String(rawValue || '').trim();

            if (!value) {
                setStatus('Vui lòng nhập mã vé hoặc quét QR.', 'error');
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
                    setStatus(data.message || 'Vé hợp lệ. Sẵn sàng soát vé & in.', 'success');
                } else {
                    setStatus(data.message || Object.values(data.errors || {})[0]?.[0] || 'Vé không hợp lệ.', 'error');
                }
            } catch (error) {
                setStatus('Lỗi kết nối máy chủ. Hãy thử lại.', 'error');
            } finally {
                setTimeout(function () {
                    requestBusy = false;
                }, 900);
            }
        }

        async function confirmTicket(rawValue) {
            const value = String(rawValue || '').trim();
            if (!value) return;

            requestBusy = true;
            setStatus('Đang cập nhật trạng thái vé...');

            try {
                const { response, data } = await postTicket(confirmUrl, value);
                if (data.ticket) {
                    input.value = data.ticket.ma_ve;
                    renderTicket(data.ticket);
                }

                if (response.ok && data.success) {
                    setStatus(data.message || 'Đã in và soát vé thành công!', 'success');
                } else {
                    setStatus(data.message || 'Có lỗi khi cập nhật trạng thái vé.', 'error');
                }
            } catch (error) {
                setStatus('Đã xảy ra lỗi hệ thống khi cập nhật.', 'error');
            } finally {
                requestBusy = false;
            }
        }

        function handlePrintAndConfirm(ticket) {
            const printSection = document.getElementById('printTicketSection');
            
            let rawFoods = ticket.foods_list || ticket.foods || ticket.food || ticket.do_an || ticket.danh_sach_do_an || [];
            if (typeof rawFoods === 'string') {
                try { rawFoods = JSON.parse(rawFoods); } catch(e) { rawFoods = []; }
            }

            let foodHtml = '';
            if (Array.isArray(rawFoods) && rawFoods.length > 0) {
                foodHtml = `
                    <div class="print-foods">
                        <div class="print-foods-title">ĐỒ ĂN / COMBO KÈM THEO</div>
                        <table style="width: 100%;">
                            ${rawFoods.map(f => {
                                const tenMon = f.ten_mon || f.name || 'Đồ ăn';
                                const soLuong = f.so_luong || f.quantity || f.qty || 1;
                                return `
                                    <tr>
                                        <td>${escapeHtml(tenMon)}</td>
                                        <td style="text-align: right; font-weight: bold;">x${escapeHtml(soLuong)}</td>
                                    </tr>
                                `;
                            }).join('')}
                        </table>
                    </div>
                `;
            }

            printSection.innerHTML = `
                <div class="print-header">
                    <div style="font-size: 16px; font-weight: bold; letter-spacing: 1px;">CINEHOME CINEMA</div>
                    <div style="font-size: 10px;">VÉ XEM PHIM KIÊM PHIẾU ĐỒ ĂN</div>
                    <div style="font-size: 9px; margin-top: 2px;">Mã vé: ${escapeHtml(ticket.ma_ve)}</div>
                </div>
                
                <div class="print-movie-title">${escapeHtml(ticket.ten_phim)}</div>
                
                <table class="print-info-table">
                    <tr><td class="label">Phòng chiếu:</td><td class="value">${escapeHtml(ticket.ten_phong)}</td></tr>
                    <tr><td class="label">Suất chiếu:</td><td class="value">${escapeHtml(ticket.thoi_gian_chieu)}</td></tr>
                    <tr><td class="label">Kênh đặt:</td><td class="value">${escapeHtml(ticket.loai_ve_label)}</td></tr>
                </table>

                <div class="print-seat">
                    GHẾ: ${escapeHtml(ticket.ma_ghe)}
                </div>

                ${foodHtml}

                <div style="text-align: right; font-weight: bold; margin-top: 8px; border-top: 1px dashed #000; padding-top: 4px;">
                    Tổng tiền: ${escapeHtml(ticket.tong_tien)}
                </div>

                <div class="print-footer">
                    <p>Cảm ơn quý khách đã đồng hành cùng CineHome!</p>
                    <div style="font-size: 8px; margin-top: 6px;">Thời gian in: ${new Date().toLocaleString('vi-VN')}</div>
                </div>
            `;

            window.print();

            pendingTicketCode = ticket.ma_ve;
            setTimeout(function() {
                modal.classList.add('is-active');
            }, 500);
        }

        modalBtnConfirm.addEventListener('click', function() {
            modal.classList.remove('is-active');
            if (pendingTicketCode) {
                confirmTicket(pendingTicketCode);
            }
        });

        modalBtnCancel.addEventListener('click', function() {
            modal.classList.remove('is-active');
            setStatus(`Đã hủy lệnh soát. Vé "${pendingTicketCode}" vẫn giữ trạng thái Đã thanh toán.`, "success");
            pendingTicketCode = '';
        });

        async function startScanner() {
            if (!window.Html5Qrcode) {
                setStatus('Không tải được thư viện quét QR. Hãy nhập tay mã vé.', 'error');
                return;
            }

            try {
                html5QrCode = html5QrCode || new Html5Qrcode('html5QrReader');
                scanning = true;
                cameraBox.classList.add('is-live');
                setScannerButtons(true);
                setStatus('Đang bật camera...');

                await html5QrCode.start(
                    { facingMode: 'environment' },
                    {
                        fps: 15,
                        qrbox: function (viewfinderWidth, viewfinderHeight) {
                            const size = Math.floor(Math.min(viewfinderWidth, viewfinderHeight) * 0.7);
                            return { width: size, height: size };
                        },
                    },
                    function (decodedText) {
                        handleDecodedQr(decodedText);
                    },
                    function (err) {}
                );

                setStatus('Đang quét... Đưa mã QR vào khung camera.');
            } catch (error) {
                scanning = false;
                cameraBox.classList.remove('is-live');
                setScannerButtons(false);
                setStatus('Không thể mở camera. Vui lòng kiểm tra quyền và thiết bị.', 'error');
            }
        }

        async function stopScanner() {
            scanning = false;
            if (html5QrCode) {
                try {
                    await html5QrCode.stop();
                    html5QrCode.clear();
                } catch (error) {}
            }
            cameraBox.classList.remove('is-live');
            setScannerButtons(false);
            setStatus('Camera đã tắt.');
        }

        function handleDecodedQr(value) {
            value = String(value || '').trim();
            const now = Date.now();

            if (!value || requestBusy || (value === lastValue && now - lastScanAt <= 3500)) {
                return;
            }

            lastValue = value;
            lastScanAt = now;
            inspectTicket(value);
        }

        startBtn.addEventListener('click', startScanner);
        stopBtn.addEventListener('click', stopScanner);

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            inspectTicket(input.value);
        });

        resultBox.addEventListener('click', function (event) {
            const button = event.target.closest('#btnPrintConfirm');
            if (!button) return;

            event.preventDefault();
            try {
                const ticketData = JSON.parse(button.getAttribute('data-ticket-data'));
                handlePrintAndConfirm(ticketData);
            } catch (e) {
                alert('Không thể tải dữ liệu in vé.');
            }
        });
    });
</script>
@endpush