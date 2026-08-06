@extends('layouts.admin')

@section('page-title', 'Liên hệ khách hàng')
@section('page-subtitle', 'Danh sách các yêu cầu hỗ trợ, phản ánh lỗi từ khách hàng')

@push('styles')
<style>
    .contact-admin-page {
        display: grid;
        gap: 22px;
    }

    .icon-svg {
        display: inline-block;
        flex-shrink: 0;
        vertical-align: middle;
    }

    /* HERO */
    .contact-admin-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
        padding: 26px 28px;
        border-radius: 22px;
        border: 1px solid var(--cinema-line);
        background:
            radial-gradient(circle at 10% 0%, rgba(255, 59, 70, 0.16), transparent 45%),
            radial-gradient(circle at 95% 100%, rgba(247, 184, 75, 0.08), transparent 45%),
            var(--cinema-card);
        box-shadow: var(--cinema-shadow);
    }

    .contact-admin-hero-icon {
        position: relative;
        display: inline-block;
        width: 52px;
        height: 52px;
        flex: 0 0 52px;
        border-radius: 16px;
        background: linear-gradient(135deg, var(--cinema-red), var(--cinema-red-soft));
        color: #ffffff;
        box-shadow: 0 10px 26px rgba(229, 9, 20, 0.32);
    }

    .contact-admin-hero-icon .icon-svg {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 24px;
        height: 24px;
    }

    .contact-admin-hero-copy {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .contact-admin-hero h2 {
        margin: 0 0 4px;
        color: #ffffff;
        font-size: 20px;
        font-weight: 950;
    }

    .contact-admin-hero p {
        margin: 0;
        color: var(--cinema-muted);
        font-size: 13.5px;
        max-width: 480px;
    }

    /* STATS */
    .contact-admin-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .contact-admin-stat {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 18px 20px;
        border-radius: 18px;
        border: 1px solid var(--cinema-line);
        background: var(--cinema-card);
    }

    .contact-admin-stat span.icon-wrap {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        border-radius: 14px;
    }

    .contact-admin-stat span.icon-wrap .icon-svg {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 20px;
        height: 20px;
    }

    .contact-admin-stat.is-total span.icon-wrap {
        background: rgba(255, 255, 255, 0.08);
        color: #ffffff;
    }

    .contact-admin-stat.is-pending span.icon-wrap {
        background: rgba(255, 59, 70, 0.14);
        color: var(--cinema-red-soft);
    }

    .contact-admin-stat.is-progress span.icon-wrap {
        background: rgba(247, 184, 75, 0.14);
        color: var(--cinema-gold);
    }

    .contact-admin-stat.is-done span.icon-wrap {
        background: rgba(34, 197, 94, 0.14);
        color: #4ade80;
    }

    .contact-admin-stat small {
        display: block;
        color: var(--cinema-muted);
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .contact-admin-stat strong {
        display: block;
        margin-top: 2px;
        color: #ffffff;
        font-size: 22px;
        font-weight: 950;
    }

    /* PANEL */
    .contact-admin-panel {
        border: 1px solid var(--cinema-line);
        border-radius: 22px;
        background: var(--cinema-card);
        box-shadow: var(--cinema-shadow);
        overflow: hidden;
    }

    .contact-admin-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        padding: 20px 24px;
        border-bottom: 1px solid var(--cinema-line);
    }

    .contact-admin-panel-head h3 {
        margin: 0;
        color: #ffffff;
        font-size: 17px;
        font-weight: 900;
    }

    .contact-admin-panel-head p {
        margin: 4px 0 0;
        color: var(--cinema-muted);
        font-size: 13px;
    }

    .contact-admin-filter {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        padding: 16px 24px;
        border-bottom: 1px solid var(--cinema-line);
        background: rgba(255, 255, 255, 0.015);
    }

    .contact-admin-search {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 240px;
        flex: 1 1 240px;
        padding: 0 14px;
        height: 42px;
        border-radius: 12px;
        border: 1px solid var(--cinema-line);
        background: rgba(255, 255, 255, 0.04);
        color: var(--cinema-muted);
    }

    .contact-admin-search .icon-svg {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
    }

    .contact-admin-search input {
        flex: 1;
        border: 0;
        background: transparent;
        color: #ffffff;
        font-size: 14px;
        outline: none;
    }

    .contact-admin-search input::placeholder {
        color: rgba(255, 255, 255, 0.35);
    }

    /* CUSTOM STATUS FILTER DROPDOWN */
    .contact-status-select {
        position: relative;
        min-width: 200px;
    }

    .contact-status-select-trigger {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        width: 100%;
        height: 42px;
        padding: 0 14px;
        border-radius: 12px;
        border: 1px solid var(--cinema-line);
        background: rgba(255, 255, 255, 0.04);
        color: #ffffff;
        font-size: 13.5px;
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
        gap: 9px;
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
        padding: 11px 14px;
        border: 0;
        background: transparent;
        color: var(--cinema-muted);
        font-size: 13.5px;
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
        color: var(--cinema-muted);
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

    .contact-admin-filter-btn,
    .contact-admin-reset-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        height: 42px;
        padding: 0 18px;
        border-radius: 12px;
        border: 0;
        font-size: 13.5px;
        font-weight: 800;
        cursor: pointer;
        text-decoration: none;
    }

    .contact-admin-filter-btn {
        background: linear-gradient(135deg, var(--cinema-red), var(--cinema-red-soft));
        color: #ffffff;
    }

    .contact-admin-filter-btn .icon-svg {
        width: 15px;
        height: 15px;
    }

    .contact-admin-reset-btn {
        width: 42px;
        padding: 0;
        justify-content: center;
        background: rgba(255, 255, 255, 0.06);
        color: var(--cinema-muted);
        border: 1px solid var(--cinema-line);
    }

    .contact-admin-reset-btn:hover {
        color: #ffffff;
        border-color: rgba(255, 59, 70, 0.4);
    }

    .contact-admin-reset-btn .icon-svg {
        width: 15px;
        height: 15px;
    }

    /* TABLE */
    .contact-admin-table-wrap {
        overflow-x: auto;
    }

    .contact-admin-table {
        width: 100%;
        border-collapse: collapse;
        color: #ffffff;
    }

    .contact-admin-table thead {
        background: rgba(255, 255, 255, 0.03);
    }

    .contact-admin-table th {
        padding: 13px 20px;
        color: #e8d2bb;
        font-size: 11.5px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        text-align: left;
        white-space: nowrap;
    }

    .contact-admin-table th.is-right,
    .contact-admin-table td.is-right {
        text-align: right;
    }

    .contact-admin-table td {
        padding: 16px 20px;
        border-top: 1px solid var(--cinema-line);
        vertical-align: top;
        font-size: 13.5px;
    }

    .contact-profile-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .contact-avatar {
        position: relative;
        display: inline-block;
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        border-radius: 12px;
        background: rgba(255, 59, 70, 0.12);
        color: var(--cinema-red-soft);
    }

    .contact-avatar .icon-svg {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 18px;
        height: 18px;
    }

    .contact-profile-cell strong {
        display: block;
        color: #ffffff;
        font-weight: 800;
    }

    .contact-profile-cell small {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-top: 3px;
        color: var(--cinema-muted);
        font-size: 12px;
    }

    .contact-profile-cell small .icon-svg {
        width: 11px;
        height: 11px;
    }

    .contact-info-cell strong {
        display: block;
        color: #ffffff;
        font-weight: 700;
        font-size: 13px;
    }

    .contact-info-cell small {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-top: 4px;
        color: var(--cinema-muted);
        font-size: 12px;
    }

    .contact-info-cell small .icon-svg {
        width: 11px;
        height: 11px;
    }

    .contact-topic-badge {
        display: inline-block;
        padding: 4px 11px;
        margin-bottom: 6px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.08);
        color: #e8d2bb;
        font-size: 11.5px;
        font-weight: 800;
    }

    .contact-content-preview {
        display: block;
        max-width: 260px;
        color: var(--cinema-muted);
        font-size: 12.5px;
        line-height: 1.5;
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

    .contact-date-cell {
        color: var(--cinema-muted);
        font-size: 12.5px;
        white-space: nowrap;
    }

    .contact-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
    }

    .contact-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 10px;
        border: 0;
        color: #ffffff;
        cursor: pointer;
        transition: transform 0.15s var(--cinema-ease), opacity 0.15s var(--cinema-ease);
    }

    .contact-action-btn .icon-svg {
        width: 15px;
        height: 15px;
    }

    .contact-action-btn:hover {
        transform: translateY(-1px);
        opacity: 0.9;
    }

    .contact-action-btn.is-view {
        background: rgba(56, 189, 248, 0.16);
        color: #38bdf8;
    }

    .contact-action-btn.is-delete {
        background: rgba(255, 59, 70, 0.16);
        color: var(--cinema-red-soft);
    }

    /* EMPTY STATE */
    .contact-admin-empty {
        display: grid;
        justify-items: center;
        gap: 8px;
        padding: 48px 20px;
        text-align: center;
        color: var(--cinema-muted);
    }

    .contact-admin-empty .icon-svg {
        width: 34px;
        height: 34px;
        color: var(--cinema-muted);
        opacity: 0.6;
    }

    .contact-admin-empty strong {
        color: #ffffff;
        font-size: 15px;
    }

    .contact-admin-pagination {
        padding: 18px 24px;
    }

    @media (max-width: 900px) {
        .contact-admin-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 560px) {
        .contact-admin-stats {
            grid-template-columns: 1fr;
        }

        .contact-content-preview {
            max-width: 160px;
        }
    }
