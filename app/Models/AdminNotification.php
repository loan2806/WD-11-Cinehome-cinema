<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'tieu_de',
        'noi_dung',
        'loai',
        'doi_tuong',
        'da_doc',
        'thoi_gian_xuat_ban',
    ];

    protected $casts = [
        'thoi_gian_xuat_ban' => 'datetime',
    ];
}
