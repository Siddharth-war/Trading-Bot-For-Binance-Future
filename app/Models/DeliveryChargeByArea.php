<?php

namespace App\Models;

use App\Model\Branch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryChargeByArea extends Model
{
    use HasFactory;

    protected $fillable=[
        'branch_id',
        'area_name',
        'delivery_charge',
    ];

    protected $casts=[
        'branch_id' => 'integer',
        'delivery_charge' => 'float',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }
}
