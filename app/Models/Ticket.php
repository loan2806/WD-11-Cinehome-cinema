<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'movie_id',
        'ticket_code',
        'cinema_name',
        'room_name',
        'seat_code',
        'show_time',
        'total_price',
        'refund_amount',
        'type',
        'status',
        'booked_at',
        'cancelled_at',
    ];

    protected $casts = [
        'show_time' => 'datetime',
        'booked_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function canCancel(): bool
    {
        return $this->status === 'paid'
            && $this->booked_at
            && now()->diffInMinutes($this->booked_at) <= 5;
    }

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function foodOrders()
    {
        return $this->hasMany(FoodOrder::class);
    }
}
