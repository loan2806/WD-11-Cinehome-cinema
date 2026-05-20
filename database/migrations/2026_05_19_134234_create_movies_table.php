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

            $table->string('title');
            $table->string('slug')->unique();

            $table->text('description')->nullable();

            // Ảnh poster dọc
            $table->string('poster')->nullable();

            // Ảnh bìa ngang dùng làm banner
            $table->string('cover_image')->nullable();

            // Trailer YouTube
            $table->string('trailer_url')->nullable();

            $table->string('genre')->nullable();
            $table->string('country')->nullable();

            $table->integer('duration')->default(90); // phút
            $table->string('age_rating')->default('P');

            $table->date('release_date')->nullable();

            // now_showing / coming_soon / stopped
            $table->enum('status', ['now_showing', 'coming_soon', 'stopped'])
                ->default('now_showing');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};