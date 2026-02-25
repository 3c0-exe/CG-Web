<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = ['year_level_id', 'name'];

    public function yearLevel()
    {
        return $this->belongsTo(YearLevel::class);
    }

    public function students()
    {
        return $this->hasMany(User::class, 'section_id');
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }
}
