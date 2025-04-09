<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Model\Product;
class CustomBucket extends Model
{
    use HasFactory;
    protected $fillable = ["user_id","name"];

    public function products(){
        return $this->belongsToMany(Product::class,'custom_bucket_products');
    }

    public function custom_products(){
        return $this->hasMany(CustomBucketProduct::class);
    }
}
