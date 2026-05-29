<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        Ticket::create([
            'user_id' => $user->id,
            'ticket_code' => 'CINE' . rand(10000, 99999),
            'movie_title' => 'Avengers Endgame',
            'cinema_name' => 'CGV Mỹ Đình',
            'room_name' => 'Phòng 1',
            'seat_code' => 'A5',
            'show_time' => now()->addDays(2),
            'total_price' => 120000,
            'refund_amount' => 0,
            'type' => 'online',
            'status' => 'paid',
            'booked_at' => now(),
        ]);
    }
}
