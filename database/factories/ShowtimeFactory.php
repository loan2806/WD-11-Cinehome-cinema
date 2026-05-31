<?php

namespace Database\Factories;

use App\Models\Cinema;
use App\Models\Movie;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShowtimeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'movie_id' => Movie::inRandomOrder()->value('id'),
            'cinema_id' => Cinema::inRandomOrder()->value('id'),
            'room_name' => fake()->randomElement(['Phòng 1', 'Phòng 2', 'Phòng 3', 'IMAX', 'VIP']),
            'show_date' => fake()->dateTimeBetween('now', '+7 days')->format('Y-m-d'),
            'show_time' => fake()->randomElement(['09:00', '10:30', '13:00', '15:30', '18:00', '20:30', '22:00']),
            'price' => fake()->randomElement([70000, 80000, 90000, 100000, 120000]),
        ];
    }
}