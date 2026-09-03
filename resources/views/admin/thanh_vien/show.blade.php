@extends('layouts.admin')

@section('title', 'Chi tiết thành viên')
@section('page-title', 'Chi tiết thành viên')
@section('page-subtitle', 'Theo dõi hồ sơ, điểm, vé và voucher của khách hàng')

@section('content')

@php
    $rankMeta = [
        'member' => [
            'label' => 'Member',
            'icon' => 'fa-user',
            'tone' => 'is-member',
            'base' => 0
        ],
        'silver' => [
            'label' => 'Silver',
            'icon' => 'fa-medal',
            'tone' => 'is-silver',
            'base' => 500
        ],
        'gold' => [
            'label' => 'Gold',
            'icon' => 'fa-crown',
            'tone' => 'is-gold',
            'base' => 1000
        ],
        'platinum' => [
            'label' => 'Platinum',
            'icon' => 'fa-gem',
            'tone' => 'is-platinum',
            'base' => 2000
        ],
    ];

    $rankOrder = [
        'member',
        'silver',
        'gold',
        'platinum'
    ];

    $currentKey = $thanhVien->hang_thanh_vien ?: 'member';

    $rank = $rankMeta[$currentKey] ?? $rankMeta['member'];

    $currentIndex = array_search(
        $currentKey,
        $rankOrder,
        true
    );

    $nextKey =
        $currentIndex !== false &&
        $currentIndex < count($rankOrder) - 1
            ? $rankOrder[$currentIndex + 1]
            : null;

    $nextRank = $nextKey
        ? $rankMeta[$nextKey]
        : null;

    $totalPoint = (int) $thanhVien->tong_diem_tich_luy;

    $currentBase = (int) ($rank['base'] ?? 0);

    $nextBase = $nextRank
        ? (int) $nextRank['base']
        : max($totalPoint, 2000);

    $progress = $nextRank
        ? min(
            100,
            max(
                0,
                (
                    ($totalPoint - $currentBase)
                    /
                    max(
                        1,
                        $nextBase - $currentBase
                    )
                ) * 100
            )
        )
        : 100;

    $pointRemain = $nextRank
        ? max(
            0,
            $nextBase - $totalPoint
        )
        : 0;

    $displayName =
        $nguoiDung?->ho_ten
        ?: 'Khách chưa cập nhật';

    $initial =
        mb_strtoupper(
            mb_substr(
                $displayName,
                0,
                1
            )
        );
@endphp


