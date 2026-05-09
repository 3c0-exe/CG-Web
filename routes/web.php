<?php

use Illuminate\Support\Facades\Route;

// Auth
Route::get('/', fn() => redirect('/login'));
Route::get('/login', fn() => view('auth.login'))->name('login');

// Professor Dashboard & Views
Route::prefix('professor')->group(function () {
    Route::get('/dashboard', fn() => view('professor.dashboard'));
    Route::get('/subjects', fn() => view('professor.subjects'));
    Route::get('/live-attendance', fn() => view('professor.live-attendance'));
    Route::get('/history', fn() => view('professor.history'));
    Route::get('/students', fn() => view('professor.students'));
    Route::get('/rooms', fn() => view('professor.rooms'));

    // Reports
    Route::get('/reports/session-overview', fn() => view('professor.reports.session-overview'));
    Route::get('/reports/student-records', fn() => view('professor.reports.student-records'));
    Route::get('/reports/at-risk', fn() => view('professor.reports.at-risk'));
});

// Admin Dashboard & Views
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', fn() => view('admin.dashboard'));
    Route::get('/users', fn() => view('admin.users'));
    Route::get('/sections', fn() => view('admin.sections'));
    Route::get('/subjects', fn() => view('admin.subjects'));
    Route::get('/schedules', fn() => view('admin.schedules'));
    Route::get('/prospectus', fn() => view('admin.prospectus'));
    Route::get('/rooms', fn() => view('admin.rooms'));
});