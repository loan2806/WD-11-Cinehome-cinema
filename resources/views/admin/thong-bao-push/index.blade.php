@extends('layouts.admin')

@section('page-title', 'Quản lý Thông báo đẩy')

@section('content')
@php
$typeMeta = [
'info' => ['label' => 'Thông tin', 'icon' => 'fa-circle-info', 'class' => 'is-info'],
'warning' => ['label' => 'Cảnh báo', 'icon' => 'fa-triangle-exclamation', 'class' => 'is-warning'],
'promo' => ['label' => 'Khuyến mãi', 'icon' => 'fa-gift', 'class' => 'is-promo'],
'system' => ['label' => 'Hệ thống', 'icon' => 'fa-gear', 'class' => 'is-system'],
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
request('doi_tuong_nhan'),
request('hang_thanh_vien'),
request('nguoi_dung'),
request('trang_thai'),
])->filter(fn ($value) => filled($value))->count();
@endphp

<div class="push-admin-page">
    <section class="push-hero push-hero--list">
        <div class="push-hero-content">
            <span class="push-kicker">
                <i class="fa-solid fa-satellite-dish"></i>
                Trung tâm tương tác
            </span>
            <h2>Quản lý thông báo đẩy</h2>
            <p>Theo dõi, lọc và gửi thông báo đến đúng nhóm người dùng CineHome trong một màn hình gọn gàng.</p>
            <div class="push-hero-meta">
                <span><i class="fa-solid fa-layer-group"></i>{{ number_format($summary['total'] ?? 0) }} thông báo</span>
                <span><i class="fa-solid fa-paper-plane"></i>{{ number_format($summary['sent'] ?? 0) }} đã gửi</span>
                <span><i class="fa-solid fa-calendar-day"></i>{{ number_format($summary['today'] ?? 0) }} hôm nay</span>
            </div>
        </div>
        <div class="push-hero-actions">
            <a href="{{ route('admin.thong-bao-push.trash') }}"
                class="staff-list-secondary-btn">
                <i class="fa-solid fa-trash"></i>
                Thùng rác
            </a>

            <a href="{{ route('admin.thong-bao-push.create') }}"
                class="push-primary-btn">
                <i class="fa-solid fa-plus"></i>
                Tạo thông báo mới
            </a>
        </div>
    </section>

    <div class="push-stat-grid">
        <article class="push-stat-card">
            <span class="is-total"><i class="fa-solid fa-bell"></i></span>
            <small>Tổng thông báo</small>
            <strong>{{ number_format($summary['total'] ?? 0) }}</strong>
        </article>
        <article class="push-stat-card">
            <span class="is-sent"><i class="fa-solid fa-paper-plane"></i></span>
            <small>Đã gửi</small>
            <strong>{{ number_format($summary['sent'] ?? 0) }}</strong>
        </article>
        <article class="push-stat-card">
            <span class="is-promo"><i class="fa-solid fa-gift"></i></span>
            <small>Khuyến mãi</small>
            <strong>{{ number_format($summary['promo'] ?? 0) }}</strong>
        </article>
        <article class="push-stat-card">
            <span class="is-today"><i class="fa-solid fa-calendar-check"></i></span>
            <small>Tạo hôm nay</small>
            <strong>{{ number_format($summary['today'] ?? 0) }}</strong>
        </article>
    </div>

    <section class="push-panel">
        <div class="push-panel-head">
            <div>
                <span>Danh sách</span>
                <h3>Thông báo đang quản lý</h3>
                <p>Lọc nhanh theo tiêu đề, loại thông báo, nhóm nhận và trạng thái gửi.</p>
            </div>
            <strong>{{ number_format($thongBaos->total()) }} bản ghi</strong>
        </div>

        <form method="GET" action="{{ route('admin.thong-bao-push.index') }}" class="push-filter">
            <label class="push-field push-field--search">
                <span>Tìm kiếm</span>
                <div>
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nhập tiêu đề thông báo...">
                </div>
            </label>

            <div class="push-field">
                <span>Loại</span>
                <div class="push-custom-select" data-select="loai">
                    <select name="loai" class="push-custom-select__source">
                        <option value="">Tất cả loại</option>
                        @foreach ($typeMeta as $value => $meta)
                        <option value="{{ $value }}" @selected(request('loai')===$value)>{{ $meta['label'] }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="push-custom-select__trigger" aria-haspopup="listbox" aria-expanded="false">
                        <span class="push-custom-select__value"></span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                    <div class="push-custom-select__menu" role="listbox">
                        <div class="push-custom-select__option" data-value="">
                            <i class="fa-regular fa-circle"></i><span>Tất cả loại</span>
                        </div>
                        @foreach ($typeMeta as $value => $meta)
                        <div class="push-custom-select__option" data-value="{{ $value }}">
                            <i class="fa-solid {{ $meta['icon'] }}"></i><span>{{ $meta['label'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="push-field">
                <span>Trạng thái</span>
                <div class="push-custom-select" data-select="trang_thai">
                    <select name="trang_thai" class="push-custom-select__source">
                        <option value="">Tất cả trạng thái</option>
                        <option value="chua_gui" @selected(request('trang_thai')==='chua_gui')>Nháp</option>
                        <option value="da_gui" @selected(request('trang_thai')==='da_gui')>Đã gửi</option>
                    </select>
                    <button type="button" class="push-custom-select__trigger" aria-haspopup="listbox" aria-expanded="false">
                        <span class="push-custom-select__value"></span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                    <div class="push-custom-select__menu" role="listbox">
                        <div class="push-custom-select__option" data-value="">
                            <i class="fa-regular fa-circle"></i><span>Tất cả trạng thái</span>
                        </div>
                        <div class="push-custom-select__option" data-value="chua_gui">
                            <i class="fa-regular fa-clock"></i><span>Nháp</span>
                        </div>
                        <div class="push-custom-select__option" data-value="da_gui">
                            <i class="fa-regular fa-circle-check"></i><span>Đã gửi</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="push-field">
                <span>Người nhận</span>
                <div class="push-custom-select" data-select="doi_tuong_nhan">
                    <select name="doi_tuong_nhan" id="doi_tuong_nhan" class="push-custom-select__source">
                        <option value="">Tất cả nhóm</option>
                        @foreach ($audienceMeta as $value => $meta)
                        <option value="{{ $value }}" @selected(request('doi_tuong_nhan')===$value)>{{ $meta['label'] }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="push-custom-select__trigger" aria-haspopup="listbox" aria-expanded="false">
                        <span class="push-custom-select__value"></span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                    <div class="push-custom-select__menu" role="listbox">
                        <div class="push-custom-select__option" data-value="">
                            <i class="fa-solid fa-cube"></i><span>Tất cả nhóm</span>
                        </div>
                        @foreach ($audienceMeta as $value => $meta)
                        <div class="push-custom-select__option" data-value="{{ $value }}">
                            <i class="fa-solid {{ $meta['icon'] }}"></i><span>{{ $meta['label'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

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

            {{-- Chỉ hiện khi chọn Hạng thành viên --}}
            <div
                class="push-field"
                id="hang-thanh-vien-filter"
                style="{{ request('doi_tuong_nhan') === 'hang_thanh_vien' ? '' : 'display:none;' }}">
                <span>Hạng thành viên</span>
                <div class="push-custom-select" data-select="hang_thanh_vien">
                    <select name="hang_thanh_vien" class="push-custom-select__source">
                        <option value="">Tất cả hạng</option>
                        <option value="member" @selected(request('hang_thanh_vien')==='member')>Member</option>
                        <option value="silver" @selected(request('hang_thanh_vien')==='silver')>Silver</option>
                        <option value="gold" @selected(request('hang_thanh_vien')==='gold')>Gold</option>
                        <option value="platinum" @selected(request('hang_thanh_vien')==='platinum')>Platinum</option>
                    </select>
                    <button type="button" class="push-custom-select__trigger" aria-haspopup="listbox" aria-expanded="false">
                        <span class="push-custom-select__value"></span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                    <div class="push-custom-select__menu" role="listbox">
                        <div class="push-custom-select__option" data-value=""><i class="fa-solid fa-layer-group"></i><span>Tất cả hạng</span></div>
                        <div class="push-custom-select__option" data-value="member"><i class="fa-solid fa-user"></i><span>Member</span></div>
                        <div class="push-custom-select__option" data-value="silver"><i class="fa-solid fa-medal"></i><span>Silver</span></div>
                        <div class="push-custom-select__option" data-value="gold"><i class="fa-solid fa-medal"></i><span>Gold</span></div>
                        <div class="push-custom-select__option" data-value="platinum"><i class="fa-solid fa-crown"></i><span>Platinum</span></div>
                    </div>
                </div>
            </div>


            <div class="push-filter-actions">
                <button type="submit" class="push-filter-btn">
                    <i class="fa-solid fa-filter"></i>
                    Lọc
                </button>
                @if ($activeFilterCount > 0)
                <a href="{{ route('admin.thong-bao-push.index') }}" class="push-reset-btn">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
                @endif
            </div>
        </form>

        <div class="push-table-wrap">
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
                        <th>Thời gian</th>
                        <th>Trạng thái</th>
                        <th class="is-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($thongBaos as $index => $thongBao)
                    @php
                    $type = $typeMeta[$thongBao->loai] ?? ['label' => ucfirst($thongBao->loai), 'icon' => 'fa-bell', 'class' => 'is-system'];
                    $audience = $audienceMeta[$thongBao->doi_tuong_nhan] ?? ['label' => $thongBao->doi_tuong_nhan, 'icon' => 'fa-users', 'class' => 'is-all'];
                    $status = match ($thongBao->trang_thai) {

                    'chua_gui' => [
                    'label' => 'Nháp',
                    'icon' => 'fa-file-pen',
                    'class' => 'is-draft',
                    ],

                    'da_gui' => [
                    'label' => 'Đã gửi',
                    'icon' => 'fa-paper-plane',
                    'class' => 'is-sent',
                    ],

                    default => [
                    'label' => 'Chưa xác định',
                    'icon' => 'fa-circle-question',
                    'class' => 'is-unknown',
                    ],
                    };
                    $authorName = $thongBao->nguoiTao->ho_ten ?? 'Hệ thống';
                    @endphp
                    <tr>
                        <td>
                            {{ $thongBaos->firstItem() + $index }}
                        </td>
                        @if(request('doi_tuong_nhan') === 'nguoi_dung_cu_the')
                        <td>
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
                        <td data-label="Thông báo">
                            <a href="{{ route('admin.thong-bao-push.show', $thongBao) }}" class="push-message-cell">
                                <strong>{{ \Illuminate\Support\Str::limit($thongBao->tieu_de, 68) }}</strong>
                                <small>{{ \Illuminate\Support\Str::limit($thongBao->noi_dung, 96) }}</small>
                            </a>
                        </td>
                        <td data-label="Loại">
                            <span class="push-chip {{ $type['class'] }}">
                                <i class="fa-solid {{ $type['icon'] }}"></i>
                                {{ $type['label'] }}
                            </span>
                        </td>
                        <td data-label="Người nhận">
                            <span class="push-chip {{ $audience['class'] }}">
                                <i class="fa-solid {{ $audience['icon'] }}"></i>
                                {{ $audience['label'] }}
                            </span>
                        </td>
                        <td data-label="Người tạo">
                            <div class="push-author">
                                <span>{{ strtoupper(mb_substr($authorName, 0, 1)) }}</span>
                                <strong>{{ $authorName }}</strong>
                            </div>
                        </td>
                        <td data-label="Thời gian">
                            <span class="push-date">
                                <i class="fa-regular fa-calendar"></i>
                                {{ $thongBao->created_at->format('d/m/Y') }}
                                <small>{{ $thongBao->created_at->format('H:i') }}</small>
                            </span>
                        </td>
                        <td data-label="Trạng thái">
                            <span class="push-status {{ $status['class'] }}">
                                <i class="fa-solid {{ $status['icon'] }}"></i>
                                {{ $status['label'] }}
                            </span>
                        </td>
                        <td data-label="Thao tác" class="is-right">
                            <div class="push-action-buttons">
                                <a href="{{ route('admin.thong-bao-push.show', $thongBao) }}" class="push-icon-btn is-view" title="Xem chi tiết">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                @if ($thongBao->trang_thai === 'chua_gui')

                                {{-- Sửa --}}
                                <a
                                    href="{{ route('admin.thong-bao-push.edit', $thongBao) }}"
                                    class="push-icon-btn is-edit"
                                    title="Sửa thông báo">

                                    <i class="fa-solid fa-pen"></i>

                                </a>


                                @endif
                                <form action="{{ route('admin.thong-bao-push.destroy', $thongBao) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa thông báo này không?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="push-icon-btn is-delete" title="Xóa thông báo">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="push-empty">
                                <i class="fa-solid fa-bell-slash"></i>
                                <h3>Chưa có thông báo phù hợp</h3>
                                <p>Thử đổi bộ lọc hoặc tạo thông báo mới để gửi đến người dùng.</p>
                                <a href="{{ route('admin.thong-bao-push.create') }}" class="push-primary-btn">
                                    <i class="fa-solid fa-plus"></i>
                                    Tạo thông báo
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

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
                <strong>{{ $thongBaos->firstItem() ?? 0 }} - {{ $thongBaos->lastItem() ?? 0 }}</strong>
                trong
                <strong>{{ $thongBaos->total() }}</strong>
                thông báo
            </div>
            <div class="admin-pagination__controls">
                @if ($thongBaos->onFirstPage())
                <span class="admin-pagination__btn is-disabled"><i class="fa-solid fa-chevron-left"></i> Trước</span>
                @else
                <a href="{{ $thongBaos->previousPageUrl() }}" class="admin-pagination__btn"><i class="fa-solid fa-chevron-left"></i> Trước</a>
                @endif

                @if ($startPage > 1)
                <a href="{{ $thongBaos->url(1) }}" class="admin-pagination__page">1</a>
                @if ($startPage > 2)
                <span class="admin-pagination__dots">...</span>
                @endif
                @endif

                @foreach ($thongBaos->getUrlRange($startPage, $endPage) as $page => $url)
                @if ($page === $currentPage)
                <span class="admin-pagination__page is-active">{{ $page }}</span>
                @else
                <a href="{{ $url }}" class="admin-pagination__page">{{ $page }}</a>
                @endif
                @endforeach

                @if ($endPage < $lastPage)
                    @if ($endPage < $lastPage - 1)
                    <span class="admin-pagination__dots">...</span>
                    @endif
                    <a href="{{ $thongBaos->url($lastPage) }}" class="admin-pagination__page">{{ $lastPage }}</a>
                    @endif

                    @if ($thongBaos->hasMorePages())
                    <a href="{{ $thongBaos->nextPageUrl() }}" class="admin-pagination__btn">Sau <i class="fa-solid fa-chevron-right"></i></a>
                    @else
                    <span class="admin-pagination__btn is-disabled">Sau <i class="fa-solid fa-chevron-right"></i></span>
                    @endif
            </div>
        </div>
        @endif
    </section>
</div>

<style>
/* =========================================================
   CUSTOM DROPDOWNS - PUSH NOTIFICATION FILTER
   Menu được đưa ra body khi mở để không bị panel/table cắt.
========================================================= */
.push-panel,
.push-panel-head,
.push-filter,
.push-field,
.push-custom-select {
    overflow: visible !important;
}

.push-filter {
    position: relative;
    z-index: 20;
}

.push-field {
    position: relative;
    min-width: 0;
}

.push-custom-select {
    position: relative;
    width: 100%;
    z-index: 30;
}

.push-custom-select__source {
    position: absolute !important;
    width: 1px !important;
    height: 1px !important;
    padding: 0 !important;
    margin: -1px !important;
    overflow: hidden !important;
    clip: rect(0, 0, 0, 0) !important;
    white-space: nowrap !important;
    border: 0 !important;
}

.push-custom-select__trigger {
    width: 100%;
    min-height: 46px;
    padding: 0 14px 0 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    box-sizing: border-box;
    border: 1px solid #303642;
    border-radius: 14px;
    background: #171b23 !important;
    color: #f4f4f5 !important;
    font: inherit;
    font-size: 14px;
    font-weight: 600;
    line-height: 1.2;
    cursor: pointer;
    outline: none;
    appearance: none;
    -webkit-appearance: none;
    transition: border-color .16s ease, background .16s ease, box-shadow .16s ease;
}

.push-custom-select__trigger:hover,
.push-custom-select.is-open .push-custom-select__trigger {
    border-color: #ff3347 !important;
    background: #1b1f28 !important;
    box-shadow: 0 0 0 1px rgba(255,51,71,.08);
}

.push-custom-select__value {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.push-custom-select__trigger > i {
    flex: 0 0 auto;
    color: #9ca3af;
    font-size: 12px;
    transition: transform .16s ease;
}

.push-custom-select.is-open .push-custom-select__trigger > i {
    transform: rotate(180deg);
}

/* Menu khi đang ở trong dropdown (trước khi portal ra body) */
.push-custom-select__menu {
    display: none;
    position: absolute;
    left: 0;
    right: 0;
    top: calc(100% + 6px);
    z-index: 999999;
    background: #171b23 !important;
    border: 1px solid #303642 !important;
    border-radius: 14px !important;
    overflow: hidden !important;
    box-shadow: 0 14px 32px rgba(0,0,0,.48);
    box-sizing: border-box;
}

/* Menu đã được JS đưa ra body */
.push-custom-select__menu.is-portal {
    display: block !important;
    position: fixed !important;
    left: 0;
    right: auto;
    top: 0;
    z-index: 2147483647 !important;
    width: 0;
    margin: 0 !important;
}

.push-custom-select__option {
    min-height: 46px;
    width: 100%;
    padding: 0 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    box-sizing: border-box;
    background: #171b23 !important;
    color: #e5e7eb !important;
    border: 0;
    border-bottom: 1px solid #303642;
    font-size: 14px;
    font-weight: 600;
    line-height: 1.2;
    cursor: pointer;
    user-select: none;
    transition: background .14s ease, color .14s ease;
}

.push-custom-select__option:first-child {
    border-radius: 13px 13px 0 0;
}

.push-custom-select__option:last-child {
    border-bottom: 0;
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

.push-custom-select__option i {
    width: 18px;
    min-width: 18px;
    text-align: center;
    color: #9aa8bb;
}

.push-custom-select__option:hover i,
.push-custom-select__option.is-selected i {
    color: #ff5262;
}

/* Bảng luôn ở dưới menu */
.push-table-wrap {
    position: relative;
    z-index: 1;
}

/* Không tạo scrollbar phụ do custom dropdown */
.push-custom-select__menu.is-portal {
    max-height: none !important;
    overflow: hidden !important;
}

@media (max-width: 900px) {
    .push-custom-select__menu.is-portal {
        max-width: calc(100vw - 24px);
    }
}

/* =========================================================
   MÀU ICON RIÊNG CHO TỪNG LOẠI FILTER
========================================================= */

/* Không để rule chung làm mất màu */
.push-custom-select__option i {
    width: 18px;
    min-width: 18px;
    text-align: center;
    color: #9aa8bb;
}

/* ---------- LOẠI ---------- */
.push-custom-select[data-select="loai"]
.push-custom-select__option[data-value=""] i {
    color: #ff5262 !important;
}

.push-custom-select[data-select="loai"]
.push-custom-select__option[data-value="info"] i {
    color: #60a5fa !important;
}

.push-custom-select[data-select="loai"]
.push-custom-select__option[data-value="warning"] i {
    color: #fbbf24 !important;
}

.push-custom-select[data-select="loai"]
.push-custom-select__option[data-value="promo"] i {
    color: #c084fc !important;
}

.push-custom-select[data-select="loai"]
.push-custom-select__option[data-value="system"] i {
    color: #a78bfa !important;
}

/* ---------- TRẠNG THÁI ---------- */
.push-custom-select[data-select="trang_thai"]
.push-custom-select__option[data-value=""] i {
    color: #ff5262 !important;
}

.push-custom-select[data-select="trang_thai"]
.push-custom-select__option[data-value="chua_gui"] i {
    color: #f43f5e !important;
}

.push-custom-select[data-select="trang_thai"]
.push-custom-select__option[data-value="da_gui"] i {
    color: #22c55e !important;
}

/* ---------- NGƯỜI NHẬN ---------- */
.push-custom-select[data-select="doi_tuong_nhan"]
.push-custom-select__option[data-value=""] i {
    color: #ff5262 !important;
}

.push-custom-select[data-select="doi_tuong_nhan"]
.push-custom-select__option[data-value="all"] i {
    color: #60a5fa !important;
}

.push-custom-select[data-select="doi_tuong_nhan"]
.push-custom-select__option[data-value="hang_thanh_vien"] i {
    color: #c084fc !important;
}

.push-custom-select[data-select="doi_tuong_nhan"]
.push-custom-select__option[data-value="khach_hang"] i {
    color: #4ade80 !important;
}

.push-custom-select[data-select="doi_tuong_nhan"]
.push-custom-select__option[data-value="nguoi_dung_cu_the"] i {
    color: #c084fc !important;
}

.push-custom-select[data-select="doi_tuong_nhan"]
.push-custom-select__option[data-value="nhan_vien"] i {
    color: #f59e0b !important;
}

.push-custom-select[data-select="doi_tuong_nhan"]
.push-custom-select__option[data-value="quan_ly"] i {
    color: #60a5fa !important;
}

/* ---------- HẠNG THÀNH VIÊN ---------- */
.push-custom-select[data-select="hang_thanh_vien"]
.push-custom-select__option[data-value=""] i {
    color: #ff5262 !important;
}

.push-custom-select[data-select="hang_thanh_vien"]
.push-custom-select__option[data-value="member"] i {
    color: #9ca3af !important;
}

.push-custom-select[data-select="hang_thanh_vien"]
.push-custom-select__option[data-value="silver"] i {
    color: #cbd5e1 !important;
}

.push-custom-select[data-select="hang_thanh_vien"]
.push-custom-select__option[data-value="gold"] i {
    color: #fbbf24 !important;
}

.push-custom-select[data-select="hang_thanh_vien"]
.push-custom-select__option[data-value="platinum"] i {
    color: #a78bfa !important;
}

/* Các màu icon vẫn được giữ khi hover / selected */
.push-custom-select[data-select="loai"]
.push-custom-select__option[data-value="info"]:hover i,
.push-custom-select[data-select="loai"]
.push-custom-select__option[data-value="info"].is-selected i {
    color: #60a5fa !important;
}

.push-custom-select[data-select="loai"]
.push-custom-select__option[data-value="warning"]:hover i,
.push-custom-select[data-select="loai"]
.push-custom-select__option[data-value="warning"].is-selected i {
    color: #fbbf24 !important;
}

.push-custom-select[data-select="loai"]
.push-custom-select__option[data-value="promo"]:hover i,
.push-custom-select[data-select="loai"]
.push-custom-select__option[data-value="promo"].is-selected i {
    color: #c084fc !important;
}

.push-custom-select[data-select="loai"]
.push-custom-select__option[data-value="system"]:hover i,
.push-custom-select[data-select="loai"]
.push-custom-select__option[data-value="system"].is-selected i {
    color: #a78bfa !important;
}

.push-custom-select[data-select="trang_thai"]
.push-custom-select__option[data-value="chua_gui"]:hover i,
.push-custom-select[data-select="trang_thai"]
.push-custom-select__option[data-value="chua_gui"].is-selected i {
    color: #f43f5e !important;
}

.push-custom-select[data-select="trang_thai"]
.push-custom-select__option[data-value="da_gui"]:hover i,
.push-custom-select[data-select="trang_thai"]
.push-custom-select__option[data-value="da_gui"].is-selected i {
    color: #22c55e !important;
}

.push-custom-select[data-select="doi_tuong_nhan"]
.push-custom-select__option[data-value="all"]:hover i,
.push-custom-select[data-select="doi_tuong_nhan"]
.push-custom-select__option[data-value="all"].is-selected i {
    color: #60a5fa !important;
}

.push-custom-select[data-select="doi_tuong_nhan"]
.push-custom-select__option[data-value="hang_thanh_vien"]:hover i,
.push-custom-select[data-select="doi_tuong_nhan"]
.push-custom-select__option[data-value="hang_thanh_vien"].is-selected i {
    color: #c084fc !important;
}

.push-custom-select[data-select="doi_tuong_nhan"]
.push-custom-select__option[data-value="khach_hang"]:hover i,
.push-custom-select[data-select="doi_tuong_nhan"]
.push-custom-select__option[data-value="khach_hang"].is-selected i {
    color: #4ade80 !important;
}

.push-custom-select[data-select="doi_tuong_nhan"]
.push-custom-select__option[data-value="nguoi_dung_cu_the"]:hover i,
.push-custom-select[data-select="doi_tuong_nhan"]
.push-custom-select__option[data-value="nguoi_dung_cu_the"].is-selected i {
    color: #c084fc !important;
}

.push-custom-select[data-select="doi_tuong_nhan"]
.push-custom-select__option[data-value="nhan_vien"]:hover i,
.push-custom-select[data-select="doi_tuong_nhan"]
.push-custom-select__option[data-value="nhan_vien"].is-selected i {
    color: #f59e0b !important;
}

.push-custom-select[data-select="doi_tuong_nhan"]
.push-custom-select__option[data-value="quan_ly"]:hover i,
.push-custom-select[data-select="doi_tuong_nhan"]
.push-custom-select__option[data-value="quan_ly"].is-selected i {
    color: #60a5fa !important;
}

.push-custom-select[data-select="hang_thanh_vien"]
.push-custom-select__option[data-value="gold"]:hover i,
.push-custom-select[data-select="hang_thanh_vien"]
.push-custom-select__option[data-value="gold"].is-selected i {
    color: #fbbf24 !important;
}

.push-custom-select[data-select="hang_thanh_vien"]
.push-custom-select__option[data-value="platinum"]:hover i,
.push-custom-select[data-select="hang_thanh_vien"]
.push-custom-select__option[data-value="platinum"].is-selected i {
    color: #a78bfa !important;
}


/* =========================================================
   ICON MÀU KHI DROPDOWN ĐƯỢC PORTAL RA BODY
   (menu lúc này không còn nằm trong .push-custom-select)
========================================================= */
.push-custom-select__menu.is-portal .push-custom-select__option i {
    color: #60a5fa !important;
}

/* Tất cả */
.push-custom-select__menu.is-portal .push-custom-select__option[data-value=""] i {
    color: #ff5262 !important;
}

/* LOẠI */
.push-custom-select__menu.is-portal .push-custom-select__option[data-value="info"] i {
    color: #38bdf8 !important;
}
.push-custom-select__menu.is-portal .push-custom-select__option[data-value="warning"] i {
    color: #fbbf24 !important;
}
.push-custom-select__menu.is-portal .push-custom-select__option[data-value="promo"] i {
    color: #c084fc !important;
}
.push-custom-select__menu.is-portal .push-custom-select__option[data-value="system"] i {
    color: #a78bfa !important;
}

/* TRẠNG THÁI */
.push-custom-select__menu.is-portal .push-custom-select__option[data-value="chua_gui"] i {
    color: #fb7185 !important;
}
.push-custom-select__menu.is-portal .push-custom-select__option[data-value="da_gui"] i {
    color: #22c55e !important;
}

/* NGƯỜI NHẬN */
.push-custom-select__menu.is-portal .push-custom-select__option[data-value="all"] i {
    color: #38bdf8 !important;
}
.push-custom-select__menu.is-portal .push-custom-select__option[data-value="hang_thanh_vien"] i {
    color: #c084fc !important;
}
.push-custom-select__menu.is-portal .push-custom-select__option[data-value="khach_hang"] i {
    color: #4ade80 !important;
}
.push-custom-select__menu.is-portal .push-custom-select__option[data-value="nguoi_dung_cu_the"] i {
    color: #f472b6 !important;
}
.push-custom-select__menu.is-portal .push-custom-select__option[data-value="nhan_vien"] i {
    color: #fb923c !important;
}
.push-custom-select__menu.is-portal .push-custom-select__option[data-value="quan_ly"] i {
    color: #60a5fa !important;
}

/* HẠNG THÀNH VIÊN */
.push-custom-select__menu.is-portal .push-custom-select__option[data-value="member"] i {
    color: #60a5fa !important;
}
.push-custom-select__menu.is-portal .push-custom-select__option[data-value="silver"] i {
    color: #cbd5e1 !important;
}
.push-custom-select__menu.is-portal .push-custom-select__option[data-value="gold"] i {
    color: #facc15 !important;
}
.push-custom-select__menu.is-portal .push-custom-select__option[data-value="platinum"] i {
    color: #e879f9 !important;
}

/* Hover/selected không được làm mất màu icon */
.push-custom-select__menu.is-portal .push-custom-select__option:hover i,
.push-custom-select__menu.is-portal .push-custom-select__option.is-selected i {
    filter: brightness(1.12) saturate(1.12);
}

</style>

@endsection
<script>
document.addEventListener('DOMContentLoaded', function () {

    const dropdowns = Array.from(document.querySelectorAll('.push-custom-select'));
    let openedDropdown = null;

    function closeDropdown(dropdown) {
        if (!dropdown) return;

        const menu = dropdown._pushMenu;
        const trigger = dropdown.querySelector('.push-custom-select__trigger');

        dropdown.classList.remove('is-open', 'open-up');
        if (trigger) trigger.setAttribute('aria-expanded', 'false');

        if (menu) {
            menu.classList.remove('is-portal');
            menu.style.width = '';
            menu.style.left = '';
            menu.style.top = '';
            menu.style.bottom = '';

            if (menu.parentElement !== dropdown) {
                dropdown.appendChild(menu);
            }
        }

        if (openedDropdown === dropdown) {
            openedDropdown = null;
        }
    }

    function closeAll(except = null) {
        dropdowns.forEach(function (dropdown) {
            if (dropdown !== except) closeDropdown(dropdown);
        });
    }

    function syncDropdown(dropdown) {
        const select = dropdown.querySelector('.push-custom-select__source');
        const value = dropdown.querySelector('.push-custom-select__value');
        const options = dropdown.querySelectorAll('.push-custom-select__option');

        if (!select || !value) return;

        const selected = select.options[select.selectedIndex];
        value.textContent = selected ? selected.textContent.trim() : '';

        options.forEach(function (option) {
            option.classList.toggle('is-selected', option.dataset.value === select.value);
        });
    }

    function positionMenu(dropdown) {
        const trigger = dropdown.querySelector('.push-custom-select__trigger');
        const menu = dropdown._pushMenu;

        if (!trigger || !menu) return;

        const rect = trigger.getBoundingClientRect();
        const viewportPadding = 10;
        const gap = 6;
        const menuHeight = menu.offsetHeight;
        const menuWidth = rect.width;

        const spaceBelow = window.innerHeight - rect.bottom - viewportPadding;
        const spaceAbove = rect.top - viewportPadding;

        let top;

        /* Ưu tiên mở xuống nếu đủ chỗ. Nếu không đủ mà phía trên đủ thì mở lên. */
        if (spaceBelow >= menuHeight + gap || spaceBelow >= spaceAbove) {
            dropdown.classList.remove('open-up');
            top = rect.bottom + gap;
        } else {
            dropdown.classList.add('open-up');
            top = rect.top - menuHeight - gap;
        }

        /* Không cho menu vượt quá mép màn hình. */
        if (top < viewportPadding) {
            top = viewportPadding;
        }

        if (top + menuHeight > window.innerHeight - viewportPadding) {
            top = Math.max(viewportPadding, window.innerHeight - menuHeight - viewportPadding);
        }

        let left = rect.left;

        if (left + menuWidth > window.innerWidth - viewportPadding) {
            left = window.innerWidth - menuWidth - viewportPadding;
        }

        if (left < viewportPadding) {
            left = viewportPadding;
        }

        menu.style.width = menuWidth + 'px';
        menu.style.left = left + 'px';
        menu.style.top = top + 'px';
    }

    function openDropdown(dropdown) {
        closeAll(dropdown);

        const trigger = dropdown.querySelector('.push-custom-select__trigger');
        const menu = dropdown.querySelector('.push-custom-select__menu');

        if (!trigger || !menu) return;

        dropdown._pushMenu = menu;

        dropdown.classList.add('is-open');
        trigger.setAttribute('aria-expanded', 'true');

        /* Đưa menu ra khỏi mọi container có overflow/transform. */
        document.body.appendChild(menu);
        menu.classList.add('is-portal');

        openedDropdown = dropdown;

        /* Cần đo sau khi menu đã ở body. */
        requestAnimationFrame(function () {
            positionMenu(dropdown);
        });
    }

    dropdowns.forEach(function (dropdown) {
        const trigger = dropdown.querySelector('.push-custom-select__trigger');
        const select = dropdown.querySelector('.push-custom-select__source');
        const menu = dropdown.querySelector('.push-custom-select__menu');

        if (!trigger || !select || !menu) return;

        dropdown._pushMenu = menu;
        syncDropdown(dropdown);

        trigger.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            if (openedDropdown === dropdown) {
                closeDropdown(dropdown);
            } else {
                openDropdown(dropdown);
            }
        });

        menu.querySelectorAll('.push-custom-select__option').forEach(function (option) {
            option.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                select.value = option.dataset.value ?? '';
                select.dispatchEvent(new Event('change', { bubbles: true }));

                syncDropdown(dropdown);
                closeDropdown(dropdown);
            });
        });

        select.addEventListener('change', function () {
            syncDropdown(dropdown);
        });
    });

    document.addEventListener('click', function (event) {
        if (!event.target.closest('.push-custom-select') &&
            !event.target.closest('.push-custom-select__menu')) {
            closeAll();
        }
    });

    window.addEventListener('resize', function () {
        if (openedDropdown) positionMenu(openedDropdown);
    });

    window.addEventListener('scroll', function () {
        if (openedDropdown) positionMenu(openedDropdown);
    }, true);

    /* =====================================================
       NGƯỜI NHẬN -> HIỆN HẠNG THÀNH VIÊN / NGƯỜI DÙNG CỤ THỂ
    ===================================================== */

    const audienceSelect = document.getElementById('doi_tuong_nhan');
    const memberRankFilter = document.getElementById('hang-thanh-vien-filter');
    const userFilter = document.getElementById('nguoi-dung-filter');

    function toggleFilters() {
        if (!audienceSelect) return;

        if (memberRankFilter) {
            memberRankFilter.style.display =
                audienceSelect.value === 'hang_thanh_vien' ? '' : 'none';
        }

        if (userFilter) {
            userFilter.style.display =
                audienceSelect.value === 'nguoi_dung_cu_the' ? '' : 'none';
        }
    }

    if (audienceSelect) {
        audienceSelect.addEventListener('change', toggleFilters);
        toggleFilters();
    }
});
</script>