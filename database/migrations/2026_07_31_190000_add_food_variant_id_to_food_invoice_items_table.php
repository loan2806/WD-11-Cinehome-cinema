<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('food_invoice_items', function (Blueprint $table) {
            $table->foreignId('food_variant_id')->nullable()->after('food_id')
                ->constrained('food_variants')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('food_invoice_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('food_variant_id');
        });
    }
};
