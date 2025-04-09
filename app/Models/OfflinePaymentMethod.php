<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfflinePaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'method_name',
        'method_fields',
        'payment_note',
        'method_informations',
    ];

    protected $casts = [
        'id' => 'integer',
        'method_fields' => 'array',
        'method_informations' => 'array',
        'status' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', '=', 1);
    }
}
