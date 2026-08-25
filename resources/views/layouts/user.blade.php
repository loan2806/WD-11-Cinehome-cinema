<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'CineHome - Đặt vé xem phim')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    {{-- CSS riêng --}}
    <link rel="stylesheet" href="{{ asset('assets/css/user-home.css') }}?v={{ filemtime(public_path('assets/css/user-home.css')) }}">

    @stack('styles')

    {{-- Tailwind / Breeze --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    @include('components.preloader')

    {{-- Thông báo phiên làm việc/CSRF hết hạn (từ bootstrap/app.php) — dùng
         key riêng "phien_het_han" thay vì "error"/"success" để không trùng
         với các banner thông báo riêng mà nhiều trang trong layout này đã
         tự hiển thị bằng session('success')/session('error'). --}}
    @if (session('phien_het_han'))
    <div style="position: fixed; top: 24px; right: 24px; z-index: 100000; display: flex; width: min(390px, calc(100vw - 32px)); flex-direction: column; gap: 12px; pointer-events: none;">
        <x-toast type="warning" :message="session('phien_het_han')" />
    </div>
    @endif

    @include('layouts.header')

    <main class="page-content">
        @yield('content')
    </main>
    @guest
        @include('components.auth-modal')
    @endguest

    @include('layouts.footer')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- JS riêng --}}
    <script src="{{ asset('assets/js/user-home.js') }}?v={{ filemtime(public_path('assets/js/user-home.js')) }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /* ================= STORAGE ================= */
            function clearBookingData() {
                localStorage.removeItem('food_cart');

                Object.keys(localStorage).forEach(key => {
                    if (key.startsWith('booking_deadline_')) {
                        localStorage.removeItem(key);
                    }
                });
            }

            function isBookingPage() {
                return window.location.pathname.includes('/dat-ve');
            }

            function isHomePage() {
                return window.location.pathname === '/';
            }

            /* ================= RESET RULES ================= */

            // 1. Click về HOME => xoá toàn bộ booking
            document.querySelectorAll('a[href="/"], a[href="{{ url('/') }}"]').forEach(link => {
                link.addEventListener('click', function() {
                    clearBookingData();
                });
            });

            // 2. Click menu ngoài booking => xoá
            document.querySelectorAll('header a[href]').forEach(link => {
                link.addEventListener('click', function() {
                    const href = this.getAttribute('href');

                    if (href === '/' || href === '/home') {
                        clearBookingData();
                    }
                });
            });

            // 3. Click nút đặt vé mới => reset session cũ
            document.querySelectorAll('.booking-link').forEach(link => {
                link.addEventListener('click', clearBookingData);
            });

            // 4. Nếu đang ở HOME thì auto clear luôn
            if (isHomePage()) {
                clearBookingData();
            }

            // NOTE: Bỏ xử lý beforeunload để không xóa bộ đếm thanh toán khi người dùng
            // rời khỏi checkout sang trang khác và quay lại thanh toán tiếp.

        });
    </script>
    @yield('scripts')
</body>

</html>
