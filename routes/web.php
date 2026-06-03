<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FoodInvoiceController;
use App\Http\Controllers\Admin\MovieReviewController as AdminMovieReviewController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\PhimsController as AdminMovieController;
use App\Http\Controllers\Admin\RevenueReportController;
use App\Http\Controllers\Admin\ShowtimeController as AdminShowtimeController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Admin\TheloaisController;
use App\Http\Controllers\Api\CinemaMapApiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Staff\StaffDashboardController;
use App\Http\Controllers\User\BookingController;
use App\Http\Controllers\User\CinemaController;
use App\Http\Controllers\User\CinemaMapController;
use App\Http\Controllers\User\MovieReviewController;
use App\Http\Controllers\User\NotificationController as UserNotificationController;
use App\Http\Controllers\User\PhimsController;
use App\Http\Controllers\User\ShowtimeController;
use App\Http\Controllers\User\TicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| HOME
|--------------------------------------------------------------------------
*/

Route::get('/', [PhimsController::class, 'home'])
    ->name('home');

/*
|--------------------------------------------------------------------------
| phims
|--------------------------------------------------------------------------
*/

Route::get('/phims', [PhimsController::class, 'index'])
    ->name('user.phims.index');

Route::get('/phims/{movie}', [PhimsController::class, 'show'])
    ->name('user.phims.show');

Route::post('/phims/{movie}/reviews', [MovieReviewController::class, 'store'])
    ->middleware('auth')
    ->name('user.phims.reviews.store');

/*
|--------------------------------------------------------------------------
| CINEMAS
|--------------------------------------------------------------------------
*/

Route::get('/cinemas', [CinemaController::class, 'index'])
    ->name('user.cinemas.index');

Route::get('/cinemas/{cinema}', [CinemaController::class, 'show'])
    ->name('user.cinemas.show');

Route::get('/cinemas/map', CinemaMapController::class)
    ->name('user.cinemas.map');

Route::get('/api/cinemas', CinemaMapApiController::class)
    ->name('api.cinemas.index');

/*
|--------------------------------------------------------------------------
| SHOWTIMES
|--------------------------------------------------------------------------
*/

Route::get('/showtime', [ShowtimeController::class, 'index'])
    ->name('user.showtimes.index');

Route::get('/showtime/{showtime}', [ShowtimeController::class, 'show'])
    ->name('user.showtimes.show');

/*
|--------------------------------------------------------------------------
| BOOKING
|--------------------------------------------------------------------------
*/

Route::get('/booking/{movie}', [BookingController::class, 'index'])
    ->name('booking');

Route::get('/showtimes/{movie}/{cinema}', [BookingController::class, 'showtimes'])
    ->name('booking.showtimes');

Route::middleware('auth')
    ->prefix('bookings')
    ->name('user.bookings.')
    ->group(function () {

        Route::get('{showtime}/select-seats', [BookingController::class, 'selectSeats'])
            ->name('selectSeats');

        Route::post('{showtime}/store', [BookingController::class, 'store'])
            ->name('store');
    });

use App\Http\Controllers\DatVe\DatVeController;

/*
|--------------------------------------------------------------------------
| TÍNH NĂNG ĐẶT VÉ (TIẾNG VIỆT)
|--------------------------------------------------------------------------
*/
Route::prefix('dat-ve')->name('dat_ve.')->group(function () {
    Route::get('/chon-rap', [DatVeController::class, 'chonRap'])->name('chon_rap');
    Route::get('/chon-phim/{rap_id}', [DatVeController::class, 'chonPhim'])->name('chon_phim');
    Route::get('/chon-ghe/{suat_chieu_id}', [DatVeController::class, 'chonGhe'])->name('chon_ghe');
});

/*
|--------------------------------------------------------------------------
| USER
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:khach_hang'])
    ->prefix('user')
    ->name('user.')
    ->group(function () {

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
| STAFF
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:nhan_vien'])
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
| ADMIN
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:quan_tri_vien'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('phims', AdminMovieController::class);

        Route::resource('genres', TheloaisController::class);

        Route::resource('showtimes', AdminShowtimeController::class);

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
| PROFILE
|--------------------------------------------------------------------------
*/

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
| AUTH
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';