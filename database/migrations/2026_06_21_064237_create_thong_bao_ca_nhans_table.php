<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng thông báo cá nhân của từng người dùng.
     */
    public function up(): void
    {
        Schema::create('thong_bao_ca_nhans', function (Blueprint $table) {

            $table->id();

            // Người nhận thông báo
            $table->foreignId('nguoi_dung_id')
                ->constrained('nguoi_dungs')
                ->cascadeOnDelete();

            // Tiêu đề
            $table->string('tieu_de');

            // Nội dung
            $table->text('noi_dung');

            // Loại thông báo
            $table->enum('loai_thong_bao', [
                'diem',
                'voucher',
                've',
                'hang_thanh_vien',
                'he_thong',
                'tai_khoan',
            ])->default('he_thong');

            // Đường dẫn khi click
            $table->string('duong_dan')->nullable();

            // Đã đọc hay chưa
            $table->boolean('da_doc')->default(false);

            // Thời điểm đọc
            $table->timestamp('doc_luc')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thong_bao_ca_nhans');
    }
};
