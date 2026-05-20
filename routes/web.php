<?php
 

use App\Http\Controllers\Staff\StaffDashboardController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

/* =========================
| USER CONTROLLERS
========================= */
use App\Http\Controllers\User\MovieController;
use App\Http\Controllers\User\CinemaController;
use App\Http\Controllers\User\ShowtimeController;
use App\Http\Controllers\User\BookingController;

/* =========================
| HOME
========================= */

/*
|--------------------------------------------------------------------------
| Public / User
|--------------------------------------------------------------------------
*/

Route::get('/', [MovieController::class, 'home'])->name('home');

/* =========================
| MOVIES
========================= */
Route::get('/movies', [MovieController::class, 'index'])
    ->name('user.movies.index');

Route::get('/movies/{movie}', [MovieController::class, 'show'])
    ->name('user.movies.show');

/* =========================
| CINEMAS
========================= */
Route::get('/cinemas', [CinemaController::class, 'index'])
    ->name('user.cinemas.index');

Route::get('/cinemas/{cinema}', [CinemaController::class, 'show'])
    ->name('user.cinemas.show');

/* =========================
| SHOWTIMES PAGE
========================= */
Route::get('/showtime', [ShowtimeController::class, 'index'])
    ->name('user.showtimes.index');

Route::get('/showtime/{showtime}', [ShowtimeController::class, 'show'])
    ->name('user.showtimes.show');

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

/* =========================
| USER (AUTH)
========================= */
Route::middleware(['auth', 'role:user'])
    ->prefix('user')
    ->name('user.')
    ->group(function () {

        Route::get('/tickets', function () {
            return 'Trang vé của User';
        })->name('tickets.index');
    });

/* =========================
| STAFF
========================= */
Route::middleware(['auth', 'role:staff'])
    ->prefix('staff')
    ->name('staff.')
    ->group(function () {

        Route::get('/dashboard', function () {
            return view('staff.dashboard');
        })->name('dashboard');
    });

/* =========================
| ADMIN
========================= */
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');
    });
Route::get('/showtime/{showtime}', [ShowtimeController::class, 'show'])
    ->name('user.showtimes.show');
/*
|--------------------------------------------------------------------------
| Staff routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [StaffDashboardController::class, 'index'])
    ->name('dashboard');

    Route::get('/tickets/scan', function () {
        return 'Trang soát vé QR';
    })->name('tickets.scan');
});

/*
|--------------------------------------------------------------------------
| Admin routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    Route::get('/movies', function () {
        return 'Trang quản lý phim';
    })->name('movies.index');
});

/*
|--------------------------------------------------------------------------
| Profile Breeze
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

/* =========================
| AUTH BREEZE
========================= */
require __DIR__.'/auth.php';