@extends('layouts.admin')

@section('title', 'Tài khoản khách hàng')
@section('page-title', 'Tài khoản khách hàng')
@section('page-subtitle', 'Quản lý hồ sơ, trạng thái và hoạt động tài khoản khách hàng')

@section('content')
@php
    $summary = [
        'total' => $tongKhachHang ?? $khachHangs->total(),
        'active' => $tongDangHoatDong ?? 0,
        'locked' => $tongBiKhoa ?? 0,
        'new_this_month' => $tongMoiTrongThang ?? 0,
        'members' => $tongCoTheThanhVien ?? 0,
    ];

    $activeFilters = collect([
        request('tim_kiem'),
        request('trang_thai'),
    ])->filter(fn ($value) => filled($value))->count();
@endphp

<div class="customer-account-page">
    @include('admin.partials.flash')

    <section class="customer-account-hero">
        <div>
            <span class="customer-account-kicker">
                <i class="fa-solid fa-users"></i>
                Hồ sơ khách hàng
            </span>
            <h2>Quản lý tài khoản khách hàng</h2>
            <p>Tìm nhanh khách theo tên, email hoặc số điện thoại; kiểm tra hạng thành viên, số vé đã mua và trạng thái hoạt động của tài khoản.</p>
            <div class="customer-account-hero-meta">
                <span><i class="fa-solid fa-user-check"></i> {{ number_format($summary['active']) }} đang hoạt động</span>
                <span><i class="fa-solid fa-crown"></i> {{ number_format($summary['members']) }} có thẻ thành viên</span>
                <span><i class="fa-solid fa-calendar-plus"></i> {{ number_format($summary['new_this_month']) }} mới trong tháng</span>
            </div>
        </div>

        <a href="{{ route('admin.khach-hang.create') }}" class="customer-account-primary-btn">
            <i class="fa-solid fa-user-plus"></i>
            Thêm khách hàng
        </a>
    </section>

    <section class="customer-account-stats">
        <article class="customer-account-stat">
            <span class="is-total"><i class="fa-solid fa-users"></i></span>
            <div>
                <small>Tổng khách hàng</small>
                <strong>{{ number_format($summary['total']) }}</strong>
            </div>
        </article>
        <article class="customer-account-stat">
            <span class="is-active"><i class="fa-solid fa-user-check"></i></span>
            <div>
                <small>Đang hoạt động</small>
                <strong>{{ number_format($summary['active']) }}</strong>
            </div>
        </article>
        <article class="customer-account-stat">
            <span class="is-locked"><i class="fa-solid fa-user-lock"></i></span>
            <div>
                <small>Bị khóa</small>
                <strong>{{ number_format($summary['locked']) }}</strong>
            </div>
        </article>
        <article class="customer-account-stat">
            <span class="is-member"><i class="fa-solid fa-crown"></i></span>
            <div>
                <small>Có thẻ thành viên</small>
                <strong>{{ number_format($summary['members']) }}</strong>
            </div>
        </article>
    </section>

    <section class="customer-account-panel">
        <div class="customer-account-panel-head">
            <div>
                <span class="customer-account-kicker">Danh sách</span>
                <h3>Tài khoản khách hàng</h3>
                <p>Đang hiển thị {{ $khachHangs->count() }} / {{ $khachHangs->total() }} khách hàng theo bộ lọc hiện tại.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.khach-hang.index') }}" class="customer-account-filter">
            <label class="customer-account-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input
                    type="text"
                    name="tim_kiem"
                    value="{{ request('tim_kiem') }}"
                    placeholder="Tìm tên, email hoặc số điện thoại..."
                >
            </label>

            <select name="trang_thai" class="admin-input">
                <option value="">Tất cả trạng thái</option>
                <option value="1" @selected(request('trang_thai') === '1')>Đang hoạt động</option>
                <option value="0" @selected(request('trang_thai') === '0')>Bị khóa</option>
            </select>

            <button type="submit" class="customer-account-filter-btn">
                <i class="fa-solid fa-filter"></i>
                Lọc
                @if($activeFilters)
                    <span>{{ $activeFilters }}</span>
                @endif
            </button>

            @if($activeFilters)
                <a href="{{ route('admin.khach-hang.index') }}" class="customer-account-reset-btn" title="Xóa bộ lọc">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            @endif
        </form>

        <div class="customer-account-table-wrap">
            <table class="customer-account-table">
                <thead>
                    <tr>
                        <th>Khách hàng</th>
                        <th>Liên hệ</th>
                        <th>Thành viên</th>
                        <th>Vé đã mua</th>
                        <th>Ngày sinh</th>
                        <th>Trạng thái</th>
                        <th class="is-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($khachHangs as $item)
                        @php
                            $isActive = (bool) $item->trang_thai_hoat_dong;
                            $memberName = $item->thanhVien?->ten_hang;
                        @endphp

                        <tr>
                            <td>
                                <div class="customer-profile-cell">
                                    <span class="customer-avatar">
                                        <i class="fa-solid fa-user"></i>
                                    </span>
                                    <div>
                                        <strong>{{ $item->ho_ten }}</strong>
                                        <small>
                                            <i class="fa-regular fa-calendar"></i>
                                            Tạo {{ $item->created_at?->format('d/m/Y') ?? '-' }}
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="customer-contact">
                                    <strong>{{ $item->email }}</strong>
                                    <small>
                                        <i class="fa-solid fa-phone"></i>
                                        {{ $item->so_dien_thoai ?: 'Chưa có SĐT' }}
                                    </small>
                                </div>
                            </td>
                            <td>
                                <span class="customer-member-badge {{ $memberName ? 'is-member' : 'is-empty' }}">
                                    <i class="fa-solid {{ $memberName ? 'fa-crown' : 'fa-circle-minus' }}"></i>
                                    {{ $memberName ? strtoupper($memberName) : 'Chưa có' }}
                                </span>
                            </td>
                            <td>
                                <span class="customer-ticket-count">
                                    <i class="fa-solid fa-ticket"></i>
                                    {{ number_format($item->ve_xem_phims_count) }}
                                </span>
                            </td>
                            <td>
                                <span class="customer-date">
                                    <i class="fa-solid fa-cake-candles"></i>
                                    {{ $item->ngay_sinh?->format('d/m/Y') ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <span class="customer-status {{ $isActive ? 'is-active' : 'is-locked' }}">
                                    <i class="fa-solid {{ $isActive ? 'fa-circle-check' : 'fa-lock' }}"></i>
                                    {{ $isActive ? 'Hoạt động' : 'Bị khóa' }}
                                </span>
                            </td>
                            <td>
                                <div class="customer-actions">
                                    <a href="{{ route('admin.khach-hang.show', $item) }}" class="customer-action-btn is-view" title="Chi tiết">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.khach-hang.edit', $item) }}" class="customer-action-btn is-edit" title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.khach-hang.toggle-status', $item) }}" onsubmit="return confirm('Bạn có chắc muốn thay đổi trạng thái tài khoản này?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="customer-action-btn {{ $isActive ? 'is-lock' : 'is-unlock' }}" title="{{ $isActive ? 'Khóa tài khoản' : 'Mở khóa tài khoản' }}">
                                            <i class="fa-solid {{ $isActive ? 'fa-lock' : 'fa-lock-open' }}"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="customer-account-empty">
                                    <i class="fa-solid fa-user-group"></i>
                                    <h3>Chưa có khách hàng phù hợp</h3>
                                    <p>Thử đổi bộ lọc hoặc tạo tài khoản khách hàng mới để hỗ trợ đặt vé tại quầy.</p>
                                    <a href="{{ route('admin.khach-hang.create') }}" class="customer-account-primary-btn">
                                        <i class="fa-solid fa-user-plus"></i>
                                        Thêm khách hàng
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="customer-account-pagination">
            {{ $khachHangs->links() }}
        </div>
    </section>
</div>
@endsection
