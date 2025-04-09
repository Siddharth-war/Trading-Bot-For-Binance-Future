<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DeliveryChargeByArea;


class OrderArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'branch_id',
        'area_id',
    ];

    protected $casts = [
        'order_id' => 'integer',
        'branch_id' => 'integer',
        'area_id' => 'integer',
    ];

    public function area()
    {
        return $this->belongsTo(DeliveryChargeByArea::class, 'area_id');
    }
}
