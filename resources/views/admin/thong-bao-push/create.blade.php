@extends('layouts.admin')

@section('page-title', 'Tạo thông báo mới')

@section('content')

@php
/* =========================================================
| ĐỐI TƯỢNG NHẬN
========================================================= */

$audienceGuide = [
    'all' => [
        'label' => 'Tất cả người dùng',
        'description' => 'Gửi đến toàn bộ người dùng',
        'icon' => 'fa-globe',
        'class' => 'is-all',
    ],

    'khach_hang' => [
        'label' => 'Khách hàng',
        'description' => 'Chỉ gửi đến tài khoản khách hàng',
        'icon' => 'fa-users',
        'class' => 'is-user',
    ],

    'nhan_vien' => [
        'label' => 'Nhân viên',
        'description' => 'Chỉ gửi đến nhân viên',
        'icon' => 'fa-user-tie',
        'class' => 'is-staff',
    ],

    'quan_ly' => [
        'label' => 'Quản lý',
        'description' => 'Chỉ gửi đến tài khoản quản lý',
        'icon' => 'fa-user-shield',
        'class' => 'is-manager',
    ],

    'hang_thanh_vien' => [
        'label' => 'Theo hạng thành viên',
        'description' => 'Gửi theo hạng thành viên đã chọn',
        'icon' => 'fa-ranking-star',
        'class' => 'is-member',
    ],

    'nguoi_dung_cu_the' => [
        'label' => 'Người dùng cụ thể',
        'description' => 'Chọn một hoặc nhiều người dùng',
        'icon' => 'fa-user-pen',
        'class' => 'is-specific',
    ],
];

$selectedAudience = old('doi_tuong_nhan', '');
$selectedLoai = old('loai', '');
$selectedHangThanhVien = old('hang_thanh_vien');

/*
|--------------------------------------------------------------------------
| Người dùng đã chọn khi validation fail
|--------------------------------------------------------------------------
*/

$oldSelectedUsers = old('nguoi_dung_cu_the', []);

if (!is_array($oldSelectedUsers)) {
    $oldSelectedUsers = $oldSelectedUsers
        ? [$oldSelectedUsers]
        : [];
}

/*
|--------------------------------------------------------------------------
| Icon + màu cho loại thông báo
|--------------------------------------------------------------------------
*/

$loaiGuide = [
    'info' => [
        'label' => $loaiOptions['info'] ?? 'Thông tin',
        'icon' => 'fa-circle-info',
        'class' => 'type-info',
    ],

    'warning' => [
        'label' => $loaiOptions['warning'] ?? 'Cảnh báo',
        'icon' => 'fa-triangle-exclamation',
        'class' => 'type-warning',
    ],

    'promo' => [
        'label' => $loaiOptions['promo'] ?? 'Khuyến mãi',
        'icon' => 'fa-tags',
        'class' => 'type-promo',
    ],

    'system' => [
        'label' => $loaiOptions['system'] ?? 'Hệ thống',
        'icon' => 'fa-gear',
        'class' => 'type-system',
    ],
];

/*
|--------------------------------------------------------------------------
| Người dùng cũ
|--------------------------------------------------------------------------
*/

$oldSelectedUsersData = $oldSelectedUsersData ?? [];

@endphp


{{-- =========================================================
    FORM
========================================================= --}}

