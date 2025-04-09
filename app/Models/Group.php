<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Model\Category;
class Group extends Model
{
    use HasFactory;
    protected $table ="groups";
    protected $fillable = ["name",'credit_limit'];

    public function cate(){
        return $this->belongsToMany(Category::class,'group_categories');
    }

    public function price(){
        return $this->hasMany(GroupCategory::class);
    }
}
