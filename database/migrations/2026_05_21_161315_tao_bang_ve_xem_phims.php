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
            // Khóa ngoại liên kết sang bảng nguoi_dungs
            // Dùng cho khách hàng mua vé online trên website
            $table->foreignId('nguoi_dung_id')->nullable()->constrained('nguoi_dungs')->nullOnDelete();
            // Khóa ngoại liên kết sang bảng nguoi_dungs
            // Dùng cho nhân viên bán vé trực tiếp tại quầy
            $table->foreignId('nhan_vien_id')->nullable()->constrained('nguoi_dungs')->nullOnDelete();
            // Khóa ngoại liên kết sang bảng suat_chieus
            $table->foreignId('suat_chieu_id')->nullable()->constrained('suat_chieus')->nullOnDelete();
            // Mã vé duy nhất
            $table->string('ma_ve')->unique(); // Thay cho ticket_code
            // Tên phim
            $table->string('ten_phim'); // Thay cho movie_title
            // Tên rạp
            $table->string('ten_rap')->nullable(); // Thay cho cinema_name
            // Tên phòng chiếu
            $table->string('ten_phong')->nullable(); // Thay cho room_name
            // Mã ghế hoặc danh sách ghế (VD: A1,A2,A3)
            $table->string('ma_ghe')->nullable(); // Thay cho seat_code
            // Thời gian chiếu của vé
            $table->dateTime('thoi_gian_chieu')->nullable(); // Thay cho show_time
            // Tổng tiền thanh toán
            $table->decimal('tong_tien', 12, 2)->default(0); // Thay cho total_price
            // Tiền hoàn khi hủy vé
            $table->decimal('tien_hoan', 12, 2)->default(0); // Thay cho refund_amount
            // Loại vé:
            // truc_tuyen = khách đặt trên website
            // tai_quay = nhân viên bán trực tiếp tại quầy
            $table->enum('loai_ve', [
                'truc_tuyen',
                'tai_quay'
            ])->default('truc_tuyen');

            // Trạng thái vé:
            // da_thanh_toan = đã thanh toán
            // da_huy = đã hủy
            // da_su_dung = đã soát vé / đã sử dụng
            // het_han = vé đã hết hạn suất chiếu mà chưa dùng
            $table->enum('trang_thai', [
                'da_thanh_toan',
                'da_huy',
                'da_su_dung',
                'het_han' // 🌟 BỔ SUNG TRẠNG THÁI NÀY ĐỂ TRÁNH LỖI TRUNCATED!
            ])->default('da_thanh_toan');

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