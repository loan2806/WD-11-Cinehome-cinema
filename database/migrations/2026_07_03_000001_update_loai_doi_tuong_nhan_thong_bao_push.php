<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Xóa các bản ghi có loại không hợp lệ (nếu có)
        DB::table('thong_bao_pushs')->whereNotIn('loai', ['info', 'success', 'warning', 'promo', 'system'])->delete();

        // Cập nhật enum cho loại thông báo
        DB::statement("ALTER TABLE thong_bao_pushs MODIFY COLUMN loai ENUM('info', 'success', 'warning', 'promo', 'system') DEFAULT 'info'");

        // Xóa các bản ghi có đối tượng nhận không hợp lệ (nếu có)
        DB::table('thong_bao_pushs')->whereNotIn('doi_tuong_nhan', ['all', 'user', 'vip', 'staff', 'admin', 'nguoi_dung_cu_the'])->delete();

        // Cập nhật enum cho đối tượng nhận (giữ nguyên các giá trị cũ để không mất dữ liệu)
        // Thêm các giá trị mới vào enum
        DB::statement("ALTER TABLE thong_bao_pushs MODIFY COLUMN doi_tuong_nhan ENUM('all', 'khach_hang', 'nhan_vien', 'quan_tri_vien', 'nguoi_dung_cu_the', 'user', 'vip', 'staff', 'admin') DEFAULT 'all'");
    }

    public function down(): void
    {
        // Quay lại enum cũ
        DB::statement("ALTER TABLE thong_bao_pushs MODIFY COLUMN loai ENUM('info', 'success', 'warning', 'danger') DEFAULT 'info'");
        DB::statement("ALTER TABLE thong_bao_pushs MODIFY COLUMN doi_tuong_nhan ENUM('all', 'khach_hang', 'nhan_vien', 'quan_tri_vien', 'nguoi_dung_cu_the') DEFAULT 'all'");
    }
};