</style>
@endpush

@php
    $svg = fn ($paths) => '<svg class="icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">' . $paths . '</svg>';

    $iconInbox = $svg('<path d="M3 12h4.5l1.5 3h6l1.5-3H21M3 12v6a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-6M3 12l2.25-6.75A1.5 1.5 0 0 1 6.68 4.25h10.64a1.5 1.5 0 0 1 1.43 1L21 12" />');
    $iconClock = $svg('<path d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />');
    $iconLoader = $svg('<path d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />');
    $iconCheck = $svg('<path d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />');
    $iconSearch = $svg('<path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />');
    $iconFilter = $svg('<path d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m9 12h3.75M16.5 18a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 18H13.5M3.75 12h9.75m0 0a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0m9.75 0H21" />');
    $iconReset = $svg('<path d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />');
    $iconUser = $svg('<path d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />');
    $iconEnvelope = $svg('<path d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />');
    $iconPhone = $svg('<path d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />');
    $iconCalendar = $svg('<path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />');
    $iconEye = $svg('<path d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />');
    $iconTrash = $svg('<path d="m14.74 9-.346 9m-4.788 0L9.26 9M19.228 5.79c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.771 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />');
    $iconInboxEmpty = $svg('<path d="M3 12h4.5l1.5 3h6l1.5-3H21M3 12v6a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-6M3 12l2.25-6.75A1.5 1.5 0 0 1 6.68 4.25h10.64a1.5 1.5 0 0 1 1.43 1L21 12" />');
    $iconChevron = $svg('<path d="m19.5 8.25-7.5 7.5-7.5-7.5" />');
    $iconLayers = $svg('<path d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />');

    $statusFilterOptions = [
        '' => ['class' => '', 'label' => 'Tất cả trạng thái', 'icon' => $iconLayers],
        'cho_xu_ly' => ['class' => 'is-pending', 'label' => 'Chờ xử lý', 'icon' => $iconClock],
        'dang_xu_ly' => ['class' => 'is-progress', 'label' => 'Đang xử lý', 'icon' => $iconLoader],
        'da_xu_ly' => ['class' => 'is-done', 'label' => 'Đã xử lý', 'icon' => $iconCheck],
    ];
    $currentTrangThaiFilter = request('trang_thai', '');
    if (!isset($statusFilterOptions[$currentTrangThaiFilter])) {
        $currentTrangThaiFilter = '';
    }
