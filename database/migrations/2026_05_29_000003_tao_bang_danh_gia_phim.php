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
        // ĐÃ SỬA: Tạo bảng danh_gia_phims đồng bộ hệ thống tiếng Việt
        Schema::create('danh_gia_phims', function (Blueprint $table) {
            $table->id();
            
            // Khóa ngoại liên kết chuẩn xác sang bảng phims và nguoi_dungs mới
            $table->foreignId('phim_id')->constrained('phims')->cascadeOnDelete();
            $table->foreignId('nguoi_dung_id')->constrained('nguoi_dungs')->cascadeOnDelete();
            
            $table->integer('diem_danh_gia'); // Thay cho trường 'rating' (Số sao từ 1 đến 5)
            $table->text('noi_dung')->nullable(); // Thay cho trường 'content'
            $table->string('trang_thai')->default('hien_thi'); // Thay cho trường 'status' (VD: hien_thi, an)
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('danh_gia_phims');
    }
};