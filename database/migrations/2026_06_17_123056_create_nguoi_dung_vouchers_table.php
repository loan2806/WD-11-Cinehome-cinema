<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nguoi_dung_vouchers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('nguoi_dung_id')
                ->constrained('nguoi_dungs')
                ->cascadeOnDelete();

            $table->foreignId('voucher_id')
                ->constrained('vouchers')
                ->cascadeOnDelete();

            // Mã voucher cá nhân
            $table->string('ma_voucher_ca_nhan')->unique();

            // Đã dùng chưa
            $table->boolean('da_su_dung')->default(false);

            // Ngày nhận
            $table->timestamp('ngay_nhan')->nullable();

            // Ngày sử dụng
            $table->timestamp('ngay_su_dung')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nguoi_dung_vouchers');
    }
};