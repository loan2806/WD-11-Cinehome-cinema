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
        // Chuyển đổi tác vụ sang bảng nguoi_dungs theo tài liệu thiết kế của bạn
        Schema::table('nguoi_dungs', function (Blueprint $table) {
            // Thêm trường vai_tro dạng enum vào sau trường mật khẩu (mat_khau)
            $table->enum('vai_tro', ['khach_hang', 'nhan_vien', 'quan_tri_vien'])
                ->default('khach_hang')
                ->after('mat_khau');

            // Thêm trường trạng thái hoạt động dạng boolean vào sau trường vai_tro
            $table->boolean('trang_thai_hoat_dong')
                ->default(true)
                ->after('vai_tro');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nguoi_dungs', function (Blueprint $table) {
            // Xóa bỏ các cột tiếng Việt khi thực hiện lệnh rollback database
            $table->dropColumn(['vai_tro', 'trang_thai_hoat_dong']);
        });
    }
};