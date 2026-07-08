<?php

namespace Database\Seeders;

use App\Models\NguoiDung;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CinemaManagerSeeder extends Seeder
{
    public function run(): void
    {
        $roleCinemaManager = Role::findOrCreate('Quản lý phòng chiếu', 'web');

        NguoiDung::updateOrCreate(
            ['email' => 'cinemamanager@cinehome.vn'],
            [
                'ho_ten' => 'Quản lý phòng chiếu CineHome',
                'mat_khau' => Hash::make('12345678'),
                'vai_tro' => 'quan_ly',
                'trang_thai_hoat_dong' => true,
            ]
        )->assignRole($roleCinemaManager);
    }
}
