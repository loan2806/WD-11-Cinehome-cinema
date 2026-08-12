<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Trước đây "gia_ve" của suất chiếu = giá ngày thường/cuối tuần + phụ thu
     * phòng ĐƯỢC CHỐT CỨNG tại thời điểm tạo/sửa suất. Vì vậy khi Quản trị
     * viên đổi phụ thu của phòng sau đó, các suất chiếu đã tạo không tự cập
     * nhật theo (phải bấm "Dùng giá tự động" thủ công cho từng suất).
     *
     * Thêm cờ "gia_ve_tu_dong": nếu true, "gia_ve" chỉ còn là GIÁ GỐC
     * (ngày thường/cuối tuần), còn phụ thu phòng sẽ được cộng SỐNG mỗi khi
     * hiển thị/đặt vé (xem SuatChieu::getGiaVeCuoiCungAttribute). Nếu false
     * (suất đã bị Quản trị viên ghi đè giá tùy chỉnh), "gia_ve" giữ nguyên là
     * giá cuối cùng, không cộng thêm phụ thu phòng nữa.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('suat_chieus', 'gia_ve_tu_dong')) {
            Schema::table('suat_chieus', function (Blueprint $table) {
                $table->boolean('gia_ve_tu_dong')->default(true)->after('gia_ve');
            });
        }

        $settings = DB::table('cai_dat_he_thongs')->first();
        $giaNgayThuong = $settings->gia_ngay_thuong ?? 75000;
        $giaCuoiTuan = $settings->gia_cuoi_tuan ?? 120000;

        $phongPhuThu = DB::table('phong_chieus')->pluck('phu_thu', 'id');

        $suatChieus = DB::table('suat_chieus')->select('id', 'gia_ve', 'phong_chieu_id', 'thoi_gian_chieu')->get();

        foreach ($suatChieus as $suat) {
            $phuThuPhong = (float) ($phongPhuThu[$suat->phong_chieu_id] ?? 0);
            $ngayChieu = Carbon::parse($suat->thoi_gian_chieu);
            $giaGoc = in_array($ngayChieu->dayOfWeek, [Carbon::FRIDAY, Carbon::SATURDAY, Carbon::SUNDAY])
                ? (float) $giaCuoiTuan
                : (float) $giaNgayThuong;

            $giaTuDongDuKien = $giaGoc + $phuThuPhong;

            // Chỉ coi là "tự động" nếu giá đang lưu khớp đúng công thức hiện
            // tại — nếu khác (ví dụ Quản trị viên đã ghi đè tay), giữ nguyên
            // giá đó và đánh dấu là giá thủ công để không tự ý thay đổi.
            if (abs((float) $suat->gia_ve - $giaTuDongDuKien) < 0.01) {
                DB::table('suat_chieus')->where('id', $suat->id)->update([
                    'gia_ve' => $giaGoc,
                    'gia_ve_tu_dong' => true,
                ]);
            } else {
                DB::table('suat_chieus')->where('id', $suat->id)->update([
                    'gia_ve_tu_dong' => false,
                ]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('suat_chieus', 'gia_ve_tu_dong')) {
            // Gộp phụ thu phòng trở lại vào gia_ve cho các suất đang ở chế độ
            // tự động, để khôi phục đúng ý nghĩa "gia_ve = giá cuối cùng" như
            // trước khi có cột này.
            $phongPhuThu = DB::table('phong_chieus')->pluck('phu_thu', 'id');
            $suatChieus = DB::table('suat_chieus')->where('gia_ve_tu_dong', true)->select('id', 'gia_ve', 'phong_chieu_id')->get();

            foreach ($suatChieus as $suat) {
                $phuThuPhong = (float) ($phongPhuThu[$suat->phong_chieu_id] ?? 0);
                DB::table('suat_chieus')->where('id', $suat->id)->update([
                    'gia_ve' => (float) $suat->gia_ve + $phuThuPhong,
                ]);
            }

            Schema::table('suat_chieus', function (Blueprint $table) {
                $table->dropColumn('gia_ve_tu_dong');
            });
        }
    }
};
