<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComboItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'combo_food_id',
        'food_variant_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function combo(): BelongsTo
    {
        return $this->belongsTo(Food::class, 'combo_food_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(FoodVariant::class, 'food_variant_id');
    }
}