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
        Schema::create('ghe_ngois', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phong_chieu_id')->constrained('phong_chieus')->onDelete('cascade');
            $table->foreignId('hang_ghe_id')->constrained('hang_ghes')->onDelete('cascade');
            $table->foreignId('loai_ghe_id')->constrained('loai_ghes')->onDelete('restrict');
            $table->string('ma_ghe');
            $table->unsignedTinyInteger('cot'); // Số thứ tự cột (1, 2, 3...)
            $table->enum('trang_thai', ['hoat_dong', 'bao_tri'])->default('hoat_dong');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['phong_chieu_id', 'ma_ghe']);
            $table->index(['phong_chieu_id', 'trang_thai']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ghe_ngois');
    }
};
