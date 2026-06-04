<?php

namespace Database\Seeders;

use App\Models\Phims; // Giữ nguyên tên Model Phims (có s) theo đúng bộ điều khiển của bạn
use Illuminate\Database\Seeder;

class PhimSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kiểm tra xem Factory của Model Phims có tồn tại không trước khi gọi tạo dữ liệu mẫu
        if (method_exists(Phims::class, 'factory')) {
            Phims::factory(40)->create();
        }
    }
}