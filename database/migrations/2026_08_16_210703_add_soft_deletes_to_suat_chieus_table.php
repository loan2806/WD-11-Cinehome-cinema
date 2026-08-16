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
        Schema::table('suat_chieus', function (Blueprint $table) {
            $table->softDeletes(); // Tự động thêm cột 'deleted_at' (TIMESTAMP, NULLABLE)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suat_chieus', function (Blueprint $table) {
            $table->dropSoftDeletes(); // Xóa cột 'deleted_at' khi rollback
        });
    }
};