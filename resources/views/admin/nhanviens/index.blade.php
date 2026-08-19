@extends('layouts.admin')

@section('title', 'Quản lý nhân viên')
@section('page-title', 'Quản lý nhân viên')
@section('page-subtitle', 'Theo dõi tài khoản, trạng thái và thao tác nhanh với nhân sự vận hành rạp')

@section('content')
@php
$summary = $summary ?? [
'total' => $nhanViens->total(),
'active' => 0,
'locked' => 0,
'new_this_month' => 0,
];

$activeFilters = collect([
request('keyword'),
request('status'),
])->filter()->count();
@endphp

<div class="staff-list-page">
    @include('admin.partials.flash')

    <section class="staff-list-hero">
        <div class="staff-list-hero-copy">
            <span class="staff-list-kicker">
                <i class="fa-solid fa-user-shield"></i>
                Quản trị nhân sự
            </span>
            <h2>Danh sách nhân viên CineHome</h2>
            <p>Quản lý tài khoản nhân viên, kiểm tra trạng thái hoạt động và xử lý khóa/mở khóa nhanh trong cùng một màn hình.</p>
            <div class="staff-list-hero-meta">
                <span><i class="fa-solid fa-users"></i> {{ number_format($summary['total']) }} nhân viên</span>
                <span><i class="fa-solid fa-circle-check"></i> {{ number_format($summary['active']) }} đang hoạt động</span>
                <span><i class="fa-solid fa-calendar-plus"></i> {{ number_format($summary['new_this_month']) }} mới trong tháng</span>
            </div>
        </div>

        <div class="d-flex gap-2">

            <a href="{{ route('admin.nhanviens.trash') }}"
                class="staff-list-secondary-btn">
                <i class="fa-solid fa-trash"></i>
                Thùng rác
            </a>
            <a href="{{ route('admin.nhanviens.create') }}"
                class="staff-list-primary-btn">
                <i class="fa-solid fa-plus"></i>
                Thêm nhân viên
            </a>

        </div>
    </section>

    <section class="staff-list-stats">
        <article class="staff-list-stat">
            <span class="staff-list-stat-icon is-total"><i class="fa-solid fa-id-badge"></i></span>
            <div>
                <small>Tổng nhân viên</small>
                <strong>{{ number_format($summary['total']) }}</strong>
            </div>
        </article>
        <article class="staff-list-stat">
            <span class="staff-list-stat-icon is-active"><i class="fa-solid fa-user-check"></i></span>
            <div>
                <small>Đang hoạt động</small>
                <strong>{{ number_format($summary['active']) }}</strong>
            </div>
        </article>
        <article class="staff-list-stat">
            <span class="staff-list-stat-icon is-locked"><i class="fa-solid fa-user-lock"></i></span>
            <div>
                <small>Đã khóa</small>
                <strong>{{ number_format($summary['locked']) }}</strong>
            </div>
        </article>
        <article class="staff-list-stat">
            <span class="staff-list-stat-icon is-new"><i class="fa-solid fa-calendar-plus"></i></span>
            <div>
                <small>Mới trong tháng</small>
                <strong>{{ number_format($summary['new_this_month']) }}</strong>
            </div>
        </article>
    </section>

    <section class="staff-list-panel">
        <div class="staff-list-panel-head">
            <div>
                <span class="staff-list-kicker">Danh sách</span>
                <h3>Nhân viên hệ thống</h3>
                <p>Đang hiển thị {{ $nhanViens->count() }} / {{ $nhanViens->total() }} nhân viên theo bộ lọc hiện tại.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.nhanviens.index') }}" class="staff-list-filter">
            <label class="staff-list-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input
                    type="text"
                    name="keyword"
                    value="{{ request('keyword') }}"
                    placeholder="Tìm theo tên hoặc email...">
            </label>

            <div class="staff-status-dropdown" data-dropdown>
                <input
                    type="hidden"
                    name="status"
                    value="{{ request('status') }}"
                    data-dropdown-value>

                <button
                    type="button"
                    class="staff-dropdown-trigger"
                    data-dropdown-trigger>
                    <span class="staff-dropdown-current">
                        @if(request('status') === 'active')
                        <i class="fa-solid fa-circle-check status-active-icon"></i>
                        <span>Đang hoạt động</span>
                        @elseif(request('status') === 'locked')
                        <i class="fa-solid fa-lock status-locked-icon"></i>
                        <span>Đã khóa</span>
                        @else
                        <i class="fa-solid fa-filter status-all-icon"></i>
                        <span>Tất cả trạng thái</span>
                        @endif
                    </span>

                    <i class="fa-solid fa-chevron-down staff-dropdown-arrow"></i>
                </button>

                <div class="staff-dropdown-menu" data-dropdown-menu>
                    <button
                        type="button"
                        class="staff-dropdown-option {{ !request('status') ? 'is-selected' : '' }}"
                        data-value="">
                        <i class="fa-solid fa-filter status-all-icon"></i>
                        <span>Tất cả trạng thái</span>
                    </button>

                    <button
                        type="button"
                        class="staff-dropdown-option {{ request('status') === 'active' ? 'is-selected' : '' }}"
                        data-value="active">
                        <i class="fa-solid fa-circle-check status-active-icon"></i>
                        <span>Đang hoạt động</span>
                    </button>

                    <button
                        type="button"
                        class="staff-dropdown-option {{ request('status') === 'locked' ? 'is-selected' : '' }}"
                        data-value="locked">
                        <i class="fa-solid fa-lock status-locked-icon"></i>
                        <span>Đã khóa</span>
                    </button>
                </div>
            </div>

            <button class="staff-list-filter-btn" type="submit">
                <i class="fa-solid fa-filter"></i>
                Lọc
                @if ($activeFilters)
                <span>{{ $activeFilters }}</span>
                @endif
            </button>

            @if ($activeFilters)
            <a href="{{ route('admin.nhanviens.index') }}" class="staff-list-reset-btn" title="Xóa bộ lọc">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
            @endif
        </form>

        <div class="staff-list-table-wrap">
            <table class="staff-list-table">
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Nhân viên</th>
                        <th>Email</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th class="is-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($nhanViens as $nhanVien)
                    @php
                    $isActive = (bool) $nhanVien->trang_thai_hoat_dong;
                    $isSelf = auth()->id() === $nhanVien->id;
                    @endphp

                    <tr>
                        <td>
                            <span class="staff-id-badge">#{{ $nhanVien->id }}</span>
                        </td>
                        <td>
                            <div class="staff-profile-cell">
                                <span class="staff-avatar">
                                    <i class="fa-solid fa-user-tie"></i>
                                </span>
                                <div>
                                    <strong>{{ $nhanVien->ho_ten }}</strong>
                                    <small>
                                        <i class="fa-solid fa-briefcase"></i>
                                        Nhân viên hệ thống
                                        @if ($isSelf)
                                        <em>Tài khoản của bạn</em>
                                        @endif
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="staff-email">{{ $nhanVien->email }}</span>
                        </td>
                        <td>
                            <span class="staff-status {{ $isActive ? 'is-active' : 'is-locked' }}">
                                <i class="fa-solid {{ $isActive ? 'fa-circle-check' : 'fa-lock' }}"></i>
                                {{ $isActive ? 'Hoạt động' : 'Đã khóa' }}
                            </span>
                        </td>
                        <td>
                            <span class="staff-date">
                                <i class="fa-regular fa-calendar"></i>
                                {{ $nhanVien->created_at?->format('d/m/Y') ?? '-' }}
                            </span>
                        </td>
                        <td>
                            <div class="staff-actions">

                                <a href="{{ route('admin.nhanviens.edit', $nhanVien) }}"
                                    class="staff-action-btn is-edit"
                                    title="Chỉnh sửa">
                                    <i class="fa-solid fa-pen"></i>
                                </a>


                                @if($nhanVien->trashed())

                                {{-- Khôi phục nhân viên --}}
                                <form method="POST" action="{{ route('admin.nhanviens.restore', $nhanVien->id) }}">
                                    @csrf
                                    <button class="staff-action-btn is-unlock"
                                        type="submit"
                                        title="Khôi phục nhân viên">
                                        <i class="fa-solid fa-rotate-left"></i>
                                    </button>
                                </form>

                                @else

                                {{-- Khóa / mở khóa --}}
                                <form method="POST" action="{{ route('admin.nhanviens.toggle-status', $nhanVien) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        class="staff-action-btn {{ $isActive ? 'is-lock' : 'is-unlock' }}"
                                        type="submit"
                                        title="{{ $isActive ? 'Khóa tài khoản' : 'Mở khóa tài khoản' }}"
                                        @disabled($isSelf)>
                                        <i class="fa-solid {{ $isActive ? 'fa-lock' : 'fa-lock-open' }}"></i>
                                    </button>
                                </form>


                                {{-- Xóa --}}
                                <form method="POST"
                                    action="{{ route('admin.nhanviens.destroy', $nhanVien) }}"
                                    onsubmit="return confirm('Xóa nhân viên {{ $nhanVien->ho_ten }}?')">

                                    @csrf
                                    @method('DELETE')

                                    <button class="staff-action-btn is-delete"
                                        type="submit"
                                        title="Xóa nhân viên"
                                        @disabled($isSelf)>
                                        <i class="fa-solid fa-trash"></i>
                                    </button>

                                </form>

                                @endif

                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="staff-list-empty">
                                <i class="fa-solid fa-user-group"></i>
                                <h3>Chưa có nhân viên phù hợp</h3>
                                <p>Thử đổi bộ lọc hoặc tạo tài khoản nhân viên mới để bắt đầu phân quyền vận hành.</p>
                                <a href="{{ route('admin.nhanviens.create') }}" class="staff-list-primary-btn">
                                    <i class="fa-solid fa-plus"></i>
                                    Thêm nhân viên
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="staff-list-pagination">
            {{ $nhanViens->links() }}
        </div>
    </section>
