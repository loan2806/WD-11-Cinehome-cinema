<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gioi_thieu_thanh_viens', function (Blueprint $table) {

            $table->id();

            // người giới thiệu
            $table->foreignId('thanh_vien_id')
                ->constrained('thanh_viens')
                ->cascadeOnDelete();

            // người được giới thiệu
            $table->foreignId('nguoi_duoc_gioi_thieu_id')
                ->constrained('nguoi_dungs')
                ->cascadeOnDelete();


            // phần thưởng nhận được
            $table->integer('diem_thuong')
                ->default(0);


            $table->foreignId('voucher_id')
                ->nullable()
                ->constrained('vouchers')
                ->nullOnDelete();


            $table->string('noi_dung')
                ->nullable();


            $table->timestamps();


            // tránh 1 tài khoản bị nhiều người giới thiệu
            $table->unique('nguoi_duoc_gioi_thieu_id');

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('gioi_thieu_thanh_viens');
    }
};