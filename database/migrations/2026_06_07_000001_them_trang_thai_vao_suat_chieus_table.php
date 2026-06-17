<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suat_chieus', function (Blueprint $table) {
            // Kiểm tra an toàn: Nếu chưa có cột 'trang_thai' thì mới thêm vào để tránh lỗi Duplicate
            if (!Schema::hasColumn('suat_chieus', 'trang_thai')) {
                $table->string('trang_thai')->default('sap_chieu')->after('thoi_gian_ket_thuc');
            }
        });
    }

    public function down(): void
    {
        Schema::table('suat_chieus', function (Blueprint $table) {
            if (Schema::hasColumn('suat_chieus', 'trang_thai')) {
                $table->dropColumn('trang_thai');
            }
        });
    }
};