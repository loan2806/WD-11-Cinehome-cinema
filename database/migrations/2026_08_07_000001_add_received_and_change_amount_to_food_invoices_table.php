<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('food_invoices')) {
            return;
        }

        Schema::table('food_invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('food_invoices', 'received_amount')) {
                $table->decimal('received_amount', 12, 2)->nullable()->after('payment_method');
            }

            if (! Schema::hasColumn('food_invoices', 'change_amount')) {
                $table->decimal('change_amount', 12, 2)->nullable()->after('received_amount');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('food_invoices')) {
            return;
        }

        Schema::table('food_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('food_invoices', 'change_amount')) {
                $table->dropColumn('change_amount');
            }

            if (Schema::hasColumn('food_invoices', 'received_amount')) {
                $table->dropColumn('received_amount');
            }
        });
    }
};
