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
        // ĐÃ SỬA: Tạo bảng nguoi_dungs thay vì users
        Schema::create('nguoi_dungs', function (Blueprint $table) {
            $table->id();
            $table->string('ho_ten'); // Đã sửa từ 'name' thành 'ho_ten'
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('mat_khau'); // Đã sửa từ 'password' thành 'mat_khau'
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
            // ĐÃ SỬA: Đổi khóa ngoại liên kết từ user_id sang nguoi_dung_id để đồng bộ
            $table->foreignId('nguoi_dung_id')->nullable()->index();
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