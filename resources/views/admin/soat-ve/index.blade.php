@extends('layouts.admin')

@section('title', 'Soát vé QR - CineHome')
@section('page-title', 'Soát vé QR')
@section('page-subtitle', 'Quét mã QR hoặc nhập mã vé để kiểm tra trước khi xác nhận khách vào phòng chiếu')

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

    <section class="admin-qr-hero">
        <div class="admin-qr-hero-copy">
            <span class="admin-eyebrow">
                <i class="fa-solid fa-qrcode"></i>
                Cổng kiểm vé
            </span>
            <h2>Quét QR nhanh, kiểm tra vé chính xác</h2>
            <p>
                Dùng camera để đọc mã trên vé điện tử hoặc nhập mã vé thủ công. Vé hợp lệ sẽ hiện nút xác nhận cho khách vào phòng chiếu.
            </p>
            <div class="admin-qr-steps">
                <span><b>01</b> Bật camera</span>
                <span><b>02</b> Đưa QR vào khung</span>
                <span><b>03</b> Xác nhận vào rạp</span>
            </div>
        </div>

        <div class="admin-qr-hero-card">
            <span>Trạng thái quầy</span>
            <strong>Sẵn sàng soát vé</strong>
            <p>Ưu tiên camera sau trên điện thoại hoặc webcam có ánh sáng tốt để quét nhanh hơn.</p>
        </div>
    </section>

    <div class="admin-qr-layout">
        <section class="admin-qr-panel admin-qr-camera-panel">
            <div class="admin-qr-panel-head">
                <div>
                    <span class="admin-eyebrow">Camera</span>
                    <h3>Khung quét QR</h3>
                    <p>Đặt mã QR nằm gọn trong khung sáng. Hệ thống chỉ kiểm tra vé trước, chưa tự đánh dấu đã sử dụng.</p>
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
                        <input
                            id="ticketCodeInput"
                            type="text"
                            name="ma_ve"
                            value="{{ old('ma_ve') }}"
                            placeholder="VD: VE260617ABC123"
                            autocomplete="off"
                        >
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
                            <div><span>Tổng tiền</span><strong>{{ number_format((float) $ticket->tong_tien, 0, ',', '.') }}đ</strong></div>
                            <div><span>Loại vé</span><strong>{{ $typeLabel[$ticket->loai_ve] ?? 'Không rõ' }}</strong></div>
                        </div>

                        @if ($ticket->trang_thai === 'da_thanh_toan')
                            <form method="POST" action="{{ route('admin.soat-ve.confirm') }}" class="confirm-form">
                                @csrf
                                <input type="hidden" name="ma_ve" value="{{ $ticket->ma_ve }}">
                                <button type="submit" class="scan-btn scan-btn-confirm" data-confirm-ticket="{{ $ticket->ma_ve }}">
                                    <i class="fa-solid fa-door-open"></i>
                                    Xác nhận sử dụng vé
                                </button>
                            </form>
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
        const confirmUrl = @json(route('admin.soat-ve.confirm'));
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

            const confirmButton = ticket.can_check_in ? `
                <button type="button" class="scan-btn scan-btn-confirm" data-confirm-ticket="${escapeHtml(ticket.ma_ve)}">
                    <i class="fa-solid fa-door-open"></i>
                    Xác nhận sử dụng vé
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
                    message: 'Máy chủ không trả về dữ liệu hợp lệ.',
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
                    setStatus(data.message || 'Vé hợp lệ. Chưa đánh dấu sử dụng.', 'success');
                } else {
                    setStatus(data.message || Object.values(data.errors || {})[0]?.[0] || 'Vé không hợp lệ.', 'error');
                }
            } catch (error) {
                setStatus('Không thể kết nối máy chủ. Vui lòng thử lại.', 'error');
            } finally {
                setTimeout(function () {
                    requestBusy = false;
                }, 700);
            }
        }

        async function confirmTicket(rawValue) {
            const value = String(rawValue || '').trim();

            if (!value || requestBusy) {
                return;
            }

            if (!confirm('Xác nhận khách đã vào rạp và đánh dấu vé này là đã sử dụng?')) {
                return;
            }

            requestBusy = true;
            setStatus('Đang xác nhận sử dụng vé...');

            try {
                const { response, data } = await postTicket(confirmUrl, value);

                if (data.ticket) {
                    input.value = data.ticket.ma_ve;
                    renderTicket(data.ticket);
                }

                if (response.ok && data.success) {
                    setStatus(data.message || 'Đã xác nhận sử dụng vé.', 'success');
                } else {
                    setStatus(data.message || Object.values(data.errors || {})[0]?.[0] || 'Không thể xác nhận vé.', 'error');
                }
            } catch (error) {
                setStatus('Không thể kết nối máy chủ. Vui lòng thử lại.', 'error');
            } finally {
                setTimeout(function () {
                    requestBusy = false;
                }, 700);
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
            inspectTicket(value);
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
                } catch (error) {}
            }

            requestAnimationFrame(scanFrame);
        }

        async function startScanner() {
            if (scanning) {
                return;
            }

            if ('BarcodeDetector' in window) {
                await startNativeScanner();

                if (activeScanner === 'native') {
                    return;
                }
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
                setStatus('Camera đã bật. Đưa QR vào khung để kiểm tra vé.');
                requestAnimationFrame(scanFrame);
            } catch (error) {
                setStatus('Không mở được camera. Hãy cấp quyền camera và thử lại.', 'error');
            }
        }

        async function startFallbackScanner() {
            if (!window.Html5Qrcode) {
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

                setStatus('Camera đã bật. Đưa QR vào khung để kiểm tra vé.');
            } catch (error) {
                scanning = false;
                activeScanner = null;
                cameraBox.classList.remove('is-live', 'is-fallback');
                setScannerButtons(false);
                setStatus('Không mở được camera. Hãy cấp quyền camera, tắt app khác đang dùng camera và thử lại.', 'error');
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
            inspectTicket(input.value);
        });

        resultBox.addEventListener('click', function (event) {
            const button = event.target.closest('[data-confirm-ticket]');

            if (!button || !window.fetch) {
                return;
            }

            event.preventDefault();
            confirmTicket(button.dataset.confirmTicket);
        });
    });
</script>
@endpush
