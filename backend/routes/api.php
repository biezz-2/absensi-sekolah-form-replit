<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceController;

use App\Http\Controllers\DashboardController;

Route::get('/health', function () {
	return response()->json([
		'status' => 'ok',
		'app' => config('app.name'),
		'time' => now()->toIso8601String(),
	]);
});

Route::post('/classes/{classId}/sessions', [AttendanceController::class, 'createSession']);
Route::patch('/sessions/{uuid}/close', [AttendanceController::class, 'closeSession']);
Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn']);

// Statistik dashboard admin
Route::get('/dashboard/statistics', [DashboardController::class, 'statistics']);