<div class="member-admin-page member-detail-page">

    {{-- BACK --}}
    <a
        href="{{ route('admin.thanh-vien.index') }}"
        class="member-back-link"
    >
        <i class="fa-solid fa-arrow-left"></i>
        Quay lại danh sách
    </a>


    {{-- HERO --}}
    <section class="member-detail-hero">

        <div class="member-card-preview {{ $rank['tone'] }}">

            <div class="member-card-top">
                <span>
                    CineHome Member
                </span>

                <i class="fa-solid {{ $rank['icon'] }}"></i>
            </div>

            <strong>
                {{ $thanhVien->ma_thanh_vien }}
            </strong>

            <p>
                {{ $displayName }}
            </p>

            <div class="member-card-footer">

                <span>
                    {{ $rank['label'] }}
                </span>

                <span>
                    {{ number_format($thanhVien->diem_hien_tai) }}
                    điểm
                </span>

            </div>

        </div>


        <div class="member-detail-summary">

            <span class="member-kicker">

                <i class="fa-solid fa-id-card-clip"></i>

                Hồ sơ thành viên

            </span>

            <h2>
                {{ $displayName }}
            </h2>

            <p>
                Quản lý điểm, lịch sử giao dịch và quyền lợi
                đang sở hữu của khách hàng.
            </p>


            <div class="member-progress-box">

                <div class="member-progress-head">

                    <span>
                        Hạng hiện tại:

                        <strong>
                            {{ $rank['label'] }}
                        </strong>
                    </span>

                    @if($nextRank)

                        <span>
                            Còn
                            {{ number_format($pointRemain) }}
                            điểm lên
                            {{ $nextRank['label'] }}
                        </span>

                    @else

                        <span>
                            Đã đạt hạng cao nhất
                        </span>

                    @endif

                </div>


                <div class="member-progress-track">

                    <span
                        style="width: {{ $progress }}%"
                    ></span>

                </div>

            </div>

        </div>

    </section>


    {{-- STAT --}}
    <section class="member-stat-grid is-detail">

        <article class="member-stat-card is-total">

            <span>
                <i class="fa-solid fa-coins"></i>
            </span>

            <div>

                <small>
                    Điểm hiện tại
                </small>

                <strong>
                    {{ number_format($thanhVien->diem_hien_tai) }}
                </strong>

            </div>

        </article>


        <article class="member-stat-card is-gold">

            <span>
                <i class="fa-solid fa-chart-line"></i>
            </span>

            <div>

                <small>
                    Tổng tích lũy
                </small>

                <strong>
                    {{ number_format($thanhVien->tong_diem_tich_luy) }}
                </strong>

            </div>

        </article>


        <article class="member-stat-card is-silver">

            <span>
                <i class="fa-solid fa-ticket"></i>
            </span>

            <div>

                <small>
                    Số vé đã mua
                </small>

                <strong>
                    {{ number_format($tongVe) }}
                </strong>

            </div>

        </article>


        <article class="member-stat-card is-platinum">

            <span>
                <i class="fa-solid fa-wallet"></i>
            </span>

            <div>

                <small>
                    Tổng chi tiêu
                </small>

                <strong>
                    {{ number_format($tongChiTieu, 0, ',', '.') }}đ
                </strong>

            </div>

        </article>

    </section>


    {{-- THÔNG TIN --}}
    <section class="member-detail-grid">

        <div class="member-panel">

            <div class="member-panel-head">

                <div>

                    <span class="member-kicker">
                        Thông tin
                    </span>

                    <h3>
                        Thông tin khách hàng
                    </h3>

                    <p>
                        Các thông tin cơ bản dùng để đối soát
                        và chăm sóc khách hàng.
                    </p>

                </div>

            </div>


            <div class="member-info-grid">

                <div class="member-info-item">

                    <span>
                        Họ tên
                    </span>

                    <strong>
                        {{ $displayName }}
                    </strong>

                </div>


                <div class="member-info-item">

                    <span>
                        Email
                    </span>

                    <strong>
                        {{ $nguoiDung?->email ?? 'Chưa cập nhật' }}
                    </strong>

                </div>


                <div class="member-info-item">

                    <span>
                        Số điện thoại
                    </span>

                    <strong>
                        {{ $nguoiDung?->so_dien_thoai ?? 'Chưa cập nhật' }}
                    </strong>

                </div>


                <div class="member-info-item">

                    <span>
                        Ngày sinh
                    </span>

                    <strong>
                        {{ $nguoiDung?->ngay_sinh?->format('d/m/Y') ?? 'Chưa cập nhật' }}
                    </strong>

                </div>


                <div class="member-info-item">

                    <span>
                        Ngày tham gia
                    </span>

                    <strong>
                        {{ $thanhVien->ngay_tham_gia?->format('d/m/Y') ?? '---' }}
                    </strong>

                </div>


                <div class="member-info-item">

                    <span>
                        Mã giới thiệu
                    </span>

                    <strong>
                        {{ $thanhVien->ma_gioi_thieu ?? 'Chưa có' }}
                    </strong>

                </div>

            </div>

        </div>


        <div class="member-panel member-quick-profile">

            <span class="member-avatar {{ $rank['tone'] }}">
                {{ $initial }}
            </span>

            <h3>
                {{ $displayName }}
            </h3>

            <p>
                {{ $nguoiDung?->email ?? 'Chưa có email' }}
            </p>

            <span class="member-rank-chip {{ $rank['tone'] }}">

                <i class="fa-solid {{ $rank['icon'] }}"></i>

                {{ $rank['label'] }}

            </span>

        </div>

    </section>


    {{-- =========================================================
         FORM TẶNG / THU HỒI ĐIỂM
    ========================================================== --}}

    <section class="member-point-forms">


        {{-- =====================================================
             TẶNG ĐIỂM
        ====================================================== --}}

        <article class="member-point-form is-add">

            <div class="member-form-head">

                <span>
                    <i class="fa-solid fa-plus"></i>
                </span>

                <div>

                    <h3>
                        Tặng điểm
                    </h3>

                    <p>
                        Cộng điểm bù sự cố, tri ân hoặc theo
                        chương trình chăm sóc khách hàng.
                    </p>

                </div>

            </div>


            <form
                method="POST"
                action="{{ route('admin.thanh-vien.tang-diem', $thanhVien) }}"
                id="giftPointForm"
            >

                @csrf

                <input
                    type="hidden"
                    name="form"
                    value="tang"
                >


                {{-- SỐ ĐIỂM --}}

                <label>

                    <span>
                        Số điểm
                    </span>

                    <input
                        type="number"
                        name="so_diem"
                        value="{{ old('form') === 'tang' ? old('so_diem') : '' }}"
                        placeholder="VD: 100"
                        inputmode="numeric"
                        autocomplete="off"
                    >

                    @if (
                        $errors->has('so_diem')
                        &&
                        old('form') === 'tang'
                    )

                        <small class="server-validation-error">

                            <i class="fa-solid fa-circle-exclamation"></i>

                            {{ $errors->first('so_diem') }}

                        </small>

                    @endif

                </label>


                {{-- NỘI DUNG --}}

                <label>

                    <span>
                        Lý do tặng điểm
                    </span>

                    <textarea
                        name="noi_dung"
                        rows="3"
                        placeholder="VD: Đền bù lỗi thanh toán"
                    >{{ old('form') === 'tang' ? old('noi_dung') : '' }}</textarea>

                    @if (
                        $errors->has('noi_dung')
                        &&
                        old('form') === 'tang'
                    )

                        <small class="server-validation-error">

                            <i class="fa-solid fa-circle-exclamation"></i>

                            {{ $errors->first('noi_dung') }}

                        </small>

                    @endif

                </label>


                {{-- CÁCH TÍNH HẠNG --}}

                <label>

                    <span>
                        Cách tính hạng
                    </span>

                    <div
                        class="custom-dropdown"
                        id="tinhVaoHangDropdown"
                    >

                        <input
                            type="hidden"
                            name="tinh_vao_hang"
                            id="tinhVaoHangValue"
                            value="{{ old('form') === 'tang' ? old('tinh_vao_hang', '1') : '1' }}"
                        >


                        <button
                            type="button"
                            class="custom-dropdown-trigger"
                        >

                            <span class="dropdown-selected">

                                <i class="fa-solid fa-ranking-star"></i>

                                <span>
                                    Có - Tính vào hạng
                                </span>

                            </span>

                            <i class="fa-solid fa-chevron-down dropdown-arrow"></i>

                        </button>


                        <div class="custom-dropdown-menu">

                            <button
                                type="button"
                                class="custom-dropdown-option is-active"
                                data-value="1"
                            >

                                <i class="fa-solid fa-ranking-star"></i>

                                <span>
                                    Có - Tính vào hạng
                                </span>

                            </button>


                            <button
                                type="button"
                                class="custom-dropdown-option is-danger"
                                data-value="0"
                            >

                                <i class="fa-solid fa-ban"></i>

                                <span>
                                    Không - Không tính vào hạng
                                </span>

                            </button>

                        </div>

                    </div>

                </label>


                <button type="submit">

                    <i class="fa-solid fa-gift"></i>

                    Xác nhận tặng điểm

                </button>

            </form>

        </article>


        {{-- =====================================================
             THU HỒI ĐIỂM
        ====================================================== --}}

        <article class="member-point-form is-remove">

            <div class="member-form-head">

                <span>
                    <i class="fa-solid fa-minus"></i>
                </span>

                <div>

                    <h3>
                        Thu hồi điểm
                    </h3>

                    <p>
                        Dùng khi cộng nhầm, cần điều chỉnh sai lệch
                        hoặc xử lý gian lận điểm.
                    </p>

                </div>

            </div>


            <form
                method="POST"
                action="{{ route('admin.thanh-vien.tru-diem', $thanhVien) }}"
                id="withdrawPointForm"
            >

                @csrf

                <input
                    type="hidden"
                    name="form"
                    value="thu_hoi"
                >


                {{-- SỐ ĐIỂM --}}

                <label>

                    <span>
                        Số điểm
                    </span>

                    <input
                        type="number"
                        name="so_diem"
                        value="{{ old('form') === 'thu_hoi' ? old('so_diem') : '' }}"
                        placeholder="VD: 50"
                        inputmode="numeric"
                        autocomplete="off"
                    >

                    @if (
                        $errors->has('so_diem')
                        &&
                        old('form') === 'thu_hoi'
                    )

                        <small class="server-validation-error">

                            <i class="fa-solid fa-circle-exclamation"></i>

                            {{ $errors->first('so_diem') }}

                        </small>

                    @endif

                </label>


                {{-- NỘI DUNG --}}

                <label>

                    <span>
                        Lý do thu hồi
                    </span>

                    <textarea
                        name="noi_dung"
                        rows="3"
                        placeholder="VD: Thu hồi điểm cấp sai"
                    >{{ old('form') === 'thu_hoi' ? old('noi_dung') : '' }}</textarea>

                    @if (
                        $errors->has('noi_dung')
                        &&
                        old('form') === 'thu_hoi'
                    )

                        <small class="server-validation-error">

                            <i class="fa-solid fa-circle-exclamation"></i>

                            {{ $errors->first('noi_dung') }}

                        </small>

                    @endif

                </label>


                <div class="member-warning-note">

                    <i class="fa-solid fa-triangle-exclamation"></i>

                    Điểm thu hồi sẽ ảnh hưởng cả điểm hiện tại
                    và tổng điểm tích lũy.

                </div>


                <button type="submit">

                    <i class="fa-solid fa-rotate-left"></i>

                    Xác nhận thu hồi

                </button>

            </form>

        </article>

    </section>


    {{-- =========================================================
         LỊCH SỬ ĐIỂM
    ========================================================== --}}

    <section class="member-panel">

        <div class="member-panel-head">

            <div>

                <span class="member-kicker">
                    Lịch sử
                </span>

                <h3>
                    Lịch sử điểm
                </h3>

                <p>
                    Theo dõi toàn bộ giao dịch cộng/trừ điểm
                    gần đây của thành viên.
                </p>

            </div>

        </div>


        <div class="member-table-wrap">

            <table class="member-table is-history">

                <thead>

                    <tr>

                        <th>
                            Thời gian
                        </th>

                        <th>
                            Loại giao dịch
                        </th>

                        <th>
                            Điểm
                        </th>

                        <th>
                            Nội dung
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($lichSuDiems as $item)

                        <tr>

                            <td data-label="Thời gian">

                                <span class="member-date">

                                    <i class="fa-regular fa-clock"></i>

                                    {{ $item->created_at->format('d/m/Y H:i') }}

                                </span>

                            </td>


                            <td data-label="Loại giao dịch">

                                @if($item->loai_giao_dich === 'cong_diem')

                                    <span class="member-history-chip is-add">

                                        <i class="fa-solid fa-arrow-trend-up"></i>

                                        Cộng điểm

                                    </span>

                                @else

                                    <span class="member-history-chip is-remove">

                                        <i class="fa-solid fa-arrow-trend-down"></i>

                                        Trừ điểm

                                    </span>

                                @endif

                            </td>


                            <td data-label="Điểm">

                                <span
                                    class="member-history-point {{ $item->loai_giao_dich === 'cong_diem' ? 'is-add' : 'is-remove' }}"
                                >

                                    {{
                                        $item->loai_giao_dich === 'cong_diem'
                                            ? '+'
                                            : '-'
                                    }}{{ number_format($item->so_diem) }}

                                </span>

                            </td>


                            <td data-label="Nội dung">

                                <span class="member-history-content">

                                    {{ $item->noi_dung }}

                                </span>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="4">

                                <div class="member-empty">

                                    <i class="fa-solid fa-clock-rotate-left"></i>

                                    <h3>
                                        Chưa có lịch sử điểm
                                    </h3>

                                    <p>
                                        Các giao dịch cộng/trừ điểm
                                        sẽ được ghi lại tại đây.
                                    </p>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        <div class="member-pagination">

            {{ $lichSuDiems->links() }}

        </div>

    </section>


    {{-- =========================================================
         VOUCHER
    ========================================================== --}}

    <section class="member-panel">

        <div class="member-panel-head">

            <div>

                <span class="member-kicker">
                    Voucher
                </span>

                <h3>
                    Voucher khách đang sở hữu
                </h3>

                <p>
                    Kiểm tra nhanh voucher đã cấp,
                    trạng thái sử dụng và hạn dùng.
                </p>

            </div>


            <span class="member-result-count">

                <i class="fa-solid fa-ticket-simple"></i>

                {{ number_format($vouchers->count()) }}
                voucher

            </span>

        </div>


        <div class="member-voucher-grid">

            @forelse($vouchers as $voucherCaNhan)

                @php
                    $isExpired =
                        $voucherCaNhan->ngay_het_han
                        &&
                        $voucherCaNhan->ngay_het_han->isPast();
                @endphp


                <article
                    class="member-voucher-card
                    {{
                        $voucherCaNhan->da_su_dung
                            ? 'is-used'
                            : (
                                $isExpired
                                    ? 'is-expired'
                                    : 'is-ready'
                            )
                    }}"
                >

                    <div>

                        <span>
                            {{
                                $voucherCaNhan->voucher?->ten_voucher
                                ?? 'Voucher CineHome'
                            }}
                        </span>

                        <strong>
                            {{ $voucherCaNhan->ma_voucher_ca_nhan }}
                        </strong>

                    </div>


                    <p>
                        {{
                            number_format(
                                $voucherCaNhan->voucher?->gia_tri_giam ?? 0,
                                0,
                                ',',
                                '.'
                            )
                        }}đ
                    </p>


                    <footer>

                        @if($voucherCaNhan->da_su_dung)

                            <span>
                                Đã sử dụng
                            </span>

                        @elseif($isExpired)

                            <span>
                                Đã hết hạn
                            </span>

                        @else

                            <span>
                                Có thể dùng
                            </span>

                        @endif


                        <small>

                            HSD:

                            {{
                                $voucherCaNhan->ngay_het_han?->format('d/m/Y')
                                ?? 'Không giới hạn'
                            }}

                        </small>

                    </footer>

                </article>

            @empty

                <div class="member-empty is-wide">

                    <i class="fa-solid fa-ticket"></i>

                    <h3>
                        Khách hàng chưa có voucher
                    </h3>

                    <p>
                        Có thể cấp voucher từ trang
                        Khuyến mãi & Voucher khi cần
                        chăm sóc khách hàng.
                    </p>


                    <a
                        href="{{ route('admin.vouchers.index') }}"
                        class="member-primary-btn"
                    >

                        <i class="fa-solid fa-gift"></i>

                        Cấp voucher

                    </a>

                </div>

            @endforelse

        </div>

    </section>

