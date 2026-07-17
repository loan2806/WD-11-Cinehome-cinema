@extends('layouts.admin')

@section('page-title', 'Quản lý Thông báo đẩy')

@section('content')
@php
    $typeMeta = [
        'info' => ['label' => 'Thông tin', 'icon' => 'fa-circle-info', 'class' => 'is-info'],
        'success' => ['label' => 'Thành công', 'icon' => 'fa-circle-check', 'class' => 'is-success'],
        'warning' => ['label' => 'Cảnh báo', 'icon' => 'fa-triangle-exclamation', 'class' => 'is-warning'],
        'promo' => ['label' => 'Khuyến mãi', 'icon' => 'fa-gift', 'class' => 'is-promo'],
        'system' => ['label' => 'Hệ thống', 'icon' => 'fa-gear', 'class' => 'is-system'],
    ];

    $audienceMeta = [
        'all' => ['label' => 'Tất cả', 'icon' => 'fa-globe', 'class' => 'is-all'],
        'user' => ['label' => 'Khách hàng', 'icon' => 'fa-user', 'class' => 'is-user'],
        'vip' => ['label' => 'VIP', 'icon' => 'fa-crown', 'class' => 'is-vip'],
        'staff' => ['label' => 'Nhân viên', 'icon' => 'fa-user-tie', 'class' => 'is-staff'],
        'admin' => ['label' => 'Quản trị', 'icon' => 'fa-user-shield', 'class' => 'is-admin'],
        'nguoi_dung_cu_the' => ['label' => 'Cụ thể', 'icon' => 'fa-user-pen', 'class' => 'is-specific'],
    ];

    $statusMeta = [
        'da_gui' => ['label' => 'Đã gửi', 'icon' => 'fa-paper-plane', 'class' => 'is-sent'],
        'chua_gui' => ['label' => 'Chưa gửi', 'icon' => 'fa-clock', 'class' => 'is-pending'],
    ];

    $activeFilterCount = collect([
        request('search'),
        request('loai'),
        request('doi_tuong_nhan'),
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

        <a href="{{ route('admin.thong-bao-push.create') }}" class="push-primary-btn">
            <i class="fa-solid fa-plus"></i>
            Tạo thông báo mới
        </a>
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

            <label class="push-field">
                <span>Loại</span>
                <select name="loai">
                    <option value="">Tất cả loại</option>
                    @foreach ($typeMeta as $value => $meta)
                        <option value="{{ $value }}" @selected(request('loai') === $value)>{{ $meta['label'] }}</option>
                    @endforeach
                </select>
            </label>

            <label class="push-field">
                <span>Người nhận</span>
                <select name="doi_tuong_nhan">
                    <option value="">Tất cả nhóm</option>
                    @foreach ($audienceMeta as $value => $meta)
                        <option value="{{ $value }}" @selected(request('doi_tuong_nhan') === $value)>{{ $meta['label'] }}</option>
                    @endforeach
                </select>
            </label>

            <label class="push-field">
                <span>Trạng thái</span>
                <select name="trang_thai">
                    <option value="">Tất cả trạng thái</option>
                    @foreach ($statusMeta as $value => $meta)
                        <option value="{{ $value }}" @selected(request('trang_thai') === $value)>{{ $meta['label'] }}</option>
                    @endforeach
                </select>
            </label>

            <div class="push-filter-actions">
                <button type="submit" class="push-filter-btn">
                    <i class="fa-solid fa-filter"></i>
                    Lọc
                    @if ($activeFilterCount > 0)
                        <span>{{ $activeFilterCount }}</span>
                    @endif
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
                    @forelse ($thongBaos as $thongBao)
                        @php
                            $type = $typeMeta[$thongBao->loai] ?? ['label' => ucfirst($thongBao->loai), 'icon' => 'fa-bell', 'class' => 'is-system'];
                            $audience = $audienceMeta[$thongBao->doi_tuong_nhan] ?? ['label' => $thongBao->doi_tuong_nhan, 'icon' => 'fa-users', 'class' => 'is-all'];
                            $status = $statusMeta[$thongBao->trang_thai] ?? ['label' => $thongBao->trang_thai, 'icon' => 'fa-clock', 'class' => 'is-pending'];
                            $authorName = $thongBao->nguoiTao->ho_ten ?? 'Hệ thống';
                        @endphp
                        <tr>
                            <td data-label="Thông báo">
                                <a href="{{ route('admin.thong-bao-push.show', $thongBao) }}" class="push-message-cell">
                                    <span>#{{ $thongBao->id }}</span>
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
@endsection
