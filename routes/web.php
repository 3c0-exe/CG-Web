<?php

use Illuminate\Support\Facades\Route;

// Auth
Route::get('/', fn() => redirect('/login'));
Route::get('/login', fn() => view('auth.login'));
Route::get('/register', fn() => view('auth.register'));

// Student
Route::get('/student/dashboard', fn() => view('student.dashboard'));
Route::get('/student/classes', fn() => view('student.classes'));
Route::get('/student/attendance', fn() => view('student.attendance'));
Route::get('/student/rfid-link', fn() => view('student.rfid-link'));

// Professor
Route::get('/professor/dashboard', fn() => view('professor.dashboard'));
Route::get('/professor/subjects', fn() => view('professor.subjects'));
Route::get('/professor/live-attendance', fn() => view('professor.live-attendance'));
Route::get('/professor/history', fn() => view('professor.history'));

// Admin
Route::get('/admin/dashboard', fn() => view('admin.dashboard'));
Route::get('/admin/users', fn() => view('admin.users'));
