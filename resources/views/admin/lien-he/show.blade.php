@extends('layouts.admin')

@section('page-title', 'Chi tiết liên hệ #' . $lienHe->id)
@section('page-subtitle', 'Xem và phản hồi yêu cầu hỗ trợ từ khách hàng')

@push('styles')
<style>
    .icon-svg {
        display: inline-block;
        flex-shrink: 0;
        vertical-align: middle;
    }

    .contact-detail-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 380px;
        gap: 22px;
        align-items: start;
    }

    .contact-detail-card,
    .contact-reply-card {
        border: 1px solid var(--cinema-line);
        border-radius: 20px;
        background: var(--cinema-card);
        box-shadow: var(--cinema-shadow);
    }

    .contact-detail-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 20px 22px;
        border-bottom: 1px solid var(--cinema-line);
    }

    .contact-detail-head h3 {
        margin: 0;
        color: #ffffff;
        font-size: 16px;
        font-weight: 900;
    }

    .contact-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }

    .contact-status-pill .icon-svg {
        width: 12px;
        height: 12px;
    }

    .contact-status-pill.is-pending {
        background: rgba(255, 59, 70, 0.14);
        color: var(--cinema-red-soft);
    }

    .contact-status-pill.is-progress {
        background: rgba(247, 184, 75, 0.14);
        color: var(--cinema-gold);
    }

    .contact-status-pill.is-done {
        background: rgba(34, 197, 94, 0.14);
        color: #4ade80;
    }

    .contact-detail-body {
        padding: 22px;
    }

    .contact-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }

    .contact-info-row {
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }

    .contact-info-row .icon-svg {
        width: 16px;
        height: 16px;
        margin-top: 2px;
        color: var(--cinema-red-soft);
    }

    .contact-info-row small {
        display: block;
        color: var(--cinema-muted);
        font-size: 11.5px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .contact-info-row strong {
        display: block;
        color: #ffffff;
        font-size: 13.5px;
        font-weight: 700;
        word-break: break-word;
    }

    .contact-message-box {
        padding: 18px;
        border-radius: 14px;
        border-left: 3px solid var(--cinema-red-soft);
        background: rgba(255, 255, 255, 0.03);
        color: #e7ecf5;
        font-size: 14px;
        line-height: 1.7;
        white-space: pre-line;
    }

    .contact-reply-body {
        padding: 22px;
        display: grid;
        gap: 14px;
    }

    .contact-reply-body .admin-input {
        min-height: 140px;
    }

    .contact-reply-hint {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        padding: 12px 14px;
        border-radius: 12px;
        border: 1px dashed rgba(247, 184, 75, 0.35);
        background: rgba(247, 184, 75, 0.06);
        color: #f2d9a8;
        font-size: 12.5px;
        line-height: 1.6;
    }

    .contact-reply-hint .icon-svg {
        width: 15px;
        height: 15px;
        margin-top: 1px;
        color: var(--cinema-gold);
    }

    .contact-reply-meta {
        color: var(--cinema-muted);
        font-size: 12.5px;
    }

    .contact-back-link {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-top: 22px;
        padding: 10px 20px 10px 10px;
        border-radius: 999px;
        border: 1px solid var(--cinema-line);
        background: var(--cinema-card);
        color: var(--cinema-muted);
        font-size: 13.5px;
        font-weight: 700;
        text-decoration: none;
        transition: border-color 0.2s var(--cinema-ease), color 0.2s var(--cinema-ease), background 0.2s var(--cinema-ease), transform 0.2s var(--cinema-ease);
    }

    .contact-back-link:hover {
        color: #ffffff;
        border-color: rgba(255, 59, 70, 0.4);
        background: rgba(255, 59, 70, 0.08);
        transform: translateX(-3px);
    }

    .contact-back-link .icon-circle {
        position: relative;
        display: inline-block;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: rgba(255, 59, 70, 0.14);
        color: var(--cinema-red-soft);
        transition: background 0.2s var(--cinema-ease);
    }

    .contact-back-link:hover .icon-circle {
        background: linear-gradient(135deg, var(--cinema-red), var(--cinema-red-soft));
        color: #ffffff;
    }

    .contact-back-link .icon-circle .icon-svg {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 13px;
        height: 13px;
    }

    /* CUSTOM STATUS DROPDOWN */
    .contact-status-select {
        position: relative;
    }

    .contact-status-select-trigger {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        width: 100%;
        padding: 11px 14px;
        border-radius: 12px;
        border: 1px solid var(--cinema-line);
        background: rgba(255, 255, 255, 0.04);
        color: #ffffff;
        font-size: 14.5px;
        font-weight: 700;
        cursor: pointer;
        transition: border-color 0.2s var(--cinema-ease), background 0.2s var(--cinema-ease);
    }

    .contact-status-select-trigger:hover {
        background: rgba(255, 255, 255, 0.06);
    }

    .contact-status-select.is-open .contact-status-select-trigger {
        border-color: var(--cinema-red-soft);
        box-shadow: 0 0 0 3px rgba(255, 59, 70, 0.14);
    }

    .contact-status-select-trigger .icon-svg {
        width: 15px;
        height: 15px;
        color: var(--cinema-muted);
        transition: transform 0.2s var(--cinema-ease);
    }

    .contact-status-select.is-open .contact-status-select-trigger .icon-svg {
        transform: rotate(180deg);
    }

    .contact-status-select-current {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .contact-status-select-current .icon-svg {
        width: 15px;
        height: 15px;
        color: var(--cinema-muted);
    }

    .contact-status-select-current.is-pending .icon-svg { color: var(--cinema-red-soft); }
    .contact-status-select-current.is-progress .icon-svg { color: var(--cinema-gold); }
    .contact-status-select-current.is-done .icon-svg { color: #4ade80; }

    .contact-status-select-menu {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        right: 0;
        z-index: 20;
        overflow: hidden;
        border-radius: 14px;
        border: 1px solid var(--cinema-line);
        background: var(--cinema-surface-2);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
    }

    .contact-status-option {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        padding: 12px 14px;
        border: 0;
        background: transparent;
        color: var(--cinema-muted);
        font-size: 14px;
        font-weight: 700;
        text-align: left;
        cursor: pointer;
        transition: background 0.15s var(--cinema-ease), color 0.15s var(--cinema-ease);
    }

    .contact-status-option:not(:last-child) {
        border-bottom: 1px solid var(--cinema-line);
    }

    .contact-status-option .icon-svg {
        width: 15px;
        height: 15px;
    }

    .contact-status-option.is-pending .icon-svg { color: var(--cinema-red-soft); }
    .contact-status-option.is-progress .icon-svg { color: var(--cinema-gold); }
    .contact-status-option.is-done .icon-svg { color: #4ade80; }

    .contact-status-option:hover {
        background: rgba(255, 255, 255, 0.05);
        color: #ffffff;
    }

    .contact-status-option.is-selected {
        background: rgba(255, 59, 70, 0.1);
        color: #ffffff;
    }

    @media (max-width: 900px) {
        .contact-detail-grid {
            grid-template-columns: 1fr;
        }

        .contact-info-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@php
    $svg = fn ($paths) => '<svg class="icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">' . $paths . '</svg>';

    $iconClock = $svg('<path d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />');
    $iconLoader = $svg('<path d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />');
    $iconCheck = $svg('<path d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />');
    $iconEnvelope = $svg('<path d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />');
    $iconPhone = $svg('<path d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />');
    $iconUser = $svg('<path d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />');
    $iconTag = $svg('<path d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z M6 6h.008v.008H6V6Z" />');
    $iconCalendar = $svg('<path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />');
    $iconInfo = $svg('<path d="M11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />');
    $iconArrowLeft = $svg('<path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />');
    $iconChevron = $svg('<path d="m19.5 8.25-7.5 7.5-7.5-7.5" />');

    $statusOptions = [
        'cho_xu_ly' => ['class' => 'is-pending', 'label' => 'Chờ xử lý', 'icon' => $iconClock],
        'dang_xu_ly' => ['class' => 'is-progress', 'label' => 'Đang xử lý', 'icon' => $iconLoader],
        'da_xu_ly' => ['class' => 'is-done', 'label' => 'Đã xử lý', 'icon' => $iconCheck],
    ];
    $statusMeta = $statusOptions[$lienHe->trang_thai];
@endphp

@section('content')
@include('admin.partials.flash')

<div class="contact-detail-grid">
    <div class="contact-detail-card">
        <div class="contact-detail-head">
            <h3>Nội dung liên hệ #{{ $lienHe->id }}</h3>
            <span class="contact-status-pill {{ $statusMeta['class'] }}">{!! $statusMeta['icon'] !!} {{ $statusMeta['label'] }}</span>
        </div>
        <div class="contact-detail-body">
            <div class="contact-info-grid">
                <div class="contact-info-row">
                    {!! $iconUser !!}
                    <div>
                        <small>Họ tên</small>
                        <strong>{{ $lienHe->ho_ten }}</strong>
                    </div>
                </div>
                <div class="contact-info-row">
                    {!! $iconEnvelope !!}
                    <div>
                        <small>Email</small>
                        <strong>{{ $lienHe->email }}</strong>
                    </div>
                </div>
                <div class="contact-info-row">
                    {!! $iconPhone !!}
                    <div>
                        <small>Số điện thoại</small>
                        <strong>{{ $lienHe->so_dien_thoai ?? 'Không cung cấp' }}</strong>
                    </div>
                </div>
                <div class="contact-info-row">
                    {!! $iconTag !!}
                    <div>
                        <small>Chủ đề</small>
                        <strong>{{ $lienHe->chu_de }}</strong>
                    </div>
                </div>
                <div class="contact-info-row">
                    {!! $iconCalendar !!}
                    <div>
                        <small>Ngày gửi</small>
                        <strong>{{ $lienHe->created_at->format('d/m/Y H:i') }}</strong>
                    </div>
                </div>
                @if($lienHe->nguoiDung)
                    <div class="contact-info-row">
                        {!! $iconUser !!}
                        <div>
                            <small>Tài khoản</small>
                            <strong>{{ $lienHe->nguoiDung->ho_ten }} (#{{ $lienHe->nguoiDung->id }})</strong>
                        </div>
                    </div>
                @endif
            </div>

            <div class="contact-message-box">{{ $lienHe->noi_dung }}</div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.lien-he.update', $lienHe) }}" class="contact-reply-card">
        @csrf
        @method('PATCH')
        <div class="contact-detail-head">
            <h3>Xử lý liên hệ</h3>
        </div>
        <div class="contact-reply-body">
            @php $currentTrangThai = old('trang_thai', $lienHe->trang_thai); @endphp
            <div class="contact-status-select" data-name="trang_thai" data-value="{{ $currentTrangThai }}">
                <input type="hidden" name="trang_thai" value="{{ $currentTrangThai }}">

                <button type="button" class="contact-status-select-trigger">
                    <span class="contact-status-select-current {{ $statusOptions[$currentTrangThai]['class'] }}">
                        {!! $statusOptions[$currentTrangThai]['icon'] !!}
                        <span class="label">{{ $statusOptions[$currentTrangThai]['label'] }}</span>
                    </span>
                    {!! $iconChevron !!}
                </button>

                <div class="contact-status-select-menu hidden">
                    @foreach ($statusOptions as $value => $meta)
                        <button type="button" class="contact-status-option {{ $meta['class'] }}" data-value="{{ $value }}" data-label="{{ $meta['label'] }}">
                            {!! $meta['icon'] !!}
                            <span>{{ $meta['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <textarea name="phan_hoi" class="admin-input" placeholder="Nhập phản hồi gửi tới khách hàng...">{{ old('phan_hoi', $lienHe->phan_hoi) }}</textarea>

            <div class="contact-reply-hint">
                {!! $iconInfo !!}
                <span>Nội dung này sẽ được gửi qua email tới <strong>{{ $lienHe->email }}</strong> khi bạn lưu.</span>
            </div>

            @if($lienHe->nguoiXuLy)
                <p class="contact-reply-meta">
                    Xử lý gần nhất bởi {{ $lienHe->nguoiXuLy->ho_ten }}
                    @if($lienHe->thoi_gian_xu_ly)
                        lúc {{ $lienHe->thoi_gian_xu_ly->format('d/m/Y H:i') }}
                    @endif
                </p>
            @endif

            <button class="btn-admin w-full" type="submit">Lưu cập nhật</button>
        </div>
    </form>
</div>

<a href="{{ route('admin.lien-he.index') }}" class="contact-back-link">
    <span class="icon-circle">{!! $iconArrowLeft !!}</span>
    Quay lại danh sách
</a>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.contact-status-select').forEach(function (wrap) {
            const trigger = wrap.querySelector('.contact-status-select-trigger');
            const menu = wrap.querySelector('.contact-status-select-menu');
            const hiddenInput = wrap.querySelector('input[type="hidden"]');
            const current = wrap.querySelector('.contact-status-select-current');
            const options = wrap.querySelectorAll('.contact-status-option');

            function closeMenu() {
                wrap.classList.remove('is-open');
                menu.classList.add('hidden');
            }

            function markSelected(value) {
                options.forEach(function (opt) {
                    opt.classList.toggle('is-selected', opt.dataset.value === value);
                });
            }

            markSelected(hiddenInput.value);

            trigger.addEventListener('click', function (event) {
                event.stopPropagation();
                const willOpen = menu.classList.contains('hidden');
                closeMenu();
                if (willOpen) {
                    wrap.classList.add('is-open');
                    menu.classList.remove('hidden');
                }
            });

            options.forEach(function (opt) {
                opt.addEventListener('click', function () {
                    hiddenInput.value = opt.dataset.value;
                    current.className = 'contact-status-select-current ' + opt.className.replace('contact-status-option', '').replace('is-selected', '').trim();
                    current.innerHTML = opt.innerHTML.replace(/<span>(.*?)<\/span>/, '<span class="label">$1</span>');
                    markSelected(opt.dataset.value);
                    closeMenu();
                });
            });

            document.addEventListener('click', function (event) {
                if (!wrap.contains(event.target)) {
                    closeMenu();
                }
            });
        });
    });
</script>
@endpush
@endsection
