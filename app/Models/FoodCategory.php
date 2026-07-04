<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FoodCategory extends Model
{
    use HasFactory;

    protected $table = 'food_categories';

    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * FIX RELATION CHUẨN
     */
    public function foods(): HasMany
    {
        return $this->hasMany(Food::class, 'category_id', 'id');
    }

    /**
     * route model binding bằng slug
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
}