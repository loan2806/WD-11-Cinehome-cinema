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
        'food_name',
        'quantity',
        'unit_price',
        'total_price',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FoodInvoice::class, 'food_invoice_id');
    }
}
