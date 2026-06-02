<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Movie extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */
    protected $table = 'phims';

    /*
    |--------------------------------------------------------------------------
    | MASS ASSIGNMENT
    |--------------------------------------------------------------------------
    */
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

    /*
    |--------------------------------------------------------------------------
    | APPENDS
    |--------------------------------------------------------------------------
    */
    protected $appends = [
        'schedule_status',
    ];

    /*
    |--------------------------------------------------------------------------
    | AUTO SLUG
    |--------------------------------------------------------------------------
    */
    protected static function booted(): void
    {
        static::creating(function ($movie) {

            if (empty($movie->slug)) {

                $movie->slug =
                    Str::slug($movie->ten_phim)
                    . '-' .
                    uniqid();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RELATION: SHOWTIMES
    |--------------------------------------------------------------------------
    */
    public function showtimes()
    {
        return $this->hasMany(
            Showtime::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RELATION: COUNTRY
    |--------------------------------------------------------------------------
    */
    public function country()
    {
        return $this->belongsTo(
            Country::class,
            'quoc_gia_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RELATION: GENRES
    |--------------------------------------------------------------------------
    */
    public function genres()
    {
        return $this->belongsToMany(
            Genre::class,
            'movie_genre',
            'movie_id',
            'genre_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CHỈ HIỆN PHIM CÓ SUẤT CHIẾU
    |--------------------------------------------------------------------------
    */
    public function scopeVisibleToUsers($query)
    {
        return $query->whereHas('showtimes');
    }

    /*
    |--------------------------------------------------------------------------
    | ALIAS SCOPE
    |--------------------------------------------------------------------------
    */
    public function scopeWithAvailableShowtimes($query)
    {
        return $this->scopeVisibleToUsers($query);
    }

    /*
    |--------------------------------------------------------------------------
    | REALTIME STATUS
    |--------------------------------------------------------------------------
    */
    public function getScheduleStatusAttribute(): string
    {
        $now = now(config('app.timezone'));

        $showtimes = $this->showtimes;

        /*
        |--------------------------------------------------------------------------
        | KHÔNG CÓ SUẤT CHIẾU
        |--------------------------------------------------------------------------
        */
        if ($showtimes->isEmpty()) {

            return 'Sắp ra mắt';
        }

        $hasNowShowing = false;

        $hasFutureWithin10 = false;

        $hasFutureBeyond10 = false;

        /*
        |--------------------------------------------------------------------------
        | CHECK SHOWTIMES
        |--------------------------------------------------------------------------
        */
        foreach ($showtimes as $showtime) {

            /*
            |--------------------------------------------------------------------------
            | FIX DATE
            |--------------------------------------------------------------------------
            */
            $date = Carbon::parse(
                $showtime->show_date
            )->format('Y-m-d');

            /*
            |--------------------------------------------------------------------------
            | START TIME
            |--------------------------------------------------------------------------
            */
            $startTime = Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $date . ' ' . $showtime->show_time,
                config('app.timezone')
            );

            /*
            |--------------------------------------------------------------------------
            | END TIME
            |--------------------------------------------------------------------------
            */
            $endTime = $startTime
                ->copy()
                ->addMinutes(
                    $this->thoi_luong ?? 90
                );

            /*
            |--------------------------------------------------------------------------
            | ĐANG CHIẾU
            |--------------------------------------------------------------------------
            */
            if ($now->between(
                $startTime,
                $endTime
            )) {

                $hasNowShowing = true;
            }

            /*
            |--------------------------------------------------------------------------
            | SUẤT CHIẾU TƯƠNG LAI
            |--------------------------------------------------------------------------
            */
            if ($startTime->gt($now)) {

                $days = $now->diffInDays(
                    $startTime
                );

                if ($days > 10) {

                    $hasFutureBeyond10 = true;

                } else {

                    $hasFutureWithin10 = true;
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | RETURN STATUS
        |--------------------------------------------------------------------------
        */
        if ($hasNowShowing) {

            return 'Đang chiếu';
        }

        if ($hasFutureBeyond10) {

            return 'Sắp ra mắt';
        }

        if ($hasFutureWithin10) {

            return 'Sắp chiếu';
        }

        /*
        |--------------------------------------------------------------------------
        | ĐÃ KẾT THÚC
        |--------------------------------------------------------------------------
        */
        return 'Đã kết thúc';
    }
}
