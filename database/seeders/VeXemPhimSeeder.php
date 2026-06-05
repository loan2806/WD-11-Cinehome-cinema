<?php

namespace Database\Seeders;

use App\Models\VeXemPhim;
use Illuminate\Database\Seeder;

class VeXemPhimSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo 30 vé xem phim mẫu bằng tiếng Việt
        VeXemPhim::factory()->count(30)->create();
    }
}