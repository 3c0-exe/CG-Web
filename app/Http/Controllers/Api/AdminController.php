<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subject;
use App\Models\ClassSession;
use App\Models\Section;
use App\Models\YearLevel;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    // ─── Dashboard Stats ──────────────────────────────────────────────────────

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

    // ─── User Management ──────────────────────────────────────────────────────

    public function allUsers(Request $request)
    {
        $role  = $request->query('role');
        $query = User::with('yearLevel', 'section', 'sections');

        if ($role) {
            $query->where('role', $role);
        }

        return response()->json(['success' => true, 'users' => $query->get()]);
    }

public function createProfessor(Request $request)
    {
        $request->validate([
            'title'    => 'required|in:Prof.,Ms.,Mrs.,Mr.,Dr.,Engr.,Atty.',
            'name'     => 'required|string',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        $professor = User::create([
            'title'    => $request->title,
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
        $user = User::findOrFail($userId);

        $rules = [
            'name'              => 'required|string',
            'student_id_number' => 'nullable|string',
            'rfid_uid'          => 'nullable|string',
        ];

        if ($user->role === 'professor') {
            $rules['title'] = 'required|in:Prof.,Ms.,Mrs.,Mr.,Dr.,Engr.,Atty.';
        }

        $request->validate($rules);

        if ($request->rfid_uid && $request->rfid_uid !== $user->rfid_uid) {
            $exists = User::where('rfid_uid', $request->rfid_uid)->exists();
            if ($exists) {
                return response()->json(['success' => false, 'message' => 'This RFID UID is already assigned to another user.'], 400);
            }
        }

        $fields = [
            'name'              => $request->name,
            'student_id_number' => $request->student_id_number,
            'rfid_uid'          => $request->rfid_uid,
        ];

        if ($user->role === 'professor') {
            $fields['title'] = $request->title;
        }

        if ($user->role === 'student' && $request->has('is_irregular')) {
            $fields['is_irregular'] = (bool) $request->is_irregular;
            // If switching back to regular, clear the pivot table
            if (!$request->is_irregular) {
                $user->sections()->detach();
            }
        }

        $user->update($fields);

        return response()->json(['success' => true, 'user' => $user->load('section', 'yearLevel', 'sections')]);
    }

    public function importStudentsCsv(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('file');
        $fileHandle = fopen($file->getPathname(), 'r');

        $header = fgetcsv($fileHandle); // Skip header

        $importedCount = 0;
        $skippedCount = 0;

        $debugLog = [];

        while (($row = fgetcsv($fileHandle)) !== false) {
            $debugLog[] = $row;

            // Skip blank rows silently (don't count as skipped)
            if (count(array_filter($row, fn($v) => trim($v) !== '')) === 0) {
                continue;
            }

            if (count($row) < 4) {
                $skippedCount++;
                continue;
            }

            $name      = trim($row[0]);
            $email     = trim($row[1]);
            $studentId = trim($row[2]);
            $rfidUid   = trim($row[3]);

            if (empty($name) || empty($email)) {
                $skippedCount++;
                continue;
            }

            $rfidValue = empty($rfidUid) ? null : $rfidUid;

            // If RFID is provided, check it isn't already taken by a different user
            if ($rfidValue) {
                $conflict = User::where('rfid_uid', $rfidValue)
                    ->where('email', '!=', $email)
                    ->exists();
                if ($conflict) {
                    $skippedCount++;
                    $rfidValue = null; // Don't skip the row — just clear the RFID and continue importing
                }
            }

            try {
                User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name'              => $name,
                        'student_id_number' => empty($studentId) ? null : $studentId,
                        'rfid_uid'          => $rfidValue,
                        'role'              => 'student',
                        'status'            => 'active',
                        'password'          => Hash::make(Str::random(16)),
                    ]
                );
                $importedCount++;
            } catch (\Throwable $e) {
                $skippedCount++;
                \Log::error("CSV import row failed: " . $e->getMessage(), ['row' => $row]);
            }
        }

        fclose($fileHandle);

        return response()->json([
            'success' => true,
            'message' => "Import complete. $importedCount students imported/updated. $skippedCount rows skipped (duplicate RFID cleared or missing fields).",
        ]);
    }

    public function assignSection(Request $request, $userId)
    {
        $request->validate([
            'year_level_id' => 'nullable|exists:year_levels,id',
            'section_id'    => 'nullable|exists:sections,id',
        ]);

        $user = User::findOrFail($userId);

        if ($user->role !== 'student') {
            return response()->json(['success' => false, 'message' => 'Only students can be assigned to sections.'], 400);
        }

        if ($user->is_irregular) {
            return response()->json(['success' => false, 'message' => 'Use the irregular section endpoints for irregular students.'], 400);
        }

        $user->update([
            'year_level_id' => $request->year_level_id,
            'section_id'    => $request->section_id,
        ]);

        return response()->json(['success' => true, 'user' => $user->load('section', 'yearLevel', 'sections')]);
    }

    public function addSection(Request $request, $userId)
    {
        $request->validate([
            'year_level_id' => 'required|exists:year_levels,id',
            'section_id'    => 'required|exists:sections,id',
        ]);

        $user = User::findOrFail($userId);

        if ($user->role !== 'student') {
            return response()->json(['success' => false, 'message' => 'Only students can be assigned to sections.'], 400);
        }

        if (!$user->is_irregular) {
            return response()->json(['success' => false, 'message' => 'Student is not marked as irregular.'], 400);
        }

        // Prevent duplicate pivot entry
        $already = $user->sections()->where('section_id', $request->section_id)->exists();
        if ($already) {
            return response()->json(['success' => false, 'message' => 'Student is already assigned to this section.'], 400);
        }

        $user->sections()->attach($request->section_id, [
            'year_level_id' => $request->year_level_id,
        ]);

        return response()->json(['success' => true, 'user' => $user->load('sections')]);
    }

    public function removeSection(Request $request, $userId, $sectionId)
    {
        $user = User::findOrFail($userId);

        if ($user->role !== 'student' || !$user->is_irregular) {
            return response()->json(['success' => false, 'message' => 'Invalid operation.'], 400);
        }

        $user->sections()->detach($sectionId);

        return response()->json(['success' => true, 'user' => $user->load('sections')]);
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
            'schedule_days'          => 'nullable|array',
            'schedule_days.*'        => 'in:Mon,Tue,Wed,Thu,Fri,Sat,Sun',
            'schedule_start_time'    => 'nullable|date_format:H:i',
            'schedule_end_time'      => 'nullable|date_format:H:i|after:schedule_start_time',
        ]);

        $subject = Subject::create($request->only([
            'name', 'year_level_id', 'section_id', 'professor_id',
            'allow_guests', 'late_threshold_minutes',
            'schedule_days', 'schedule_start_time', 'schedule_end_time',
        ]));

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
            'schedule_days'          => 'nullable|array',
            'schedule_days.*'        => 'in:Mon,Tue,Wed,Thu,Fri,Sat,Sun',
            'schedule_start_time'    => 'nullable|date_format:H:i',
            'schedule_end_time'      => 'nullable|date_format:H:i|after:schedule_start_time',
        ]);

        $subject = Subject::findOrFail($subjectId);
        $subject->update($request->only([
            'name', 'year_level_id', 'section_id', 'professor_id',
            'allow_guests', 'late_threshold_minutes',
            'schedule_days', 'schedule_start_time', 'schedule_end_time',
        ]));

        return response()->json(['success' => true, 'subject' => $subject->load('professor', 'section', 'yearLevel')]);
    }

    public function deleteSubject($subjectId)
    {
        $subject = Subject::findOrFail($subjectId);

        $activeSession = ClassSession::where('subject_id', $subjectId)
            ->where('status', 'active')
            ->exists();

        if ($activeSession) {
            return response()->json(['success' => false, 'message' => 'Cannot delete a subject with an active session.'], 400);
        }

        $subject->delete();
        return response()->json(['success' => true, 'message' => 'Subject deleted']);
    }

    // ─── Professor specific logic ─────────────────────────────────────────────

    public function professorSubjects(Request $request)
    {
        $subjects = Subject::where('professor_id', $request->user()->id)
            ->with('section', 'yearLevel')
            ->get();

        return response()->json(['success' => true, 'subjects' => $subjects]);
    }

    public function professorStudents(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $subjectId = $request->query('subject_id');

        $subject = Subject::where('id', $subjectId)
            ->where('professor_id', $request->user()->id)
            ->first();

        if (!$subject) return response()->json(['success' => false, 'message' => 'Subject not found'], 403);

        $sessionCount = ClassSession::where('subject_id', $subjectId)->where('status', 'ended')->count();
        $sectionId = $subject->section_id;
        $studentsInSection = User::where('role', 'student')
            ->where(function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId)
                      ->orWhereHas('sections', function ($q) use ($sectionId) {
                          $q->where('sections.id', $sectionId);
                      });
            })
            ->get();
        $sessionIds = ClassSession::where('subject_id', $subjectId)->where('status', 'ended')->pluck('id');

        $students = $studentsInSection->map(function ($student) use ($sessionIds, $sessionCount) {
            $presentCount = 0; $lateCount = 0; $absentCount = 0;

            if ($sessionIds->isNotEmpty()) {
                $presentCount = AttendanceRecord::whereIn('session_id', $sessionIds)->where('student_id', $student->id)->where('status', 'present')->count();
                $lateCount    = AttendanceRecord::whereIn('session_id', $sessionIds)->where('student_id', $student->id)->where('status', 'late')->count();
                $absentCount  = AttendanceRecord::whereIn('session_id', $sessionIds)->where('student_id', $student->id)->where('status', 'absent')->count();

                $recordedSessions = AttendanceRecord::whereIn('session_id', $sessionIds)->where('student_id', $student->id)->distinct('session_id')->count('session_id');
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

        return response()->json(['success' => true, 'students' => $students, 'session_count' => $sessionCount, 'subject' => $subject->load('section')]);
    }

    // ─── Year Levels & Sections ───────────────────────────────────────────────

    public function yearLevels()
    {
        return response()->json(['success' => true, 'year_levels' => YearLevel::with('sections')->get()]);
    }

    public function createSection(Request $request)
    {
        $request->validate([
            'year_level_id' => 'required|exists:year_levels,id',
            'name'          => 'required|string',
        ]);

        $exists = Section::where('year_level_id', $request->year_level_id)->where('name', strtoupper($request->name))->exists();
        if ($exists) return response()->json(['success' => false, 'message' => 'Section already exists'], 400);

        $section = Section::create(['year_level_id' => $request->year_level_id, 'name' => strtoupper($request->name)]);
        return response()->json(['success' => true, 'section' => $section], 201);
    }

    public function updateSection(Request $request, $sectionId)
    {
        $request->validate(['year_level_id' => 'required|exists:year_levels,id', 'name' => 'required|string']);
        $section = Section::findOrFail($sectionId);
        $section->update(['year_level_id' => $request->year_level_id, 'name' => strtoupper($request->name)]);
        return response()->json(['success' => true, 'section' => $section]);
    }

    public function deleteSection($sectionId)
    {
        Section::findOrFail($sectionId)->delete();
        return response()->json(['success' => true, 'message' => 'Section deleted']);
    }

    // ─── Room Management ──────────────────────────────────────────────────────

    public function allRooms()
    {
        return response()->json(['success' => true, 'rooms' => \App\Models\Room::orderBy('name')->get()]);
    }

    public function roomAvailability()
    {
        $rooms = \App\Models\Room::orderBy('name')->get();

        $activeSessions = \App\Models\ClassSession::where('status', 'active')
            ->with(['subject.professor', 'subject.section', 'room'])
            ->get()
            ->keyBy('room_id');

        $now = now();

        $result = $rooms->map(function ($room) use ($activeSessions, $now) {
            $session = $activeSessions->get($room->id);

            if (!$session) {
                return [
                    'id'     => $room->id,
                    'name'   => $room->name,
                    'status' => 'available',
                ];
            }

            $subject = $session->subject;
            $professor = $subject?->professor;

            // Compute time remaining if schedule_end_time is set, otherwise elapsed
            $timeInfo = null;
            $timeType = null;

            if ($subject?->schedule_end_time) {
                $endTime = \Carbon\Carbon::createFromTimeString($subject->schedule_end_time);
                // Use today's date with the schedule end time
                $endToday = $now->copy()->setTimeFromTimeString($subject->schedule_end_time);
                $diffMins = (int) $now->diffInMinutes($endToday, false);

                if ($diffMins > 0) {
                    $h = intdiv($diffMins, 60);
                    $m = $diffMins % 60;
                    $timeInfo = $h > 0 ? "{$h}h {$m}m remaining" : "{$m}m remaining";
                    $timeType = 'remaining';
                } else {
                    $timeInfo = 'Overtime';
                    $timeType = 'overtime';
                }
            } else {
                // Fallback: elapsed
                $elapsedMins = (int) $now->diffInMinutes(\Carbon\Carbon::parse($session->started_at));
                $h = intdiv($elapsedMins, 60);
                $m = $elapsedMins % 60;
                $timeInfo = $h > 0 ? "{$h}h {$m}m elapsed" : "{$m}m elapsed";
                $timeType = 'elapsed';
            }

            return [
                'id'      => $room->id,
                'name'    => $room->name,
                'status'  => 'occupied',
                'session' => [
                    'subject'     => $subject?->name,
                    'section'     => $subject?->section?->name,
                    'professor'   => $professor
                        ? trim(($professor->title ? $professor->title . ' ' : '') . $professor->name)
                        : null,
                    'started_at'  => $session->started_at,
                    'time_info'   => $timeInfo,
                    'time_type'   => $timeType,
                ],
            ];
        });

        return response()->json(['success' => true, 'rooms' => $result]);
    }

    public function createRoom(Request $request)
    {
        $request->validate(['name' => 'required|string|unique:rooms,name']);
        $room = \App\Models\Room::create(['name' => $request->name]);
        return response()->json(['success' => true, 'room' => $room], 201);
    }

    public function updateRoom(Request $request, $roomId)
    {
        $request->validate(['name' => 'required|string|unique:rooms,name,' . $roomId]);
        $room = \App\Models\Room::findOrFail($roomId);
        $room->update(['name' => $request->name]);
        return response()->json(['success' => true, 'room' => $room]);
    }

    public function deleteRoom($roomId)
    {
        $room = \App\Models\Room::findOrFail($roomId);
        $active = \App\Models\ClassSession::where('room_id', $roomId)->where('status', 'active')->exists();
        if ($active) {
            return response()->json(['success' => false, 'message' => 'Cannot delete a room with an active session.'], 400);
        }
        $room->delete();
        return response()->json(['success' => true, 'message' => 'Room deleted']);
    }
    // ─── Subject Enrollment ───────────────────────────────────────────────────

    public function subjectEnrollments($subjectId)
    {
        $subject = Subject::findOrFail($subjectId);
        $enrolled = $subject->enrolledStudents()->get();
        return response()->json(['success' => true, 'students' => $enrolled]);
    }

    public function enrollStudents(Request $request, $subjectId)
    {
        $request->validate([
            'student_ids'   => 'required|array',
            'student_ids.*' => 'exists:users,id',
        ]);

        $subject = Subject::findOrFail($subjectId);

        $syncData = collect($request->student_ids)->mapWithKeys(fn($id) => [
            $id => ['enrollment_type' => 'regular']
        ])->toArray();

        // syncWithoutDetaching so we don't remove existing enrollments
        $subject->enrolledStudents()->syncWithoutDetaching($syncData);

        return response()->json(['success' => true, 'message' => count($request->student_ids) . ' student(s) enrolled.']);
    }

    public function unenrollStudent($subjectId, $studentId)
    {
        $subject = Subject::findOrFail($subjectId);
        $subject->enrolledStudents()->detach($studentId);
        return response()->json(['success' => true, 'message' => 'Student removed from subject.']);
    }

    public function availableStudentsForSubject($subjectId)
    {
        $subject = Subject::findOrFail($subjectId);
        $enrolledIds = $subject->enrolledStudents()->pluck('users.id');

        // All students not yet enrolled in this subject
        $students = User::where('role', 'student')
            ->whereNotIn('id', $enrolledIds)
            ->orderBy('name')
            ->get(['id', 'name', 'student_id_number', 'section_id']);

        return response()->json(['success' => true, 'students' => $students]);
    }
}