<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('nguoi_dungs', 'vai_tro')) {
            DB::statement("ALTER TABLE nguoi_dungs MODIFY vai_tro ENUM('admin','quan_ly','quan_ly_he_thong','nhan_vien','khach_hang') DEFAULT 'khach_hang'");
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('nguoi_dungs', 'vai_tro')) {
            DB::statement("ALTER TABLE nguoi_dungs MODIFY vai_tro ENUM('admin','nhan_vien','khach_hang') DEFAULT 'khach_hang'");
        }
    }
};
