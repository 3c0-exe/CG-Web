<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = ['name'];

    public function sessions()
    {
        return $this->hasMany(ClassSession::class);
    }
}