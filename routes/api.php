<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RfidController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\SessionController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AdminController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// MQTT webhook (called by ESP32 / MQTT listener)
Route::post('/attendance/scan', [AttendanceController::class, 'scanCard']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // RFID
    Route::post('/rfid/link', [RfidController::class, 'linkCard']);
    Route::post('/rfid/unlink', [RfidController::class, 'unlinkCard']);

    // Enrollment (student)
    Route::post('/enrollment/join', [EnrollmentController::class, 'joinClass']);
    Route::get('/enrollment/my-classes', [EnrollmentController::class, 'myClasses']);
    Route::delete('/enrollment/leave/{subjectId}', [EnrollmentController::class, 'leaveClass']);

    // Sessions (professor)
    Route::post('/session/start', [SessionController::class, 'startSession']);
    Route::post('/session/end/{sessionId}', [SessionController::class, 'endSession']);
    Route::get('/session/active', [SessionController::class, 'activeSessions']);
    Route::get('/session/history', [SessionController::class, 'sessionHistory']);

    // Attendance
    Route::post('/attendance/confirm', [AttendanceController::class, 'confirmCode']);
    Route::get('/attendance/live/{sessionId}', [AttendanceController::class, 'liveFeed']);
    Route::get('/attendance/my', [AttendanceController::class, 'myAttendance']);

    // Admin only
    Route::prefix('admin')->group(function () {
        Route::get('/stats', [AdminController::class, 'stats']);
        Route::get('/users', [AdminController::class, 'allUsers']);
        Route::post('/users/professor', [AdminController::class, 'createProfessor']);
        Route::patch('/users/{userId}/status', [AdminController::class, 'updateUserStatus']);
        Route::get('/subjects', [AdminController::class, 'allSubjects']);
        Route::post('/subjects', [AdminController::class, 'createSubject']);
        Route::get('/year-levels', [AdminController::class, 'yearLevels']);

        // Sections
        Route::post('/sections', [AdminController::class, 'createSection']);
        Route::patch('/sections/{sectionId}', [AdminController::class, 'updateSection']);
        Route::delete('/sections/{sectionId}', [AdminController::class, 'deleteSection']);
    });
});
