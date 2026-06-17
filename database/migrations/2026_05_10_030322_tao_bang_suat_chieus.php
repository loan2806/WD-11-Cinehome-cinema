<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suat_chieus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phim_id')->constrained('phims')->cascadeOnDelete();
            $table->foreignId('rap_chieu_phim_id')->constrained('rap_chieu_phims')->cascadeOnDelete();
            $table->dateTime('thoi_gian_chieu');
            $table->integer('thoi_luong')->default(90); // Giữ nguyên phục vụ logic mới
            $table->dateTime('thoi_gian_ket_thuc');     // Giữ nguyên phục vụ logic mới
            $table->decimal('gia_ve', 10, 2)->default(0);
            $table->string('trang_thai')->default('sap_chieu'); // Giữ nguyên phục vụ logic mới
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suat_chieus');
    }
};