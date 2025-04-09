<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class SocialMedia extends Model
{
    protected $casts = [
        'status'        => 'integer',
        'active_status' => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', '=', 1);
    }

    protected $table = 'social_medias';
}
