<?php

namespace App\Models;

use App\Models\ComboItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class FoodVariant extends Model
{
    use HasFactory;

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

    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class);
    }

    public function comboItems(): HasMany
{
    return $this->hasMany(
        ComboItem::class,
        'food_variant_id'
    );
}
}