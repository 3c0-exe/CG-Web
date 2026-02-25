<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    protected $fillable = [
        'session_id', 'student_id', 'rfid_uid',
        'rfid_scanned_at', 'code_confirmed_at',
        'status', 'attendance_type'
    ];

    protected $casts = [
        'rfid_scanned_at' => 'datetime',
        'code_confirmed_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(ClassSession::class, 'session_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
