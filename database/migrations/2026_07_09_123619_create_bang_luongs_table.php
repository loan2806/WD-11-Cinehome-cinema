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
        Schema::create('bang_luongs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoi_dung_id')->constrained('nguoi_dungs')->onDelete('cascade');
            $table->integer('thang');
            $table->integer('nam');
            $table->decimal('tong_ngay_cong', 5, 2)->default(0.00);
            $table->decimal('tong_gio_lam', 6, 2)->default(0.00);
            $table->decimal('tong_gio_tang_ca', 5, 2)->default(0.00);
            $table->integer('so_lan_di_muon')->default(0);
            $table->integer('so_lan_ve_som')->default(0);
            $table->integer('so_ngay_nghi_phep')->default(0);
            $table->integer('so_ngay_nghi_khong_phep')->default(0);
            $table->decimal('luong_co_ban', 15, 2)->default(0.00);
            $table->decimal('phu_cap', 15, 2)->default(0.00);
            $table->decimal('thuong', 15, 2)->default(0.00);
            $table->decimal('phat', 15, 2)->default(0.00);
            $table->decimal('luong_thuc_nhan', 15, 2)->default(0.00);
            $table->enum('trang_thai', ['chua_thanh_toan', 'da_thanh_toan'])->default('chua_thanh_toan');
            $table->timestamps();

            // Đảm bảo không trùng bảng lương tháng của nhân viên
            $table->unique(['nguoi_dung_id', 'thang', 'nam']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bang_luongs');
    }
};
