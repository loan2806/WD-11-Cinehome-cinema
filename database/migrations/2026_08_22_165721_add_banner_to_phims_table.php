<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phims', function (Blueprint $table) {
            // Thêm cột banner khổ ngang (cho phép null để tránh lỗi dữ liệu cũ)
            $table->string('banner')->nullable()->after('poster');
        });
    }

    public function down(): void
    {
        Schema::table('phims', function (Blueprint $table) {
            $table->dropColumn('banner');
        });
    }
};