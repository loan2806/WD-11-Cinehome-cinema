<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suat_chieus', function (Blueprint $table) {
            // Index giúp tìm kiếm cực nhanh theo Tab và Lịch chiếu
            $table->index(['trang_thai', 'thoi_gian_chieu'], 'idx_suat_chieu_trang_thai_thoi_gian');
            $table->index(['phim_id', 'trang_thai'], 'idx_suat_chieu_phim_trang_thai');
            $table->index(['phong_chieu_id', 'thoi_gian_chieu'], 'idx_suat_chieu_phong_thoi_gian');
        });
    }

    public function down(): void
    {
        Schema::table('suat_chieus', function (Blueprint $table) {
            $table->dropIndex('idx_suat_chieu_trang_thai_thoi_gian');
            $table->dropIndex('idx_suat_chieu_phim_trang_thai');
            $table->dropIndex('idx_suat_chieu_phong_thoi_gian');
        });
    }
};