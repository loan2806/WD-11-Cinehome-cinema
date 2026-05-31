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
        'release_date' => 'date',
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
    $today = Carbon::today('Asia/Ho_Chi_Minh');

    if (!$this->release_date) {
        return 'Sắp ra mắt';
    }

    if ($this->release_date->lte($today)) {
        return 'Đang chiếu';
    }

    if ($this->release_date->lte($today->copy()->addDays(10))) {
        return 'Sắp chiếu';
    }

    return 'Sắp ra mắt';
}
    public function showtimes()
    {
        return $this->hasMany(Showtime::class);
    }

    public function reviews()
    {
        return $this->hasMany(MovieReview::class);
    }

    public function approvedReviews()
    {
        return $this->hasMany(MovieReview::class)->where('status', 'approved');
    }
}
