<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DanhGiaPhimController as AdminDanhGiaPhimController;
use App\Http\Controllers\Admin\FoodInvoiceController;
use App\Http\Controllers\Admin\GheNgoiController;
use App\Http\Controllers\Admin\HangGheController;
use App\Http\Controllers\Admin\LoaiGheController;
use App\Http\Controllers\Admin\NhanVienController;
use App\Http\Controllers\Admin\NhatKyHoatDongHeThongController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\PhimsController as AdminMovieController;
use App\Http\Controllers\Admin\PhongChieuController;
use App\Http\Controllers\Admin\RevenueReportController;
use App\Http\Controllers\Admin\SuatChieuController as AdminSuatChieuController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Admin\TheloaisController;
use App\Http\Controllers\Api\BandoRapApiController;
use App\Http\Controllers\DatVe\DatVeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Staff\BanVeController;
use App\Http\Controllers\Staff\LichSuVeController;
use App\Http\Controllers\Staff\SoatVeController;
use App\Http\Controllers\Staff\StaffDashboardController;
use App\Http\Controllers\User\BandoRapController;
use App\Http\Controllers\User\BookingController;
use App\Http\Controllers\User\DanhGiaPhimController;
use App\Http\Controllers\User\NotificationController as UserNotificationController;
use App\Http\Controllers\User\PhimsController;
use App\Http\Controllers\User\RapChieuPhimController;
use App\Http\Controllers\User\SuatChieuController as UserSuatChieuController;
use App\Http\Controllers\User\VeXemPhimController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\QuocGiaController;


Route::get('/', [PhimsController::class, 'home'])->name('home');

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->vai_tro === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    if ($user->vai_tro === 'nhan_vien') {
        return redirect()->route('staff.dashboard');
    }

    return redirect()->route('home');
})->name('dashboard');

Route::get('/phims', [PhimsController::class, 'index'])->name('user.phims.index');
Route::get('/phims/{movie}', [PhimsController::class, 'show'])->name('user.phims.show');
Route::get('/movies/{movie}', [PhimsController::class, 'show'])->name('user.movies.show');
Route::post('/phims/{movie}/reviews', [DanhGiaPhimController::class, 'store'])
    ->middleware('auth')
    ->name('user.phims.reviews.store');

Route::get('/cinemas', [RapChieuPhimController::class, 'index'])->name('user.cinemas.index');
Route::get('/cinemas/{rapChieuPhim}', [RapChieuPhimController::class, 'show'])->name('user.cinemas.show');
Route::get('/cinemas/map', BandoRapController::class)->name('user.cinemas.map');
Route::get('/api/cinemas', BandoRapApiController::class)->name('api.cinemas.index');

Route::get('/showtime', [UserSuatChieuController::class, 'index'])->name('user.showtimes.index');
Route::get('/showtime/{suatChieu}', [UserSuatChieuController::class, 'show'])->name('user.showtimes.show');

Route::get('/booking/{movie}', [BookingController::class, 'index'])->name('booking');
Route::get('/showtimes/{movie}/{cinema}', [BookingController::class, 'showtimes'])->name('booking.showtimes');

Route::middleware('auth')
    ->prefix('bookings')
    ->name('user.bookings.')
    ->group(function () {
        Route::get('/', fn () => redirect()->route('user.ve_xem_phim.index'))->name('index');
        Route::get('{showtime}/select-seats', [BookingController::class, 'selectSeats'])->name('selectSeats');
        Route::post('{showtime}/store', [BookingController::class, 'store'])->name('store');
    });


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

Route::middleware(['auth', 'role:khach_hang'])
    ->prefix('user')
    ->name('user.')
    ->group(function () {
        Route::get('/ve-xem-phim', [VeXemPhimController::class, 'index'])->name('ve_xem_phim.index');
        Route::get('/ve-xem-phim/{veXemPhim}', [VeXemPhimController::class, 'show'])->name('ve_xem_phim.show');
        Route::patch('/ve-xem-phim/{veXemPhim}/huy', [VeXemPhimController::class, 'cancel'])->name('ve_xem_phim.cancel');
        Route::get('/notifications', [UserNotificationController::class, 'index'])->name('notifications.index');
    });

