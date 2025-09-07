<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\ClassController;
use App\Http\Controllers\DashboardController;

Route::get('/health', function () {
        return response()->json([
                'status' => 'ok',
                'app' => config('app.name'),
                'time' => now()->toIso8601String(),
        ]);
});

// Attendance routes
Route::post('/classes/{classId}/sessions', [AttendanceController::class, 'createSession']);
Route::patch('/sessions/{uuid}/close', [AttendanceController::class, 'closeSession']);
Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn']);
Route::get('/attendance/history', [AttendanceController::class, 'getHistory']);
Route::get('/attendance/sessions', [AttendanceController::class, 'getSessions']);

// Students CRUD
Route::apiResource('students', StudentController::class);

// Teachers CRUD  
Route::apiResource('teachers', TeacherController::class);

// Classes CRUD
Route::apiResource('classes', ClassController::class);

// Reports
Route::get('/reports/attendance', [AttendanceController::class, 'getAttendanceReport']);

// Dashboard statistics
Route::get('/dashboard/statistics', [DashboardController::class, 'statistics']);
