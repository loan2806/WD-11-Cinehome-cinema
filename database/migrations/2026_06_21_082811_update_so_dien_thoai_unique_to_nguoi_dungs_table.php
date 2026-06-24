<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Đảm bảo mỗi số điện thoại chỉ thuộc về 1 tài khoản.
     */
    public function up(): void
    {
        Schema::table('nguoi_dungs', function (Blueprint $table) {
            if (!Schema::hasColumn('nguoi_dungs', 'so_dien_thoai')) {
                $table->string('so_dien_thoai', 20)->nullable()->after('email');
            }

            // Cho phép null, nhưng nếu có số thì không được trùng.
            $table->unique('so_dien_thoai');
        });
    }

    public function down(): void
    {
        Schema::table('nguoi_dungs', function (Blueprint $table) {
            $table->dropUnique(['so_dien_thoai']);
        });
    }
};