<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cấu Hình Xác Thực Mặc Định
    |--------------------------------------------------------------------------
    |
    | Tùy chọn này điều khiển cơ chế "guard" xác thực mặc định và các tùy chọn
    | khôi phục mật khẩu cho ứng dụng của bạn. Bạn có thể thay đổi các cài
    | đặt này dựa trên nhu cầu, nhưng nó là khởi đầu tốt cho mọi ứng dụng.
    |
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Khung Bảo Vệ Xác Thực (Guards)
    |--------------------------------------------------------------------------
    |
    | Tiếp theo, bạn có thể định nghĩa mọi guard xác thực cho ứng dụng của mình.
    | Cấu hình mặc định tuyệt vời đã được thiết lập sẵn cho bạn ở đây
    | sử dụng lưu trữ session và nhà cung cấp dữ liệu Eloquent (Eloquent user provider).
    |
    | Tất cả các guard xác thực đều cần một nhà cung cấp người dùng. Điều này xác định
    | cách người dùng được lấy ra từ cơ sở dữ liệu hoặc các cơ chế khác.
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Nhà Cung Cấp Người Dùng (User Providers)
    |--------------------------------------------------------------------------
    |
    | Tất cả các guard xác thực đều có một nhà cung cấp người dùng. Nó định nghĩa
    | cách người dùng thực sự được truy vấn ra khỏi cơ sở dữ liệu của bạn.
    |
    | Hỗ trợ: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\NguoiDung::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Khôi Phục Mật Khẩu (Resetting Passwords)
    |--------------------------------------------------------------------------
    |
    | Bạn có thể chỉ định nhiều cấu hình đặt lại mật khẩu nếu bạn có nhiều hơn
    | một bảng người dùng hoặc model trong ứng dụng và bạn muốn có các thiết lập
    | khôi phục mật khẩu riêng biệt dựa trên từng loại người dùng cụ thể.
    |
    | Thời gian hết hạn là số phút mà mã token đặt lại mật khẩu có hiệu lực.
    | Tính năng bảo mật này giúp token tồn tại ngắn hạn để giảm thiểu rủi ro.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Thời Gian Hết Hạn Xác Nhận Mật Khẩu
    |--------------------------------------------------------------------------
    |
    | Tại đây bạn xác định số giây trước khi màn hình xác nhận mật khẩu hết hạn
    | và người dùng được yêu cầu nhập lại mật khẩu của họ qua màn hình xác nhận.
    | Mặc định thời gian này kéo dài trong khoảng 30 phút (1800 giây).
    |
    */

    'password_timeout' => 10800,

];