<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loai_ghes', function (Blueprint $table) {
            $table->string('mau_sac')->default('#666666')->after('phu_thu');
        });
    }

    public function down(): void
    {
        Schema::table('loai_ghes', function (Blueprint $table) {
            $table->dropColumn('mau_sac');
        });
    }
};
