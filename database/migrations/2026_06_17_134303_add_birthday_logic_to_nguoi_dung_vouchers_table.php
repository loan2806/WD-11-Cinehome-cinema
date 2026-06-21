<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bổ sung thông tin nguồn cấp voucher.
     * Dùng để kiểm soát mỗi khách chỉ nhận 1 voucher sinh nhật trong 1 năm.
     */
    public function up(): void
    {
        Schema::table('nguoi_dung_vouchers', function (Blueprint $table) {
            $table->enum('loai_cap_phat', [
                'doi_diem',
                'sinh_nhat',
                'khach_hang_than_thiet',
                'admin_tang'
            ])->default('doi_diem')->after('ma_voucher_ca_nhan');

            $table->integer('nam_ap_dung')->nullable()->after('loai_cap_phat');

            $table->timestamp('ngay_het_han')->nullable()->after('ngay_nhan');
        });
    }

    public function down(): void
    {
        Schema::table('nguoi_dung_vouchers', function (Blueprint $table) {
            $table->dropColumn([
                'loai_cap_phat',
                'nam_ap_dung',
                'ngay_het_han',
            ]);
        });
    }
};