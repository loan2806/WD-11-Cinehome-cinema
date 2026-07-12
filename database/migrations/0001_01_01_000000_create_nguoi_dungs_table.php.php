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
        // Bảng lưu trữ người dùng thuần Việt đầy đủ các trường thông tin
        Schema::create('nguoi_dungs', function (Blueprint $table) {
            $table->id();
            $table->string('ho_ten'); 
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('mat_khau'); 
            
            // ĐÃ BỔ SUNG: Cột vai trò kiểu ENUM cấu hình chuẩn khớp 100% với TaiKhoanSeeder
            $table->enum('vai_tro', ['admin', 'quan_ly', 'quan_ly_he_thong', 'nhan_vien', 'khach_hang'])->default('khach_hang');
            
            // ĐÃ BỔ SUNG: Cột trạng thái hoạt động tài khoản (mặc định là true - đang hoạt động)
            $table->boolean('trang_thai_hoat_dong')->default(true);
            
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nguoi_dungs');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};