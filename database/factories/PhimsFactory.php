<?php

namespace Database\Factories;

use App\Models\Phims; // Gọi chuẩn tên Model Phims có chữ "s" của bạn để liên kết factory
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PhimsFactory extends Factory
{
    // Chỉ định rõ ràng Model đích để Laravel nhận diện chính xác
    protected $model = Phims::class;

    /**
     * Định nghĩa cấu trúc dữ liệu giả lập hoàn chỉnh cho bảng phims
     */
    public function definition(): array
    {
        $tenPhim = $this->faker->unique()->sentence(3);

        // Lấy ngẫu nhiên một quốc gia đang có sẵn trong database làm khóa ngoại
        $quocGiaId = DB::table('quoc_gias')->inRandomOrder()->value('id');

        // Phòng hờ nếu bảng quoc_gias trống dữ liệu, tự động tạo nhanh bản ghi Việt Nam để gán khóa ngoại chuẩn xác
        if (!$quocGiaId) {
            $quocGiaId = DB::table('quoc_gias')->insertGetId([
                'ten_quoc_gia' => 'Việt Nam',
                'slug' => 'viet-nam',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $anh = collect(File::files(public_path('storage/movies')));

        $poster = $anh->isNotEmpty()
            ? $anh->random()->getFilename()
            : 'placeholder.jpg';

        return [
            'ten_phim' => $tenPhim,
            'slug' => Str::slug($tenPhim) . '-' . Str::random(4),
            'mo_ta' => $this->faker->paragraph(2),
            'poster' => $poster,
            'trailer' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'dao_dien' => $this->faker->name(),
            'dien_vien' => $this->faker->name() . ', ' . $this->faker->name(),
            'ngon_ngu' => 'Tiếng Việt / Phụ đề',
            'thoi_luong' => $this->faker->numberBetween(90, 180),
            'gioi_han_tuoi' => $this->faker->randomElement(['P', 'T13', 'T16', 'T18']),
            'quoc_gia_id' => $quocGiaId, // Khắc phục hoàn toàn lỗi Field 'quoc_gia_id' doesn't have a default value
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
