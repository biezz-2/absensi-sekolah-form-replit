<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Student;
use App\Models\ClassRoom;
use App\Models\AttendanceSession;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function getAttendanceReport(Request $request): JsonResponse
    {
        $data = $request->validate([
            'class_id' => 'nullable|exists:class_rooms,id',
            'student_id' => 'nullable|exists:students,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'period' => 'nullable|in:today,week,month,semester',
            'export_format' => 'nullable|in:json,csv,excel'
        ]);

        // Set date range based on period
        if (isset($data['period'])) {
            switch ($data['period']) {
                case 'today':
                    $data['date_from'] = now()->startOfDay();
                    $data['date_to'] = now()->endOfDay();
                    break;
                case 'week':
                    $data['date_from'] = now()->startOfWeek();
                    $data['date_to'] = now()->endOfWeek();
                    break;
                case 'month':
                    $data['date_from'] = now()->startOfMonth();
                    $data['date_to'] = now()->endOfMonth();
                    break;
                case 'semester':
                    $data['date_from'] = now()->startOfYear();
                    $data['date_to'] = now()->endOfYear();
                    break;
            }
        }

        $query = AttendanceRecord::with(['student.user', 'attendanceSession.class']);

        if (isset($data['class_id'])) {
            $query->whereHas('attendanceSession', function ($q) use ($data) {
                $q->where('class_id', $data['class_id']);
            });
        }

        if (isset($data['student_id'])) {
            $query->where('student_id', $data['student_id']);
        }

        if (isset($data['date_from'])) {
            $query->where('check_in_time', '>=', $data['date_from']);
        }

        if (isset($data['date_to'])) {
            $query->where('check_in_time', '<=', $data['date_to']);
        }

        $records = $query->orderBy('check_in_time', 'desc')->get();

        // Calculate statistics
        $totalRecords = $records->count();
        $validRecords = $records->where('is_valid', true)->count();
        $invalidRecords = $records->where('is_valid', false)->count();
        $attendanceRate = $totalRecords > 0 ? round(($validRecords / $totalRecords) * 100, 2) : 0;

        // Group by student for detailed analysis
        $studentStats = $records->groupBy('student_id')->map(function ($studentRecords) {
            $student = $studentRecords->first()->student;
            $total = $studentRecords->count();
            $valid = $studentRecords->where('is_valid', true)->count();
            $invalid = $studentRecords->where('is_valid', false)->count();

            return [
                'student' => $student,
                'total_attendance' => $total,
                'valid_attendance' => $valid,
                'invalid_attendance' => $invalid,
                'attendance_rate' => $total > 0 ? round(($valid / $total) * 100, 2) : 0,
                'records' => $studentRecords
            ];
        })->values();

        // Group by class
        $classStats = $records->groupBy(function ($record) {
            return $record->attendanceSession->class_id;
        })->map(function ($classRecords) {
            $class = $classRecords->first()->attendanceSession->class;
            $total = $classRecords->count();
            $valid = $classRecords->where('is_valid', true)->count();

            return [
                'class' => $class,
                'total_attendance' => $total,
                'valid_attendance' => $valid,
                'invalid_attendance' => $total - $valid,
                'attendance_rate' => $total > 0 ? round(($valid / $total) * 100, 2) : 0
            ];
        })->values();

        // Daily attendance trend
        $dailyTrend = $records->groupBy(function ($record) {
            return $record->check_in_time->format('Y-m-d');
        })->map(function ($dayRecords, $date) {
            $total = $dayRecords->count();
            $valid = $dayRecords->where('is_valid', true)->count();

            return [
                'date' => $date,
                'total_attendance' => $total,
                'valid_attendance' => $valid,
                'invalid_attendance' => $total - $valid,
                'attendance_rate' => $total > 0 ? round(($valid / $total) * 100, 2) : 0
            ];
        })->values();

        $report = [
            'summary' => [
                'total_records' => $totalRecords,
                'valid_records' => $validRecords,
                'invalid_records' => $invalidRecords,
                'overall_attendance_rate' => $attendanceRate,
                'period' => $data['period'] ?? 'custom',
                'date_from' => $data['date_from'] ?? null,
                'date_to' => $data['date_to'] ?? null,
                'generated_at' => now()
            ],
            'student_statistics' => $studentStats,
            'class_statistics' => $classStats,
            'daily_trend' => $dailyTrend,
            'detailed_records' => $records
        ];

        // Handle export formats
        if (isset($data['export_format']) && $data['export_format'] === 'csv') {
            return $this->exportToCsv($report);
        }

        return response()->json($report);
    }

    public function getDashboardAnalytics(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month'); // today, week, month

        $dateFrom = match($period) {
            'today' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            default => now()->startOfMonth()
        };

        // Basic counts
        $totalStudents = Student::count();
        $totalClasses = ClassRoom::count();
        $totalSessions = AttendanceSession::where('created_at', '>=', $dateFrom)->count();
        $totalAttendance = AttendanceRecord::where('check_in_time', '>=', $dateFrom)->count();
        $validAttendance = AttendanceRecord::where('check_in_time', '>=', $dateFrom)->where('is_valid', true)->count();

        // Attendance rate
        $attendanceRate = $totalAttendance > 0 ? round(($validAttendance / $totalAttendance) * 100, 2) : 0;

        // Active sessions
        $activeSessions = AttendanceSession::where('is_active', true)
            ->where('end_time', '>', now())
            ->count();

        // Recent activity
        $recentAttendance = AttendanceRecord::with(['student.user', 'attendanceSession.class'])
            ->where('check_in_time', '>=', now()->subHours(24))
            ->orderBy('check_in_time', 'desc')
            ->limit(10)
            ->get();

        // Top performing classes
        $topClasses = AttendanceRecord::selectRaw('
                COUNT(*) as total_attendance,
                SUM(CASE WHEN is_valid = 1 THEN 1 ELSE 0 END) as valid_attendance
            ')
            ->with(['attendanceSession.class'])
            ->where('check_in_time', '>=', $dateFrom)
            ->groupBy('attendance_session_id')
            ->having('total_attendance', '>', 0)
            ->get()
            ->map(function ($record) {
                $rate = round(($record->valid_attendance / $record->total_attendance) * 100, 2);
                return [
                    'class' => $record->attendanceSession->class ?? null,
                    'total_attendance' => $record->total_attendance,
                    'valid_attendance' => $record->valid_attendance,
                    'attendance_rate' => $rate
                ];
            })
            ->filter(function ($item) {
                return $item['class'] !== null;
            })
            ->sortByDesc('attendance_rate')
            ->take(5)
            ->values();

        return response()->json([
            'overview' => [
                'total_students' => $totalStudents,
                'total_classes' => $totalClasses,
                'total_sessions' => $totalSessions,
                'total_attendance' => $totalAttendance,
                'valid_attendance' => $validAttendance,
                'attendance_rate' => $attendanceRate,
                'active_sessions' => $activeSessions
            ],
            'recent_activity' => $recentAttendance,
            'top_classes' => $topClasses,
            'period' => $period,
            'date_from' => $dateFrom,
            'generated_at' => now()
        ]);
    }

    private function exportToCsv($report): JsonResponse
    {
        $csvData = [];
        $csvData[] = ['Student Name', 'Student ID', 'Class', 'Check In Time', 'Status', 'Reason'];

        foreach ($report['detailed_records'] as $record) {
            $csvData[] = [
                $record->student->user->name,
                $record->student->student_id_number,
                $record->attendanceSession->class->name,
                $record->check_in_time->format('Y-m-d H:i:s'),
                $record->is_valid ? 'Valid' : 'Invalid',
                $record->reason ?? 'N/A'
            ];
        }

        // Convert to CSV string
        $csvString = '';
        foreach ($csvData as $row) {
            $csvString .= implode(',', array_map(function($field) {
                return '"' . str_replace('"', '""', $field) . '"';
            }, $row)) . "\n";
        }

        return response()->json([
            'export_format' => 'csv',
            'csv_data' => $csvString,
            'filename' => 'attendance_report_' . now()->format('Y-m-d_H-i-s') . '.csv',
            'records_count' => count($csvData) - 1 // Exclude header
        ]);
    }
}