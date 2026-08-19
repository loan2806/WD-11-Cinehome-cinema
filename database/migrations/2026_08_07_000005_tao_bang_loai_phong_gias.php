<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng cấu hình PHỤ THU vé theo từng loại phòng chiếu (2D/3D/IMAX/4DX).
     * Phụ thu này cộng thêm vào giá vé ngày thường/cuối tuần (cai_dat_he_thongs),
     * cùng cách tính với phụ thu ghế VIP/Couple ở bảng loai_ghes.
     */
    public function up(): void
    {
        Schema::create('loai_phong_gias', function (Blueprint $table) {
            $table->id();
            $table->string('ma_loai_phong')->unique();
            $table->string('ten_loai');
            $table->decimal('phu_thu', 10, 2)->default(0);
            $table->timestamps();
        });

        // Khởi tạo đúng 4 loại phòng đang có trong PhongChieu::LOAI_PHONG.
        // Giữ nguyên hành vi cũ: IMAX/4DX phụ thu bằng đúng mức "phu_thu_ghe_vip"
        // đang bị dùng tạm trước đây (mặc định 20.000đ), 2D/3D chưa có phụ thu.
        $phuThuMacDinh = (float) (DB::table('cai_dat_he_thongs')->value('phu_thu_ghe_vip') ?? 20000);

        DB::table('loai_phong_gias')->insert([
            ['ma_loai_phong' => '2d', 'ten_loai' => '2D', 'phu_thu' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['ma_loai_phong' => '3d', 'ten_loai' => '3D', 'phu_thu' => 15000, 'created_at' => now(), 'updated_at' => now()],
            ['ma_loai_phong' => 'imax', 'ten_loai' => 'IMAX', 'phu_thu' => $phuThuMacDinh, 'created_at' => now(), 'updated_at' => now()],
            ['ma_loai_phong' => '4dx', 'ten_loai' => '4DX', 'phu_thu' => $phuThuMacDinh, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('loai_phong_gias');
    }
};
