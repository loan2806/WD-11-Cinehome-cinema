<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CineHome') }} - Hồ sơ cá nhân</title>

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet"
        href="{{ asset('assets/css/user-home.css') }}?v={{ filemtime(public_path('assets/css/user-home.css')) }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="profile-body">

@php
    $urlLogo = asset('assets/images/LOGO copy.png');
    $user = Auth::user();

    $roleLabel = $user?->vai_tro === 'admin'
        ? 'Quản trị viên'
        : ($user?->vai_tro === 'nhan_vien'
            ? 'Nhân viên rạp'
            : 'Thành viên CineHome');
@endphp


<div class="profile-layout">

    {{-- =========================================================
        NAVBAR
    ========================================================== --}}
    <nav class="profile-navbar" aria-label="Thanh điều hướng hồ sơ">

        {{-- LOGO --}}
        <a href="{{ route('home') }}" class="profile-brand">

            <span class="profile-brand-mark cinehome-logo-sparkle">

                <img
                    src="{{ $urlLogo }}"
                    alt="CineHome"
                    class="cinehome-logo-img">

            </span>

            <span>
                Cine<span>Home</span>
                <small>Hồ sơ cá nhân</small>
            </span>

        </a>


        {{-- =====================================================
            NAV ACTIONS
        ====================================================== --}}
        <div class="profile-nav-actions">

            @php

                $userId = Auth::id();

                /*
                |--------------------------------------------------------------------------
                | THÔNG BÁO CÁ NHÂN
                |--------------------------------------------------------------------------
                */
                $userNotifications =
                    \App\Models\ThongBaoCaNhan::where(
                        'nguoi_dung_id',
                        $userId
                    )
                    ->latest()
                    ->get();


                /*
                |--------------------------------------------------------------------------
                | THÔNG BÁO PUSH TỪ ADMIN
                |--------------------------------------------------------------------------
                */
                $pushNotifications =
                    \App\Models\ThongBaoPushNguoiDung::with('thongBaoPush')
                    ->where(
                        'nguoi_dung_id',
                        $userId
                    )
                    ->whereHas(
                        'thongBaoPush',
                        function ($query) {

                            $query->where(
                                'trang_thai',
                                'da_gui'
                            );

                        }
                    )
                    ->latest()
                    ->get();


                /*
                |--------------------------------------------------------------------------
                | GỘP THÔNG BÁO
                |--------------------------------------------------------------------------
                */
                $allNotifications = collect();


                /*
                |--------------------------------------------------------------------------
                | THÔNG BÁO CÁ NHÂN
                |--------------------------------------------------------------------------
                */
                foreach ($userNotifications as $notification) {

                    $notification->notification_type = 'personal';

                    $notification->notification_time =
                        $notification->created_at;

                    $allNotifications->push(
                        $notification
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | THÔNG BÁO PUSH
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

                    $allNotifications->push(
                        $notification
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | SẮP XẾP MỚI NHẤT
                |--------------------------------------------------------------------------
                */
                $allNotifications =
                    $allNotifications
                    ->sortByDesc('notification_time')
                    ->take(5)
                    ->values();


                /*
                |--------------------------------------------------------------------------
                | ĐẾM THÔNG BÁO CÁ NHÂN CHƯA ĐỌC
                |--------------------------------------------------------------------------
                */
                $soThongBaoCaNhanChuaDoc =
                    \App\Models\ThongBaoCaNhan::where(
                        'nguoi_dung_id',
                        $userId
                    )
                    ->where(
                        'da_doc',
                        0
                    )
                    ->count();


                /*
                |--------------------------------------------------------------------------
                | ĐẾM THÔNG BÁO PUSH CHƯA ĐỌC
                |--------------------------------------------------------------------------
                */
                $soThongBaoPushChuaDoc =
                    \App\Models\ThongBaoPushNguoiDung::where(
                        'nguoi_dung_id',
                        $userId
                    )
                    ->where(
                        'da_doc',
                        0
                    )
                    ->whereHas(
                        'thongBaoPush',
                        function ($query) {

                            $query->where(
                                'trang_thai',
                                'da_gui'
                            );

                        }
                    )
                    ->count();


                /*
                |--------------------------------------------------------------------------
                | TỔNG CHƯA ĐỌC
                |--------------------------------------------------------------------------
                */
                $soThongBaoChuaDoc =
                    $soThongBaoCaNhanChuaDoc
                    +
                    $soThongBaoPushChuaDoc;

            @endphp


            {{-- =================================================
                CHUÔNG THÔNG BÁO
            ================================================== --}}
            <div class="profile-notification-wrapper">

                <button
                    type="button"
                    id="profileNotificationBtn"
                    class="profile-icon-btn"
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


                {{-- =================================================
                    DROPDOWN
                ================================================== --}}
                <div
                    id="profileNotificationDropdown"
                    class="profile-notification-dropdown"
                    aria-hidden="true">


                    {{-- HEADER --}}
                    <div class="booking-notification-header">

                        <div class="booking-notification-title">

                            <span class="booking-notification-title-icon">

                                <i class="fa-solid fa-bell"></i>

                            </span>

                            <div>

                                <strong>
                                    Thông báo hệ thống
                                </strong>

                                <small>
                                    {{ $soThongBaoChuaDoc }}
                                    thông báo chưa đọc
                                </small>

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                        LIST
                    ================================================== --}}
                    <div class="booking-notification-list">

                        @forelse ($allNotifications as $notification)


                            {{-- =================================================
                                THÔNG BÁO CÁ NHÂN
                            ================================================== --}}
                            @if (
                                $notification->notification_type === 'personal'
                            )

                                <a
                                    href="{{ $notification->duong_dan ?: route('user.notifications.index') }}"
                                    class="booking-notification-item
                                    {{ !$notification->da_doc ? 'is-unread' : '' }}">

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
                                            {{
                                                \Illuminate\Support\Str::limit(
                                                    $notification->noi_dung,
                                                    70
                                                )
                                            }}
                                        </p>

                                        <time>
                                            {{ $notification->created_at->diffForHumans() }}
                                        </time>

                                    </div>

                                </a>


                            {{-- =================================================
                                THÔNG BÁO PUSH
                            ================================================== --}}
                            @elseif (
                                $notification->notification_type === 'push'
                            )

                                @php
                                    $push = $notification->thongBaoPush;
                                @endphp


                                @if ($push)

                                    <a
                                        href="{{ route('user.notifications.index') }}"
                                        class="booking-notification-item
                                        {{ !$notification->da_doc ? 'is-unread' : '' }}">

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
                                                {{
                                                    \Illuminate\Support\Str::limit(
                                                        $push->noi_dung,
                                                        70
                                                    )
                                                }}
                                            </p>

                                            <time>

                                                {{
                                                    (
                                                        $push->thoi_gian_gui
                                                        ?? $push->created_at
                                                    )->diffForHumans()
                                                }}

                                            </time>

                                        </div>

                                    </a>

                                @endif

                            @endif


                        @empty

                            {{-- KHÔNG CÓ THÔNG BÁO --}}
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


                    {{-- =================================================
                        FOOTER
                    ================================================== --}}
                    <div class="booking-notification-footer">

                        <a
                            href="{{ route('user.notifications.index') }}">

                            Xem tất cả

                            <i class="fa-solid fa-arrow-right"></i>

                        </a>

                    </div>

                </div>

            </div>


            {{-- =====================================================
                USER MENU
            ====================================================== --}}
            <details class="profile-user-menu">

                <summary>

                    <span class="profile-avatar-sm">

                        {{
                            mb_substr(
                                $user->ho_ten ?? 'U',
                                0,
                                1
                            )
                        }}

                    </span>


                    <span>

                        {{ $user->ho_ten ?? 'Tài khoản' }}

                        <small>
                            {{ $roleLabel }}
                        </small>

                    </span>


                    <i class="fa-solid fa-chevron-down"></i>

                </summary>


                {{-- DROPDOWN USER --}}
                <div class="profile-user-dropdown">


                    {{-- TRANG CHỦ --}}
                    <a href="{{ route('home') }}">

                        <i class="fa-solid fa-house"></i>

                        Trang chủ

                    </a>


                    {{-- KHU QUẢN TRỊ --}}
                    @if (
                        $user &&
                        (
                            $user->hasRole('Quản trị viên') ||
                            $user->hasRole('Quản lý hệ thống') ||
                            $user->hasRole('Quản lý') ||
                            $user->hasRole('Quản lý phòng chiếu') ||
                            $user->hasRole('Nhân viên') ||
                            in_array(
                                $user->vai_tro,
                                [
                                    'admin',
                                    'quan_ly_he_thong',
                                    'nhan_vien'
                                ]
                            )
                        )
                    )

                        <a
                            href="{{ route('dashboard') }}"
                            class="is-highlight">

                            <i class="fa-solid fa-gauge-high"></i>

                            Khu quản trị

                        </a>

                    @endif


                    {{-- ĐĂNG XUẤT --}}
                    <form
                        method="POST"
                        action="{{ route('logout') }}">

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


    {{-- =========================================================
        PROFILE CONTENT
    ========================================================== --}}
    <main class="profile-main">

        {{ $slot }}

    </main>

</div>


{{-- =============================================================
    USER HOME JS
============================================================= --}}
<script
    src="{{ asset('assets/js/user-home.js') }}?v={{ filemtime(public_path('assets/js/user-home.js')) }}">
</script>


{{-- =============================================================
    NOTIFICATION JS
============================================================= --}}
<script>

document.addEventListener('DOMContentLoaded', function () {

    const notificationBtn =
        document.getElementById(
            'profileNotificationBtn'
        );

    const notificationDropdown =
        document.getElementById(
            'profileNotificationDropdown'
        );


    if (
        !notificationBtn ||
        !notificationDropdown
    ) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | CLICK CHUÔNG
    |--------------------------------------------------------------------------
    */
    notificationBtn.addEventListener(
        'click',
        function (event) {

            event.preventDefault();

            event.stopPropagation();


            const isOpen =
                notificationDropdown.classList.contains(
                    'show'
                );


            /*
            |--------------------------------------------------------------------------
            | ĐANG MỞ -> ĐÓNG
            |--------------------------------------------------------------------------
            */
            if (isOpen) {

                notificationDropdown.classList.remove(
                    'show'
                );

                notificationBtn.setAttribute(
                    'aria-expanded',
                    'false'
                );

                notificationDropdown.setAttribute(
                    'aria-hidden',
                    'true'
                );

                return;

            }


            /*
            |--------------------------------------------------------------------------
            | ĐANG ĐÓNG -> MỞ
            |--------------------------------------------------------------------------
            */
            notificationDropdown.classList.add(
                'show'
            );

            notificationBtn.setAttribute(
                'aria-expanded',
                'true'
            );

            notificationDropdown.setAttribute(
                'aria-hidden',
                'false'
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | CLICK BÊN TRONG DROPDOWN
    |--------------------------------------------------------------------------
    */
    notificationDropdown.addEventListener(
        'click',
        function (event) {

            event.stopPropagation();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | CLICK RA NGOÀI
    |--------------------------------------------------------------------------
    */
    document.addEventListener(
        'click',
        function (event) {

            if (
                !notificationDropdown.contains(
                    event.target
                ) &&
                !notificationBtn.contains(
                    event.target
                )
            ) {

                notificationDropdown.classList.remove(
                    'show'
                );

                notificationBtn.setAttribute(
                    'aria-expanded',
                    'false'
                );

                notificationDropdown.setAttribute(
                    'aria-hidden',
                    'true'
                );

            }

        }
    );

});

</script>


{{-- =============================================================
    USER DROPDOWN
============================================================= --}}
<script>

document.addEventListener('DOMContentLoaded', function () {

    const userMenu =
        document.querySelector(
            '.profile-user-menu'
        );


    if (!userMenu) {
        return;
    }


    document.addEventListener(
        'click',
        function (event) {

            if (
                !userMenu.contains(
                    event.target
                )
            ) {

                userMenu.removeAttribute(
                    'open'
                );

            }

        }
    );

});

</script>

</body>
</html>