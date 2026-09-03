<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\ResendVerificationEmailController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| GUEST
|--------------------------------------------------------------------------
|
| Các route dành cho người chưa đăng nhập.
|
*/

Route::middleware('guest')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | ĐĂNG KÝ
    |--------------------------------------------------------------------------
    */

    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);


    /*
    |--------------------------------------------------------------------------
    | ĐĂNG NHẬP
    |--------------------------------------------------------------------------
    */

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);


    /*
    |--------------------------------------------------------------------------
    | QUÊN MẬT KHẨU
    |--------------------------------------------------------------------------
    */

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');


    /*
    |--------------------------------------------------------------------------
    | ĐẶT LẠI MẬT KHẨU
    |--------------------------------------------------------------------------
    */

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store']);
});


/*
|--------------------------------------------------------------------------
| XÁC THỰC EMAIL
|--------------------------------------------------------------------------
|
| Link có dạng:
|
| /verify-email/{id}/{hash}?expires=...&signature=...
|
| KHÔNG dùng middleware "signed" ở đây.
|
| Lý do:
| Middleware "signed" sẽ tự trả về:
|
| 403 Invalid signature.
|
| trước khi VerifyEmailController được chạy.
|
| Thay vào đó, VerifyEmailController sẽ tự kiểm tra:
|
| $request->hasValidSignature()
|
| để có thể trả về thông báo dễ hiểu cho người dùng.
|
*/

Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware('throttle:6,1')
    ->name('verification.verify');


/*
|--------------------------------------------------------------------------
| GỬI LẠI EMAIL XÁC THỰC - CHƯA ĐĂNG NHẬP
|--------------------------------------------------------------------------
|
| Dùng cho trường hợp:
| - Người dùng vừa đăng ký
| - Email chưa xác thực
| - Người dùng không đăng nhập được
| - Cần gửi lại email xác thực
|
*/

Route::post(
    'email/resend-verification',
    [ResendVerificationEmailController::class, 'store']
)
    ->middleware('guest')
    ->name('verification.resend-guest');


/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
|
| Các route yêu cầu người dùng đã đăng nhập.
|
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | TRANG YÊU CẦU XÁC THỰC EMAIL
    |--------------------------------------------------------------------------
    */

    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');


    /*
    |--------------------------------------------------------------------------
    | GỬI / GỬI LẠI EMAIL XÁC THỰC
    |--------------------------------------------------------------------------
    |
    | Không dùng throttle:6,1 ở đây.
    |
    | Controller:
    | EmailVerificationNotificationController
    |
    | sẽ tự xử lý giới hạn gửi.
    |
    */

    Route::post(
        'email/verification-notification',
        [EmailVerificationNotificationController::class, 'store']
    )
        ->name('verification.send');


    /*
    |--------------------------------------------------------------------------
    | XÁC NHẬN MẬT KHẨU
    |--------------------------------------------------------------------------
    */

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);


    /*
    |--------------------------------------------------------------------------
    | ĐỔI MẬT KHẨU
    |--------------------------------------------------------------------------
    */

    Route::put('password', [PasswordController::class, 'update'])
        ->name('password.update');


    /*
    |--------------------------------------------------------------------------
    | ĐĂNG XUẤT
    |--------------------------------------------------------------------------
    */

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});