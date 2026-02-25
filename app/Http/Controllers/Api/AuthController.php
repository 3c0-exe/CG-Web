<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string',
            'email'             => 'required|email|unique:users',
            'password'          => 'required|min:8|confirmed',
            'student_id_number' => 'required|unique:users',
            'year_level_id'     => 'required|exists:year_levels,id',
            'section_id'        => 'required|exists:sections,id',
            'phone'             => 'nullable|string',
        ]);

        $user = User::create([
            ...$validated,
            'password' => Hash::make($request->password),
            'role'     => 'student',
            'status'   => 'pending',
        ]);

        return response()->json(['success' => true, 'user' => $user], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);
        }

        if ($user->status === 'inactive') {
            return response()->json(['success' => false, 'message' => 'Account is inactive'], 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'user'    => $user,
            'token'   => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Logged out']);
    }

    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'user'    => $request->user()->load('yearLevel', 'section'),
        ]);
    }
}
