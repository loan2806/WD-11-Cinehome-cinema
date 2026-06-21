<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bổ sung loại voucher để phân biệt voucher giảm vé,
     * giảm đồ ăn, giảm ghế VIP, sinh nhật hoặc chăm sóc khách hàng.
     */
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->enum('loai_voucher', [
                'giam_gia_ve',
                'giam_gia_do_an',
                'giam_gia_ghe_vip',
                'sinh_nhat',
                'khach_hang_than_thiet',
            ])->default('giam_gia_ve')->after('ten_voucher');
        });
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn('loai_voucher');
        });
    }
};