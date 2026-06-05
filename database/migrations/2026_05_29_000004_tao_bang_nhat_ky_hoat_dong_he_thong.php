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
        // ĐÃ SỬA: Tạo tên bảng thành nhat_ky_hoat_dong_he_thongs chuẩn Việt
        Schema::create('nhat_ky_hoat_dong_he_thongs', function (Blueprint $table) {
            $table->id();
            
            // ĐÃ SỬA: Chỉ định rõ liên kết khóa ngoại tới bảng nguoi_dungs
            $table->foreignId('nguoi_dung_id')->nullable()->constrained('nguoi_dungs')->nullOnDelete();
            
            $table->string('hanh_dong'); // Thay cho 'action'
            $table->string('chuc_nang')->nullable(); // Thay cho 'module'
            $table->text('mo_ta')->nullable(); // Thay cho 'description'
            $table->string('dia_chi_ip')->nullable(); // Thay cho 'ip_address'
            $table->string('user_agent')->nullable();
            
            // ĐÃ BỔ SUNG: Cột lưu trữ mảng dữ liệu thuộc tính mà migration cũ bị thiếu
            $table->json('thuoc_tinh')->nullable(); // Thay cho 'properties'
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nhat_ky_hoat_dong_he_thongs');
    }
};