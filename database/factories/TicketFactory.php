<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->value('id'),
            'ticket_code' => strtoupper(uniqid('CINE-')),
            'movie_title' => fake()->sentence(3),
            'cinema_name' => fake()->randomElement([
                'CGV Mỹ Đình',
                'Beta Thanh Xuân',
                'Lotte Tây Hồ',
            ]),

            'room_name' => fake()->randomElement([
                'Phòng 1',
                'Phòng 2',
                'IMAX',
            ]),

            'seat_code' => fake()->randomElement([
                'A1',
                'B5',
                'C7',
                'D9',
            ]),

            'show_time' => now()->addDays(rand(1, 7)),

            'total_price' => fake()->randomElement([
                90000,
                120000,
                150000,
            ]),

            'refund_amount' => 0,

            'type' => fake()->randomElement([
                'online',
                'offline',
            ]),

            'status' => 'paid',

            'booked_at' => now()->subMinutes(rand(1, 4)),

        ];
    }
}
