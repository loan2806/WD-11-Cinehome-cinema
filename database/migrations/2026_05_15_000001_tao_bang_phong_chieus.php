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
        Schema::create('phong_chieus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rap_chieu_phim_id')->constrained('rap_chieu_phims')->onDelete('cascade');
            $table->string('ten_phong');
            $table->enum('loai_phong', ['2d', '3d', 'imax', '4dx'])->default('2d');
            $table->unsignedInteger('suc_chua');
            $table->enum('trang_thai', ['hoat_dong', 'bao_tri', 'ngung_hoat_dong'])->default('hoat_dong');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['rap_chieu_phim_id', 'ten_phong']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phong_chieus');
    }
};
