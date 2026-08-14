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

        <div style="display: flex; gap: 10px; align-items: center;">

            <a href="{{ route('admin.khach-hang.trash') }}"
                class="customer-account-primary-btn"
                style="background: #3f3f46;">
                <i class="fa-solid fa-trash-can"></i>
                Thùng rác
            </a>

            <a href="{{ route('admin.khach-hang.create') }}"
                class="customer-account-primary-btn">
                <i class="fa-solid fa-user-plus"></i>
                Thêm khách hàng
            </a>

        </div>
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
                    placeholder="Tìm tên, email hoặc số điện thoại...">
            </label>

            <div class="customer-status-dropdown" data-customer-dropdown>

                <input
                    type="hidden"
                    name="trang_thai"
                    value="{{ request('trang_thai') }}"
                    data-customer-dropdown-value>

                <button
                    type="button"
                    class="customer-dropdown-trigger"
                    data-customer-dropdown-trigger>

                    <span class="customer-dropdown-current">

                        @if(request('trang_thai') === '1')

                        <i class="fa-solid fa-circle-check customer-status-active-icon"></i>
                        <span>Đang hoạt động</span>

                        @elseif(request('trang_thai') === '0')

                        <i class="fa-solid fa-lock customer-status-locked-icon"></i>
                        <span>Bị khóa</span>

                        @else

                        <i class="fa-solid fa-filter customer-status-all-icon"></i>
                        <span>Tất cả trạng thái</span>

                        @endif

                    </span>

                    <i class="fa-solid fa-chevron-down customer-dropdown-arrow"></i>
                </button>


                <div
                    class="customer-dropdown-menu"
                    data-customer-dropdown-menu>

                    <button
                        type="button"
                        class="customer-dropdown-option {{ !request('trang_thai') ? 'is-selected' : '' }}"
                        data-value="">

                        <i class="fa-solid fa-filter customer-status-all-icon"></i>
                        <span>Tất cả trạng thái</span>

                    </button>


                    <button
                        type="button"
                        class="customer-dropdown-option {{ request('trang_thai') === '1' ? 'is-selected' : '' }}"
                        data-value="1">

                        <i class="fa-solid fa-circle-check customer-status-active-icon"></i>
                        <span>Đang hoạt động</span>

                    </button>


                    <button
                        type="button"
                        class="customer-dropdown-option {{ request('trang_thai') === '0' ? 'is-selected' : '' }}"
                        data-value="0">

                        <i class="fa-solid fa-lock customer-status-locked-icon"></i>
                        <span>Bị khóa</span>

                    </button>

                </div>

            </div>

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
                        <th>Hạng thành viên</th>
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

                    $memberName = $item->thanhVien?->hang_thanh_vien;

                    $memberClass = match (strtolower($memberName ?? '')) {
                    'member' => 'is-member',
                    'silver' => 'is-silver',
                    'gold' => 'is-gold',
                    'platinum' => 'is-platinum',
                    default => 'is-empty',
                    };

                    $memberIcon = match (strtolower($memberName ?? '')) {
                    'member' => 'fa-user',
                    'silver' => 'fa-medal',
                    'gold' => 'fa-crown',
                    'platinum' => 'fa-gem',
                    default => 'fa-circle-minus',
                    };
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
                            <span class="customer-member-badge {{ $memberClass }}">
                                <i class="fa-solid {{ $memberIcon }}"></i>
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
                                <form
                                    action="{{ route('admin.khach-hang.destroy', $item) }}"
                                    method="POST"
                                    onsubmit="return confirm('Bạn có chắc muốn chuyển khách hàng này vào thùng rác?');">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="staff-action-btn is-delete"
                                        title="Xóa">
                                        <i class="fa-solid fa-trash-can"></i>
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
<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('[data-customer-dropdown]').forEach(function (dropdown) {

        const trigger = dropdown.querySelector(
            '[data-customer-dropdown-trigger]'
        );

        const menu = dropdown.querySelector(
            '[data-customer-dropdown-menu]'
        );

        const hiddenInput = dropdown.querySelector(
            '[data-customer-dropdown-value]'
        );

        if (!trigger || !menu || !hiddenInput) return;


        /* =====================================================
           TÍNH HƯỚNG XỔ
        ===================================================== */

        function calculateDirection() {

            dropdown.classList.remove(
                'dropdown-up',
                'dropdown-down'
            );

            const triggerRect =
                trigger.getBoundingClientRect();

            /*
             * Menu có 3 option,
             * mỗi option khoảng 52px
             */
            const menuHeight =
                menu.scrollHeight || 160;

            const spaceBelow =
                window.innerHeight - triggerRect.bottom;

            const spaceAbove =
                triggerRect.top;


            /*
             * Không đủ phía dưới
             * và phía trên rộng hơn
             * => xổ lên
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


        /* =====================================================
           MỞ
        ===================================================== */

        function openDropdown() {

            calculateDirection();

            dropdown.classList.add('is-open');

            requestAnimationFrame(function () {
                calculateDirection();
            });
        }


        /* =====================================================
           ĐÓNG
        ===================================================== */

        function closeDropdown() {

            dropdown.classList.remove('is-open');

        }


        /* =====================================================
           CLICK TRIGGER
        ===================================================== */

        trigger.addEventListener('click', function (e) {

            e.preventDefault();
            e.stopPropagation();

            if (
                dropdown.classList.contains('is-open')
            ) {

                closeDropdown();

            } else {

                /* Đóng dropdown khác */

                document
                    .querySelectorAll(
                        '[data-customer-dropdown].is-open'
                    )
                    .forEach(function (other) {

                        if (other !== dropdown) {
                            other.classList.remove('is-open');
                        }

                    });

                openDropdown();
            }

        });


        /* =====================================================
           CHỌN OPTION
        ===================================================== */

        menu
            .querySelectorAll('[data-value]')
            .forEach(function (option) {

                option.addEventListener('click', function (e) {

                    e.preventDefault();
                    e.stopPropagation();

                    const value =
                        this.dataset.value;

                    const icon =
                        this.querySelector('i')
                            ?.outerHTML || '';

                    const text =
                        this.querySelector('span')
                            ?.textContent
                            .trim() || '';

                    /* Gán value cho form */

                    hiddenInput.value = value;


                    /* Đổi text trên trigger */

                    dropdown
                        .querySelector(
                            '.customer-dropdown-current'
                        )
                        .innerHTML =
                            icon +
                            '<span>' +
                            text +
                            '</span>';


                    /* Đổi selected */

                    menu
                        .querySelectorAll(
                            '.customer-dropdown-option'
                        )
                        .forEach(function (item) {

                            item.classList.remove(
                                'is-selected'
                            );

                        });

                    this.classList.add(
                        'is-selected'
                    );


                    closeDropdown();

                });

            });


        /* =====================================================
           CLICK RA NGOÀI
        ===================================================== */

        document.addEventListener('click', function (e) {

            if (!dropdown.contains(e.target)) {
                closeDropdown();
            }

        });


        /* =====================================================
           RESIZE
        ===================================================== */

        window.addEventListener('resize', function () {

            if (
                dropdown.classList.contains('is-open')
            ) {

                calculateDirection();

            }

        });


        /* =====================================================
           SCROLL
        ===================================================== */

        window.addEventListener(
            'scroll',
            function () {

                if (
                    dropdown.classList.contains('is-open')
                ) {

                    calculateDirection();

                }

            },
            true
        );

    });

});
</script>