<?php

namespace App\Model;

use App\User;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'transaction_id',
        'reference',
        'admin_bonus',
        'transaction_type',
        'debit',
        'credit',
        'balance',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'credit' => 'float',
        'debit' => 'float',
        'admin_bonus'=>'float',
        'balance'=>'float',
        'reference'=>'string',
        'created_at'=>'string'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
