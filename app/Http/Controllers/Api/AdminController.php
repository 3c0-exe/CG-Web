<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subject;
use App\Models\ClassSession;
use App\Models\Section;
use App\Models\YearLevel;
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

        return response()->json(['success' => true, 'subject' => $subject], 201);
    }

    public function allSubjects()
    {
        $subjects = Subject::with('professor', 'section', 'yearLevel')->get();
        return response()->json(['success' => true, 'subjects' => $subjects]);
    }

    public function yearLevels()
    {
        return response()->json(['success' => true, 'year_levels' => YearLevel::with('sections')->get()]);
    }
}
