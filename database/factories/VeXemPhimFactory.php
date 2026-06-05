<?php

namespace Database\Factories;

use App\Models\VeXemPhim;
use App\Models\NguoiDung;
use App\Models\Phims; // Khớp với Model Phims có s của bạn
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<VeXemPhim>
 */
class VeXemPhimFactory extends Factory
{
    protected $model = VeXemPhim::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Lấy ngẫu nhiên người dùng có sẵn hoặc tạo mới nếu bảng trống
            'nguoi_dung_id'   => NguoiDung::inRandomOrder()->value('id') ?? NguoiDung::factory(),
            
            'ma_ve'           => 'VE-' . Str::upper(Str::random(8)),
            
            // Lấy ngẫu nhiên tên phim từ bảng phims có sẵn để dữ liệu mẫu nhìn thật hơn
            'ten_phim'        => Phims::inRandomOrder()->value('ten_phim') ?? (fake()->sentence(3) . ' (Việt hóa)'),
            
            'ten_rap'         => 'CineHome ' . fake()->city(),
            'ten_phong'       => 'Phòng Chiếu ' . rand(1, 5),
            'ma_ghe'          => fake()->randomElement(['A1', 'A2', 'B5', 'C9', 'H12', 'Vip-01']),
            'thoi_gian_chieu' => now()->addDays(rand(1, 5))->setTime(rand(9, 23), fake()->randomElement([0, 30])),
            'tong_tien'       => fake()->randomElement([45000, 60000, 75000, 90000, 120000]),
            'tien_hoan'       => 0,
            'loai_ve'         => fake()->randomElement(['truc_tuyen', 'tai_quay']),
            'trang_thai'      => fake()->randomElement(['da_thanh_toan', 'da_huy', 'da_su_dung']),
        ];
    }
}