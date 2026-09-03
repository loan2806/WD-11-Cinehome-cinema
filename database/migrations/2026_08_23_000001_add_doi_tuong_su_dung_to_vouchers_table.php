<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('vouchers', 'doi_tuong_su_dung')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->enum('doi_tuong_su_dung', [
                    'user',
                    'staff',
                    'all',
                ])
                    ->default('user')
                    ->after('kieu_giam');
            });
        }

        // Voucher cũ staff_dac_biet -> staff
        DB::table('vouchers')
            ->where('loai_voucher', 'staff_dac_biet')
            ->update([
                'doi_tuong_su_dung' => 'staff',
            ]);

        // Các voucher còn lại hiện tại là voucher User
        DB::table('vouchers')
            ->whereNull('doi_tuong_su_dung')
            ->update([
                'doi_tuong_su_dung' => 'user',
            ]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('vouchers', 'doi_tuong_su_dung')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->dropColumn('doi_tuong_su_dung');
            });
        }
    }
};