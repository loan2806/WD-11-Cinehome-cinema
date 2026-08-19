<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('nguoi_dung_vouchers')) {
            return;
        }

        DB::statement("ALTER TABLE nguoi_dung_vouchers MODIFY loai_cap_phat ENUM('doi_diem','sinh_nhat','khach_hang_than_thiet','admin_tang','khac') DEFAULT 'doi_diem'");

        if (! Schema::hasColumn('nguoi_dung_vouchers', 'ly_do_khac')) {
            Schema::table('nguoi_dung_vouchers', function ($table) {
                $table->string('ly_do_khac')->nullable()->after('loai_cap_phat');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('nguoi_dung_vouchers')) {
            return;
        }

        if (Schema::hasColumn('nguoi_dung_vouchers', 'ly_do_khac')) {
            Schema::table('nguoi_dung_vouchers', function ($table) {
                $table->dropColumn('ly_do_khac');
            });
        }

        DB::statement("UPDATE nguoi_dung_vouchers SET loai_cap_phat = 'admin_tang' WHERE loai_cap_phat = 'khac'");
        DB::statement("ALTER TABLE nguoi_dung_vouchers MODIFY loai_cap_phat ENUM('doi_diem','sinh_nhat','khach_hang_than_thiet','admin_tang') DEFAULT 'doi_diem'");
    }
};
