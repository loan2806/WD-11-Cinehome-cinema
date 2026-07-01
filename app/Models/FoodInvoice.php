<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FoodInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_code',
        'user_id',
        'ticket_id',
        'customer_name',
        'customer_phone',
        'subtotal',
        'discount',
        'total',
        'payment_status',
        'inventory_deducted',
        'payment_method',
        'note',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'inventory_deducted' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(FoodInvoiceItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'user_id');
    }
}
