<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Showtime extends Model
{
    use HasFactory;

    protected $fillable = [
        'movie_id',
        'cinema_id',
        'room_name',
        'show_date',
        'show_time',
        'price',
        'slug',
    ];

    protected $casts = [
        'show_date' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | AUTO SLUG
    |--------------------------------------------------------------------------
    */
    protected static function booted(): void
    {
        static::creating(function ($showtime) {

            if (empty($showtime->slug)) {

                $showtime->slug =
                    Str::slug($showtime->movie->ten_phim)
                    . '-'
                    . strtolower(
                        str_replace(
                            ' ',
                            '-',
                            $showtime->room_name
                        )
                    )
                    . '-'
                    . uniqid();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | ROUTE KEY
    |--------------------------------------------------------------------------
    */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */
    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function cinema()
    {
        return $this->belongsTo(Cinema::class);
    }

    // public function seats()
    // {
    //     return $this->hasMany(Seat::class);
    // }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /*
    |--------------------------------------------------------------------------
    | START DATETIME
    |--------------------------------------------------------------------------
    */
    public function getStartDateTimeAttribute()
    {
        return Carbon::parse($this->show_date)
            ->setTimeFromTimeString($this->show_time);
    }

    /*
    |--------------------------------------------------------------------------
    | END DATETIME
    |--------------------------------------------------------------------------
    */
    public function getEndTimeAttribute()
    {
        return $this->start_date_time
            ->copy()
            ->addMinutes(
                $this->movie->thoi_luong ?? 90
            );
    }

    /*
    |--------------------------------------------------------------------------
    | CHECK ENDED
    |--------------------------------------------------------------------------
    */
    public function getIsEndedAttribute()
    {
        return now(config('app.timezone'))
            ->greaterThan($this->end_time);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    // Đang chiếu
    public function isNowShowing(): bool
    {
        $now = now(config('app.timezone'));

        return $now->between(
            $this->start_date_time,
            $this->end_time
        );
    }

    // Sắp chiếu (< 10 ngày)
    public function isUpcoming(): bool
    {
        return $this->start_date_time->isFuture()
            && $this->start_date_time
                ->diffInDays(now()) <= 10;
    }

    // Sắp ra mắt (> 10 ngày)
    public function isComingLater(): bool
    {
        return $this->start_date_time->isFuture()
            && $this->start_date_time
                ->diffInDays(now()) > 10;
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS
    |--------------------------------------------------------------------------
    */
    public function getStatusAttribute(): string
    {
        $now = now(config('app.timezone'));

        // Đang chiếu
        if ($now->between(
            $this->start_date_time,
            $this->end_time
        )) {

            return 'Đang chiếu';
        }

        // Đã kết thúc
        if ($now->greaterThan($this->end_time)) {

            return 'Đã kết thúc';
        }

        // Sắp chiếu / Sắp ra mắt
        if ($this->start_date_time->gt($now)) {

            $days = $now->diffInDays(
                $this->start_date_time
            );

            if ($days > 10) {

                return 'Sắp ra mắt';
            }

            return 'Sắp chiếu';
        }

        return 'Sắp ra mắt';
    }
}