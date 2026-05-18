<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Staff - CineHome')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    {{-- Custom CSS --}}
    <link rel="stylesheet" href="{{ asset('assets/css/cinehome.css') }}">

    {{-- Tailwind / Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    @include('components.preloader')

    <div class="dashboard-wrapper">

        <aside class="sidebar">
            <div class="sidebar-logo">
                <img src="{{ asset('assets/images/logo.png') }}" alt="CineHome Logo">
                <div>
                    <h5>CineHome</h5>
                    <small>Staff Panel</small>
                </div>
            </div>

            <div class="sidebar-menu">

                {{-- Dashboard --}}
                <a href="{{ route('staff.dashboard') }}"
                    class="{{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">

                    <i class="fa-solid fa-gauge"></i>
                    Tổng quan
                </a>

                {{-- Scan QR --}}
                <a href="{{ route('staff.tickets.scan') }}"
                    class="{{ request()->routeIs('staff.tickets.scan') ? 'active' : '' }}">

                    <i class="fa-solid fa-qrcode"></i>
                    Soát vé QR
                </a>

                {{-- Sell Ticket --}}
                <a href="#">
                    <i class="fa-solid fa-ticket"></i>
                    Bán vé tại quầy
                </a>

                {{-- Seat --}}
                <a href="#">
                    <i class="fa-solid fa-chair"></i>
                    Chọn ghế
                </a>

                {{-- Payment --}}
                <a href="#">
                    <i class="fa-solid fa-money-bill-wave"></i>
                    Thanh toán
                </a>

                {{-- Ticket Type --}}
                <a href="#">
                    <i class="fa-solid fa-layer-group"></i>
                    Phân loại vé
                </a>

                {{-- History --}}
                <a href="#">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Lịch sử bán vé
                </a>

                {{-- Back Home --}}
                <a href="{{ route('home') }}">
                    <i class="fa-solid fa-house"></i>
                    Về trang User
                </a>

            </div>
        </aside>

        <section class="dashboard-main">
            <header class="dashboard-topbar">
                <div>
                    <h5 class="mb-0 fw-bold">@yield('page-title', 'Trang nhân viên')</h5>
                    <small class="text-muted">Soát vé, bán vé và quản lý vé offline</small>
                </div>

                <div class="dropdown">
                    <button class="btn btn-cine dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-user-tie"></i> Nhân viên
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a href="#" class="dropdown-item">Tài khoản</a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button class="dropdown-item text-danger">Đăng xuất</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </header>

            <main class="dashboard-content">
                @yield('content')
            </main>
        </section>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @yield('scripts')
</body>

</html>