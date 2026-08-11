<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Chuyển từ phụ thu theo LOẠI phòng (2D/3D/IMAX/4DX, bảng loai_phong_gias)
     * sang phụ thu theo TỪNG phòng chiếu cụ thể — để danh sách giá luôn tự
     * đồng bộ với danh sách phòng thật (thêm phòng mới là có ngay dòng để
     * chỉnh giá, không cần thao tác gì thêm).
     */
    public function up(): void
    {
        if (! Schema::hasColumn('phong_chieus', 'phu_thu')) {
            Schema::table('phong_chieus', function (Blueprint $table) {
                $table->decimal('phu_thu', 10, 2)->default(0)->after('loai_phong');
            });
        }

        // Chuyển dữ liệu phụ thu đã cấu hình theo loại phòng (nếu có) làm giá trị
        // khởi tạo cho từng phòng thuộc loại đó, để không mất cấu hình đã lưu.
        if (Schema::hasTable('loai_phong_gias')) {
            $mucPhuThu = DB::table('loai_phong_gias')->pluck('phu_thu', 'ma_loai_phong');

            foreach ($mucPhuThu as $maLoaiPhong => $phuThu) {
                DB::table('phong_chieus')
                    ->where('loai_phong', $maLoaiPhong)
                    ->update(['phu_thu' => $phuThu]);
            }
        }

        Schema::dropIfExists('loai_phong_gias');
    }

    public function down(): void
    {
        if (Schema::hasColumn('phong_chieus', 'phu_thu')) {
            Schema::table('phong_chieus', function (Blueprint $table) {
                $table->dropColumn('phu_thu');
            });
        }
    }
};
