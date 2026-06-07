<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('suat_chieus', function (Blueprint $table) {
            $table->foreignId('phong_chieu_id')
                ->nullable()
                ->after('rap_chieu_phim_id')
                ->constrained('phong_chieus')
                ->nullOnDelete();
            
            $table->integer('thoi_luong')
                ->nullable()
                ->after('thoi_gian_chieu')
                ->comment('Thời lượng chiếu tính bằng phút');
            
            $table->dateTime('thoi_gian_ket_thuc')
                ->nullable()
                ->after('thoi_luong')
                ->comment('Thời gian kết thúc tự động tính');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suat_chieus', function (Blueprint $table) {
            $table->dropForeign(['phong_chieu_id']);
            $table->dropColumn(['phong_chieu_id', 'thoi_luong', 'thoi_gian_ket_thuc']);
        });
    }
};