@endphp

@section('content')
<div class="contact-admin-page">
    @include('admin.partials.flash')

    <section class="contact-admin-hero">
        <div class="contact-admin-hero-copy">
            <span class="contact-admin-hero-icon">{!! $iconInbox !!}</span>
            <div>
                <h2>Hộp thư liên hệ khách hàng</h2>
                <p>Theo dõi và xử lý các yêu cầu hỗ trợ, phản ánh lỗi được khách hàng gửi từ trang Liên hệ.</p>
            </div>
        </div>
    </section>

    <section class="contact-admin-stats">
        <article class="contact-admin-stat is-total">
            <span class="icon-wrap">{!! $iconInbox !!}</span>
            <div>
                <small>Tổng liên hệ</small>
                <strong>{{ number_format($thongKe['tong']) }}</strong>
            </div>
        </article>
        <article class="contact-admin-stat is-pending">
            <span class="icon-wrap">{!! $iconClock !!}</span>
            <div>
                <small>Chờ xử lý</small>
                <strong>{{ number_format($thongKe['cho_xu_ly']) }}</strong>
            </div>
        </article>
        <article class="contact-admin-stat is-progress">
            <span class="icon-wrap">{!! $iconLoader !!}</span>
            <div>
                <small>Đang xử lý</small>
                <strong>{{ number_format($thongKe['dang_xu_ly']) }}</strong>
            </div>
        </article>
        <article class="contact-admin-stat is-done">
            <span class="icon-wrap">{!! $iconCheck !!}</span>
            <div>
                <small>Đã xử lý</small>
                <strong>{{ number_format($thongKe['da_xu_ly']) }}</strong>
            </div>
        </article>
    </section>

    <section class="contact-admin-panel">
        <div class="contact-admin-panel-head">
            <div>
                <h3>Danh sách liên hệ</h3>
                <p>Đang hiển thị {{ $lienHes->count() }} / {{ $lienHes->total() }} liên hệ theo bộ lọc hiện tại.</p>
            </div>
        </div>

        <form method="GET" class="contact-admin-filter">
            <label class="contact-admin-search">
                {!! $iconSearch !!}
                <input type="text" name="tim_kiem" value="{{ request('tim_kiem') }}" placeholder="Tìm theo tên, email hoặc số điện thoại...">
            </label>

            <div class="contact-status-select" data-name="trang_thai" data-value="{{ $currentTrangThaiFilter }}">
                <input type="hidden" name="trang_thai" value="{{ $currentTrangThaiFilter }}">

                <button type="button" class="contact-status-select-trigger">
                    <span class="contact-status-select-current {{ $statusFilterOptions[$currentTrangThaiFilter]['class'] }}">
                        {!! $statusFilterOptions[$currentTrangThaiFilter]['icon'] !!}
                        <span class="label">{{ $statusFilterOptions[$currentTrangThaiFilter]['label'] }}</span>
                    </span>
                    {!! $iconChevron !!}
                </button>

                <div class="contact-status-select-menu hidden">
                    @foreach ($statusFilterOptions as $value => $meta)
                        <button type="button" class="contact-status-option {{ $meta['class'] }}" data-value="{{ $value }}">
                            {!! $meta['icon'] !!}
                            <span>{{ $meta['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="contact-admin-filter-btn">
                {!! $iconFilter !!}
                Lọc
            </button>

            @if(request('tim_kiem') || request('trang_thai'))
                <a href="{{ route('admin.lien-he.index') }}" class="contact-admin-reset-btn" title="Xóa bộ lọc">
                    {!! $iconReset !!}
                </a>
            @endif
        </form>

        <div class="contact-admin-table-wrap">
            <table class="contact-admin-table">
                <thead>
                    <tr>
                        <th>Người gửi</th>
                        <th>Liên hệ</th>
                        <th>Chủ đề & nội dung</th>
                        <th>Trạng thái</th>
                        <th>Ngày gửi</th>
                        <th class="is-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lienHes as $lienHe)
                        <tr>
                            <td>
                                <div class="contact-profile-cell">
                                    <span class="contact-avatar">{!! $iconUser !!}</span>
                                    <div>
                                        <strong>{{ $lienHe->ho_ten }}</strong>
                                        <small>{!! $iconCalendar !!} {{ $lienHe->created_at->format('d/m/Y') }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="contact-info-cell">
                                <strong>{{ $lienHe->email }}</strong>
                                @if($lienHe->so_dien_thoai)
                                    <small>{!! $iconPhone !!} {{ $lienHe->so_dien_thoai }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="contact-topic-badge">{{ $lienHe->chu_de }}</span>
                                <span class="contact-content-preview">{{ \Illuminate\Support\Str::limit($lienHe->noi_dung, 70) }}</span>
                            </td>
                            <td>
                                @switch($lienHe->trang_thai)
                                    @case('cho_xu_ly')
                                        <span class="contact-status-pill is-pending">{!! $iconClock !!} Chờ xử lý</span>
                                        @break
                                    @case('dang_xu_ly')
                                        <span class="contact-status-pill is-progress">{!! $iconLoader !!} Đang xử lý</span>
                                        @break
                                    @case('da_xu_ly')
                                        <span class="contact-status-pill is-done">{!! $iconCheck !!} Đã xử lý</span>
                                        @break
                                @endswitch
                            </td>
                            <td class="contact-date-cell">{{ $lienHe->created_at->format('H:i d/m/Y') }}</td>
                            <td class="is-right">
                                <div class="contact-actions">
                                    <a href="{{ route('admin.lien-he.show', $lienHe) }}" class="contact-action-btn is-view" title="Xem chi tiết">{!! $iconEye !!}</a>
                                    <form method="POST" action="{{ route('admin.lien-he.destroy', $lienHe) }}" onsubmit="return confirm('Xóa liên hệ này?')">
                                        @csrf @method('DELETE')
                                        <button class="contact-action-btn is-delete" type="submit" title="Xóa">{!! $iconTrash !!}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="contact-admin-empty">
                                    {!! $iconInboxEmpty !!}
                                    <strong>Chưa có liên hệ nào</strong>
                                    <span>Yêu cầu hỗ trợ từ khách hàng sẽ xuất hiện tại đây.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="contact-admin-pagination">
            {{ $lienHes->links() }}
        </div>
    </section>
</div>

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
