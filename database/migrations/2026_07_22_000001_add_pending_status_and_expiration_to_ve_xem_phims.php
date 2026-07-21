<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ve_xem_phims', function (Blueprint $table) {
            if (!Schema::hasColumn('ve_xem_phims', 'thoi_gian_het_han')) {
                $table->dateTime('thoi_gian_het_han')->nullable()->after('trang_thai');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `ve_xem_phims` MODIFY `trang_thai` ENUM('da_thanh_toan', 'da_huy', 'da_su_dung', 'het_han', 'cho_thanh_toan') NOT NULL DEFAULT 'da_thanh_toan'");
        } else {
            Schema::table('ve_xem_phims', function (Blueprint $table) {
                $table->enum('trang_thai', ['da_thanh_toan', 'da_huy', 'da_su_dung', 'het_han', 'cho_thanh_toan'])
                    ->default('da_thanh_toan')
                    ->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('ve_xem_phims', function (Blueprint $table) {
            if (Schema::hasColumn('ve_xem_phims', 'thoi_gian_het_han')) {
                $table->dropColumn('thoi_gian_het_han');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `ve_xem_phims` MODIFY `trang_thai` ENUM('da_thanh_toan', 'da_huy', 'da_su_dung', 'het_han') NOT NULL DEFAULT 'da_thanh_toan'");
        } else {
            Schema::table('ve_xem_phims', function (Blueprint $table) {
                $table->enum('trang_thai', ['da_thanh_toan', 'da_huy', 'da_su_dung', 'het_han'])
                    ->default('da_thanh_toan')
                    ->change();
            });
        }
    }
};