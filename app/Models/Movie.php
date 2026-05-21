<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'poster',
        'cover_image',
        'trailer_url',
        'genre',
        'country',
        'duration',
        'age_rating',
        'release_date',
        'status',
    ];

  protected $casts = [
    'release_date' => 'datetime',
];

    protected static function booted(): void
    {
        static::creating(function ($movie) {
            if (empty($movie->slug)) {
                $movie->slug = Str::slug($movie->title) . '-' . uniqid();
            }
        });
    }

public function getScheduleStatusAttribute(): string
{
    $now = Carbon::now('Asia/Ho_Chi_Minh');

    if (!$this->release_date) {
        return 'Sắp ra mắt';
    }

    $releaseDate = Carbon::parse($this->release_date);

    // CHƯA TỚI GIỜ CHIẾU
    if ($releaseDate->gt($now)) {

        // <= 10 ngày
        if ($releaseDate->lte($now->copy()->addDays(10))) {
            return 'Sắp chiếu';
        }

        return 'Sắp ra mắt';
    }

    // ĐANG CHIẾU (30 ngày)
    if ($releaseDate->copy()->addDays(30)->gte($now)) {
        return 'Đang chiếu';
    }

    return 'Ngừng chiếu';
}
    public function showtimes()
    {
        return $this->hasMany(Showtime::class);
    }
}