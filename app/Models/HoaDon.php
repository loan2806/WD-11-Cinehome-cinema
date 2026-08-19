<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HoaDon extends Model
{
    use HasFactory;

    protected $fillable = [
        'nguoi_dung_id',
        'tong_tien',
        'trang_thai',
    ];

    public function nguoiDung()
    {
        return $this->belongsTo(
            NguoiDung::class,
            'nguoi_dung_id'
        );
    }
}