</div>

@endsection


{{-- =============================================================
     VALIDATION CSS
============================================================= --}}

<style>

    .member-point-form input.is-invalid,
    .member-point-form textarea.is-invalid {
        border-color: #dc2626 !important;
        box-shadow: 0 0 0 1px rgba(220, 38, 38, 0.15) !important;
    }

    .member-point-form label.is-invalid,
    .member-point-form label.is-invalid > span {
        color: inherit !important;
    }

    .member-point-validation-error,
    .server-validation-error {
        display: block !important;
        width: 100%;
        margin-top: 6px;
        color: #dc2626 !important;
        font-size: 12px;
        line-height: 1.5;
        font-weight: 500;
    }

    .member-point-validation-error i,
    .server-validation-error i {
        color: #dc2626 !important;
        margin-right: 4px;
    }

</style>


<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | CẤU HÌNH CHUNG
    |--------------------------------------------------------------------------
    |
    | CẢ TẶNG ĐIỂM VÀ THU HỒI ĐIỂM:
    |
    | 1  -> 100.000
    |
    */

    const MIN_POINT = 1;
    const MAX_POINT = 100000;


    /*
    |--------------------------------------------------------------------------
    | HIỂN THỊ LỖI
    |--------------------------------------------------------------------------
    */

    function showFieldError(field, message) {

        if (!field) {
            return;
        }

        field.classList.add('is-invalid');

        const label = field.closest('label');

        if (!label) {
            return;
        }

        const oldErrors = label.querySelectorAll(
            '.member-point-validation-error'
        );

        oldErrors.forEach(function (error) {
            error.remove();
        });

        const error = document.createElement('small');

        error.className =
            'member-point-validation-error';

        error.innerHTML =
            '<i class="fa-solid fa-circle-exclamation"></i> ' +
            message;

        field.insertAdjacentElement(
            'afterend',
            error
        );
    }


    /*
    |--------------------------------------------------------------------------
    | XÓA LỖI
    |--------------------------------------------------------------------------
    */

    function clearFieldError(field) {

        if (!field) {
            return;
        }

        field.classList.remove(
            'is-invalid'
        );

        const label = field.closest('label');

        if (!label) {
            return;
        }

        label.querySelectorAll(
            '.member-point-validation-error'
        ).forEach(function (error) {
            error.remove();
        });
    }


    /*
    |--------------------------------------------------------------------------
    | XÓA TOÀN BỘ LỖI FORM
    |--------------------------------------------------------------------------
    */

    function clearFormErrors(form) {

        if (!form) {
            return;
        }

        form.querySelectorAll(
            '.is-invalid'
        ).forEach(function (field) {

            field.classList.remove(
                'is-invalid'
            );
        });

        form.querySelectorAll(
            '.member-point-validation-error'
        ).forEach(function (error) {

            error.remove();
        });
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE SỐ ĐIỂM
    |--------------------------------------------------------------------------
    */

    function validatePointInput(input) {

        if (!input) {
            return true;
        }

        const value =
            input.value.trim();


        /*
        |--------------------------------------------------------------------------
        | BỎ TRỐNG
        |--------------------------------------------------------------------------
        */

        if (value === '') {

            showFieldError(
                input,
                'Vui lòng nhập số điểm.'
            );

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | CHỈ SỐ NGUYÊN
        |--------------------------------------------------------------------------
        */

        if (!/^\d+$/.test(value)) {

            showFieldError(
                input,
                'Số điểm phải là số nguyên.'
            );

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | NUMBER
        |--------------------------------------------------------------------------
        */

        const number =
            Number(value);


        if (!Number.isSafeInteger(number)) {

            showFieldError(
                input,
                'Số điểm không hợp lệ.'
            );

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | MIN
        |--------------------------------------------------------------------------
        */

        if (number < MIN_POINT) {

            showFieldError(
                input,
                'Số điểm phải lớn hơn hoặc bằng 1.'
            );

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | MAX
        |--------------------------------------------------------------------------
        */

        if (number > MAX_POINT) {

            showFieldError(
                input,
                'Số điểm không được vượt quá 100.000.'
            );

            return false;
        }


        clearFieldError(input);

        return true;
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE NỘI DUNG
    |--------------------------------------------------------------------------
    */

    function validateContentInput(input) {

        if (!input) {
            return true;
        }

        const value =
            input.value.trim();

        if (value === '') {

            showFieldError(
                input,
                'Vui lòng nhập nội dung.'
            );

            return false;
        }

        clearFieldError(input);

        return true;
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE FORM
    |--------------------------------------------------------------------------
    */

    function validatePointForm(form) {

        if (!form) {
            return false;
        }

        clearFormErrors(form);

        const pointInput =
            form.querySelector(
                '[name="so_diem"]'
            );

        const contentInput =
            form.querySelector(
                '[name="noi_dung"]'
            );

        let valid = true;


        /*
        |----------------------------------------------------------------------
        | ĐIỂM
        |----------------------------------------------------------------------
        */

        if (!validatePointInput(pointInput)) {
            valid = false;
        }


        /*
        |----------------------------------------------------------------------
        | NỘI DUNG
        |----------------------------------------------------------------------
        */

        if (!validateContentInput(contentInput)) {
            valid = false;
        }


        return valid;
    }


    /*
    |--------------------------------------------------------------------------
    | INPUT ĐIỂM
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll(
        'input[name="so_diem"]'
    ).forEach(function (input) {

        input.addEventListener(
            'input',
            function () {

                const value =
                    this.value.trim();

                if (value === '') {

                    clearFieldError(this);

                    return;
                }

                validatePointInput(this);
            }
        );


        input.addEventListener(
            'blur',
            function () {

                validatePointInput(this);
            }
        );
    });


    /*
    |--------------------------------------------------------------------------
    | TEXTAREA
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll(
        'textarea[name="noi_dung"]'
    ).forEach(function (textarea) {

        textarea.addEventListener(
            'input',
            function () {

                if (
                    this.value.trim() !== ''
                ) {

                    clearFieldError(this);
                }
            }
        );


        textarea.addEventListener(
            'blur',
            function () {

                validateContentInput(this);
            }
        );
    });


    /*
    |--------------------------------------------------------------------------
    | FORM TẶNG
    |--------------------------------------------------------------------------
    */

    const giftForm =
        document.getElementById(
            'giftPointForm'
        );

    if (giftForm) {

        giftForm.addEventListener(
            'submit',
            function (e) {

                const valid =
                    validatePointForm(this);

                if (!valid) {

                    e.preventDefault();

                    return;
                }


                const confirmed =
                    window.confirm(
                        'Bạn có chắc muốn tặng điểm cho thành viên này? Hành động này sẽ cộng điểm vào tài khoản của thành viên.'
                    );


                if (!confirmed) {

                    e.preventDefault();
                }
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FORM THU HỒI
    |--------------------------------------------------------------------------
    */

    const withdrawForm =
        document.getElementById(
            'withdrawPointForm'
        );

    if (withdrawForm) {

        withdrawForm.addEventListener(
            'submit',
            function (e) {

                const valid =
                    validatePointForm(this);

                if (!valid) {

                    e.preventDefault();

                    return;
                }


                const confirmed =
                    window.confirm(
                        'Bạn có chắc muốn thu hồi điểm của thành viên này?'
                    );


                if (!confirmed) {

                    e.preventDefault();
                }
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DROPDOWN TÍNH VÀO HẠNG
    |--------------------------------------------------------------------------
    */

    const dropdown =
        document.getElementById(
            'tinhVaoHangDropdown'
        );

    if (dropdown) {

        const trigger =
            dropdown.querySelector(
                '.custom-dropdown-trigger'
            );

        const valueInput =
            document.getElementById(
                'tinhVaoHangValue'
            );

        const selectedIcon =
            dropdown.querySelector(
                '.dropdown-selected i'
            );

        const selectedText =
            dropdown.querySelector(
                '.dropdown-selected span'
            );

        const options =
            dropdown.querySelectorAll(
                '.custom-dropdown-option'
            );


        /*
        |--------------------------------------------------------------------------
        | KHÔI PHỤC GIÁ TRỊ CŨ
        |--------------------------------------------------------------------------
        */

        const currentValue =
            valueInput?.value ?? '1';

        options.forEach(function (option) {

            if (
                option.dataset.value ===
                currentValue
            ) {

                option.classList.add(
                    'is-active'
                );

                const optionIcon =
                    option.querySelector('i');

                const optionText =
                    option.querySelector('span');

                if (
                    optionIcon &&
                    selectedIcon
                ) {

                    selectedIcon.className =
                        optionIcon.className;
                }

                if (
                    optionText &&
                    selectedText
                ) {

                    selectedText.textContent =
                        optionText.textContent.trim();
                }
            }
        });


        /*
        |--------------------------------------------------------------------------
        | MỞ / ĐÓNG
        |--------------------------------------------------------------------------
        */

        if (trigger) {

            trigger.addEventListener(
                'click',
                function (e) {

                    e.stopPropagation();

                    document.querySelectorAll(
                        '.custom-dropdown.is-open'
                    ).forEach(function (item) {

                        if (item !== dropdown) {

                            item.classList.remove(
                                'is-open'
                            );
                        }
                    });


                    dropdown.classList.toggle(
                        'is-open'
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | CHỌN OPTION
        |--------------------------------------------------------------------------
        */

        options.forEach(
            function (option) {

                option.addEventListener(
                    'click',
                    function (e) {

                        e.stopPropagation();

                        const value =
                            this.dataset.value;

                        const text =
                            this.querySelector(
                                'span'
                            )
                            .textContent
                            .trim();

                        const icon =
                            this.querySelector(
                                'i'
                            ).className;


                        valueInput.value =
                            value;

                        selectedText.textContent =
                            text;

                        selectedIcon.className =
                            icon;


                        options.forEach(
                            function (item) {

                                item.classList.remove(
                                    'is-active'
                                );
                            }
                        );


                        this.classList.add(
                            'is-active'
                        );


                        dropdown.classList.remove(
                            'is-open'
                        );
                    }
                );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | CLICK RA NGOÀI
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'click',
            function (e) {

                if (
                    !dropdown.contains(
                        e.target
                    )
                ) {

                    dropdown.classList.remove(
                        'is-open'
                    );
                }
            }
        );
    }

});
</script>