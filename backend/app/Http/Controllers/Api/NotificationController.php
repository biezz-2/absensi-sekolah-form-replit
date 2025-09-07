<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function getNotifications(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifications = [];

        if ($user->user_type === 'teacher' || $user->user_type === 'admin') {
            // Get recent attendance activities
            $recentAttendance = AttendanceRecord::with(['student.user', 'attendanceSession.class'])
                ->where('created_at', '>=', now()->subHours(2))
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            foreach ($recentAttendance as $record) {
                $notifications[] = [
                    'id' => 'attendance_' . $record->id,
                    'type' => 'attendance',
                    'title' => $record->is_valid ? 'Absensi Berhasil' : 'Absensi Gagal',
                    'message' => $record->student->user->name . ' melakukan absensi di ' . $record->attendanceSession->class->name,
                    'status' => $record->is_valid ? 'success' : 'warning',
                    'timestamp' => $record->created_at,
                    'data' => [
                        'student_name' => $record->student->user->name,
                        'class_name' => $record->attendanceSession->class->name,
                        'is_valid' => $record->is_valid,
                        'reason' => $record->reason
                    ]
                ];
            }
        }

        if ($user->user_type === 'student') {
            // Get student's recent attendance
            $student = $user->student;
            if ($student) {
                $recentAttendance = AttendanceRecord::with(['attendanceSession.class'])
                    ->where('student_id', $student->id)
                    ->where('created_at', '>=', now()->subDays(7))
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();

                foreach ($recentAttendance as $record) {
                    $notifications[] = [
                        'id' => 'my_attendance_' . $record->id,
                        'type' => 'my_attendance',
                        'title' => $record->is_valid ? 'Absensi Anda Berhasil' : 'Absensi Anda Gagal',
                        'message' => 'Absensi di ' . $record->attendanceSession->class->name . ' pada ' . $record->created_at->format('d/m/Y H:i'),
                        'status' => $record->is_valid ? 'success' : 'error',
                        'timestamp' => $record->created_at,
                        'data' => [
                            'class_name' => $record->attendanceSession->class->name,
                            'is_valid' => $record->is_valid,
                            'reason' => $record->reason
                        ]
                    ];
                }
            }
        }

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => count(array_filter($notifications, function($n) {
                return $n['timestamp'] >= now()->subHour();
            }))
        ]);
    }

    public function sendTestNotification(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => 'required|in:success,warning,error,info',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:500'
        ]);

        // In a real application, this would broadcast to WebSocket or push notification service
        return response()->json([
            'notification_sent' => true,
            'notification' => [
                'id' => 'test_' . time(),
                'type' => $data['type'],
                'title' => $data['title'],
                'message' => $data['message'],
                'timestamp' => now(),
                'test' => true
            ]
        ]);
    }
}