<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CineHome') }} - Hồ sơ cá nhân</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/user-home.css') }}?v={{ filemtime(public_path('assets/css/user-home.css')) }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="profile-body">
    @php
    $urlLogo = asset('assets/images/LOGO copy.png');
    $user = Auth::user();
    $roleLabel = $user?->vai_tro === 'admin'
    ? 'Quản trị viên'
    : ($user?->vai_tro === 'nhan_vien' ? 'Nhân viên rạp' : 'Thành viên CineHome');
    @endphp

    <div class="profile-layout">
        <nav class="profile-navbar" aria-label="Thanh điều hướng hồ sơ">
            <a href="{{ route('home') }}" class="profile-brand">
                <span class="profile-brand-mark cinehome-logo-sparkle">
                    <img src="{{ $urlLogo }}" alt="CineHome" class="cinehome-logo-img">
                </span>
                <span>
                    Cine<span>Home</span>
                    <small>Hồ sơ cá nhân</small>
                </span>
            </a>

            <div class="profile-nav-actions">
                @php
                $userNotifications = \App\Models\ThongBaoCaNhan::where(
                'nguoi_dung_id',
                Auth::id()
                )
                ->latest()
                ->take(5)
                ->get();

                $soThongBaoChuaDoc = \App\Models\ThongBaoCaNhan::where(
                'nguoi_dung_id',
                Auth::id()
                )
                ->where('da_doc', 0)
                ->count();
                @endphp

                <div class="profile-notification-wrapper">

                    <button
                        type="button"
                        id="profileNotificationBtn"
                        class="profile-icon-btn"
                        aria-label="Thông báo"
                        aria-expanded="false"
                        aria-controls="profileNotificationDropdown">

                        <i class="fa-solid fa-bell"></i>

                        @if ($soThongBaoChuaDoc > 0)
                        <span class="booking-notification-badge">
                            {{ $soThongBaoChuaDoc }}
                        </span>
                        @endif

                    </button>

                    <div
                        id="profileNotificationDropdown"
                        class="profile-notification-dropdown">

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

                        <div class="booking-notification-list">

                            @forelse ($userNotifications as $notification)

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
                                    <strong>{{ $notification->tieu_de }}</strong>

                                    <p>
                                        {{ \Illuminate\Support\Str::limit($notification->noi_dung, 70) }}
                                    </p>

                                    <time>
                                        {{ $notification->created_at->diffForHumans() }}
                                    </time>
                                </div>

                            </a>

                            @empty

                            <div class="booking-notification-empty">
                                <i class="fa-regular fa-bell-slash"></i>
                                <strong>Không có thông báo</strong>
                                <span>Bạn hiện không có thông báo mới.</span>
                            </div>

                            @endforelse

                        </div>

                        <div class="booking-notification-footer">
                            <a href="{{ route('user.notifications.index') }}">
                                Xem tất cả
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>

                    </div>
                </div>

                <details class="profile-user-menu">
                    <summary>
                        <span class="profile-avatar-sm">
                            {{ mb_substr($user->ho_ten ?? 'U', 0, 1) }}
                        </span>
                        <span>
                            {{ $user->ho_ten ?? 'Tài khoản' }}
                            <small>{{ $roleLabel }}</small>
                        </span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </summary>

                    <div class="profile-user-dropdown">
                        <a href="{{ route('home') }}">
                            <i class="fa-solid fa-house"></i>
                            Trang chủ
                        </a>

                        @if($user && ($user->hasRole('Quản trị viên') || $user->hasRole('Quản lý hệ thống') || $user->hasRole('Quản lý') || $user->hasRole('Quản lý phòng chiếu') || $user->hasRole('Nhân viên') || in_array($user->vai_tro, ['admin', 'quan_ly_he_thong', 'nhan_vien'])))
                        <a href="{{ route('dashboard') }}" class="is-highlight">
                            <i class="fa-solid fa-gauge-high"></i>
                            Khu quản trị
                        </a>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit">
                                <i class="fa-solid fa-right-from-bracket"></i>
                                Đăng xuất
                            </button>
                        </form>
                    </div>
                </details>
            </div>
        </nav>

        <main class="profile-main">
            {{ $slot }}
        </main>
    </div>

    <script src="{{ asset('assets/js/user-home.js') }}?v={{ filemtime(public_path('assets/js/user-home.js')) }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const btn = document.getElementById('profileNotificationBtn');
            const dropdown = document.getElementById('profileNotificationDropdown');

            if (!btn || !dropdown) {
                return;
            }

            btn.addEventListener('click', function(event) {

                event.preventDefault();
                event.stopPropagation();

                // Đánh dấu tất cả thông báo đã đọc
                fetch('{{ route('user.notifications.mark-all-read') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            }
                        })
                    .then(response => response.json())
                    .then(data => {

                        if (data.success) {

                            // Xóa số đỏ trên chuông
                            const badge = btn.querySelector('.booking-notification-badge');

                            if (badge) {
                                badge.remove();
                            }

                            // Đổi số thông báo chưa đọc thành 0
                            const unreadText = dropdown.querySelector(
                                '.booking-notification-title small'
                            );

                            if (unreadText) {
                                unreadText.textContent = '0 thông báo chưa đọc';
                            }

                            // Bỏ trạng thái chưa đọc
                            dropdown
                                .querySelectorAll('.booking-notification-item.is-unread')
                                .forEach(item => {
                                    item.classList.remove('is-unread');
                                });
                        }

                    })
                    .catch(error => {
                        console.error('Không thể đánh dấu thông báo đã đọc:', error);
                    });

                // Mở / đóng dropdown
                const isOpen = dropdown.classList.contains('show');

                if (isOpen) {
                    dropdown.classList.remove('show');
                    btn.setAttribute('aria-expanded', 'false');
                } else {
                    dropdown.classList.add('show');
                    btn.setAttribute('aria-expanded', 'true');
                }
            });


            // Không cho click bên trong dropdown đóng dropdown
            dropdown.addEventListener('click', function(event) {
                event.stopPropagation();
            });


            // Click bên ngoài thì đóng dropdown
            document.addEventListener('click', function() {

                dropdown.classList.remove('show');

                btn.setAttribute(
                    'aria-expanded',
                    'false'
                );

            });

        });
    </script>
</body>

</html>