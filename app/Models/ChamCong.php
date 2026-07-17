<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChamCong extends Model
{
    use HasFactory;

    protected $table = 'cham_congs';

    protected $fillable = [
        'nguoi_dung_id',
        'ngay',
        'gio_vao',
        'gio_ra',
        'so_gio_lam',
        'so_gio_tang_ca',
        'di_muon',
        've_som',
        'nghi_phep',
        'nghi_khong_phep',
        'ghi_chu',
    ];

    protected $casts = [
        'ngay' => 'date',
        'di_muon' => 'boolean',
        've_som' => 'boolean',
        'nghi_phep' => 'boolean',
        'nghi_khong_phep' => 'boolean',
        'so_gio_lam' => 'decimal:2',
        'so_gio_tang_ca' => 'decimal:2',
    ];

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id');
    }
}