Route::middleware(['auth', 'role:nhan_vien'])
    ->prefix('staff')
    ->name('staff.')
    ->group(function () {
        Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');
        Route::get('/soat-ve', [SoatVeController::class, 'index'])->name('soat-ve.index');
        Route::post('/soat-ve/check', [SoatVeController::class, 'check'])->name('soat-ve.check');
        Route::get('/ban-ve', [BanVeController::class, 'index'])->name('ban-ve.index');
        Route::get('/ban-ve/{suatChieu}', [BanVeController::class, 'show'])->name('ban-ve.show');
        Route::post('/ban-ve/{suatChieu}', [BanVeController::class, 'store'])->name('ban-ve.store');
        Route::get('/lich-su-ve', [LichSuVeController::class, 'index'])->name('lich-su-ve.index');
    });

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('phims', AdminMovieController::class);

        Route::resource('quoc-gias', QuocGiaController::class);

        Route::resource('the-loais', TheloaisController::class);
        Route::resource('suat-chieus', AdminSuatChieuController::class);

        Route::resource('phong-chieus', PhongChieuController::class);
        Route::post('phong-chieus/{phong_chieu}/generate-seats', [PhongChieuController::class, 'generateSeats'])
            ->name('phong-chieus.generate-seats');
        Route::post('phong-chieus/{phong_chieu}/toggle-seat-maintenance', [PhongChieuController::class, 'toggleSeatMaintenance'])
            ->name('phong-chieus.toggle-seat-maintenance');
        Route::post('phong-chieus/{phong_chieu}/update-seat-type', [PhongChieuController::class, 'updateSeatType'])
            ->name('phong-chieus.update-seat-type');
        Route::post('phong-chieus/{phong_chieu}/update-row-seats', [PhongChieuController::class, 'updateRowSeats'])
            ->name('phong-chieus.update-row-seats');
        Route::post('phong-chieus/{phong_chieu}/bulk-update-seats', [PhongChieuController::class, 'bulkUpdateSeats'])
            ->name('phong-chieus.bulk-update-seats');
        Route::post('phong-chieus/{phong_chieu}/delete-row-seats', [PhongChieuController::class, 'deleteRowSeats'])
            ->name('phong-chieus.delete-row-seats');

        Route::resource('hang-ghes', HangGheController::class);
        Route::post('hang-ghes/{hang_ghe}/update-row-type', [HangGheController::class, 'updateRowType'])
            ->name('hang-ghes.update-row-type');

        Route::resource('loai-ghes', LoaiGheController::class);

        Route::resource('ghe-ngois', GheNgoiController::class);
        Route::post('ghe-ngois/{ghe_ngoi}/toggle-maintenance', [GheNgoiController::class, 'toggleMaintenance'])
            ->name('ghe-ngois.toggle-maintenance');

        Route::get('/food-invoices', [FoodInvoiceController::class, 'index'])->name('food-invoices.index');
        Route::post('/food-invoices', [FoodInvoiceController::class, 'store'])->name('food-invoices.store');
        Route::delete('/food-invoices/{foodInvoice}', [FoodInvoiceController::class, 'destroy'])->name('food-invoices.destroy');

        Route::resource('notifications', AdminNotificationController::class)->only(['index', 'create', 'store', 'destroy']);

        Route::get('/movie-reviews', [AdminDanhGiaPhimController::class, 'index'])->name('movie-reviews.index');
        Route::post('/movie-reviews', [AdminDanhGiaPhimController::class, 'store'])->name('movie-reviews.store');
        Route::patch('/movie-reviews/{danhGiaPhim}', [AdminDanhGiaPhimController::class, 'update'])->name('movie-reviews.update');
        Route::delete('/movie-reviews/{danhGiaPhim}', [AdminDanhGiaPhimController::class, 'destroy'])->name('movie-reviews.destroy');

        Route::get('/activity-logs', [NhatKyHoatDongHeThongController::class, 'index'])->name('activity-logs.index');
        Route::get('/revenue-reports', [RevenueReportController::class, 'index'])->name('revenue-reports.index');
        Route::get('/system-settings', [SystemSettingController::class, 'index'])->name('system-settings.index');
        Route::patch('/system-settings', [SystemSettingController::class, 'update'])->name('system-settings.update');

        Route::resource('nhanviens', NhanVienController::class);
        Route::patch('nhanviens/{nhanvien}/toggle-status', [NhanVienController::class, 'toggleStatus'])
            ->name('nhanviens.toggle-status');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
