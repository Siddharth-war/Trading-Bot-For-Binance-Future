<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomBucketProduct extends Model
{
    use HasFactory;
    protected $fillable = ["custom_bucket_id","product_id",'qty'];
}
