@extends('layouts.user')

@section('title', 'Liên hệ - CineHome')

@push('styles')
<style>
    .contact-page {
        width: min(100% - 32px, 1100px);
        margin: 0 auto;
        padding: 112px 0 80px;
        color: var(--cinema-text);
    }

    .icon-svg {
        display: inline-block;
        flex-shrink: 0;
        vertical-align: middle;
    }

    .contact-hero {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 14px;
        padding: 40px 24px 44px;
        margin-bottom: 36px;
        border: 1px solid var(--cinema-line);
        border-radius: 24px;
        background:
            radial-gradient(circle at 20% 0%, rgba(247, 184, 75, 0.14), transparent 55%),
            radial-gradient(circle at 85% 100%, rgba(229, 9, 20, 0.14), transparent 50%),
            var(--cinema-surface);
        box-shadow: var(--cinema-shadow);
    }

    .contact-hero-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 62px;
        height: 62px;
        border-radius: 18px;
        background: linear-gradient(135deg, var(--cinema-gold), #d99a32);
        color: #201002;
        box-shadow: 0 10px 26px rgba(247, 184, 75, 0.35);
    }

    .contact-hero-icon .icon-svg {
        width: 28px;
        height: 28px;
    }

    .contact-hero h1 {
        margin: 0;
        font-size: clamp(28px, 4vw, 40px);
        font-weight: 950;
        color: #ffffff;
    }

    .contact-hero p {
        max-width: 560px;
        margin: 0;
        color: var(--cinema-muted);
        font-size: 15.5px;
        line-height: 1.7;
    }

    .contact-grid {
        display: grid;
        grid-template-columns: 320px minmax(0, 1fr);
        gap: 26px;
        align-items: start;
    }

    .contact-info-card,
    .contact-form-card {
        border: 1px solid var(--cinema-line);
        border-radius: 22px;
        background: var(--cinema-card);
        box-shadow: var(--cinema-shadow);
    }

    .contact-info-card {
        padding: 28px 24px;
    }

    .contact-info-card h3 {
        margin: 0 0 6px;
        color: #ffffff;
        font-size: 19px;
        font-weight: 900;
    }

    .contact-info-card > p {
        margin: 0 0 22px;
        color: var(--cinema-muted);
        font-size: 14px;
        line-height: 1.6;
    }

    .contact-info-item {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 14px 0;
        border-top: 1px solid var(--cinema-line);
    }

    .contact-info-item:first-of-type {
        border-top: 0;
        padding-top: 0;
    }

    .contact-info-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: rgba(247, 184, 75, 0.12);
        color: var(--cinema-gold);
    }

    .contact-info-icon .icon-svg {
        width: 18px;
        height: 18px;
    }

    .contact-info-item strong {
        display: block;
        color: #ffffff;
        font-size: 14.5px;
        font-weight: 800;
        margin-bottom: 2px;
    }

    .contact-info-item span,
    .contact-info-item a.contact-info-link {
        display: block;
        color: var(--cinema-muted);
        font-size: 13.5px;
        line-height: 1.55;
    }

    .contact-info-item a.contact-info-link:hover {
        color: var(--cinema-gold);
    }

    .contact-note {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        margin-top: 22px;
        padding: 14px 16px;
        border-radius: 14px;
        border: 1px dashed rgba(247, 184, 75, 0.35);
        background: rgba(247, 184, 75, 0.06);
        color: #f2d9a8;
        font-size: 13px;
        line-height: 1.6;
    }

    .contact-note .icon-svg {
        width: 16px;
        height: 16px;
        margin-top: 2px;
        color: var(--cinema-gold);
    }

    .contact-form-card {
        padding: 30px 30px 32px;
    }

    .contact-form-card h3 {
        margin: 0 0 4px;
        color: #ffffff;
        font-size: 19px;
        font-weight: 900;
    }

    .contact-form-card > p {
        margin: 0 0 24px;
        color: var(--cinema-muted);
        font-size: 14px;
    }

    .contact-alert {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 18px;
        margin-bottom: 22px;
        border-radius: 14px;
        font-size: 14px;
        font-weight: 600;
    }

    .contact-alert .icon-svg {
        width: 20px;
        height: 20px;
    }

    .contact-alert-success {
        background: rgba(52, 211, 153, 0.12);
        border: 1px solid rgba(52, 211, 153, 0.35);
        color: #8ff0c9;
    }

    .contact-alert-danger {
        background: rgba(229, 9, 20, 0.1);
        border: 1px solid rgba(229, 9, 20, 0.35);
        color: #ff9da2;
    }

    .contact-alert-danger ul {
        margin: 0;
        padding-left: 18px;
    }

    .contact-field {
        margin-bottom: 18px;
    }

    .contact-field label {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 8px;
        color: #e7ecf5;
        font-size: 13.5px;
        font-weight: 700;
    }

    .contact-field label .icon-svg {
        width: 15px;
        height: 15px;
        color: var(--cinema-gold);
    }

    .contact-field .form-control,
    .contact-field .form-select {
        width: 100%;
        padding: 12px 14px;
        border-radius: 12px;
        border: 1px solid var(--cinema-line);
        background: rgba(255, 255, 255, 0.04);
        color: #ffffff;
        font-size: 14.5px;
        transition: border-color 0.2s var(--cinema-ease), box-shadow 0.2s var(--cinema-ease), background 0.2s var(--cinema-ease);
    }

    .contact-field .form-control::placeholder {
        color: rgba(255, 255, 255, 0.4);
    }

    .contact-field .form-control:focus,
    .contact-field .form-select:focus {
        outline: none;
        border-color: var(--cinema-gold);
        background: rgba(255, 255, 255, 0.06);
        box-shadow: 0 0 0 3px rgba(247, 184, 75, 0.16);
    }

    .contact-field select.form-select option {
        background: var(--cinema-surface-2);
        color: #ffffff;
    }

    .contact-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }

    .contact-submit-row {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 14px;
        margin-top: 6px;
    }

    .contact-submit-hint {
        color: var(--cinema-muted);
        font-size: 12.5px;
    }

    .contact-submit-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 13px 30px;
        border: 0;
        border-radius: 14px;
        background: linear-gradient(135deg, var(--cinema-gold), #d99a32);
        color: #201002;
        font-size: 15px;
        font-weight: 900;
        cursor: pointer;
        box-shadow: 0 12px 26px rgba(247, 184, 75, 0.28);
        transition: transform 0.2s var(--cinema-ease), box-shadow 0.2s var(--cinema-ease);
    }

    .contact-submit-btn .icon-svg {
        width: 16px;
        height: 16px;
    }

    .contact-submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 32px rgba(247, 184, 75, 0.36);
    }

    @media (max-width: 900px) {
        .contact-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 560px) {
        .contact-row {
            grid-template-columns: 1fr;
        }

        .contact-form-card {
            padding: 24px 20px 26px;
        }

        .contact-submit-row {
            justify-content: stretch;
        }

        .contact-submit-btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@php
    $svg = fn ($paths) => '<svg class="icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">' . $paths . '</svg>';

    $iconHeadset = $svg('<path d="M4 13.5V12a8 8 0 0 1 16 0v1.5" /><rect x="2.5" y="12.5" width="4" height="6.5" rx="2" /><rect x="17.5" y="12.5" width="4" height="6.5" rx="2" />');
    $iconEnvelope = $svg('<path d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />');
    $iconPhone = $svg('<path d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />');
    $iconClock = $svg('<path d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />');
    $iconPin = $svg('<path d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />');
    $iconInfo = $svg('<path d="M11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />');
    $iconCheck = $svg('<path d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />');
    $iconWarning = $svg('<path d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />');
    $iconUser = $svg('<path d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />');
    $iconAt = $svg('<path d="M16.5 12a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Zm0 0c0 1.657 1.007 3 2.25 3S21 13.657 21 12a9 9 0 1 0-2.636 6.364M16.5 12V8.25" />');
    $iconTag = $svg('<path d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z M6 6h.008v.008H6V6Z" />');
    $iconMessage = $svg('<path d="M4.5 5.25h15a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H9l-4.5 3v-3H4.5A1.5 1.5 0 0 1 3 15.75v-9a1.5 1.5 0 0 1 1.5-1.5Z" />');
    $iconSend = $svg('<path d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />');
@endphp

@section('content')
<div class="contact-page">

    <div class="contact-hero">
        <span class="contact-hero-icon">{!! $iconHeadset !!}</span>
        <h1>Liên hệ với chúng tôi</h1>
        <p>Bạn gặp lỗi khi đặt vé, thanh toán hay có bất kỳ vấn đề nào khi sử dụng CineHome? Hãy để lại lời nhắn, đội ngũ hỗ trợ sẽ tiếp nhận và xử lý sớm nhất.</p>
    </div>

    <div class="contact-grid">
        <div class="contact-info-card">
            <h3>Thông tin hỗ trợ</h3>
            <p>Liên hệ trực tiếp nếu vấn đề của bạn cần xử lý gấp.</p>

            <div class="contact-info-item">
                <span class="contact-info-icon">{!! $iconEnvelope !!}</span>
                <div>
                    <strong>Email hỗ trợ</strong>
                    <a href="mailto:support@cinehome.vn" class="contact-info-link">support@cinehome.vn</a>
                </div>
            </div>

            <div class="contact-info-item">
                <span class="contact-info-icon">{!! $iconPhone !!}</span>
                <div>
                    <strong>Hotline</strong>
                    <a href="tel:0123456789" class="contact-info-link">0123 456 789</a>
                </div>
            </div>

            <div class="contact-info-item">
                <span class="contact-info-icon">{!! $iconClock !!}</span>
                <div>
                    <strong>Thời gian hỗ trợ</strong>
                    <span>8:00 - 22:00 tất cả các ngày trong tuần</span>
                </div>
            </div>

            <div class="contact-info-item">
                <span class="contact-info-icon">{!! $iconPin !!}</span>
                <div>
                    <strong>Trụ sở</strong>
                    <span>Hệ thống rạp CineHome trên toàn quốc</span>
                </div>
            </div>

            <div class="contact-note">
                {!! $iconInfo !!}
                <span>Yêu cầu của bạn sẽ được gửi trực tiếp đến quản trị viên và xử lý theo thứ tự tiếp nhận.</span>
            </div>
        </div>

        <div class="contact-form-card">
            <h3>Gửi yêu cầu hỗ trợ</h3>
            <p>Vui lòng điền đầy đủ thông tin để chúng tôi có thể phản hồi bạn nhanh nhất.</p>

            @if (session('success'))
                <div class="contact-alert contact-alert-success">
                    {!! $iconCheck !!}
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="contact-alert contact-alert-danger">
                    {!! $iconWarning !!}
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('user.lien-he.store') }}">
                @csrf

                <div class="contact-row">
                    <div class="contact-field">
                        <label>{!! $iconUser !!} Họ tên</label>
                        <input type="text" name="ho_ten" class="form-control" placeholder="Nguyễn Văn A" value="{{ old('ho_ten', auth()->user()->ho_ten ?? '') }}" required>
                    </div>

                    <div class="contact-field">
                        <label>{!! $iconAt !!} Email</label>
                        <input type="email" name="email" class="form-control" placeholder="ban@email.com" value="{{ old('email', auth()->user()->email ?? '') }}" required>
                    </div>
                </div>

                <div class="contact-row">
                    <div class="contact-field">
                        <label>{!! $iconPhone !!} Số điện thoại <span style="color: var(--cinema-muted); font-weight: 400;">(không bắt buộc)</span></label>
                        <input type="text" name="so_dien_thoai" class="form-control" placeholder="09xx xxx xxx" value="{{ old('so_dien_thoai', auth()->user()->so_dien_thoai ?? '') }}">
                    </div>

                    <div class="contact-field">
                        <label>{!! $iconTag !!} Chủ đề</label>
                        <select name="chu_de" class="form-select" required>
                            <option value="">-- Chọn chủ đề --</option>
                            <option value="Lỗi đặt vé" {{ old('chu_de') === 'Lỗi đặt vé' ? 'selected' : '' }}>Lỗi đặt vé</option>
                            <option value="Lỗi thanh toán" {{ old('chu_de') === 'Lỗi thanh toán' ? 'selected' : '' }}>Lỗi thanh toán</option>
                            <option value="Lỗi tài khoản" {{ old('chu_de') === 'Lỗi tài khoản' ? 'selected' : '' }}>Lỗi tài khoản</option>
                            <option value="Góp ý" {{ old('chu_de') === 'Góp ý' ? 'selected' : '' }}>Góp ý</option>
                            <option value="Khác" {{ old('chu_de') === 'Khác' ? 'selected' : '' }}>Khác</option>
                        </select>
                    </div>
                </div>

                <div class="contact-field">
                    <label>{!! $iconMessage !!} Nội dung</label>
                    <textarea name="noi_dung" class="form-control" rows="5" placeholder="Mô tả chi tiết vấn đề bạn gặp phải...">{{ old('noi_dung') }}</textarea>
                </div>

                <div class="contact-submit-row">
                    <span class="contact-submit-hint">Chúng tôi thường phản hồi trong vòng 24 giờ.</span>
                    <button type="submit" class="contact-submit-btn">
                        {!! $iconSend !!}
                        Gửi liên hệ
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
