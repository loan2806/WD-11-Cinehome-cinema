<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Thêm trạng thái nhận thưởng giới thiệu.
     */
    public function up(): void
    {
        Schema::table('thanh_viens', function (Blueprint $table) {
            $table->boolean('da_nhan_thuong')
                ->default(false)
                ->after('nguoi_gioi_thieu_id');
        });
    }

    /**
     * Hoàn tác migration.
     */
    public function down(): void
    {
        Schema::table('thanh_viens', function (Blueprint $table) {
            $table->dropColumn('da_nhan_thuong');
        });
    }
};