<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Xóa các bản ghi có loại 'error' (không hợp lệ với enum mới)
        DB::table('thong_bao_pushs')->where('loai', 'error')->delete();

        // Sau đó mới sửa enum
        DB::statement("ALTER TABLE thong_bao_pushs MODIFY COLUMN loai ENUM('info', 'success', 'warning', 'danger') DEFAULT 'info'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE thong_bao_pushs MODIFY COLUMN loai ENUM('info', 'success', 'warning', 'error') DEFAULT 'info'");
    }
};
