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
            if (! Schema::hasColumn('food_invoices', 'printed_at')) {
                $table->timestamp('printed_at')->nullable()->after('expires_at');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('food_invoices')) {
            return;
        }

        Schema::table('food_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('food_invoices', 'printed_at')) {
                $table->dropColumn('printed_at');
            }
        });
    }
};
