<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suat_chieus', function (Blueprint $table) {
            $table->string('trang_thai', 20)
                ->default('sap_chieu')
                ->after('thoi_gian_ket_thuc')
                ->comment('Trạng thái: sap_chieu, dang_chieu, da_chieu, huy');
        });
    }

    public function down(): void
    {
        Schema::table('suat_chieus', function (Blueprint $table) {
            $table->dropColumn(['trang_thai']);
        });
    }
};
