<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('food_invoices') || ! Schema::hasTable('nguoi_dungs')) {
            return;
        }

        DB::statement('ALTER TABLE food_invoices DROP FOREIGN KEY food_invoices_user_id_foreign');

        DB::statement('
            UPDATE food_invoices
            SET user_id = NULL
            WHERE user_id IS NOT NULL
              AND user_id NOT IN (SELECT id FROM nguoi_dungs)
        ');

        DB::statement('
            ALTER TABLE food_invoices
            ADD CONSTRAINT food_invoices_user_id_foreign
            FOREIGN KEY (user_id) REFERENCES nguoi_dungs(id)
            ON DELETE SET NULL
        ');
    }

    public function down(): void
    {
        if (! Schema::hasTable('food_invoices') || ! Schema::hasTable('users')) {
            return;
        }

        DB::statement('ALTER TABLE food_invoices DROP FOREIGN KEY food_invoices_user_id_foreign');

        DB::statement('
            UPDATE food_invoices
            SET user_id = NULL
            WHERE user_id IS NOT NULL
              AND user_id NOT IN (SELECT id FROM users)
        ');

        DB::statement('
            ALTER TABLE food_invoices
            ADD CONSTRAINT food_invoices_user_id_foreign
            FOREIGN KEY (user_id) REFERENCES users(id)
            ON DELETE SET NULL
        ');
    }
};
