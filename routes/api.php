<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RfidController;
use App\Http\Controllers\Api\SessionController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AdminController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::get('/year-levels', [AdminController::class, 'yearLevels']);

// MQTT webhook (called by ESP32 / MQTT listener)
Route::post('/attendance/scan', [AttendanceController::class, 'scanCard']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {

    // Global Auth / Utilities
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // RFID Management
    Route::post('/rfid/link', [RfidController::class, 'linkCard']);
    Route::post('/rfid/unlink', [RfidController::class, 'unlinkCard']);

    // Professor Routes
    Route::prefix('professor')->group(function () {
        Route::get('/subjects', [AdminController::class, 'professorSubjects']);
        Route::post('/session/start', [SessionController::class, 'startSession']);
        Route::post('/session/end/{sessionId}', [SessionController::class, 'endSession']);
        Route::get('/session/active', [SessionController::class, 'activeSessions']);
        Route::get('/session/history', [SessionController::class, 'sessionHistory']);
        Route::get('/attendance/live/{sessionId}', [AttendanceController::class, 'liveFeed']);
        Route::get('/students', [AdminController::class, 'professorStudents']);
    });

    // Admin Routes
    Route::prefix('admin')->group(function () {
        Route::get('/stats', [AdminController::class, 'stats']);

        // User Management
        Route::post('/users/import-students', [AdminController::class, 'importStudentsCsv']);
        Route::get('/users', [AdminController::class, 'allUsers']);
        Route::post('/users/professor', [AdminController::class, 'createProfessor']);
        Route::patch('/users/{userId}/status', [AdminController::class, 'updateUserStatus']);
        Route::patch('/users/{userId}', [AdminController::class, 'updateUser']); // Fix: was missing

        // Subjects
        Route::get('/subjects', [AdminController::class, 'allSubjects']);
        Route::post('/subjects', [AdminController::class, 'createSubject']);
        Route::patch('/subjects/{subjectId}', [AdminController::class, 'updateSubject']);
        Route::delete('/subjects/{subjectId}', [AdminController::class, 'deleteSubject']);

        // Sections & Year Levels
        Route::get('/year-levels', [AdminController::class, 'yearLevels']);
        Route::post('/sections', [AdminController::class, 'createSection']);
        Route::patch('/sections/{sectionId}', [AdminController::class, 'updateSection']);
        Route::delete('/sections/{sectionId}', [AdminController::class, 'deleteSection']);
    });
});