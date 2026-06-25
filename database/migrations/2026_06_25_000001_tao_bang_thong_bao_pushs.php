<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng thông báo đẩy chính.
     */
    public function up(): void
    {
        Schema::dropIfExists('thong_bao_push_nguoi_dungs');
        Schema::dropIfExists('thong_bao_pushs');

        Schema::create('thong_bao_pushs', function (Blueprint $table) {
            $table->id();

            // Tiêu đề thông báo
            $table->string('tieu_de');

            // Nội dung thông báo
            $table->text('noi_dung');

            // Loại thông báo: info, success, warning, error
            $table->enum('loai', ['info', 'success', 'warning', 'error'])->default('info');

            // Đối tượng nhận: all, khach_hang, nhan_vien, quan_tri_vien, nguoi_dung_cu_the
            $table->enum('doi_tuong_nhan', ['all', 'khach_hang', 'nhan_vien', 'quan_tri_vien', 'nguoi_dung_cu_the'])->default('all');

            // ID người tạo thông báo
            $table->foreignId('nguoi_tao_id')->constrained('nguoi_dungs')->cascadeOnDelete();

            // Trạng thái: da_gui, chua_gui
            $table->enum('trang_thai', ['da_gui', 'chua_gui'])->default('chua_gui');

            // Thời gian gửi thông báo
            $table->timestamp('thoi_gian_gui')->nullable();

            $table->timestamps();
        });

        // Bảng trung gian cho thông báo gửi đến người dùng cụ thể
        Schema::create('thong_bao_push_nguoi_dungs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thong_bao_push_id')->constrained('thong_bao_pushs')->cascadeOnDelete();
            $table->foreignId('nguoi_dung_id')->constrained('nguoi_dungs')->cascadeOnDelete();
            $table->boolean('da_doc')->default(false);
            $table->timestamp('doc_luc')->nullable();
            $table->timestamps();

            $table->unique(['thong_bao_push_id', 'nguoi_dung_id'], 'tb_push_nd_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thong_bao_push_nguoi_dungs');
        Schema::dropIfExists('thong_bao_pushs');
    }
};
