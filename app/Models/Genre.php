<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    use HasFactory;

    protected $table = 'genres';

    protected $fillable = [
        'ten_the_loai',
        'mo_ta',
        'trang_thai',
    ];

    public function movies()
    {
        return $this->belongsToMany(
            Movie::class,
            'movie_genre',
            'genre_id',
            'movie_id'
        );
    }
}