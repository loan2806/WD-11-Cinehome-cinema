<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'food_invoice_id',
        'food_id',
        'food_variant_id',
        'food_name',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FoodInvoice::class, 'food_invoice_id');
    }

    public function food(): BelongsTo
    {
        return $this->belongsTo(DoAn::class, 'food_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(BienTheDoAn::class, 'food_variant_id');
    }
}
