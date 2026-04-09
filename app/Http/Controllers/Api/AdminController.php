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

    public function updateUser(Request $request, $userId)
    {
        $request->validate([
            'name'              => 'required|string',
            'student_id_number' => 'nullable|string',
            'rfid_uid'          => 'nullable|string',
        ]);

        $user = \App\Models\User::findOrFail($userId);
        
        // Ensure the new RFID isn't already assigned to someone else
        if ($request->rfid_uid && $request->rfid_uid !== $user->rfid_uid) {
            $exists = \App\Models\User::where('rfid_uid', $request->rfid_uid)->exists();
            if ($exists) {
                return response()->json(['success' => false, 'message' => 'This RFID UID is already assigned to another user.'], 400);
            }
        }

        $user->update([
            'name'              => $request->name,
            'student_id_number' => $request->student_id_number,
            'rfid_uid'          => $request->rfid_uid,
        ]);

        return response()->json(['success' => true, 'user' => $user]);
    }

    public function importStudentsCsv(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120', // Max 5MB
        ]);

        $file = $request->file('file');
        $fileHandle = fopen($file->getPathname(), 'r');
        
        // Read the first row (headers) and skip it
        $header = fgetcsv($fileHandle);

        $importedCount = 0;
        $skippedCount = 0;

        while (($row = fgetcsv($fileHandle)) !== false) {
            // Map row data to variables based on our expected CSV columns
            // [0]name, [1]email, [2]student_id, [3]rfid, [4]year_code, [5]section_name
            if (count($row) < 6) {
                $skippedCount++;
                continue; // Skip incomplete rows
            }

            $name = trim($row[0]);
            $email = trim($row[1]);
            $studentId = trim($row[2]);
            $rfidUid = trim($row[3]);
            $yearCode = strtoupper(trim($row[4]));
            $sectionName = strtoupper(trim($row[5]));

            // 1. Find the Year Level by its code (e.g., '1Y', '2Y')
            $yearLevel = \App\Models\YearLevel::where('code', $yearCode)->first();
            
            if (!$yearLevel) {
                $skippedCount++;
                continue; // Skip if year level doesn't exist in system
            }

            // 2. Find or Create the Section
            $section = \App\Models\Section::firstOrCreate(
                ['name' => $sectionName, 'year_level_id' => $yearLevel->id]
            );

            // 3. Create or Update the Student Record
            // We use updateOrCreate so if they upload the same file twice, it updates instead of crashing
            \App\Models\User::updateOrCreate(
                ['email' => $email], // Search by email
                [
                    'name'              => $name,
                    'student_id_number' => $studentId,
                    'rfid_uid'          => empty($rfidUid) ? null : $rfidUid,
                    'year_level_id'     => $yearLevel->id,
                    'section_id'        => $section->id,
                    'role'              => 'student',
                    'status'            => 'active',
                    // Give a random dummy password since students can't log in anymore
                    'password'          => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(16)), 
                ]
            );

            $importedCount++;
        }

        fclose($fileHandle);

        return response()->json([
            'success' => true,
            'message' => "Import complete. $importedCount students imported/updated. $skippedCount rows skipped.",
        ]);
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
            'year_level_id'          => 'required|exists:year_levels,id',
            'section_id'             => 'required|exists:sections,id',
            'professor_id'           => 'required|exists:users,id',
            'allow_guests'           => 'boolean',
            'late_threshold_minutes' => 'integer|min:1',
        ]);

        $subject = \App\Models\Subject::create($request->all());

        return response()->json(['success' => true, 'subject' => $subject->load('professor', 'section', 'yearLevel')], 201);
    }

    public function updateSubject(Request $request, $subjectId)
    {
        $request->validate([
            'name'                   => 'required|string',
            'year_level_id'          => 'required|exists:year_levels,id',
            'section_id'             => 'required|exists:sections,id',
            'professor_id'           => 'required|exists:users,id',
            'allow_guests'           => 'boolean',
            'late_threshold_minutes' => 'integer|min:1',
        ]);

        $subject = \App\Models\Subject::findOrFail($subjectId);
        $subject->update($request->only([
            'name',
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
        $subject = \App\Models\Subject::where('id', $subjectId)
            ->where('professor_id', $request->user()->id)
            ->first();

        if (!$subject) {
            return response()->json(['success' => false, 'message' => 'Subject not found or not assigned to you'], 403);
        }

        // Total ended sessions for this subject (for rate calculation)
        $sessionCount = \App\Models\ClassSession::where('subject_id', $subjectId)
            ->where('status', 'ended')
            ->count();

        // INSTEAD OF ENROLLMENTS: Just get all students in the Subject's Section!
        $studentsInSection = \App\Models\User::where('role', 'student')
            ->where('section_id', $subject->section_id)
            ->get();

        // Get all ended session IDs for this subject
        $sessionIds = \App\Models\ClassSession::where('subject_id', $subjectId)
            ->where('status', 'ended')
            ->pluck('id');

        $students = $studentsInSection->map(function ($student) use ($sessionIds, $sessionCount) {
            $presentCount = 0;
            $lateCount    = 0;
            $absentCount  = 0;

            if ($sessionIds->isNotEmpty()) {
                $presentCount = \App\Models\AttendanceRecord::whereIn('session_id', $sessionIds)
                    ->where('student_id', $student->id)
                    ->where('status', 'present')
                    ->count();

                $lateCount = \App\Models\AttendanceRecord::whereIn('session_id', $sessionIds)
                    ->where('student_id', $student->id)
                    ->where('status', 'late')
                    ->count();

                $absentCount = \App\Models\AttendanceRecord::whereIn('session_id', $sessionIds)
                    ->where('student_id', $student->id)
                    ->where('status', 'absent')
                    ->count();

                // Implicit absences (they didn't tap their card at all)
                $recordedSessions = \App\Models\AttendanceRecord::whereIn('session_id', $sessionIds)
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
                'present_count'     => $presentCount,
                'late_count'        => $lateCount,
                'absent_count'      => $absentCount,
                'attendance_rate'   => $rate,
            ];
        });

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