<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lich_bao_tri_ghe_ngois', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ghe_ngoi_id')->constrained('ghe_ngois')->onDelete('cascade');
            $table->foreignId('phong_chieu_id')->constrained('phong_chieus')->onDelete('cascade');
            $table->foreignId('nguoi_dung_id')->nullable()->constrained('nguoi_dungs')->nullOnDelete();
            $table->dateTime('thoi_gian_bat_dau');
            $table->dateTime('thoi_gian_ket_thuc')->nullable();
            $table->text('ly_do')->nullable();
            $table->string('trang_thai_truoc');
            $table->string('trang_thai_sau');
            $table->enum('trang_thai', ['cho_thuc_hien', 'dang_thuc_hien', 'da_hoan_thanh', 'da_huy'])->default('cho_thuc_hien');
            $table->text('ghi_chu')->nullable();
            $table->timestamps();

            $table->index(['ghe_ngoi_id', 'trang_thai']);
            $table->index(['phong_chieu_id', 'thoi_gian_bat_dau']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lich_bao_tri_ghe_ngois');
    }
};
