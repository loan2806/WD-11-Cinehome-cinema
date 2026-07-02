<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Cập nhật bảng admin_notifications - thêm 'promo', 'system' vào enum loai
        DB::statement("ALTER TABLE admin_notifications MODIFY COLUMN loai ENUM('info', 'success', 'warning', 'danger', 'promo', 'system') DEFAULT 'info'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE admin_notifications MODIFY COLUMN loai ENUM('info', 'success', 'warning', 'danger') DEFAULT 'info'");
    }
};
