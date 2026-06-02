<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Genre;
use Illuminate\Support\Str;

class GenresSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = [
            'Hành động',
            'Tình cảm',
            'Hài',
            'Kinh dị',
            // thêm thể loại mới yêu cầu
            'Phiêu lưu',
        ];

        $genres = array_map(function ($name) {
            return [
                'ten_the_loai' => $name,
                'slug' => Str::slug($name),
                'trang_thai' => 1,
            ];
        }, $names);

        Genre::upsert($genres, ['ten_the_loai'], ['slug', 'trang_thai']);
    }
}
