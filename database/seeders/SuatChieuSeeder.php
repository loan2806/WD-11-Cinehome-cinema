<?php

namespace Database\Seeders;

use App\Models\SuatChieu;
use Illuminate\Database\Seeder;

class SuatChieuSeeder extends Seeder
{
    public function run(): void
    {
        SuatChieu::factory()->count(20)->create();
    }
}