@extends('layouts.admin')

@section('page-title', 'Quản lý Thông báo đẩy')

@section('content')
@php
    $typeMeta = [
        'info'    => ['label' => 'Thông tin', 'icon' => 'fa-circle-info', 'class' => 'is-info'],
        'warning' => ['label' => 'Cảnh báo', 'icon' => 'fa-triangle-exclamation', 'class' => 'is-warning'],
        'promo'   => ['label' => 'Khuyến mãi', 'icon' => 'fa-gift', 'class' => 'is-promo'],
        'system'  => ['label' => 'Hệ thống', 'icon' => 'fa-gear', 'class' => 'is-system'],
    ];

    $audienceMeta = [
        'all'               => ['label' => 'Tất cả người dùng', 'icon' => 'fa-users', 'class' => 'is-all'],
        'hang_thanh_vien'  => ['label' => 'Hạng thành viên', 'icon' => 'fa-ranking-star', 'class' => 'is-vip'],
        'khach_hang'        => ['label' => 'Khách hàng', 'icon' => 'fa-user', 'class' => 'is-user'],
        'nguoi_dung_cu_the' => ['label' => 'Người dùng cụ thể', 'icon' => 'fa-user-pen', 'class' => 'is-specific'],
        'nhan_vien'         => ['label' => 'Nhân viên', 'icon' => 'fa-user-tie', 'class' => 'is-staff'],
        'quan_ly'           => ['label' => 'Quản lý', 'icon' => 'fa-user-shield', 'class' => 'is-admin'],
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
                <i class="fa-solid fa-satellite-dish"></i> Trung tâm tương tác
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
            <a href="{{ route('admin.thong-bao-push.trash') }}" class="staff-list-secondary-btn">
                <i class="fa-solid fa-trash"></i> Thùng rác
            </a>
            <a href="{{ route('admin.thong-bao-push.create') }}" class="push-primary-btn">
                <i class="fa-solid fa-plus"></i> Tạo thông báo mới
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

            {{-- Filter: Loại --}}
            <div class="push-field">
                <span>Loại</span>
                <div class="push-custom-select" data-select="loai">
                    <select name="loai" class="push-custom-select__source">
                        <option value="">Tất cả loại</option>
                        @foreach ($typeMeta as $value => $meta)
                            <option value="{{ $value }}" @selected(request('loai') === $value)>{{ $meta['label'] }}</option>
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

            {{-- Filter: Trạng thái --}}
            <div class="push-field">
                <span>Trạng thái</span>
                <div class="push-custom-select" data-select="trang_thai">
                    <select name="trang_thai" class="push-custom-select__source">
                        <option value="">Tất cả trạng thái</option>
                        <option value="chua_gui" @selected(request('trang_thai') === 'chua_gui')>Nháp</option>
                        <option value="da_gui" @selected(request('trang_thai') === 'da_gui')>Đã gửi</option>
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

            {{-- Filter: Người nhận --}}
            <div class="push-field">
                <span>Người nhận</span>
                <div class="push-custom-select" data-select="doi_tuong_nhan">
                    <select name="doi_tuong_nhan" id="doi_tuong_nhan" class="push-custom-select__source">
                        <option value="">Tất cả nhóm</option>
                        @foreach ($audienceMeta as $value => $meta)
                            <option value="{{ $value }}" @selected(request('doi_tuong_nhan') === $value)>{{ $meta['label'] }}</option>
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

            {{-- Sub Filter: Người dùng cụ thể --}}
            <div class="push-field" id="nguoi-dung-filter" style="{{ request('doi_tuong_nhan') === 'nguoi_dung_cu_the' ? '' : 'display:none;' }}">
                <span>Người dùng</span>
                <div>
                    <input type="text" name="nguoi_dung" value="{{ request('nguoi_dung') }}" placeholder="Nhập tên hoặc email...">
                </div>
            </div>

            {{-- Sub Filter: Hạng thành viên --}}
            <div class="push-field" id="hang-thanh-vien-filter" style="{{ request('doi_tuong_nhan') === 'hang_thanh_vien' ? '' : 'display:none;' }}">
                <span>Hạng thành viên</span>
                <div class="push-custom-select" data-select="hang_thanh_vien">
                    <select name="hang_thanh_vien" class="push-custom-select__source">
                        <option value="">Tất cả hạng</option>
                        <option value="member" @selected(request('hang_thanh_vien') === 'member')>Member</option>
                        <option value="silver" @selected(request('hang_thanh_vien') === 'silver')>Silver</option>
                        <option value="gold" @selected(request('hang_thanh_vien') === 'gold')>Gold</option>
                        <option value="platinum" @selected(request('hang_thanh_vien') === 'platinum')>Platinum</option>
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
                    <i class="fa-solid fa-filter"></i> Lọc
                </button>
                @if ($activeFilterCount > 0)
                    <a href="{{ route('admin.thong-bao-push.index') }}" class="push-reset-btn" title="Xóa bộ lọc">
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
                                'chua_gui' => ['label' => 'Nháp', 'icon' => 'fa-file-pen', 'class' => 'is-draft'],
                                'da_gui'   => ['label' => 'Đã gửi', 'icon' => 'fa-paper-plane', 'class' => 'is-sent'],
                                default    => ['label' => 'Chưa xác định', 'icon' => 'fa-circle-question', 'class' => 'is-unknown'],
                            };
                            $authorName = $thongBao->nguoiTao->ho_ten ?? 'Hệ thống';
                        @endphp
                        <tr>
                            <td>{{ $thongBaos->firstItem() + $index }}</td>

                            @if(request('doi_tuong_nhan') === 'nguoi_dung_cu_the')
                                <td>
                                    @forelse($thongBao->nguoiDungs ?? [] as $user)
                                        <div class="user-cell">
                                            <div class="user-name">{{ $user->ho_ten }}</div>
                                            <div class="user-email">{{ $user->email }}</div>
                                        </div>
                                    @empty
                                        <span class="text-muted">-</span>
                                    @endforelse
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
                                    <i class="fa-solid {{ $type['icon'] }}"></i> {{ $type['label'] }}
                                </span>
                            </td>
                            <td data-label="Người nhận">
                                <span class="push-chip {{ $audience['class'] }}">
                                    <i class="fa-solid {{ $audience['icon'] }}"></i> {{ $audience['label'] }}
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
                                    <i class="fa-solid {{ $status['icon'] }}"></i> {{ $status['label'] }}
                                </span>
                            </td>
                            <td data-label="Thao tác" class="is-right">
                                <div class="push-action-buttons">
                                    <a href="{{ route('admin.thong-bao-push.show', $thongBao) }}" class="push-icon-btn is-view" title="Xem chi tiết">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    @if ($thongBao->trang_thai === 'chua_gui')
                                        <a href="{{ route('admin.thong-bao-push.edit', $thongBao) }}" class="push-icon-btn is-edit" title="Sửa thông báo">
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
                            <td colspan="{{ request('doi_tuong_nhan') === 'nguoi_dung_cu_the' ? '9' : '8' }}">
                                <div class="push-empty">
                                    <i class="fa-solid fa-bell-slash"></i>
                                    <h3>Chưa có thông báo phù hợp</h3>
                                    <p>Thử đổi bộ lọc hoặc tạo thông báo mới để gửi đến người dùng.</p>
                                    <a href="{{ route('admin.thong-bao-push.create') }}" class="push-primary-btn">
                                        <i class="fa-solid fa-plus"></i> Tạo thông báo
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($thongBaos->hasPages())
            <div class="admin-pagination push-pagination">
                <div class="admin-pagination__meta">
                    Hiển thị <strong>{{ $thongBaos->firstItem() ?? 0 }} - {{ $thongBaos->lastItem() ?? 0 }}</strong> trong <strong>{{ $thongBaos->total() }}</strong> thông báo
                </div>
                <div class="admin-pagination__controls">
                    {{ $thongBaos->withQueryString()->links() }}
                </div>
            </div>
        @endif
    </section>
