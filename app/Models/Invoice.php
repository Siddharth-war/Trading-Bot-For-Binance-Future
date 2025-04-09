<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = ["invoice_number","amount","status","upload_pdf","notes","due_date","vendor_id",'invoice_id','is_send'];

    public function vendor(){
        return $this->hasOne(Vendor::class,'id','vendor_id');
    }
}
