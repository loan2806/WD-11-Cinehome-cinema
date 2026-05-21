<?php

namespace App\Models;

use Database\Factories\CinemaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cinema extends Model
{
    /** @use HasFactory<CinemaFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'city',
        'phone',
        'image',
        'latitude',
        'longitude',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }
}
