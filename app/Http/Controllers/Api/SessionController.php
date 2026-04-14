<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassSession;
use App\Models\AttendanceRecord;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\MqttService;

class SessionController extends Controller
{
    public function startSession(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'room_id'    => 'required|exists:rooms,id',
        ]);

        // Ensure the professor actually owns the subject they are trying to start
        $subject = Subject::where('id', $request->subject_id)
            ->where('professor_id', $request->user()->id)
            ->first();

        if (!$subject) {
            return response()->json(['success' => false, 'message' => 'Unauthorized or invalid subject'], 403);
        }

        $existing = ClassSession::where('subject_id', $request->subject_id)
            ->where('status', 'active')
            ->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Session already active for this subject'], 400);
        }

        $session = ClassSession::create([
            'session_id'   => strtoupper(Str::random(8)),
            'subject_id'   => $request->subject_id,
            'professor_id' => $request->user()->id,
            'room_id'      => $request->room_id,
            'started_at'   => now(),
            'status'       => 'active',
        ]);

        $session->load('subject.section');
        
        if (class_exists(MqttService::class)) {
            app(MqttService::class)->sessionStart($session);
        }

        return response()->json([
            'success' => true,
            'session' => $session,
        ]);
    }

    public function endSession(Request $request, $sessionId)
    {
        $session = ClassSession::where('session_id', $sessionId)
            ->where('professor_id', $request->user()->id)
            ->first();

        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Session not found'], 404);
        }

        // Because we removed the 'pending' status, we just count Present and Late directly.
        // We calculate Absents based on the total number of students in the Section.
        
        $present = AttendanceRecord::where('session_id', $session->id)->where('status', 'present')->count();
        $late    = AttendanceRecord::where('session_id', $session->id)->where('status', 'late')->count();
        
        $sectionId = $session->subject->section_id;

        $totalStudentsInSection = \App\Models\User::where('role', 'student')
            ->where(function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId)
                      ->orWhereHas('sections', function ($q) use ($sectionId) {
                          $q->where('sections.id', $sectionId);
                      });
            })
            ->count();
            
        $absent = max(0, $totalStudentsInSection - ($present + $late));

        $session->update([
            'ended_at'      => now(),
            'status'        => 'ended',
            'present_count' => $present,
            'late_count'    => $late,
            'absent_count'  => $absent,
        ]);

        if (class_exists(MqttService::class)) {
            app(MqttService::class)->sessionEnd($session);
        }

        return response()->json(['success' => true, 'session' => $session]);
    }

    public function activeSessions(Request $request)
    {
        $sessions = ClassSession::where('professor_id', $request->user()->id)
            ->where('status', 'active')
            ->with('subject', 'room')
            ->get();

        return response()->json(['success' => true, 'sessions' => $sessions]);
    }

    public function sessionHistory(Request $request)
    {
        $sessions = ClassSession::where('professor_id', $request->user()->id)
            ->where('status', 'ended')
            ->with('subject.section')
            ->orderBy('ended_at', 'desc')
            ->get();

        return response()->json(['success' => true, 'sessions' => $sessions]);
    }
}