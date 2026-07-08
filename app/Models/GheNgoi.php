<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GheNgoi extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'ghe_ngois';

    protected $fillable = [
        'phong_chieu_id',
        'hang_ghe_id',
        'loai_ghe_id',
        'ma_ghe',
        'cot',
        'couple_group_id',
        'trang_thai',
    ];

    protected $casts = [
        'cot' => 'integer',
    ];

    public const TRANG_THAI = [
        'hoat_dong' => 'Hoạt động',
        'bao_tri' => 'Bảo trì',
    ];

    public function phongChieu(): BelongsTo
    {
        return $this->belongsTo(PhongChieu::class, 'phong_chieu_id');
    }

    public function hangGhe(): BelongsTo
    {
        return $this->belongsTo(HangGhe::class, 'hang_ghe_id');
    }

    public function loaiGhe(): BelongsTo
    {
        return $this->belongsTo(LoaiGhe::class, 'loai_ghe_id');
    }

    public function isAvailable(): bool
    {
        return $this->trang_thai === 'hoat_dong';
    }

    public function isUnderMaintenance(): bool
    {
        return $this->trang_thai === 'bao_tri';
    }

    public function scopeByPhongChieu($query, $phongChieuId)
    {
        return $query->where('phong_chieu_id', $phongChieuId);
    }

    public function lichBaoTris()
    {
        return $this->hasMany(LichBaoTriGheNgoi::class, 'ghe_ngoi_id');
    }

    public function lichBaoTriHienTai()
    {
        return $this->lichBaoTris()
            ->whereIn('trang_thai', ['cho_thuc_hien', 'dang_thuc_hien'])
            ->where('thoi_gian_bat_dau', '<=', now())
            ->orderByDesc('thoi_gian_bat_dau')
            ->first();
    }

    public function lichBaoTriSapToi()
    {
        return $this->lichBaoTris()
            ->where('trang_thai', 'cho_thuc_hien')
            ->where('thoi_gian_bat_dau', '>', now())
            ->orderBy('thoi_gian_bat_dau')
            ->first();
    }

    public function isEffectivelyUnderMaintenance(): bool
    {
        if ($this->trang_thai === 'bao_tri') {
            return true;
        }

        $lich = $this->lichBaoTriHienTai();
        return (bool) $lich;
    }

    public function isEffectivelyActive(): bool
    {
        return !$this->isEffectivelyUnderMaintenance();
    }

    public function scopeActive($query)
    {
        return $query->where('trang_thai', 'hoat_dong')
            ->whereDoesntHave('lichBaoTris', function ($q) {
                $q->whereIn('trang_thai', ['cho_thuc_hien', 'dang_thuc_hien'])
                    ->where('thoi_gian_bat_dau', '<=', now());
            });
    }
}
