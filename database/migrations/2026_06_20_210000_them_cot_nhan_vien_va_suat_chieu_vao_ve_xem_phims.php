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
        Schema::table('ve_xem_phims', function (Blueprint $table) {
            if (!Schema::hasColumn('ve_xem_phims', 'nhan_vien_id')) {
                $table->foreignId('nhan_vien_id')->nullable()->constrained('nguoi_dungs')->nullOnDelete()->after('nguoi_dung_id');
            }
            if (!Schema::hasColumn('ve_xem_phims', 'suat_chieu_id')) {
                $table->foreignId('suat_chieu_id')->nullable()->constrained('suat_chieus')->nullOnDelete()->after('nhan_vien_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ve_xem_phims', function (Blueprint $table) {
            if (Schema::hasColumn('ve_xem_phims', 'suat_chieu_id')) {
                $table->dropForeign(['suat_chieu_id']);
                $table->dropColumn('suat_chieu_id');
            }
            if (Schema::hasColumn('ve_xem_phims', 'nhan_vien_id')) {
                $table->dropForeign(['nhan_vien_id']);
                $table->dropColumn('nhan_vien_id');
            }
        });
    }
};
