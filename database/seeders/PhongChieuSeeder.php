<?php

namespace Database\Seeders;

use App\Models\GheNgoi;
use App\Models\HangGhe;
use App\Models\LoaiGhe;
use App\Models\PhongChieu;
use App\Models\RapChieuPhim;
use Illuminate\Database\Seeder;

class PhongChieuSeeder extends Seeder
{
    public function run(): void
    {
        $rap = RapChieuPhim::first();

        if (!$rap) {
            $this->command->warn('Không có rạp chiếu phim. Hãy chạy RapChieuPhimSeeder trước.');
            return;
        }

        $phongChieus = [
            ['ten_phong' => 'Phòng 1', 'loai_phong' => '2d', 'suc_chua' => 80],
            ['ten_phong' => 'Phòng 2', 'loai_phong' => '2d', 'suc_chua' => 80],
            ['ten_phong' => 'Phòng 3', 'loai_phong' => '3d', 'suc_chua' => 60],
            ['ten_phong' => 'Phòng VIP', 'loai_phong' => '2d', 'suc_chua' => 40],
            ['ten_phong' => 'Phòng IMAX', 'loai_phong' => 'imax', 'suc_chua' => 50],
        ];

        foreach ($phongChieus as $phong) {
            $phongChieu = PhongChieu::firstOrCreate(
                [
                    'rap_chieu_phim_id' => $rap->id,
                    'ten_phong' => $phong['ten_phong'],
                ],
                [
                    'loai_phong' => $phong['loai_phong'],
                    'suc_chua' => $phong['suc_chua'],
                    'trang_thai' => 'hoat_dong',
                ]
            );

            // Tạo hàng ghế cho phòng
            $this->createHangGhes($phongChieu, $phong['suc_chua']);
        }
    }

    private function createHangGhes(PhongChieu $phongChieu, int $sucChua): void
    {
        $soHang = 8;
        $soCot = (int) ceil($sucChua / $soHang);
        $loaiGhes = LoaiGhe::all();

        if ($loaiGhes->isEmpty()) {
            return;
        }

        $loaiThuong = $loaiGhes->firstWhere('ten_loai', 'Thường') ?? $loaiGhes->first();
        $loaiVip = $loaiGhes->firstWhere('ten_loai', 'VIP') ?? $loaiThuong;

        $hangChuCai = range('A', 'Z');

        for ($i = 0; $i < $soHang; $i++) {
            $tenHang = $hangChuCai[$i];

            $hangGhe = HangGhe::firstOrCreate(
                [
                    'phong_chieu_id' => $phongChieu->id,
                    'ten_hang' => $tenHang,
                ],
                []
            );

            for ($j = 1; $j <= $soCot; $j++) {
                // Hàng A-D là VIP, các hàng khác là thường
                $loaiGhe = ($i < 4) ? $loaiVip : $loaiThuong;

                GheNgoi::firstOrCreate(
                    [
                        'phong_chieu_id' => $phongChieu->id,
                        'hang_ghe_id' => $hangGhe->id,
                        'ma_ghe' => $tenHang . $j,
                        'cot' => $j,
                    ],
                    [
                        'loai_ghe_id' => $loaiGhe->id,
                        'trang_thai' => 'hoat_dong',
                    ]
                );
            }
        }
    }
}
