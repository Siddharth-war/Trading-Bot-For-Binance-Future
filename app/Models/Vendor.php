<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
class Vendor extends Authenticatable
{
    use HasFactory;
    protected $table = "vendors";
    protected $fillable = ["first_name","last_name","email","password","phone_number","is_active","vat_number","address","bussiness_name",'image'];


    public function invoice(){
        return $this->hasMany(Invoice::class)->where("is_send",1);
    }
}
