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
        Schema::table('cai_dat_he_thongs', function (Blueprint $table) {
            // Chỉ thêm cột nếu dưới database chưa có, tránh tuyệt đối lỗi trùng lặp
            if (!Schema::hasColumn('cai_dat_he_thongs', 'so_phut_truoc_chieu_dang_chieu')) {
                $table->integer('so_phut_truoc_chieu_dang_chieu')->default(15);
            }
            
            if (!Schema::hasColumn('cai_dat_he_thongs', 'so_ngay_truoc_chieu_sap_ra_mat')) {
                $table->integer('so_ngay_truoc_chieu_sap_ra_mat')->default(30);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cai_dat_he_thongs', function (Blueprint $table) {
            if (Schema::hasColumn('cai_dat_he_thongs', 'so_phut_truoc_chieu_dang_chieu')) {
                $table->dropColumn('so_phut_truoc_chieu_dang_chieu');
            }
            
            if (Schema::hasColumn('cai_dat_he_thongs', 'so_ngay_truoc_chieu_sap_ra_mat')) {
                $table->dropColumn('so_ngay_truoc_chieu_sap_ra_mat');
            }
        });
    }
};