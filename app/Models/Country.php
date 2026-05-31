<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $table = 'countries';

    protected $fillable = [
        'ten_quoc_gia',
        'ma_quoc_gia',
        'trang_thai',
    ];

    public function movies()
    {
        return $this->hasMany(
            Movie::class,
            'quoc_gia_id'
        );
    }
}