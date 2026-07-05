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
    <link rel="stylesheet" href="{{ asset('assets/css/user-home.css') }}">

    @stack('styles')

    {{-- Tailwind / Breeze --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    @include('components.preloader')

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
    <script src="{{ asset('assets/js/user-home.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const bookingPages = [
                '/dat_ve/chon_ghe',
                '/dat_ve/chon_do_an',
                '/dat_ve/checkout'
            ];

            document.querySelectorAll('a[href]').forEach(link => {

                link.addEventListener('click', function() {

                    const href = this.getAttribute('href');

                    if (!href) return;

                    // Không reset nếu vẫn ở trong quy trình đặt vé
                    const isBookingPage = bookingPages.some(page => href.includes(page));

                    if (isBookingPage) return;

                    // Reset tất cả timer booking
                    Object.keys(localStorage).forEach(key => {
                        if (key.startsWith('booking_deadline_')) {
                            localStorage.removeItem(key);
                        }
                    });

                });

            });

        });
    </script>
    @yield('scripts')
</body>

</html>
