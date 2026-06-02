<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    use HasFactory;

    protected $table = 'the_loais';

    protected $fillable = [
        'ten_the_loai',
        'slug',
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