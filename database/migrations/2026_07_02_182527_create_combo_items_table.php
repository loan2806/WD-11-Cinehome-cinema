<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('combo_items', function (Blueprint $table) {

            $table->id();

            // Food có category = combo
            $table->foreignId('combo_food_id')
                ->constrained('foods')
                ->cascadeOnDelete();

            // Biến thể của đồ ăn/đồ uống
            $table->foreignId('food_variant_id')
                ->constrained('food_variants')
                ->cascadeOnDelete();

            // Số lượng biến thể trong combo
            $table->unsignedInteger('quantity')->default(1);

            $table->timestamps();

            $table->unique([
                'combo_food_id',
                'food_variant_id'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('combo_items');
    }
};