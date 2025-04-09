<?php

namespace App\Model;

use App\CentralLogics\Helpers;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $casts = [
        'min_purchase' => 'float',
        'max_discount' => 'float',
        'discount' => 'float',
        'status' => 'integer',
        'start_date' => 'date',
        'expire_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function getMinPurchaseAttribute($min_purchase): float
    {
        return (float)Helpers::set_price($min_purchase);
    }

    public function getMaxDiscountAttribute($max_discount): float
    {
        return (float)Helpers::set_price($max_discount);
    }

    public function getDiscountAttribute($discount): float
    {
        return (float)Helpers::set_price($discount);
    }

    public function scopeActive($query)
    {
        return $query->where(['status' => 1])->where('start_date', '<=', now()->format('Y-m-d'))->where('expire_date', '>=', now()->format('Y-m-d'));
    }

    public function scopeDefault($query)
    {
        return $query->where(['coupon_type' => 'default']);
    }
}
