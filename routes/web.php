<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CaiDatThanhToanController as AdminCaiDatThanhToanController;
use App\Http\Controllers\Admin\DanhGiaPhimController as AdminDanhGiaPhimController;
use App\Http\Controllers\Admin\FoodController;
use App\Http\Controllers\Admin\FoodInvoiceController;
use App\Http\Controllers\Admin\GheNgoiController;
use App\Http\Controllers\Admin\HangGheController;
use App\Http\Controllers\Admin\LoaiGheController;
use App\Http\Controllers\Admin\NhanVienController;
use App\Http\Controllers\Admin\ChamCongController;
use App\Http\Controllers\Admin\BangLuongController;
use App\Http\Controllers\Admin\NhatKyHoatDongHeThongController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\PhanQuyenController;
use App\Http\Controllers\Admin\PhimsController as AdminMovieController;
use App\Http\Controllers\Admin\PhongChieuController;
use App\Http\Controllers\Admin\QuocGiaController;
use App\Http\Controllers\Admin\RevenueReportController;
use App\Http\Controllers\Admin\SoatVeController as AdminSoatVeController;
use App\Http\Controllers\Admin\SuatChieuController as AdminSuatChieuController;
use App\Http\Controllers\Admin\CaiDatHeThongController;
use App\Http\Controllers\Admin\TheloaisController;
use App\Http\Controllers\Admin\FoodCategoryController;
use App\Http\Controllers\Admin\FoodVariantController;
use App\Http\Controllers\Admin\VeXemPhimController as AdminVeXemPhimController;
use App\Http\Controllers\Admin\VoucherController as AdminVoucherController;
use App\Http\Controllers\Api\BandoRapApiController;
use App\Http\Controllers\DatVe\DatVeController;
use App\Http\Controllers\DongBoDuLieuController;
use App\Http\Controllers\HoSoController;
use App\Http\Controllers\Staff\BanVeController;
use App\Http\Controllers\Staff\LichSuVeController;
use App\Http\Controllers\Staff\SoatVeController;
use App\Http\Controllers\Staff\StaffDashboardController;
use App\Http\Controllers\System\CaiDatThanhToanController;
use App\Http\Controllers\User\BandoRapController;
use App\Http\Controllers\User\BookingController;
use App\Http\Controllers\User\DanhGiaPhimController;
use App\Http\Controllers\User\NotificationController as UserNotificationController;
use App\Http\Controllers\User\PhimsController;
use App\Http\Controllers\User\RapChieuPhimController;
use App\Http\Controllers\User\SuatChieuController as UserSuatChieuController;
use App\Http\Controllers\User\VeXemPhimController;
use App\Services\SaoLuuDuLieuService;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\User\ThanhVienController;
use App\Http\Controllers\User\VoucherController;
use App\Http\Controllers\User\ChamSocKhachHangController;
use App\Http\Controllers\Admin\ThanhVienController as AdminThanhVienController;
use App\Http\Controllers\Admin\KhachHangController;
use App\Http\Controllers\User\NotificationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schedule;
use Kreait\Laravel\Firebase\Facades\Firebase;

/*
|--------------------------------------------------------------------------
| GIAO DIỆN CÔNG CỘNG - FRONTEND WEBSITE
|--------------------------------------------------------------------------
*/

Route::get('/', [PhimsController::class, 'home'])->name('home');

