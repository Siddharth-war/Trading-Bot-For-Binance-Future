<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TableOrder extends Model
{
    protected $table = 'table_orders';

    public function order(): HasMany
    {
        return $this->hasMany(Order::class, 'table_order_id', 'id');
    }
}