</div>

<style>
.push-panel, .push-panel-head, .push-filter, .push-field, .push-custom-select {
    overflow: visible !important;
}
.push-filter { position: relative; z-index: 20; }
.push-field { position: relative; min-width: 0; }
.push-custom-select { position: relative; width: 100%; z-index: 30; }

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

.push-custom-select__menu {
    display: none;
    position: absolute;
    left: 0;
    right: 0;
    top: calc(100% + 6px);
    z-index: 1000;
    background: #171b23 !important;
    border: 1px solid #303642 !important;
    border-radius: 14px !important;
    overflow: hidden !important;
    box-shadow: 0 14px 32px rgba(0,0,0,.48);
    box-sizing: border-box;
}

.push-custom-select__menu.is-portal {
    display: block !important;
    position: fixed !important;
    left: 0;
    top: 0;
    z-index: 8000 !important;
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
    cursor: pointer;
    user-select: none;
    transition: background .14s ease, color .14s ease;
}

.push-custom-select__option:last-child {
    border-bottom: 0;
}

.push-custom-select__option:hover,
.push-custom-select__option.is-selected {
    background: #252b36 !important;
    color: #fff !important;
}

.push-custom-select__option i {
    width: 18px;
    min-width: 18px;
    text-align: center;
    color: #9aa8bb;
}

.push-table-wrap { position: relative; z-index: 1; }

