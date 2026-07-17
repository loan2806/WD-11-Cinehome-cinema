<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BangLuong extends Model
{
    use HasFactory;

    protected $table = 'bang_luongs';

    protected $fillable = [
        'nguoi_dung_id',
        'thang',
        'nam',
        'tong_ngay_cong',
        'tong_gio_lam',
        'tong_gio_tang_ca',
        'so_lan_di_muon',
        'so_lan_ve_som',
        'so_ngay_nghi_phep',
        'so_ngay_nghi_khong_phep',
        'luong_co_ban',
        'phu_cap',
        'thuong',
        'phat',
        'luong_thuc_nhan',
        'trang_thai',
    ];

    protected $casts = [
        'tong_ngay_cong' => 'decimal:2',
        'tong_gio_lam' => 'decimal:2',
        'tong_gio_tang_ca' => 'decimal:2',
        'luong_co_ban' => 'decimal:2',
        'phu_cap' => 'decimal:2',
        'thuong' => 'decimal:2',
        'phat' => 'decimal:2',
        'luong_thuc_nhan' => 'decimal:2',
    ];

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id');
    }
}
