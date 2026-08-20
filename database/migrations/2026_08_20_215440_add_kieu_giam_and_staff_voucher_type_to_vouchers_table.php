<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('vouchers', 'kieu_giam')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->enum('kieu_giam', ['tien', 'phan_tram'])
                    ->default('tien')
                    ->after('loai_voucher');
            });
        }

        // Bổ sung loại voucher chỉ dành cho nhân viên bán vé tại quầy.
        // Database của project đang dùng MySQL.
        DB::statement(
            "ALTER TABLE vouchers MODIFY loai_voucher ENUM(
                'giam_gia_ve',
                'giam_gia_do_an',
                'giam_gia_ghe_vip',
                'sinh_nhat',
                'khach_hang_than_thiet',
                'staff_dac_biet'
            ) NOT NULL DEFAULT 'giam_gia_ve'"
        );
    }

    public function down(): void
    {
        DB::statement(
            "ALTER TABLE vouchers MODIFY loai_voucher ENUM(
                'giam_gia_ve',
                'giam_gia_do_an',
                'giam_gia_ghe_vip',
                'sinh_nhat',
                'khach_hang_than_thiet'
            ) NOT NULL DEFAULT 'giam_gia_ve'"
        );

        if (Schema::hasColumn('vouchers', 'kieu_giam')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->dropColumn('kieu_giam');
            });
        }
    }
};
