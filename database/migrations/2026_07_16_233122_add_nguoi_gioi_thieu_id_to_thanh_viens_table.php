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
        Schema::table('thanh_viens', function (Blueprint $table) {
            // Thêm cột nguoi_gioi_thieu_id (cho phép rỗng - nullable) ngay sau cột ma_gioi_thieu
            $table->unsignedBigInteger('nguoi_gioi_thieu_id')->nullable()->after('ma_gioi_thieu');

            // Thiết lập liên kết khóa ngoại an toàn đến bảng nguoi_dungs (hoặc users nếu bảng của bạn là users)
            $table->foreign('nguoi_gioi_thieu_id')
                  ->references('id')
                  ->on('nguoi_dungs')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('thanh_viens', function (Blueprint $table) {
            $table->dropForeign(['nguoi_gioi_thieu_id']);
            $table->dropColumn('nguoi_gioi_thieu_id');
        });
    }
};