/* Dynamic Icon Colors */
.push-custom-select__menu.is-portal .push-custom-select__option[data-value=""] i { color: #ff5262 !important; }
.push-custom-select__menu.is-portal .push-custom-select__option[data-value="info"] i { color: #38bdf8 !important; }
.push-custom-select__menu.is-portal .push-custom-select__option[data-value="warning"] i { color: #fbbf24 !important; }
.push-custom-select__menu.is-portal .push-custom-select__option[data-value="promo"] i { color: #c084fc !important; }
.push-custom-select__menu.is-portal .push-custom-select__option[data-value="system"] i { color: #a78bfa !important; }
.push-custom-select__menu.is-portal .push-custom-select__option[data-value="chua_gui"] i { color: #fb7185 !important; }
.push-custom-select__menu.is-portal .push-custom-select__option[data-value="da_gui"] i { color: #22c55e !important; }
.push-custom-select__menu.is-portal .push-custom-select__option[data-value="all"] i { color: #38bdf8 !important; }
.push-custom-select__menu.is-portal .push-custom-select__option[data-value="hang_thanh_vien"] i { color: #c084fc !important; }
.push-custom-select__menu.is-portal .push-custom-select__option[data-value="khach_hang"] i { color: #4ade80 !important; }
.push-custom-select__menu.is-portal .push-custom-select__option[data-value="nguoi_dung_cu_the"] i { color: #f472b6 !important; }
.push-custom-select__menu.is-portal .push-custom-select__option[data-value="nhan_vien"] i { color: #fb923c !important; }
.push-custom-select__menu.is-portal .push-custom-select__option[data-value="quan_ly"] i { color: #60a5fa !important; }
.push-custom-select__menu.is-portal .push-custom-select__option[data-value="member"] i { color: #60a5fa !important; }
.push-custom-select__menu.is-portal .push-custom-select__option[data-value="silver"] i { color: #cbd5e1 !important; }
.push-custom-select__menu.is-portal .push-custom-select__option[data-value="gold"] i { color: #facc15 !important; }
.push-custom-select__menu.is-portal .push-custom-select__option[data-value="platinum"] i { color: #e879f9 !important; }
</style>

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
            menu.style.cssText = '';
            if (menu.parentElement !== dropdown) {
                dropdown.appendChild(menu);
            }
        }
        if (openedDropdown === dropdown) openedDropdown = null;
    }

    function closeAll(except = null) {
        dropdowns.forEach(dropdown => {
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

        options.forEach(option => {
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

        let top = (rect.bottom + menuHeight + gap > window.innerHeight) 
            ? rect.top - menuHeight - gap 
            : rect.bottom + gap;

        top = Math.max(viewportPadding, Math.min(top, window.innerHeight - menuHeight - viewportPadding));
        let left = Math.max(viewportPadding, Math.min(rect.left, window.innerWidth - rect.width - viewportPadding));

        menu.style.width = rect.width + 'px';
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

        document.body.appendChild(menu);
        menu.classList.add('is-portal');
        openedDropdown = dropdown;

        requestAnimationFrame(() => positionMenu(dropdown));
    }

    dropdowns.forEach(dropdown => {
        const trigger = dropdown.querySelector('.push-custom-select__trigger');
        const select = dropdown.querySelector('.push-custom-select__source');
        const menu = dropdown.querySelector('.push-custom-select__menu');
        if (!trigger || !select || !menu) return;

        dropdown._pushMenu = menu;
        syncDropdown(dropdown);

        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            openedDropdown === dropdown ? closeDropdown(dropdown) : openDropdown(dropdown);
        });

        menu.querySelectorAll('.push-custom-select__option').forEach(option => {
            option.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                select.value = option.dataset.value ?? '';
                select.dispatchEvent(new Event('change', { bubbles: true }));
                syncDropdown(dropdown);
                closeDropdown(dropdown);
            });
        });

        select.addEventListener('change', () => syncDropdown(dropdown));
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.push-custom-select') && !e.target.closest('.push-custom-select__menu')) {
            closeAll();
        }
    });

    window.addEventListener('resize', () => openedDropdown && positionMenu(openedDropdown));
    window.addEventListener('scroll', () => openedDropdown && positionMenu(openedDropdown), true);

    // Toggle sub filters
    const audienceSelect = document.getElementById('doi_tuong_nhan');
    const memberRankFilter = document.getElementById('hang-thanh-vien-filter');
    const userFilter = document.getElementById('nguoi-dung-filter');

    function toggleFilters() {
        if (!audienceSelect) return;
        if (memberRankFilter) memberRankFilter.style.display = audienceSelect.value === 'hang_thanh_vien' ? '' : 'none';
        if (userFilter) userFilter.style.display = audienceSelect.value === 'nguoi_dung_cu_the' ? '' : 'none';
    }

    if (audienceSelect) {
        audienceSelect.addEventListener('change', toggleFilters);
        toggleFilters();
    }
});
</script>
@endsection