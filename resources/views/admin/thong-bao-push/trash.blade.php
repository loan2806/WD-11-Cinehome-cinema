@extends('layouts.admin')

@section('page-title', 'Thùng rác thông báo đẩy')

@section('content')

@php
$typeMeta = [
'info' => [
'label' => 'Thông tin',
'icon' => 'fa-circle-info',
'class' => 'is-info',
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
'hang_thanh_vien' => [
'label' => 'Hạng thành viên',
'icon' => 'fa-ranking-star',
'class' => 'is-vip',
],
'khach_hang' => [
'label' => 'Khách hàng',
'icon' => 'fa-user',
'class' => 'is-user',
],
'nguoi_dung_cu_the' => [
'label' => 'Người dùng cụ thể',
'icon' => 'fa-user-pen',
'class' => 'is-specific',
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
];

$activeFilterCount = collect([
request('search'),
request('loai'),
request('trang_thai'),
request('doi_tuong_nhan'),
request('hang_thanh_vien'),
request('nguoi_dung'),
])->filter(fn ($value) => filled($value))->count();
@endphp


<div class="push-admin-page">

    {{-- =========================================================
        HERO
    ========================================================== --}}
    <section class="push-hero push-hero--list">

        <div class="push-hero-content">

            <span class="push-kicker">
                <i class="fa-solid fa-trash-can"></i>
                Thùng rác
            </span>

            <h2>Thùng rác thông báo đẩy</h2>

            <p>
                Quản lý các thông báo đã xóa. Bạn có thể khôi phục
                hoặc xóa vĩnh viễn những thông báo này.
            </p>

            <div class="push-hero-meta">

                <span>
                    <i class="fa-solid fa-trash"></i>
                    {{ number_format($thongBaos->total()) }} thông báo
                </span>

                <span>
                    <i class="fa-solid fa-rotate-left"></i>
                    Có thể khôi phục
                </span>

            </div>

        </div>

        <div class="push-hero-actions">

            <a
                href="{{ route('admin.thong-bao-push.index') }}"
                class="staff-list-secondary-btn">

                <i class="fa-solid fa-arrow-left"></i>
                Quay lại danh sách

            </a>

        </div>

    </section>


    {{-- =========================================================
        CONTENT
    ========================================================== --}}
    <section class="push-panel">

        {{-- =====================================================
            PANEL HEADER
        ====================================================== --}}
        <div class="push-panel-head">

            <div>

                <span>Danh sách</span>

                <h3>
                    <i class="fa-solid fa-trash-can"></i>
                    Thông báo đã xóa
                </h3>

                <p>
                    Các thông báo trong thùng rác có thể được
                    khôi phục hoặc xóa vĩnh viễn.
                </p>

            </div>

            <strong>
                {{ number_format($thongBaos->total()) }} bản ghi
            </strong>

        </div>


        {{-- =====================================================
            FILTER
        ====================================================== --}}
        <form
            method="GET"
            action="{{ route('admin.thong-bao-push.trash') }}"
            class="push-filter">

            {{-- Tìm kiếm --}}
            <label class="push-field push-field--search">

                <span>Tìm kiếm</span>

                <div>
                    <i class="fa-solid fa-magnifying-glass"></i>

                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Nhập tiêu đề thông báo...">
                </div>

            </label>


            {{-- Loại --}}
            <label class="push-field">

                <span>Loại</span>

                <div class="push-custom-select" data-select="loai">
    <select name="loai" class="custom-select-source">
        <option value="">Tất cả loại</option>
        @foreach ($typeMeta as $value => $meta)
        <option value="{{ $value }}" @selected(request('loai')===$value)>
            {{ $meta['label'] }}
        </option>
        @endforeach
    </select>

    <button type="button" class="push-custom-select__trigger" aria-haspopup="listbox" aria-expanded="false">
        <span class="push-custom-select__value">Tất cả loại</span>
        <i class="fa-solid fa-chevron-down push-custom-select__arrow"></i>
    </button>

    <div class="push-custom-select__menu" role="listbox">
        <div class="push-custom-select__option" data-value="" data-icon-color="all">
            <i class="fa-regular fa-circle"></i><span>Tất cả loại</span>
        </div>
        @foreach ($typeMeta as $value => $meta)
        <div class="push-custom-select__option" data-value="{{ $value }}" data-icon-color="{{ $value }}">
            <i class="fa-solid {{ $meta['icon'] }}"></i><span>{{ $meta['label'] }}</span>
        </div>
        @endforeach
    </div>
</div>

            </label>


            {{-- Trạng thái --}}
            <label class="push-field">

                <span>Trạng thái</span>

                <div class="push-custom-select" data-select="trang_thai">
    <select name="trang_thai" class="custom-select-source">
        <option value="">Tất cả trạng thái</option>
        <option value="chua_gui" @selected(request('trang_thai')==='chua_gui')>Nháp</option>
        <option value="da_gui" @selected(request('trang_thai')==='da_gui')>Đã gửi</option>
    </select>

    <button type="button" class="push-custom-select__trigger" aria-haspopup="listbox" aria-expanded="false">
        <span class="push-custom-select__value">Tất cả trạng thái</span>
        <i class="fa-solid fa-chevron-down push-custom-select__arrow"></i>
    </button>

    <div class="push-custom-select__menu" role="listbox">
        <div class="push-custom-select__option" data-value="" data-icon-color="all">
            <i class="fa-regular fa-circle"></i><span>Tất cả trạng thái</span>
        </div>
        <div class="push-custom-select__option" data-value="chua_gui" data-icon-color="draft">
            <i class="fa-regular fa-clock"></i><span>Nháp</span>
        </div>
        <div class="push-custom-select__option" data-value="da_gui" data-icon-color="sent">
            <i class="fa-regular fa-circle-check"></i><span>Đã gửi</span>
        </div>
    </div>
</div>

            </label>


            {{-- Người nhận --}}
            <label class="push-field">

                <span>Người nhận</span>

                <div class="push-custom-select" data-select="doi_tuong_nhan">
    <select name="doi_tuong_nhan" id="doi_tuong_nhan" class="custom-select-source">
        <option value="">Tất cả nhóm</option>
        @foreach ($audienceMeta as $value => $meta)
        <option value="{{ $value }}" @selected(request('doi_tuong_nhan')===$value)>
            {{ $meta['label'] }}
        </option>
        @endforeach
    </select>

    <button type="button" class="push-custom-select__trigger" aria-haspopup="listbox" aria-expanded="false">
        <span class="push-custom-select__value">Tất cả nhóm</span>
        <i class="fa-solid fa-chevron-down push-custom-select__arrow"></i>
    </button>

    <div class="push-custom-select__menu" role="listbox">
        <div class="push-custom-select__option" data-value="" data-icon-color="all">
            <i class="fa-solid fa-cube"></i><span>Tất cả nhóm</span>
        </div>
        @foreach ($audienceMeta as $value => $meta)
        <div class="push-custom-select__option" data-value="{{ $value }}" data-icon-color="{{ $value }}">
            <i class="fa-solid {{ $meta['icon'] }}"></i><span>{{ $meta['label'] }}</span>
        </div>
        @endforeach
    </div>
</div>

            </label>


            {{-- Người dùng cụ thể --}}
            <div
                class="push-field"
                id="nguoi-dung-filter"
                style="{{ request('doi_tuong_nhan') === 'nguoi_dung_cu_the' ? '' : 'display:none;' }}">

                <span>Người dùng</span>

                <div>

                    <input
                        type="text"
                        name="nguoi_dung"
                        value="{{ request('nguoi_dung') }}"
                        placeholder="Nhập tên hoặc email...">

                </div>

            </div>


            {{-- Hạng thành viên --}}
            <label
                class="push-field"
                id="hang-thanh-vien-filter"
                style="{{ request('doi_tuong_nhan') === 'hang_thanh_vien' ? '' : 'display:none;' }}">

                <span>Hạng thành viên</span>

                <div class="push-custom-select" data-select="hang_thanh_vien">
    <select name="hang_thanh_vien" class="custom-select-source">
        <option value="">Tất cả hạng</option>
        <option value="member" @selected(request('hang_thanh_vien')==='member')>Member</option>
        <option value="silver" @selected(request('hang_thanh_vien')==='silver')>Silver</option>
        <option value="gold" @selected(request('hang_thanh_vien')==='gold')>Gold</option>
        <option value="platinum" @selected(request('hang_thanh_vien')==='platinum')>Platinum</option>
    </select>

    <button type="button" class="push-custom-select__trigger" aria-haspopup="listbox" aria-expanded="false">
        <span class="push-custom-select__value">Tất cả hạng</span>
        <i class="fa-solid fa-chevron-down push-custom-select__arrow"></i>
    </button>

    <div class="push-custom-select__menu" role="listbox">
        <div class="push-custom-select__option" data-value="" data-icon-color="all">
            <i class="fa-solid fa-layer-group"></i><span>Tất cả hạng</span>
        </div>
        <div class="push-custom-select__option" data-value="member" data-icon-color="member">
            <i class="fa-solid fa-user"></i><span>Member</span>
        </div>
        <div class="push-custom-select__option" data-value="silver" data-icon-color="silver">
            <i class="fa-solid fa-medal"></i><span>Silver</span>
        </div>
        <div class="push-custom-select__option" data-value="gold" data-icon-color="gold">
            <i class="fa-solid fa-medal"></i><span>Gold</span>
        </div>
        <div class="push-custom-select__option" data-value="platinum" data-icon-color="platinum">
            <i class="fa-solid fa-crown"></i><span>Platinum</span>
        </div>
    </div>
</div>

            </label>


            {{-- Nút lọc --}}
            <div class="push-filter-actions">

                <button
                    type="submit"
                    class="push-filter-btn">

                    <i class="fa-solid fa-filter"></i>
                    Lọc

                </button>


                @if ($activeFilterCount > 0)

                <a
                    href="{{ route('admin.thong-bao-push.trash') }}"
                    class="push-reset-btn"
                    title="Xóa bộ lọc">

                    <i class="fa-solid fa-rotate-left"></i>

                </a>

                @endif

            </div>

        </form>


        {{-- =====================================================
            TABLE
        ====================================================== --}}
        <div class="push-table-wrap">

            @if ($thongBaos->count())

            <table class="push-table">

                <thead>

                    <tr>

                        <th>STT</th>

                        @if(request('doi_tuong_nhan') === 'nguoi_dung_cu_the')
                        <th>Người dùng</th>
                        @endif

                        <th>Thông báo</th>

                        <th>Loại</th>

                        <th>Người nhận</th>

                        <th>Người tạo</th>

                        <th>Ngày xóa</th>

                        <th class="is-right">
                            Thao tác
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @foreach ($thongBaos as $index => $thongBao)

                    @php

                    $type = $typeMeta[$thongBao->loai]
                    ?? [
                    'label' => ucfirst($thongBao->loai),
                    'icon' => 'fa-bell',
                    'class' => 'is-system',
                    ];

                    $audience = $audienceMeta[$thongBao->doi_tuong_nhan]
                    ?? [
                    'label' => 'Không xác định',
                    'icon' => 'fa-users',
                    'class' => 'is-all',
                    ];

                    $authorName =
                    $thongBao->nguoiTao->ho_ten
                    ?? 'Hệ thống';

                    @endphp


                    <tr>

                        {{-- STT --}}
                        <td>
                            <span class="table-index">
                                {{ $thongBaos->firstItem() + $index }}
                            </span>
                        </td>


                        {{-- NGƯỜI DÙNG CỤ THỂ --}}
                        @if(request('doi_tuong_nhan') === 'nguoi_dung_cu_the')
                        <td data-label="Người dùng">

                            @php
                            $nguoiNhan = \App\Models\ThongBaoPushNguoiDung::with('nguoiDung')
                            ->where('thong_bao_push_id', $thongBao->id)
                            ->get()
                            ->pluck('nguoiDung')
                            ->filter();
                            @endphp

                            @foreach($nguoiNhan as $user)
                            <div class="user-cell">

                                <div class="user-name">
                                    {{ $user->ho_ten }}
                                </div>

                                <div class="user-email">
                                    {{ $user->email }}
                                </div>

                            </div>
                            @endforeach

                        </td>
                        @endif



                        {{-- THÔNG BÁO --}}
                        <td data-label="Thông báo">

                            <a
                                href="{{ route('admin.thong-bao-push.show', $thongBao) }}"
                                class="push-message-cell">

                                <strong>
                                    {{ \Illuminate\Support\Str::limit($thongBao->tieu_de, 68) }}
                                </strong>

                                <small>
                                    {{ \Illuminate\Support\Str::limit($thongBao->noi_dung, 96) }}
                                </small>

                            </a>

                        </td>


                        {{-- LOẠI --}}
                        <td data-label="Loại">

                            <span
                                class="push-chip {{ $type['class'] }}">

                                <i
                                    class="fa-solid {{ $type['icon'] }}">
                                </i>

                                {{ $type['label'] }}

                            </span>

                        </td>


                        {{-- NGƯỜI NHẬN --}}
                        <td data-label="Người nhận">

                            <span
                                class="push-chip {{ $audience['class'] }}">

                                <i
                                    class="fa-solid {{ $audience['icon'] }}">
                                </i>

                                {{ $audience['label'] }}

                            </span>



                        </td>


                        {{-- NGƯỜI TẠO --}}
                        <td data-label="Người tạo">

                            <div class="push-author">

                                <span>
                                    {{ strtoupper(mb_substr($authorName, 0, 1)) }}
                                </span>

                                <strong>
                                    {{ $authorName }}
                                </strong>

                            </div>

                        </td>


                        {{-- NGÀY XÓA --}}
                        <td data-label="Ngày xóa">

                            @if ($thongBao->deleted_at)

                            <span class="push-date">

                                <i class="fa-regular fa-calendar-xmark"></i>

                                {{ $thongBao->deleted_at->format('d/m/Y') }}

                                <small>
                                    {{ $thongBao->deleted_at->format('H:i') }}
                                </small>

                            </span>

                            @else

                            <span class="text-muted">
                                —
                            </span>

                            @endif

                        </td>


                        {{-- THAO TÁC --}}
                        <td
                            data-label="Thao tác"
                            class="is-right">

                            <div class="push-action-buttons">


                                <form
                                    method="POST"
                                    action="{{ route('admin.thong-bao-push.restore', ['thongBao' => $thongBao->id]) }}"
                                    class="restore-form">

                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="staff-action-btn is-unlock"
                                        title="Khôi phục">

                                        <i class="fa-solid fa-rotate-left"></i>

                                    </button>

                                </form>
                                {{-- XÓA VĨNH VIỄN --}}
                                <form
                                    action="{{ route('admin.thong-bao-push.force-delete', $thongBao->id) }}"
                                    method="POST"
                                    class="force-delete-form">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="push-icon-btn is-delete"
                                        title="Xóa vĩnh viễn">

                                        <i class="fa-solid fa-trash-can"></i>

                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>

                    @endforeach

                </tbody>

            </table>

            @else

            {{-- EMPTY --}}
            <div class="push-empty">

                <i class="fa-solid fa-trash-can"></i>

                <h3>
                    Thùng rác đang trống
                </h3>

                <p>
                    Chưa có thông báo đẩy nào được xóa.
                </p>

                <a
                    href="{{ route('admin.thong-bao-push.index') }}"
                    class="push-primary-btn">

                    <i class="fa-solid fa-bell"></i>

                    Quay lại thông báo

                </a>

            </div>

            @endif

        </div>


        {{-- =====================================================
            PAGINATION
        ====================================================== --}}
        @if ($thongBaos->hasPages())

        @php

        $currentPage = $thongBaos->currentPage();

        $lastPage = $thongBaos->lastPage();

        $startPage = max(1, $currentPage - 1);

        $endPage = min($lastPage, $currentPage + 1);

        @endphp


        <div class="admin-pagination push-pagination">

            <div class="admin-pagination__meta">

                Hiển thị

                <strong>
                    {{ $thongBaos->firstItem() ?? 0 }}
                    -
                    {{ $thongBaos->lastItem() ?? 0 }}
                </strong>

                trong

                <strong>
                    {{ $thongBaos->total() }}
                </strong>

                thông báo

            </div>


            <div class="admin-pagination__controls">

                {{-- PREVIOUS --}}
                @if ($thongBaos->onFirstPage())

                <span class="admin-pagination__btn is-disabled">
                    <i class="fa-solid fa-chevron-left"></i>
                    Trước
                </span>

                @else

                <a
                    href="{{ $thongBaos->previousPageUrl() }}"
                    class="admin-pagination__btn">

                    <i class="fa-solid fa-chevron-left"></i>
                    Trước

                </a>

                @endif


                {{-- FIRST PAGE --}}
                @if ($startPage > 1)

                <a
                    href="{{ $thongBaos->url(1) }}"
                    class="admin-pagination__page">

                    1

                </a>

                @if ($startPage > 2)

                <span class="admin-pagination__dots">
                    ...
                </span>

                @endif

                @endif


                {{-- PAGES --}}
                @foreach (
                $thongBaos->getUrlRange($startPage, $endPage)
                as $page => $url
                )

                @if ($page === $currentPage)

                <span class="admin-pagination__page is-active">
                    {{ $page }}
                </span>

                @else

                <a
                    href="{{ $url }}"
                    class="admin-pagination__page">

                    {{ $page }}

                </a>

                @endif

                @endforeach


                {{-- LAST PAGE --}}
                @if ($endPage < $lastPage)

                    @if ($endPage < $lastPage - 1)

                    <span class="admin-pagination__dots">
                    ...
                    </span>

                    @endif

                    <a
                        href="{{ $thongBaos->url($lastPage) }}"
                        class="admin-pagination__page">

                        {{ $lastPage }}

                    </a>

                    @endif


                    {{-- NEXT --}}
                    @if ($thongBaos->hasMorePages())

                    <a
                        href="{{ $thongBaos->nextPageUrl() }}"
                        class="admin-pagination__btn">

                        Sau
                        <i class="fa-solid fa-chevron-right"></i>

                    </a>

                    @else

                    <span class="admin-pagination__btn is-disabled">

                        Sau
                        <i class="fa-solid fa-chevron-right"></i>

                    </span>

                    @endif

            </div>

        </div>

        @endif

    </section>

</div>


{{-- =============================================================
    STYLE BỔ SUNG CHO TRASH
============================================================= --}}

<style>
/* =========================================================
   CUSTOM DROPDOWN - TRASH PAGE
   Đồng bộ với trang Quản lý thông báo đẩy
========================================================= */

.push-panel,
.push-panel-head,
.push-filter,
.push-filter-actions,
.push-field,
.push-custom-select {
    overflow: visible !important;
}

.push-panel {
    position: relative;
    z-index: 10;
}

.push-filter {
    position: relative;
    z-index: 1000 !important;
}

.push-field {
    position: relative;
    z-index: 10;
}

.push-custom-select {
    position: relative;
    width: 100%;
    z-index: 20;
}

.push-custom-select.is-open {
    z-index: 99999 !important;
}

.custom-select-source {
    display: none !important;
}

.push-custom-select__trigger {
    width: 100%;
    min-height: 46px;
    padding: 0 14px 0 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;

    border: 1px solid #303642;
    border-radius: 14px;
    background: #171b23 !important;
    color: #f5f5f5 !important;

    font: inherit;
    font-size: 14px;
    font-weight: 600;
    line-height: 1.2;

    cursor: pointer;
    outline: none;
    box-sizing: border-box;

    transition: border-color .18s ease, background .18s ease;
}

.push-custom-select__trigger:hover,
.push-custom-select.is-open .push-custom-select__trigger {
    border-color: #ff3347 !important;
    background: #1b1f28 !important;
}

.push-custom-select__value {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.push-custom-select__arrow {
    flex: 0 0 auto;
    color: #9ca3af !important;
    font-size: 12px;
    transition: transform .18s ease;
}

.push-custom-select.is-open .push-custom-select__arrow {
    transform: rotate(180deg);
}

.push-custom-select__menu {
    position: absolute !important;
    left: 0;
    right: 0;
    top: calc(100% + 6px);

    padding: 0;
    margin: 0;

    background: #171b23 !important;
    border: 1px solid #303642 !important;
    border-radius: 14px !important;

    box-shadow: 0 14px 32px rgba(0,0,0,.48);

    opacity: 0 !important;
    visibility: hidden;
    pointer-events: none;

    transform: translateY(-5px);

    z-index: 999999 !important;

    overflow: hidden !important;
    backdrop-filter: none !important;
    -webkit-backdrop-filter: none !important;

    transition:
        opacity .15s ease,
        transform .15s ease,
        visibility .15s ease;
}

.push-custom-select.is-open .push-custom-select__menu {
    opacity: 1 !important;
    visibility: visible;
    pointer-events: auto !important;
    transform: translateY(0);
}

/* Nếu JS phát hiện không đủ chỗ phía dưới */
.push-custom-select.open-up .push-custom-select__menu {
    top: auto !important;
    bottom: calc(100% + 6px) !important;
    transform: translateY(5px);
}

.push-custom-select.open-up.is-open .push-custom-select__menu {
    transform: translateY(0);
}

.push-custom-select__option {
    min-height: 46px;
    width: 100%;
    padding: 0 14px;

    display: flex;
    align-items: center;
    gap: 10px;

    background: #171b23 !important;
    color: #e5e7eb !important;

    border: 0;
    border-bottom: 1px solid #303642;

    font-size: 14px;
    font-weight: 600;
    line-height: 1.2;

    cursor: pointer;
    user-select: none;
    pointer-events: auto !important;
    box-sizing: border-box;
}

.push-custom-select__option:first-child {
    border-radius: 13px 13px 0 0;
}

.push-custom-select__option:last-child {
    border-bottom: none;
    border-radius: 0 0 13px 13px;
}

.push-custom-select__option:hover {
    background: #252b36 !important;
    color: #fff !important;
}

.push-custom-select__option.is-selected {
    background: #2c202c !important;
    color: #fff !important;
}

/* =========================================================
   ICON - TẤT CẢ ĐỀU CÓ MÀU
========================================================= */

.push-custom-select__option i {
    width: 18px;
    min-width: 18px;
    text-align: center;
    font-size: 14px;
    margin: 0;
}

/* Chung */
.push-custom-select__option[data-icon-color="all"] i {
    color: #ff5262 !important;
}

/* Loại */
.push-custom-select__option[data-icon-color="info"] i {
    color: #60a5fa !important;
}

.push-custom-select__option[data-icon-color="warning"] i {
    color: #fbbf24 !important;
}

.push-custom-select__option[data-icon-color="promo"] i {
    color: #c084fc !important;
}

.push-custom-select__option[data-icon-color="system"] i {
    color: #38bdf8 !important;
}

/* Trạng thái */
.push-custom-select__option[data-icon-color="draft"] i {
    color: #ff5262 !important;
}

.push-custom-select__option[data-icon-color="sent"] i {
    color: #34d399 !important;
}

/* Người nhận */
.push-custom-select__option[data-icon-color="all"] i {
    color: #ff5262 !important;
}

.push-custom-select__option[data-icon-color="hang_thanh_vien"] i {
    color: #fbbf24 !important;
}

.push-custom-select__option[data-icon-color="khach_hang"] i {
    color: #60a5fa !important;
}

.push-custom-select__option[data-icon-color="nguoi_dung_cu_the"] i {
    color: #c084fc !important;
}

.push-custom-select__option[data-icon-color="nhan_vien"] i {
    color: #34d399 !important;
}

.push-custom-select__option[data-icon-color="quan_ly"] i {
    color: #a78bfa !important;
}

/* Hạng */
.push-custom-select__option[data-icon-color="member"] i {
    color: #60a5fa !important;
}

.push-custom-select__option[data-icon-color="silver"] i {
    color: #cbd5e1 !important;
}

.push-custom-select__option[data-icon-color="gold"] i {
    color: #fbbf24 !important;
}

.push-custom-select__option[data-icon-color="platinum"] i {
    color: #c084fc !important;
}

/* Giữ màu icon khi hover */
.push-custom-select__option:hover i,
.push-custom-select__option.is-selected i {
    filter: brightness(1.08);
}

/* Không cho table đè menu */
.push-table-wrap {
    position: relative;
    z-index: 1;
}

@media (max-width: 768px) {
    .push-custom-select__menu {
        max-width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* =====================================================
       CUSTOM DROPDOWN
    ===================================================== */

    const dropdowns = document.querySelectorAll('.push-custom-select');

    function closeAll(except = null) {
        dropdowns.forEach(function (dropdown) {
            if (dropdown !== except) {
                dropdown.classList.remove('is-open', 'open-up');

                const trigger = dropdown.querySelector(
                    '.push-custom-select__trigger'
                );

                if (trigger) {
                    trigger.setAttribute('aria-expanded', 'false');
                }
            }
        });
    }

    dropdowns.forEach(function (dropdown) {

        const trigger = dropdown.querySelector(
            '.push-custom-select__trigger'
        );

        const menu = dropdown.querySelector(
            '.push-custom-select__menu'
        );

        const select = dropdown.querySelector(
            '.custom-select-source'
        );

        const valueDisplay = dropdown.querySelector(
            '.push-custom-select__value'
        );

        const options = dropdown.querySelectorAll(
            '.push-custom-select__option'
        );

        if (!trigger || !menu || !select || !valueDisplay) {
            return;
        }

        function sync() {
            const selected = select.options[select.selectedIndex];

            if (selected) {
                valueDisplay.textContent =
                    selected.textContent.trim();
            }

            options.forEach(function (option) {
                option.classList.toggle(
                    'is-selected',
                    option.dataset.value === select.value
                );
            });
        }

        function calculateDirection() {
            dropdown.classList.remove('open-up');

            const rect = trigger.getBoundingClientRect();

            const spaceBelow =
                window.innerHeight - rect.bottom;

            const spaceAbove =
                rect.top;

            const menuHeight =
                menu.scrollHeight;

            if (
                spaceBelow < menuHeight &&
                spaceAbove > spaceBelow
            ) {
                dropdown.classList.add('open-up');
            }
        }

        sync();

        trigger.addEventListener('click', function (event) {

            event.preventDefault();
            event.stopPropagation();

            const wasOpen =
                dropdown.classList.contains('is-open');

            closeAll();

            if (wasOpen) {
                return;
            }

            dropdown.classList.add('is-open');

            trigger.setAttribute(
                'aria-expanded',
                'true'
            );

            requestAnimationFrame(function () {
                calculateDirection();
            });
        });

        options.forEach(function (option) {

            option.addEventListener('click', function (event) {

                event.preventDefault();
                event.stopPropagation();

                select.value = this.dataset.value;

                sync();

                dropdown.classList.remove(
                    'is-open',
                    'open-up'
                );

                trigger.setAttribute(
                    'aria-expanded',
                    'false'
                );

                select.dispatchEvent(
                    new Event('change', {
                        bubbles: true
                    })
                );
            });

        });

        select.addEventListener('change', sync);
    });

    /* Click ngoài dropdown */
    document.addEventListener('click', function (event) {

        if (!event.target.closest('.push-custom-select')) {
            closeAll();
        }

    });

    /* Nếu resize/scroll khi đang mở thì tính lại hướng */
    window.addEventListener('resize', function () {

        document
            .querySelectorAll('.push-custom-select.is-open')
            .forEach(function (dropdown) {

                const trigger =
                    dropdown.querySelector(
                        '.push-custom-select__trigger'
                    );

                const menu =
                    dropdown.querySelector(
                        '.push-custom-select__menu'
                    );

                if (!trigger || !menu) return;

                dropdown.classList.remove('open-up');

                const rect =
                    trigger.getBoundingClientRect();

                const spaceBelow =
                    window.innerHeight - rect.bottom;

                const spaceAbove =
                    rect.top;

                if (
                    spaceBelow < menu.scrollHeight &&
                    spaceAbove > spaceBelow
                ) {
                    dropdown.classList.add('open-up');
                }
            });
    });

    /* =====================================================
       HIỆN / ẨN BỘ LỌC THEO NGƯỜI NHẬN
    ===================================================== */

    const audienceSelect =
        document.getElementById('doi_tuong_nhan');

    const memberRankFilter =
        document.getElementById('hang-thanh-vien-filter');

    const userFilter =
        document.getElementById('nguoi-dung-filter');

    function toggleTrashFilters() {

        if (!audienceSelect) {
            return;
        }

        const value = audienceSelect.value;

        if (memberRankFilter) {
            memberRankFilter.style.display =
                value === 'hang_thanh_vien'
                    ? ''
                    : 'none';
        }

        if (userFilter) {
            userFilter.style.display =
                value === 'nguoi_dung_cu_the'
                    ? ''
                    : 'none';
        }
    }

    if (audienceSelect) {
        audienceSelect.addEventListener(
            'change',
            toggleTrashFilters
        );
    }

    toggleTrashFilters();

});
</script>


<style>
    /*
     * Dùng chung màu với index
     */

    .member-rank {
        display: block;
        margin-top: 5px;
        font-size: 11px;
        color: #94a3b8;
    }

    /* =========================================================
   NGƯỜI DÙNG CỤ THỂ
========================================================= */

    .user-cell {
        display: flex;
        flex-direction: column;
        gap: 3px;
        min-width: 150px;
    }

    .user-name {
        font-size: 13px;
        font-weight: 700;
        color: #f8fafc;
        line-height: 1.3;
    }

    .user-email {
        font-size: 11px;
        color: #94a3b8;
        line-height: 1.3;
    }




    /* =========================================================
   MÀU LOẠI THÔNG BÁO
   ========================================================= */

    /* Thông tin - xanh dương */
    .push-chip.is-info {
        background: rgba(59, 130, 246, 0.12);
        border: 1px solid rgba(59, 130, 246, 0.38);
        color: #7db7ff;
    }

    /* Cảnh báo - vàng/cam */
    .push-chip.is-warning {
        background: rgba(245, 158, 11, 0.12);
        border: 1px solid rgba(245, 158, 11, 0.38);
        color: #ffc85c;
    }

    /* Khuyến mãi - tím */
    .push-chip.is-promo {
        background: rgba(139, 92, 246, 0.14);
        border: 1px solid rgba(139, 92, 246, 0.38);
        color: #c4a7ff;
    }

    /* Hệ thống - xám */
    .push-chip.is-system {
        background: rgba(148, 163, 184, 0.10);
        border: 1px solid rgba(148, 163, 184, 0.28);
        color: #cbd5e1;
    }


    /* =========================================================
   MÀU ĐỐI TƯỢNG NHẬN
   ========================================================= */

    /* Tất cả người dùng - vàng */
    .push-chip.is-all {
        background: rgba(180, 120, 20, 0.15);
        border: 1px solid rgba(245, 180, 50, 0.38);
        color: #f5c76b;
    }

    /* Hạng thành viên - vàng */
    .push-chip.is-vip {
        background: rgba(180, 120, 20, 0.15);
        border: 1px solid rgba(245, 180, 50, 0.38);
        color: #f5c76b;
    }

    /* Khách hàng - xanh dương */
    .push-chip.is-user {
        background: rgba(37, 99, 235, 0.13);
        border: 1px solid rgba(59, 130, 246, 0.38);
        color: #80b8ff;
    }

    /* Người dùng cụ thể - tím */
    .push-chip.is-specific {
        background: rgba(124, 58, 237, 0.14);
        border: 1px solid rgba(139, 92, 246, 0.38);
        color: #c5a8ff;
    }

    /* Nhân viên - xanh ngọc */
    .push-chip.is-staff {
        background: rgba(16, 185, 129, 0.13);
        border: 1px solid rgba(16, 185, 129, 0.36);
        color: #72e3bb;
    }

    /* Quản lý - tím */
    .push-chip.is-admin {
        background: rgba(139, 92, 246, 0.15);
        border: 1px solid rgba(168, 120, 255, 0.38);
        color: #c8a9ff;
    }


    /* =========================================================
   ICON TRONG BADGE
   ========================================================= */

    .push-chip i {
        margin-right: 4px;
        font-size: 10px;
    }
</style>


<script>
    document.addEventListener('DOMContentLoaded', function() {

        /* =========================================================
           1. LẤY CÁC ELEMENT
        ========================================================= */

        const audienceSelect =
            document.getElementById('doi_tuong_nhan');

        const memberRankFilter =
            document.getElementById('hang-thanh-vien-filter');

        const userFilter =
            document.getElementById('nguoi-dung-filter');


        /* =========================================================
           2. HIỆN / ẨN BỘ LỌC THEO NGƯỜI NHẬN
        ========================================================= */

        function toggleFilters() {

            if (!audienceSelect) {
                return;
            }

            const value = audienceSelect.value;


            /* -----------------------------------------------------
               HẠNG THÀNH VIÊN
            ----------------------------------------------------- */

            if (memberRankFilter) {

                if (value === 'hang_thanh_vien') {
                    memberRankFilter.style.display = '';
                } else {
                    memberRankFilter.style.display = 'none';
                }

            }


            /* -----------------------------------------------------
               NGƯỜI DÙNG CỤ THỂ
            ----------------------------------------------------- */

            if (userFilter) {

                if (value === 'nguoi_dung_cu_the') {
                    userFilter.style.display = '';
                } else {
                    userFilter.style.display = 'none';
                }

            }

        }


        /* =========================================================
           3. KHI THAY ĐỔI NGƯỜI NHẬN
        ========================================================= */

        if (audienceSelect) {

            audienceSelect.addEventListener(
                'change',
                function() {

                    toggleFilters();

                }
            );

        }


        /* =========================================================
           4. CHẠY NGAY KHI LOAD TRANG
           
           Ví dụ:
           ?doi_tuong_nhan=nguoi_dung_cu_the

           => tự hiện ô "Người dùng"

           ?doi_tuong_nhan=hang_thanh_vien

           => tự hiện ô "Hạng thành viên"
        ========================================================= */

        toggleFilters();


        /* =========================================================
           5. XÁC NHẬN KHÔI PHỤC
        ========================================================= */

        document
            .querySelectorAll('.restore-form')
            .forEach(function(form) {

                form.addEventListener(
                    'submit',
                    function(event) {

                        const confirmed = confirm(
                            'Bạn có chắc chắn muốn khôi phục thông báo này?'
                        );

                        if (!confirmed) {
                            event.preventDefault();
                        }

                    }
                );

            });


        /* =========================================================
           6. XÁC NHẬN XÓA VĨNH VIỄN
        ========================================================= */

        document
            .querySelectorAll('.force-delete-form')
            .forEach(function(form) {

                form.addEventListener(
                    'submit',
                    function(event) {

                        const confirmed = confirm(
                            'Thông báo sẽ bị xóa vĩnh viễn và không thể khôi phục. Bạn có chắc chắn muốn tiếp tục?'
                        );

                        if (!confirmed) {
                            event.preventDefault();
                        }

                    }
                );

            });


        /* =========================================================
           7. HỖ TRỢ NÚT KHÔI PHỤC
           
           Nếu form khôi phục của bạn đang dùng class
           "staff-action-btn" thay vì "restore-form",
           đoạn này vẫn bắt được.
        ========================================================= */

        document
            .querySelectorAll('form')
            .forEach(function(form) {

                const action = form.getAttribute('action') || '';

                if (
                    action.includes('/restore') &&
                    !form.classList.contains('restore-form')
                ) {

                    form.addEventListener(
                        'submit',
                        function(event) {

                            const confirmed = confirm(
                                'Bạn có chắc chắn muốn khôi phục thông báo này?'
                            );

                            if (!confirmed) {
                                event.preventDefault();
                            }

                        }
                    );

                }

            });


        /* =========================================================
           8. HỖ TRỢ XÓA VĨNH VIỄN
           
           Tránh trường hợp form không có class
           "force-delete-form".
        ========================================================= */

        document
            .querySelectorAll('form')
            .forEach(function(form) {

                const action = form.getAttribute('action') || '';

                if (
                    action.includes('/force-delete') &&
                    !form.classList.contains('force-delete-form')
                ) {

                    form.addEventListener(
                        'submit',
                        function(event) {

                            const confirmed = confirm(
                                'Thông báo sẽ bị xóa vĩnh viễn và không thể khôi phục. Bạn có chắc chắn muốn tiếp tục?'
                            );

                            if (!confirmed) {
                                event.preventDefault();
                            }

                        }
                    );

                }

            });

    });
</script>

@endsection