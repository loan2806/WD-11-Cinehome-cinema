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
        'price',
        'category',
        'description',
        'stock_quantity',
        'min_stock_quantity',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'min_stock_quantity' => 'integer',
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

    public function getStockStatusAttribute(): string
    {
        if (! $this->is_active) {
            return 'inactive';
        }

        if ($this->stock_quantity <= 0) {
            return 'out';
        }

        if ($this->stock_quantity <= $this->min_stock_quantity) {
            return 'low';
        }

        return 'available';
    }

    public function getStockStatusLabelAttribute(): string
    {
        return match ($this->stock_status) {
            'inactive' => 'Tạm ẩn',
            'out' => 'Hết hàng',
            'low' => 'Sắp hết',
            default => 'Đang bán',
        };
    }
}
