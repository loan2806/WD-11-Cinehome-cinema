<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('thanh_viens', function (Blueprint $table) {

            if (Schema::hasColumn('thanh_viens', 'da_nhan_thuong')) {

                $table->dropColumn('da_nhan_thuong');

            }

        });
    }


    public function down(): void
    {
        Schema::table('thanh_viens', function (Blueprint $table) {

            if (!Schema::hasColumn('thanh_viens', 'da_nhan_thuong')) {

                $table->boolean('da_nhan_thuong')
                    ->default(false)
                    ->after('nguoi_gioi_thieu_id');

            }

        });
    }
};