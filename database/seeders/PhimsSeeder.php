<?php

namespace Database\Seeders;

use App\Models\Phims;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PhimSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Phims::factory(40)->create();
    }
}
