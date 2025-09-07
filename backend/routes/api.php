<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\ClassController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\QRCodeController;
use App\Http\Controllers\Api\GeolocationController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\DashboardController;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\ClassRoom;
use App\Models\Student;

Route::get('/health', function () {
        return response()->json([
                'status' => 'ok',
                'app' => config('app.name'),
                'time' => now()->toIso8601String(),
        ]);
});

// Authentication routes (public)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Public routes for testing (temporary) - remove these in production
Route::get('/attendance/history', function(Request $request) {
    $records = AttendanceRecord::with(['student.user', 'session.classroom'])
        ->when($request->date_from, function($query) use ($request) {
            return $query->whereDate('check_in_time', '>=', $request->date_from);
        })
        ->when($request->date_to, function($query) use ($request) {
            return $query->whereDate('check_in_time', '<=', $request->date_to);
        })
        ->orderBy('check_in_time', 'desc')
        ->paginate(15);
    
    return response()->json($records);
});

Route::get('/dashboard/statistics', function() {
    $totalStudents = Student::count();
    $totalClasses = ClassRoom::count();
    $totalSessions = AttendanceSession::count();
    $totalAttendance = AttendanceRecord::count();
    $validAttendance = AttendanceRecord::where('is_valid', true)->count();
    
    return response()->json([
        'total_students' => $totalStudents,
        'total_classes' => $totalClasses,
        'total_sessions' => $totalSessions,
        'total_attendance' => $totalAttendance,
        'valid_attendance' => $validAttendance,
        'invalid_attendance' => $totalAttendance - $validAttendance,
        'attendance_rate' => $totalAttendance > 0 ? round(($validAttendance / $totalAttendance) * 100, 2) : 0
    ]);
});

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth user info
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // QR Code generation (teachers and admins only)
    Route::middleware(['role:teacher,admin'])->group(function () {
        Route::post('/qr/generate', [QRCodeController::class, 'generateSessionQR']);
        Route::get('/qr/session/{classId}', [QRCodeController::class, 'getActiveSession']);
        Route::patch('/qr/session/{sessionId}/close', [QRCodeController::class, 'closeSession']);
    });

    // Attendance routes
    Route::post('/classes/{classId}/sessions', [AttendanceController::class, 'createSession']);
    Route::patch('/sessions/{uuid}/close', [AttendanceController::class, 'closeSession']);
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn']);
    Route::get('/attendance/sessions', [AttendanceController::class, 'getSessions']);

    // Geolocation routes
    Route::post('/location/validate', [GeolocationController::class, 'validateLocation']);
    Route::get('/location/nearby-classes', [GeolocationController::class, 'getNearbyClasses']);
    Route::get('/location/permissions', [GeolocationController::class, 'getLocationPermissions']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'getNotifications']);
    Route::post('/notifications/test', [NotificationController::class, 'sendTestNotification'])->middleware('role:admin');

    // Admin and Teacher routes
    Route::middleware(['role:admin,teacher'])->group(function () {
        Route::apiResource('students', StudentController::class);
        Route::apiResource('teachers', TeacherController::class)->middleware('role:admin');
        Route::apiResource('classes', ClassController::class);
        
        // Advanced reporting
        Route::get('/reports/attendance', [ReportController::class, 'getAttendanceReport']);
        Route::get('/reports/analytics', [ReportController::class, 'getDashboardAnalytics']);
        
        // Basic dashboard statistics
        Route::get('/dashboard/statistics', [DashboardController::class, 'statistics']);
    });
});
