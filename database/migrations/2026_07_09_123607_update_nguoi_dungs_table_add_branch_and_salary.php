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
        Schema::table('nguoi_dungs', function (Blueprint $table) {
            $table->foreignId('rap_chieu_phim_id')
                ->nullable()
                ->after('vai_tro')
                ->constrained('rap_chieu_phims')
                ->nullOnDelete();
            $table->decimal('luong_co_ban', 15, 2)->default(0.00)->after('trang_thai_hoat_dong');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nguoi_dungs', function (Blueprint $table) {
            $table->dropForeign(['rap_chieu_phim_id']);
            $table->dropColumn(['rap_chieu_phim_id', 'luong_co_ban']);
        });
    }
};
