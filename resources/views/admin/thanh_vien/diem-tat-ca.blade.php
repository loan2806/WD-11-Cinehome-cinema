@extends('layouts.admin')

@section('title', 'Tặng & thu hồi điểm')
@section('page-title', 'Tặng & thu hồi điểm')
@section('page-subtitle', 'Quản lý điểm cho toàn bộ thành viên')

@section('content')

@php
/*
|--------------------------------------------------------------------------
| ĐỐI TƯỢNG
|--------------------------------------------------------------------------
*/
$audienceGuide = [
'all' => [
'label' => 'Tất cả thành viên',
'description' => 'Áp dụng cho toàn bộ thành viên',
'icon' => 'fa-globe',
'class' => 'audience-all',
],

'hang_thanh_vien' => [
'label' => 'Theo hạng thành viên',
'description' => 'Chỉ áp dụng cho một hạng cụ thể',
'icon' => 'fa-ranking-star',
'class' => 'audience-rank',
],

'nguoi_dung_cu_the' => [
'label' => 'Người dùng cụ thể',
'description' => 'Có thể chọn nhiều người dùng',
'icon' => 'fa-user-pen',
'class' => 'audience-specific',
],
];

/*
|--------------------------------------------------------------------------
| OLD DATA
|--------------------------------------------------------------------------
*/

$oldAudience = old('doi_tuong_nhan', 'all');
$oldRank = old('hang_thanh_vien', '');

$oldUsers = old('nguoi_dung_cu_the', []);

if (!is_array($oldUsers)) {
$oldUsers = $oldUsers ? [$oldUsers] : [];
}
@endphp


