<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{

    protected $table = 'tags';
    protected $fillable = ['tag'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->using('App\Model\ProductTag');
    }



}
