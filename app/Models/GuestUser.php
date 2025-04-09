<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestUser extends Model
{
    protected $casts = [
        'ip_address' => 'string',
        'fcm_token' => 'string',
    ];
}
