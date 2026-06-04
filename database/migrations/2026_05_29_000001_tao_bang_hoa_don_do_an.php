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
        // ĐÃ SỬA: Tạo bảng hoa_don_do_ans theo đúng tài liệu thiết kế số 25 của bạn
        Schema::create('hoa_don_do_ans', function (Blueprint $table) {
            $table->id();
            
            // ĐÃ SỬA: Đổi khóa ngoại liên kết sang bảng nguoi_dungs thay vì users cũ
            $table->foreignId('nguoi_dung_id')
                  ->constrained('nguoi_dungs')
                  ->cascadeOnDelete();
                  
            $table->decimal('tong_tien', 12, 2)->default(0); // Tổng tiền hóa đơn đồ ăn
            
            // Trạng thái thanh toán: chua_thanh_toan, da_thanh_toan, da_huy
            $table->enum('trang_thai', ['chua_thanh_toan', 'da_thanh_toan', 'da_huy'])
                  ->default('chua_thanh_toan'); 
                  
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hoa_don_do_ans');
    }
};