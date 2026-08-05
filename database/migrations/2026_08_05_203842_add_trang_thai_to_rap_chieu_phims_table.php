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
        Schema::table('rap_chieu_phims', function (Blueprint $table) {
            $table->enum('trang_thai', ['hoat_dong', 'bao_tri', 'ngung_hoat_dong'])
                  ->default('hoat_dong')
                  ->after('ten_rap');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rap_chieu_phims', function (Blueprint $table) {
            $table->dropColumn('trang_thai');
        });
    }
};