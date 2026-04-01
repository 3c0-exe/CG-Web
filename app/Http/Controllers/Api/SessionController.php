<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassSession;
use App\Models\Enrollment;
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
        ]);

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
            'started_at'   => now(),
            'status'       => 'active',
        ]);

        $session->load('subject.section');
        app(MqttService::class)->sessionStart($session);

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

        // Mark all still-pending records as absent
        AttendanceRecord::where('session_id', $session->id)
            ->where('status', 'pending')
            ->update(['status' => 'absent']);

        // Update counts
        $present = AttendanceRecord::where('session_id', $session->id)->where('status', 'present')->count();
        $late    = AttendanceRecord::where('session_id', $session->id)->where('status', 'late')->count();
        $absent  = AttendanceRecord::where('session_id', $session->id)->where('status', 'absent')->count();

        $session->update([
            'ended_at'      => now(),
            'status'        => 'ended',
            'present_count' => $present,
            'late_count'    => $late,
            'absent_count'  => $absent,
        ]);

        app(MqttService::class)->sessionEnd($session);

        return response()->json(['success' => true, 'session' => $session]);
    }

    public function activeSessions(Request $request)
    {
        $sessions = ClassSession::where('professor_id', $request->user()->id)
            ->where('status', 'active')
            ->with('subject')
            ->get();

        return response()->json(['success' => true, 'sessions' => $sessions]);
    }

    public function sessionHistory(Request $request)
    {
        $sessions = ClassSession::where('professor_id', $request->user()->id)
            ->where('status', 'ended')
            ->with('subject')
            ->orderBy('ended_at', 'desc')
            ->get();

        return response()->json(['success' => true, 'sessions' => $sessions]);
    }
}