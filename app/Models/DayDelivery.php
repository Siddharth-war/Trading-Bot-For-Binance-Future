<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DayDelivery extends Model
{
    use HasFactory;
    protected $table = "day_deliveries";
    protected $fillable = ["day_id","location"];

    public function day(){
        return $this->belongsTo(Day::class);
    }
}
