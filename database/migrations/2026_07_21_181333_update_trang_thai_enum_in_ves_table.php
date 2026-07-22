<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Chuyển các dữ liệu cũ 'da_su_dung' (nếu có) sang 'da_in' trước để tránh lỗi
        DB::table('ves')->where('trang_thai', 'da_su_dung')->update(['trang_thai' => 'da_in']);

        // 2. Cập nhật lại danh sách ENUM trong MySQL
        DB::statement("ALTER TABLE ves MODIFY COLUMN trang_thai ENUM('da_thanh_toan', 'da_in', 'da_huy') DEFAULT 'da_thanh_toan'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE ves MODIFY COLUMN trang_thai ENUM('da_thanh_toan', 'da_su_dung', 'da_huy') DEFAULT 'da_thanh_toan'");
    }
};