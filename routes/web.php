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
});

// Admin Dashboard & Views
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', fn() => view('admin.dashboard'));
    Route::get('/users', fn() => view('admin.users'));
    Route::get('/sections', fn() => view('admin.sections'));
    Route::get('/subjects', fn() => view('admin.subjects'));
});