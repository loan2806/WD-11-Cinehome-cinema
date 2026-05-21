<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CinemaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'CineHome Nguyễn Trãi',
                'CineHome Cầu Giấy',
                'CineHome Times City',
                'CineHome Hà Đông',
                'CineHome Royal City',
                'CineHome Vincom Bà Triệu',
                'CineHome Mỹ Đình',
                'CineHome Long Biên',
                'CineHome Hồ Gươm',
            ]),

            'address' => fake()->randomElement([
                '123 Nguyễn Trãi, Thanh Xuân, Hà Nội',
                '88 Xuân Thủy, Cầu Giấy, Hà Nội',
                '458 Minh Khai, Hai Bà Trưng, Hà Nội',
                '72 Trần Duy Hưng, Cầu Giấy, Hà Nội',
                '191 Bà Triệu, Hai Bà Trưng, Hà Nội',
                'Vincom Mega Mall Royal City, Thanh Xuân, Hà Nội',
            ]),

            'city' => 'Hà Nội',

            'latitude' => fake()->latitude(20.8, 21.2),
            'longitude' => fake()->longitude(105.5, 105.9),
            'status' => 'active',

            'phone' => fake()->numerify('09########'),

            'image' => fake()->randomElement([
                'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=1200',
                'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?q=80&w=1200',
                'https://images.unsplash.com/photo-1505686994434-e3cc5abf1330?q=80&w=1200',
            ]),
        ];
    }
}
