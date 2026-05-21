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
       Schema::create('showtimes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('movie_id')->constrained()->cascadeOnDelete();
    $table->foreignId('cinema_id')->constrained()->cascadeOnDelete();
    $table->string('room_name')->default('Phòng 1');
    $table->date('show_date');
    $table->time('show_time');
    $table->integer('price')->default(90000);
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('showtimes');
    }
};
