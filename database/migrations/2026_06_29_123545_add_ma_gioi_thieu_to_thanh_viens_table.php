<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('thanh_viens', 'ma_gioi_thieu')) {

            Schema::table('thanh_viens', function (Blueprint $table) {

                $table->string('ma_gioi_thieu')
                    ->unique()
                    ->after('ma_thanh_vien');

            });

        }
    }


    public function down(): void
    {
        if (Schema::hasColumn('thanh_viens', 'ma_gioi_thieu')) {

            Schema::table('thanh_viens', function (Blueprint $table) {

                $table->dropColumn('ma_gioi_thieu');

            });

        }
    }
};