<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('food_invoices')) {
            return;
        }

        if (! Schema::hasColumn('food_invoices', 'inventory_deducted')) {
            Schema::table('food_invoices', function (Blueprint $table) {
                $table->boolean('inventory_deducted')
                    ->default(false)
                    ->after('payment_status');
            });
        }

        DB::table('food_invoices')
            ->where('payment_status', 'paid')
            ->update(['inventory_deducted' => true]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('food_invoices') || ! Schema::hasColumn('food_invoices', 'inventory_deducted')) {
            return;
        }

        Schema::table('food_invoices', function (Blueprint $table) {
            $table->dropColumn('inventory_deducted');
        });
    }
};
