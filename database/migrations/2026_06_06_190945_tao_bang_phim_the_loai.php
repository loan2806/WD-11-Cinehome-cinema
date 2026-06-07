<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phim_the_loai', function (Blueprint $table) {
            $table->foreignId('phim_id')->constrained('phims')->onDelete('cascade');
            $table->foreignId('the_loai_id')->constrained('the_loais')->onDelete('cascade');
            $table->primary(['phim_id', 'the_loai_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phim_the_loai');
    }
};
