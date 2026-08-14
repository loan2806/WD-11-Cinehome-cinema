@extends('layouts.admin')

@section('page-title', 'Sửa thông báo')

@section('content')

@php

/*
|--------------------------------------------------------------------------
| ĐỐI TƯỢNG NHẬN
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


/*
|--------------------------------------------------------------------------
| DỮ LIỆU CŨ
|--------------------------------------------------------------------------
*/

$selectedAudience = old(
'doi_tuong_nhan',
$thongBaoPush->doi_tuong_nhan
);

$selectedLoai = old(
'loai',
$thongBaoPush->loai
);

$selectedHangThanhVien = old(
'hang_thanh_vien',
$thongBaoPush->hang_thanh_vien ?? null
);


/*
|--------------------------------------------------------------------------
| NGƯỜI DÙNG CỤ THỂ
|--------------------------------------------------------------------------
*/

$selectedUser = null;

if ($selectedAudience === 'nguoi_dung_cu_the') {

$selectedUser = \App\Models\ThongBaoPushNguoiDung::with('nguoiDung')
->where('thong_bao_push_id', $thongBaoPush->id)
->first();

$selectedUser = $selectedUser?->nguoiDung;
}

$selectedUserId = old(
'nguoi_dung_cu_the',
$selectedUser?->id
);

@endphp


{{-- =========================================================
     CSS
========================================================= --}}

@push('styles')

<style>
    /*
    |--------------------------------------------------------------------------
    | LAYOUT 2 CỘT
    |--------------------------------------------------------------------------
    */

    .push-compose-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 380px;
        gap: 24px;
        align-items: start;
        width: 100%;
    }

    .push-compose-main {
        min-width: 0;
    }

    .push-compose-side {
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 24px;
    }


    /*
    |--------------------------------------------------------------------------
    | HIDDEN
    |--------------------------------------------------------------------------
    */

    .is-hidden {
        display: none !important;
    }


    /*
    |--------------------------------------------------------------------------
    | SEARCH USER
    |--------------------------------------------------------------------------
    */

    .push-user-results {
        margin-top: 10px;
        border: 1px solid rgba(255, 255, 255, .1);
        border-radius: 14px;
        background: #15171d;
        overflow: hidden;
        max-height: 260px;
        overflow-y: auto;
    }

    .push-user-result {
        display: flex;
        align-items: center;
        gap: 12px;
        width: 100%;
        padding: 12px 14px;
        border: 0;
        background: transparent;
        color: #fff;
        text-align: left;
        cursor: pointer;
        transition: .2s;
    }

    .push-user-result:hover {
        background: rgba(255, 255, 255, .06);
    }

    .push-user-result-icon {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, .08);
        flex-shrink: 0;
    }

    .push-user-result-info {
        min-width: 0;
    }

    .push-user-result-info strong {
        display: block;
        color: #fff;
    }

    .push-user-result-info small {
        display: block;
        margin-top: 3px;
        color: #8d929d;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }


    /*
    |--------------------------------------------------------------------------
    | SELECTED USER
    |--------------------------------------------------------------------------
    */

    .push-selected-user {
        margin-top: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px;
        border: 1px solid rgba(255, 255, 255, .1);
        border-radius: 14px;
        background: rgba(255, 255, 255, .04);
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
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, .08);
        flex-shrink: 0;
    }

    .push-selected-user-info strong {
        display: block;
        color: #fff;
    }

    .push-selected-user-info small {
        display: block;
        margin-top: 3px;
        color: #8d929d;
    }

    #removeSelectedUser {
        width: 34px;
        height: 34px;
        border: 0;
        border-radius: 10px;
        background: rgba(255, 255, 255, .06);
        color: #aaa;
        cursor: pointer;
        transition: .2s;
    }

    #removeSelectedUser:hover {
        background: rgba(255, 0, 0, .15);
        color: #ff6b6b;
    }


    /*
    |--------------------------------------------------------------------------
    | RESPONSIVE
    |--------------------------------------------------------------------------
    */

    @media (max-width: 1200px) {

        .push-compose-layout {
            grid-template-columns: minmax(0, 1fr) 340px;
        }

    }


    @media (max-width: 1050px) {

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
            display: flex;
        }

    }
</style>

@endpush



{{-- =========================================================
     LAYOUT 2 CỘT
========================================================= --}}

