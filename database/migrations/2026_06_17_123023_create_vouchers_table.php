<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();

            // Mã voucher mẫu: VC10K
            $table->string('ma_voucher')->unique();

            // Tên voucher
            $table->string('ten_voucher');

            // Giá trị giảm
            $table->decimal('gia_tri_giam', 12, 2);

            // Số điểm cần đổi
            $table->integer('diem_can_doi');

            // Hạn sử dụng
            $table->date('ngay_het_han');

            // Trạng thái
            $table->boolean('trang_thai')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};