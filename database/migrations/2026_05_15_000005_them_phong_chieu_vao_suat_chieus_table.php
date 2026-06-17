<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suat_chieus', function (Blueprint $table) {
            // Đợi bảng phong_chieus sinh ra xong xuôi mới thực hiện liên kết khóa ngoại
            if (!Schema::hasColumn('suat_chieus', 'phong_chieu_id')) {
                $table->foreignId('phong_chieu_id')
                      ->after('rap_chieu_phim_id')
                      ->constrained('phong_chieus')
                      ->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('suat_chieus', function (Blueprint $table) {
            $table->dropForeign(['phong_chieu_id']);
            $table->dropColumn('phong_chieu_id');
        });
    }
};