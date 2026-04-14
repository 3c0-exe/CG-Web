<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\ClassSession;
use App\Models\User;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    // THIS IS THE WEBHOOK FOR YOUR ESP32 HARDWARE!
    public function scanCard(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
            'uid'        => 'required|string',
        ]);

        $session = ClassSession::where('session_id', $request->session_id)
            ->where('status', 'active')
            ->with('subject')
            ->first();

        if (!$session) {
            return response()->json(['success' => false, 'message' => 'No active session found'], 404);
        }

        $student = User::where('rfid_uid', strtoupper($request->uid))->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Card not registered'], 404);
        }

        // Check if this student belongs to the session's section
        $sessionSectionId = $session->subject->section_id;

        $belongsToSection = ($student->section_id == $sessionSectionId)
            || $student->sections()->where('section_id', $sessionSectionId)->exists();

        if (!$belongsToSection) {
            return response()->json(['success' => false, 'message' => 'Student not enrolled in this section'], 403);
        }

        // Prevent duplicate scan
        $existing = AttendanceRecord::where('session_id', $session->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Already scanned', 'record' => $existing], 400);
        }

        // Determine present or late immediately based on Professor's setting
        $threshold = $session->subject->late_threshold_minutes ?? 15;
        $minutesLate = now()->diffInMinutes($session->started_at);
        $status = $minutesLate > $threshold ? 'late' : 'present';

        $record = AttendanceRecord::create([
            'session_id'        => $session->id,
            'student_id'        => $student->id,
            'rfid_uid'          => strtoupper($request->uid),
            'rfid_scanned_at'   => now(),
            'status'            => $status, // Instantly set to Present or Late
            'attendance_type'   => 'regular',
        ]);

        return response()->json([
            'success' => true,
            'record'  => $record,
            'student' => $student->only('id', 'name', 'student_id_number'),
            'status'  => $status,
        ]);
    }

    // Professor fetches this to update the Live Attendance screen
    public function liveFeed(Request $request, $sessionId)
    {
        $session = ClassSession::where('session_id', $sessionId)->first();

        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Session not found'], 404);
        }

        $records = AttendanceRecord::where('session_id', $session->id)
            ->with('student:id,name,student_id_number')
            ->orderBy('rfid_scanned_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'session' => $session->load('subject.section', 'room'),
            'records' => $records,
        ]);
    }
}