<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Subject;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function joinClass(Request $request)
    {
        $request->validate([
            'class_code' => 'required|string',
        ]);

        $subject = Subject::where('class_code', strtoupper($request->class_code))->first();

        if (!$subject) {
            return response()->json(['success' => false, 'message' => 'Invalid class code'], 404);
        }

        $alreadyEnrolled = Enrollment::where('student_id', $request->user()->id)
            ->where('subject_id', $subject->id)
            ->exists();

        if ($alreadyEnrolled) {
            return response()->json(['success' => false, 'message' => 'Already enrolled'], 400);
        }

        $isGuest = $request->user()->section_id !== $subject->section_id;

        if ($isGuest && !$subject->allow_guests) {
            return response()->json(['success' => false, 'message' => 'This class does not allow guest students'], 403);
        }

        $enrollment = Enrollment::create([
            'student_id'      => $request->user()->id,
            'subject_id'      => $subject->id,
            'enrollment_type' => $isGuest ? 'guest' : 'regular',
            'home_section_id' => $isGuest ? $request->user()->section_id : null,
        ]);

        return response()->json([
            'success'    => true,
            'is_guest'   => $isGuest,
            'enrollment' => $enrollment->load('subject'),
        ]);
    }

    public function myClasses(Request $request)
    {
        $enrollments = Enrollment::where('student_id', $request->user()->id)
            ->with(['subject.professor', 'subject.section', 'subject.yearLevel'])
            ->get();

        return response()->json(['success' => true, 'classes' => $enrollments]);
    }

    public function leaveClass(Request $request, $subjectId)
    {
        $enrollment = Enrollment::where('student_id', $request->user()->id)
            ->where('subject_id', $subjectId)
            ->first();

        if (!$enrollment) {
            return response()->json(['success' => false, 'message' => 'Enrollment not found'], 404);
        }

        $enrollment->delete();

        return response()->json(['success' => true, 'message' => 'Left class successfully']);
    }
}
