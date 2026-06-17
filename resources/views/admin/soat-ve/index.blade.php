@extends('layouts.admin')

@section('title', 'Soát vé QR - CineHome')
@section('page-title', 'Soát vé QR')
@section('page-subtitle', 'Quét mã QR hoặc nhập mã vé để xác nhận khách vào phòng chiếu')

@section('content')
@php
    $ticket = session('ticket');

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
        <section class="scan-panel">
            <div class="scan-panel-head">
                <div>
                    <h2>Camera quét QR</h2>
                    <p>Đưa mã QR trên vé vào khung hình, hệ thống sẽ tự kiểm tra và đánh dấu vé đã sử dụng.</p>
                </div>
                <span class="scan-head-icon"><i class="fa-solid fa-qrcode"></i></span>
            </div>

            <div class="camera-box">
                <video id="qrVideo" playsinline muted></video>
                <div id="html5QrReader"></div>
                <div class="scan-frame" aria-hidden="true"></div>
                <div class="camera-empty">
                    <i class="fa-solid fa-camera"></i>
                    <span>Camera chưa được bật</span>
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
                <span>Trình duyệt cần quyền camera. Nếu không quét được, hãy nhập mã vé ở bên dưới.</span>
            </div>

            <form id="ticketCheckForm" method="POST" action="{{ route('admin.soat-ve.check') }}" class="manual-form">
                @csrf
                <label for="ticketCodeInput">Mã vé / dữ liệu QR</label>
                <div class="manual-input-wrap">
                    <i class="fa-solid fa-barcode"></i>
                    <input
                        id="ticketCodeInput"
                        type="text"
                        name="ma_ve"
                        value="{{ old('ma_ve') }}"
                        placeholder="VD: VE260617ABC123"
                        autocomplete="off"
                    >
                </div>
                @error('ma_ve')
                    <p class="scan-field-error">{{ $message }}</p>
                @enderror
                <button type="submit" class="scan-btn scan-btn-submit">
                    <i class="fa-solid fa-clipboard-check"></i>
                    Kiểm tra vé
                </button>
            </form>
        </section>

        <section class="scan-panel">
            <div class="scan-panel-head">
                <div>
                    <h2>Kết quả soát vé</h2>
                    <p>Thông tin vé mới nhất sau khi quét sẽ hiển thị tại đây.</p>
                </div>
                <span class="scan-head-icon"><i class="fa-solid fa-ticket"></i></span>
            </div>

            <div id="ticketResult">
                @if ($ticket)
                    <div class="ticket-result">
                        <div class="ticket-result-top">
                            <div>
                                <span>Mã vé</span>
                                <strong>{{ $ticket->ma_ve }}</strong>
                            </div>
                            <em class="scan-status-pill status-{{ $ticket->trang_thai }}">
                                {{ $statusLabel[$ticket->trang_thai] ?? 'Không rõ' }}
                            </em>
                        </div>

                        <div class="ticket-result-grid">
                            <div><span>Phim</span><strong>{{ $ticket->ten_phim }}</strong></div>
                            <div><span>Rạp</span><strong>{{ $ticket->ten_rap ?? 'Chưa có' }}</strong></div>
                            <div><span>Phòng</span><strong>{{ $ticket->ten_phong ?? 'Chưa có' }}</strong></div>
                            <div><span>Ghế</span><strong>{{ $ticket->ma_ghe ?? 'Chưa có' }}</strong></div>
                            <div><span>Suất chiếu</span><strong>{{ $ticket->thoi_gian_chieu ? $ticket->thoi_gian_chieu->format('d/m/Y H:i') : 'Chưa có' }}</strong></div>
                            <div><span>Tổng tiền</span><strong>{{ number_format($ticket->tong_tien, 0, ',', '.') }}đ</strong></div>
                            <div><span>Loại vé</span><strong>{{ $typeLabel[$ticket->loai_ve] ?? 'Không rõ' }}</strong></div>
                        </div>
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
        grid-template-columns: minmax(0, 1.08fr) minmax(360px, .92fr);
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

    .scan-panel-head h2 {
        margin: 0;
        font-size: 22px;
        font-weight: 900;
    }

    .scan-panel-head p {
        margin: 6px 0 0;
        max-width: 560px;
        color: #9ca3af;
        line-height: 1.5;
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

    .camera-box video,
    #html5QrReader video {
        width: 100% !important;
        height: 100% !important;
        display: block;
        object-fit: cover;
    }

    #html5QrReader {
        position: absolute;
        inset: 0;
        display: none;
        overflow: hidden;
        border: 0 !important;
        background: #050505;
    }

    #html5QrReader__dashboard_section,
    #html5QrReader__scan_region img,
    #html5QrReader__header_message {
        display: none !important;
    }

    .camera-box.is-fallback #qrVideo {
        display: none;
    }

    .camera-box.is-fallback #html5QrReader {
        display: block;
    }

    .scan-frame {
        position: absolute;
        left: 50%;
        top: 50%;
        width: min(58%, 310px);
        aspect-ratio: 1;
        transform: translate(-50%, -50%);
        border: 2px solid rgba(217, 154, 50, .88);
        border-radius: 18px;
        box-shadow: 0 0 0 999px rgba(0, 0, 0, .34);
        pointer-events: none;
        z-index: 2;
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
        transition: transform .25s ease, opacity .25s ease;
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

    .empty-result p {
        margin: 0;
    }

    @media (max-width: 1180px) {
        .scan-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .scan-panel {
            padding: 18px;
        }

        .scan-toolbar {
            grid-template-columns: 1fr;
        }

        .ticket-result-top,
        .ticket-result-grid div {
            flex-direction: column;
        }

        .ticket-result-grid strong {
            text-align: left;
        }
    }
</style>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
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
        const csrfToken = @json(csrf_token());

        let stream = null;
        let detector = null;
        let html5QrCode = null;
        let scanning = false;
        let requestBusy = false;
        let activeScanner = null;
        let lastValue = '';
        let lastScanAt = 0;

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

            resultBox.innerHTML = `
                <div class="ticket-result">
                    <div class="ticket-result-top">
                        <div>
                            <span>Mã vé</span>
                            <strong>${escapeHtml(ticket.ma_ve)}</strong>
                        </div>
                        <em class="scan-status-pill status-${escapeHtml(ticket.trang_thai)}">
                            ${escapeHtml(ticket.trang_thai_label)}
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
                </div>
            `;
        }

        async function submitTicket(rawValue) {
            const value = String(rawValue || '').trim();

            if (!value) {
                setStatus('Vui lòng nhập mã vé hoặc quét QR.', 'error');
                return;
            }

            requestBusy = true;
            input.value = value;
            setStatus('Đang kiểm tra vé...');

            try {
                const response = await fetch(checkUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ ma_ve: value }),
                });

                const data = await response.json();

                if (data.ticket) {
                    input.value = data.ticket.ma_ve;
                    renderTicket(data.ticket);
                } else {
                    renderEmpty(data.message || 'Không tìm thấy vé.');
                }

                if (response.ok && data.success) {
                    setStatus(data.message || 'Soát vé thành công.', 'success');
                } else {
                    setStatus(data.message || Object.values(data.errors || {})[0]?.[0] || 'Vé không hợp lệ.', 'error');
                }
            } catch (error) {
                setStatus('Không thể kết nối máy chủ. Vui lòng thử lại.', 'error');
            } finally {
                setTimeout(function () {
                    requestBusy = false;
                }, 1800);
            }
        }

        function handleDecodedQr(value) {
            value = String(value || '').trim();
            const now = Date.now();

            if (!value || requestBusy || (value === lastValue && now - lastScanAt <= 3500)) {
                return;
            }

            lastValue = value;
            lastScanAt = now;
            submitTicket(value);
        }

        async function scanFrame() {
            if (!scanning || !detector || !video.srcObject) {
                return;
            }

            if (!requestBusy && video.readyState >= 2) {
                try {
                    const barcodes = await detector.detect(video);

                    if (barcodes.length > 0) {
                        handleDecodedQr(barcodes[0].rawValue);
                    }
                } catch (error) {
                    setStatus('Không đọc được khung hình hiện tại. Hãy giữ QR rõ hơn.', 'error');
                }
            }

            requestAnimationFrame(scanFrame);
        }

        async function startScanner() {
            if ('BarcodeDetector' in window) {
                await startNativeScanner();
                return;
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
                        height: { ideal: 720 },
                    },
                    audio: false,
                });

                video.srcObject = stream;
                await video.play();

                scanning = true;
                activeScanner = 'native';
                cameraBox.classList.add('is-live');
                cameraBox.classList.remove('is-fallback');
                setScannerButtons(true);
                setStatus('Camera đã bật. Đưa QR vào khung để quét.');
                requestAnimationFrame(scanFrame);
            } catch (error) {
                setStatus('Không mở được camera. Hãy cấp quyền camera và thử lại.', 'error');
            }
        }

        async function startFallbackScanner() {
            if (!window.Html5Qrcode) {
                setStatus('Không tải được thư viện quét QR dự phòng. Hãy kiểm tra kết nối mạng hoặc nhập mã vé thủ công.', 'error');
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
                        qrbox: function (viewfinderWidth, viewfinderHeight) {
                            const size = Math.floor(Math.min(viewfinderWidth, viewfinderHeight) * 0.68);
                            return { width: size, height: size };
                        },
                    },
                    function (decodedText) {
                        handleDecodedQr(decodedText);
                    },
                    function () {}
                );

                setStatus('Camera đã bật bằng trình quét dự phòng. Đưa QR vào khung để quét.');
            } catch (error) {
                scanning = false;
                activeScanner = null;
                cameraBox.classList.remove('is-live', 'is-fallback');
                setScannerButtons(false);
                setStatus('Không mở được camera. Hãy cấp quyền camera, tắt trình duyệt khác đang dùng camera và thử lại.', 'error');
            }
        }

        async function stopScanner() {
            scanning = false;

            if (activeScanner === 'fallback' && html5QrCode) {
                try {
                    await html5QrCode.stop();
                    html5QrCode.clear();
                } catch (error) {}
            }

            if (stream) {
                stream.getTracks().forEach(function (track) {
                    track.stop();
                });
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

        form.addEventListener('submit', function (event) {
            if (!window.fetch) {
                return;
            }

            event.preventDefault();
            submitTicket(input.value);
        });
    });
</script>
@endpush
