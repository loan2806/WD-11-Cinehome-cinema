<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cai_dat_he_thongs', function (Blueprint $table) {
            $table->id();
            
            // Nhóm rạp phim
            $table->string('ten_rap')->default('CineHome Cinema');
            $table->string('logo')->nullable();
            $table->string('hotline')->default('1900 1234');
            $table->string('email')->default('contact@cinehome.vn');
            $table->string('dia_chi')->default('Số 1 Cầu Diễn, Bắc Từ Liêm, Hà Nội');

            // Nhóm đặt vé
            $table->integer('thoi_gian_giu_ghe')->default(10)->comment('Tính bằng phút');
            $table->integer('so_ve_toi_da_don')->default(8)->comment('Số vé tối đa trên 1 đơn hàng');

            // Nhóm suất chiếu
            $table->integer('thoi_gian_don_phong')->default(15)->comment('Tính bằng phút');
            $table->integer('so_ngay_duoc_dat_ve_truoc')->default(7)->comment('Số ngày');
            $table->integer('so_phut_truoc_chieu_dang_chieu')->default(15)->comment('Số phút trước giờ chiếu để tự động chuyển sang Đang chiếu');
$table->integer('so_ngay_truoc_chieu_sap_ra_mat')->default(30)->comment('Số ngày trước ngày chiếu để coi là phim Sắp ra mắt');

            // Nhóm giá vé gốc
            $table->decimal('gia_ngay_thuong', 15, 2)->default(75000);
            $table->decimal('gia_cuoi_tuan', 15, 2)->default(120000);
            $table->decimal('phu_thu_ghe_vip', 15, 2)->default(20000);

            // Nhóm cấu hình thanh toán công nghệ
            $table->boolean('bat_tat_vnpay')->default(false);
            $table->string('vnpay_tmn_code')->nullable();
            $table->string('vnpay_hash_secret')->nullable();
            
            $table->boolean('bat_tat_momo')->default(false);
            $table->string('momo_partner_code')->nullable();
            $table->string('momo_access_key')->nullable();
            $table->string('momo_secret_key')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cai_dat_he_thongs');
    }
};