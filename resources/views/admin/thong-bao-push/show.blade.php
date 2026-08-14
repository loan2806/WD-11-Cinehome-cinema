@extends('layouts.admin')

@section('page-title', 'Chi tiết thông báo đẩy')

@section('content')

@php

$typeMeta = [
    'info' => [
        'label' => 'Thông tin',
        'icon' => 'fa-circle-info',
        'class' => 'is-info',
    ],
    'success' => [
        'label' => 'Thành công',
        'icon' => 'fa-circle-check',
        'class' => 'is-success',
    ],
    'warning' => [
        'label' => 'Cảnh báo',
        'icon' => 'fa-triangle-exclamation',
        'class' => 'is-warning',
    ],
    'promo' => [
        'label' => 'Khuyến mãi',
        'icon' => 'fa-gift',
        'class' => 'is-promo',
    ],
    'system' => [
        'label' => 'Hệ thống',
        'icon' => 'fa-gear',
        'class' => 'is-system',
    ],
];

$audienceMeta = [

    'all' => [
        'label' => 'Tất cả người dùng',
        'icon' => 'fa-users',
        'class' => 'is-all',
    ],

    'khach_hang' => [
        'label' => 'Khách hàng',
        'icon' => 'fa-user',
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
        'class' => 'is-admin',
    ],

    'hang_thanh_vien' => [
        'label' => 'Hạng thành viên',
        'icon' => 'fa-ranking-star',
        'class' => 'is-vip',
    ],

    'nguoi_dung_cu_the' => [
        'label' => 'Người dùng cụ thể',
        'icon' => 'fa-user-pen',
        'class' => 'is-specific',
    ],
];

$status = [
    'label' => 'Đã gửi',
    'icon' => 'fa-paper-plane',
    'class' => 'is-sent',
];

$type = $typeMeta[$thongBaoPush->loai] ?? [
    'label' => ucfirst($thongBaoPush->loai),
    'icon' => 'fa-bell',
    'class' => 'is-system',
];

$audience = $audienceMeta[$thongBaoPush->doi_tuong_nhan] ?? [
    'label' => $thongBaoPush->doi_tuong_nhan,
    'icon' => 'fa-users',
    'class' => 'is-all',
];

$status = [
    'label' => 'Đã gửi',
    'icon' => 'fa-paper-plane',
    'class' => 'is-sent',
];

$recipientCollection = collect($nguoiNhanList ?? []);

$recipientTotal = $recipientCount ?? $recipientCollection->count();

$rankLabels = [
    'member' => 'Member',
    'silver' => 'Silver',
    'gold' => 'Gold',
    'platinum' => 'Platinum',
];

$rankLabel = $rankLabels[$thongBaoPush->hang_thanh_vien] 
    ?? $thongBaoPush->hang_thanh_vien;

@endphp


{{-- =========================================================
    HEADER
========================================================= --}}

<section class="push-page-header">
    <div class="push-header-actions">

        <a
            href="{{ route('admin.thong-bao-push.index') }}"
            class="push-soft-btn">

            <i class="fa-solid fa-arrow-left"></i>
            Quay lại
        </a>

    </div>

</section>


{{-- =========================================================
    MAIN DETAIL
========================================================= --}}