<form
    action="{{ route('admin.thong-bao-push.store') }}"
    method="POST"
    id="pushNotificationForm">

    @csrf

    <div class="push-compose-layout">

        {{-- =====================================================
             CỘT TRÁI
        ====================================================== --}}

        <section class="push-panel push-compose-main">

            {{-- HEADER --}}

            <div class="push-panel-head">

                <div>

                    <span>Thông báo</span>

                    <h3>Tạo thông báo mới</h3>

                    <p>
                        Nhập nội dung và chọn đối tượng nhận thông báo.
                    </p>

                </div>

            </div>


            {{-- =================================================
                 FORM INPUT
            ================================================== --}}

            <div class="push-form-grid">


                {{-- =================================================
                     TIÊU ĐỀ
                ================================================== --}}

                <label class="push-field push-field--full">

                    <span>
                        Tiêu đề thông báo
                        <em>*</em>
                    </span>

                    <input
                        type="text"
                        name="tieu_de"
                        id="tieu_de"
                        value="{{ old('tieu_de') }}"
                        maxlength="255"
                        class="@error('tieu_de') is-invalid @enderror"
                        placeholder="VD: Voucher cuối tuần">

                    @error('tieu_de')
                        <small>{{ $message }}</small>
                    @enderror

                </label>


                {{-- =================================================
                     LOẠI THÔNG BÁO
                ================================================== --}}

                <div class="push-field push-field--full">

                    <span>
                        Loại thông báo
                        <em>*</em>
                    </span>

                    <div
                        class="push-custom-select @error('loai') is-invalid @enderror"
                        id="loaiCustomSelect">

                        <input
                            type="hidden"
                            name="loai"
                            id="loai"
                            value="{{ $selectedLoai }}">

                        <button
                            type="button"
                            class="push-custom-select-trigger"
                            id="loaiSelectTrigger">

                            <span class="push-custom-selected">

                                <span
                                    class="push-type-icon type-info"
                                    id="loaiSelectedIcon">

                                    <i class="fa-solid fa-bell"></i>

                                </span>

                                <span id="loaiSelectedText">
                                    {{ $selectedLoai && isset($loaiGuide[$selectedLoai])
                                        ? $loaiGuide[$selectedLoai]['label']
                                        : '-- Chọn loại thông báo --' }}
                                </span>

                            </span>

                            <i class="fa-solid fa-chevron-down push-select-arrow"></i>

                        </button>


                        {{-- OPTIONS --}}

                        <div
                            class="push-custom-options"
                            id="loaiCustomOptions">

                            <div class="push-custom-option-placeholder">
                                Chọn loại thông báo
                            </div>

                            @foreach ($loaiGuide as $value => $meta)

                                <button
                                    type="button"
                                    class="push-custom-option {{ $selectedLoai === $value ? 'is-selected' : '' }}"
                                    data-value="{{ $value }}"
                                    data-label="{{ $meta['label'] }}"
                                    data-icon="{{ $meta['icon'] }}"
                                    data-class="{{ $meta['class'] }}">

                                    <span class="push-type-icon {{ $meta['class'] }}">

                                        <i class="fa-solid {{ $meta['icon'] }}"></i>

                                    </span>

                                    <span class="push-custom-option-text">

                                        <strong>
                                            {{ $meta['label'] }}
                                        </strong>

                                        @if ($value === 'info')
                                            <small>Thông tin chung</small>
                                        @elseif ($value === 'warning')
                                            <small>Cảnh báo quan trọng</small>
                                        @elseif ($value === 'promo')
                                            <small>Ưu đãi và khuyến mãi</small>
                                        @elseif ($value === 'system')
                                            <small>Thông báo hệ thống</small>
                                        @endif

                                    </span>

                                    <i class="fa-solid fa-check push-option-check"></i>

                                </button>

                            @endforeach

                        </div>

                    </div>

                    @error('loai')
                        <small>{{ $message }}</small>
                    @enderror

                </div>


                {{-- =================================================
                     NỘI DUNG
                ================================================== --}}

                <label class="push-field push-field--full">

                    <span>
                        Nội dung thông báo
                        <em>*</em>
                    </span>

                    <textarea
                        name="noi_dung"
                        id="noi_dung"
                        rows="7"
                        maxlength="1000"
                        class="@error('noi_dung') is-invalid @enderror"
                        placeholder="Nhập nội dung thông báo...">{{ old('noi_dung') }}</textarea>

                    @error('noi_dung')
                        <small>{{ $message }}</small>
                    @enderror

                </label>


                {{-- =================================================
                     ĐỐI TƯỢNG NHẬN - CUSTOM DROPDOWN
                ================================================== --}}

                <div class="push-field push-field--full">

                    <span>
                        Đối tượng nhận
                        <em>*</em>
                    </span>

                    <div
                        class="push-custom-select push-audience-custom-select @error('doi_tuong_nhan') is-invalid @enderror"
                        id="audienceCustomSelect">

                        {{-- Giá trị thật gửi về server --}}
                        <input
                            type="hidden"
                            name="doi_tuong_nhan"
                            id="doi_tuong_nhan"
                            value="{{ $selectedAudience }}">


                        {{-- ĐANG CHỌN --}}
                        <button
                            type="button"
                            class="push-custom-select-trigger"
                            id="audienceSelectTrigger">

                            <span class="push-custom-selected">

                                <span
                                    class="push-type-icon audience-icon-is-all"
                                    id="audienceSelectedIcon">

                                    <i class="fa-solid fa-users"></i>

                                </span>

                                <span id="audienceSelectedText">

                                    {{ $selectedAudience && isset($audienceGuide[$selectedAudience])
                                        ? $audienceGuide[$selectedAudience]['label']
                                        : '-- Chọn đối tượng nhận --' }}

                                </span>

                            </span>

                            <i class="fa-solid fa-chevron-down push-select-arrow"></i>

                        </button>


                        {{-- =================================================
                             OPTIONS ĐỐI TƯỢNG
                        ================================================== --}}

                        <div
                            class="push-custom-options push-audience-options"
                            id="audienceCustomOptions">

                            <div class="push-custom-option-placeholder">
                                Chọn đối tượng nhận
                            </div>

                            @foreach ($audienceGuide as $value => $meta)

                                <button
                                    type="button"
                                    class="push-custom-option audience-custom-option {{ $selectedAudience === $value ? 'is-selected' : '' }}"
                                    data-value="{{ $value }}"
                                    data-label="{{ $meta['label'] }}"
                                    data-icon="{{ $meta['icon'] }}"
                                    data-class="{{ $meta['class'] }}">

                                    <span class="push-type-icon audience-option-icon {{ $meta['class'] }}">

                                        <i class="fa-solid {{ $meta['icon'] }}"></i>

                                    </span>

                                    <span class="push-custom-option-text">

                                        <strong>
                                            {{ $meta['label'] }}
                                        </strong>

                                        <small>
                                            {{ $meta['description'] }}
                                        </small>

                                    </span>

                                    <i class="fa-solid fa-check push-option-check"></i>

                                </button>

                            @endforeach

                        </div>

                    </div>

                    @error('doi_tuong_nhan')
                        <small>{{ $message }}</small>
                    @enderror

                </div>


                {{-- =================================================
                     HẠNG THÀNH VIÊN
                ================================================== --}}

                <div
                    id="hang_thanh_vien_wrapper"
                    class="push-specific-box push-field--full
                    {{ $selectedAudience === 'hang_thanh_vien' ? '' : 'is-hidden' }}">

                    <label class="push-field">

                        <span>
                            Hạng thành viên
                            <em>*</em>
                        </span>

                        <div
                            class="push-member-rank-select @error('hang_thanh_vien') is-invalid @enderror"
                            id="memberRankCustomSelect">

                            <input
                                type="hidden"
                                name="hang_thanh_vien"
                                id="hang_thanh_vien"
                                value="{{ $selectedHangThanhVien }}">

                            <button
                                type="button"
                                class="push-member-rank-trigger"
                                id="memberRankSelectTrigger">

                                <span class="push-member-rank-selected">
                                    <span
                                        class="push-member-rank-icon member-rank-default"
                                        id="memberRankSelectedIcon">
                                        <i class="fa-solid fa-layer-group"></i>
                                    </span>

                                    <span id="memberRankSelectedText">
                                        @switch($selectedHangThanhVien)
                                            @case('member') Member @break
                                            @case('silver') Silver @break
                                            @case('gold') Gold @break
                                            @case('platinum') Platinum @break
                                            @default -- Chọn hạng thành viên --
                                        @endswitch
                                    </span>
                                </span>

                                <i class="fa-solid fa-chevron-down push-member-rank-arrow"></i>
                            </button>

                            <div class="push-member-rank-options" id="memberRankCustomOptions">

                                <div class="push-member-rank-placeholder">
                                    Chọn hạng thành viên
                                </div>

                                @php
                                    $memberRankGuide = [
                                        'member' => ['label' => 'Member', 'desc' => 'Hạng thành viên cơ bản', 'icon' => 'fa-user', 'class' => 'member-rank-member'],
                                        'silver' => ['label' => 'Silver', 'desc' => 'Hạng bạc', 'icon' => 'fa-medal', 'class' => 'member-rank-silver'],
                                        'gold' => ['label' => 'Gold', 'desc' => 'Hạng vàng', 'icon' => 'fa-medal', 'class' => 'member-rank-gold'],
                                        'platinum' => ['label' => 'Platinum', 'desc' => 'Hạng bạch kim', 'icon' => 'fa-crown', 'class' => 'member-rank-platinum'],
                                    ];
                                @endphp

                                @foreach ($memberRankGuide as $value => $meta)
                                    <button
                                        type="button"
                                        class="push-member-rank-option {{ $selectedHangThanhVien === $value ? 'is-selected' : '' }}"
                                        data-value="{{ $value }}"
                                        data-label="{{ $meta['label'] }}"
                                        data-icon="{{ $meta['icon'] }}"
                                        data-class="{{ $meta['class'] }}">

                                        <span class="push-member-rank-icon {{ $meta['class'] }}">
                                            <i class="fa-solid {{ $meta['icon'] }}"></i>
                                        </span>

                                        <span class="push-member-rank-option-text">
                                            <strong>{{ $meta['label'] }}</strong>
                                            <small>{{ $meta['desc'] }}</small>
                                        </span>

                                        <i class="fa-solid fa-check push-member-rank-check"></i>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        @error('hang_thanh_vien')
                            <small>{{ $message }}</small>
                        @enderror

                    </label>

                </div>


                {{-- =================================================
                     NGƯỜI DÙNG CỤ THỂ
                ================================================== --}}

                <div
                    id="nguoi_dung_cu_the_wrapper"
                    class="push-specific-box push-field--full
                    {{ $selectedAudience === 'nguoi_dung_cu_the' ? '' : 'is-hidden' }}">

                    {{-- TÌM KIẾM --}}

                    <label class="push-field">

                        <span>
                            Tìm người dùng
                            <em>*</em>
                        </span>

                        <div class="push-user-search">

                            <i class="fa-solid fa-magnifying-glass"></i>

                            <input
                                type="text"
                                id="tim_nguoi_dung"
                                placeholder="Nhập tên hoặc email để tìm..."
                                autocomplete="off">

                        </div>

                        @error('nguoi_dung_cu_the')
                            <small>{{ $message }}</small>
                        @enderror

                    </label>


                    {{-- KẾT QUẢ TÌM KIẾM --}}

                    <div
                        id="ket_qua_nguoi_dung"
                        class="push-user-results is-hidden">
                    </div>


                    {{-- NGƯỜI DÙNG ĐÃ CHỌN --}}

                    <div
                        id="nguoi_dung_da_chon"
                        class="push-selected-users">
                    </div>


                    {{-- INPUT HIDDEN --}}

                    <div id="selectedUsersInputs"></div>


                    <p class="push-specific-help">

                        <i class="fa-solid fa-circle-info"></i>

                        Bạn có thể chọn nhiều người dùng cùng lúc.

                    </p>

                </div>

            </div>


            {{-- =================================================
                 BUTTON
            ================================================== --}}

            <div class="push-form-actions">

                <a
                    href="{{ route('admin.thong-bao-push.index') }}"
                    class="push-soft-btn">

                    <i class="fa-solid fa-xmark"></i>

                    Hủy

                </a>


                <button
                    type="submit"
                    name="action"
                    value="draft"
                    class="push-secondary-btn">

                    <i class="fa-solid fa-file-pen"></i>

                    Lưu nháp

                </button>


                <button
                    type="submit"
                    name="action"
                    value="send"
                    class="push-primary-btn"
                    id="submitPush">

                    <i class="fa-solid fa-paper-plane"></i>

                    Gửi thông báo

                </button>

            </div>

        </section>


        {{-- =====================================================
             CỘT PHẢI
        ====================================================== --}}

        <aside class="push-compose-side">


            {{-- =================================================
                 PREVIEW
            ================================================== --}}

            <section class="push-phone-preview">

                <div class="push-phone-top">

                    <span>CineHome</span>

                    <small>vừa xong</small>

                </div>


                <div class="push-preview-card">

                    <span
                        class="push-preview-icon"
                        id="previewTypeIcon">

                        <i class="fa-solid fa-bell"></i>

                    </span>


                    <div>

                        <strong id="previewTitle">
                            Tiêu đề thông báo của bạn
                        </strong>


                        <p id="previewContent">
                            Nội dung thông báo sẽ hiển thị tại đây.
                        </p>


                        <span class="push-chip is-info">

                            <i
                                class="fa-solid fa-bell"
                                id="previewChipIcon">
                            </i>

                            <span id="previewType">
                                Thông báo
                            </span>

                        </span>

                    </div>

                </div>

            </section>


            {{-- =================================================
                 THỐNG KÊ NGƯỜI NHẬN
            ================================================== --}}

            <section class="push-panel push-audience-panel">

                <div class="push-panel-head">

                    <div>

                        <span>Người nhận</span>

                        <h3>Đối tượng gửi</h3>

                    </div>

                </div>


                <div class="push-audience-list">

                    @foreach ($audienceGuide as $value => $meta)

                        @php

                            if ($value === 'nguoi_dung_cu_the') {

                                $count =
                                    $audienceCounts['nguoi_dung_cu_the']
                                    ?? $audienceCounts['all']
                                    ?? 0;

                            } else {

                                $count =
                                    $audienceCounts[$value]
                                    ?? 0;

                            }

                        @endphp


                        <article
                            class="{{ $selectedAudience === $value ? 'is-active' : '' }}"
                            data-audience-card="{{ $value }}">

                            <span class="push-chip {{ $meta['class'] }}">

                                <i class="fa-solid {{ $meta['icon'] }}"></i>

                                {{ $meta['label'] }}

                            </span>


                            <strong>
                                {{ number_format($count) }}
                            </strong>

                        </article>

                    @endforeach

                </div>

            </section>

        </aside>

    </div>

