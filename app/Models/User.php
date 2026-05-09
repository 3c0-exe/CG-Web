<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

protected $fillable = [
        'name', 'title', 'email', 'password', 'role',
        'student_id_number', 'year_level_id', 'section_id',
        'rfid_uid', 'rfid_linked_at', 'phone', 'status', 'is_irregular'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'rfid_linked_at' => 'datetime',
        'password' => 'hashed',
        'is_irregular' => 'boolean',
    ];

    // Role helpers
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isProfessor()
    {
        return $this->role === 'professor';
    }

    public function isStudent()
    {
        return $this->role === 'student';
    }

    // Relationships
    public function yearLevel()
    {
        return $this->belongsTo(YearLevel::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    

    // For irregular students — multiple sections via pivot
    public function sections()
    {
        return $this->belongsToMany(Section::class, 'student_sections')
                    ->withPivot('year_level_id')
                    ->withTimestamps();
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'student_id');
    }

    // Professor's schedules (was subjects)
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'professor_id');
    }

    public function sessions()
    {
        return $this->hasMany(ClassSession::class, 'professor_id');
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class, 'student_id');
    }
}
