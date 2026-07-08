<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BienTheDoAn extends Model
{
    use HasFactory;

    protected $table = 'food_variants'; // Giữ nguyên tên bảng cấu trúc cũ trong database

    protected $fillable = [
        'food_id',
        'value',
        'price',
        'stock_quantity',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'is_active' => 'boolean',
    ];

    public function doAn(): BelongsTo
    {
        return $this->belongsTo(DoAn::class, 'food_id');
    }

    public function chiTietCombos(): HasMany
    {
        return $this->hasMany(ChiTietCombo::class, 'food_variant_id');
    }
}