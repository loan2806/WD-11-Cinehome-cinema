<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ADMIN CONTROLLERS
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GenreController;
use App\Http\Controllers\Admin\MovieController as AdminMovieController;
use App\Http\Controllers\Admin\ShowtimeController as AdminShowtimeController;

/*
|--------------------------------------------------------------------------
| USER CONTROLLERS
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\User\BookingController;
use App\Http\Controllers\User\CinemaController;
use App\Http\Controllers\User\CinemaMapController;
use App\Http\Controllers\User\MovieController;
use App\Http\Controllers\User\ShowtimeController;
use App\Http\Controllers\User\TicketController;

/*
|--------------------------------------------------------------------------
| OTHER CONTROLLERS
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Api\CinemaMapApiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Staff\StaffDashboardController;

/*
|--------------------------------------------------------------------------
| HOME
|--------------------------------------------------------------------------
*/

Route::get('/', [MovieController::class, 'home'])
    ->name('home');

/*
|--------------------------------------------------------------------------
| MOVIES
|--------------------------------------------------------------------------
*/

Route::get('/movies', [MovieController::class, 'index'])
    ->name('user.movies.index');

Route::get('/movies/{movie:slug}', [MovieController::class, 'show'])
    ->name('user.movies.show');

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
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | MOVIES CRUD
        |--------------------------------------------------------------------------
        */

        Route::resource('movies', AdminMovieController::class);

        /*
        |--------------------------------------------------------------------------
        | GENRES CRUD
        |--------------------------------------------------------------------------
        */

        Route::resource('genres', GenreController::class);

        /*
        |--------------------------------------------------------------------------
        | SHOWTIMES CRUD
        |--------------------------------------------------------------------------
        */

        Route::get('/showtimes', [AdminShowtimeController::class, 'index'])
            ->name('showtimes.index');

        Route::get('/showtimes/create', [AdminShowtimeController::class, 'create'])
            ->name('showtimes.create');

        Route::post('/showtimes', [AdminShowtimeController::class, 'store'])
            ->name('showtimes.store');

        Route::get('/showtimes/{id}/edit', [AdminShowtimeController::class, 'edit'])
            ->name('showtimes.edit');

        Route::put('/showtimes/{id}', [AdminShowtimeController::class, 'update'])
            ->name('showtimes.update');

        Route::delete('/showtimes/{id}', [AdminShowtimeController::class, 'destroy'])
            ->name('showtimes.destroy');
    });

/*
|--------------------------------------------------------------------------
| USER ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:user'])
    ->prefix('user')
    ->name('user.')
    ->group(function () {

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
| PROFILE ROUTES
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
| AUTH ROUTES
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';