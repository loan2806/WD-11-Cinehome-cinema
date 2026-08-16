@extends('layouts.admin')

@section('page-title', 'Tạo thông báo mới')

@section('content')

@php
/*
|--------------------------------------------------------------------------
| Đối tượng nhận
|--------------------------------------------------------------------------
*/

$audienceGuide = [
'all' => [
'label' => 'Tất cả người dùng',
'icon' => 'fa-globe',
'class' => 'is-all',
],

'khach_hang' => [
'label' => 'Khách hàng',
'icon' => 'fa-users',
'class' => 'is-user',
],

'nhan_vien' => [
'label' => 'Nhân viên',
'icon' => 'fa-user-tie',
'class' => 'is-staff',
],

'quan_ly' => [
'label' => 'Quản lý',
'icon' => 'fa-user-shield',
'class' => 'is-manager',
],

'hang_thanh_vien' => [
'label' => 'Theo hạng thành viên',
'icon' => 'fa-ranking-star',
'class' => 'is-member',
],

'nguoi_dung_cu_the' => [
'label' => 'Người dùng cụ thể',
'icon' => 'fa-user-pen',
'class' => 'is-specific',
],
];

$selectedAudience = old('doi_tuong_nhan', '');
$selectedLoai = old('loai', '');
$selectedHangThanhVien = old('hang_thanh_vien');
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

                <label class="push-field push-field--full">

                    <span>
                        Loại thông báo
                        <em>*</em>
                    </span>

                    <select
                        name="loai"
                        id="loai"
                        class="@error('loai') is-invalid @enderror">
                        <option value="">-- Chọn loại thông báo --</option>

                        @foreach ($loaiOptions as $value => $label)

                        <option
                            value="{{ $value }}"
                            @selected($selectedLoai===$value)>
                            {{ $label }}
                        </option>

                        @endforeach

                    </select>

                    @error('loai')
                    <small>{{ $message }}</small>
                    @enderror

                </label>


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
                     ĐỐI TƯỢNG NHẬN
                ================================================== --}}

                <label class="push-field push-field--full">

                    <span>
                        Đối tượng nhận
                        <em>*</em>
                    </span>

                    <select
                        name="doi_tuong_nhan"
                        id="doi_tuong_nhan"
                        class="@error('doi_tuong_nhan') is-invalid @enderror">

                        <option value="">-- Chọn đối tượng nhận --</option>

                        @foreach ($doiTuongOptions as $value => $label)

                        <option
                            value="{{ $value }}"
                            @selected($selectedAudience===$value)>
                            {{ $label }}
                        </option>

                        @endforeach

                    </select>

                    @error('doi_tuong_nhan')
                    <small>{{ $message }}</small>
                    @enderror

                </label>

                <div
                    id="hang_thanh_vien_wrapper"
                    class="push-specific-box push-field--full
        {{ $selectedAudience === 'hang_thanh_vien' ? '' : 'is-hidden' }}">
                    <label class="push-field">

                        <span>
                            Hạng thành viên
                            <em>*</em>
                        </span>

                        <select
                            name="hang_thanh_vien"
                            id="hang_thanh_vien"
                            class="@error('hang_thanh_vien') is-invalid @enderror">
                            <option value="">-- Chọn hạng thành viên --</option>

                            @foreach ($hangThanhVienOptions as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected($selectedHangThanhVien===$value)>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>

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
                                placeholder="Nhập tên hoặc email..."
                                autocomplete="off"
                                value=""
                                class="@error('nguoi_dung_cu_the') is-invalid @enderror">

                        </div>

                        @error('nguoi_dung_cu_the')
                        <small>{{ $message }}</small>
                        @enderror

                    </label>


                    {{-- KẾT QUẢ --}}
                    <div
                        id="ket_qua_nguoi_dung"
                        class="push-user-results is-hidden"></div>


                    {{-- NGƯỜI DÙNG ĐÃ CHỌN --}}
                    <div
                        id="nguoi_dung_da_chon"
                        class="push-selected-user is-hidden">

                        <div class="push-selected-user-info">

                            <span class="push-selected-user-icon">
                                <i class="fa-solid fa-user"></i>
                            </span>

                            <div>

                                <strong id="selectedUserName"></strong>

                                <small id="selectedUserEmail"></small>

                            </div>

                        </div>


                        <button
                            type="button"
                            id="removeSelectedUser"
                            title="Bỏ chọn người dùng">
                            <i class="fa-solid fa-xmark"></i>
                        </button>

                    </div>


                    {{-- ID NGƯỜI DÙNG --}}
                    <input
                        type="hidden"
                        name="nguoi_dung_cu_the"
                        id="nguoi_dung_cu_the"
                        value="{{ old('nguoi_dung_cu_the') }}">

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

                {{-- LƯU NHÁP --}}
                <button
                    type="submit"
                    name="action"
                    value="draft"
                    class="push-secondary-btn">

                    <i class="fa-solid fa-file-pen"></i>
                    Lưu nháp

                </button>

                {{-- GỬI THÔNG BÁO --}}
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

                    <span class="push-preview-icon">

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

                            <i class="fa-solid fa-bell"></i>

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
                    $count = $audienceCounts['nguoi_dung_cu_the']
                    ?? $audienceCounts['all']
                    ?? 0;
                    } else {
                    $count = $audienceCounts[$value] ?? 0;
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
    }

    .push-field>span {
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
    .push-field select.is-invalid {
        border-color: #ff3045;
    }

    .push-field small {
        color: #ff6570;
        font-size: 12px;
    }

    /* SEARCH */

    .push-specific-box {
        background: rgba(37, 29, 25, .8);
        border: 1px solid rgba(245, 166, 35, .28);
        border-radius: 18px;
        padding: 18px;
    }

    .push-specific-box.is-hidden {
        display: none;
    }

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
        max-height: 240px;
        overflow-y: auto;
        border-radius: 14px;
        background: #181a20;
        border: 1px solid rgba(255, 255, 255, .08);
    }

    .push-user-results.is-hidden {
        display: none;
    }

    .push-user-result {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 15px;
        cursor: pointer;
        transition: .15s;
        border-bottom: 1px solid rgba(255, 255, 255, .05);
    }

    .push-user-result:last-child {
        border-bottom: 0;
    }

    .push-user-result:hover {
        background: rgba(255, 255, 255, .06);
    }

    .push-user-avatar {
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #ff3045, #f5a623);
        color: white;
    }

    .push-user-result-info {
        min-width: 0;
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

    .push-no-result {
        padding: 18px;
        text-align: center;
        color: #777d89;
        font-size: 13px;
    }

    /* SELECTED USER */

    .push-selected-user {
        margin-top: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px;
        border-radius: 14px;
        background: rgba(255, 48, 69, .08);
        border: 1px solid rgba(255, 48, 69, .22);
    }

    .push-selected-user.is-hidden {
        display: none;
    }

    .push-selected-user-info {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .push-selected-user-icon {
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #ff3045;
        color: white;
    }

    .push-selected-user-info strong {
        display: block;
        color: white;
        font-size: 13px;
    }

    .push-selected-user-info small {
        display: block;
        color: #9096a3;
        margin-top: 3px;
        font-size: 12px;
    }

    #removeSelectedUser {
        width: 34px;
        height: 34px;
        border: 0;
        border-radius: 10px;
        background: rgba(255, 255, 255, .07);
        color: #aaa;
        cursor: pointer;
    }

    #removeSelectedUser:hover {
        background: rgba(255, 48, 69, .15);
        color: #ff5361;
    }

    .push-specific-help {
        margin: 12px 0 0;
        color: #999faa;
        font-size: 12px;
    }

    .push-specific-help i {
        color: #f5a623;
        margin-right: 5px;
    }

    /* BUTTON */

    .push-form-actions {
        padding: 20px 26px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        border-top: 1px solid rgba(255, 255, 255, .07);
    }

    .push-soft-btn,
    .push-primary-btn {
        height: 44px;
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
    }

    .push-soft-btn {
        background: rgba(255, 255, 255, .07);
        color: #d7dae0;
    }

    .push-soft-btn:hover {
        background: rgba(255, 255, 255, .11);
        color: white;
    }

    .push-primary-btn {
        background: linear-gradient(135deg, #ff3045, #e51f36);
        color: white;
        box-shadow: 0 10px 25px rgba(255, 48, 69, .18);
    }

    .push-primary-btn:hover {
        transform: translateY(-1px);
    }

    /* PREVIEW */

    .push-phone-preview {
        background: #11141a;
        border: 1px solid rgba(255, 255, 255, .08);
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
        border: 1px solid rgba(255, 255, 255, .08);
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

    /* CHIP */

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
        background: rgba(59, 130, 246, .12);
        color: #72a8ff;
    }

    .push-chip.is-all {
        background: rgba(245, 166, 35, .12);
        color: #f5a623;
    }

    .push-chip.is-user {
        background: rgba(34, 197, 94, .12);
        color: #4ade80;
    }

    .push-chip.is-staff {
        background: rgba(59, 130, 246, .12);
        color: #60a5fa;
    }

    .push-chip.is-manager {
        background: rgba(168, 85, 247, .12);
        color: #c084fc;
    }

    .push-chip.is-specific {
        background: rgba(236, 72, 153, .12);
        color: #f472b6;
    }

    /* AUDIENCE */

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
        background: rgba(255, 255, 255, .025);
        transition: .2s;
    }

    .push-audience-list article.is-active {
        background: rgba(255, 48, 69, .07);
        border-color: rgba(255, 48, 69, .2);
    }

    .push-audience-list article strong {
        color: white;
        font-size: 14px;
    }

    /* ERROR */

    .push-error-box {
        margin-bottom: 20px;
        padding: 16px 18px;
        border-radius: 15px;
        background: rgba(255, 48, 69, .08);
        border: 1px solid rgba(255, 48, 69, .25);
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

    /* RESPONSIVE */

    @media (max-width: 1100px) {

        .push-compose-layout {
            grid-template-columns: 1fr;
        }

        .push-compose-side {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

    }

    @media (max-width: 700px) {

        .push-compose-side {
            grid-template-columns: 1fr;
        }

        .push-form-actions {
            flex-direction: column-reverse;
        }

        .push-soft-btn,
        .push-primary-btn {
            width: 100%;
        }

    }
</style>

@endpush


{{-- =========================================================
JAVASCRIPT
========================================================= --}}

@push('scripts')

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const audienceSelect = document.getElementById('doi_tuong_nhan');

        const rankWrapper = document.getElementById('hang_thanh_vien_wrapper');
        const rankSelect = document.getElementById('hang_thanh_vien');

        const specificWrapper = document.getElementById('nguoi_dung_cu_the_wrapper');

        const searchInput = document.getElementById('tim_nguoi_dung');
        const resultBox = document.getElementById('ket_qua_nguoi_dung');
        const selectedBox = document.getElementById('nguoi_dung_da_chon');
        const selectedId = document.getElementById('nguoi_dung_cu_the');
        const selectedName = document.getElementById('selectedUserName');
        const selectedEmail = document.getElementById('selectedUserEmail');


        /**
         * Xóa lỗi validation cũ
         */
        function clearValidationError(wrapper, input) {

            if (!wrapper) {
                return;
            }

            if (input) {
                input.classList.remove('is-invalid');
            }

            const errorMessages = wrapper.querySelectorAll('small');

            errorMessages.forEach(function(error) {
                error.remove();
            });
        }


        /**
         * Hiện / ẩn các ô theo đối tượng nhận
         */
        function toggleAudience() {

            const value = audienceSelect.value;

            if (value === 'hang_thanh_vien') {

                rankWrapper.classList.remove('is-hidden');
                specificWrapper.classList.add('is-hidden');

                rankSelect.value = '';

                searchInput.value = '';
                resultBox.innerHTML = '';
                resultBox.classList.add('is-hidden');
                selectedBox.classList.add('is-hidden');

                selectedId.value = '';
                selectedName.textContent = '';
                selectedEmail.textContent = '';

            } else if (value === 'nguoi_dung_cu_the') {

                specificWrapper.classList.remove('is-hidden');
                rankWrapper.classList.add('is-hidden');

                rankSelect.value = '';

            } else {

                rankWrapper.classList.add('is-hidden');
                rankSelect.value = '';

                specificWrapper.classList.add('is-hidden');

                searchInput.value = '';
                resultBox.innerHTML = '';
                resultBox.classList.add('is-hidden');

                selectedBox.classList.add('is-hidden');

                selectedId.value = '';
                selectedName.textContent = '';
                selectedEmail.textContent = '';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | TÌM NGƯỜI DÙNG THEO TÊN / EMAIL
        |--------------------------------------------------------------------------
        */

        let searchTimer = null;

        if (searchInput) {

            searchInput.addEventListener('input', function() {

                const keyword = this.value.trim();

                clearTimeout(searchTimer);

                // Chưa nhập đủ 2 ký tự
                if (keyword.length < 2) {

                    resultBox.innerHTML = '';
                    resultBox.classList.add('is-hidden');

                    return;
                }

                // Đợi 300ms rồi mới gọi API
                searchTimer = setTimeout(function() {

                    fetch(
                            '{{ route("admin.thong-bao-push.tim-nguoi-dung") }}?keyword=' +
                            encodeURIComponent(keyword), {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            }
                        )
                        .then(function(response) {

                            if (!response.ok) {
                                throw new Error('Không thể tìm người dùng');
                            }

                            return response.json();
                        })
                        .then(function(users) {

                            resultBox.innerHTML = '';

                            if (!users || users.length === 0) {

                                resultBox.innerHTML = `
                <div class="push-user-no-result">
                    Không tìm thấy người dùng.
                </div>
            `;

                                resultBox.classList.remove('is-hidden');

                                return;
                            }

                            users.forEach(function(user) {

                                const item = document.createElement('button');

                                item.type = 'button';
                                item.className = 'push-user-result';

                                item.innerHTML = `
                <span class="push-user-result-icon">
                    <i class="fa-solid fa-user"></i>
                </span>

                <span class="push-user-result-info">
                    <strong>${escapeHtml(user.ho_ten ?? '')}</strong>
                    <small>${escapeHtml(user.email ?? '')}</small>
                </span>
            `;

                                item.addEventListener('click', function() {

                                    selectedId.value = user.id;

                                    selectedName.textContent = user.ho_ten ?? '';
                                    selectedEmail.textContent = user.email ?? '';

                                    selectedBox.classList.remove('is-hidden');

                                    searchInput.value = '';

                                    resultBox.innerHTML = '';
                                    resultBox.classList.add('is-hidden');
                                });

                                resultBox.appendChild(item);
                            });

                            resultBox.classList.remove('is-hidden');

                        })
                        .catch(function(error) {

                            console.error('Lỗi tìm người dùng:', error);

                        });

                }, 300);
            });
        }


        /*
        |--------------------------------------------------------------------------
        | BỎ CHỌN NGƯỜI DÙNG
        |--------------------------------------------------------------------------
        */

        const removeSelectedUser =
            document.getElementById('removeSelectedUser');

        if (removeSelectedUser) {

            removeSelectedUser.addEventListener('click', function() {

                selectedId.value = '';

                selectedName.textContent = '';
                selectedEmail.textContent = '';

                selectedBox.classList.add('is-hidden');

            });
        }


        /*
        |--------------------------------------------------------------------------
        | ESCAPE HTML
        |--------------------------------------------------------------------------
        */

        function escapeHtml(value) {

            const div = document.createElement('div');

            div.textContent = value;

            return div.innerHTML;
        }


        /*
        |--------------------------------------------------------------------------
        | CHANGE ĐỐI TƯỢNG
        |--------------------------------------------------------------------------
        */

        if (audienceSelect) {

            audienceSelect.addEventListener(
                'change',
                toggleAudience
            );

            // Chạy lúc load trang
            toggleAudience();
        }

    });
</script>

@endpush

@endsection