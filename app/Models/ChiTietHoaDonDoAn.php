<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChiTietHoaDonDoAn extends Model
{
    use HasFactory;

    protected $table = 'food_invoice_items'; // Giữ nguyên tên bảng cấu trúc cũ trong database

    protected $fillable = [
        'food_invoice_id',
        'food_id',
        'food_variant_id',
        'quantity',
        'price',
    ];

    public function hoaDonDoAn(): BelongsTo
    {
        return $this->belongsTo(HoaDonDoAn::class, 'food_invoice_id');
    }

    public function doAn(): BelongsTo
    {
        return $this->belongsTo(DoAn::class, 'food_id');
    }

    public function bienTheDoAn(): BelongsTo
    {
        return $this->belongsTo(BienTheDoAn::class, 'food_variant_id');
    }
}