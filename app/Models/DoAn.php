<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DoAn extends Model
{
    use HasFactory;

    private const NGUONG_TON_KHO_THAP = 10;

    protected $table = 'foods';

    protected $fillable = [
        'sku',
        'name',
        'image',
        'category_id',
        'description',
        'is_active',
        'sort_order',
        'price',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'price' => 'float',
    ];

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(ChiTietHoaDonDoAn::class, 'food_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(BienTheDoAn::class, 'food_id');
    }

    public function defaultVariant(): HasOne
    {
        return $this->hasOne(BienTheDoAn::class, 'food_id')
            ->where('is_active', true)
            ->orderBy('price')
            ->orderBy('id');
    }

    public function getPriceAttribute(): float
    {
        if ($this->isCombo()) {
            return (float) ($this->attributes['price'] ?? 0);
        }

        return (float) ($this->saleVariant()?->price ?? 0);
    }

    public function getStockQuantityAttribute(): int
    {
        if ($this->isCombo()) {
            if ($this->comboItems->isEmpty()) {
                return 0;
            }

            return (int) $this->comboItems
                ->map(function ($item) {
                    $quantity = max((int) ($item->quantity ?? 1), 1);
                    $stock = (int) ($item->variant?->stock_quantity ?? 0);

                    return intdiv($stock, $quantity);
                })
                ->min();
        }

        return (int) ($this->saleVariant()?->stock_quantity ?? 0);
    }

    public function getMinStockQuantityAttribute(): int
    {
        return self::NGUONG_TON_KHO_THAP;
    }

    public function isCombo(): bool
    {
        return str_contains(strtolower($this->category?->name ?? ''), 'combo');
    }

    public function saleVariant(): ?BienTheDoAn
    {
        if ($this->relationLoaded('defaultVariant')) {
            return $this->defaultVariant;
        }

        $variants = $this->relationLoaded('variants')
            ? $this->variants
            : $this->variants()->where('is_active', true)->orderBy('price')->orderBy('id')->get();

        return $variants
            ->where('is_active', true)
            ->sortBy([
                ['price', 'asc'],
                ['id', 'asc'],
            ])
            ->first();
    }

    public function comboItems(): HasMany
    {
        return $this->hasMany(ChiTietCombo::class, 'combo_food_id');
    }

    public function category()
    {
        return $this->belongsTo(DanhMucDoAn::class, 'category_id');
    }
}
