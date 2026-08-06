<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DanhMucDoAn extends Model
{
    use HasFactory;

    protected $table = 'food_categories'; // Giữ nguyên tên bảng cấu trúc cũ trong database

   protected $fillable = [
    'name',
    'slug',
    'is_combo',
];
protected $casts = [
    'is_combo' => 'boolean',
];

    public function doAns(): HasMany
    {
        return $this->hasMany(DoAn::class, 'category_id');
    }
}