<?php

namespace Database\Seeders;

use App\Models\Showtime;
use Illuminate\Database\Seeder;

class ShowtimeSeeder extends Seeder
{
    public function run(): void
    {
        Showtime::factory()->count(30)->create();
    }
}