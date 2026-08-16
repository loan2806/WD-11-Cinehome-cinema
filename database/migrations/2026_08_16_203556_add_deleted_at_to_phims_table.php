<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phims', function (Blueprint $table) {
            $table->softDeletes(); // Tự động thêm cột deleted_at
        });
    }

    public function down(): void
    {
        Schema::table('phims', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};