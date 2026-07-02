<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tin_tuc_tags', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tin_tuc_id');
            $table->unsignedBigInteger('tag_id');
            $table->timestamps();

            $table->foreign('tin_tuc_id')->references('id')->on('tin_tucs')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tin_tuc_tags');
    }
};
