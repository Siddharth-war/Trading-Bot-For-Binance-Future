<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $casts = [
        'user_id' => 'integer',
        'is_reply' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}
