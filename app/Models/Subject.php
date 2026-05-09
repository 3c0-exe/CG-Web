<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'name', 'code', 'year_level_id',
    ];

    public function yearLevel()
    {
        return $this->belongsTo(YearLevel::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
