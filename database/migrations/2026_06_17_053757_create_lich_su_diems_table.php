<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tạo bảng lịch sử điểm.
     * Bảng này lưu lại toàn bộ giao dịch cộng/trừ điểm của thành viên.
     */
    public function up(): void
    {
        Schema::create('lich_su_diems', function (Blueprint $table) {
            $table->id();

            // Thành viên được cộng/trừ điểm
            $table->foreignId('thanh_vien_id')
                ->constrained('thanh_viens')
                ->cascadeOnDelete();

            // Vé liên quan đến giao dịch điểm, có thể null nếu admin chỉnh tay sau này
            $table->foreignId('ve_xem_phim_id')
                ->nullable()
                ->constrained('ve_xem_phims')
                ->nullOnDelete();

            // Loại giao dịch điểm
            $table->enum('loai_giao_dich', [
                'cong_diem',
                'tru_diem',
                'doi_voucher',
                'dieu_chinh'
            ]);

            // Số điểm thay đổi
            $table->integer('so_diem');

            // Nội dung hiển thị cho khách hàng
            $table->text('noi_dung')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lich_su_diems');
    }
};