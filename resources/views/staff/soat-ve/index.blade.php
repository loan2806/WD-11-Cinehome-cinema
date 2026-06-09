@extends('layouts.staff')

@section('title', 'Soát vé QR')

@section('content')
<div class="scan-page">
    <div class="scan-header">
        <div>
            <h1>Soát vé QR</h1>
            <p>Nhập mã vé hoặc quét mã QR để kiểm tra vé của khách hàng.</p>
        </div>

        <div class="scan-icon">
            <i class="fa-solid fa-qrcode"></i>
        </div>
    </div>

    @if (session('success'))
        <div class="alert-box alert-success">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="alert-box alert-error">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="scan-grid">
        <div class="scan-card">
            <div class="card-title">
                <i class="fa-solid fa-ticket"></i>
                <h2>Kiểm tra vé</h2>
            </div>

            <form action="{{ route('staff.soat-ve.check') }}" method="POST">
                @csrf

                <label for="ma_ve">Mã vé</label>

                <div class="input-wrap">
                    <i class="fa-solid fa-barcode"></i>
                    <input
                        type="text"
                        id="ma_ve"
                        name="ma_ve"
                        value="{{ old('ma_ve') }}"
                        placeholder="Ví dụ: OFF-20260608123456-ABCDE"
                        class="ticket-input"
                        autocomplete="off"
                        autofocus
                    >
                </div>

                @error('ma_ve')
                    <div class="field-error">{{ $message }}</div>
                @enderror

                <button type="submit" class="btn-check-ticket">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    Kiểm tra vé
                </button>
            </form>

            <div class="scan-note">
                <i class="fa-solid fa-circle-info"></i>
                <span>QR chỉ cần chứa mã vé. Khi quét xong, hệ thống sẽ dùng mã vé để kiểm tra và cập nhật trạng thái.</span>
            </div>
        </div>

        <div class="result-card">
            <div class="card-title">
                <i class="fa-solid fa-clipboard-check"></i>
                <h2>Kết quả kiểm tra</h2>
            </div>

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

            @if ($ticket)
                <div class="ticket-result">
                    <div class="ticket-top">
                        <div>
                            <span class="ticket-small-title">Mã vé</span>
                            <h3>{{ $ticket->ma_ve }}</h3>
                        </div>

                        <span class="ticket-status status-{{ $ticket->trang_thai }}">
                            {{ $statusLabel[$ticket->trang_thai] ?? 'Không rõ' }}
                        </span>
                    </div>

                    <div class="ticket-info-list">
                        <div class="ticket-row">
                            <span><i class="fa-solid fa-film"></i> Phim</span>
                            <strong>{{ $ticket->ten_phim }}</strong>
                        </div>

                        <div class="ticket-row">
                            <span><i class="fa-solid fa-building"></i> Rạp</span>
                            <strong>{{ $ticket->ten_rap ?? 'Chưa có' }}</strong>
                        </div>

                        <div class="ticket-row">
                            <span><i class="fa-solid fa-door-open"></i> Phòng</span>
                            <strong>{{ $ticket->ten_phong ?? 'Chưa có' }}</strong>
                        </div>

                        <div class="ticket-row">
                            <span><i class="fa-solid fa-couch"></i> Ghế</span>
                            <strong>{{ $ticket->ma_ghe ?? 'Chưa có' }}</strong>
                        </div>

                        <div class="ticket-row">
                            <span><i class="fa-solid fa-clock"></i> Suất chiếu</span>
                            <strong>
                                {{ $ticket->thoi_gian_chieu ? $ticket->thoi_gian_chieu->format('d/m/Y H:i') : 'Chưa có' }}
                            </strong>
                        </div>

                        <div class="ticket-row">
                            <span><i class="fa-solid fa-money-bill"></i> Tổng tiền</span>
                            <strong>{{ number_format($ticket->tong_tien, 0, ',', '.') }}đ</strong>
                        </div>

                        <div class="ticket-row">
                            <span><i class="fa-solid fa-tags"></i> Loại vé</span>
                            <strong>{{ $typeLabel[$ticket->loai_ve] ?? 'Không rõ' }}</strong>
                        </div>
                    </div>
                </div>
            @else
                <div class="empty-result">
                    <div class="empty-icon">
                        <i class="fa-solid fa-qrcode"></i>
                    </div>
                    <h3>Chưa có vé nào được kiểm tra</h3>
                    <p>Nhập mã vé ở khung bên trái để xem thông tin vé tại đây.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .scan-page {
        animation: fadeIn 0.35s ease;
    }

    .scan-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 22px;
    }

    .scan-header h1 {
        margin: 0;
        font-size: 30px;
        color: #fff;
        font-weight: 900;
    }

    .scan-header p {
        margin-top: 8px;
        color: #b7b7b7;
    }

    .scan-icon {
        width: 64px;
        height: 64px;
        border-radius: 22px;
        display: grid;
        place-items: center;
        color: #f5a623;
        background: radial-gradient(circle at top, rgba(245, 166, 35, 0.28), rgba(245, 166, 35, 0.08));
        box-shadow: 0 0 30px rgba(245, 166, 35, 0.18);
        transition: all 0.3s ease;
    }

    .scan-icon:hover {
        transform: translateY(-4px) scale(1.04);
        box-shadow: 0 0 42px rgba(245, 166, 35, 0.3);
    }

    .scan-icon i {
        font-size: 28px;
    }

    .scan-grid {
        display: grid;
        grid-template-columns: minmax(420px, 1fr) minmax(420px, 1fr);
        gap: 28px;
        align-items: stretch;
    }

    .scan-card,
    .result-card {
        position: relative;
        overflow: hidden;
        background: linear-gradient(145deg, #171717, #101010);
        border: 1px solid rgba(245, 166, 35, 0.28);
        border-radius: 30px;
        padding: 30px;
        box-shadow: 0 20px 55px rgba(0, 0, 0, 0.28);
        transition: transform 0.32s ease, box-shadow 0.32s ease, border-color 0.32s ease;
    }

    .scan-card::before,
    .result-card::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background: radial-gradient(circle at top left, rgba(245, 166, 35, 0.1), transparent 36%);
        opacity: 0;
        transition: opacity 0.32s ease;
    }

    .scan-card:hover,
    .result-card:hover {
        transform: translateY(-6px);
        border-color: rgba(245, 166, 35, 0.68);
        box-shadow:
            0 26px 70px rgba(0, 0, 0, 0.45),
            0 0 28px rgba(245, 166, 35, 0.12);
    }

    .scan-card:hover::before,
    .result-card:hover::before {
        opacity: 1;
    }

    .card-title {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 22px;
        position: relative;
        z-index: 1;
    }

    .card-title i {
        color: #f5a623;
        font-size: 20px;
    }

    .card-title h2 {
        margin: 0;
        font-size: 22px;
        color: #fff;
        font-weight: 800;
    }

    label {
        display: block;
        margin-bottom: 10px;
        color: #f0f0f0;
        font-weight: 700;
        position: relative;
        z-index: 1;
    }

    .input-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
        background: rgba(255, 255, 255, 0.035);
        border: 1px solid rgba(245, 166, 35, 0.32);
        border-radius: 20px;
        padding: 0 16px;
        transition: all 0.28s ease;
        position: relative;
        z-index: 1;
    }

    .input-wrap:focus-within {
        border-color: #f5a623;
        background: rgba(245, 166, 35, 0.05);
        box-shadow: 0 0 0 5px rgba(245, 166, 35, 0.12);
    }

    .input-wrap i {
        color: #f5a623;
        min-width: 18px;
    }

    .ticket-input {
        width: 100%;
        height: 54px;
        background: transparent;
        border: none;
        outline: none;
        color: #ffffff;
        font-size: 15px;
        font-weight: 600;
        letter-spacing: 0.4px;
        transition: color 0.25s ease;
    }

    .ticket-input::placeholder {
        color: #7f7f7f;
    }

    .ticket-input:focus {
        color: #f5a623;
    }

    .btn-check-ticket {
        width: 100%;
        margin-top: 18px;
        border: none;
        border-radius: 20px;
        padding: 15px 18px;
        color: #fff;
        font-weight: 900;
        cursor: pointer;
        background: linear-gradient(135deg, #d89227, #f5a623);
        box-shadow: 0 12px 28px rgba(245, 166, 35, 0.18);
        transition: transform 0.28s ease, box-shadow 0.28s ease, filter 0.28s ease;
        position: relative;
        z-index: 1;
    }

    .btn-check-ticket:hover {
        transform: translateY(-3px) scale(1.01);
        box-shadow: 0 18px 42px rgba(245, 166, 35, 0.32);
        filter: brightness(1.06);
    }

    .btn-check-ticket i {
        margin-right: 8px;
    }

    .scan-note {
        display: flex;
        gap: 10px;
        margin-top: 18px;
        padding: 15px 16px;
        border-radius: 20px;
        color: #aaa;
        background: rgba(255, 255, 255, 0.06);
        line-height: 1.5;
        transition: all 0.25s ease;
        position: relative;
        z-index: 1;
    }

    .scan-note:hover {
        background: rgba(245, 166, 35, 0.08);
        color: #ddd;
    }

    .scan-note i {
        color: #f5a623;
        margin-top: 3px;
    }

    .alert-box {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 18px;
        padding: 15px 18px;
        border-radius: 20px;
        font-weight: 700;
        animation: slideDown 0.25s ease;
    }

    .alert-success {
        color: #b9ffd2;
        background: rgba(34, 197, 94, 0.13);
        border: 1px solid rgba(34, 197, 94, 0.35);
    }

    .alert-error {
        color: #ffcccc;
        background: rgba(239, 68, 68, 0.13);
        border: 1px solid rgba(239, 68, 68, 0.35);
    }

    .ticket-result {
        position: relative;
        z-index: 1;
    }

    .ticket-top {
        display: flex;
        justify-content: space-between;
        gap: 18px;
        align-items: flex-start;
        padding: 18px;
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.045);
        margin-bottom: 18px;
    }

    .ticket-small-title {
        display: block;
        color: #aaa;
        font-size: 13px;
        margin-bottom: 6px;
    }

    .ticket-top h3 {
        margin: 0;
        color: #fff;
        font-size: 20px;
        word-break: break-word;
    }

    .ticket-status {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        padding: 9px 14px;
        border-radius: 999px;
        font-weight: 900;
        font-size: 13px;
    }

    .status-da_su_dung {
        color: #b9ffd2;
        background: rgba(34, 197, 94, 0.14);
        border: 1px solid rgba(34, 197, 94, 0.38);
    }

    .status-da_thanh_toan {
        color: #ffe4a3;
        background: rgba(245, 166, 35, 0.14);
        border: 1px solid rgba(245, 166, 35, 0.38);
    }

    .status-da_huy {
        color: #ffcccc;
        background: rgba(239, 68, 68, 0.14);
        border: 1px solid rgba(239, 68, 68, 0.38);
    }

    .ticket-info-list {
        display: grid;
        gap: 10px;
    }

    .ticket-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 14px 16px;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.035);
        transition: all 0.25s ease;
    }

    .ticket-row:hover {
        background: rgba(245, 166, 35, 0.08);
        transform: translateX(4px);
    }

    .ticket-row span {
        display: flex;
        align-items: center;
        gap: 9px;
        color: #aaa;
    }

    .ticket-row span i {
        color: #f5a623;
        width: 18px;
    }

    .ticket-row strong {
        color: #fff;
        text-align: right;
        font-weight: 800;
    }

    .empty-result {
        min-height: 280px;
        display: grid;
        place-items: center;
        text-align: center;
        color: #888;
        position: relative;
        z-index: 1;
    }

    .empty-icon {
        width: 90px;
        height: 90px;
        display: grid;
        place-items: center;
        border-radius: 28px;
        margin: 0 auto 18px;
        color: rgba(245, 166, 35, 0.55);
        background: rgba(245, 166, 35, 0.08);
        transition: all 0.28s ease;
    }

    .empty-result:hover .empty-icon {
        transform: rotate(4deg) scale(1.04);
        color: #f5a623;
    }

    .empty-icon i {
        font-size: 40px;
    }

    .empty-result h3 {
        margin: 0 0 8px;
        color: #ddd;
        font-size: 18px;
    }

    .empty-result p {
        margin: 0;
        color: #888;
    }

    .field-error {
        color: #ffb4b4;
        margin-top: 10px;
        font-size: 14px;
        position: relative;
        z-index: 1;
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

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-6px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 1100px) {
        .scan-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection