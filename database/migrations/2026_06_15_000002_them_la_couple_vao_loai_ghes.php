<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loai_ghes', function (Blueprint $table) {
            // Đánh dấu loại ghế là ghế đôi (couple). Khi ghế thuộc loại này sẽ tự ghép cặp trên sơ đồ.
            $table->boolean('la_couple')->default(false)->after('mau_sac');
        });

        // Đồng bộ dữ liệu mẫu: loại "Couple" đã có sẵn trong seeder -> đánh dấu couple
        \Illuminate\Support\Facades\DB::table('loai_ghes')
            ->where('ten_loai', 'Couple')
            ->update(['la_couple' => true]);
    }

    public function down(): void
    {
        Schema::table('loai_ghes', function (Blueprint $table) {
            $table->dropColumn('la_couple');
        });
    }
};
