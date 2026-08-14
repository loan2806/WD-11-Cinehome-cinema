<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE thong_bao_pushs
            MODIFY COLUMN doi_tuong_nhan
            ENUM(
                'all',
                'khach_hang',
                'nhan_vien',
                'quan_ly',
                'hang_thanh_vien',
                'nguoi_dung_cu_the'
            )
            DEFAULT 'all'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE thong_bao_pushs
            MODIFY COLUMN doi_tuong_nhan
            ENUM(
                'all',
                'khach_hang',
                'nhan_vien',
                'quan_tri_vien',
                'nguoi_dung_cu_the'
            )
            DEFAULT 'all'
        ");
    }
};