<style>
    /* =========================================================
       PAGE
    ========================================================= */

    .all-point-page {
        width: 100%;
    }


    /* =========================================================
       HERO
    ========================================================= */

    .all-point-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;

        padding: 28px;
        margin-bottom: 22px;

        border-radius: 22px;

        background: linear-gradient(135deg,
                #171b24,
                #202633);

        border: 1px solid rgba(255, 255, 255, .07);
    }

    .all-point-hero-copy {
        flex: 1;
    }

    .all-point-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;

        margin-bottom: 8px;

        color: #f5b942;
        font-size: 13px;
        font-weight: 800;
    }

    .all-point-hero h2 {
        margin: 0 0 8px;

        color: #fff;

        font-size: 26px;
        font-weight: 900;
    }

    .all-point-hero p {
        margin: 0;

        color: #9ca3af;
    }

    .all-point-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;

        padding: 11px 16px;

        border-radius: 12px;

        color: #fff;
        background: rgba(255, 255, 255, .07);

        text-decoration: none;

        font-weight: 800;
        white-space: nowrap;
    }

    .all-point-back:hover {
        color: #fff;
        background: rgba(255, 255, 255, .12);
    }


    /* =========================================================
       COUNT
    ========================================================= */

    .all-point-count {
        margin-bottom: 22px;
        padding: 18px 22px;

        border-radius: 18px;

        background: #171b24;

        border: 1px solid rgba(255, 255, 255, .07);

        color: #cbd5e1;
    }

    .all-point-count strong {
        color: #f5b942;
        font-size: 20px;
    }


    /* =========================================================
       GRID
    ========================================================= */

    .all-point-grid {
        display: grid;

        grid-template-columns:
            minmax(0, 1fr) minmax(0, 1fr);

        gap: 22px;

        align-items: start;
    }


    /* =========================================================
       CARD
    ========================================================= */

    .all-point-card {
        position: relative;

        min-width: 0;

        padding: 26px;

        border-radius: 22px;

        background: #171b24;

        border: 1px solid rgba(255, 255, 255, .07);

        overflow: visible;
    }

    .all-point-card-head {
        display: flex;
        align-items: center;
        gap: 14px;

        margin-bottom: 22px;
    }

    .all-point-icon {
        width: 48px;
        height: 48px;

        flex: 0 0 48px;

        display: grid;
        place-items: center;

        border-radius: 14px;

        font-size: 20px;
    }

    .all-point-icon.gift {
        color: #22c55e;
        background: rgba(34, 197, 94, .12);
    }

    .all-point-icon.withdraw {
        color: #ef4444;
        background: rgba(239, 68, 68, .12);
    }

    .all-point-card-head h3 {
        margin: 0;

        color: #fff;

        font-size: 19px;
        font-weight: 900;
    }

    .all-point-card-head p {
        margin: 3px 0 0;

        color: #8b93a1;

        font-size: 13px;
    }


    /* =========================================================
       FIELD
    ========================================================= */

    .all-point-field {
        position: relative;

        margin-bottom: 17px;

        min-width: 0;
    }

    .all-point-field>label,
    .all-point-field-label {
        display: block;

        margin-bottom: 8px;

        color: #e5e7eb;

        font-size: 14px;
        font-weight: 800;
    }

    .required {
        color: #ef4444;
    }


    /* =========================================================
       INPUT
    ========================================================= */

    .all-point-field input,
    .all-point-field textarea {
        width: 100%;

        box-sizing: border-box;

        padding: 12px 14px;

        border: 1px solid rgba(255, 255, 255, .1);

        border-radius: 12px;

        outline: none;

        background: #10141b;

        color: #fff;

        font-size: 14px;

        transition: .2s;
    }

    .all-point-field input {
        height: 48px;
    }

    .all-point-field textarea {
        min-height: 100px;

        resize: vertical;
    }

    .all-point-field input::placeholder,
    .all-point-field textarea::placeholder {
        color: #667085;
    }

    .all-point-field input:focus,
    .all-point-field textarea:focus {
        border-color: #f5b942;

        box-shadow:
            0 0 0 3px rgba(245, 185, 66, .08);
    }


    /* =========================================================
       INVALID
    ========================================================= */

    .all-point-field input.is-invalid,
    .all-point-field textarea.is-invalid,
    .all-point-field .all-point-custom-select.is-invalid .all-point-custom-trigger {
        border-color: #ef4444 !important;

        box-shadow:
            0 0 0 3px rgba(239, 68, 68, .08) !important;
    }

    .all-point-error {
        display: block;

        margin-top: 6px;

        color: #ff5c67;

        font-size: 12px;
        line-height: 1.45;

        font-weight: 600;
    }

    .all-point-error i {
        margin-right: 4px;
    }


    /* =========================================================
       HELP
    ========================================================= */

    .all-point-help {
        display: flex;
        align-items: flex-start;
        gap: 5px;

        margin-top: 7px;

        color: #7f8795;

        font-size: 12px;
        line-height: 1.5;
    }

    .all-point-help i {
        margin-top: 2px;

        color: #a855f7;
    }


    /* =========================================================
       WARNING
    ========================================================= */

    .all-point-warning {
        display: flex;
        gap: 10px;

        padding: 13px 14px;
        margin-bottom: 18px;

        border-radius: 12px;

        color: #fbbf24;

        background: rgba(245, 158, 11, .08);

        border: 1px solid rgba(245, 158, 11, .15);

        font-size: 13px;

        line-height: 1.5;
    }

    [data-rank-select] .all-point-custom-menu {
        max-height: none !important;
        overflow-y: visible !important;
    }

    /* Dropdown mở lên trên khi không đủ khoảng trống phía dưới */
    .all-point-custom-select.open-up .all-point-custom-menu {
        top: auto;
        bottom: calc(100% + 7px);
    }

    /* Khi mở lên thì giới hạn chiều cao theo màn hình */
    .all-point-custom-select.open-up .all-point-custom-menu,
    .all-point-custom-select.is-open .all-point-custom-menu {
        max-height: min(280px, calc(100vh - 30px));
    }

    /* =========================================================
       CUSTOM DROPDOWN
    ========================================================= */

    .all-point-custom-select {
        position: relative;

        width: 100%;

        z-index: 30;
    }

    .all-point-custom-select.is-open {
        z-index: 9999;
    }

    .all-point-custom-trigger {
        width: 100%;
        height: 48px;

        padding: 0 14px;

        border: 1px solid rgba(255, 255, 255, .1);

        border-radius: 12px;

        background: #10141b;

        color: #fff;

        display: flex;
        align-items: center;
        justify-content: space-between;

        cursor: pointer;

        transition: .2s;
    }

    .all-point-custom-trigger:hover {
        border-color: rgba(255, 255, 255, .2);
    }

    .all-point-custom-select.is-open .all-point-custom-trigger {
        border-color: #f5b942;

        box-shadow:
            0 0 0 3px rgba(245, 185, 66, .08);
    }

    .all-point-current {
        display: flex;
        align-items: center;
        gap: 10px;

        min-width: 0;
    }

    .all-point-current-text {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .all-point-arrow {
        color: #8992a1;

        font-size: 12px;

        transition: transform .2s;
    }

    .all-point-custom-select.is-open .all-point-arrow {
        transform: rotate(180deg);
    }


    /* =========================================================
       DROPDOWN MENU
    ========================================================= */

    .all-point-custom-menu {
        position: absolute;

        left: 0;
        right: 0;

        top: calc(100% + 7px);

        display: none;

        max-height: 280px;

        padding: 6px;

        border-radius: 14px;

        background: #1b202a;

        border: 1px solid rgba(255, 255, 255, .10);

        box-shadow:
            0 18px 50px rgba(0, 0, 0, .45);

        overflow-y: auto;

        z-index: 10000;
    }

    .all-point-custom-select.is-open .all-point-custom-menu {
        display: block;
    }

    .all-point-option {
        width: 100%;

        display: flex;
        align-items: center;

        gap: 11px;

        padding: 12px;

        border: 0;

        border-radius: 10px;

        background: transparent;

        color: #fff;

        text-align: left;

        cursor: pointer;
    }

    .all-point-option:hover {
        background: rgba(255, 255, 255, .07);
    }

    .all-point-option.is-selected {
        background: rgba(245, 185, 66, .10);
    }

    .all-point-option-icon {
        width: 32px;
        height: 32px;

        flex: 0 0 32px;

        display: grid;
        place-items: center;

        border-radius: 9px;

        font-size: 14px;
    }

    .all-point-option-content {
        flex: 1;

        min-width: 0;
    }

    .all-point-option-content strong {
        display: block;

        color: #fff;

        font-size: 13px;
    }

    .all-point-option-content small {
        display: block;

        margin-top: 2px;

        color: #7f8795;

        font-size: 11px;
    }

    .all-point-check {
        color: #22c55e;

        opacity: 0;
    }

    .all-point-option.is-selected .all-point-check {
        opacity: 1;
    }


    /* =========================================================
       AUDIENCE COLORS
    ========================================================= */

    .audience-all {
        color: #60a5fa;
        background: rgba(59, 130, 246, .12);
    }

    .audience-rank {
        color: #f59e0b;
        background: rgba(245, 158, 11, .12);
    }

    .audience-specific {
        color: #c084fc;
        background: rgba(168, 85, 247, .12);
    }


    /* =========================================================
       RANK COLORS
    ========================================================= */

    .rank-default {
        color: #94a3b8;
        background: rgba(148, 163, 184, .12);
    }

    .rank-member {
        color: #3b82f6;
        background: rgba(59, 130, 246, .12);
    }

    .rank-silver {
        color: #cbd5e1;
        background: rgba(203, 213, 225, .12);
    }

    .rank-gold {
        color: #f59e0b;
        background: rgba(245, 158, 11, .12);
    }

    .rank-platinum {
        color: #c084fc;
        background: rgba(192, 132, 252, .12);
    }


    /* =========================================================
       CONDITIONAL BOX
    ========================================================= */

    .point-conditional {
        display: none;

        padding: 0;

        margin-bottom: 17px;

        border-radius: 0;

        background: transparent;

        border: none;
    }

    .point-conditional.is-visible {
        display: block;
    }


    /* =========================================================
       SEARCH USER
    ========================================================= */

    .point-user-search {
        position: relative;
    }

    .point-user-search>i {
        position: absolute;

        left: 14px;
        top: 50%;

        transform: translateY(-50%);

        color: #718096;

        pointer-events: none;
    }

    .point-user-search input {
        padding-left: 42px;
    }


    /* =========================================================
       SEARCH RESULTS
    ========================================================= */

    .point-user-results {
        position: absolute;

        left: 0;
        right: 0;

        top: calc(100% + 7px);

        max-height: 260px;

        padding: 6px;

        border-radius: 14px;

        background: #1b202a;

        border: 1px solid rgba(255, 255, 255, .10);

        box-shadow:
            0 18px 50px rgba(0, 0, 0, .45);

        overflow-y: auto;

        z-index: 99999;
    }

    .point-user-results.is-hidden {
        display: none;
    }

    .point-user-result {
        width: 100%;

        display: flex;
        align-items: center;

        gap: 10px;

        padding: 11px;

        border: 0;

        border-radius: 10px;

        background: transparent;

        color: #fff;

        cursor: pointer;

        text-align: left;
    }

    .point-user-result:hover {
        background: rgba(255, 255, 255, .07);
    }

    .point-user-avatar {
        width: 34px;
        height: 34px;

        flex: 0 0 34px;

        display: grid;
        place-items: center;

        border-radius: 50%;

        background: rgba(168, 85, 247, .12);

        color: #c084fc;
    }

    .point-user-info {
        min-width: 0;
        flex: 1;
    }

    .point-user-info strong {
        display: block;

        color: #fff;

        font-size: 13px;
    }

    .point-user-info small {
        display: block;

        margin-top: 2px;

        color: #7f8795;

        font-size: 11px;

        white-space: nowrap;

        overflow: hidden;

        text-overflow: ellipsis;
    }


    /* =========================================================
       SELECTED USERS
    ========================================================= */

    .point-selected-users {
        display: flex;

        flex-direction: column;

        gap: 7px;

        margin-top: 10px;
    }

    .point-selected-user {
        display: flex;
        align-items: center;

        gap: 10px;

        padding: 9px 10px;

        border-radius: 11px;

        background: rgba(168, 85, 247, .07);

        border: 1px solid rgba(168, 85, 247, .12);
    }

    .point-selected-user-info {
        flex: 1;
        min-width: 0;
    }

    .point-selected-user-info strong {
        display: block;

        color: #fff;

        font-size: 12px;
    }

    .point-selected-user-info small {
        display: block;

        margin-top: 2px;

        color: #7f8795;

        font-size: 11px;
    }

    .point-remove-user {
        width: 28px;
        height: 28px;

        display: grid;
        place-items: center;

        border: 0;

        border-radius: 8px;

        background: rgba(239, 68, 68, .10);

        color: #ef4444;

        cursor: pointer;
    }


    /* =========================================================
       SUBMIT
    ========================================================= */

    .all-point-submit {
        width: 100%;

        min-height: 48px;

        border: 0;

        border-radius: 13px;

        color: #fff;

        font-weight: 900;

        cursor: pointer;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 8px;
    }

    .all-point-submit.gift {
        background: linear-gradient(135deg,
                #16a34a,
                #22c55e);
    }

    .all-point-submit.withdraw {
        background: linear-gradient(135deg,
                #dc2626,
                #ef4444);
    }


    /* =========================================================
       MOBILE
    ========================================================= */

    @media (max-width: 900px) {

        .all-point-grid {
            grid-template-columns: 1fr;
        }

        .all-point-hero {
            flex-direction: column;

            align-items: flex-start;
        }
    }
</style>





<div class="all-point-page">

    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <section class="all-point-hero">

        <div class="all-point-hero-copy">

            <span class="all-point-kicker">
                <i class="fa-solid fa-star"></i>
                Quản lý điểm thành viên
            </span>

            <h2>
                Tặng & thu hồi điểm
            </h2>

            <p>
                Thực hiện cộng hoặc thu hồi điểm theo đối tượng được chọn.
            </p>

        </div>

        <a
            href="{{ route('admin.thanh-vien.index') }}"
            class="all-point-back">

            <i class="fa-solid fa-arrow-left"></i>

            Danh sách thành viên

        </a>

    </section>


    {{-- =========================================================
         COUNT
    ========================================================== --}}

    <section class="all-point-count">

        <i class="fa-solid fa-users"></i>

        Hiện có

        <strong>
            {{ number_format($soLuongThanhVien) }}
        </strong>

        thành viên trong hệ thống.

    </section>


    {{-- =========================================================
         2 FORM
    ========================================================== --}}

    <div class="all-point-grid">


        {{-- =====================================================
             FORM TẶNG ĐIỂM
        ====================================================== --}}

        <section class="all-point-card">

            <div class="all-point-card-head">

                <span class="all-point-icon gift">
                    <i class="fa-solid fa-gift"></i>
                </span>

                <div>

                    <h3>
                        Tặng điểm
                    </h3>

                    <p>
                        Cộng điểm cho thành viên theo đối tượng được chọn
                    </p>

                </div>

            </div>


            <div class="all-point-warning">

                <i class="fa-solid fa-triangle-exclamation"></i>

                <span id="giftWarningText">
                    Điểm sẽ được cộng cho
                    <strong>tất cả thành viên</strong>.
                </span>

            </div>


            <form
                method="POST"
                action="{{ route('admin.thanh-vien.xu-ly-diem-hang-loat') }}"
                id="giftPointForm">

                @csrf

                <input
                    type="hidden"
                    name="loai"
                    value="tang">


                {{-- =================================================
                     ĐỐI TƯỢNG
                ================================================== --}}

                <div class="all-point-field">

                    <label>
                        Đối tượng được áp dụng
                        <span class="required">*</span>
                    </label>


                    <div
                        class="all-point-custom-select
                        {{ session('error_form') === 'tang' && $errors->has('doi_tuong_nhan') ? 'is-invalid' : '' }}"
                        data-audience-select
                        data-form="gift">

                        <input
                            type="hidden"
                            name="doi_tuong_nhan"
                            value="{{ session('error_form') === 'tang' ? old('doi_tuong_nhan', $oldAudience ?? 'all') : 'all' }}"
                            data-audience-value>


                        <button
                            type="button"
                            class="all-point-custom-trigger"
                            data-audience-trigger>

                            <span
                                class="all-point-current"
                                data-audience-current>

                                @php
                                    $giftAudience =
                                        session('error_form') === 'tang'
                                            ? old('doi_tuong_nhan', $oldAudience ?? 'all')
                                            : 'all';
                                @endphp

                                <span
                                    class="all-point-option-icon {{ $audienceGuide[$giftAudience]['class'] ?? 'audience-all' }}">

                                    <i class="fa-solid {{ $audienceGuide[$giftAudience]['icon'] ?? 'fa-globe' }}"></i>

                                </span>

                                <span class="all-point-current-text">

                                    {{ $audienceGuide[$giftAudience]['label'] ?? 'Tất cả thành viên' }}

                                </span>

                            </span>

                            <i class="fa-solid fa-chevron-down all-point-arrow"></i>

                        </button>


                        <div
                            class="all-point-custom-menu"
                            data-audience-menu>

                            @foreach ($audienceGuide as $value => $meta)

                                <button
                                    type="button"
                                    class="all-point-option
                                        {{ $giftAudience === $value ? 'is-selected' : '' }}"
                                    data-value="{{ $value }}"
                                    data-label="{{ $meta['label'] }}"
                                    data-icon="{{ $meta['icon'] }}"
                                    data-class="{{ $meta['class'] }}">

                                    <span class="all-point-option-icon {{ $meta['class'] }}">

                                        <i class="fa-solid {{ $meta['icon'] }}"></i>

                                    </span>

                                    <span class="all-point-option-content">

                                        <strong>
                                            {{ $meta['label'] }}
                                        </strong>

                                        <small>
                                            {{ $meta['description'] }}
                                        </small>

                                    </span>

                                    <i class="fa-solid fa-check all-point-check"></i>

                                </button>

                            @endforeach

                        </div>

                    </div>


                    @if(session('error_form') === 'tang')

                        @error('doi_tuong_nhan')

                            <small class="all-point-error server-error">

                                <i class="fa-solid fa-circle-exclamation"></i>

                                {{ $message }}

                            </small>

                        @enderror

                    @endif

                </div>


                {{-- =================================================
                     HẠNG
                ================================================== --}}

                <div
                    class="point-conditional
                    {{ $giftAudience === 'hang_thanh_vien' ? 'is-visible' : '' }}"
                    data-rank-wrapper="gift">

                    <div class="all-point-field">

                        <label>
                            Hạng thành viên được tặng
                            <span class="required">*</span>
                        </label>


                        <div
                            class="all-point-custom-select
                            {{ session('error_form') === 'tang' && $errors->has('hang_thanh_vien') ? 'is-invalid' : '' }}"
                            data-rank-select>

                            <input
                                type="hidden"
                                name="hang_thanh_vien"
                                value="{{ session('error_form') === 'tang' ? old('hang_thanh_vien', $oldRank ?? '') : '' }}"
                                data-rank-value>


                            <button
                                type="button"
                                class="all-point-custom-trigger"
                                data-rank-trigger>

                                <span
                                    class="all-point-current"
                                    data-rank-current>

                                    @php
                                        $giftRank =
                                            session('error_form') === 'tang'
                                                ? old('hang_thanh_vien', $oldRank ?? '')
                                                : '';
                                    @endphp

                                    @switch($giftRank)

                                        @case('member')

                                            <span class="all-point-option-icon rank-member">
                                                <i class="fa-solid fa-user"></i>
                                            </span>

                                            <span class="all-point-current-text">
                                                Member
                                            </span>

                                        @break


                                        @case('silver')

                                            <span class="all-point-option-icon rank-silver">
                                                <i class="fa-solid fa-medal"></i>
                                            </span>

                                            <span class="all-point-current-text">
                                                Silver
                                            </span>

                                        @break


                                        @case('gold')

                                            <span class="all-point-option-icon rank-gold">
                                                <i class="fa-solid fa-crown"></i>
                                            </span>

                                            <span class="all-point-current-text">
                                                Gold
                                            </span>

                                        @break


                                        @case('platinum')

                                            <span class="all-point-option-icon rank-platinum">
                                                <i class="fa-solid fa-gem"></i>
                                            </span>

                                            <span class="all-point-current-text">
                                                Platinum
                                            </span>

                                        @break


                                        @default

                                            <span class="all-point-option-icon rank-default">
                                                <i class="fa-solid fa-layer-group"></i>
                                            </span>

                                            <span class="all-point-current-text">
                                                -- Chọn hạng --
                                            </span>

                                    @endswitch

                                </span>

                                <i class="fa-solid fa-chevron-down all-point-arrow"></i>

                            </button>


                            <div
                                class="all-point-custom-menu"
                                data-rank-menu>


                                {{-- DEFAULT --}}

                                <button
                                    type="button"
                                    class="all-point-option {{ $giftRank === '' ? 'is-selected' : '' }}"
                                    data-value=""
                                    data-label="-- Chọn hạng --"
                                    data-icon="fa-layer-group"
                                    data-class="rank-default">

                                    <span class="all-point-option-icon rank-default">

                                        <i class="fa-solid fa-layer-group"></i>

                                    </span>

                                    <span class="all-point-option-content">

                                        <strong>
                                            -- Chọn hạng --
                                        </strong>

                                    </span>

                                    <i class="fa-solid fa-check all-point-check"></i>

                                </button>


                                {{-- MEMBER --}}

                                <button
                                    type="button"
                                    class="all-point-option {{ $giftRank === 'member' ? 'is-selected' : '' }}"
                                    data-value="member"
                                    data-label="Member"
                                    data-icon="fa-user"
                                    data-class="rank-member">

                                    <span class="all-point-option-icon rank-member">

                                        <i class="fa-solid fa-user"></i>

                                    </span>

                                    <span class="all-point-option-content">

                                        <strong>
                                            Member
                                        </strong>

                                        <small>
                                            Hạng thành viên cơ bản
                                        </small>

                                    </span>

                                    <i class="fa-solid fa-check all-point-check"></i>

                                </button>


                                {{-- SILVER --}}

                                <button
                                    type="button"
                                    class="all-point-option {{ $giftRank === 'silver' ? 'is-selected' : '' }}"
                                    data-value="silver"
                                    data-label="Silver"
                                    data-icon="fa-medal"
                                    data-class="rank-silver">

                                    <span class="all-point-option-icon rank-silver">

                                        <i class="fa-solid fa-medal"></i>

                                    </span>

                                    <span class="all-point-option-content">

                                        <strong>
                                            Silver
                                        </strong>

                                        <small>
                                            Hạng bạc
                                        </small>

                                    </span>

                                    <i class="fa-solid fa-check all-point-check"></i>

                                </button>


                                {{-- GOLD --}}

                                <button
                                    type="button"
                                    class="all-point-option {{ $giftRank === 'gold' ? 'is-selected' : '' }}"
                                    data-value="gold"
                                    data-label="Gold"
                                    data-icon="fa-crown"
                                    data-class="rank-gold">

                                    <span class="all-point-option-icon rank-gold">

                                        <i class="fa-solid fa-crown"></i>

                                    </span>

                                    <span class="all-point-option-content">

                                        <strong>
                                            Gold
                                        </strong>

                                        <small>
                                            Hạng vàng
                                        </small>

                                    </span>

                                    <i class="fa-solid fa-check all-point-check"></i>

                                </button>


                                {{-- PLATINUM --}}

                                <button
                                    type="button"
                                    class="all-point-option {{ $giftRank === 'platinum' ? 'is-selected' : '' }}"
                                    data-value="platinum"
                                    data-label="Platinum"
                                    data-icon="fa-gem"
                                    data-class="rank-platinum">

                                    <span class="all-point-option-icon rank-platinum">

                                        <i class="fa-solid fa-gem"></i>

                                    </span>

                                    <span class="all-point-option-content">

                                        <strong>
                                            Platinum
                                        </strong>

                                        <small>
                                            Hạng bạch kim
                                        </small>

                                    </span>

                                    <i class="fa-solid fa-check all-point-check"></i>

                                </button>

                            </div>

                        </div>


                        @if(session('error_form') === 'tang')

                            @error('hang_thanh_vien')

                                <small class="all-point-error server-error">

                                    <i class="fa-solid fa-circle-exclamation"></i>

                                    {{ $message }}

                                </small>

                            @enderror

                        @endif

                    </div>

                </div>


                {{-- =================================================
                     NGƯỜI DÙNG CỤ THỂ
                ================================================== --}}

                <div
                    class="point-conditional
                    {{ $giftAudience === 'nguoi_dung_cu_the' ? 'is-visible' : '' }}"
                    data-specific-wrapper="gift">

                    <div class="all-point-field">

                        <label>
                            Tìm người dùng
                            <span class="required">*</span>
                        </label>


                        <div class="point-user-search">

                            <i class="fa-solid fa-magnifying-glass"></i>

                            <input
                                type="text"
                                autocomplete="off"
                                placeholder="Nhập tên hoặc email để tìm..."
                                data-user-search="gift">

                        </div>


                        <div
                            class="point-user-results is-hidden"
                            data-user-results="gift">
                        </div>


                        <div
                            class="point-selected-users"
                            data-selected-users="gift">
                        </div>


                        <div data-selected-inputs="gift">

                            @if(session('error_form') === 'tang')

                                @foreach(old('nguoi_dung_cu_the', []) as $userId)

                                    <input
                                        type="hidden"
                                        name="nguoi_dung_cu_the[]"
                                        value="{{ $userId }}">

                                @endforeach

                            @endif

                        </div>


                        @if(session('error_form') === 'tang')

                            @error('nguoi_dung_cu_the')

                                <small class="all-point-error server-error">

                                    <i class="fa-solid fa-circle-exclamation"></i>

                                    {{ $message }}

                                </small>

                            @enderror

                        @endif

                    </div>

                </div>


                {{-- =================================================
                     SỐ ĐIỂM TẶNG
                ================================================== --}}

                <div class="all-point-field">

                    <label>
                        Số điểm tặng
                        <span class="required">*</span>
                    </label>


                    <input
                        type="number"
                        name="so_diem"
                        step="1"
                        inputmode="numeric"
                        value="{{ session('error_form') === 'tang' ? old('so_diem') : '' }}"
                        placeholder="Ví dụ: 100"
                        class="{{ session('error_form') === 'tang' && $errors->has('so_diem') ? 'is-invalid' : '' }}">


                    @if(session('error_form') === 'tang')

                        @error('so_diem')

                            <small class="all-point-error server-error">

                                <i class="fa-solid fa-circle-exclamation"></i>

                                {{ $message }}

                            </small>

                        @enderror

                    @endif

                </div>


                {{-- =================================================
                     TÍNH HẠNG
                ================================================== --}}

                <div class="all-point-field">

                    <label>
                        Tính vào hạng thành viên
                        <span class="required">*</span>
                    </label>


                    <div
                        class="all-point-custom-select"
                        data-tinh-hang-select>

                        <input
                            type="hidden"
                            name="tinh_vao_hang"
                            value="{{ session('error_form') === 'tang' ? old('tinh_vao_hang', '1') : '1' }}"
                            data-tinh-hang-value>


                        <button
                            type="button"
                            class="all-point-custom-trigger"
                            data-tinh-hang-trigger>

                            <span
                                class="all-point-current"
                                data-tinh-hang-current>

                                @if(
                                    session('error_form') === 'tang'
                                    && old('tinh_vao_hang') === '0'
                                )

                                    <span
                                        class="all-point-option-icon"
                                        style="color:#ef4444;background:rgba(239,68,68,.12);">

                                        <i class="fa-solid fa-ban"></i>

                                    </span>

                                    <span class="all-point-current-text">
                                        Không - Không tính vào hạng
                                    </span>

                                @else

                                    <span
                                        class="all-point-option-icon"
                                        style="color:#22c55e;background:rgba(34,197,94,.12);">

                                        <i class="fa-solid fa-ranking-star"></i>

                                    </span>

                                    <span class="all-point-current-text">
                                        Có - Tính vào hạng
                                    </span>

                                @endif

                            </span>

                            <i class="fa-solid fa-chevron-down all-point-arrow"></i>

                        </button>


                        <div
                            class="all-point-custom-menu"
                            data-tinh-hang-menu>


                            <button
                                type="button"
                                class="all-point-option"
                                data-value="1"
                                data-label="Có - Tính vào hạng"
                                data-icon="fa-ranking-star"
                                data-inline-color="#22c55e">

                                <span
                                    class="all-point-option-icon"
                                    style="color:#22c55e;background:rgba(34,197,94,.12);">

                                    <i class="fa-solid fa-ranking-star"></i>

                                </span>

                                <span class="all-point-option-content">

                                    <strong>
                                        Có - Tính vào hạng
                                    </strong>

                                    <small>
                                        Điểm được cộng vào tổng tích lũy
                                    </small>

                                </span>

                                <i class="fa-solid fa-check all-point-check"></i>

                            </button>


                            <button
                                type="button"
                                class="all-point-option"
                                data-value="0"
                                data-label="Không - Không tính vào hạng"
                                data-icon="fa-ban"
                                data-inline-color="#ef4444">

                                <span
                                    class="all-point-option-icon"
                                    style="color:#ef4444;background:rgba(239,68,68,.12);">

                                    <i class="fa-solid fa-ban"></i>

                                </span>

                                <span class="all-point-option-content">

                                    <strong>
                                        Không - Không tính vào hạng
                                    </strong>

                                    <small>
                                        Điểm không làm thay đổi hạng
                                    </small>

                                </span>

                                <i class="fa-solid fa-check all-point-check"></i>

                            </button>

                        </div>

                    </div>


                    <div class="all-point-help">

                        <i class="fa-solid fa-circle-info"></i>

                        <span>
                            Nếu chọn "Có", điểm sẽ được cộng vào tổng tích lũy
                            và có thể làm thay đổi hạng.
                        </span>

                    </div>

                </div>


                {{-- =================================================
                     NỘI DUNG
                ================================================== --}}

                <div class="all-point-field">

                    <label>
                        Nội dung
                        <span class="required">*</span>
                    </label>


                    <textarea
                        name="noi_dung"
                        maxlength="255"
                        placeholder="Ví dụ: Tặng 100 điểm nhân dịp sinh nhật CineHome..."
                        class="{{ session('error_form') === 'tang' && $errors->has('noi_dung') ? 'is-invalid' : '' }}">{{ session('error_form') === 'tang' ? old('noi_dung') : '' }}</textarea>


                    @if(session('error_form') === 'tang')

                        @error('noi_dung')

                            <small class="all-point-error server-error">

                                <i class="fa-solid fa-circle-exclamation"></i>

                                {{ $message }}

                            </small>

                        @enderror

                    @endif

                </div>


                <button
                    type="submit"
                    class="all-point-submit gift">

                    <i class="fa-solid fa-gift"></i>

                    Tặng điểm

                </button>

            </form>

        </section>



        {{-- =====================================================
             FORM THU HỒI ĐIỂM
        ====================================================== --}}

        <section class="all-point-card">

            <div class="all-point-card-head">

                <span class="all-point-icon withdraw">

                    <i class="fa-solid fa-arrow-rotate-left"></i>

                </span>

                <div>

                    <h3>
                        Thu hồi điểm
                    </h3>

                    <p>
                        Trừ điểm của thành viên theo đối tượng được chọn
                    </p>

                </div>

            </div>


            <div class="all-point-warning">

                <i class="fa-solid fa-triangle-exclamation"></i>

                <span id="withdrawWarningText">

                    Điểm sẽ được thu hồi khỏi
                    <strong>tất cả thành viên</strong>.

                    Không để điểm bị âm.

                </span>

            </div>


            <form
                method="POST"
                action="{{ route('admin.thanh-vien.xu-ly-diem-hang-loat') }}"
                id="withdrawPointForm">

                @csrf

                <input
                    type="hidden"
                    name="loai"
                    value="thu_hoi">


                {{-- =================================================
                     ĐỐI TƯỢNG
                ================================================== --}}

                <div class="all-point-field">

                    <label>
                        Đối tượng được áp dụng
                        <span class="required">*</span>
                    </label>


                    <div
                        class="all-point-custom-select
                        {{ session('error_form') === 'thu_hoi' && $errors->has('doi_tuong_nhan') ? 'is-invalid' : '' }}"
                        data-audience-select
                        data-form="withdraw">

                        <input
                            type="hidden"
                            name="doi_tuong_nhan"
                            value="{{ session('error_form') === 'thu_hoi' ? old('doi_tuong_nhan', $oldAudience ?? 'all') : 'all' }}"
                            data-audience-value>


                        <button
                            type="button"
                            class="all-point-custom-trigger"
                            data-audience-trigger>

                            <span
                                class="all-point-current"
                                data-audience-current>

                                @php
                                    $withdrawAudience =
                                        session('error_form') === 'thu_hoi'
                                            ? old('doi_tuong_nhan', $oldAudience ?? 'all')
                                            : 'all';
                                @endphp

                                <span
                                    class="all-point-option-icon {{ $audienceGuide[$withdrawAudience]['class'] ?? 'audience-all' }}">

                                    <i class="fa-solid {{ $audienceGuide[$withdrawAudience]['icon'] ?? 'fa-globe' }}"></i>

                                </span>

                                <span class="all-point-current-text">

                                    {{ $audienceGuide[$withdrawAudience]['label'] ?? 'Tất cả thành viên' }}

                                </span>

                            </span>

                            <i class="fa-solid fa-chevron-down all-point-arrow"></i>

                        </button>


                        <div
                            class="all-point-custom-menu"
                            data-audience-menu>

                            @foreach ($audienceGuide as $value => $meta)

                                <button
                                    type="button"
                                    class="all-point-option
                                        {{ $withdrawAudience === $value ? 'is-selected' : '' }}"
                                    data-value="{{ $value }}"
                                    data-label="{{ $meta['label'] }}"
                                    data-icon="{{ $meta['icon'] }}"
                                    data-class="{{ $meta['class'] }}">

                                    <span class="all-point-option-icon {{ $meta['class'] }}">

                                        <i class="fa-solid {{ $meta['icon'] }}"></i>

                                    </span>

                                    <span class="all-point-option-content">

                                        <strong>
                                            {{ $meta['label'] }}
                                        </strong>

                                        <small>
                                            {{ $meta['description'] }}
                                        </small>

                                    </span>

                                    <i class="fa-solid fa-check all-point-check"></i>

                                </button>

                            @endforeach

                        </div>

                    </div>


                    @if(session('error_form') === 'thu_hoi')

                        @error('doi_tuong_nhan')

                            <small class="all-point-error server-error">

                                <i class="fa-solid fa-circle-exclamation"></i>

                                {{ $message }}

                            </small>

                        @enderror

                    @endif

                </div>


                {{-- =================================================
                     HẠNG
                ================================================== --}}

                <div
                    class="point-conditional
                    {{ $withdrawAudience === 'hang_thanh_vien' ? 'is-visible' : '' }}"
                    data-rank-wrapper="withdraw">

                    <div class="all-point-field">

                        <label>
                            Hạng thành viên được thu hồi
                            <span class="required">*</span>
                        </label>


                        <div
                            class="all-point-custom-select
                            {{ session('error_form') === 'thu_hoi' && $errors->has('hang_thanh_vien') ? 'is-invalid' : '' }}"
                            data-rank-select>

                            <input
                                type="hidden"
                                name="hang_thanh_vien"
                                value="{{ session('error_form') === 'thu_hoi' ? old('hang_thanh_vien', $oldRank ?? '') : '' }}"
                                data-rank-value>


                            <button
                                type="button"
                                class="all-point-custom-trigger"
                                data-rank-trigger>

                                <span
                                    class="all-point-current"
                                    data-rank-current>

                                    @php
                                        $withdrawRank =
                                            session('error_form') === 'thu_hoi'
                                                ? old('hang_thanh_vien', $oldRank ?? '')
                                                : '';
                                    @endphp

                                    @switch($withdrawRank)

                                        @case('member')

                                            <span class="all-point-option-icon rank-member">
                                                <i class="fa-solid fa-user"></i>
                                            </span>

                                            <span class="all-point-current-text">
                                                Member
                                            </span>

                                        @break


                                        @case('silver')

                                            <span class="all-point-option-icon rank-silver">
                                                <i class="fa-solid fa-medal"></i>
                                            </span>

                                            <span class="all-point-current-text">
                                                Silver
                                            </span>

                                        @break


                                        @case('gold')

                                            <span class="all-point-option-icon rank-gold">
                                                <i class="fa-solid fa-crown"></i>
                                            </span>

                                            <span class="all-point-current-text">
                                                Gold
                                            </span>

                                        @break


                                        @case('platinum')

                                            <span class="all-point-option-icon rank-platinum">
                                                <i class="fa-solid fa-gem"></i>
                                            </span>

                                            <span class="all-point-current-text">
                                                Platinum
                                            </span>

                                        @break


                                        @default

                                            <span class="all-point-option-icon rank-default">
                                                <i class="fa-solid fa-layer-group"></i>
                                            </span>

                                            <span class="all-point-current-text">
                                                -- Chọn hạng --
                                            </span>

                                    @endswitch

                                </span>

                                <i class="fa-solid fa-chevron-down all-point-arrow"></i>

                            </button>


                            <div
                                class="all-point-custom-menu"
                                data-rank-menu>


                                {{-- DEFAULT --}}

                                <button
                                    type="button"
                                    class="all-point-option {{ $withdrawRank === '' ? 'is-selected' : '' }}"
                                    data-value=""
                                    data-label="-- Chọn hạng --"
                                    data-icon="fa-layer-group"
                                    data-class="rank-default">

                                    <span class="all-point-option-icon rank-default">

                                        <i class="fa-solid fa-layer-group"></i>

                                    </span>

                                    <span class="all-point-option-content">

                                        <strong>
                                            -- Chọn hạng --
                                        </strong>

                                    </span>

                                    <i class="fa-solid fa-check all-point-check"></i>

                                </button>


                                {{-- MEMBER --}}

                                <button
                                    type="button"
                                    class="all-point-option {{ $withdrawRank === 'member' ? 'is-selected' : '' }}"
                                    data-value="member"
                                    data-label="Member"
                                    data-icon="fa-user"
                                    data-class="rank-member">

                                    <span class="all-point-option-icon rank-member">

                                        <i class="fa-solid fa-user"></i>

                                    </span>

                                    <span class="all-point-option-content">

                                        <strong>
                                            Member
                                        </strong>

                                        <small>
                                            Hạng thành viên cơ bản
                                        </small>

                                    </span>

                                    <i class="fa-solid fa-check all-point-check"></i>

                                </button>


                                {{-- SILVER --}}

                                <button
                                    type="button"
                                    class="all-point-option {{ $withdrawRank === 'silver' ? 'is-selected' : '' }}"
                                    data-value="silver"
                                    data-label="Silver"
                                    data-icon="fa-medal"
                                    data-class="rank-silver">

                                    <span class="all-point-option-icon rank-silver">

                                        <i class="fa-solid fa-medal"></i>

                                    </span>

                                    <span class="all-point-option-content">

                                        <strong>
                                            Silver
                                        </strong>

                                        <small>
                                            Hạng bạc
                                        </small>

                                    </span>

                                    <i class="fa-solid fa-check all-point-check"></i>

                                </button>


                                {{-- GOLD --}}

                                <button
                                    type="button"
                                    class="all-point-option {{ $withdrawRank === 'gold' ? 'is-selected' : '' }}"
                                    data-value="gold"
                                    data-label="Gold"
                                    data-icon="fa-crown"
                                    data-class="rank-gold">

                                    <span class="all-point-option-icon rank-gold">

                                        <i class="fa-solid fa-crown"></i>

                                    </span>

                                    <span class="all-point-option-content">

                                        <strong>
                                            Gold
                                        </strong>

                                        <small>
                                            Hạng vàng
                                        </small>

                                    </span>

                                    <i class="fa-solid fa-check all-point-check"></i>

                                </button>


                                {{-- PLATINUM --}}

                                <button
                                    type="button"
                                    class="all-point-option {{ $withdrawRank === 'platinum' ? 'is-selected' : '' }}"
                                    data-value="platinum"
                                    data-label="Platinum"
                                    data-icon="fa-gem"
                                    data-class="rank-platinum">

                                    <span class="all-point-option-icon rank-platinum">

                                        <i class="fa-solid fa-gem"></i>

                                    </span>

                                    <span class="all-point-option-content">

                                        <strong>
                                            Platinum
                                        </strong>

                                        <small>
                                            Hạng bạch kim
                                        </small>

                                    </span>

                                    <i class="fa-solid fa-check all-point-check"></i>

                                </button>

                            </div>

                        </div>


                        @if(session('error_form') === 'thu_hoi')

                            @error('hang_thanh_vien')

                                <small class="all-point-error server-error">

                                    <i class="fa-solid fa-circle-exclamation"></i>

                                    {{ $message }}

                                </small>

                            @enderror

                        @endif

                    </div>

                </div>


                {{-- =================================================
                     NGƯỜI DÙNG CỤ THỂ
                ================================================== --}}

                <div
                    class="point-conditional
                    {{ $withdrawAudience === 'nguoi_dung_cu_the' ? 'is-visible' : '' }}"
                    data-specific-wrapper="withdraw">

                    <div class="all-point-field">

                        <label>
                            Tìm người dùng
                            <span class="required">*</span>
                        </label>


                        <div class="point-user-search">

                            <i class="fa-solid fa-magnifying-glass"></i>

                            <input
                                type="text"
                                autocomplete="off"
                                placeholder="Nhập tên hoặc email để tìm..."
                                data-user-search="withdraw">

                        </div>


                        <div
                            class="point-user-results is-hidden"
                            data-user-results="withdraw">
                        </div>


                        <div
                            class="point-selected-users"
                            data-selected-users="withdraw">
                        </div>


                        <div data-selected-inputs="withdraw">

                            @if(session('error_form') === 'thu_hoi')

                                @foreach(old('nguoi_dung_cu_the', []) as $userId)

                                    <input
                                        type="hidden"
                                        name="nguoi_dung_cu_the[]"
                                        value="{{ $userId }}">

                                @endforeach

                            @endif

                        </div>


                        @if(session('error_form') === 'thu_hoi')

                            @error('nguoi_dung_cu_the')

                                <small class="all-point-error server-error">

                                    <i class="fa-solid fa-circle-exclamation"></i>

                                    {{ $message }}

                                </small>

                            @enderror

                        @endif

                    </div>

                </div>


                {{-- =================================================
                     SỐ ĐIỂM THU HỒI
                ================================================== --}}

                <div class="all-point-field">

                    <label>
                        Số điểm thu hồi
                        <span class="required">*</span>
                    </label>


                    <input
                        type="number"
                        name="so_diem"
                        step="1"
                        inputmode="numeric"
                        value="{{ session('error_form') === 'thu_hoi' ? old('so_diem') : '' }}"
                        placeholder="Ví dụ: 50"
                        class="{{ session('error_form') === 'thu_hoi' && $errors->has('so_diem') ? 'is-invalid' : '' }}">


                    @if(session('error_form') === 'thu_hoi')

                        @error('so_diem')

                            <small class="all-point-error server-error">

                                <i class="fa-solid fa-circle-exclamation"></i>

                                {{ $message }}

                            </small>

                        @enderror

                    @endif

                </div>


                {{-- =================================================
                     NỘI DUNG
                ================================================== --}}

                <div class="all-point-field">

                    <label>
                        Nội dung
                        <span class="required">*</span>
                    </label>


                    <textarea
                        name="noi_dung"
                        maxlength="255"
                        placeholder="Ví dụ: Thu hồi điểm tặng nhầm..."
                        class="{{ session('error_form') === 'thu_hoi' && $errors->has('noi_dung') ? 'is-invalid' : '' }}">{{ session('error_form') === 'thu_hoi' ? old('noi_dung') : '' }}</textarea>


                    @if(session('error_form') === 'thu_hoi')

                        @error('noi_dung')

                            <small class="all-point-error server-error">

                                <i class="fa-solid fa-circle-exclamation"></i>

                                {{ $message }}

                            </small>

                        @enderror

                    @endif

                </div>


                <button
                    type="submit"
                    class="all-point-submit withdraw">

                    <i class="fa-solid fa-arrow-rotate-left"></i>

                    Thu hồi điểm

                </button>

            </form>

        </section>

    </div>

</div>


{{-- =============================================================
     JAVASCRIPT
============================================================== --}}

<script>

document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | CẤU HÌNH
    |--------------------------------------------------------------------------
    */

    const MAX_POINTS = 100000;


    /*
    |--------------------------------------------------------------------------
    | ĐÓNG TẤT CẢ DROPDOWN
    |--------------------------------------------------------------------------
    */

    function closeAllDropdowns(except = null) {

        document
            .querySelectorAll('.all-point-custom-select.is-open')
            .forEach(function (dropdown) {

                if (dropdown !== except) {

                    dropdown.classList.remove('is-open');
                    dropdown.classList.remove('open-up');

                }

            });

    }


    /*
    |--------------------------------------------------------------------------
    | ĐIỀU CHỈNH VỊ TRÍ DROPDOWN
    |--------------------------------------------------------------------------
    */

    function adjustDropdownPosition(select) {

        if (!select) {
            return;
        }

        const trigger =
            select.querySelector('.all-point-custom-trigger');

        const menu =
            select.querySelector('.all-point-custom-menu');

        if (!trigger || !menu) {
            return;
        }

        select.classList.remove('open-up');

        const wasOpen =
            select.classList.contains('is-open');

        if (!wasOpen) {
            select.classList.add('is-open');
        }

        const triggerRect =
            trigger.getBoundingClientRect();

        const menuHeight =
            menu.offsetHeight;

        const spaceBelow =
            window.innerHeight - triggerRect.bottom;

        const spaceAbove =
            triggerRect.top;

        if (
            spaceBelow < menuHeight + 15 &&
            spaceAbove > spaceBelow
        ) {

            select.classList.add('open-up');

        }

        if (!wasOpen) {
            select.classList.remove('is-open');
        }

    }


    /*
    |--------------------------------------------------------------------------
    | CLIENT ERROR - SHOW
    |--------------------------------------------------------------------------
    */

    function showClientError(field, message) {

        if (!field) {
            return;
        }

        field.classList.add('is-invalid');

        const parent =
            field.closest('.all-point-field');

        if (!parent) {
            return;
        }

        let error =
            parent.querySelector('.client-validation-error');

        if (!error) {

            error =
                document.createElement('small');

            error.className =
                'all-point-error client-validation-error';

            parent.appendChild(error);

        }

        error.innerHTML = `
            <i class="fa-solid fa-circle-exclamation"></i>
            ${message}
        `;

    }


    /*
    |--------------------------------------------------------------------------
    | CLIENT ERROR - CLEAR
    |--------------------------------------------------------------------------
    */

    function clearClientError(field) {

        if (!field) {
            return;
        }

        field.classList.remove('is-invalid');

        const parent =
            field.closest('.all-point-field');

        if (!parent) {
            return;
        }

        const error =
            parent.querySelector('.client-validation-error');

        if (error) {

            error.remove();

        }

    }


    /*
    |--------------------------------------------------------------------------
    | CLEAR TOÀN BỘ CLIENT ERROR TRONG FORM
    |--------------------------------------------------------------------------
    */

    function clearFormClientErrors(form) {

        if (!form) {
            return;
        }

        form
            .querySelectorAll('.client-validation-error')
            .forEach(function (error) {

                error.remove();

            });

        form
            .querySelectorAll('.is-invalid')
            .forEach(function (field) {

                field.classList.remove('is-invalid');

            });

    }


    /*
    |--------------------------------------------------------------------------
    | DROPDOWN ĐỐI TƯỢNG
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('[data-audience-select]')
        .forEach(function (select) {

            const trigger =
                select.querySelector('[data-audience-trigger]');

            const menu =
                select.querySelector('[data-audience-menu]');

            const valueInput =
                select.querySelector('[data-audience-value]');

            const current =
                select.querySelector('[data-audience-current]');

            const form =
                select.dataset.form;


            if (!trigger || !menu || !valueInput || !current) {
                return;
            }


            trigger.addEventListener('click', function (e) {

                e.stopPropagation();

                const isOpen =
                    select.classList.contains('is-open');

                closeAllDropdowns();

                if (!isOpen) {

                    select.classList.add('is-open');

                    requestAnimationFrame(function () {

                        adjustDropdownPosition(select);

                    });

                }

            });


            menu
                .querySelectorAll('.all-point-option')
                .forEach(function (option) {

                    option.addEventListener('click', function (e) {

                        e.stopPropagation();

                        const value =
                            this.dataset.value;

                        const label =
                            this.dataset.label;

                        const icon =
                            this.dataset.icon;

                        const iconClass =
                            this.dataset.class;


                        valueInput.value =
                            value;


                        current.innerHTML = `
                            <span class="all-point-option-icon ${iconClass}">
                                <i class="fa-solid ${icon}"></i>
                            </span>

                            <span class="all-point-current-text">
                                ${label}
                            </span>
                        `;


                        menu
                            .querySelectorAll('.all-point-option')
                            .forEach(function (item) {

                                item.classList.remove('is-selected');

                            });


                        this.classList.add('is-selected');


                        const targetForm =
                            form === 'gift'
                                ? document.getElementById('giftPointForm')
                                : document.getElementById('withdrawPointForm');


                        clearFormClientErrors(targetForm);


                        closeAllDropdowns();


                        updateAudience(
                            form,
                            value
                        );

                    });

                });

        });


    /*
    |--------------------------------------------------------------------------
    | UPDATE ĐỐI TƯỢNG
    |--------------------------------------------------------------------------
    */

    function updateAudience(form, value) {

        const rankWrapper =
            document.querySelector(
                `[data-rank-wrapper="${form}"]`
            );

        const specificWrapper =
            document.querySelector(
                `[data-specific-wrapper="${form}"]`
            );


        if (!rankWrapper || !specificWrapper) {
            return;
        }


        rankWrapper.classList.remove('is-visible');

        specificWrapper.classList.remove('is-visible');


        if (value === 'hang_thanh_vien') {

            rankWrapper.classList.add('is-visible');

        }


        if (value === 'nguoi_dung_cu_the') {

            specificWrapper.classList.add('is-visible');

        }


        updateWarning(
            form,
            value
        );

    }


    /*
    |--------------------------------------------------------------------------
    | WARNING
    |--------------------------------------------------------------------------
    */

    function updateWarning(form, value) {

        const warning =
            document.getElementById(
                form === 'gift'
                    ? 'giftWarningText'
                    : 'withdrawWarningText'
            );


        if (!warning) {
            return;
        }


        if (value === 'all') {

            warning.innerHTML =
                form === 'gift'
                    ? 'Điểm sẽ được cộng cho <strong>tất cả thành viên</strong>.'
                    : 'Điểm sẽ được thu hồi khỏi <strong>tất cả thành viên</strong>. Không để điểm bị âm.';

            return;

        }


        if (value === 'hang_thanh_vien') {

            warning.innerHTML =
                form === 'gift'
                    ? 'Điểm sẽ được cộng cho <strong>thành viên thuộc hạng được chọn</strong>.'
                    : 'Điểm sẽ được thu hồi khỏi <strong>thành viên thuộc hạng được chọn</strong>. Không để điểm bị âm.';

            return;

        }


        if (value === 'nguoi_dung_cu_the') {

            warning.innerHTML =
                form === 'gift'
                    ? 'Điểm sẽ được cộng cho <strong>những người dùng được chọn</strong>.'
                    : 'Điểm sẽ được thu hồi khỏi <strong>những người dùng được chọn</strong>. Không để điểm bị âm.';

        }

    }


    /*
    |--------------------------------------------------------------------------
    | DROPDOWN HẠNG
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('[data-rank-select]')
        .forEach(function (select) {

            const trigger =
                select.querySelector('[data-rank-trigger]');

            const menu =
                select.querySelector('[data-rank-menu]');

            const valueInput =
                select.querySelector('[data-rank-value]');

            const current =
                select.querySelector('[data-rank-current]');


            if (!trigger || !menu || !valueInput || !current) {
                return;
            }


            trigger.addEventListener('click', function (e) {

                e.stopPropagation();

                const isOpen =
                    select.classList.contains('is-open');

                closeAllDropdowns();

                if (!isOpen) {

                    select.classList.add('is-open');

                    requestAnimationFrame(function () {

                        adjustDropdownPosition(select);

                    });

                }

            });


            menu
                .querySelectorAll('.all-point-option')
                .forEach(function (option) {

                    option.addEventListener('click', function (e) {

                        e.stopPropagation();

                        const value =
                            this.dataset.value;

                        const label =
                            this.dataset.label;

                        const icon =
                            this.dataset.icon;

                        const iconClass =
                            this.dataset.class;


                        valueInput.value =
                            value;


                        current.innerHTML = `
                            <span class="all-point-option-icon ${iconClass}">
                                <i class="fa-solid ${icon}"></i>
                            </span>

                            <span class="all-point-current-text">
                                ${label}
                            </span>
                        `;


                        menu
                            .querySelectorAll('.all-point-option')
                            .forEach(function (item) {

                                item.classList.remove('is-selected');

                            });


                        this.classList.add('is-selected');


                        const form =
                            select.closest('form');

                        clearFormClientErrors(form);

                        closeAllDropdowns();

                    });

                });

        });


    /*
    |--------------------------------------------------------------------------
    | DROPDOWN TÍNH HẠNG
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('[data-tinh-hang-select]')
        .forEach(function (select) {

            const trigger =
                select.querySelector('[data-tinh-hang-trigger]');

            const menu =
                select.querySelector('[data-tinh-hang-menu]');

            const valueInput =
                select.querySelector('[data-tinh-hang-value]');

            const current =
                select.querySelector('[data-tinh-hang-current]');


            if (!trigger || !menu || !valueInput || !current) {
                return;
            }


            trigger.addEventListener('click', function (e) {

                e.stopPropagation();

                const isOpen =
                    select.classList.contains('is-open');

                closeAllDropdowns();

                if (!isOpen) {

                    select.classList.add('is-open');

                    requestAnimationFrame(function () {

                        adjustDropdownPosition(select);

                    });

                }

            });


            menu
                .querySelectorAll('.all-point-option')
                .forEach(function (option) {

                    option.addEventListener('click', function (e) {

                        e.stopPropagation();

                        const value =
                            this.dataset.value;

                        const label =
                            this.dataset.label;

                        const icon =
                            this.dataset.icon;

                        const color =
                            this.dataset.inlineColor || '#22c55e';


                        valueInput.value =
                            value;


                        current.innerHTML = `
                            <span
                                class="all-point-option-icon"
                                style="
                                    color:${color};
                                    background:rgba(255,255,255,.06);
                                "
                            >
                                <i class="fa-solid ${icon}"></i>
                            </span>

                            <span class="all-point-current-text">
                                ${label}
                            </span>
                        `;


                        menu
                            .querySelectorAll('.all-point-option')
                            .forEach(function (item) {

                                item.classList.remove('is-selected');

                            });


                        this.classList.add('is-selected');


                        const form =
                            select.closest('form');

                        clearFormClientErrors(form);

                        closeAllDropdowns();

                    });

                });

        });


    /*
    |--------------------------------------------------------------------------
    | USER ĐƯỢC CHỌN
    |--------------------------------------------------------------------------
    */

    const selectedUsers = {

        gift: new Map(),

        withdraw: new Map()

    };


    /*
    |--------------------------------------------------------------------------
    | KHÔI PHỤC USER SAU VALIDATION - TẶNG
    |--------------------------------------------------------------------------
    */

    @if(session('error_form') === 'tang')

        @foreach(old('nguoi_dung_cu_the', []) as $userId)

            selectedUsers.gift.set(
                "{{ $userId }}",
                {
                    id: "{{ $userId }}",
                    name: "Người dùng đã chọn",
                    email: ""
                }
            );

        @endforeach

    @endif


    /*
    |--------------------------------------------------------------------------
    | KHÔI PHỤC USER SAU VALIDATION - THU HỒI
    |--------------------------------------------------------------------------
    */

    @if(session('error_form') === 'thu_hoi')

        @foreach(old('nguoi_dung_cu_the', []) as $userId)

            selectedUsers.withdraw.set(
                "{{ $userId }}",
                {
                    id: "{{ $userId }}",
                    name: "Người dùng đã chọn",
                    email: ""
                }
            );

        @endforeach

    @endif


    /*
    |--------------------------------------------------------------------------
    | TÌM NGƯỜI DÙNG
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('[data-user-search]')
        .forEach(function (input) {

            const form =
                input.dataset.userSearch;

            const resultBox =
                document.querySelector(
                    `[data-user-results="${form}"]`
                );

            const selectedBox =
                document.querySelector(
                    `[data-selected-users="${form}"]`
                );

            const hiddenBox =
                document.querySelector(
                    `[data-selected-inputs="${form}"]`
                );


            let timer = null;


            if (!resultBox || !selectedBox || !hiddenBox) {
                return;
            }


            input.addEventListener('input', function () {

                const keyword =
                    this.value.trim();

                clearTimeout(timer);


                /*
                |----------------------------------------------------------
                | XÓA LỖI KHI NGƯỜI DÙNG BẮT ĐẦU NHẬP
                |----------------------------------------------------------
                */

                const targetForm =
                    form === 'gift'
                        ? document.getElementById('giftPointForm')
                        : document.getElementById('withdrawPointForm');


                clearFormClientErrors(targetForm);


                if (keyword.length < 2) {

                    resultBox.classList.add('is-hidden');

                    resultBox.innerHTML = '';

                    return;

                }


                timer = setTimeout(function () {

                    fetch(
                        '{{ route("admin.thong-bao-push.tim-nguoi-dung") }}?keyword=' +
                        encodeURIComponent(keyword)
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

                        resultBox.innerHTML = '';


                        if (
                            !Array.isArray(users) ||
                            users.length === 0
                        ) {

                            resultBox.innerHTML = `
                                <div
                                    style="
                                        padding:14px;
                                        color:#7f8795;
                                        font-size:12px;
                                        text-align:center;
                                    "
                                >
                                    Không tìm thấy người dùng.
                                </div>
                            `;

                            resultBox.classList.remove(
                                'is-hidden'
                            );

                            return;

                        }


                        users.forEach(function (user) {

                            if (
                                selectedUsers[form]
                                    .has(String(user.id))
                            ) {

                                return;

                            }


                            const button =
                                document.createElement('button');


                            button.type =
                                'button';


                            button.className =
                                'point-user-result';


                            const userName =
                                user.name ??
                                user.ho_ten ??
                                'Người dùng';


                            const userEmail =
                                user.email ??
                                '';


                            button.innerHTML = `

                                <span class="point-user-avatar">

                                    <i class="fa-solid fa-user"></i>

                                </span>


                                <span class="point-user-info">

                                    <strong>
                                        ${escapeHtml(userName)}
                                    </strong>

                                    <small>
                                        ${escapeHtml(userEmail)}
                                    </small>

                                </span>

                            `;


                            button.addEventListener(
                                'click',
                                function () {

                                    addUser(
                                        form,
                                        user
                                    );


                                    input.value =
                                        '';


                                    resultBox.classList.add(
                                        'is-hidden'
                                    );


                                    resultBox.innerHTML =
                                        '';

                                }
                            );


                            resultBox.appendChild(
                                button
                            );

                        });


                        resultBox.classList.remove(
                            'is-hidden'
                        );

                    })

                    .catch(function () {

                        resultBox.innerHTML = `

                            <div
                                style="
                                    padding:14px;
                                    color:#ff6570;
                                    font-size:12px;
                                    text-align:center;
                                "
                            >
                                Có lỗi khi tìm người dùng.
                            </div>

                        `;


                        resultBox.classList.remove(
                            'is-hidden'
                        );

                    });

                }, 300);

            });

        });


    /*
    |--------------------------------------------------------------------------
    | ESCAPE HTML
    |--------------------------------------------------------------------------
    */

    function escapeHtml(value) {

        if (value === null || value === undefined) {
            return '';
        }

        const div =
            document.createElement('div');

        div.textContent =
            String(value);

        return div.innerHTML;

    }


    /*
    |--------------------------------------------------------------------------
    | ADD USER
    |--------------------------------------------------------------------------
    */

    function addUser(form, user) {

        const id =
            String(user.id);


        if (
            selectedUsers[form].has(id)
        ) {

            return;

        }


        selectedUsers[form].set(
            id,
            user
        );


        renderSelectedUsers(
            form
        );


        const targetForm =
            form === 'gift'
                ? document.getElementById('giftPointForm')
                : document.getElementById('withdrawPointForm');


        clearFormClientErrors(
            targetForm
        );

    }


    /*
    |--------------------------------------------------------------------------
    | REMOVE USER
    |--------------------------------------------------------------------------
    */

    function removeUser(form, id) {

        selectedUsers[form].delete(
            String(id)
        );


        renderSelectedUsers(
            form
        );

    }


    /*
    |--------------------------------------------------------------------------
    | RENDER USER ĐÃ CHỌN
    |--------------------------------------------------------------------------
    */

    function renderSelectedUsers(form) {

        const selectedBox =
            document.querySelector(
                `[data-selected-users="${form}"]`
            );

        const hiddenBox =
            document.querySelector(
                `[data-selected-inputs="${form}"]`
            );


        if (!selectedBox || !hiddenBox) {
            return;
        }


        selectedBox.innerHTML =
            '';

        hiddenBox.innerHTML =
            '';


        selectedUsers[form]
            .forEach(function (user) {

                const id =
                    String(user.id);


                const name =
                    user.name ??
                    user.ho_ten ??
                    'Người dùng';


                const email =
                    user.email ??
                    '';


                const item =
                    document.createElement('div');


                item.className =
                    'point-selected-user';


                item.innerHTML = `

                    <span class="point-user-avatar">

                        <i class="fa-solid fa-user"></i>

                    </span>


                    <span class="point-selected-user-info">

                        <strong>
                            ${escapeHtml(name)}
                        </strong>

                        <small>
                            ${escapeHtml(email)}
                        </small>

                    </span>


                    <button
                        type="button"
                        class="point-remove-user"
                        title="Bỏ chọn">

                        <i class="fa-solid fa-xmark"></i>

                    </button>

                `;


                item
                    .querySelector('.point-remove-user')
                    .addEventListener(
                        'click',
                        function () {

                            removeUser(
                                form,
                                id
                            );

                        }
                    );


                selectedBox.appendChild(
                    item
                );


                const hidden =
                    document.createElement('input');


                hidden.type =
                    'hidden';


                hidden.name =
                    'nguoi_dung_cu_the[]';


                hidden.value =
                    id;


                hiddenBox.appendChild(
                    hidden
                );

            });

    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE SỐ ĐIỂM
    |--------------------------------------------------------------------------
    */

    function validatePoints(points) {

        if (!points) {
            return false;
        }


        const rawValue =
            points.value.trim();


        /*
        |--------------------------------------------------------------
        | BỎ TRỐNG
        |--------------------------------------------------------------
        */

        if (rawValue === '') {

            showClientError(
                points,
                'Vui lòng nhập số điểm.'
            );

            return false;

        }


        /*
        |--------------------------------------------------------------
        | CHỈ CHO PHÉP SỐ NGUYÊN
        |--------------------------------------------------------------
        */

        if (!/^\d+$/.test(rawValue)) {

            showClientError(
                points,
                'Số điểm phải là số nguyên.'
            );

            return false;

        }


        const value =
            Number(rawValue);


        /*
        |--------------------------------------------------------------
        | KIỂM TRA SỐ AN TOÀN
        |--------------------------------------------------------------
        */

        if (!Number.isSafeInteger(value)) {

            showClientError(
                points,
                'Số điểm không hợp lệ.'
            );

            return false;

        }


        /*
        |--------------------------------------------------------------
        | MIN
        |--------------------------------------------------------------
        */

        if (value < 1) {

            showClientError(
                points,
                'Số điểm phải lớn hơn 0.'
            );

            return false;

        }


        /*
        |--------------------------------------------------------------
        | MAX = 100000
        |--------------------------------------------------------------
        */

        if (value > MAX_POINTS) {

            showClientError(
                points,
                'Số điểm tối đa được phép là 100.000 điểm.'
            );

            return false;

        }


        /*
        |--------------------------------------------------------------
        | HỢP LỆ
        |--------------------------------------------------------------
        */

        clearClientError(points);

        return true;

    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE FORM
    |--------------------------------------------------------------------------
    */

    function validatePointForm(form) {

        let valid = true;


        if (!form) {
            return false;
        }


        /*
        |--------------------------------------------------------------
        | XÓA LỖI CLIENT CŨ
        |--------------------------------------------------------------
        */

        clearFormClientErrors(form);


        /*
        |--------------------------------------------------------------
        | LẤY DỮ LIỆU TRONG FORM HIỆN TẠI
        |--------------------------------------------------------------
        */

        const audience =
            form.querySelector(
                '[data-audience-value]'
            )?.value || 'all';


        const rank =
            form.querySelector(
                '[data-rank-value]'
            )?.value || '';


        const points =
            form.querySelector(
                '[name="so_diem"]'
            );


        const content =
            form.querySelector(
                '[name="noi_dung"]'
            );


        const formType =
            form.id === 'giftPointForm'
                ? 'gift'
                : 'withdraw';


        /*
        |--------------------------------------------------------------
        | SỐ ĐIỂM
        |--------------------------------------------------------------
        */

        if (!validatePoints(points)) {

            valid = false;

        }


        /*
        |--------------------------------------------------------------
        | NỘI DUNG
        |--------------------------------------------------------------
        */

        if (!content.value.trim()) {

            showClientError(
                content,
                'Vui lòng nhập nội dung.'
            );

            valid = false;

        }


        /*
        |--------------------------------------------------------------
        | GIỚI HẠN NỘI DUNG
        |--------------------------------------------------------------
        */

        if (content.value.trim().length > 255) {

            showClientError(
                content,
                'Nội dung không được vượt quá 255 ký tự.'
            );

            valid = false;

        }


        /*
        |--------------------------------------------------------------
        | HẠNG THÀNH VIÊN
        |--------------------------------------------------------------
        */

        if (
            audience === 'hang_thanh_vien' &&
            !rank
        ) {

            const rankInput =
                form.querySelector(
                    '[data-rank-value]'
                );


            const rankSelect =
                rankInput?.closest(
                    '.all-point-custom-select'
                );


            if (rankSelect) {

                rankSelect.classList.add(
                    'is-invalid'
                );


                const parent =
                    rankSelect.closest(
                        '.all-point-field'
                    );


                if (parent) {

                    let error =
                        parent.querySelector(
                            '.client-validation-error'
                        );


                    if (!error) {

                        error =
                            document.createElement(
                                'small'
                            );


                        error.className =
                            'all-point-error client-validation-error';


                        parent.appendChild(
                            error
                        );

                    }


                    error.innerHTML = `

                        <i class="fa-solid fa-circle-exclamation"></i>

                        Vui lòng chọn hạng thành viên.

                    `;

                }

            }


            valid = false;

        }


        /*
        |--------------------------------------------------------------
        | NGƯỜI DÙNG CỤ THỂ
        |--------------------------------------------------------------
        */

        if (
            audience === 'nguoi_dung_cu_the'
        ) {

            if (
                selectedUsers[formType].size === 0
            ) {

                const specificWrapper =
                    form.querySelector(
                        '[data-specific-wrapper].is-visible'
                    );


                if (specificWrapper) {

                    const field =
                        specificWrapper.querySelector(
                            '.point-user-search input'
                        );


                    showClientError(
                        field,
                        'Vui lòng chọn ít nhất một người dùng.'
                    );

                }


                valid = false;

            }

        }


        return valid;

    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATION REALTIME SỐ ĐIỂM
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('[name="so_diem"]')
        .forEach(function (pointsInput) {


            /*
            |--------------------------------------------------------------
            | INPUT
            |--------------------------------------------------------------
            */

            pointsInput.addEventListener(
                'input',
                function () {

                    const rawValue =
                        this.value.trim();


                    /*
                    |------------------------------------------------------
                    | CHƯA NHẬP
                    |------------------------------------------------------
                    */

                    if (rawValue === '') {

                        clearClientError(this);

                        return;

                    }


                    /*
                    |------------------------------------------------------
                    | CÓ KÝ TỰ KHÔNG PHẢI SỐ
                    |------------------------------------------------------
                    */

                    if (!/^\d+$/.test(rawValue)) {

                        showClientError(
                            this,
                            'Số điểm phải là số nguyên.'
                        );

                        return;

                    }


                    const value =
                        Number(rawValue);


                    /*
                    |------------------------------------------------------
                    | QUÁ LỚN
                    |------------------------------------------------------
                    */

                    if (
                        !Number.isSafeInteger(value)
                    ) {

                        showClientError(
                            this,
                            'Số điểm không hợp lệ.'
                        );

                        return;

                    }


                    /*
                    |------------------------------------------------------
                    | NHỎ HƠN 1
                    |------------------------------------------------------
                    */

                    if (value < 1) {

                        showClientError(
                            this,
                            'Số điểm phải lớn hơn 0.'
                        );

                        return;

                    }


                    /*
                    |------------------------------------------------------
                    | VƯỢT MAX
                    |------------------------------------------------------
                    */

                    if (value > MAX_POINTS) {

                        showClientError(
                            this,
                            'Số điểm tối đa được phép là 100.000 điểm.'
                        );

                        return;

                    }


                    /*
                    |------------------------------------------------------
                    | HỢP LỆ
                    |------------------------------------------------------
                    */

                    clearClientError(this);

                }
            );


            /*
            |--------------------------------------------------------------
            | BLUR
            |--------------------------------------------------------------
            */

            pointsInput.addEventListener(
                'blur',
                function () {

                    validatePoints(this);

                }
            );

        });


    /*
    |--------------------------------------------------------------------------
    | VALIDATION REALTIME NỘI DUNG
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('[name="noi_dung"]')
        .forEach(function (textarea) {

            textarea.addEventListener(
                'input',
                function () {

                    const value =
                        this.value.trim();


                    if (value === '') {

                        clearClientError(this);

                        return;

                    }


                    if (value.length > 255) {

                        showClientError(
                            this,
                            'Nội dung không được vượt quá 255 ký tự.'
                        );

                        return;

                    }


                    clearClientError(this);

                }
            );


            textarea.addEventListener(
                'blur',
                function () {

                    if (!this.value.trim()) {

                        showClientError(
                            this,
                            'Vui lòng nhập nội dung.'
                        );

                    }

                }
            );

        });


    /*
    |--------------------------------------------------------------------------
    | SUBMIT - TẶNG ĐIỂM
    |--------------------------------------------------------------------------
    */

    document
        .getElementById('giftPointForm')
        ?.addEventListener(
            'submit',
            function (e) {

                const form =
                    this;


                /*
                |----------------------------------------------------------
                | VALIDATE
                |----------------------------------------------------------
                */

                if (
                    !validatePointForm(form)
                ) {

                    e.preventDefault();

                    return;

                }


                const audience =
                    form.querySelector(
                        '[data-audience-value]'
                    )?.value || 'all';


                const rank =
                    form.querySelector(
                        '[data-rank-value]'
                    )?.value || '';


                const points =
                    form.querySelector(
                        '[name="so_diem"]'
                    )?.value || 0;


                let audienceText =
                    'tất cả thành viên';


                /*
                |----------------------------------------------------------
                | THEO HẠNG
                |----------------------------------------------------------
                */

                if (
                    audience === 'hang_thanh_vien'
                ) {

                    const rankNames = {

                        member: 'Member',
                        silver: 'Silver',
                        gold: 'Gold',
                        platinum: 'Platinum'

                    };


                    audienceText =
                        'thành viên thuộc hạng ' +
                        (
                            rankNames[rank] ||
                            rank
                        );

                }


                /*
                |----------------------------------------------------------
                | USER CỤ THỂ
                |----------------------------------------------------------
                */

                if (
                    audience === 'nguoi_dung_cu_the'
                ) {

                    const count =
                        selectedUsers.gift.size;


                    audienceText =
                        count +
                        ' người dùng được chọn';

                }


                /*
                |----------------------------------------------------------
                | CONFIRM
                |----------------------------------------------------------
                */

                const confirmed =
                    window.confirm(
                        'Bạn có chắc chắn muốn TẶNG ' +
                        Number(points).toLocaleString('vi-VN') +
                        ' điểm cho ' +
                        audienceText +
                        '?'
                    );


                if (!confirmed) {

                    e.preventDefault();

                }

            }
        );


    /*
    |--------------------------------------------------------------------------
    | SUBMIT - THU HỒI ĐIỂM
    |--------------------------------------------------------------------------
    */

    document
        .getElementById('withdrawPointForm')
        ?.addEventListener(
            'submit',
            function (e) {

                const form =
                    this;


                /*
                |----------------------------------------------------------
                | VALIDATE
                |----------------------------------------------------------
                */

                if (
                    !validatePointForm(form)
                ) {

                    e.preventDefault();

                    return;

                }


                const audience =
                    form.querySelector(
                        '[data-audience-value]'
                    )?.value || 'all';


                const rank =
                    form.querySelector(
                        '[data-rank-value]'
                    )?.value || '';


                const points =
                    form.querySelector(
                        '[name="so_diem"]'
                    )?.value || 0;


                let audienceText =
                    'tất cả thành viên';


                /*
                |----------------------------------------------------------
                | THEO HẠNG
                |----------------------------------------------------------
                */

                if (
                    audience === 'hang_thanh_vien'
                ) {

                    const rankNames = {

                        member: 'Member',
                        silver: 'Silver',
                        gold: 'Gold',
                        platinum: 'Platinum'

                    };


                    audienceText =
                        'thành viên thuộc hạng ' +
                        (
                            rankNames[rank] ||
                            rank
                        );

                }


                /*
                |----------------------------------------------------------
                | USER CỤ THỂ
                |----------------------------------------------------------
                */

                if (
                    audience === 'nguoi_dung_cu_the'
                ) {

                    const count =
                        selectedUsers.withdraw.size;


                    audienceText =
                        count +
                        ' người dùng được chọn';

                }


                /*
                |----------------------------------------------------------
                | CONFIRM
                |----------------------------------------------------------
                */

                const confirmed =
                    window.confirm(
                        'Bạn có chắc chắn muốn THU HỒI ' +
                        Number(points).toLocaleString('vi-VN') +
                        ' điểm khỏi ' +
                        audienceText +
                        '?\n\n' +
                        'Điểm của thành viên sẽ không bị âm.'
                    );


                if (!confirmed) {

                    e.preventDefault();

                }

            }
        );


    /*
    |--------------------------------------------------------------------------
    | CLICK RA NGOÀI
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        function () {

            closeAllDropdowns();


            document
                .querySelectorAll('.point-user-results')
                .forEach(function (box) {

                    box.classList.add(
                        'is-hidden'
                    );

                });

        }
    );


    /*
    |--------------------------------------------------------------------------
    | KHÔNG ĐÓNG USER RESULTS KHI CLICK BÊN TRONG
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('.point-user-results')
        .forEach(function (box) {

            box.addEventListener(
                'click',
                function (e) {

                    e.stopPropagation();

                }
            );

        });


    /*
    |--------------------------------------------------------------------------
    | KHÔNG ĐÓNG SEARCH KHI CLICK INPUT
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('[data-user-search]')
        .forEach(function (input) {

            input.addEventListener(
                'click',
                function (e) {

                    e.stopPropagation();

                }
            );

        });


    /*
    |--------------------------------------------------------------------------
    | KHỞI TẠO AUDIENCE
    |--------------------------------------------------------------------------
    */

    const giftAudienceInput =
        document.querySelector(
            '[data-audience-select][data-form="gift"] [data-audience-value]'
        );


    const withdrawAudienceInput =
        document.querySelector(
            '[data-audience-select][data-form="withdraw"] [data-audience-value]'
        );


    updateAudience(
        'gift',
        giftAudienceInput?.value || 'all'
    );


    updateAudience(
        'withdraw',
        withdrawAudienceInput?.value || 'all'
    );


    /*
    |--------------------------------------------------------------------------
    | RENDER USER CŨ
    |--------------------------------------------------------------------------
    */

    renderSelectedUsers('gift');

    renderSelectedUsers('withdraw');


    /*
    |--------------------------------------------------------------------------
    | RESIZE
    |--------------------------------------------------------------------------
    */

    window.addEventListener(
        'resize',
        function () {

            closeAllDropdowns();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | ESC
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'keydown',
        function (e) {

            if (e.key === 'Escape') {

                closeAllDropdowns();

                document
                    .querySelectorAll('.point-user-results')
                    .forEach(function (box) {

                        box.classList.add(
                            'is-hidden'
                        );

                    });

            }

        }
    );

});

</script>

@endsection