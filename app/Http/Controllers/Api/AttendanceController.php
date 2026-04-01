<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\ClassSession;
use App\Models\User;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
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

        // Prevent duplicate scan
        $existing = AttendanceRecord::where('session_id', $session->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Already scanned', 'record' => $existing], 400);
        }

        // Determine present or late immediately
        $threshold = $session->subject->late_threshold_minutes ?? 15;
        $minutesLate = now()->diffInMinutes($session->started_at);
        $status = $minutesLate > $threshold ? 'late' : 'present';

        $record = AttendanceRecord::create([
            'session_id'        => $session->id,
            'student_id'        => $student->id,
            'rfid_uid'          => strtoupper($request->uid),
            'rfid_scanned_at'   => now(),
            'code_confirmed_at' => now(),
            'status'            => $status,
            'attendance_type'   => 'regular',
        ]);

        return response()->json([
            'success' => true,
            'record'  => $record,
            'student' => $student->only('id', 'name', 'student_id_number'),
            'status'  => $status,
        ]);
    }

    public function confirmCode(Request $request)
    {
        // Kept for backward compatibility but no longer needed in normal flow
        $request->validate([
            'record_id'  => 'required|exists:attendance_records,id',
            'class_code' => 'required|string',
        ]);

        $record  = AttendanceRecord::findOrFail($request->record_id);
        $session = $record->session;

        if (strtoupper($request->class_code) !== strtoupper($session->subject->class_code)) {
            return response()->json(['success' => false, 'message' => 'Invalid class code'], 400);
        }

        $threshold   = $session->subject->late_threshold_minutes ?? 15;
        $minutesLate = now()->diffInMinutes($session->started_at);
        $status      = $minutesLate > $threshold ? 'late' : 'present';

        $record->update([
            'code_confirmed_at' => now(),
            'status'            => $status,
        ]);

        return response()->json(['success' => true, 'status' => $status, 'record' => $record]);
    }

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
            'session' => $session->load('subject'),
            'records' => $records,
        ]);
    }

    public function myAttendance(Request $request)
    {
        $records = AttendanceRecord::where('student_id', $request->user()->id)
            ->with(['session.subject'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['success' => true, 'attendance' => $records]);
    }
}