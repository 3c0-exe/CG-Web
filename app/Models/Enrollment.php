<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $fillable = [
        'student_id', 'schedule_id', 'enrollment_type', 'home_section_id'
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function homeSection()
    {
        return $this->belongsTo(Section::class, 'home_section_id');
    }
}
