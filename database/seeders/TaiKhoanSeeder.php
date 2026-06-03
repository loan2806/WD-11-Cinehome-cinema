<?php

namespace Database\Seeders;

use App\Models\NguoiDung;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        NguoiDung::updateOrCreate(
            ['email' => 'admin@cinehome.vn'],
            [
                'name' => 'Admin CineHome',
                'password' => Hash::make('12345678'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        NguoiDung::updateOrCreate(
            ['email' => 'staff@cinehome.vn'],
            [
                'name' => 'Staff CineHome',
                'password' => Hash::make('12345678'),
                'role' => 'staff',
                'is_active' => true,
            ]
        );

        NguoiDung::updateOrCreate(
            ['email' => 'user@cinehome.vn'],
            [
                'name' => 'User CineHome',
                'password' => Hash::make('12345678'),
                'role' => 'user',
                'is_active' => true,
            ]
        );
    }
}