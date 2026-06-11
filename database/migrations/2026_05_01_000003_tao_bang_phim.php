<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phims', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | THÔNG TIN PHIM
            |--------------------------------------------------------------------------
            */
            $table->string('ten_phim');
            $table->string('slug')->unique();
            $table->text('mo_ta');

            /*
            |--------------------------------------------------------------------------
            | MEDIA
            |--------------------------------------------------------------------------
            */
            $table->string('poster');
            $table->string('trailer');

            /*
            |--------------------------------------------------------------------------
            | THÔNG TIN BỔ SUNG
            |--------------------------------------------------------------------------
            */
            $table->string('dao_dien');
            $table->text('dien_vien');
            $table->string('ngon_ngu');
            $table->integer('thoi_luong')->default(90);
            $table->string('gioi_han_tuoi');

            /*
            |--------------------------------------------------------------------------
            | QUỐC GIA
            |--------------------------------------------------------------------------
            */
            $table->foreignId('quoc_gia_id')
                ->constrained('quoc_gias')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | TIMESTAMPS
            |--------------------------------------------------------------------------
            */
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phims');
    }
};