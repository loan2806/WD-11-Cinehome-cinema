<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('foods', function (Blueprint $table) {

            $table->foreignId('category_id')
                ->nullable()
                ->after('image')
                ->constrained('food_categories')
                ->nullOnDelete();

            $table->dropColumn('category');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('foods')) {
            Schema::table('foods', function (Blueprint $table) {
                if (Schema::hasColumn('foods', 'category_id')) {
                    $table->dropConstrainedForeignId('category_id');
                }

                if (! Schema::hasColumn('foods', 'category')) {
                    $table->string('category', 100)->nullable()->index()->after('image');
                }
            });
        }
    }
};
