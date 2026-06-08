<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TheLoai extends Model
{
    use HasFactory;

    protected $table = 'the_loais';

    protected $fillable = [
        'ten_the_loai',
        'slug',
        'mo_ta',
        'trang_thai',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug from ten_the_loai when creating
        static::creating(function ($model) {
            if (!$model->slug) {
                $model->slug = Str::slug($model->ten_the_loai, '-');
            }
        });

        // Auto-generate slug from ten_the_loai when updating
        static::updating(function ($model) {
            if ($model->isDirty('ten_the_loai') && !$model->isDirty('slug')) {
                $model->slug = Str::slug($model->ten_the_loai, '-');
            }
        });
    }

    /**
     * Mối quan hệ nhiều-nhiều với bảng Phims
     */
    public function phims()
    {
        return $this->belongsToMany(
            Phims::class,
            'phim_the_loai',
            'the_loai_id',
            'phim_id'
        );
    }
}