Route::get('/dashboard', function () {
    $user = Auth::user();

    if ($user->hasRole('Quản lý hệ thống') || $user->vai_tro === 'quan_ly_he_thong') {
        return redirect()->route('system.dashboard');
    }

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

Route::get('/tin-tuc', [\App\Http\Controllers\User\TinTucController::class, 'index'])->name('user.tin-tuc.index');
Route::get('/tin-tuc/{slug}', [\App\Http\Controllers\User\TinTucController::class, 'show'])->name('user.tin-tuc.show');
Route::get('/khuyen-mai', [\App\Http\Controllers\User\TinTucController::class, 'voucherIndex'])->name('user.khuyen-mai.index');

Route::get('/cinemas', [RapChieuPhimController::class, 'index'])->name('user.cinemas.index');
Route::get('/cinemas/{rapChieuPhim}', [RapChieuPhimController::class, 'show'])->name('user.cinemas.show');
Route::get('/cinemas/map', BandoRapController::class)->name('user.cinemas.map');
Route::get('/api/cinemas', BandoRapApiController::class)->name('api.cinemas.index');

Route::get('/showtime', [UserSuatChieuController::class, 'index'])->name('user.showtimes.index');
Route::get('/showtime/{suatChieu}', [UserSuatChieuController::class, 'show'])->name('user.showtimes.show');

Route::get('/showtimes/{movie}/{cinema}', [BookingController::class, 'showtimes'])->name('booking.showtimes');

Route::middleware('auth')
    ->prefix('bookings')
    ->name('user.bookings.')
    ->group(function () {
        Route::get('/', fn() => redirect()->route('user.ve_xem_phim.index'))->name('index');
        Route::get('{showtime}/select-seats', [BookingController::class, 'selectSeats'])->name('selectSeats');
        Route::post('{showtime}/store', [BookingController::class, 'store'])->name('store');
    });

Route::post('/voucher/save-tam', [\App\Http\Controllers\User\VoucherController::class, 'saveTam'])
    ->middleware('auth')
    ->name('user.voucher.save-tam');

Route::post('/voucher/xoa-tam', [\App\Http\Controllers\User\VoucherController::class, 'xoaVoucherTam'])
    ->name('user.voucher.xoa-tam');

/*
|--------------------------------------------------------------------------
| ĐẶT VÉ
|--------------------------------------------------------------------------
*/

Route::prefix('dat-ve')->name('dat_ve.')->group(function () {

    Route::get('/chon-phim', [DatVeController::class, 'chonPhim'])
        ->name('chon_phim');

    Route::middleware('auth')->group(function () {

        Route::get('/chon-ghe/{movie}', [DatVeController::class, 'chonGhe'])
            ->name('chon_ghe');
        Route::get('/chon-do-an/{suat_chieu_id}', [DatVeController::class, 'chonDoAn'])
            ->name('chon_do_an');

        Route::get('/checkout/{suat_chieu_id}', [DatVeController::class, 'checkout'])
            ->name('checkout');

        // ĐỊNH TUYẾN XỬ LÝ THANH TOÁN MỚI TÍCH HỢP
        Route::post('/xu-ly-thanh-toan/{movie}', [DatVeController::class, 'xuLyThanhToan'])
            ->name('xu_ly_thanh_toan');
        Route::get('/vnpay-callback', [DatVeController::class, 'vnpayCallback'])
            ->name('vnpay_callback');
        Route::get('/xac-nhan-vietqr/{ve}', [DatVeController::class, 'xacNhanVietQR'])
            ->name('xac_nhan_vietqr');
        Route::get('/thanh-toan-thanh-cong/{ve}', [DatVeController::class, 'thanhToanThanhCong'])
            ->name('thanh_toan_thanh_cong');

        Route::get('/seat-locks/{suat_chieu}', [\App\Http\Controllers\DatVe\SeatLockController::class, 'index'])
            ->name('seat_locks.index');
        Route::post('/seat-locks/{suat_chieu}/{seat}', [\App\Http\Controllers\DatVe\SeatLockController::class, 'reserve'])
            ->name('seat_locks.reserve');
        Route::delete('/seat-locks/{suat_chieu}/{seat}', [\App\Http\Controllers\DatVe\SeatLockController::class, 'release'])
            ->name('seat_locks.release');
        Route::post('/seat-locks/{suat_chieu}/release-all', [\App\Http\Controllers\DatVe\SeatLockController::class, 'releaseAll']);
    });
});

Route::middleware(['auth'])
    ->prefix('user')
    ->name('user.')
    ->group(function () {
        Route::get('/ve-xem-phim', [VeXemPhimController::class, 'index'])->name('ve_xem_phim.index');
        Route::get('/ve-xem-phim/{veXemPhim}', [VeXemPhimController::class, 'show'])->name('ve_xem_phim.show');
        Route::patch('/ve-xem-phim/{veXemPhim}/huy', [VeXemPhimController::class, 'cancel'])->name('ve_xem_phim.cancel');
        Route::get('/notifications', [UserNotificationController::class, 'index'])->name('notifications.index');
        Route::get('/thanh-vien', [ThanhVienController::class, 'index'])->name('thanh-vien.index');

        Route::get('/doi-diem', [VoucherController::class, 'index'])->name('voucher.index');
        Route::post('/doi-diem/{voucher}', [VoucherController::class, 'exchange'])->name('voucher.exchange');
        Route::get('/voucher-cua-toi', [VoucherController::class, 'myVoucher'])->name('voucher.my');
        Route::post('/nhan-voucher-sinh-nhat', [ChamSocKhachHangController::class, 'nhanVoucherSinhNhat'])->name('birthday.voucher.receive');
        Route::get('/user/thong-bao', [NotificationController::class, 'index'])
            ->name('user.thong-bao.index');
    });

/*
|--------------------------------------------------------------------------
| STAFF PANEL
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])
    ->prefix('staff')
    ->name('staff.')
    ->group(function () {
        Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');

        Route::middleware(['permission:soat_ve_vao_cua'])->group(function () {
            Route::get('/soat-ve', [SoatVeController::class, 'index'])->name('soat-ve.index');
            Route::post('/soat-ve/check', [SoatVeController::class, 'check'])->name('soat-ve.check');
        });

        Route::middleware(['permission:ban_ve_tai_quay'])->group(function () {
            Route::get('/ban-ve', [BanVeController::class, 'index'])->name('ban-ve.index');
            Route::get('/lich-su-ve', [LichSuVeController::class, 'index'])->name('lich-su-ve.index');
            Route::get('/ban-ve/{suatChieu}', [BanVeController::class, 'show'])->name('ban-ve.show');
            Route::post('/ban-ve/{suatChieu}', [BanVeController::class, 'store'])->name('ban-ve.store');
        });
    });

/*
|--------------------------------------------------------------------------
| ADMIN PANEL
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/cai-dat-thanh-toan', [AdminCaiDatThanhToanController::class, 'edit'])->name('cai-dat-thanh-toan.edit');
        Route::patch('/cai-dat-thanh-toan', [AdminCaiDatThanhToanController::class, 'update'])->name('cai-dat-thanh-toan.update');

        Route::middleware(['permission:soat_ve_vao_cua'])->group(function () {
            Route::get('/soat-ve', [AdminSoatVeController::class, 'index'])->name('soat-ve.index');
            Route::post('/soat-ve/check', [AdminSoatVeController::class, 'check'])->name('soat-ve.check');
            Route::post('/soat-ve/confirm', [AdminSoatVeController::class, 'confirm'])->name('soat-ve.confirm');
        });

        Route::middleware(['permission:quan_ly_phim_suat_chieu'])->group(function () {
            Route::resource('phims', AdminMovieController::class);
            Route::resource('quoc-gias', QuocGiaController::class);
            Route::resource('the-loais', TheloaisController::class);
            Route::resource('suat-chieus', AdminSuatChieuController::class);
        });

        Route::middleware(['permission:quan_ly_phong_ghe'])->group(function () {
            Route::resource('phong-chieus', PhongChieuController::class);
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
            Route::get('ghe-ngois/bao-tri', [GheNgoiController::class, 'baoTri'])->name('ghe-ngois.baoTri');
            Route::resource('ghe-ngois', GheNgoiController::class);
            Route::post('ghe-ngois/{ghe_ngoi}/toggle-maintenance', [GheNgoiController::class, 'toggleMaintenance'])->name('ghe-ngois.toggle-maintenance');
            Route::post('ghe-ngois/{ghe_ngoi}/check-conflicts', [GheNgoiController::class, 'checkConflicts'])->name('ghe-ngois.check-conflicts');
            Route::post('ghe-ngois/{ghe_ngoi}/schedule-maintenance', [GheNgoiController::class, 'scheduleMaintenance'])->name('ghe-ngois.schedule-maintenance');
            Route::patch('lich-bao-tri-ghe-ngois/{lichBaoTriGheNgoi}/complete', [GheNgoiController::class, 'completeMaintenance'])->name('lich-bao-tri-ghe-ngois.complete');
        });

        Route::middleware(['permission:quan_ly_do_an_combo'])->group(function () {

            Route::prefix('foods')->name('foods.categories.')->group(function () {
                Route::get('categories', [FoodCategoryController::class, 'index'])->name('index');
                Route::get('categories/create', [FoodCategoryController::class, 'create'])->name('create');
                Route::post('categories', [FoodCategoryController::class, 'store'])->name('store');
                Route::get('categories/{category}/edit', [FoodCategoryController::class, 'edit'])->name('edit');
                Route::patch('categories/{category}', [FoodCategoryController::class, 'update'])->name('update');
                Route::delete('categories/{category}', [FoodCategoryController::class, 'destroy'])->name('destroy');
            });

            Route::resource('foods', FoodController::class)
                ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

            Route::patch('foods/{food}/stock', [FoodController::class, 'updateStock'])
                ->name('foods.update-stock');
            Route::patch('foods/{food}/toggle-status', [FoodController::class, 'toggleStatus'])
                ->name('foods.toggle-status');

            Route::get('foods/{food}/variants', [FoodVariantController::class, 'index'])->name('foods.variants.index');
            Route::get('foods/{food}/variants/create', [FoodVariantController::class, 'create'])->name('foods.variants.create');
            Route::post('foods/{food}/variants', [FoodVariantController::class, 'store'])->name('foods.variants.store');
            Route::get('foods/{food}/variants/{variant}/edit', [FoodVariantController::class, 'edit'])->name('foods.variants.edit');
            Route::patch('foods/{food}/variants/{variant}', [FoodVariantController::class, 'update'])->name('foods.variants.update');
            Route::delete('foods/{food}/variants/{variant}', [FoodVariantController::class, 'destroy'])->name('foods.variants.destroy');
        });

        Route::middleware(['permission:quan_ly_khach_hang'])->group(function () {
            Route::resource('vouchers', AdminVoucherController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::patch('vouchers/{voucher}/toggle-status', [AdminVoucherController::class, 'toggleStatus'])->name('vouchers.toggle-status');
            Route::post('vouchers/issue', [AdminVoucherController::class, 'issue'])->name('vouchers.issue');
            Route::delete('vouchers/issued/{nguoiDungVoucher}', [AdminVoucherController::class, 'destroyIssued'])->name('vouchers.issued.destroy');
        });

        Route::middleware(['permission:ban_ve_tai_quay'])->group(function () {
            Route::get('/food-invoices', [FoodInvoiceController::class, 'index'])->name('food-invoices.index');
            Route::post('/food-invoices', [FoodInvoiceController::class, 'store'])->name('food-invoices.store');
            Route::patch('/food-invoices/{foodInvoice}/status', [FoodInvoiceController::class, 'updateStatus'])->name('food-invoices.update-status');
            Route::delete('/food-invoices/{foodInvoice}', [FoodInvoiceController::class, 'destroy'])->name('food-invoices.destroy');

            Route::resource('ve-xem-phims', AdminVeXemPhimController::class)->only(['index', 'show', 'edit', 'update'])->names('ve-xem-phims');
            Route::patch('ve-xem-phims/{veXemPhim}/huy', [AdminVeXemPhimController::class, 'huy'])->name('ve-xem-phims.huy');
            Route::patch('ve-xem-phims/{veXemPhim}/su-dung', [AdminVeXemPhimController::class, 'suDung'])->name('ve-xem-phims.su-dung');
            Route::patch('ve-xem-phims/{veXemPhim}/cap-nhat-trang-thai', [AdminVeXemPhimController::class, 'capNhatTrangThai'])->name('ve-xem-phims.cap-nhat-trang-thai');
        });

        Route::middleware(['permission:quan_ly_khach_hang'])->group(function () {
            // Future Customer Modules
        });

        Route::middleware(['permission:quan_ly_nhan_vien'])->group(function () {
            Route::resource('nhanviens', NhanVienController::class);
            Route::patch('nhanviens/{nhanvien}/toggle-status', [NhanVienController::class, 'toggleStatus'])->name('nhanviens.toggle-status');
            Route::resource('cham-congs', ChamCongController::class)->names('cham-congs');
            
            Route::get('bang-luongs/calculate', [BangLuongController::class, 'showCalculateForm'])->name('bang-luongs.calculate');
            Route::post('bang-luongs', [BangLuongController::class, 'store'])->name('bang-luongs.store');
            Route::get('bang-luongs', [BangLuongController::class, 'index'])->name('bang-luongs.index');
            Route::patch('bang-luongs/{bangLuong}/toggle-payment', [BangLuongController::class, 'togglePaymentStatus'])->name('bang-luongs.toggle-payment');
            Route::delete('bang-luongs/{bangLuong}', [BangLuongController::class, 'destroy'])->name('bang-luongs.destroy');
        });

        Route::middleware(['permission:thong_ke_doanh_thu'])->group(function () {
            Route::get('/revenue-reports', [RevenueReportController::class, 'index'])->name('revenue-reports.index');
        });

        Route::middleware(['permission:xem_nhat_ky_hoat_dong'])->group(function () {
            Route::get('/activity-logs', [NhatKyHoatDongHeThongController::class, 'index'])->name('activity-logs.index');
        });

        Route::middleware(['permission:quan_ly_cau_hinh_he_thong'])->group(function () {
            Route::resource('notifications', AdminNotificationController::class)->only(['index', 'create', 'store', 'destroy']);
            Route::post('/notifications/mark-all-read', [AdminNotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
            Route::resource('thong-bao-push', \App\Http\Controllers\Admin\ThongBaoPushController::class)->names('thong-bao-push');
            Route::get('/thong-bao-push/users-by-role', [\App\Http\Controllers\Admin\ThongBaoPushController::class, 'getUsersByRole'])->name('thong-bao-push.users-by-role');
            Route::get('/movie-reviews', [AdminDanhGiaPhimController::class, 'index'])->name('movie-reviews.index');
            Route::post('/movie-reviews', [AdminDanhGiaPhimController::class, 'store'])->name('movie-reviews.store');
            Route::patch('/movie-reviews/{danhGiaPhim}', [AdminDanhGiaPhimController::class, 'update'])->name('movie-reviews.update');
            Route::delete('/movie-reviews/{danhGiaPhim}', [AdminDanhGiaPhimController::class, 'destroy'])->name('movie-reviews.destroy');

            Route::get('/system-settings', [CaiDatHeThongController::class, 'index'])->name('system-settings.index');
            Route::patch('/system-settings', [CaiDatHeThongController::class, 'update'])->name('system-settings.update');
        });

        Route::middleware(['permission:phan_quyen_he_thong'])->group(function () {
            Route::get('/phan-quyen', [PhanQuyenController::class, 'index'])->name('phan-quyen.index');
            Route::post('/phan-quyen/vai-tro', [PhanQuyenController::class, 'storeRole'])->name('phan-quyen.storeRole');
            Route::put('/phan-quyen/cap-nhat/{id}', [PhanQuyenController::class, 'updateMatrix'])->name('phan-quyen.updateMatrix');
        });

        Route::get('/khach-hang/tao-moi', [KhachHangController::class, 'create'])->name('khach-hang.create');
        Route::post('/khach-hang', [KhachHangController::class, 'store'])->name('khach-hang.store');
        Route::get('/thanh-vien', [AdminThanhVienController::class, 'index'])->name('thanh-vien.index');
        Route::get('/thanh-vien/{thanhVien}', [AdminThanhVienController::class, 'show'])->name('thanh-vien.show');

        Route::get('/khach-hang', [KhachHangController::class, 'index'])->name('khach-hang.index');
        Route::get('/khach-hang/{khachHang}', [KhachHangController::class, 'show'])->name('khach-hang.show');
        Route::patch('/khach-hang/{khachHang}/trang-thai', [KhachHangController::class, 'toggleStatus'])->name('khach-hang.toggle-status');
        Route::get('/khach-hang/{khachHang}/edit', [KhachHangController::class, 'edit'])->name('khach-hang.edit');
        Route::patch('/khach-hang/{khachHang}', [KhachHangController::class, 'update'])->name('khach-hang.update');

        Route::post('/thanh-vien/{thanhVien}/tang-diem', [AdminThanhVienController::class, 'tangDiem'])->name('thanh-vien.tang-diem');
        Route::post('/thanh-vien/{thanhVien}/tru-diem', [AdminThanhVienController::class, 'truDiem'])->name('thanh-vien.tru-diem');

        Route::post('/sao-luu', function () {
            SaoLuuDuLieuService::saoLuu();
            return back()->with('success', 'Đã sao lưu toàn bộ dữ liệu lên Firebase');
        })->name('backup');

        Route::post('/khoi-phuc', function () {
            $ketQua = SaoLuuDuLieuService::dongBo();
            if (!$ketQua) {
                return back()->with('error', 'Không tìm thấy dữ liệu sao lưu trên Firebase');
            }
            return back()->with('success', 'Khôi phục dữ liệu thành công');
        })->name('restore');

        Route::post('/dong-bo-du-lieu', [DongBoDuLieuController::class, 'dongBo'])->name('dong-bo-du-lieu');
    });

Route::get('/test-firebase', function () {
    try {
        $auth = Firebase::auth();
        $users = [];
        foreach ($auth->listUsers() as $user) {
            $users[] = [
                'uid' => $user->uid,
                'email' => $user->email,
            ];
        }
        return response()->json([
            'count' => count($users),
            'users' => $users,
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'class' => get_class($e),
        ]);
    }
});

/*
|--------------------------------------------------------------------------
| SYSTEM PANEL
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])
    ->prefix('system')
    ->name('system.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('system.dashboard');
        })->name('dashboard');

        Route::get('/cai-dat-thanh-toan', [CaiDatThanhToanController::class, 'edit'])->name('payments');
        Route::patch('/cai-dat-thanh-toan', [CaiDatThanhToanController::class, 'update'])->name('payments.update');
        Route::get('/sao-luu-du-lieu', function () {
            return 'Quản trị sao lưu cơ sở dữ liệu MySQL hạt nhân';
        })->name('backups');
        Route::get('/giam-sat-loi', function () {
            return 'Nhật ký lỗi hệ thống tập trung';
        })->name('logs');
    });

Schedule::call(function () {
    SaoLuuDuLieuService::saoLuu();
})->everyMinute();

Route::post('/dong-bo-du-lieu', [DongBoDuLieuController::class, 'dongBo'])->name('dong-bo-du-lieu');

/*
|--------------------------------------------------------------------------
| PROFILE MANAGEMENT
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [HoSoController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [HoSoController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [HoSoController::class, 'destroy'])->name('profile.destroy');

    Route::get('/thanh-vien', [ThanhVienController::class, 'index'])->name('user.thanh-vien.index');
    Route::get('/user/thong-bao', [NotificationController::class, 'index'])->name('user.thong-bao.index');
});

require __DIR__ . '/auth.php';