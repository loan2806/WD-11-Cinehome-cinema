<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Food extends Model
{
    use HasFactory;

    protected $table = 'foods';

    protected $fillable = [
        'sku',
        'name',
        'image',
        'category_id',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(FoodInvoiceItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(FoodVariant::class);
    }
    public function comboItems(): HasMany
    {
        return $this->hasMany(ComboItem::class, 'combo_food_id');
    }
    public function category()
    {
        return $this->belongsTo(FoodCategory::class);
    }
    
    
}
