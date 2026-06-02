<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | THÔNG TIN PHIM
            |--------------------------------------------------------------------------
            */
            $table->string('ten_phim');

            $table->string('slug')->unique();

            $table->text('mo_ta')->nullable();

            /*
            |--------------------------------------------------------------------------
            | MEDIA
            |--------------------------------------------------------------------------
            */
            $table->string('poster')->nullable();

            $table->string('trailer')->nullable();

            /*
            |--------------------------------------------------------------------------
            | THÔNG TIN BỔ SUNG
            |--------------------------------------------------------------------------
            */
            $table->string('dao_dien')->nullable();

            $table->text('dien_vien')->nullable();

            $table->string('ngon_ngu')->nullable();

            $table->integer('thoi_luong')->default(90);

            $table->string('gioi_han_tuoi')->nullable();

            /*
            |--------------------------------------------------------------------------
            | QUỐC GIA
            |--------------------------------------------------------------------------
            */
            $table->foreignId('quoc_gia_id')
                ->nullable()
                ->constrained('countries')
                ->onDelete('set null');

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
        Schema::dropIfExists('movies');
    }
};