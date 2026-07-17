@extends('layouts.admin')

@section('page-title', 'Nhật ký hệ thống')
@section('page-subtitle', 'Theo dõi thao tác người dùng và quản trị trên hệ thống CineHome')

@section('content')
@php
    $hasFilters = request()->hasAny(['keyword', 'chuc_nang', 'from', 'to']);
    $statCards = [
        ['label' => 'Bản ghi phù hợp', 'value' => $summary['filtered'], 'icon' => 'fa-clipboard-list', 'tone' => 'is-total'],
        ['label' => 'Hôm nay', 'value' => $summary['today'], 'icon' => 'fa-calendar-day', 'tone' => 'is-today'],
        ['label' => 'Chức năng', 'value' => $summary['modules'], 'icon' => 'fa-layer-group', 'tone' => 'is-module'],
        ['label' => 'Người thao tác', 'value' => $summary['actors'], 'icon' => 'fa-users-gear', 'tone' => 'is-actor'],
    ];
@endphp

<div class="activity-log-page">
    <section class="activity-log-hero">
        <div>
            <span class="activity-log-kicker">
                <i class="fa-solid fa-shield-halved"></i>
                Audit Trail
            </span>
            <h2>Nhật ký hoạt động hệ thống</h2>
            <p>Kiểm tra nhanh ai đã thao tác, thao tác ở module nào, thời điểm nào và từ địa chỉ IP nào.</p>

            <div class="activity-log-meta">
                <span><i class="fa-solid fa-database"></i> {{ number_format($logs->total()) }} bản ghi đang lọc</span>
                <span><i class="fa-regular fa-clock"></i> Sắp xếp mới nhất trước</span>
                <span><i class="fa-solid fa-filter"></i> {{ $hasFilters ? 'Đang áp dụng bộ lọc' : 'Chưa lọc dữ liệu' }}</span>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="activity-log-filter">
            <label class="activity-log-search">
                <span>Tìm kiếm</span>
                <div>
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input name="keyword" value="{{ request('keyword') }}" placeholder="Hành động, mô tả, email, IP...">
                </div>
            </label>

            <label>
                <span>Chức năng</span>
                <select name="chuc_nang">
                    <option value="">Tất cả chức năng</option>
                    @foreach($modules as $module)
                        <option value="{{ $module }}" @selected(request('chuc_nang') === $module)>{{ $module }}</option>
                    @endforeach
                </select>
            </label>

            <label>
                <span>Từ ngày</span>
                <input type="date" name="from" value="{{ request('from') }}">
            </label>

            <label>
                <span>Đến ngày</span>
                <input type="date" name="to" value="{{ request('to') }}">
            </label>

            <div class="activity-log-filter-actions">
                <button type="submit">
                    <i class="fa-solid fa-filter"></i>
                    Lọc nhật ký
                </button>
                @if($hasFilters)
                    <a href="{{ route('admin.activity-logs.index') }}" title="Xóa bộ lọc">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </section>

    <section class="activity-log-stats">
        @foreach($statCards as $card)
            <article class="activity-log-stat {{ $card['tone'] }}">
                <span><i class="fa-solid {{ $card['icon'] }}"></i></span>
                <div>
                    <small>{{ $card['label'] }}</small>
                    <strong>{{ number_format($card['value']) }}</strong>
                </div>
            </article>
        @endforeach
    </section>

    <section class="activity-log-panel">
        <div class="activity-log-panel-head">
            <div>
                <span class="activity-log-kicker">Danh sách</span>
                <h3>Dòng thời gian thao tác</h3>
                <p>Mỗi bản ghi gồm người thao tác, module, hành động, mô tả và IP để hỗ trợ rà soát nhanh.</p>
            </div>
            <span class="activity-log-count">
                <i class="fa-solid fa-list-check"></i>
                {{ number_format($logs->total()) }} bản ghi
            </span>
        </div>

        <div class="activity-log-table-wrap">
            <table class="activity-log-table">
                <thead>
                    <tr>
                        <th>Thời gian</th>
                        <th>Người thao tác</th>
                        <th>Chức năng</th>
                        <th>Hành động & mô tả</th>
                        <th>Địa chỉ IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        @php
                            $actorName = $log->nguoiDung?->ho_ten ?: 'Hệ thống';
                            $actorEmail = $log->nguoiDung?->email ?: 'Tác vụ tự động';
                            $initial = mb_strtoupper(mb_substr($actorName, 0, 1));
                            $actionText = mb_strtolower($log->hanh_dong ?? '');
                            $tone = 'is-neutral';

                            if (str_contains($actionText, 'xóa') || str_contains($actionText, 'hủy') || str_contains($actionText, 'khóa')) {
                                $tone = 'is-danger';
                            } elseif (str_contains($actionText, 'thêm') || str_contains($actionText, 'tạo')) {
                                $tone = 'is-create';
                            } elseif (str_contains($actionText, 'cập nhật') || str_contains($actionText, 'sửa')) {
                                $tone = 'is-update';
                            }
                        @endphp
                        <tr>
                            <td data-label="Thời gian">
                                <span class="activity-log-time">{{ $log->created_at->format('d/m/Y H:i:s') }}</span>
                                <small>{{ $log->created_at->diffForHumans() }}</small>
                            </td>

                            <td data-label="Người thao tác">
                                <div class="activity-log-actor">
                                    <span>{{ $initial }}</span>
                                    <div>
                                        <strong>{{ $actorName }}</strong>
                                        <small>{{ $actorEmail }}</small>
                                    </div>
                                </div>
                            </td>

                            <td data-label="Chức năng">
                                <span class="activity-log-module">
                                    <i class="fa-solid fa-cube"></i>
                                    {{ $log->chuc_nang ?: 'Không xác định' }}
                                </span>
                            </td>

                            <td data-label="Hành động">
                                <div class="activity-log-action">
                                    <span class="{{ $tone }}">{{ $log->hanh_dong ?: 'Không rõ hành động' }}</span>
                                    <p>{{ $log->mo_ta ?: 'Không có mô tả chi tiết.' }}</p>
                                </div>
                            </td>

                            <td data-label="Địa chỉ IP">
                                <span class="activity-log-ip">
                                    <i class="fa-solid fa-network-wired"></i>
                                    {{ $log->dia_chi_ip ?: '-' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="activity-log-empty">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                    <h3>Chưa có nhật ký phù hợp</h3>
                                    <p>Thử thay đổi từ khóa, chức năng hoặc khoảng ngày để xem thêm bản ghi.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="activity-log-pagination">
            <span>
                Hiển thị {{ $logs->firstItem() ?? 0 }} - {{ $logs->lastItem() ?? 0 }}
                trên {{ number_format($logs->total()) }} bản ghi
            </span>
            {{ $logs->links() }}
        </div>
    </section>
</div>
@endsection
