<?php

namespace Database\Seeders;

use App\Models\Phims;
use Illuminate\Database\Seeder;

class PhimSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ép buộc tạo 40 phim mẫu trực tiếp để giải quyết việc database bị trống dữ liệu
        Phims::factory()->count(20)->create();
    }
}