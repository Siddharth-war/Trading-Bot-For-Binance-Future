<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductByBranch extends Model
{
    protected $table = 'product_by_branches';

    protected $fillable = [
        'product_id',
        'price',
        'discount_type',
        'discount',
        'branch_id',
        'is_available',
        'variations',
        'stock_type',
        'stock',
    ];

    protected $casts = [
        'id' => 'integer',
        'product_id' => 'integer',
        'discount_type' => 'string',
        'discount' => 'float',
        'price' => 'float',
        'branch_id' => 'integer',
        'is_available' => 'integer',
        'variations' => 'array',
        'stock_type' => 'string',
        'stock' => 'integer',
        'sold_quantity' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
