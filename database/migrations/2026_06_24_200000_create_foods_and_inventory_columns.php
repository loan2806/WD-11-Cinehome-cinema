<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('foods')) {
            Schema::create('foods', function (Blueprint $table) {
                $table->id();
                $table->string('sku', 50)->nullable()->unique();
                $table->string('name');
                $table->string('image');
                $table->string('category', 100)->nullable()->index();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index(['is_active']);
            });
        } else {
            Schema::table('foods', function (Blueprint $table) {
                if (! Schema::hasColumn('foods', 'sku')) {
                    $table->string('sku', 50)->nullable()->unique()->after('id');
                }

                if (! Schema::hasColumn('foods', 'image')) {
                    $table->string('image')->nullable()->after('name');
                }

                if (! Schema::hasColumn('foods', 'description')) {
                    $table->text('description')->nullable()->after('category');
                }




                if (! Schema::hasColumn('foods', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('description');
                }

                if (! Schema::hasColumn('foods', 'sort_order')) {
                    $table->unsignedInteger('sort_order')->default(0)->after('is_active');
                }
            });
        }

        if (Schema::hasTable('food_invoice_items') && ! Schema::hasColumn('food_invoice_items', 'food_id')) {
            Schema::table('food_invoice_items', function (Blueprint $table) {
                $table->foreignId('food_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('foods')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('food_invoice_items') && Schema::hasColumn('food_invoice_items', 'food_id')) {
            Schema::table('food_invoice_items', function (Blueprint $table) {
                $table->dropConstrainedForeignId('food_id');
            });
        }

        Schema::dropIfExists('foods');
    }
};
