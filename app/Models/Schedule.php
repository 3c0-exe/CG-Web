<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'subject_id', 'section_id', 'professor_id', 'room_id',
        'schedule_days', 'schedule_start_time', 'schedule_end_time',
        'late_threshold_minutes', 'allow_guests', 'class_code',
    ];

    protected $casts = [
        'schedule_days' => 'array',
        'allow_guests'  => 'boolean',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function professor()
    {
        return $this->belongsTo(User::class, 'professor_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function enrolledStudents()
    {
        return $this->belongsToMany(User::class, 'enrollments', 'schedule_id', 'student_id')
                    ->withPivot('enrollment_type')
                    ->withTimestamps();
    }

    public function sessions()
    {
        return $this->hasMany(ClassSession::class);
    }
}
