<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TheLoai extends Model
{
    use HasFactory;

    protected $table = 'the_loais';

    protected $fillable = [
        'ten_the_loai',
        'slug', // ĐÃ BỔ SUNG: Cho phép điền dữ liệu slug từ tầng seeder/factory
        'mo_ta',
        'trang_thai',
    ];

    /**
     * Mối quan hệ nhiều-nhiều với bảng Phims
     */
    public function phims()
    {
        return $this->belongsToMany(
            Phims::class,
            'movie_genre',
            'genre_id',
            'movie_id'
        );
    }
}