</form>


{{-- =========================================================
    CSS
========================================================= --}}

@push('styles')

<style>

/* =========================================================
   LAYOUT
========================================================= */

.push-compose-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 340px;
    gap: 24px;
    align-items: start;
}

.push-compose-main {
    min-width: 0;
}

.push-compose-side {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.push-panel {
    background: rgba(15, 18, 25, .95);
    border: 1px solid rgba(255, 255, 255, .08);
    border-radius: 24px;
    overflow: hidden;
}

.push-panel-head {
    padding: 24px 26px;
    border-bottom: 1px solid rgba(255, 255, 255, .07);
}

.push-panel-head span {
    display: block;
    color: #f5a623;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
    margin-bottom: 6px;
}

.push-panel-head h3 {
    margin: 0;
    color: white;
    font-size: 21px;
    font-weight: 800;
}

.push-panel-head p {
    margin: 6px 0 0;
    color: #8f96a3;
    font-size: 13px;
}


/* =========================================================
   FORM
========================================================= */

.push-form-grid {
    padding: 26px;
    display: grid;
    grid-template-columns: 1fr;
    gap: 22px;
}

.push-field {
    display: flex;
    flex-direction: column;
    gap: 9px;
    min-width: 0;
}

.push-field > span {
    color: #d8dbe2;
    font-size: 13px;
    font-weight: 800;
}

.push-field em {
    color: #ff3b4a;
    font-style: normal;
}

.push-field input,
.push-field textarea,
.push-field select {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid rgba(255, 255, 255, .10);
    background: #20232a;
    color: white;
    border-radius: 14px;
    outline: none;
    transition: .2s;
    font-size: 14px;
}

.push-field input,
.push-field select {
    height: 46px;
    padding: 0 15px;
}

.push-field textarea {
    padding: 14px 15px;
    resize: vertical;
    min-height: 150px;
}

.push-field input:focus,
.push-field textarea:focus,
.push-field select:focus {
    border-color: #ff3045;
    box-shadow: 0 0 0 3px rgba(255, 48, 69, .10);
}

.push-field input::placeholder,
.push-field textarea::placeholder {
    color: #777d89;
}

.push-field select option {
    background: #20232a;
    color: white;
}

.push-field input.is-invalid,
.push-field textarea.is-invalid,
.push-field select.is-invalid,
.push-custom-select.is-invalid .push-custom-select-trigger {
    border-color: #ff3045;
}

.push-field small {
    color: #ff6570;
    font-size: 12px;
}


/* =========================================================
   CUSTOM DROPDOWN
========================================================= */

.push-custom-select {
    position: relative;
    width: 100%;
    z-index: 20;
}

.push-custom-select.is-open-up {
    z-index: 1005;
}

.push-custom-select-trigger {
    width: 100%;
    height: 48px;
    padding: 0 15px;
    border: 1px solid rgba(255,255,255,.10);
    border-radius: 14px;
    background: #20232a;
    color: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    transition: .2s;
}

.push-custom-select-trigger:hover {
    border-color: rgba(255,255,255,.18);
}

.push-custom-select.open .push-custom-select-trigger {
    border-color: #ff3045;
    box-shadow: 0 0 0 3px rgba(255,48,69,.10);
}

.push-custom-selected {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}

.push-custom-selected > span:last-child {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.push-select-arrow {
    color: #8e95a2;
    font-size: 12px;
    transition: .2s;
}

.push-custom-select.open .push-select-arrow {
    transform: rotate(180deg);
    color: #ff5361;
}


/* =========================================================
   DROPDOWN OPTIONS
========================================================= */

.push-custom-options {
    position: absolute;
    z-index: 1000;
    top: calc(100% + 8px);
    left: 0;
    right: 0;

    padding: 7px;

    background: #181a20;
    border: 1px solid rgba(255,255,255,.10);
    border-radius: 16px;

    box-shadow: 0 18px 45px rgba(0,0,0,.42);

    opacity: 0;
    visibility: hidden;

    transform: translateY(-6px);

    transition:
        opacity .18s ease,
        visibility .18s ease,
        transform .18s ease;
}


/* MỞ XUỐNG */

.push-custom-select.open:not(.is-open-up) .push-custom-options {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}


/* MỞ LÊN */

.push-custom-select.is-open-up .push-custom-options {
    top: auto;
    bottom: calc(100% + 8px);

    transform: translateY(6px);
}

.push-custom-select.is-open-up.open .push-custom-options {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}


.push-custom-option-placeholder {
    padding: 8px 10px 10px;
    color: #707784;
    font-size: 11px;
    font-weight: 700;
}

.push-custom-option {
    width: 100%;
    min-height: 54px;
    padding: 8px 10px;

    display: flex;
    align-items: center;
    gap: 11px;

    border: 0;
    border-radius: 11px;

    background: transparent;
    color: white;

    text-align: left;
    cursor: pointer;

    transition: .16s;
}

.push-custom-option:hover {
    background: rgba(255,255,255,.06);
}

.push-custom-option.is-selected {
    background: rgba(255,48,69,.08);
}

.push-custom-option-text {
    display: flex;
    flex-direction: column;
    gap: 3px;
    min-width: 0;
    flex: 1;
}

.push-custom-option-text strong {
    color: #e9ebef;
    font-size: 13px;
}

.push-custom-option-text small {
    color: #777f8d;
    font-size: 11px;
}

.push-option-check {
    color: transparent;
    font-size: 12px;
}

.push-custom-option.is-selected .push-option-check {
    color: #ff4052;
}


/* =========================================================
   ICON LOẠI THÔNG BÁO
========================================================= */

.push-type-icon {
    width: 36px;
    height: 36px;
    flex: 0 0 36px;

    border-radius: 11px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    font-size: 13px;
}

.push-type-icon.type-info {
    background: rgba(59,130,246,.13);
    color: #60a5fa;
}

.push-type-icon.type-warning {
    background: rgba(245,166,35,.13);
    color: #f5a623;
}

.push-type-icon.type-promo {
    background: rgba(236,72,153,.13);
    color: #f472b6;
}

.push-type-icon.type-system {
    background: rgba(168,85,247,.13);
    color: #c084fc;
}


/* =========================================================
   ICON ĐỐI TƯỢNG NHẬN
========================================================= */

.push-type-icon.is-all,
.push-chip.is-all {
    background: rgba(245,166,35,.12);
    color: #f5a623;
}

.push-type-icon.is-user,
.push-chip.is-user {
    background: rgba(34,197,94,.12);
    color: #4ade80;
}

.push-type-icon.is-staff,
.push-chip.is-staff {
    background: rgba(59,130,246,.12);
    color: #60a5fa;
}

.push-type-icon.is-manager,
.push-chip.is-manager {
    background: rgba(168,85,247,.12);
    color: #c084fc;
}

.push-type-icon.is-member,
.push-chip.is-member {
    background: rgba(14,165,233,.12);
    color: #38bdf8;
}

.push-type-icon.is-specific,
.push-chip.is-specific {
    background: rgba(236,72,153,.12);
    color: #f472b6;
}


/* =========================================================
   BOX CỤ THỂ
========================================================= */

.push-specific-box {
    background: rgba(37, 29, 25, .8);
    border: 1px solid rgba(245, 166, 35, .28);
    border-radius: 18px;
    padding: 18px;
}

.push-specific-box.is-hidden {
    display: none;
}


/* =========================================================
   SEARCH USER
========================================================= */

.push-user-search {
    position: relative;
}

.push-user-search i {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #858b98;
    pointer-events: none;
}

.push-user-search input {
    padding-left: 44px !important;
}

.push-user-results {
    margin-top: 8px;
    max-height: 260px;
    overflow-y: auto;

    border-radius: 14px;
    background: #181a20;
    border: 1px solid rgba(255,255,255,.08);
}

.push-user-results.is-hidden {
    display: none;
}


/* =========================================================
   RESULT USER
========================================================= */

.push-user-result {
    width: 100%;

    display: flex;
    align-items: center;
    gap: 12px;

    padding: 12px 14px;

    cursor: pointer;
    transition: .15s;

    border: 0;
    border-bottom: 1px solid rgba(255,255,255,.05);

    background: transparent;
    color: white;

    text-align: left;
}

.push-user-result:last-child {
    border-bottom: 0;
}

.push-user-result:hover {
    background: rgba(255,255,255,.06);
}

.push-user-result.is-already-selected {
    opacity: .45;
    cursor: not-allowed;
}

.push-user-result-icon {
    width: 38px;
    height: 38px;
    flex: 0 0 38px;

    border-radius: 50%;

    display: flex;
    align-items: center;
    justify-content: center;

    background: linear-gradient(135deg, #ff3045, #f5a623);
    color: white;

    font-size: 13px;
}

.push-user-result-info {
    min-width: 0;
    flex: 1;
}

.push-user-result-info strong {
    display: block;
    color: white;
    font-size: 13px;
    margin-bottom: 3px;
}

.push-user-result-info small {
    display: block;
    color: #8e95a2;
    font-size: 12px;
}

.push-user-result-check {
    color: #4ade80;
    font-size: 13px;
}

.push-no-result {
    padding: 18px;
    text-align: center;
    color: #777d89;
    font-size: 13px;
}


/* =========================================================
   USER ĐÃ CHỌN
========================================================= */

.push-selected-users {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 12px;
}

.push-selected-user {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;

    padding: 11px 12px;

    border-radius: 14px;

    background: rgba(255,48,69,.08);
    border: 1px solid rgba(255,48,69,.20);

    animation: pushUserIn .18s ease;
}

@keyframes pushUserIn {

    from {
        opacity: 0;
        transform: translateY(-4px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }

}

.push-selected-user-info {
    display: flex;
    align-items: center;
    gap: 11px;
    min-width: 0;
}

.push-selected-user-icon {
    width: 38px;
    height: 38px;
    flex: 0 0 38px;

    border-radius: 50%;

    display: flex;
    align-items: center;
    justify-content: center;

    background: linear-gradient(135deg, #ff3045, #e51f36);
    color: white;

    font-size: 13px;
}

.push-selected-user-text {
    min-width: 0;
}

.push-selected-user-text strong {
    display: block;
    color: white;
    font-size: 13px;

    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.push-selected-user-text small {
    display: block;
    color: #9096a3;
    margin-top: 3px;
    font-size: 11px;

    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.push-remove-user {
    width: 32px;
    height: 32px;
    flex: 0 0 32px;

    border: 0;
    border-radius: 9px;

    background: rgba(255,255,255,.07);
    color: #9ba1ad;

    cursor: pointer;
    transition: .16s;
}

.push-remove-user:hover {
    background: rgba(255,48,69,.16);
    color: #ff5361;
}


/* =========================================================
   HELP
========================================================= */

.push-specific-help {
    margin: 12px 0 0;
    color: #999faa;
    font-size: 12px;
}

.push-specific-help i {
    color: #f5a623;
    margin-right: 5px;
}


/* =========================================================
   BUTTON
========================================================= */

.push-form-actions {
    padding: 20px 26px;

    display: flex;
    justify-content: flex-end;
    gap: 10px;

    border-top: 1px solid rgba(255,255,255,.07);

    flex-wrap: wrap;
}

.push-soft-btn,
.push-secondary-btn,
.push-primary-btn {
    min-height: 44px;
    padding: 0 20px;

    border-radius: 13px;

    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;

    font-size: 13px;
    font-weight: 800;

    text-decoration: none;

    border: 0;

    cursor: pointer;
    transition: .18s;
}

.push-soft-btn {
    background: rgba(255,255,255,.07);
    color: #d7dae0;
}

.push-soft-btn:hover {
    background: rgba(255,255,255,.11);
    color: white;
}

.push-secondary-btn {
    background: rgba(255,255,255,.07);
    color: #d7dae0;
}

.push-secondary-btn:hover {
    background: rgba(255,255,255,.12);
    color: white;
}

.push-primary-btn {
    background: linear-gradient(135deg,#ff3045,#e51f36);
    color: white;
    box-shadow: 0 10px 25px rgba(255,48,69,.18);
}

.push-primary-btn:hover {
    transform: translateY(-1px);
}


/* =========================================================
   PREVIEW
========================================================= */

.push-phone-preview {
    background: #11141a;
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 24px;
    padding: 18px;
}

.push-phone-top {
    display: flex;
    justify-content: space-between;
    align-items: center;

    color: #dfe2e8;
    font-size: 13px;

    margin-bottom: 15px;
}

.push-phone-top small {
    color: #777e8b;
}

.push-preview-card {
    display: flex;
    gap: 12px;

    padding: 16px;

    background: #20232a;
    border-radius: 18px;

    border: 1px solid rgba(255,255,255,.08);
}

.push-preview-icon {
    width: 38px;
    height: 38px;
    flex: 0 0 38px;

    border-radius: 12px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #ff3045;
    color: white;
}

.push-preview-card strong {
    color: white;
    font-size: 13px;
    line-height: 1.4;
}

.push-preview-card p {
    color: #999faa;
    font-size: 12px;
    line-height: 1.5;

    margin: 5px 0 10px;

    word-break: break-word;
}


/* =========================================================
   CHIP
========================================================= */

.push-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;

    padding: 6px 9px;

    border-radius: 999px;

    font-size: 10px;
    font-weight: 800;
}

.push-chip.is-info {
    background: rgba(59,130,246,.12);
    color: #72a8ff;
}


/* =========================================================
   AUDIENCE
========================================================= */

.push-audience-list {
    padding: 16px;

    display: flex;
    flex-direction: column;
    gap: 8px;
}

.push-audience-list article {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;

    padding: 12px;

    border-radius: 14px;
    border: 1px solid transparent;

    background: rgba(255,255,255,.025);

    transition: .2s;
}

.push-audience-list article.is-active {
    background: rgba(255,48,69,.07);
    border-color: rgba(255,48,69,.2);
}

.push-audience-list article strong {
    color: white;
    font-size: 14px;
}


/* =========================================================
   ERROR
========================================================= */

.push-error-box {
    margin-bottom: 20px;

    padding: 16px 18px;

    border-radius: 15px;

    background: rgba(255,48,69,.08);
    border: 1px solid rgba(255,48,69,.25);

    color: #ff858e;
}

.push-error-title {
    color: #ff5d69;
    font-weight: 800;
    margin-bottom: 8px;
}

.push-error-box ul {
    margin: 5px 0 0 20px;
    padding: 0;

    font-size: 13px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1100px) {

    .push-compose-layout {
        grid-template-columns: 1fr;
    }

    .push-compose-side {
        display: grid;
        grid-template-columns:
            minmax(0, 1fr)
            minmax(0, 1fr);
    }

}

@media (max-width: 760px) {

    .push-compose-side {
        grid-template-columns: 1fr;
    }

    .push-form-grid {
        padding: 18px;
    }

    .push-panel-head {
        padding: 20px;
    }

    .push-form-actions {
        padding: 18px;
        flex-direction: column-reverse;
    }

    .push-soft-btn,
    .push-secondary-btn,
    .push-primary-btn {
        width: 100%;
    }

}

@media (max-width: 480px) {

    .push-compose-layout {
        gap: 14px;
    }

    .push-panel,
    .push-phone-preview {
        border-radius: 18px;
    }

    .push-specific-box {
        padding: 13px;
    }

    .push-preview-card {
        padding: 13px;
    }

}



/* =========================================================
   HẠNG THÀNH VIÊN - CUSTOM DROPDOWN
========================================================= */
.push-member-rank-select{position:relative;width:100%;z-index:25}
.push-member-rank-trigger{width:100%;height:48px;padding:0 15px;box-sizing:border-box;border:1px solid rgba(255,255,255,.10);border-radius:14px;background:#20232a;color:#fff;display:flex;align-items:center;justify-content:space-between;cursor:pointer;transition:.2s}
.push-member-rank-trigger:hover{border-color:rgba(255,255,255,.18)}
.push-member-rank-select.is-open .push-member-rank-trigger{border-color:#ff3045;box-shadow:0 0 0 3px rgba(255,48,69,.10)}
.push-member-rank-selected{display:flex;align-items:center;gap:10px;min-width:0}
.push-member-rank-selected>span:last-child{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.push-member-rank-icon{width:36px;height:36px;min-width:36px;display:inline-flex;align-items:center;justify-content:center;border-radius:11px;font-size:13px}
.member-rank-default{background:rgba(148,163,184,.12);color:#94a3b8}
.member-rank-member{background:rgba(96,165,250,.13);color:#60a5fa}
.member-rank-silver{background:rgba(203,213,225,.13);color:#cbd5e1}
.member-rank-gold{background:rgba(250,204,21,.13);color:#facc15}
.member-rank-platinum{background:rgba(232,121,249,.13);color:#e879f9}
.push-member-rank-arrow{color:#8e95a2;font-size:12px;transition:.2s}
.push-member-rank-select.is-open .push-member-rank-arrow{transform:rotate(180deg);color:#ff5361}
/* =========================================================
   HẠNG THÀNH VIÊN - LUÔN MỞ LÊN TRÊN
========================================================= */

.push-member-rank-select {
    position: relative;
    width: 100%;
    z-index: 25;
}

.push-member-rank-trigger {
    width: 100%;
    height: 48px;
    padding: 0 15px;
    box-sizing: border-box;

    border: 1px solid rgba(255,255,255,.10);
    border-radius: 14px;

    background: #20232a;
    color: #fff;

    display: flex;
    align-items: center;
    justify-content: space-between;

    cursor: pointer;
    transition: .2s;
}

.push-member-rank-trigger:hover {
    border-color: rgba(255,255,255,.18);
}

.push-member-rank-select.is-open .push-member-rank-trigger {
    border-color: #ff3045;
    box-shadow: 0 0 0 3px rgba(255,48,69,.10);
}

.push-member-rank-selected {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}

.push-member-rank-selected > span:last-child {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.push-member-rank-icon {
    width: 36px;
    height: 36px;
    min-width: 36px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    border-radius: 11px;
    font-size: 13px;
}

.member-rank-default {
    background: rgba(148,163,184,.12);
    color: #94a3b8;
}

.member-rank-member {
    background: rgba(96,165,250,.13);
    color: #60a5fa;
}

.member-rank-silver {
    background: rgba(203,213,225,.13);
    color: #cbd5e1;
}

.member-rank-gold {
    background: rgba(250,204,21,.13);
    color: #facc15;
}

.member-rank-platinum {
    background: rgba(232,121,249,.13);
    color: #e879f9;
}

.push-member-rank-arrow {
    color: #8e95a2;
    font-size: 12px;
    transition: .2s;
}

.push-member-rank-select.is-open .push-member-rank-arrow {
    transform: rotate(180deg);
    color: #ff5361;
}


/* =========================================================
   QUAN TRỌNG:
   DROPDOWN HẠNG THÀNH VIÊN LUÔN XỔ LÊN TRÊN
========================================================= */

.push-member-rank-options {
    position: absolute;

    /* Không dùng top nữa */
    top: auto;

    /* Luôn nằm phía trên ô chọn */
    bottom: calc(100% + 8px);

    left: 0;
    right: 0;

    padding: 7px;

    background: #181a20;

    border: 1px solid rgba(255,255,255,.10);
    border-radius: 16px;

    box-shadow: 0 18px 45px rgba(0,0,0,.42);

    z-index: 1000;

    opacity: 0;
    visibility: hidden;

    /* Animation từ dưới lên */
    transform: translateY(6px);

    transition:
        opacity .18s ease,
        visibility .18s ease,
        transform .18s ease;
}

.push-member-rank-select.is-open .push-member-rank-options {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.push-member-rank-placeholder {
    padding: 8px 10px 10px;

    color: #707784;

    font-size: 11px;
    font-weight: 700;
}

.push-member-rank-option {
    width: 100%;
    min-height: 54px;

    padding: 8px 10px;

    display: flex;
    align-items: center;
    gap: 11px;

    border: 0;
    border-radius: 11px;

    background: transparent;
    color: #fff;

    text-align: left;
    cursor: pointer;

    transition: .16s;
}

.push-member-rank-option:hover {
    background: rgba(255,255,255,.06);
}

.push-member-rank-option.is-selected {
    background: rgba(255,48,69,.08);
}

.push-member-rank-option-text {
    display: flex;
    flex-direction: column;
    gap: 3px;

    min-width: 0;
    flex: 1;
}

.push-member-rank-option-text strong {
    color: #e9ebef;
    font-size: 13px;
}

.push-member-rank-option-text small {
    color: #777f8d;
    font-size: 11px;
}

.push-member-rank-check {
    color: transparent;
    font-size: 12px;
}

.push-member-rank-option.is-selected .push-member-rank-check {
    color: #ff4052;
}

.push-member-rank-select.is-invalid .push-member-rank-trigger {
    border-color: #ff3045;
}

@media(max-width:760px) {

    .push-member-rank-trigger {
        height: 46px;
    }

    .push-member-rank-icon {
        width: 34px;
        height: 34px;
        min-width: 34px;
    }

}
.push-member-rank-select.is-open .push-member-rank-options{opacity:1;visibility:visible;transform:translateY(0)}
.push-member-rank-placeholder{padding:8px 10px 10px;color:#707784;font-size:11px;font-weight:700}
.push-member-rank-option{width:100%;min-height:54px;padding:8px 10px;display:flex;align-items:center;gap:11px;border:0;border-radius:11px;background:transparent;color:#fff;text-align:left;cursor:pointer;transition:.16s}
.push-member-rank-option:hover{background:rgba(255,255,255,.06)}
.push-member-rank-option.is-selected{background:rgba(255,48,69,.08)}
.push-member-rank-option-text{display:flex;flex-direction:column;gap:3px;min-width:0;flex:1}
.push-member-rank-option-text strong{color:#e9ebef;font-size:13px}
.push-member-rank-option-text small{color:#777f8d;font-size:11px}
.push-member-rank-check{color:transparent;font-size:12px}
.push-member-rank-option.is-selected .push-member-rank-check{color:#ff4052}
.push-member-rank-select.is-invalid .push-member-rank-trigger{border-color:#ff3045}
@media(max-width:760px){.push-member-rank-trigger{height:46px}.push-member-rank-icon{width:34px;height:34px;min-width:34px}}
</style>

@endpush


{{-- =========================================================
    JAVASCRIPT
========================================================= --}}

@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {


    /* =========================================================
       ELEMENT
    ========================================================= */

    const form =
        document.getElementById('pushNotificationForm');

    const audienceSelect =
        document.getElementById('doi_tuong_nhan');

    const audienceCustomSelect =
        document.getElementById('audienceCustomSelect');

    const audienceTrigger =
        document.getElementById('audienceSelectTrigger');

    const audienceOptions =
        document.querySelectorAll('.audience-custom-option');

    const audienceSelectedIcon =
        document.getElementById('audienceSelectedIcon');

    const audienceSelectedText =
        document.getElementById('audienceSelectedText');


    const rankWrapper =
        document.getElementById('hang_thanh_vien_wrapper');

    const rankSelect =
        document.getElementById('hang_thanh_vien');

    const rankCustomSelect =
        document.getElementById('memberRankCustomSelect');

    const rankTrigger =
        document.getElementById('memberRankSelectTrigger');

    const rankOptions =
        document.querySelectorAll('.push-member-rank-option');

    const rankSelectedIcon =
        document.getElementById('memberRankSelectedIcon');

    const rankSelectedText =
        document.getElementById('memberRankSelectedText');


    const specificWrapper =
        document.getElementById('nguoi_dung_cu_the_wrapper');

    const searchInput =
        document.getElementById('tim_nguoi_dung');

    const resultBox =
        document.getElementById('ket_qua_nguoi_dung');

    const selectedUsersBox =
        document.getElementById('nguoi_dung_da_chon');

    const selectedUsersInputs =
        document.getElementById('selectedUsersInputs');


    /* =========================================================
       CUSTOM DROPDOWN LOẠI
    ========================================================= */

    const loaiSelect =
        document.getElementById('loaiCustomSelect');

    const loaiTrigger =
        document.getElementById('loaiSelectTrigger');

    const loaiInput =
        document.getElementById('loai');

    const loaiOptions =
        document.querySelectorAll('.push-custom-option:not(.audience-custom-option)');

    const loaiSelectedIcon =
        document.getElementById('loaiSelectedIcon');

    const loaiSelectedText =
        document.getElementById('loaiSelectedText');


    const previewType =
        document.getElementById('previewType');

    const previewTypeIcon =
        document.getElementById('previewTypeIcon');

    const previewChipIcon =
        document.getElementById('previewChipIcon');


    const loaiIconMap = {

        info: 'fa-circle-info',
        warning: 'fa-triangle-exclamation',
        promo: 'fa-tags',
        system: 'fa-gear'

    };


    const loaiLabelMap = {

        info: 'Thông tin',
        warning: 'Cảnh báo',
        promo: 'Khuyến mãi',
        system: 'Hệ thống'

    };


    const loaiClassMap = {

        info: 'type-info',
        warning: 'type-warning',
        promo: 'type-promo',
        system: 'type-system'

    };


    /* =========================================================
       DATA ĐỐI TƯỢNG
    ========================================================= */

    const audienceIconMap = {

        all: 'fa-globe',

        khach_hang: 'fa-users',

        nhan_vien: 'fa-user-tie',

        quan_ly: 'fa-user-shield',

        hang_thanh_vien: 'fa-ranking-star',

        nguoi_dung_cu_the: 'fa-user-pen'

    };


    const audienceLabelMap = {

        all: 'Tất cả người dùng',

        khach_hang: 'Khách hàng',

        nhan_vien: 'Nhân viên',

        quan_ly: 'Quản lý',

        hang_thanh_vien: 'Theo hạng thành viên',

        nguoi_dung_cu_the: 'Người dùng cụ thể'

    };


    const audienceClassMap = {

        all: 'is-all',

        khach_hang: 'is-user',

        nhan_vien: 'is-staff',

        quan_ly: 'is-manager',

        hang_thanh_vien: 'is-member',

        nguoi_dung_cu_the: 'is-specific'

    };


    /* =========================================================
       AUTO POSITION DROPDOWN
    ========================================================= */

    function positionDropdown(dropdown) {

        if (!dropdown) {
            return;
        }

        const trigger =
            dropdown.querySelector('.push-custom-select-trigger');

        const options =
            dropdown.querySelector('.push-custom-options');

        if (!trigger || !options) {
            return;
        }


        dropdown.classList.remove('is-open-up');


        /*
        | Nếu đang mở thì mới cần tính vị trí
        */

        if (!dropdown.classList.contains('open')) {
            return;
        }


        const rect =
            trigger.getBoundingClientRect();


        const viewportHeight =
            window.innerHeight;


        const spaceBelow =
            viewportHeight - rect.bottom;


        const spaceAbove =
            rect.top;


        /*
        | Chiều cao dropdown dự kiến.
        | Nếu lớn hơn thì CSS vẫn tự scroll.
        */

        const estimatedHeight =
            Math.min(
                options.scrollHeight || 300,
                360
            );


        /*
        | Nếu phía dưới không đủ chỗ
        | nhưng phía trên nhiều chỗ hơn
        | => mở lên
        */

        if (
            spaceBelow < estimatedHeight &&
            spaceAbove > spaceBelow
        ) {

            dropdown.classList.add('is-open-up');

        } else {

            dropdown.classList.remove('is-open-up');

        }

    }


    function openDropdown(dropdown) {

        if (!dropdown) {
            return;
        }

        /*
        | Đóng dropdown khác
        */

        document
            .querySelectorAll('.push-custom-select.open')
            .forEach(function (item) {

                if (item !== dropdown) {

                    item.classList.remove('open');
                    item.classList.remove('is-open-up');

                }

            });


        dropdown.classList.add('open');


        /*
        | Cho browser render xong
        | rồi mới tính vị trí
        */

        requestAnimationFrame(function () {

            positionDropdown(dropdown);

        });

    }


    function closeDropdown(dropdown) {

        if (!dropdown) {
            return;
        }

        dropdown.classList.remove('open');
        dropdown.classList.remove('is-open-up');

    }


    /* =========================================================
       DROPDOWN LOẠI
    ========================================================= */

    if (loaiTrigger) {

        loaiTrigger.addEventListener(
            'click',
            function (event) {

                event.stopPropagation();

                if (
                    loaiSelect.classList.contains('open')
                ) {

                    closeDropdown(loaiSelect);

                } else {

                    openDropdown(loaiSelect);

                }

            }
        );

    }


    loaiOptions.forEach(function (option) {

        option.addEventListener(
            'click',
            function () {

                const value =
                    this.dataset.value;

                const label =
                    this.dataset.label;

                const icon =
                    this.dataset.icon;

                const colorClass =
                    this.dataset.class;


                updateLoai(
                    value,
                    label,
                    icon,
                    colorClass
                );


                closeDropdown(loaiSelect);

            }
        );

    });


    /* =========================================================
       UPDATE LOẠI
    ========================================================= */

    function updateLoai(
        value,
        label,
        icon,
        colorClass
    ) {

        loaiInput.value =
            value;


        loaiSelectedText.textContent =
            label;


        loaiSelectedIcon.className =
            'push-type-icon ' + colorClass;


        loaiSelectedIcon.innerHTML =
            '<i class="fa-solid ' +
            icon +
            '"></i>';


        previewType.textContent =
            label;


        previewChipIcon.className =
            'fa-solid ' +
            icon;


        previewTypeIcon.innerHTML =
            '<i class="fa-solid ' +
            icon +
            '"></i>';


        loaiOptions.forEach(function (option) {

            option.classList.toggle(
                'is-selected',
                option.dataset.value === value
            );

        });


        const previewColors = {

            info: '#3b82f6',

            warning: '#f5a623',

            promo: '#ec4899',

            system: '#a855f7'

        };


        const color =
            previewColors[value] ||
            '#ff3045';


        previewTypeIcon.style.background =
            color + '20';

        previewTypeIcon.style.color =
            color;

    }


    /* =========================================================
       DROPDOWN ĐỐI TƯỢNG
    ========================================================= */

    if (audienceTrigger) {

        audienceTrigger.addEventListener(
            'click',
            function (event) {

                event.stopPropagation();


                if (
                    audienceCustomSelect.classList.contains('open')
                ) {

                    closeDropdown(
                        audienceCustomSelect
                    );

                } else {

                    openDropdown(
                        audienceCustomSelect
                    );

                }

            }
        );

    }


    audienceOptions.forEach(function (option) {

        option.addEventListener(
            'click',
            function () {

                const value =
                    this.dataset.value;

                const label =
                    this.dataset.label;

                const icon =
                    this.dataset.icon;

                const colorClass =
                    this.dataset.class;


                updateAudience(
                    value,
                    label,
                    icon,
                    colorClass
                );


                closeDropdown(
                    audienceCustomSelect
                );

            }
        );

    });


    /* =========================================================
       UPDATE ĐỐI TƯỢNG
    ========================================================= */

    function updateAudience(
        value,
        label,
        icon,
        colorClass
    ) {

        /*
        | Giá trị thật gửi server
        */

        audienceSelect.value =
            value;


        /*
        | Text đang chọn
        */

        audienceSelectedText.textContent =
            label;


        /*
        | Icon
        */

        audienceSelectedIcon.className =
            'push-type-icon ' +
            colorClass;


        audienceSelectedIcon.innerHTML =
            '<i class="fa-solid ' +
            icon +
            '"></i>';


        /*
        | Active option
        */

        audienceOptions.forEach(function (option) {

            option.classList.toggle(
                'is-selected',
                option.dataset.value === value
            );

        });


        /*
        | Xử lý phần dưới
        */

        toggleAudience();

    }


    /* =========================================================
       CLICK NGOÀI DROPDOWN
    ========================================================= */

    document.addEventListener(
        'click',
        function (event) {

            if (
                loaiSelect &&
                !loaiSelect.contains(event.target)
            ) {

                closeDropdown(loaiSelect);

            }


            if (
                audienceCustomSelect &&
                !audienceCustomSelect.contains(event.target)
            ) {

                closeDropdown(
                    audienceCustomSelect
                );

            }

        }
    );


    /* =========================================================
       RECALCULATE KHI SCROLL / RESIZE
    ========================================================= */

    window.addEventListener(
        'resize',
        function () {

            if (
                loaiSelect &&
                loaiSelect.classList.contains('open')
            ) {

                positionDropdown(loaiSelect);

            }


            if (
                audienceCustomSelect &&
                audienceCustomSelect.classList.contains('open')
            ) {

                positionDropdown(
                    audienceCustomSelect
                );

            }

        }
    );


    window.addEventListener(
        'scroll',
        function () {

            if (
                loaiSelect &&
                loaiSelect.classList.contains('open')
            ) {

                positionDropdown(loaiSelect);

            }


            if (
                audienceCustomSelect &&
                audienceCustomSelect.classList.contains('open')
            ) {

                positionDropdown(
                    audienceCustomSelect
                );

            }

        },
        true
    );


    /* =========================================================
       PREVIEW TITLE
    ========================================================= */

    const titleInput =
        document.getElementById('tieu_de');

    const contentInput =
        document.getElementById('noi_dung');

    const previewTitle =
        document.getElementById('previewTitle');

    const previewContent =
        document.getElementById('previewContent');


    if (titleInput) {

        titleInput.addEventListener(
            'input',
            function () {

                previewTitle.textContent =
                    this.value.trim()
                    ||
                    'Tiêu đề thông báo của bạn';

            }
        );

    }


    if (contentInput) {

        contentInput.addEventListener(
            'input',
            function () {

                previewContent.textContent =
                    this.value.trim()
                    ||
                    'Nội dung thông báo sẽ hiển thị tại đây.';

            }
        );

    }


    /* =========================================================
       USER SELECTED DATA
    ========================================================= */

    let selectedUsers = [];


    const oldSelectedUsers =
        @json($oldSelectedUsersData);


    if (
        Array.isArray(oldSelectedUsers) &&
        oldSelectedUsers.length
    ) {

        oldSelectedUsers.forEach(function (user) {

            if (!user || !user.id) {
                return;
            }


            addSelectedUser({

                id: user.id,

                ho_ten:
                    user.ho_ten ||
                    user.name ||
                    '',

                email:
                    user.email ||
                    ''

            });

        });

    }


    /* =========================================================
       ADD USER
    ========================================================= */

    function addSelectedUser(user) {

        const userId =
            String(user.id);


        const exists =
            selectedUsers.some(function (item) {

                return String(item.id) ===
                    userId;

            });


        if (exists) {
            return;
        }


        selectedUsers.push({

            id: user.id,

            ho_ten:
                user.ho_ten ||
                '',

            email:
                user.email ||
                ''

        });


        renderSelectedUsers();

    }


    /* =========================================================
       REMOVE USER
    ========================================================= */

    function removeSelectedUser(userId) {

        selectedUsers =
            selectedUsers.filter(function (user) {

                return String(user.id) !==
                    String(userId);

            });


        renderSelectedUsers();

        renderSearchResults();

    }


    /* =========================================================
       RENDER USER ĐÃ CHỌN
    ========================================================= */

    function renderSelectedUsers() {

        selectedUsersBox.innerHTML = '';

        selectedUsersInputs.innerHTML = '';


        selectedUsers.forEach(function (user) {

            const item =
                document.createElement('div');


            item.className =
                'push-selected-user';


            item.dataset.userId =
                user.id;


            item.innerHTML = `

                <div class="push-selected-user-info">

                    <span class="push-selected-user-icon">

                        <i class="fa-solid fa-user"></i>

                    </span>


                    <div class="push-selected-user-text">

                        <strong>
                            ${escapeHtml(user.ho_ten)}
                        </strong>

                        <small>
                            ${escapeHtml(user.email)}
                        </small>

                    </div>

                </div>


                <button
                    type="button"
                    class="push-remove-user"
                    data-user-id="${user.id}"
                    title="Bỏ chọn người dùng">

                    <i class="fa-solid fa-xmark"></i>

                </button>

            `;


            const removeButton =
                item.querySelector(
                    '.push-remove-user'
                );


            removeButton.addEventListener(
                'click',
                function () {

                    removeSelectedUser(
                        this.dataset.userId
                    );

                }
            );


            selectedUsersBox.appendChild(
                item
            );


            const hiddenInput =
                document.createElement('input');


            hiddenInput.type =
                'hidden';


            hiddenInput.name =
                'nguoi_dung_cu_the[]';


            hiddenInput.value =
                user.id;


            selectedUsersInputs.appendChild(
                hiddenInput
            );

        });

    }


    /* =========================================================
       SEARCH USER
    ========================================================= */

    let searchTimer = null;

    let lastSearchUsers = [];


    if (searchInput) {

        searchInput.addEventListener(
            'input',
            function () {

                const keyword =
                    this.value.trim();


                clearTimeout(searchTimer);


                if (keyword.length < 2) {

                    resultBox.innerHTML = '';

                    resultBox.classList.add(
                        'is-hidden'
                    );

                    return;

                }


                searchTimer =
                    setTimeout(function () {

                        fetch(
                            '{{ route("admin.thong-bao-push.tim-nguoi-dung") }}?keyword='
                            +
                            encodeURIComponent(keyword),
                            {

                                headers: {

                                    'Accept':
                                        'application/json',

                                    'X-Requested-With':
                                        'XMLHttpRequest'

                                }

                            }
                        )

                        .then(function (response) {

                            if (!response.ok) {

                                throw new Error(
                                    'Không thể tìm người dùng'
                                );

                            }

                            return response.json();

                        })

                        .then(function (users) {

                            lastSearchUsers =
                                users || [];

                            renderSearchResults();

                        })

                        .catch(function (error) {

                            console.error(
                                'Lỗi tìm người dùng:',
                                error
                            );


                            resultBox.innerHTML = `

                                <div class="push-no-result">

                                    Không thể tìm người dùng.

                                </div>

                            `;


                            resultBox.classList.remove(
                                'is-hidden'
                            );

                        });

                    }, 300);

            }
        );

    }


    /* =========================================================
       RENDER SEARCH RESULTS
    ========================================================= */

    function renderSearchResults() {

        resultBox.innerHTML = '';


        if (
            !lastSearchUsers ||
            lastSearchUsers.length === 0
        ) {

            resultBox.innerHTML = `

                <div class="push-no-result">

                    <i class="fa-solid fa-user-slash"></i>

                    Không tìm thấy người dùng.

                </div>

            `;


            resultBox.classList.remove(
                'is-hidden'
            );

            return;

        }


        lastSearchUsers.forEach(function (user) {

            const alreadySelected =
                selectedUsers.some(function (selected) {

                    return String(selected.id) ===
                        String(user.id);

                });


            const item =
                document.createElement('button');


            item.type =
                'button';


            item.className =
                'push-user-result'
                +
                (
                    alreadySelected
                        ? ' is-already-selected'
                        : ''
                );


            item.innerHTML = `

                <span class="push-user-result-icon">

                    <i class="fa-solid fa-user"></i>

                </span>


                <span class="push-user-result-info">

                    <strong>
                        ${escapeHtml(user.ho_ten ?? '')}
                    </strong>

                    <small>
                        ${escapeHtml(user.email ?? '')}
                    </small>

                </span>


                ${
                    alreadySelected
                    ?
                    `

                        <span class="push-user-result-check">

                            <i class="fa-solid fa-check"></i>

                        </span>

                    `
                    :
                    `

                        <span
                            class="push-user-result-check"
                            style="color:#777d89">

                            <i class="fa-solid fa-plus"></i>

                        </span>

                    `
                }

            `;


            if (!alreadySelected) {

                item.addEventListener(
                    'click',
                    function () {

                        addSelectedUser({

                            id: user.id,

                            ho_ten:
                                user.ho_ten ?? '',

                            email:
                                user.email ?? ''

                        });


                        searchInput.focus();

                        renderSearchResults();

                    }
                );

            }


            resultBox.appendChild(item);

        });


        resultBox.classList.remove(
            'is-hidden'
        );

    }


    /* =========================================================
       ESCAPE HTML
    ========================================================= */

    function escapeHtml(value) {

        const div =
            document.createElement('div');


        div.textContent =
            value ?? '';


        return div.innerHTML;

    }


    /* =========================================================
       TOGGLE AUDIENCE
    ========================================================= */


    /* =========================================================
       DROPDOWN HẠNG THÀNH VIÊN
    ========================================================= */

    if (rankTrigger && rankCustomSelect) {
        rankTrigger.addEventListener('click', function (event) {
            event.stopPropagation();

            if (rankCustomSelect.classList.contains('is-open')) {
                rankCustomSelect.classList.remove('is-open');
            } else {
                if (loaiSelect) closeDropdown(loaiSelect);
                if (audienceCustomSelect) closeDropdown(audienceCustomSelect);
                rankCustomSelect.classList.add('is-open');
            }
        });
    }

    rankOptions.forEach(function (option) {
        option.addEventListener('click', function () {
            const value = this.dataset.value;
            const label = this.dataset.label;
            const icon = this.dataset.icon;
            const colorClass = this.dataset.class;

            rankSelect.value = value;
            rankSelectedText.textContent = label;
            rankSelectedIcon.className = 'push-member-rank-icon ' + colorClass;
            rankSelectedIcon.innerHTML = '<i class="fa-solid ' + icon + '"></i>';

            rankOptions.forEach(function (item) {
                item.classList.toggle('is-selected', item.dataset.value === value);
            });

            rankCustomSelect.classList.remove('is-open');
        });
    });

    if (rankSelect && rankSelect.value) {
        rankOptions.forEach(function (option) {
            if (option.dataset.value === rankSelect.value) {
                option.classList.add('is-selected');
                rankSelectedText.textContent = option.dataset.label;
                rankSelectedIcon.className = 'push-member-rank-icon ' + option.dataset.class;
                rankSelectedIcon.innerHTML = '<i class="fa-solid ' + option.dataset.icon + '"></i>';
            }
        });
    }

    document.addEventListener('click', function (event) {
        if (rankCustomSelect && !rankCustomSelect.contains(event.target)) {
            rankCustomSelect.classList.remove('is-open');
        }
    });

    function toggleAudience() {

        const value =
            audienceSelect.value;


        if (value === 'hang_thanh_vien') {

            rankWrapper.classList.remove(
                'is-hidden'
            );

            specificWrapper.classList.add(
                'is-hidden'
            );


        } else if (
            value === 'nguoi_dung_cu_the'
        ) {

            if (rankCustomSelect) {
                rankCustomSelect.classList.remove('is-open');
            }

            specificWrapper.classList.remove(
                'is-hidden'
            );

            rankWrapper.classList.add(
                'is-hidden'
            );


        } else {

            if (rankCustomSelect) {
                rankCustomSelect.classList.remove('is-open');
            }

            rankWrapper.classList.add(
                'is-hidden'
            );

            specificWrapper.classList.add(
                'is-hidden'
            );

        }


        /*
        | ACTIVE CARD BÊN PHẢI
        */

        document
            .querySelectorAll(
                '[data-audience-card]'
            )
            .forEach(function (card) {

                card.classList.toggle(

                    'is-active',

                    card.dataset.audienceCard ===
                    value

                );

            });

    }


    /* =========================================================
       LOAD ĐỐI TƯỢNG CŨ
    ========================================================= */

    if (audienceSelect.value) {

        const current =
            audienceSelect.value;


        const icon =
            audienceIconMap[current]
            ||
            'fa-users';


        const label =
            audienceLabelMap[current]
            ||
            'Đối tượng nhận';


        const colorClass =
            audienceClassMap[current]
            ||
            'is-all';


        updateAudience(
            current,
            label,
            icon,
            colorClass
        );

    }


    /* =========================================================
       LOAD LOẠI CŨ
    ========================================================= */

    if (loaiInput.value) {

        const current =
            loaiInput.value;


        const icon =
            loaiIconMap[current]
            ||
            'fa-bell';


        const label =
            loaiLabelMap[current]
            ||
            'Thông báo';


        const colorClass =
            loaiClassMap[current]
            ||
            'type-info';


        updateLoai(
            current,
            label,
            icon,
            colorClass
        );

    }


    /* =========================================================
       CLICK NGOÀI SEARCH
    ========================================================= */

    document.addEventListener(
        'click',
        function (event) {

            if (
                specificWrapper &&
                !specificWrapper.contains(
                    event.target
                )
            ) {

                resultBox.classList.add(
                    'is-hidden'
                );

            }

        }
    );


    if (searchInput) {

        searchInput.addEventListener(
            'focus',
            function () {

                if (
                    lastSearchUsers.length > 0 &&
                    this.value.trim().length >= 2
                ) {

                    resultBox.classList.remove(
                        'is-hidden'
                    );

                }

            }
        );

    }


    /* =========================================================
       VALIDATE USER CỤ THỂ
    ========================================================= */

    if (form) {

        form.addEventListener(
            'submit',
            function (event) {

                if (
                    audienceSelect.value ===
                    'nguoi_dung_cu_the'
                ) {

                    if (
                        selectedUsers.length === 0
                    ) {

                        event.preventDefault();


                        alert(
                            'Vui lòng chọn ít nhất một người dùng.'
                        );


                        if (searchInput) {

                            searchInput.focus();

                        }


                        return;

                    }

                }

            }
        );

    }


});

</script>

@endpush

@endsection