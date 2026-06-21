<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tạo bảng thẻ thành viên.
     * Mỗi khách hàng sẽ có 1 thẻ thành viên để lưu điểm và hạng.
     */
    public function up(): void
    {
        Schema::create('thanh_viens', function (Blueprint $table) {
            $table->id();

            // Liên kết với bảng người dùng
            $table->foreignId('nguoi_dung_id')
                ->unique()
                ->constrained('nguoi_dungs')
                ->cascadeOnDelete();

            // Mã thẻ thành viên, ví dụ: TV000001
            $table->string('ma_thanh_vien')->unique();

            // Hạng thành viên
            $table->enum('hang_thanh_vien', [
                'member',
                'silver',
                'gold',
                'platinum'
            ])->default('member');

            // Điểm hiện có để khách dùng đổi ưu đãi
            $table->integer('diem_hien_tai')->default(0);

            // Tổng điểm từng tích lũy, dùng để xét hạng thành viên
            $table->integer('tong_diem_tich_luy')->default(0);

            $table->timestamp('ngay_tham_gia')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thanh_viens');
    }
};