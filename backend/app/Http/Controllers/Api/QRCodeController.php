<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\ClassRoom;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class QRCodeController extends Controller
{
    public function generateSessionQR(Request $request): JsonResponse
    {
        $data = $request->validate([
            'class_id' => 'required|exists:class_rooms,id',
            'duration_minutes' => 'required|integer|min:5|max:240', // 5 minutes to 4 hours
            'name' => 'required|string|max:255',
        ]);

        // Check if user is teacher and has access to this class
        $user = $request->user();
        if ($user->user_type === 'teacher') {
            $teacher = $user->teacher;
            $class = ClassRoom::where('id', $data['class_id'])
                            ->where('teacher_id', $teacher->id)
                            ->first();
            
            if (!$class) {
                return response()->json(['message' => 'You do not have access to this class'], 403);
            }
        }

        $startTime = now();
        $endTime = now()->addMinutes($data['duration_minutes']);
        
        // Close any existing active sessions for this class
        AttendanceSession::where('class_id', $data['class_id'])
                        ->where('is_active', true)
                        ->update(['is_active' => false]);

        $session = AttendanceSession::create([
            'uuid' => (string) Str::uuid(),
            'class_id' => $data['class_id'],
            'name' => $data['name'],
            'start_time' => $startTime,
            'end_time' => $endTime,
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        // Generate QR Code data (URL that students will scan)
        $qrData = [
            'session_uuid' => $session->uuid,
            'class_id' => $data['class_id'],
            'expires_at' => $endTime->toISOString(),
            'type' => 'attendance'
        ];

        return response()->json([
            'session' => $session->load('class'),
            'qr_data' => $qrData,
            'qr_string' => json_encode($qrData),
            'expires_at' => $endTime,
            'duration_minutes' => $data['duration_minutes']
        ]);
    }

    public function getActiveSession(Request $request, $classId): JsonResponse
    {
        $session = AttendanceSession::where('class_id', $classId)
                                  ->where('is_active', true)
                                  ->where('end_time', '>', now())
                                  ->with('class')
                                  ->first();

        if (!$session) {
            return response()->json(['message' => 'No active session found'], 404);
        }

        $qrData = [
            'session_uuid' => $session->uuid,
            'class_id' => $session->class_id,
            'expires_at' => $session->end_time->toISOString(),
            'type' => 'attendance'
        ];

        return response()->json([
            'session' => $session,
            'qr_data' => $qrData,
            'qr_string' => json_encode($qrData),
            'time_remaining' => now()->diffInMinutes($session->end_time)
        ]);
    }

    public function closeSession(Request $request, $sessionId): JsonResponse
    {
        $session = AttendanceSession::findOrFail($sessionId);
        
        // Check if user has permission to close this session
        $user = $request->user();
        if ($user->user_type === 'teacher') {
            $teacher = $user->teacher;
            $class = ClassRoom::where('id', $session->class_id)
                            ->where('teacher_id', $teacher->id)
                            ->first();
            
            if (!$class) {
                return response()->json(['message' => 'You do not have access to this session'], 403);
            }
        }

        $session->update([
            'is_active' => false,
            'closed_at' => now(),
            'closed_by' => $user->id
        ]);

        return response()->json([
            'message' => 'Session closed successfully',
            'session' => $session
        ]);
    }
}