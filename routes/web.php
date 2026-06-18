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
use App\Http\Controllers\Admin\QuocGiaController;
use App\Http\Controllers\Admin\RevenueReportController;
use App\Http\Controllers\Admin\SoatVeController as AdminSoatVeController;
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
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\PhanQuyenController;
use App\Http\Controllers\Admin\VeXemPhimController as AdminVeXemPhimController;

/*
|--------------------------------------------------------------------------
| GIAO DIỆN CÔNG CỘNG - FRONTEND WEBSITE
|--------------------------------------------------------------------------
*/

Route::get('/', [PhimsController::class, 'home'])->name('home');

// Điều phối trung tâm dựa trên vai trò tài khoản khi đăng nhập
Route::get('/dashboard', function () {
    $user = Auth::user();

    // 1. Chuyển hướng sang phân hệ Quản lý hệ thống tối cao
    if ($user->hasRole('Quản lý hệ thống') || $user->vai_tro === 'quan_ly_he_thong') {
        return redirect()->route('system.dashboard');
    }

    // 2. Chuyển hướng sang phân hệ Sub-Admin / Quản lý rạp thông thường
    if ($user->hasRole('Quản trị viên') || $user->vai_tro === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    if ($user->hasRole('Quản lý') || $user->hasRole('Nhân viên') || $user->vai_tro === 'nhan_vien') {
        return redirect()->route('admin.dashboard');
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
        Route::get('/', fn() => redirect()->route('user.ve_xem_phim.index'))->name('index');
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

Route::middleware(['auth'])
    ->prefix('user')
    ->name('user.')
    ->group(function () {
        Route::get('/ve-xem-phim', [VeXemPhimController::class, 'index'])->name('ve_xem_phim.index');
        Route::get('/ve-xem-phim/{veXemPhim}', [VeXemPhimController::class, 'show'])->name('ve_xem_phim.show');
        Route::patch('/ve-xem-phim/{veXemPhim}/huy', [VeXemPhimController::class, 'cancel'])->name('ve_xem_phim.cancel');
        Route::get('/notifications', [UserNotificationController::class, 'index'])->name('notifications.index');
    });

/*
|--------------------------------------------------------------------------
| PHÂN HỆ ĐỊNH TUYẾN DÀNH RIÊNG CHO NHÂN VIÊN QUẦY (STAFF PANEL)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])
    ->prefix('staff')
    ->name('staff.')
    ->group(function () {

        Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');

        // Nhóm chức năng 1: Nghiệp vụ kiểm tra & Soát vé QR vào cửa phòng chiếu
        Route::middleware(['permission:soat_ve_vao_cua'])->group(function () {
            Route::get('/soat-ve', [SoatVeController::class, 'index'])->name('soat-ve.index');
            Route::post('/soat-ve/check', [SoatVeController::class, 'check'])->name('soat-ve.check');
        });

        // Nhóm chức năng 2: Nghiệp vụ lập hóa đơn và bán vé trực tiếp cho khách tại quầy rạp
        Route::middleware(['permission:ban_ve_tai_quay'])->group(function () {
            Route::get('/ban-ve', [BanVeController::class, 'index'])->name('ban-ve.index');
            Route::get('/lich-su-ve', [LichSuVeController::class, 'index'])->name('lich-su-ve.index');
            Route::get('/ban-ve', [BanVeController::class, 'index'])->name('ban-ve.index');
            Route::get('/ban-ve/{suatChieu}', [BanVeController::class, 'show'])->name('ban-ve.show');
            Route::post('/ban-ve/{suatChieu}', [BanVeController::class, 'store'])->name('ban-ve.store');
        });
    });

/*
|--------------------------------------------------------------------------
| HỆ THỐNG QUẢN TRỊ ADMIN PANEL (CHÈN MIDDLEWARE KIỂM TRA QUYỀN CHẶT CHẼ)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::middleware(['permission:soat_ve_vao_cua'])->group(function () {
            Route::get('/soat-ve', [AdminSoatVeController::class, 'index'])->name('soat-ve.index');
            Route::post('/soat-ve/check', [AdminSoatVeController::class, 'check'])->name('soat-ve.check');
            Route::post('/soat-ve/confirm', [AdminSoatVeController::class, 'confirm'])->name('soat-ve.confirm');
        });

        // Khóa bảo vệ Module: Quản lý Phim & Lịch Chiếu
        Route::middleware(['permission:quan_ly_phim_suat_chieu'])->group(function () {
            Route::resource('phims', AdminMovieController::class);
            Route::resource('quoc-gias', QuocGiaController::class);
            Route::resource('the-loais', TheloaisController::class);
            Route::resource('suat-chieus', AdminSuatChieuController::class);
        });

        // Khóa bảo vệ Module: Cấu trúc Phòng Chiếu & Sơ đồ Ghế Ngồi
        Route::middleware(['permission:quan_ly_phong_ghe'])->group(function () {
            Route::resource('phong-chieus', PhongChieuController::class);
            // CSRF refresher: dùng GET để Laravel không check CSRF, trả về token mới nhất từ session
            Route::get('csrf-token', function () {
                return response()->json(['csrf_token' => csrf_token()]);
            })->name('admin.csrf-token');
            Route::post('phong-chieus/{phong_chieu}/generate-seats', [PhongChieuController::class, 'generateSeats'])->name('phong-chieus.generate-seats');
            Route::post('phong-chieus/{phong_chieu}/toggle-seat-maintenance', [PhongChieuController::class, 'toggleSeatMaintenance'])->name('phong-chieus.toggle-seat-maintenance');
            Route::post('phong-chieus/{phong_chieu}/update-seat-type', [PhongChieuController::class, 'updateSeatType'])->name('phong-chieus.update-seat-type');
            Route::post('phong-chieus/{phong_chieu}/update-row-seats', [PhongChieuController::class, 'updateRowSeats'])->name('phong-chieus.update-row-seats');
            Route::post('phong-chieus/{phong_chieu}/bulk-update-seats', [PhongChieuController::class, 'bulkUpdateSeats'])->name('phong-chieus.bulk-update-seats');
            Route::post('phong-chieus/{phong_chieu}/delete-row-seats', [PhongChieuController::class, 'deleteRowSeats'])->name('phong-chieus.delete-row-seats');
            Route::post('phong-chieus/{phong_chieu}/toggle-row-maintenance', [PhongChieuController::class, 'toggleRowMaintenance'])->name('phong-chieus.toggle-row-maintenance');
            Route::post('phong-chieus/{phong_chieu}/create-seat', [PhongChieuController::class, 'createSeat'])->name('phong-chieus.create-seat');
            Route::post('phong-chieus/{phong_chieu}/create-row', [PhongChieuController::class, 'createRow'])->name('phong-chieus.create-row');

            Route::resource('hang-ghes', HangGheController::class);
            Route::post('hang-ghes/{hang_ghe}/update-row-type', [HangGheController::class, 'updateRowType'])->name('hang-ghes.update-row-type');
            Route::get('phong-chieus/{phong_chieu}/hang-ghes', [HangGheController::class, 'byPhongChieu'])->name('phong-chieus.hang-ghes.index');
            Route::resource('loai-ghes', LoaiGheController::class);
            Route::resource('ghe-ngois', GheNgoiController::class);
            Route::post('ghe-ngois/{ghe_ngoi}/toggle-maintenance', [GheNgoiController::class, 'toggleMaintenance'])->name('ghe-ngois.toggle-maintenance');
        });

        // Khóa bảo vệ Module: Thao tác Bán Vé Tại Quầy & Quản lý thông tin vé của hệ thống
        Route::middleware(['permission:ban_ve_tai_quay'])->group(function () {
            Route::get('/food-invoices', [FoodInvoiceController::class, 'index'])->name('food-invoices.index');
            Route::post('/food-invoices', [FoodInvoiceController::class, 'store'])->name('food-invoices.store');
            Route::delete('/food-invoices/{foodInvoice}', [FoodInvoiceController::class, 'destroy'])->name('food-invoices.destroy');

            Route::resource('ve-xem-phims', AdminVeXemPhimController::class)->only(['index', 'show', 'edit', 'update'])->names('ve-xem-phims');
            Route::patch('ve-xem-phims/{veXemPhim}/huy', [AdminVeXemPhimController::class, 'huy'])->name('ve-xem-phims.huy');
            Route::patch('ve-xem-phims/{veXemPhim}/su-dung', [AdminVeXemPhimController::class, 'suDung'])->name('ve-xem-phims.su-dung');
            Route::patch('ve-xem-phims/{veXemPhim}/cap-nhat-trang-thai', [AdminVeXemPhimController::class, 'capNhatTrangThai'])->name('ve-xem-phims.cap-nhat-trang-thai');
        });

        // Khóa bảo vệ Module: Danh sách Khách Hàng
        Route::middleware(['permission:quan_ly_khach_hang'])->group(function () {
            // Định tuyến mở rộng tương lai đặt tại đây
        });

        // Khóa bảo vệ Module: Hồ sơ & Chia Ca Làm Nhân Viên
        Route::middleware(['permission:quan_ly_nhan_vien'])->group(function () {
            Route::resource('nhanviens', NhanVienController::class);
            Route::patch('nhanviens/{nhanvien}/toggle-status', [NhanVienController::class, 'toggleStatus'])->name('nhanviens.toggle-status');
        });

        // Khóa bảo vệ Module: Thống Kê Doanh Thu Rạp Phim
        Route::middleware(['permission:thong_ke_doanh_thu'])->group(function () {
            Route::get('/revenue-reports', [RevenueReportController::class, 'index'])->name('revenue-reports.index');
        });

        // Khóa bảo vệ Module: Nhật ký hoạt động (chỉ admin và super admin)
        Route::middleware(['permission:xem_nhat_ky_hoat_dong'])->group(function () {
            Route::get('/activity-logs', [NhatKyHoatDongHeThongController::class, 'index'])->name('activity-logs.index');
        });

        // Khóa bảo vệ Module: Cấu Hinh Hệ Thống Chung
        Route::middleware(['permission:quan_ly_cau_hinh_he_thong'])->group(function () {
            Route::resource('notifications', AdminNotificationController::class)->only(['index', 'create', 'store', 'destroy']);
            Route::post('/notifications/mark-all-read', [AdminNotificationController::class, 'markAllRead'])
                ->name('notifications.markAllRead');
            Route::get('/movie-reviews', [AdminDanhGiaPhimController::class, 'index'])->name('movie-reviews.index');
            Route::post('/movie-reviews', [AdminDanhGiaPhimController::class, 'store'])->name('movie-reviews.store');
            Route::patch('/movie-reviews/{danhGiaPhim}', [AdminDanhGiaPhimController::class, 'update'])->name('movie-reviews.update');
            Route::delete('/movie-reviews/{danhGiaPhim}', [AdminDanhGiaPhimController::class, 'destroy'])->name('movie-reviews.destroy');
            Route::get('/system-settings', [SystemSettingController::class, 'index'])->name('system-settings.index');
            Route::patch('/system-settings', [SystemSettingController::class, 'update'])->name('system-settings.update');
        });

        // Khóa bảo vệ tối cao: Ma trận Phân quyền động của hệ thống CineHome
        Route::middleware(['permission:phan_quyen_he_thong'])->group(function () {
            Route::get('/phan-quyen', [PhanQuyenController::class, 'index'])->name('phan-quyen.index');
            Route::post('/phan-quyen/vai-tro', [PhanQuyenController::class, 'storeRole'])->name('phan-quyen.storeRole');
            Route::put('/phan-quyen/cap-nhat/{id}', [PhanQuyenController::class, 'updateMatrix'])->name('phan-quyen.updateMatrix');
        });
    });

/*
|--------------------------------------------------------------------------
| PHÂN HỆ ĐỊNH TUYẾN RIÊNG BIỆT CHO QUẢN LÝ HỆ THỐNG (SYSTEM PANEL)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])
    ->prefix('system')
    ->name('system.')
    ->group(function () {

        // Trang chủ Dashboard đầu não hệ thống
        Route::get('/dashboard', function () {
            return view('system.dashboard'); // Trả về view độc lập sử dụng layout system.blade.php
        })->name('dashboard');

        // Các đặc quyền hạ tầng kỹ thuật gốc (Độc lập 100%)
        Route::get('/cai-dat-thanh-toan', function () {
            return 'Cấu hình cổng API MoMo, VNPAY';
        })->name('payments');
        Route::get('/sao-luu-du-lieu', function () {
            return 'Quản trị sao lưu cơ sở dữ liệu MySQL hạt nhân';
        })->name('backups');
        Route::get('/giam-sat-loi', function () {
            return 'Nhật ký lỗi hệ thống tập trung';
        })->name('logs');
    });

/*
|--------------------------------------------------------------------------
| QUẢN LÝ THÔNG TIN CÁ NHÂN (PROFILE)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
