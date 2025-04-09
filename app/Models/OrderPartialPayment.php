<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPartialPayment extends Model
{
    use HasFactory;

    protected $casts = [
        'order_id' => 'integer',
        'paid_amount' => 'float',
        'due_amount' => 'float',
    ];

    protected $fillable = [
        'order_id',
        'paid_with',
        'paid_amount',
        'due_amount',
    ];
}
