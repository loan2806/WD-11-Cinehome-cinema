<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class TinTuc extends Model
{
    use HasFactory;

    protected $table = 'tin_tucs';

    protected $fillable = [
        'danh_muc_tin_id',
        'tieu_de',
        'slug',
        'mo_ta_ngan',
        'noi_dung',
        'hinh_anh',
        'hinh_anh_thumbnail',
        'noi_bat',
        'trang_thai',
        'tac_gia',
        'luot_xem',
        'ngay_dang',
    ];

    protected $casts = [
        'noi_bat' => 'boolean',
        'trang_thai' => 'boolean',
        'luot_xem' => 'integer',
        'ngay_dang' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->tieu_de);
            }
            if (empty($model->ngay_dang)) {
                $model->ngay_dang = now();
            }
        });
    }

    public function danhMucTin(): BelongsTo
    {
        return $this->belongsTo(DanhMucTin::class, 'danh_muc_tin_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'tin_tuc_tags');
    }

    public function scopeActive($query)
    {
        return $query->where('trang_thai', true);
    }

    public function scopeNoiBat($query)
    {
        return $query->where('noi_bat', true);
    }

    public function scopeOrderByNgayDang($query)
    {
        return $query->orderBy('ngay_dang', 'desc');
    }

    public function incrementLuotXem(): void
    {
        $this->increment('luot_xem');
    }
}
