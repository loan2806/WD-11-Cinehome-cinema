<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\FoodInvoiceController;
use App\Http\Controllers\Admin\MovieReviewController as AdminMovieReviewController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\RevenueReportController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Api\CinemaMapApiController;
use App\Http\Controllers\Staff\StaffDashboardController;

/* =========================
| USER CONTROLLERS
========================= */
use App\Http\Controllers\User\MovieController;
use App\Http\Controllers\User\CinemaController;
use App\Http\Controllers\User\CinemaMapController;
use App\Http\Controllers\User\ShowtimeController;
use App\Http\Controllers\User\BookingController;
use App\Http\Controllers\User\TicketController;
use App\Http\Controllers\User\MovieReviewController;
use App\Http\Controllers\User\NotificationController as UserNotificationController;

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

Route::post('/movies/{movie}/reviews', [MovieReviewController::class, 'store'])
    ->middleware('auth')
    ->name('user.movies.reviews.store');

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

Route::get('/cinemas/map', CinemaMapController::class)
    ->name('user.cinemas.map');

Route::get('/cinemas/{cinema}', [CinemaController::class, 'show'])
    ->name('user.cinemas.show');

/*
|--------------------------------------------------------------------------
| CINEMA API
|--------------------------------------------------------------------------
*/

Route::get('/api/cinemas', CinemaMapApiController::class)
    ->name('api.cinemas.index');

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

    Route::get('/notifications', [UserNotificationController::class, 'index'])
        ->name('notifications.index');

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

        Route::get('/food-invoices', [FoodInvoiceController::class, 'index'])
            ->name('food-invoices.index');
        Route::post('/food-invoices', [FoodInvoiceController::class, 'store'])
            ->name('food-invoices.store');
        Route::delete('/food-invoices/{foodInvoice}', [FoodInvoiceController::class, 'destroy'])
            ->name('food-invoices.destroy');

        Route::resource('notifications', AdminNotificationController::class)
            ->only(['index', 'create', 'store', 'destroy']);

        Route::get('/movie-reviews', [AdminMovieReviewController::class, 'index'])
            ->name('movie-reviews.index');
        Route::post('/movie-reviews', [AdminMovieReviewController::class, 'store'])
            ->name('movie-reviews.store');
        Route::patch('/movie-reviews/{movieReview}', [AdminMovieReviewController::class, 'update'])
            ->name('movie-reviews.update');
        Route::delete('/movie-reviews/{movieReview}', [AdminMovieReviewController::class, 'destroy'])
            ->name('movie-reviews.destroy');

        Route::get('/activity-logs', [ActivityLogController::class, 'index'])
            ->name('activity-logs.index');

        Route::get('/revenue-reports', [RevenueReportController::class, 'index'])
            ->name('revenue-reports.index');

        Route::get('/system-settings', [SystemSettingController::class, 'index'])
            ->name('system-settings.index');
        Route::patch('/system-settings', [SystemSettingController::class, 'update'])
            ->name('system-settings.update');
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
