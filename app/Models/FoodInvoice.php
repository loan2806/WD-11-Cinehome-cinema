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
        'received_amount',
        'change_amount',
        'payos_order_code',
        'payos_qr_code',
        'payos_checkout_url',
        'expires_at',
        'printed_at',
        'note',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'inventory_deducted' => 'boolean',
        'received_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'payos_order_code' => 'integer',
        'expires_at' => 'datetime',
        'printed_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at && now()->greaterThan($this->expires_at);
    }

    public function isPrinted(): bool
    {
        return $this->printed_at !== null;
    }

    public function items(): HasMany
    {
        return $this->hasMany(FoodInvoiceItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'user_id');
    }
}
