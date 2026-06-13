<?php

return [

    'models' => [

        /*
        |--------------------------------------------------------------------------
        | Model cấu hình cho Quyền hạn
        |--------------------------------------------------------------------------
        | Khi sử dụng các tính năng kiểm tra quyền, ứng dụng sẽ sử dụng Model này.
        */
        'permission' => Spatie\Permission\Models\Permission::class,

        /*
        |--------------------------------------------------------------------------
        | Model cấu hình cho Vai trò
        |--------------------------------------------------------------------------
        | Khi gán vai trò cho người dùng, ứng dụng sẽ sử dụng Model này.
        */
        'role' => Spatie\Permission\Models\Role::class,

    ],

    'table_names' => [

        /*
        |--------------------------------------------------------------------------
        | Tên các bảng dữ liệu bằng Tiếng Việt
        |--------------------------------------------------------------------------
        | Định nghĩa lại tên hiển thị của các bảng phân quyền trong cơ sở dữ liệu.
        */

        'roles' => 'vai_tros', // Bảng lưu danh sách vai trò

        'permissions' => 'quyens', // Bảng lưu danh sách quyền hạn cụ thể

        'model_has_permissions' => 'nguoi_dung_quyens', // Bảng trung gian Người dùng - Quyền

        'model_has_roles' => 'nguoi_dung_vai_tros', // Bảng trung gian Người dùng - Vai trò

        'role_has_permissions' => 'vai_tro_quyens', // Bảng trung gian Vai trò - Quyền

    ],

    'column_names' => [
        /*
        |--------------------------------------------------------------------------
        | Tên các cột khóa ngoại trong bảng trung gian
        |--------------------------------------------------------------------------
        */
        'role_pivot_key' => 'role_id', // Thay cho tên class để tránh lỗi độ dài ký tự
        'permission_pivot_key' => 'permission_id',
        'model_morph_key' => 'model_id',
        'team_foreign_key' => 'team_id',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cấu hình phân quyền theo Nhóm (Teams)
    |--------------------------------------------------------------------------
    | Đặt thành true nếu dự án của bạn cần quản lý quyền theo từng nhóm/phòng ban độc lập.
    */
    'teams' => false,

    /*
    |--------------------------------------------------------------------------
    | Cấu hình lưu trữ bộ nhớ đệm (Cache)
    |--------------------------------------------------------------------------
    | Tối ưu hóa hiệu năng bằng cách lưu cache phân quyền, giảm tải số lượng truy vấn database.
    */
    'cache' => [
        'expiration_time' => \DateInterval::createFromDateString('24 hours'),
        'key' => 'spatie.permission.cache',
        'store' => 'default',
    ],
];