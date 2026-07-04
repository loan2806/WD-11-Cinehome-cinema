<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_variants', function (Blueprint $table) {

            $table->id();

            $table->foreignId('food_id')
                ->constrained('foods')
                ->cascadeOnDelete();

            // Ví dụ: S, M, L hoặc 330ml, 500ml
            $table->string('value',50);

            $table->decimal('price',12,2);

            $table->unsignedInteger('stock_quantity')->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['food_id','value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_variants');
    }
};