<?php

namespace Database\Seeders;

use App\Models\Phims;
use App\Models\PhongChieu;
use App\Models\SuatChieu;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SuatChieuSeeder extends Seeder
{
    public function run(): void
    {
        $phongChieus = PhongChieu::all();
        $phims = Phims::whereDate('ngay_khoi_chieu', '<=', now())->get();

        if ($phongChieus->isEmpty() || $phims->isEmpty()) {
            $this->command->warn('Không đủ dữ liệu để tạo suất chiếu.');
            return;
        }

        // Tạo suất chiếu cho 7 ngày tới
        for ($day = 0; $day < 7; $day++) {
            $ngayChieu = Carbon::today()->addDays($day);

            // Mỗi ngày có 4 suất chiếu
            $gioChieus = [9, 13, 17, 21];

            foreach ($phongChieus->take(3) as $phong) {
                foreach ($gioChieus as $gio) {
                    $phim = $phims->random();
                    $thoiGianChieu = $ngayChieu->copy()->setTime($gio, 0);
                    $thoiLuong = $phim->thoi_luong ?? 120;
                    $thoiGianKetThuc = $thoiGianChieu->copy()->addMinutes($thoiLuong + 15);

                    $now = Carbon::now();
                    if ($now < $thoiGianChieu) {
                        $trangThai = SuatChieu::TRANG_THAI_SAP_CHIEU;
                    } elseif ($now < $thoiGianKetThuc) {
                        $trangThai = SuatChieu::TRANG_THAI_DANG_CHIEU;
                    } else {
                        $trangThai = SuatChieu::TRANG_THAI_DA_CHIEU;
                    }

                    SuatChieu::firstOrCreate(
                        [
                            'phim_id' => $phim->id,
                            'rap_chieu_phim_id' => $phong->rap_chieu_phim_id,
                            'phong_chieu_id' => $phong->id,
                            'thoi_gian_chieu' => $thoiGianChieu,
                        ],
                        [
                            'thoi_gian_ket_thuc' => $thoiGianKetThuc,
                            'thoi_luong' => $thoiLuong,
                            'gia_ve' => fake()->randomElement([75000, 85000, 100000]),
                            'trang_thai' => $trangThai,
                        ]
                    );
                }
            }
        }
    }
}
