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
        Schema::create('ve_xem_phims', function (Blueprint $table) {
            $table->id();
            // Khóa ngoại liên kết sang bảng nguoi_dungs đã tạo trước đó
            $table->foreignId('nguoi_dung_id')->nullable()->constrained('nguoi_dungs')->nullOnDelete();
            $table->foreignId('nhan_vien_id')->nullable()->constrained('nguoi_dungs')->nullOnDelete();
            $table->string('ma_ve')->unique(); // Thay cho ticket_code
            $table->string('ten_phim'); // Thay cho movie_title
            $table->string('ten_rap')->nullable(); // Thay cho cinema_name
            $table->string('ten_phong')->nullable(); // Thay cho room_name
            $table->string('ma_ghe')->nullable(); // Thay cho seat_code
            $table->dateTime('thoi_gian_chieu')->nullable(); // Thay cho show_time
            $table->decimal('tong_tien', 12, 2)->default(0); // Thay cho total_price
            $table->decimal('tien_hoan', 12, 2)->default(0); // Thay cho refund_amount
            
            // Loại vé: truc_tuyen (online) hoặc tai_quay (offline)
            $table->enum('loai_ve', ['truc_tuyen', 'tai_quay'])->default('truc_tuyen');
            
            // Liên kết tới suất chiếu mà vé này thuộc về.
            // Giúp xác định chính xác vé được đặt cho suất chiếu nào,
            // hỗ trợ kiểm tra ghế đã bán, soát vé và thống kê theo suất chiếu.
            $table->foreignId('suat_chieu_id')->nullable()->constrained('suat_chieus')->nullOnDelete();
            
            // Trạng thái vé: da_thanh_toan (paid), da_huy (cancelled), da_su_dung (used)
            $table->enum('trang_thai', ['da_thanh_toan', 'da_huy', 'da_su_dung'])->default('da_thanh_toan');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ve_xem_phims');
    }
};