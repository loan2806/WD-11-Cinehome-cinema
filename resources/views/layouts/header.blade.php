<header class="cine-navbar booking-navbar">
    <div class="container-fluid px-5">
        <div class="booking-navbar-inner">

            <a href="{{ route('home') }}" class="booking-brand text-decoration-none">
                <span class="booking-brand-mark cinehome-logo-sparkle">
                    <img src="{{ asset('assets/images/LOGO copy.png') }}" alt="CineHome Logo" class="cinehome-logo-img">
                </span>
                <span>Cine<span>Home</span></span>
            </a>

            <nav class="nav-menu booking-nav">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">
                    Trang chủ
                </a>

                <a href="{{ route('user.phims.index') }}" class="{{ request()->routeIs('user.phims.*') || request()->routeIs('user.movies.*') ? 'active' : '' }}">
                    Phim
                </a>

                <a href="{{ route('user.cinemas.index') }}" class="{{ request()->routeIs('user.cinemas.*') ? 'active' : '' }}">
                    Rạp chiếu
                </a>

                <a href="{{ route('dat_ve.chon_phim') }}" class="{{ request()->routeIs('dat_ve.*') ? 'active' : '' }}">
                    Đặt vé
                </a>

                <a href="{{ route('user.tin-tuc.index') }}" class="{{ request()->routeIs('user.tin-tuc.*') ? 'active' : '' }}">
                    Tin tức
                </a>

                <a href="{{ route('user.voucher.index') }}" class="{{ request()->routeIs('user.voucher.*') || request()->routeIs('user.khuyen-mai.*') ? 'active' : '' }}">
                    Khuyến mãi
                </a>

                <a href="{{ route('user.lien-he.index') }}" class="{{ request()->routeIs('user.lien-he.*') ? 'active' : '' }}">
                    Liên hệ
                </a>
            </nav>

            <div class="nav-action booking-actions">
                <form action="{{ route('user.phims.index') }}" method="GET" class="booking-search">
                    <button type="submit" class="booking-search-btn" aria-label="Tìm kiếm phim">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>

                    <input type="text" name="tim_kiem" class="search-box" placeholder="Tìm phim..." value="{{ request('tim_kiem') }}" autocomplete="off">
                </form>

                @guest

                {{-- ========================= --}}
                {{-- KHÁCH CHƯA ĐĂNG NHẬP --}}
                {{-- ========================= --}}

                <button type="button"
                    data-auth-open="login"
                    class="booking-login-btn">

                    Đăng nhập

                </button>

                <button type="button"
                    data-auth-open="register"
                    class="booking-register-btn">

                    Đăng ký

                </button>

                @else

                @php

                $userId = Auth::id();

                /*
                |--------------------------------------------------------------------------
                | Thông báo cá nhân
                |--------------------------------------------------------------------------
                */
                $userNotifications = \App\Models\ThongBaoCaNhan::where(
                'nguoi_dung_id',
                $userId
                )
                ->latest()
                ->get();

                /*
                |--------------------------------------------------------------------------
                | Thông báo Push từ Admin
                |--------------------------------------------------------------------------
                */
                $pushNotifications = \App\Models\ThongBaoPushNguoiDung::with('thongBaoPush')
                ->where('nguoi_dung_id', $userId)
                ->latest()
                ->get();

                /*
                |--------------------------------------------------------------------------
                | Gộp 2 loại thông báo
                |--------------------------------------------------------------------------
                */
                $allNotifications = collect();

                /*
                |--------------------------------------------------------------------------
                | Thông báo cá nhân
                |--------------------------------------------------------------------------
                */
                foreach ($userNotifications as $notification) {

                $notification->notification_type = 'personal';

                $notification->notification_time = $notification->created_at;

                $allNotifications->push($notification);
                }

                /*
                |--------------------------------------------------------------------------
                | Thông báo Push
                |--------------------------------------------------------------------------
                */
                foreach ($pushNotifications as $notification) {

                if (!$notification->thongBaoPush) {
                continue;
                }

                $notification->notification_type = 'push';

                $notification->notification_time =
                $notification->thongBaoPush->thoi_gian_gui
                ?? $notification->created_at;

                $allNotifications->push($notification);
                }

                /*
                |--------------------------------------------------------------------------
                | Sắp xếp mới nhất
                |--------------------------------------------------------------------------
                */
                $allNotifications = $allNotifications
                ->sortByDesc('notification_time')
                ->take(5)
                ->values();

                /*
                |--------------------------------------------------------------------------
                | Đếm chưa đọc
                |--------------------------------------------------------------------------
                */
                $soThongBaoCaNhanChuaDoc = \App\Models\ThongBaoCaNhan::where(
                'nguoi_dung_id',
                $userId
                )
                ->where('da_doc', 0)
                ->count();

                $soThongBaoPushChuaDoc = \App\Models\ThongBaoPushNguoiDung::where(
                'nguoi_dung_id',
                $userId
                )
                ->where('da_doc', 0)
                ->count();

                $soThongBaoChuaDoc =
                $soThongBaoCaNhanChuaDoc +
                $soThongBaoPushChuaDoc;

                @endphp

                {{-- CHUÔNG THÔNG BÁO USER --}}
                <div class="booking-notification-wrapper">

                    {{-- NÚT CHUÔNG --}}
                    <button
                        type="button"
                        id="userNotificationBtn"
                        class="booking-notification-btn"
                        aria-label="Thông báo"
                        aria-expanded="false"
                        aria-haspopup="true">

                        <i class="fa-solid fa-bell"></i>

                        @if ($soThongBaoChuaDoc > 0)
                        <span class="booking-notification-badge">
                            {{ (int) $soThongBaoChuaDoc }}
                        </span>
                        @endif

                    </button>


                    {{-- DROPDOWN THÔNG BÁO --}}
                    <div
                        id="userNotificationDropdown"
                        class="booking-notification-dropdown"
                        aria-hidden="true">

                        {{-- HEADER --}}
                        <div class="booking-notification-header">

                            <div class="booking-notification-title">

                                <span class="booking-notification-title-icon">
                                    <i class="fa-solid fa-bell"></i>
                                </span>

                                <div>
                                    <strong>Thông báo hệ thống</strong>

                                    <small>
                                        {{ $soThongBaoChuaDoc }} thông báo chưa đọc
                                    </small>
                                </div>

                            </div>

                        </div>


                        {{-- DANH SÁCH --}}
                        <div class="booking-notification-list">

                            @forelse ($allNotifications as $notification)

                            @if ($notification->notification_type === 'personal')

                            {{-- =====================================================
             THÔNG BÁO CÁ NHÂN
        ====================================================== --}}

                            <a
                                href="{{ $notification->duong_dan ?: route('user.notifications.index') }}"
                                class="booking-notification-item {{ !$notification->da_doc ? 'is-unread' : '' }}">

                                <span class="booking-notification-item-icon">

                                    @if ($notification->loai_thong_bao === 'diem')

                                    <i class="fa-solid fa-star"></i>

                                    @elseif ($notification->loai_thong_bao === 've')

                                    <i class="fa-solid fa-ticket"></i>

                                    @elseif ($notification->loai_thong_bao === 'voucher')

                                    <i class="fa-solid fa-gift"></i>

                                    @elseif ($notification->loai_thong_bao === 'hang_thanh_vien')

                                    <i class="fa-solid fa-ranking-star"></i>

                                    @elseif ($notification->loai_thong_bao === 'tai_khoan')

                                    <i class="fa-solid fa-user-gear"></i>

                                    @else

                                    <i class="fa-solid fa-bell"></i>

                                    @endif

                                </span>


                                <div class="booking-notification-item-content">

                                    <strong>
                                        {{ $notification->tieu_de }}
                                    </strong>

                                    <p>
                                        {{ \Illuminate\Support\Str::limit($notification->noi_dung, 70) }}
                                    </p>

                                    <time>
                                        {{ $notification->created_at->diffForHumans() }}
                                    </time>

                                </div>

                            </a>


                            @else

                            {{-- =====================================================
             THÔNG BÁO PUSH TỪ ADMIN
        ====================================================== --}}

                            @php
                            $push = $notification->thongBaoPush;
                            @endphp

                            @if ($push)

                            <a
                                href="{{ route('user.notifications.index') }}"
                                class="booking-notification-item {{ !$notification->da_doc ? 'is-unread' : '' }}">

                                <span class="booking-notification-item-icon">

                                    @if ($push->loai === 'promo')

                                    <i class="fa-solid fa-gift"></i>

                                    @elseif ($push->loai === 'success')

                                    <i class="fa-solid fa-circle-check"></i>

                                    @elseif ($push->loai === 'warning')

                                    <i class="fa-solid fa-triangle-exclamation"></i>

                                    @elseif ($push->loai === 'system')

                                    <i class="fa-solid fa-gear"></i>

                                    @else

                                    <i class="fa-solid fa-bell"></i>

                                    @endif

                                </span>


                                <div class="booking-notification-item-content">

                                    <strong>
                                        {{ $push->tieu_de }}
                                    </strong>

                                    <p>
                                        {{ \Illuminate\Support\Str::limit($push->noi_dung, 70) }}
                                    </p>

                                    <time>
                                        {{
                            ($push->thoi_gian_gui ?? $push->created_at)
                                ->diffForHumans()
                        }}
                                    </time>

                                </div>

                            </a>

                            @endif

                            @endif

                            @empty

                            <div class="booking-notification-empty">

                                <i class="fa-regular fa-bell-slash"></i>

                                <strong>
                                    Không có thông báo
                                </strong>

                                <span>
                                    Bạn hiện không có thông báo mới.
                                </span>

                            </div>

                            @endforelse

                        </div>


                        {{-- FOOTER --}}
                        <div class="booking-notification-footer">

                            <a href="{{ route('user.notifications.index') }}">
                                Xem tất cả
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>

                        </div>

                    </div>

                </div>
            </div>

            <div class="booking-user-dropdown" id="userDropdownBox">
                <button type="button" id="userDropdownBtn" class="booking-user-btn" aria-expanded="false" aria-haspopup="menu" aria-controls="userDropdownMenu">
                    <span class="booking-user-avatar">
                        <i class="fa-solid fa-user"></i>
                    </span>

                    <span class="booking-user-name">
                        {{ Auth::user()->ho_ten }}
                    </span>

                    <i class="fa-solid fa-chevron-down text-[10px]"></i>
                </button>

                <div id="userDropdownMenu" class="booking-user-menu hidden" role="menu" hidden>
                    <div class="booking-user-info">
                        <span class="booking-user-avatar lg">
                            <i class="fa-solid fa-user"></i>
                        </span>

                        <div class="min-w-0">
                            <strong>{{ Auth::user()->ho_ten }}</strong>
                            <small>{{ Auth::user()->email }}</small>
                        </div>
                    </div>

                    <div class="booking-menu-list">
                        <a href="{{ route('profile.edit') }}">
                            <i class="fa-solid fa-user-gear"></i>
                            Tài khoản
                        </a>

                        @if (Auth::user()->hasRole('Khách hàng') || Auth::user()->vai_tro === 'khach_hang')
                        <a href="{{ route('user.ve_xem_phim.index') }}">
                            <i class="fa-solid fa-ticket"></i>
                            Vé của tôi
                        </a>

                        <a href="{{ route('user.thanh-vien.index') }}">
                            <i class="fa-solid fa-id-card"></i>
                            Thẻ thành viên & Điểm
                        </a>

                        <a href="{{ route('user.voucher.index') }}">
                            <i class="fa-solid fa-gift"></i>
                            Đổi điểm lấy voucher
                        </a>

                        <a href="{{ route('user.voucher.my') }}">
                            <i class="fa-solid fa-ticket-percent"></i>
                            Voucher của tôi
                        </a>

                        <a href="{{ route('user.notifications.index') }}">
                            <i class="fa-solid fa-bell"></i>
                            Thông báo
                        </a>
                        <a href="{{ route('user.lien-he.luu-tru') }}">
                            <i class="fa-solid fa-headset"></i>
                            Liên hệ
                        </a>
                        @endif

                        <a href="{{ route('user.phims.index') }}">
                            <i class="fa-solid fa-film"></i>
                            Danh sách phim
                        </a>

                        {{-- Cấu hình hiển thị nút Trang quản lý cho toàn bộ các nhóm Quản lý / Admin --}}
                        @if (
                        Auth::user()->hasRole('Quản trị viên') ||
                        Auth::user()->hasRole('Quản lý hệ thống') ||
                        Auth::user()->hasRole('Quản lý') ||
                        Auth::user()->hasRole('Quản lý phòng chiếu') ||
                        Auth::user()->hasRole('Quản lý rạp') ||
                        Auth::user()->hasRole('Sub-Admin') ||
                        in_array(Auth::user()->vai_tro, ['admin', 'super_admin', 'quan_ly', 'quan_ly_phong_chieu', 'quan_ly_rap', 'quan_ly_he_thong', 'manager'])
                        )
                        <a href="{{ route('dashboard') }}">
                            <i class="fa-solid fa-user-shield"></i>
                            Trang quản lý
                        </a>
                        @endif

                        @if (Auth::user()->hasRole('Nhân viên') || Auth::user()->vai_tro === 'nhan_vien')
                        <a href="{{ route('dashboard') }}">
                            <i class="fa-solid fa-user-tie"></i>
                            Trang nhân viên
                        </a>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="booking-logout-form">
                        @csrf
                        <button type="submit">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            Đăng xuất
                        </button>
                    </form>
                </div>
            </div>
            @endguest
        </div>

    </div>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const notificationBtn = document.getElementById('userNotificationBtn');
        const notificationDropdown = document.getElementById('userNotificationDropdown');
        const notificationBadge = document.querySelector('.booking-notification-badge');

        if (!notificationBtn || !notificationDropdown) {
            return;
        }

        notificationBtn.addEventListener('click', function(event) {

            event.preventDefault();
            event.stopPropagation();

            const isOpen = notificationDropdown.classList.contains('show');

            if (isOpen) {

                notificationDropdown.classList.remove('show');

                notificationBtn.setAttribute('aria-expanded', 'false');

                notificationDropdown.setAttribute('aria-hidden', 'true');

            } else {

                notificationDropdown.classList.add('show');

                notificationBtn.setAttribute('aria-expanded', 'true');

                notificationDropdown.setAttribute('aria-hidden', 'false');

                // ==========================================
                // ĐÁNH DẤU TẤT CẢ THÔNG BÁO ĐÃ ĐỌC
                // ==========================================
                fetch('{{ route('user.notifications.mark-all-read') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({})
                        })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Không thể đánh dấu thông báo đã đọc');
                        }

                        return response.json();
                    })
                    .then(data => {

                        if (data.success) {

                            // Xóa số đỏ trên chuông
                            if (notificationBadge) {
                                notificationBadge.remove();
                            }

                            // Đổi dòng "x thông báo chưa đọc" thành 0
                            const notificationCountText =
                                document.querySelector('.booking-notification-title small');

                            if (notificationCountText) {
                                notificationCountText.textContent =
                                    '0 thông báo chưa đọc';
                            }

                            // Bỏ trạng thái chưa đọc
                            document
                                .querySelectorAll('.booking-notification-item.is-unread')
                                .forEach(item => {
                                    item.classList.remove('is-unread');
                                });
                        }

                    })
                    .catch(error => {
                        console.error('Lỗi đánh dấu thông báo:', error);
                    });
            }

        });


        // Click bên ngoài thì đóng
        document.addEventListener('click', function(event) {

            if (
                !notificationDropdown.contains(event.target) &&
                !notificationBtn.contains(event.target)
            ) {

                notificationDropdown.classList.remove('show');

                notificationBtn.setAttribute(
                    'aria-expanded',
                    'false'
                );

                notificationDropdown.setAttribute(
                    'aria-hidden',
                    'true'
                );
            }

        });

    });
</script>