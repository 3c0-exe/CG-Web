<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\ClassSession;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // ─── Per Section Overall Attendance ───────────────────────────────────────
    //
    // GET /api/professor/reports/section-attendance?subject_id=1
    //
    // Returns: per-session breakdown for the subject's section showing
    // total present, late, absent counts across all ended sessions.

    public function sectionAttendance(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $subject = Subject::where('id', $request->subject_id)
            ->where('professor_id', $request->user()->id)
            ->with('section', 'yearLevel')
            ->first();

        if (!$subject) {
            return response()->json(['success' => false, 'message' => 'Subject not found or access denied.'], 403);
        }

        $sessions = ClassSession::where('subject_id', $subject->id)
            ->where('status', 'ended')
            ->orderBy('started_at', 'asc')
            ->get();

        // Students belonging to this section (regular + irregular)
        $sectionId = $subject->section_id;
        $students = User::where('role', 'student')
            ->where(function ($q) use ($sectionId) {
                $q->where('section_id', $sectionId)
                  ->orWhereHas('sections', fn($q2) => $q2->where('sections.id', $sectionId));
            })
            ->get(['id', 'name', 'student_id_number']);

        $totalStudents = $students->count();

        $sessionBreakdown = $sessions->map(function ($session) use ($students) {
            $records = AttendanceRecord::where('session_id', $session->id)->get();

            $presentCount = $records->where('status', 'present')->count();
            $lateCount    = $records->where('status', 'late')->count();
            $absentCount  = $students->count() - $records->count();

            return [
                'session_id'    => $session->session_id,
                'date'          => $session->started_at->toDateString(),
                'started_at'    => $session->started_at->format('h:i A'),
                'ended_at'      => $session->ended_at?->format('h:i A'),
                'present_count' => $presentCount,
                'late_count'    => $lateCount,
                'absent_count'  => max(0, $absentCount),
                'total'         => $students->count(),
                'rate'          => $students->count() > 0
                    ? round((($presentCount + $lateCount) / $students->count()) * 100)
                    : 0,
            ];
        });

        $totalSessions = $sessions->count();
        $overallPresent = $sessionBreakdown->sum('present_count');
        $overallLate    = $sessionBreakdown->sum('late_count');
        $overallAbsent  = $sessionBreakdown->sum('absent_count');
        $overallRate    = ($totalSessions * $totalStudents) > 0
            ? round((($overallPresent + $overallLate) / ($totalSessions * $totalStudents)) * 100)
            : 0;

        return response()->json([
            'success'  => true,
            'subject'  => [
                'id'         => $subject->id,
                'name'       => $subject->name,
                'section'    => $subject->section?->name,
                'year_level' => $subject->yearLevel?->name,
            ],
            'summary' => [
                'total_sessions' => $totalSessions,
                'total_students' => $totalStudents,
                'overall_rate'   => $overallRate,
                'overall_present'=> $overallPresent,
                'overall_late'   => $overallLate,
                'overall_absent' => $overallAbsent,
            ],
            'sessions' => $sessionBreakdown,
        ]);
    }

    // ─── Students Overall Attendance ──────────────────────────────────────────
    //
    // GET /api/professor/reports/student-attendance?subject_id=1
    //
    // Returns: per-student attendance summary (present, late, absent, rate)
    // across all ended sessions for the given subject.

    public function studentAttendance(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $subject = Subject::where('id', $request->subject_id)
            ->where('professor_id', $request->user()->id)
            ->with('section', 'yearLevel')
            ->first();

        if (!$subject) {
            return response()->json(['success' => false, 'message' => 'Subject not found or access denied.'], 403);
        }

        $sectionId   = $subject->section_id;
        $sessionIds  = ClassSession::where('subject_id', $subject->id)
            ->where('status', 'ended')
            ->pluck('id');
        $sessionCount = $sessionIds->count();

        $students = User::where('role', 'student')
            ->where(function ($q) use ($sectionId) {
                $q->where('section_id', $sectionId)
                  ->orWhereHas('sections', fn($q2) => $q2->where('sections.id', $sectionId));
            })
            ->get(['id', 'name', 'student_id_number', 'rfid_uid']);

        $studentData = $students->map(function ($student) use ($sessionIds, $sessionCount) {
            $records = AttendanceRecord::whereIn('session_id', $sessionIds)
                ->where('student_id', $student->id)
                ->get();

            $presentCount = $records->where('status', 'present')->count();
            $lateCount    = $records->where('status', 'late')->count();
            $recordedCount = $records->pluck('session_id')->unique()->count();
            $absentCount  = max(0, $sessionCount - $recordedCount);
            $attended     = $presentCount + $lateCount;
            $rate         = $sessionCount > 0 ? round(($attended / $sessionCount) * 100) : 0;

            return [
                'id'                => $student->id,
                'name'              => $student->name,
                'student_id_number' => $student->student_id_number,
                'present_count'     => $presentCount,
                'late_count'        => $lateCount,
                'absent_count'      => $absentCount,
                'attendance_rate'   => $rate,
            ];
        })->sortByDesc('attendance_rate')->values();

        return response()->json([
            'success'       => true,
            'subject'       => [
                'id'         => $subject->id,
                'name'       => $subject->name,
                'section'    => $subject->section?->name,
                'year_level' => $subject->yearLevel?->name,
            ],
            'session_count' => $sessionCount,
            'students'      => $studentData,
        ]);
    }

    // ─── Students At Risk ─────────────────────────────────────────────────────
    //
    // GET /api/professor/reports/at-risk?subject_id=1&threshold=3
    //
    // Returns: students with absences >= threshold (default: 3).
    // Professors can customize the threshold per their school's policy.

    public function atRisk(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'threshold'  => 'nullable|integer|min:1',
        ]);

        $subject = Subject::where('id', $request->subject_id)
            ->where('professor_id', $request->user()->id)
            ->with('section', 'yearLevel')
            ->first();

        if (!$subject) {
            return response()->json(['success' => false, 'message' => 'Subject not found or access denied.'], 403);
        }

        $threshold   = (int) ($request->threshold ?? 3);
        $sectionId   = $subject->section_id;
        $sessionIds  = ClassSession::where('subject_id', $subject->id)
            ->where('status', 'ended')
            ->pluck('id');
        $sessionCount = $sessionIds->count();

        $students = User::where('role', 'student')
            ->where(function ($q) use ($sectionId) {
                $q->where('section_id', $sectionId)
                  ->orWhereHas('sections', fn($q2) => $q2->where('sections.id', $sectionId));
            })
            ->get(['id', 'name', 'student_id_number', 'rfid_uid']);

        $atRiskStudents = $students->map(function ($student) use ($sessionIds, $sessionCount) {
            $records = AttendanceRecord::whereIn('session_id', $sessionIds)
                ->where('student_id', $student->id)
                ->get();

            $presentCount  = $records->where('status', 'present')->count();
            $lateCount     = $records->where('status', 'late')->count();
            $recordedCount = $records->pluck('session_id')->unique()->count();
            $absentCount   = max(0, $sessionCount - $recordedCount);
            $attended      = $presentCount + $lateCount;
            $rate          = $sessionCount > 0 ? round(($attended / $sessionCount) * 100) : 0;

            return [
                'id'                => $student->id,
                'name'              => $student->name,
                'student_id_number' => $student->student_id_number,
                'present_count'     => $presentCount,
                'late_count'        => $lateCount,
                'absent_count'      => $absentCount,
                'attendance_rate'   => $rate,
            ];
        })
        ->filter(fn($s) => $s['absent_count'] >= $threshold)
        ->sortByDesc('absent_count')
        ->values();

        return response()->json([
            'success'       => true,
            'subject'       => [
                'id'         => $subject->id,
                'name'       => $subject->name,
                'section'    => $subject->section?->name,
                'year_level' => $subject->yearLevel?->name,
            ],
            'threshold'     => $threshold,
            'session_count' => $sessionCount,
            'at_risk_count' => $atRiskStudents->count(),
            'students'      => $atRiskStudents,
        ]);
    }
}