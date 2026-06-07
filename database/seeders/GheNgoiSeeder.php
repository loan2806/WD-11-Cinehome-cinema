<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class GheNgoiSeeder extends Seeder
{
    public function run(): void
    {
        // Ghế đã được tạo trong PhongChieuSeeder
        $this->command->info('Ghế ngồi được tạo tự động khi tạo phòng chiếu.');
    }
}
