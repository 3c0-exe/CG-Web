<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subject;
use App\Models\ClassSession;
use App\Models\Section;
use App\Models\YearLevel;
use App\Models\Enrollment;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function stats()
    {
        return response()->json([
            'success' => true,
            'stats'   => [
                'total_students'    => User::where('role', 'student')->count(),
                'total_professors'  => User::where('role', 'professor')->count(),
                'total_subjects'    => Subject::count(),
                'active_sessions'   => ClassSession::where('status', 'active')->count(),
                'pending_users'     => User::where('status', 'pending')->count(),
            ],
        ]);
    }

    public function allUsers(Request $request)
    {
        $role  = $request->query('role');
        $query = User::with('yearLevel', 'section');

        if ($role) {
            $query->where('role', $role);
        }

        return response()->json(['success' => true, 'users' => $query->get()]);
    }

    public function createProfessor(Request $request)
    {
        $request->validate([
            'name'     => 'required|string',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        $professor = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'professor',
            'status'   => 'active',
        ]);

        return response()->json(['success' => true, 'user' => $professor], 201);
    }

    public function updateUserStatus(Request $request, $userId)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,pending',
        ]);

        $user = User::findOrFail($userId);
        $user->update(['status' => $request->status]);

        return response()->json(['success' => true, 'user' => $user]);
    }

    // ─── Subject Management ───────────────────────────────────────────────────

    public function allSubjects()
    {
        $subjects = Subject::with('professor', 'section', 'yearLevel')->get();
        return response()->json(['success' => true, 'subjects' => $subjects]);
    }

    public function createSubject(Request $request)
    {
        $request->validate([
            'name'                   => 'required|string',
            'code'                   => 'required|string',
            'year_level_id'          => 'required|exists:year_levels,id',
            'section_id'             => 'required|exists:sections,id',
            'professor_id'           => 'required|exists:users,id',
            'allow_guests'           => 'boolean',
            'late_threshold_minutes' => 'integer|min:1',
        ]);

        $subject = Subject::create([
            ...$request->all(),
            'class_code' => strtoupper(\Illuminate\Support\Str::random(6)),
        ]);

        return response()->json(['success' => true, 'subject' => $subject->load('professor', 'section', 'yearLevel')], 201);
    }

    public function updateSubject(Request $request, $subjectId)
    {
        $request->validate([
            'name'                   => 'required|string',
            'code'                   => 'required|string',
            'year_level_id'          => 'required|exists:year_levels,id',
            'section_id'             => 'required|exists:sections,id',
            'professor_id'           => 'required|exists:users,id',
            'allow_guests'           => 'boolean',
            'late_threshold_minutes' => 'integer|min:1',
        ]);

        $subject = Subject::findOrFail($subjectId);
        $subject->update($request->only([
            'name',
            'code',
            'year_level_id',
            'section_id',
            'professor_id',
            'allow_guests',
            'late_threshold_minutes',
        ]));

        return response()->json(['success' => true, 'subject' => $subject->load('professor', 'section', 'yearLevel')]);
    }

    public function deleteSubject($subjectId)
    {
        $subject = Subject::findOrFail($subjectId);

        // Prevent deletion if there are active sessions
        $activeSession = ClassSession::where('subject_id', $subjectId)
            ->where('status', 'active')
            ->exists();

        if ($activeSession) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete a subject with an active session. End the session first.',
            ], 400);
        }

        $subject->delete();

        return response()->json(['success' => true, 'message' => 'Subject deleted successfully']);
    }

    // ─── Professor: Students by Subject ──────────────────────────────────────

    public function professorStudents(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $subjectId = $request->query('subject_id');

        // Verify the professor owns this subject
        $subject = Subject::where('id', $subjectId)
            ->where('professor_id', $request->user()->id)
            ->first();

        if (!$subject) {
            return response()->json(['success' => false, 'message' => 'Subject not found or not assigned to you'], 403);
        }

        // Total ended sessions for this subject (for rate calculation)
        $sessionCount = ClassSession::where('subject_id', $subjectId)
            ->where('status', 'ended')
            ->count();

        // Get enrollments with student info
        $enrollments = Enrollment::where('subject_id', $subjectId)
            ->with(['student.section'])
            ->get();

        // Get all session IDs for this subject
        $sessionIds = ClassSession::where('subject_id', $subjectId)
            ->where('status', 'ended')
            ->pluck('id');

        $students = $enrollments->map(function ($enrollment) use ($sessionIds, $sessionCount) {
            $student = $enrollment->student;

            if (!$student) return null;

            // Calculate attendance counts for this subject
            $presentCount = 0;
            $lateCount    = 0;
            $absentCount  = 0;

            if ($sessionIds->isNotEmpty()) {
                $presentCount = AttendanceRecord::whereIn('session_id', $sessionIds)
                    ->where('student_id', $student->id)
                    ->where('status', 'present')
                    ->count();

                $lateCount = AttendanceRecord::whereIn('session_id', $sessionIds)
                    ->where('student_id', $student->id)
                    ->where('status', 'late')
                    ->count();

                $absentCount = AttendanceRecord::whereIn('session_id', $sessionIds)
                    ->where('student_id', $student->id)
                    ->where('status', 'absent')
                    ->count();

                // Students with no record at all for a session are also absent
                // Count sessions where student has no record
                $recordedSessions = AttendanceRecord::whereIn('session_id', $sessionIds)
                    ->where('student_id', $student->id)
                    ->distinct('session_id')
                    ->count('session_id');
                $absentCount += ($sessionCount - $recordedSessions);
            }

            $attended = $presentCount + $lateCount;
            $rate = $sessionCount > 0 ? round(($attended / $sessionCount) * 100) : 0;

            return [
                'id'                => $student->id,
                'name'              => $student->name,
                'email'             => $student->email,
                'student_id_number' => $student->student_id_number,
                'rfid_uid'          => $student->rfid_uid,
                'status'            => $student->status,
                'section'           => $student->section,
                'enrollment_type'   => $enrollment->enrollment_type,
                'present_count'     => $presentCount,
                'late_count'        => $lateCount,
                'absent_count'      => $absentCount,
                'attendance_rate'   => $rate,
            ];
        })->filter()->values();

        return response()->json([
            'success'       => true,
            'students'      => $students,
            'session_count' => $sessionCount,
            'subject'       => $subject->load('section'),
        ]);
    }

    // ─── Year Levels ──────────────────────────────────────────────────────────

    public function yearLevels()
    {
        return response()->json(['success' => true, 'year_levels' => YearLevel::with('sections')->get()]);
    }

    // ─── Section Management ───────────────────────────────────────────────────

    public function createSection(Request $request)
    {
        $request->validate([
            'year_level_id' => 'required|exists:year_levels,id',
            'name'          => 'required|string',
        ]);

        $exists = Section::where('year_level_id', $request->year_level_id)
            ->where('name', strtoupper($request->name))
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Section already exists'], 400);
        }

        $section = Section::create([
            'year_level_id' => $request->year_level_id,
            'name'          => strtoupper($request->name),
        ]);

        return response()->json(['success' => true, 'section' => $section], 201);
    }

    public function updateSection(Request $request, $sectionId)
    {
        $request->validate([
            'year_level_id' => 'required|exists:year_levels,id',
            'name'          => 'required|string',
        ]);

        $section = Section::findOrFail($sectionId);
        $section->update([
            'year_level_id' => $request->year_level_id,
            'name'          => strtoupper($request->name),
        ]);

        return response()->json(['success' => true, 'section' => $section]);
    }

    public function deleteSection($sectionId)
    {
        $section = Section::findOrFail($sectionId);
        $section->delete();
        return response()->json(['success' => true, 'message' => 'Section deleted']);
    }
}