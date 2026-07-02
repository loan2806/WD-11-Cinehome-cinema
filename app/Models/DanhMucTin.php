<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class DanhMucTin extends Model
{
    use HasFactory;

    protected $table = 'danh_muc_tins';

    protected $fillable = [
        'ten_danh_muc',
        'slug',
        'mo_ta',
        'icon',
        'mau_sac',
        'thu_tu',
        'trang_thai',
    ];

    protected $casts = [
        'trang_thai' => 'boolean',
        'thu_tu' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->ten_danh_muc);
            }
        });
    }

    public function tinTucs(): HasMany
    {
        return $this->hasMany(TinTuc::class, 'danh_muc_tin_id');
    }

    public function scopeActive($query)
    {
        return $query->where('trang_thai', true);
    }

    public function scopeOrderByThuTu($query)
    {
        return $query->orderBy('thu_tu', 'asc');
    }
}