<div class="push-compose-layout">


    {{-- =====================================================
         CỘT TRÁI
    ====================================================== --}}

    <section class="push-panel push-compose-main">


        {{-- =================================================
             HEADER
        ================================================== --}}

        <div class="push-panel-head">

            <div>

                <span>Thông báo</span>

                <h3>Sửa thông báo</h3>

                <p>
                    Chỉnh sửa nội dung và đối tượng nhận thông báo.
                </p>

            </div>

        </div>



        {{-- =================================================
             FORM
        ================================================== --}}

        <form
            action="{{ route('admin.thong-bao-push.update', [
        'thongBao' => $thongBaoPush->id
    ]) }}"
            method="POST">

            @csrf

            @method('PUT')


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
                        value="{{ old('tieu_de', $thongBaoPush->tieu_de) }}"
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

                        <option value="">
                            -- Chọn loại thông báo --
                        </option>

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
                        placeholder="Nhập nội dung thông báo...">{{ old('noi_dung', $thongBaoPush->noi_dung) }}</textarea>

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

                        <option value="">
                            -- Chọn đối tượng nhận --
                        </option>

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

                        <select
                            name="hang_thanh_vien"
                            id="hang_thanh_vien"
                            class="@error('hang_thanh_vien') is-invalid @enderror">

                            <option value="">
                                -- Chọn hạng thành viên --
                            </option>

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


                    {{-- TÌM NGƯỜI DÙNG --}}

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
                                value="{{ $selectedUser?->ho_ten ?? '' }}"
                                class="@error('nguoi_dung_cu_the') is-invalid @enderror">

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
                        class="push-selected-user
                        {{ $selectedUser ? '' : 'is-hidden' }}">

                        <div class="push-selected-user-info">

                            <span class="push-selected-user-icon">

                                <i class="fa-solid fa-user"></i>

                            </span>


                            <div>

                                <strong id="selectedUserName">
                                    {{ $selectedUser?->ho_ten ?? '' }}
                                </strong>

                                <small id="selectedUserEmail">
                                    {{ $selectedUser?->email ?? '' }}
                                </small>

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
                        value="{{ $selectedUserId }}">

                </div>

            </div>



            {{-- =================================================
                 BUTTON
            ================================================== --}}

            <div class="push-form-actions">


                {{-- HỦY --}}

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


        </form>

    </section>



    {{-- =====================================================
         CỘT PHẢI
    ====================================================== --}}

    <aside class="push-compose-side">

        {{-- =================================================
             ĐỐI TƯỢNG GỬI
        ================================================== --}}

        <section class="push-panel push-audience-panel">


            {{-- DANH SÁCH --}}

            <div class="push-audience-list">


                @foreach ($audienceGuide as $value => $meta)

                @php

                if ($value === 'nguoi_dung_cu_the') {

                $count =
                $audienceCounts['nguoi_dung_cu_the']
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



{{-- =========================================================
     JAVASCRIPT
========================================================= --}}

@push('scripts')

<script>
    document.addEventListener('DOMContentLoaded', function() {


        /*
        |--------------------------------------------------------------------------
        | ELEMENT
        |--------------------------------------------------------------------------
        */

        const audienceSelect =
            document.getElementById('doi_tuong_nhan');

        const memberWrapper =
            document.getElementById('hang_thanh_vien_wrapper');

        const specificWrapper =
            document.getElementById('nguoi_dung_cu_the_wrapper');

        const memberSelect =
            document.getElementById('hang_thanh_vien');

        const userInput =
            document.getElementById('tim_nguoi_dung');

        const userIdInput =
            document.getElementById('nguoi_dung_cu_the');

        const resultBox =
            document.getElementById('ket_qua_nguoi_dung');

        const selectedUserBox =
            document.getElementById('nguoi_dung_da_chon');

        const selectedUserName =
            document.getElementById('selectedUserName');

        const selectedUserEmail =
            document.getElementById('selectedUserEmail');

        const removeSelectedUser =
            document.getElementById('removeSelectedUser');

        const titleInput =
            document.getElementById('tieu_de');

        const contentInput =
            document.getElementById('noi_dung');

        const typeSelect =
            document.getElementById('loai');

        const previewTitle =
            document.getElementById('previewTitle');

        const previewContent =
            document.getElementById('previewContent');

        const previewType =
            document.getElementById('previewType');


        /*
        |--------------------------------------------------------------------------
        | HIỂN THỊ ĐỐI TƯỢNG
        |--------------------------------------------------------------------------
        */

        function updateAudience() {

            const value = audienceSelect.value;


            /*
            | Hạng thành viên
            */

            if (value === 'hang_thanh_vien') {

                memberWrapper.classList.remove('is-hidden');

            } else {

                memberWrapper.classList.add('is-hidden');

            }


            /*
            | Người dùng cụ thể
            */

            if (value === 'nguoi_dung_cu_the') {

                specificWrapper.classList.remove('is-hidden');

            } else {

                specificWrapper.classList.add('is-hidden');

            }


            /*
            | Active card bên phải
            */

            document
                .querySelectorAll('[data-audience-card]')
                .forEach(function(card) {

                    if (
                        card.dataset.audienceCard === value
                    ) {

                        card.classList.add('is-active');

                    } else {

                        card.classList.remove('is-active');

                    }

                });

        }


        /*
        |--------------------------------------------------------------------------
        | SELECT ĐỐI TƯỢNG
        |--------------------------------------------------------------------------
        */

        if (audienceSelect) {

            audienceSelect.addEventListener(
                'change',
                updateAudience
            );

        }


        /*
        |--------------------------------------------------------------------------
        | PREVIEW TIÊU ĐỀ
        |--------------------------------------------------------------------------
        */

        function updateTitlePreview() {

            const value =
                titleInput.value.trim();

            previewTitle.textContent =
                value || 'Tiêu đề thông báo của bạn';

        }


        /*
        |--------------------------------------------------------------------------
        | PREVIEW NỘI DUNG
        |--------------------------------------------------------------------------
        */

        function updateContentPreview() {

            const value =
                contentInput.value.trim();

            previewContent.textContent =
                value || 'Nội dung thông báo sẽ hiển thị tại đây.';

        }


        /*
        |--------------------------------------------------------------------------
        | PREVIEW LOẠI
        |--------------------------------------------------------------------------
        */

        function updateTypePreview() {

            const selected =
                typeSelect.options[
                    typeSelect.selectedIndex
                ];

            if (
                selected &&
                selected.value
            ) {

                previewType.textContent =
                    selected.textContent.trim();

            } else {

                previewType.textContent =
                    'Thông báo';

            }

        }


        /*
        |--------------------------------------------------------------------------
        | EVENT PREVIEW
        |--------------------------------------------------------------------------
        */

        if (titleInput) {

            titleInput.addEventListener(
                'input',
                updateTitlePreview
            );

        }


        if (contentInput) {

            contentInput.addEventListener(
                'input',
                updateContentPreview
            );

        }


        if (typeSelect) {

            typeSelect.addEventListener(
                'change',
                updateTypePreview
            );

        }



        /*
        |--------------------------------------------------------------------------
        | TÌM NGƯỜI DÙNG
        |--------------------------------------------------------------------------
        */

        let searchTimer = null;


        if (userInput) {

            userInput.addEventListener(
                'input',
                function() {

                    const keyword =
                        this.value.trim();


                    clearTimeout(searchTimer);


                    /*
                    | Không đủ ký tự
                    */

                    if (keyword.length < 2) {

                        resultBox.innerHTML = '';

                        resultBox.classList.add(
                            'is-hidden'
                        );

                        return;

                    }


                    /*
                    | Delay search
                    */

                    searchTimer = setTimeout(
                        function() {


                            fetch(
                                    "{{ route('admin.thong-bao-push.tim-nguoi-dung') }}?keyword=" +
                                    encodeURIComponent(keyword), {
                                        headers: {
                                            'Accept': 'application/json',
                                            'X-Requested-With': 'XMLHttpRequest'
                                        }
                                    }
                                )

                                .then(function(response) {

                                    if (!response.ok) {

                                        throw new Error(
                                            'Không thể tìm người dùng'
                                        );

                                    }

                                    return response.json();

                                })

                                .then(function(data) {

                                    /*
                                    | Có thể API trả:
                                    | [
                                    |   {...},
                                    |   {...}
                                    | ]
                                    |
                                    | hoặc:
                                    | {
                                    |   data: [...]
                                    | }
                                    */

                                    const users =
                                        Array.isArray(data) ?
                                        data :
                                        (
                                            Array.isArray(data.data) ?
                                            data.data : []
                                        );


                                    resultBox.innerHTML = '';


                                    /*
                                    | Không có kết quả
                                    */

                                    if (!users.length) {

                                        resultBox.innerHTML = `
                                    <div style="
                                        padding: 14px;
                                        color: #8d929d;
                                        text-align: center;
                                    ">
                                        Không tìm thấy người dùng
                                    </div>
                                `;

                                        resultBox.classList.remove(
                                            'is-hidden'
                                        );

                                        return;

                                    }


                                    /*
                                    | Hiển thị kết quả
                                    */

                                    users.forEach(
                                        function(user) {

                                            const id =
                                                user.id;

                                            const name =
                                                user.ho_ten ??
                                                user.name ??
                                                'Không có tên';

                                            const email =
                                                user.email ??
                                                '';


                                            const button =
                                                document.createElement(
                                                    'button'
                                                );

                                            button.type =
                                                'button';

                                            button.className =
                                                'push-user-result';


                                            button.innerHTML = `

                                        <span class="push-user-result-icon">

                                            <i class="fa-solid fa-user"></i>

                                        </span>

                                        <span class="push-user-result-info">

                                            <strong>
                                                ${escapeHtml(name)}
                                            </strong>

                                            <small>
                                                ${escapeHtml(email)}
                                            </small>

                                        </span>

                                    `;


                                            button.addEventListener(
                                                'click',
                                                function() {

                                                    selectUser(
                                                        id,
                                                        name,
                                                        email
                                                    );

                                                }
                                            );


                                            resultBox.appendChild(
                                                button
                                            );

                                        }
                                    );


                                    resultBox.classList.remove(
                                        'is-hidden'
                                    );

                                })

                                .catch(function(error) {

                                    console.error(
                                        error
                                    );

                                    resultBox.innerHTML = `

                                <div style="
                                    padding: 14px;
                                    color: #ff6b6b;
                                    text-align: center;
                                ">
                                    Không thể tìm người dùng
                                </div>

                            `;

                                    resultBox.classList.remove(
                                        'is-hidden'
                                    );

                                });


                        },
                        300
                    );

                }
            );

        }



        /*
        |--------------------------------------------------------------------------
        | CHỌN NGƯỜI DÙNG
        |--------------------------------------------------------------------------
        */

        function selectUser(
            id,
            name,
            email
        ) {

            userIdInput.value =
                id;

            userInput.value =
                name;

            selectedUserName.textContent =
                name;

            selectedUserEmail.textContent =
                email;


            selectedUserBox.classList.remove(
                'is-hidden'
            );

            resultBox.innerHTML = '';

            resultBox.classList.add(
                'is-hidden'
            );

        }



        /*
        |--------------------------------------------------------------------------
        | BỎ CHỌN NGƯỜI DÙNG
        |--------------------------------------------------------------------------
        */

        if (removeSelectedUser) {

            removeSelectedUser.addEventListener(
                'click',
                function() {

                    userIdInput.value = '';

                    userInput.value = '';

                    selectedUserName.textContent =
                        '';

                    selectedUserEmail.textContent =
                        '';

                    selectedUserBox.classList.add(
                        'is-hidden'
                    );

                }
            );

        }



        /*
        |--------------------------------------------------------------------------
        | ESCAPE HTML
        |--------------------------------------------------------------------------
        */

        function escapeHtml(value) {

            return String(value)
                .replace(
                    /&/g,
                    '&amp;'
                )
                .replace(
                    /</g,
                    '&lt;'
                )
                .replace(
                    />/g,
                    '&gt;'
                )
                .replace(
                    /"/g,
                    '&quot;'
                )
                .replace(
                    /'/g,
                    '&#039;'
                );

        }



        /*
        |--------------------------------------------------------------------------
        | CLICK RA NGOÀI SEARCH
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'click',
            function(event) {

                if (
                    resultBox &&
                    userInput &&
                    !resultBox.contains(event.target) &&
                    event.target !== userInput
                ) {

                    resultBox.classList.add(
                        'is-hidden'
                    );

                }

            }
        );



        /*
        |--------------------------------------------------------------------------
        | KHỞI TẠO
        |--------------------------------------------------------------------------
        */

        updateAudience();

        updateTitlePreview();

        updateContentPreview();

        updateTypePreview();

    });
</script>

@endpush


@endsection