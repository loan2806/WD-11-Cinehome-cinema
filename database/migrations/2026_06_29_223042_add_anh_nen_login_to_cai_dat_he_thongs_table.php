<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cai_dat_he_thongs', function (Blueprint $table) {
            if (!Schema::hasColumn('cai_dat_he_thongs', 'anh_nen_login')) {
                $table->string('anh_nen_login')->nullable()->after('logo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cai_dat_he_thongs', function (Blueprint $table) {
            if (Schema::hasColumn('cai_dat_he_thongs', 'anh_nen_login')) {
                $table->dropColumn('anh_nen_login');
            }
        });
    }
};