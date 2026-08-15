<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('thong_bao_pushs', function (Blueprint $table) {
            $table->string('hang_thanh_vien')->nullable()->after('doi_tuong_nhan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('thong_bao_pushs', function (Blueprint $table) {
            $table->dropColumn('hang_thanh_vien');
        });
    }
};