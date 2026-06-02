<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuocGia extends Model
{
    use HasFactory;

    protected $table = 'quoc_gias';

    protected $fillable = [
        'ten_quoc_gia',
        'ma_quoc_gia',
        'trang_thai',
    ];

    public function movies()
    {
        return $this->hasMany(
            Phims::class,
            'quoc_gia_id'
        );
    }
}