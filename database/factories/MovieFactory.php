<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MovieFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->randomElement([
            'Đêm Cuối Cùng',
            'Bí Mật Sau Màn Ảnh',
            'Thành Phố Ánh Đèn',
            'Cuộc Đua Cuối Cùng',
            'Ngôi Nhà Điện Ảnh',
            'Ký Ức Mùa Hè',
            'Vùng Đất Lạ',
            'Hành Trình Bóng Tối',
            'Ánh Sáng Cuối Đường',
            'Rạp Chiếu Ký Ức',
        ]) . ' ' . fake()->numberBetween(1, 999);

        $posterImages = [
            'https://images.unsplash.com/photo-1574267432553-4b4628081c31?q=80&w=500&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1536440136628-849c177e76a1?q=80&w=500&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1524985069026-dd778a71c7b4?q=80&w=500&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1582738411706-bfc8e691d1c2?q=80&w=500&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1512149177596-f817c7ef5d4c?q=80&w=500&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1535016120720-40c646be5580?q=80&w=500&auto=format&fit=crop',
        ];

        $coverImages = [
            'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=1600&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?q=80&w=1600&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1505686994434-e3cc5abf1330?q=80&w=1600&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1478720568477-152d9b164e26?q=80&w=1600&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1594909122845-11baa439b7bf?q=80&w=1600&auto=format&fit=crop',
        ];

        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . uniqid(),

            'description' => fake()->paragraph(4),

            'poster' => fake()->randomElement($posterImages),
            'cover_image' => fake()->randomElement($coverImages),

            'trailer_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',

            'genre' => fake()->randomElement([
                'Hành động',
                'Kinh dị',
                'Tình cảm',
                'Hài',
                'Hoạt hình',
                'Trinh thám',
                'Phiêu lưu',
            ]),

            'country' => fake()->randomElement([
                'Việt Nam',
                'Mỹ',
                'Hàn Quốc',
                'Nhật Bản',
                'Trung Quốc',
                'Thái Lan',
            ]),

            'duration' => fake()->numberBetween(90, 150),

            'age_rating' => fake()->randomElement([
                'P',
                'T13',
                'T16',
                'T18',
            ]),

            'release_date' => fake()->dateTimeBetween('-2 months', '+2 months'),

            'status' => fake()->randomElement([
                'now_showing',
                'now_showing',
                'now_showing',
                'coming_soon',
            ]),
        ];
    }
}