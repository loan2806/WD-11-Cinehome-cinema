<?php

namespace Database\Seeders;

use App\Models\RapChieuPhim;
use Illuminate\Database\Seeder;

class RapChieuPhimSeeder extends Seeder
{
    public function run(): void
    {
        RapChieuPhim::factory()->count(5)->create();
    }
}