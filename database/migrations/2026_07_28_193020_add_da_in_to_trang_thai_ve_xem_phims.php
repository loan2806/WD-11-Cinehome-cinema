<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE ve_xem_phims
            MODIFY COLUMN trang_thai
            ENUM(
                'da_thanh_toan',
                'da_in',
                'da_su_dung',
                'da_huy',
                'het_han'
            )
            NOT NULL
            DEFAULT 'da_thanh_toan'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE ve_xem_phims
            MODIFY COLUMN trang_thai
            ENUM(
                'da_thanh_toan',
                'da_su_dung',
                'da_huy',
                'het_han'
            )
            NOT NULL
            DEFAULT 'da_thanh_toan'
        ");
    }
};