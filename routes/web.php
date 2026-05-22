<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

use App\Http\Controllers\Staff\StaffDashboardController;

/* =========================
| USER CONTROLLERS
========================= */
use App\Http\Controllers\User\MovieController;
use App\Http\Controllers\User\CinemaController;
use App\Http\Controllers\User\ShowtimeController;
use App\Http\Controllers\User\BookingController;
use App\Http\Controllers\User\TicketController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
| Các route không cần đăng nhập
| Trang chủ, phim, rạp, lịch chiếu
|--------------------------------------------------------------------------
*/

/* =========================
| HOME
========================= */

Route::get('/', [MovieController::class, 'home'])
    ->name('home');

/*
|--------------------------------------------------------------------------
| MOVIE ROUTES
|--------------------------------------------------------------------------
| Danh sách phim và chi tiết phim
|--------------------------------------------------------------------------
*/

/* =========================
| MOVIES
========================= */
Route::get('/movies', [MovieController::class, 'index'])
    ->name('user.movies.index');

Route::get('/movies/{movie}', [MovieController::class, 'show'])
    ->name('user.movies.show');

/*
|--------------------------------------------------------------------------
| CINEMA ROUTES
|--------------------------------------------------------------------------
| Danh sách rạp và chi tiết rạp
|--------------------------------------------------------------------------
*/

/* =========================
| CINEMAS
========================= */
Route::get('/cinemas', [CinemaController::class, 'index'])
    ->name('user.cinemas.index');

Route::get('/cinemas/{cinema}', [CinemaController::class, 'show'])
    ->name('user.cinemas.show');

/*
|--------------------------------------------------------------------------
| SHOWTIME ROUTES
|--------------------------------------------------------------------------
| Lịch chiếu phim
|--------------------------------------------------------------------------
*/

/* =========================
| SHOWTIMES PAGE
========================= */
Route::get('/showtime', [ShowtimeController::class, 'index'])
    ->name('user.showtimes.index');

Route::get('/showtime/{showtime}', [ShowtimeController::class, 'show'])
    ->name('user.showtimes.show');

/*
|--------------------------------------------------------------------------
| BOOKING ROUTES
|--------------------------------------------------------------------------
| Đặt vé, chọn ghế
| Yêu cầu đăng nhập
|--------------------------------------------------------------------------
*/

/* =========================
| BOOKING FLOW
========================= */
Route::get('/booking/{movie}', [BookingController::class, 'index'])
    ->name('booking');

Route::get('/showtimes/{movie}/{cinema}', [BookingController::class, 'showtimes'])
    ->name('booking.showtimes');

Route::prefix('bookings')
    ->name('user.bookings.')
    ->middleware('auth')
    ->group(function () {

        Route::get('{showtime}/select-seats', [BookingController::class, 'selectSeats'])
            ->name('selectSeats');

        Route::post('{showtime}/store', [BookingController::class, 'store'])
            ->name('store');
    });

/*
|--------------------------------------------------------------------------
| DASHBOARD REDIRECT
|--------------------------------------------------------------------------
| Điều hướng dashboard theo role
| admin  -> admin.dashboard
| staff  -> staff.dashboard
| user   -> home
|--------------------------------------------------------------------------
*/

/* =========================
| DASHBOARD
========================= */
Route::get('/dashboard', function () {

    $user = auth()->user();

    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    if ($user->role === 'staff') {
        return redirect()->route('staff.dashboard');
    }

    return redirect()->route('home');
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| USER ROUTES
|--------------------------------------------------------------------------
| Middleware:
| - auth
| - role:user
|--------------------------------------------------------------------------
*/

/* =========================
| USER (AUTH)
========================= */
Route::middleware(['auth', 'role:user'])->prefix('user')->name('user.')->group(function () {

    Route::get('/tickets', [TicketController::class, 'index'])
        ->name('tickets.index');

    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])
        ->name('tickets.show');

    Route::patch('/tickets/{ticket}/cancel', [TicketController::class, 'cancel'])
        ->name('tickets.cancel');
});

/*
|--------------------------------------------------------------------------
| STAFF ROUTES
|--------------------------------------------------------------------------
| Middleware:
| - auth
| - role:staff
|
| Prefix:
| - /staff
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:staff'])
    ->prefix('staff')
    ->name('staff.')
    ->group(function () {

        Route::get('/dashboard', [StaffDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/tickets/scan', function () {
            return 'Trang soát vé QR';
        })->name('tickets.scan');
    });

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
| Middleware:
| - auth
| - role:admin
|
| Prefix:
| - /admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        Route::get('/movies', function () {
            return 'Trang quản lý phim';
        })->name('movies.index');
    });

/*
|--------------------------------------------------------------------------
| PROFILE ROUTES
|--------------------------------------------------------------------------
| Laravel Breeze profile management
|--------------------------------------------------------------------------
*/

/* =========================
| PROFILE
========================= */
Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
| Laravel Breeze authentication
|--------------------------------------------------------------------------
*/

/* =========================
| AUTH BREEZE
========================= */
require __DIR__ . '/auth.php';