<div class="push-detail-grid">


    {{-- =====================================================
        MESSAGE PREVIEW
    ====================================================== --}}

    <section class="push-panel push-detail-message">

        <div class="push-panel-head">

            <div>

                <span>Nội dung</span>

                <h3>Bản xem thông báo</h3>

                <p>
                    Nội dung người dùng sẽ nhận được trên hệ thống.
                </p>

            </div>

            <span class="push-status {{ $status['class'] }}">

                <i class="fa-solid {{ $status['icon'] }}"></i>

                {{ $status['label'] }}

            </span>

        </div>


        {{-- Notification preview --}}

        <article class="push-message-shell">

            <span class="push-preview-icon">

                <i class="fa-solid fa-paper-plane"></i>

            </span>


            <div class="push-message-content">

                <div class="push-message-top">

                    <strong>CineHome</strong>

                    <small>
                        {{ optional($thongBaoPush->created_at)->format('d/m/Y H:i') }}
                    </small>

                </div>


                <h3>
                    {{ $thongBaoPush->tieu_de }}
                </h3>


                <p>
                    {{ $thongBaoPush->noi_dung }}
                </p>


                <span class="push-chip {{ $type['class'] }}">

                    <i class="fa-solid {{ $type['icon'] }}"></i>

                    {{ $type['label'] }}

                </span>

            </div>

        </article>

    </section>



    {{-- =====================================================
        SEND INFORMATION
    ====================================================== --}}

    <aside class="push-panel push-detail-side">

        <div class="push-panel-head">

            <div>

                <span>Thông tin gửi</span>

                <h3>Chi tiết vận hành</h3>

            </div>

        </div>


        <div class="push-detail-facts">


            {{-- Người tạo --}}

            <article>

                <i class="fa-solid fa-user-gear"></i>

                <span>Người tạo</span>

                <strong>
                    {{ $thongBaoPush->nguoiTao->ho_ten ?? 'Hệ thống' }}
                </strong>

            </article>


            {{-- Loại --}}

            <article>

                <i class="fa-solid {{ $type['icon'] }}"></i>

                <span>Loại thông báo</span>

                <strong>

                    {{ $type['label'] }}

                </strong>

            </article>


            {{-- Đối tượng --}}

            <article>

                <i class="fa-solid {{ $audience['icon'] }}"></i>

                <span>Đối tượng nhận</span>

                <strong>

                    {{ $audience['label'] }}

                </strong>

            </article>


            {{-- Hạng thành viên --}}

            @if ($thongBaoPush->doi_tuong_nhan === 'hang_thanh_vien')

                <article>

                    <i class="fa-solid fa-ranking-star"></i>

                    <span>Hạng thành viên</span>

                    <strong>

                        {{ $rankLabel ?: 'Tất cả hạng' }}

                    </strong>

                </article>

            @endif


            {{-- Thời gian gửi --}}

            <article>

                <i class="fa-solid fa-clock"></i>

                <span>Thời gian gửi</span>

                <strong>

                    {{ optional($thongBaoPush->thoi_gian_gui)->format('d/m/Y H:i') ?? 'Chưa gửi' }}

                </strong>

            </article>


            {{-- Số người nhận --}}

            <article>

                <i class="fa-solid fa-users"></i>

                <span>Số người nhận</span>

                <strong>

                    {{ number_format($recipientTotal) }}

                </strong>

            </article>


        </div>

    </aside>

</div>



{{-- =========================================================
    SPECIFIC USERS
========================================================= --}}

@if (
    $thongBaoPush->doi_tuong_nhan === 'nguoi_dung_cu_the'
    && $recipientCollection->count() > 0
)

<section class="push-panel">

    <div class="push-panel-head">

        <div>

            <span>Người nhận</span>

            <h3>Danh sách người dùng cụ thể</h3>

            <p>
                Các tài khoản được gắn trực tiếp với thông báo này.
            </p>

        </div>

        <strong>

            {{ number_format($recipientCollection->count()) }}

            người

        </strong>

    </div>


    <div class="push-table-wrap">

        <table class="push-table">

            <thead>

                <tr>

                    <th>ID</th>

                    <th>Họ tên</th>

                    <th>Email</th>

                </tr>

            </thead>


            <tbody>

                @foreach ($recipientCollection as $nguoiDung)

                    <tr>

                        <td data-label="ID">

                            <span class="push-code">

                                #{{ $nguoiDung->id }}

                            </span>

                        </td>


                        <td data-label="Họ tên">

                            <div class="push-author">

                                <span>
                                    {{ strtoupper(mb_substr($nguoiDung->ho_ten ?? 'U', 0, 1)) }}
                                </span>

                                <strong>
                                    {{ $nguoiDung->ho_ten }}
                                </strong>

                            </div>

                        </td>


                        <td data-label="Email">

                            {{ $nguoiDung->email }}

                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

    </div>

</section>

@endif



{{-- =========================================================
    SPECIFIC USER BUT NO RECIPIENT
========================================================= --}}

@if (
    $thongBaoPush->doi_tuong_nhan === 'nguoi_dung_cu_the'
    && $recipientCollection->count() === 0
)

<section class="push-panel">

    <div class="push-empty">

        <i class="fa-solid fa-user-slash"></i>

        <h3>Chưa có người nhận</h3>

        <p>
            Thông báo này chưa được gắn với người dùng cụ thể nào.
        </p>

    </div>

</section>

@endif



{{-- =========================================================
    FOOTER ACTIONS
========================================================= --}}

<div class="push-form-actions">





    <form
        action="{{ route('admin.thong-bao-push.destroy', $thongBaoPush) }}"
        method="POST"
        onsubmit="return confirm('Bạn có chắc chắn muốn xóa thông báo này không?');">

        @csrf
        @method('DELETE')

        <button
            type="submit"
            class="push-danger-btn">

            <i class="fa-solid fa-trash"></i>

            Xóa thông báo

        </button>

    </form>

</div>

@endsection
