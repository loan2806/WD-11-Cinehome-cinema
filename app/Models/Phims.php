<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Showtime;

class Phims extends Model
{
    use HasFactory;

    protected $table = 'phims';

    protected $fillable = [
        'ten_phim',
        'slug',
        'mo_ta',
        'poster',
        'trailer',
        'quoc_gia_id',
        'dao_dien',
        'dien_vien',
        'ngon_ngu',
        'thoi_luong',
        'gioi_han_tuoi',
    ];

    protected $appends = [
        'schedule_status',
    ];

    protected static function booted(): void
    {
        static::creating(function ($movie) {

            if (empty($movie->slug)) {

                $movie->slug = Str::slug($movie->ten_phim) . '-' . uniqid();
            }
        });
    }

    /*
    |---------------------------------------
    | SHOWTIMES (FIX LỖI Ở ĐÂY)
    |---------------------------------------
    */
    public function showtimes()
    {
        return $this->hasMany(Showtime::class, 'movie_id');
    }

    /*
    |---------------------------------------
    | COUNTRY
    |---------------------------------------
    */
    public function country()
    {
        return $this->belongsTo(QuocGia::class, 'quoc_gia_id');
    }

    /*
    |---------------------------------------
    | GENRES
    |---------------------------------------
    */
    public function genres()
    {
        return $this->belongsToMany(
            TheLoai::class,
            'movie_genre',
            'movie_id',
            'genre_id'
        );
    }

    /*
    |---------------------------------------
    | SCOPE
    |---------------------------------------
    */
    public function scopeVisibleToUsers($query)
    {
        return $query->whereHas('showtimes');
    }

    public function scopeWithAvailableShowtimes($query)
    {
        return $query->whereHas('showtimes');
    }

    /*
    |---------------------------------------
    | STATUS PHIM
    |---------------------------------------
    */
    public function getScheduleStatusAttribute(): string
    {
        $now = now(config('app.timezone'));
        $showtimes = $this->showtimes;

        if ($showtimes->isEmpty()) {
            return 'Sắp ra mắt';
        }

        $hasNowShowing = false;
        $hasFutureWithin10 = false;
        $hasFutureBeyond10 = false;

        foreach ($showtimes as $showtime) {

            $date = Carbon::parse($showtime->show_date)->format('Y-m-d');

            $startTime = Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $date . ' ' . $showtime->show_time,
                config('app.timezone')
            );

            $endTime = $startTime->copy()->addMinutes($this->thoi_luong ?? 90);

            if ($now->between($startTime, $endTime)) {
                $hasNowShowing = true;
            }

            if ($startTime->gt($now)) {

                $days = $now->diffInDays($startTime);

                if ($days > 10) {
                    $hasFutureBeyond10 = true;
                } else {
                    $hasFutureWithin10 = true;
                }
            }
        }

        if ($hasNowShowing) {
            return 'Đang chiếu';
        }

        if ($hasFutureBeyond10) {
            return 'Sắp ra mắt';
        }

        if ($hasFutureWithin10) {
            return 'Sắp chiếu';
        }

        return 'Đã kết thúc';
    }
}