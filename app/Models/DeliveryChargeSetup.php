<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryChargeSetup extends Model
{
    use HasFactory;

    protected $fillable=[
        'branch_id',
        'delivery_charge_type',
        'delivery_charge_per_kilometer',
        'minimum_delivery_charge',
        'minimum_distance_for_free_delivery',
        'fixed_delivery_charge',
    ];

    protected $casts=[
        'branch_id' => 'integer',
        'delivery_charge_per_kilometer' => 'float',
        'minimum_delivery_charge' => 'float',
        'minimum_distance_for_free_delivery' => 'float',
        'fixed_delivery_charge' => 'float',
    ];
}
