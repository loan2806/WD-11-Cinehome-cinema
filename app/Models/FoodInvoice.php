<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'payment_method',
        'note',
    ];

    public function items()
    {
        return $this->hasMany(FoodInvoiceItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
