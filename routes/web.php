<?php

use App\Http\Controllers\Api\CinemaMapApiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\User\CinemaController;
use App\Http\Controllers\User\CinemaMapController;
use App\Http\Controllers\User\MovieController;
use App\Http\Controllers\User\ShowtimeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public / User
|--------------------------------------------------------------------------
*/

Route::get('/', [MovieController::class, 'home'])->name('home');

Route::get('/movies', [MovieController::class, 'index'])
    ->name('user.movies.index');

/*
|--------------------------------------------------------------------------
| Dashboard redirect sau đăng nhập
|--------------------------------------------------------------------------
*/

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
| User routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:user'])->prefix('user')->name('user.')->group(function () {
    Route::get('/tickets', function () {
        return 'Trang vé của User';
    })->name('tickets.index');
});

Route::get('/movies/{movie:slug}', [MovieController::class, 'show'])
    ->name('user.movies.show');

Route::get('/api/cinemas', CinemaMapApiController::class)->name('api.cinemas.index');

Route::get('/cinemas/map', CinemaMapController::class)->name('user.cinemas.map');

Route::get('/cinemas', [CinemaController::class, 'index'])
    ->name('user.cinemas.index');

Route::get('/cinemas/{cinema}', [CinemaController::class, 'show'])
    ->name('user.cinemas.show');

Route::get('/showtime', [ShowtimeController::class, 'index'])
    ->name('user.showtimes.index');

Route::get('/showtime/{showtime}', [ShowtimeController::class, 'show'])
    ->name('user.showtimes.show');
/*
|--------------------------------------------------------------------------
| Staff routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', function () {
        return view('staff.dashboard');
    })->name('dashboard');

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
| Auth Breeze
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';
