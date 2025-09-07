<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\ClassRoom;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
        public function createSession(Request $request, int $classId)
        {
                $data = $request->validate([
                        'start_time' => 'required|date',
                        'end_time' => 'required|date|after:start_time',
                ]);

                $class = ClassRoom::findOrFail($classId);

                $session = AttendanceSession::create([
                        'uuid' => (string) Str::uuid(),
                        'class_id' => $class->id,
                        'start_time' => $data['start_time'],
                        'end_time' => $data['end_time'],
                        'is_active' => true,
                ]);

                return response()->json([
                        'uuid' => $session->uuid,
                ]);
        }

        public function closeSession(string $uuid)
        {
                $session = AttendanceSession::where('uuid', $uuid)->firstOrFail();
                $session->update(['is_active' => false]);
                return response()->json(['is_active' => false]);
        }

        public function checkIn(Request $request)
        {
                $data = $request->validate([
                        'session_uuid' => 'required|uuid|exists:attendance_sessions,uuid',
                        'lat' => 'required|numeric|between:-90,90',
                        'lng' => 'required|numeric|between:-180,180',
                ]);

                $session = AttendanceSession::where('uuid', $data['session_uuid'])
                        ->where('is_active', true)
                        ->where('start_time', '<=', now())
                        ->where('end_time', '>=', now())
                        ->first();
                if (!$session) {
                        return response()->json(['status' => 'invalid', 'reason' => 'expired_or_closed'], 422);
                }

                $student = Student::where('user_id', $request->user()?->id)->first();
                if (!$student) {
                        return response()->json(['status' => 'invalid', 'reason' => 'not_student'], 403);
                }
                if ($student->class_id !== $session->class_id) {
                        return response()->json(['status' => 'invalid', 'reason' => 'wrong_class'], 422);
                }

                $class = ClassRoom::findOrFail($session->class_id);
                $distance = $this->calculateDistanceMeters($data['lat'], $data['lng'], (float) $class->location_latitude, (float) $class->location_longitude);
                $radius = (int) (env('ATTENDANCE_RADIUS_METERS', 50));
                $isValid = $distance <= $radius;

                $record = AttendanceRecord::updateOrCreate(
                        [
                                'student_id' => $student->id,
                                'attendance_session_id' => $session->id,
                        ],
                        [
                                'check_in_time' => now(),
                                'check_in_latitude' => $data['lat'],
                                'check_in_longitude' => $data['lng'],
                                'check_in_ip' => $request->ip(),
                                'is_valid' => $isValid,
                                'reason' => $isValid ? null : 'outside_radius',
                        ]
                );

                return response()->json([
                        'status' => $isValid ? 'ok' : 'invalid',
                        'record' => $record,
                ]);
        }

        public function getHistory(Request $request)
        {
                $query = AttendanceRecord::with(['student.user', 'attendanceSession.class'])
                        ->orderBy('check_in_time', 'desc');

                if ($request->has('student_id')) {
                        $query->where('student_id', $request->student_id);
                }

                if ($request->has('class_id')) {
                        $query->whereHas('attendanceSession', function ($q) use ($request) {
                                $q->where('class_id', $request->class_id);
                        });
                }

                $records = $query->paginate(20);
                return response()->json($records);
        }

        public function getSessions(Request $request)
        {
                $query = AttendanceSession::with(['class'])
                        ->orderBy('start_time', 'desc');

                if ($request->has('class_id')) {
                        $query->where('class_id', $request->class_id);
                }

                $sessions = $query->paginate(20);
                return response()->json($sessions);
        }

        public function getAttendanceReport(Request $request)
        {
                $query = AttendanceRecord::with(['student.user', 'attendanceSession.class'])
                        ->selectRaw('
                                COUNT(*) as total_records,
                                SUM(CASE WHEN is_valid = 1 THEN 1 ELSE 0 END) as valid_records,
                                SUM(CASE WHEN is_valid = 0 THEN 1 ELSE 0 END) as invalid_records,
                                student_id
                        ')
                        ->groupBy('student_id');

                if ($request->has('class_id')) {
                        $query->whereHas('attendanceSession', function ($q) use ($request) {
                                $q->where('class_id', $request->class_id);
                        });
                }

                if ($request->has('date_from')) {
                        $query->where('check_in_time', '>=', $request->date_from);
                }

                if ($request->has('date_to')) {
                        $query->where('check_in_time', '<=', $request->date_to);
                }

                $report = $query->get();
                return response()->json($report);
        }

        private function calculateDistanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
        {
                $earthRadius = 6371000; // meters
                $dLat = deg2rad($lat2 - $lat1);
                $dLng = deg2rad($lng2 - $lng1);
                $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
                return 2 * $earthRadius * asin(min(1, sqrt($a)));
        }
}


