<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'device_id', 'name', 'is_active', 'registered_at', 'last_seen_at',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'registered_at' => 'datetime',
        'last_seen_at'  => 'datetime',
    ];
}
