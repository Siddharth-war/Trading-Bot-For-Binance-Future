<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Day extends Model
{
    use HasFactory;
    protected $table="days";
    protected $fillable = ["name"];

    public function location(){
        return $this->hasMany(DayDelivery::class);
    }
}
