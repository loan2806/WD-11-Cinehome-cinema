<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'staff@cinehome.vn'],
            [
                'name' => 'Staff',
                'password' => Hash::make('12345678'),
                'role' => 'staff',
                'is_active' => true,
            ]
        );
    }
}