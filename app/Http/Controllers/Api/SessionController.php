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
            'override'   => 'boolean',
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

        // ── Schedule validation ───────────────────────────────────────────────

        // Block if no schedule set
        if (empty($subject->schedule_days) || !$subject->schedule_start_time || !$subject->schedule_end_time) {
            return response()->json([
                'success' => false,
                'message' => 'This subject has no schedule set. Please ask your admin to add a schedule before starting a session.',
            ], 422);
        }

        $now          = now();
        $dayMap       = [0 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'];
        $today        = $dayMap[$now->dayOfWeek];
        $scheduleDays = is_array($subject->schedule_days) ? $subject->schedule_days : json_decode($subject->schedule_days, true);

        $wrongDay    = !in_array($today, $scheduleDays);
        $startTime   = \Carbon\Carbon::createFromTimeString($subject->schedule_start_time);
        $endTime     = \Carbon\Carbon::createFromTimeString($subject->schedule_end_time);
        $beforeStart = $now->lt($startTime);
        $afterEnd    = $now->gt($endTime);

        // Soft warnings — professor can override
        if (!$request->override) {
            if ($wrongDay) {
                return response()->json([
                    'success'  => false,
                    'warning'  => true,
                    'message'  => "⚠️ Today ({$today}) is not a scheduled day for this subject (" . implode(', ', $scheduleDays) . "). Start anyway?",
                ], 422);
            }

            if ($beforeStart) {
                $formatted = \Carbon\Carbon::createFromTimeString($subject->schedule_start_time)->format('g:i A');
                return response()->json([
                    'success' => false,
                    'warning' => true,
                    'message' => "⚠️ This subject is scheduled to start at {$formatted}. Start early anyway?",
                ], 422);
            }
        }

        // After end time — session starts but late_override flag noted
        // Students will still be marked based on late_threshold_minutes from session start
        // but we flag the session started after schedule end
        $startedAfterEnd = $afterEnd;

        // ── Create session ────────────────────────────────────────────────────

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
            'success'           => true,
            'session'           => $session,
            'started_after_end' => $startedAfterEnd,
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
        
        $sectionId  = $session->subject->section_id;
        $subjectId  = $session->subject_id;

        // Students in section (regular + irregular)
        $sectionStudentIds = \App\Models\User::where('role', 'student')
            ->where(function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId)
                      ->orWhereHas('sections', function ($q) use ($sectionId) {
                          $q->where('sections.id', $sectionId);
                      });
            })
            ->pluck('id');

        // Explicitly enrolled students NOT already in the section
        $extraEnrolledIds = \App\Models\Enrollment::where('subject_id', $subjectId)
            ->whereNotIn('student_id', $sectionStudentIds)
            ->pluck('student_id');

        $totalExpected = $sectionStudentIds->count() + $extraEnrolledIds->count();
        $absent = max(0, $totalExpected - ($present + $late));

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