</div>
@endsection
<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('[data-dropdown]').forEach(function (dropdown) {

        const trigger = dropdown.querySelector('[data-dropdown-trigger]');
        const menu = dropdown.querySelector('[data-dropdown-menu]');
        const hiddenInput = dropdown.querySelector('[data-dropdown-value]');

        if (!trigger || !menu) return;

        function calculateDirection() {

            dropdown.classList.remove('dropdown-up', 'dropdown-down');

            const triggerRect = trigger.getBoundingClientRect();

            const menuHeight = menu.scrollHeight || 160;

            const spaceBelow =
                window.innerHeight - triggerRect.bottom;

            const spaceAbove =
                triggerRect.top;

            /*
             * Nếu phía dưới không đủ:
             * -> xổ lên
             *
             * Nếu phía dưới đủ:
             * -> xổ xuống
             */
            if (
                spaceBelow < menuHeight + 15 &&
                spaceAbove > spaceBelow
            ) {
                dropdown.classList.add('dropdown-up');
            } else {
                dropdown.classList.add('dropdown-down');
            }
        }

        function openDropdown() {

            calculateDirection();

            dropdown.classList.add('is-open');

            // Tính lại sau khi menu hiện
            requestAnimationFrame(function () {
                calculateDirection();
            });
        }

        function closeDropdown() {
            dropdown.classList.remove('is-open');
        }

        trigger.addEventListener('click', function (e) {

            e.stopPropagation();

            if (dropdown.classList.contains('is-open')) {
                closeDropdown();
            } else {
                // Đóng dropdown khác
                document.querySelectorAll('[data-dropdown].is-open')
                    .forEach(function (other) {
                        if (other !== dropdown) {
                            other.classList.remove('is-open');
                        }
                    });

                openDropdown();
            }
        });

        menu.querySelectorAll('[data-value]').forEach(function (option) {

            option.addEventListener('click', function (e) {

                e.stopPropagation();

                const value = this.dataset.value;

                const icon = this.querySelector('i')?.outerHTML || '';
                const text = this.querySelector('span')?.textContent.trim() || '';

                hiddenInput.value = value;

                dropdown.querySelector('.staff-dropdown-current').innerHTML =
                    icon + '<span>' + text + '</span>';

                menu.querySelectorAll('.staff-dropdown-option')
                    .forEach(function (item) {
                        item.classList.remove('is-selected');
                    });

                this.classList.add('is-selected');

                closeDropdown();
            });
        });

        // Click ra ngoài
        document.addEventListener('click', function (e) {

            if (!dropdown.contains(e.target)) {
                closeDropdown();
            }

        });

        // Scroll / resize thì tính lại vị trí
        window.addEventListener('resize', function () {

            if (dropdown.classList.contains('is-open')) {
                calculateDirection();
            }

        });

        window.addEventListener('scroll', function () {

            if (dropdown.classList.contains('is-open')) {
                calculateDirection();
            }

        }, true);

    });

});
</script>