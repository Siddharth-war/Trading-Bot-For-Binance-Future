<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuisineProduct extends Model
{
    use HasFactory;

    protected $table = 'cuisine_product';

    protected $casts = [
        'cuisine_id' => 'integer',
        'product_id' => 'integer',
    ];

}
