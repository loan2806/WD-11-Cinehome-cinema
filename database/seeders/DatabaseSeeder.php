<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
// use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PhimSeeder::class,
            TheLoaiSeeder::class,
            QuocGiaSeeder::class,
            AccountSeeder::class,
            CinemaSeeder::class,
            AccountSeeder::class,
            TicketSeeder::class,
        ]);

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);


        $this->call([
            AccountSeeder::class,
            TicketSeeder::class,
            UserSeeder::class,
        ]);
    }
}
