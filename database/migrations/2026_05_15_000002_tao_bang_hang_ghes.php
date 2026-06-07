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
        Schema::create('hang_ghes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phong_chieu_id')->constrained('phong_chieus')->onDelete('cascade');
            $table->string('ten_hang');
            $table->timestamps();

            $table->unique(['phong_chieu_id', 'ten_hang']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hang_ghes');
    }
};
