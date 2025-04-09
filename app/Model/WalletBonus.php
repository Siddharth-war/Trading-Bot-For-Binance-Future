<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletBonus extends Model
{
    use HasFactory;

    protected $casts = [
        'bonus_amount' => 'float',
        'minimum_add_amount' => 'float',
        'maximum_bonus_amount' => 'float',
        'status' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', '=', 1);
    }
}
