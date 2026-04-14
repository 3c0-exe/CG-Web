<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'name', 'code', 'year_level_id', 'section_id',
        'professor_id', 'class_code', 'allow_guests', 'late_threshold_minutes',
        'schedule_days', 'schedule_start_time', 'schedule_end_time'
    ];

    protected $casts = [
        'schedule_days' => 'array',
        'allow_guests'  => 'boolean',
    ];

    public function yearLevel()
    {
        return $this->belongsTo(YearLevel::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function professor()
    {
        return $this->belongsTo(User::class, 'professor_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function enrolledStudents()
    {
        return $this->belongsToMany(User::class, 'enrollments', 'subject_id', 'student_id')
                    ->withPivot('enrollment_type')
                    ->withTimestamps();
    }

    public function sessions()
    {
        return $this->hasMany(ClassSession::class);
    }
}
